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

?>
<script language="JavaScript" type="text/javascript">
<!-- //
$('#edit').live('pageinit', function(event){
	// bind to the form's submit event
	$("form[name='Vnos']").submit(function(){
		// inside event callbacks 'this' is the DOM element so we first
		// wrap it in a jQuery object
		jqObj = $(this);
		if ( (this.SQLfile.selectedIndex && this.SQLfile.selectedIndex==0) || empty(this.SQL) )	{alert("Izberite datoteko ali vpišite SQL!"); this.SQL.focus(); return false;}
		return true;
	});
	if ( window.tReload ) clearTimeout( window.tReload );
});
//-->
</script>
<?php
echo "<div id=\"edit\" data-role=\"page\" data-title=\"SQL\">\n";
echo "\t<div data-role=\"header\" data-theme=\"b\">\n";
echo "\t\t<h1>SQL</h1>\n";
echo "\t\t<a href=\"./\" title=\"Home\" class=\"ui-btn-left\" data-direction=\"reverse\" data-iconpos=\"notext\" data-icon=\"home\" data-ajax=\"false\">Home</a>\n";
echo "\t</div>\n";
echo "\t<div data-role=\"content\">\n";

if ( isset($_POST['SQL']) ) {
	// read SQL file from disk
	if ( isset($_POST['SQLfile']) && $_POST['SQLfile'] != "" ) {
		$SQLdata = file_get_contents($StoreRoot ."/servis/qry/". $_POST['SQLfile'], "r");
		$_POST['SQL'] = $SQLdata;
	} else 
		$SQLdata = $_POST['SQL'];

	echo "<div class=\"ui-body ui-body-d ui-corner-all\" style=\"padding:1em;text-align:center;\">\n";

	// parse SQL string into statements
	$_POST['SQL'] = str_replace("\r\n", "\n", $_POST['SQL']); // remove CR
	$SQLcmds      = explode(";\n", $_POST['SQL']);

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";

	// execute each SQL statement
	foreach ( $SQLcmds as $SQLcmd ) {

		// if nonempty string or SQL comment
		if ( rtrim(ltrim($SQLcmd)) != "" && left(rtrim(ltrim($SQLcmd)),2) != "--" ) {

			// evaluate variables in SQL string 
			//$SQLcmd = evaluate(DE($SQLcmd));
			$SQLcmd = str_replace( "\'", "'", $SQLcmd );	// correct Safari behaviour

			// execute query (cache results for SELECT)
			$db->query($SQLcmd);

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

				$BgCol = "";
				if ( $DoIt ) foreach ( $DoIt as $Row ) {
					// change row background color
					$BgCol = ($BgCol=="")? "#edf3fe" : "";
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

	echo "</div>\n";
	echo "<div><a href=\"".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\" data-role=\"button\" data-direction=\"reverse\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-theme=\"c\" data-ajax=\"false\">Back</a></div>\n";
} else {
?>
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post" ENCTYPE="multipart/form-data">
<fieldset class="ui-hide-label" data-role="fieldcontain">
	<LABEL FOR="SQLfile" class="ui-hidden-accessible">SQL file</LABEL>
	<SELECT ID="SQLfile" NAME="SQLfile" SIZE="1">
	<OPTION VALUE="">- select -</OPTION>
<?php
	$SQLfiles = scandir($StoreRoot ."/servis/qry/");
	foreach ( $SQLfiles as $SQLfile )
		if ( is_file($StoreRoot ."/servis/qry/". $SQLfile) && right( $SQLfile, 4 ) == ".sql" )
			echo "\t<OPTION VALUE=\"$SQLfile\">$SQLfile</OPTION>\n";
?>
	</SELECT>
	<label for="SQL" class="ui-hidden-accessible">SQL statement(s)</LABEL>
	<TEXTAREA ID="SQL" NAME="SQL" ROWS="6" data-theme="d"><?php if (isset( $SQLdata )) echo $SQLdata; ?></TEXTAREA>
</fieldset>
<INPUT TYPE="submit" VALUE="Izvedi" data-iconpos="left" data-icon="gear" data-theme="a">
</FORM>
<?php
}

echo "\t</div>\n";
echo "</div>\n"; // page
?>
