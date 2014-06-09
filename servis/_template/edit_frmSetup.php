<?php
/*~ edit_frmSetup.php - edit forum parameters
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
$(document).ready(function(){
	// bind to the form's submit event
	$("form[name='Vnos']").submit(function(){
		// inside event callbacks 'this' is the DOM element so we first
		// wrap it in a jQuery object and then invoke ajaxSubmit
		$(this).ajaxSubmit({
			target: '#divEdit', // target element(s) to be updated with server response
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (!empty(fObj.NewName) && empty(fObj.NewValue))	{alert("Prosim vnesite vrednost!"); fObj.NewValue.focus(); return false;}
				$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
				return true;
			}
		});
		return false;
	});
	// refresh list
	listRefresh();
});
//-->
</script>

<FIELDSET ID="fldData" style="margin-top:5px;width:420px;">
<LEGEND ID="lgdData">
	Basic&nbsp;information</LEGEND>
	<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
	<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
	<TR>
		<TD><B>Parameter</B></TD>
		<TD><B>Vrednost</B></TD>
	</TR>
	<TR>
		<TD COLSPAN="2"><HR SIZE="1"></TD>
	</TR>
	<TR>
		<TD ALIGN="right">
		<INPUT NAME="NewName" VALUE="" STYLE="width:100%" MAXLENGTH="16" CLASS="txt">
		</TD>
		<TD>
		<INPUT NAME="NewValue" VALUE="" STYLE="width:100%" MAXLENGTH="128" CLASS="txt">
		</TD>
	</TR>
<?php
$Podatki = $db->get_results( "SELECT * FROM frmParameters" );
if ( $Podatki ) foreach ( $Podatki as $Podatek ) {
	echo "<TR>\n";
	echo "<TD ALIGN=\"right\">";
	echo "<B>$Podatek->ParamName:</B>&nbsp;";
	echo "</TD>\n";
	echo "<TD>";
	echo "<INPUT NAME=\"$Podatek->ParamName\" VALUE=\"$Podatek->ParamValue\" STYLE=\"width:100%\" MAXLENGTH=\"128\" CLASS=\"txt\">";
	echo "</TD>\n";
	echo "</TR>\n";
}
?>
	<TR>
		<TD COLSPAN="2" ALIGN="right" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="Submit" VALUE=" Save " CLASS="but"></TD>
	</TR>
	</TABLE>
</FIELDSET>
