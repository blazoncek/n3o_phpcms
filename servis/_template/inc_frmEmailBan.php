<?php
/*
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend and backend)                                 |
|   Version: 2.2.2                                                          |
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

if ( isset($_POST['addEmail']) && $_POST['addEmail']!='' )
	$db->query("INSERT INTO frmBanList (Email) VALUES ('". $db->escape($_POST['addEmail']) ."')");

if ( isset($_POST['Brisi']) && $_POST['Brisi']!='' )
	$db->query("DELETE FROM frmBanList WHERE ID = ". (int)$_POST['Brisi']);

$BanList = $db->get_results(
	"SELECT
		ID,
		Email
	FROM
		frmBanList
	WHERE
		Email IS NOT NULL"
);
?>
<script language="JavaScript" type="text/javascript">
<!-- //
function customResize () {
	// vertically resize edit child divs
	edit = $("#divContent").height(0).height( $("#divEdit").height() + $("#divEdit").position().top - $("#divContent").position().top );
	// fix scroller problem when resizing
	if ( $("#divEmails").text() ) $("#divEmails").height(0);
	// actualy resize
	if ( $("#divEmails").text() ) $("#divEmails").height( edit.height() + edit.position().top - $("#divEmails").position().top - 16 );
}

$(document).ready(function(){
	window.customResize = customResize;

	// bind to the form's submit event
	$("form[name='Vnos']").each(function(){
		$(this).submit(function(){
			// inside event callbacks 'this' is the DOM element so we first
			// wrap it in a jQuery object and then invoke ajaxSubmit
			$(this).ajaxSubmit({
				target: '#divEdit', // target element(s) to be updated with server response
				beforeSubmit: function( formDataArr, jqObj, options ) {
					var fObj = jqObj[0];	// form object
					return true;
				}
			});
			return false;
		});
	});
	
	// resize content div
	window.customResize();

	// refresh list
	listRefresh();
});
//-->
</script>

<DIV CLASS="subtitle">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
	<td><div id="ToggleFrame" style="display:none;">&nbsp;<A HREF="javascript:toggleFrame()"><img src="pic/control.frame.gif" height="14" width="14" alt="Preklop celo/zmanj�ano okno" border="0" align="absmiddle" class="icon">&nbsp;List</a></div></td>
	<td id="editNote" align="right"><B>Blocked email addresses</B>&nbsp;&nbsp;</td>
</tr>
</table>
</DIV>
<div id="divContent" style="padding: 5px; overflow: auto;">
<FIELDSET ID="fldData" style="width:320px;">
<LEGEND ID="lgdData">
	Basic&nbsp;information</LEGEND>
	<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
	<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="2" WIDTH="100%">
	<TR>
		<TD ALIGN="right"><B>Email:</B>&nbsp;</TD>
		<TD><INPUT TYPE="Text" NAME="addEmail" MAXLENGTH="15" VALUE="" CLASS="txt" STYLE="width:100%;"></TD>
		<TD ALIGN="right"><INPUT TYPE="Submit" VALUE="Add" TABINDEX="1" CLASS="but"></TD>
	</TR>
	<TR>
		<TD CLASS="f10" COLSPAN="3">e.g.: *@hotmail.com, name@domain.com</TD>
	</TR>
	</TABLE>
	</FORM>
</FIELDSET>
<div id="divEmails" class="frame" style="margin:5px;overflow:auto;width:328px;">
<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="2" WIDTH="100%">
<?php
if ( $BanList ) foreach ( $BanList as $Item ) {
	$Title = $Item->Email;
	echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
	echo "<TD VALIGN=\"top\">$Title</TD>\n";
	echo "<TD ALIGN=\"right\" WIDTH=\"20\">";
	echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divEdit').load('inc.php?Izbor=frmEmailBan',{Brisi:$Item->ID});\" TITLE=\"Delete\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=\"11\" HEIGHT=\"11\" ALT=\"Briši\" BORDER=\"0\" ALIGN=\"absmiddle\" CLASS=\"icon\"></A>";
	echo "</TD>\n";
	echo "</TR>\n";
}
?>
</TABLE>
</div>
</div>
