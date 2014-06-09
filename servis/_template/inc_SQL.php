<?php
/*~ inc_SQL.php - Execution of SQL statements/scripts.
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend and backend)                                 |
|   Version: 2.2.0                                                          |
|   Contact: contact author (also http://blaz.at/home)                      |
| ------------------------------------------------------------------------- |
|    Author: Blaž Kristan (blaz@kristan-sp.si)                              |
| Copyright (c) 2007-2014, Blaž Kristan. All Rights Reserved.               |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (LGPL)     |
|            http://www.gnu.org/copyleft/lesser.html                        |
| ------------------------------------------------------------------------- |
| This file is part of N3O CMS (backend).                                   |
|                                                                           |
| N3O CMS is free software: you can redistribute it and/or                  |
| modify it under the terms of the GNU Lesser General Public License as     |
| published by the Free Software Foundation, either version 3 of the        |
| License, or (at your option) any later version.                           |
|                                                                           |
| N3O CMS is distributed in the hope that it will be useful,                |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU Lesser General Public License for more details.                       |
'---------------------------------------------------------------------------'
*/

if ( isset($_POST['Load']) && $_POST['Load'] != "" ) {
	// may also use $_SERVER['DOCUMENT_ROOT']
	$SQLdata = file_get_contents($StoreRoot ."/servis/qry/". $_POST['Load'], "r");
}

if ( isset($_POST['SQL']) && $_POST['SQL'] != "" )
	$SQLdata = $_POST['SQL'];

?>
<script language="JavaScript" type="text/javascript">
<!-- //
function customResize () {
	// vertically resize edit child divs
	edit = $("#divContent").height(0).height( $("#divEdit").height() + $("#divEdit").position().top - $("#divContent").position().top );
}
$(document).ready(function(){
	window.customResize = customResize;

	$("form[name='Vnos']").submit(function(){
		$(this).ajaxSubmit({target: '#divEdit'});
		return false;
	});
	// resize view
	toggleFrame(0);
	if ( window.tReload ) clearTimeout( window.tReload );
	$("#divList").text('');
	
	// resize content div
	window.customResize();
});
//-->
</script>
<div class="subtitle">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
	<td><div id="ToggleFrame" style="display:none;">&nbsp;<A HREF="javascript:toggleFrame()"><img src="pic/control.frame.gif" height="14" width="14" alt="Preklop celo/zmanjšano okno" border="0" align="absmiddle" class="icon">&nbsp;List</a></div></td>
	<td align="right">SQL database access (Use with caution!)</td>
</tr>
</table>
</div>
<div>
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post" ENCTYPE="multipart/form-data">
<TABLE ALIGN="center" BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD ALIGN="left" VALIGN="bottom">SQL statement(s):</TD>
	<TD ALIGN="right" VALIGN="bottom">Load:
	<SELECT NAME="Load" SIZE="1">
		<OPTION VALUE="">- select -</OPTION>
<?php
	$SQLfiles = scandir($StoreRoot ."/servis/qry/");
	foreach ( $SQLfiles as $SQLfile )
		if ( is_file($StoreRoot."/servis/qry/".$SQLfile) && right($SQLfile, 4) == ".sql" )
			echo "<OPTION VALUE=\"$SQLfile\">$SQLfile</OPTION>\n";
?>
	</SELECT>&nbsp;
	<INPUT TYPE="submit" VALUE=" Execute " CLASS="but">&nbsp;
	</TD>
</TR>
<TR>
	<TD COLSPAN="2"><TEXTAREA NAME="SQL" ROWS="6" STYLE="width:100%;"><?php if (isset($SQLdata)) echo $SQLdata; ?></TEXTAREA></TD>
</TR>
</TABLE>
</FORM>
</div>
<DIV ID="divContent" STYLE="background-color: whitesmoke; overflow: auto; border: inset 1px;">
<?php
if ( isset($_POST['SQL']) && $_POST['SQL'] != "" ) {

	// parse SQL string into statements
	$_POST['SQL'] = str_replace("\r\n", "\n", $_POST['SQL']); // remove CR
	$SQLcmds      = explode(";\n", $_POST['SQL']);

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";

	// execute each SQL statement
	foreach ( $SQLcmds as $SQLcmd ) {

		// cleanup SQL statement
		$SQLcmd = preg_replace("/\s--[^\r\n]*/i", ' ', $SQLcmd); // strip comment
		$SQLcmd = str_replace("\n", ' ', $SQLcmd); // convert LF to space
		$SQLcmd = preg_replace('/\s\s+/', ' ', $SQLcmd); // reduce whitespace
		$SQLcmd = str_replace("\'", "'", $SQLcmd);	// correct Safari behaviour
			
		// if nonempty string or SQL comment
		if ( rtrim(ltrim($SQLcmd)) != "" ) {

			// evaluate variables in SQL string 
			//$SQLcmd = evaluate(DE($SQLcmd));
			
			$db->query($SQLcmd); // execute query (cache results for SELECT)

			if ( strtoupper(left($SQLcmd,6)) == "SELECT" || strtoupper(left($SQLcmd,4)) == "SHOW" ) {

				echo "<TR VALIGN=\"top\">\n";
				echo "<TD COLSPAN=\"2\">\n";

				echo "<TABLE BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
				echo "<TR VALIGN=\"top\">\n";
				// display column names
				foreach ( $db->get_col_info("name") as $Name )
					echo "<TD>&nbsp;<B>$Name</B></TD>\n";
				echo "</TR>\n";

				// get cached results
				$DoIt = $db->get_results( null, ARRAY_N );

				$BgCol = "white";
				if ( $DoIt ) foreach ( $DoIt as $Row ) {
					// row background color
					if ( $BgCol == "white" )
						$BgCol="#edf3fe";
					else
						$BgCol = "white";
					echo "<TR bgcolor=\"$BgCol\" VALIGN=\"top\">\n";
					// display column values
					foreach ( $Row as $Col )
						echo "<TD>&nbsp;$Col</TD>\n";
					echo "</TR>\n";
				}
				echo "</TABLE>\n";
				echo "</TD>\n";
				echo "</TR>\n";

			} else {

				echo "<TR VALIGN=\"top\">\n";
				echo "<TD><B>OK:</B>&nbsp;</TD>\n";
				echo "<TD>&nbsp;$SQLcmd</TD>\n";
				echo "</TR>\n";

			}
		}
	}
	echo "</TABLE>\n";
}
?>
</DIV>