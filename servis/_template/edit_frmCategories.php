<?php
/*~ edit_frmCategories.php - Edit forum forum categories
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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "0";

// get data
$Podatek = $db->get_row("SELECT * FROM frmCategories WHERE ID = ". (int)$_GET['ID']);

// get available moderators
$Moderators = $db->get_results(
	"SELECT ID, Name, Nickname
	FROM frmMembers
	WHERE AccessLevel > 3
	ORDER BY AccessLevel DESC, Nickname"
);
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
				if (empty(fObj.Name))	{alert("Please enter category name!"); fObj.Name.focus(); return false;}
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

<FIELDSET ID="fldData" style="width:420px;">
<LEGEND ID="lgdData">
	Basic&nbsp;information</LEGEND>
	<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
	<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="1" WIDTH="100%">
	<TR>
		<TD><B>Name:</B>&nbsp;</TD>
		<TD><INPUT TYPE="Text" NAME="Name" MAXLENGTH="50" VALUE="<?php echo ($Podatek ? $Podatek->CategoryName : "") ?>" CLASS="txt" STYLE="width:100%"></TD>
	</TR>
	<TR>
		<TD VALIGN="top">Admin.:&nbsp;</TD>
		<TD COLSPAN="1"><SELECT NAME="Admin" SIZE="1">
			<OPTION VALUE="">- nobody -</OPTION>
<?php
	if ( $Moderators ) foreach ( $Moderators as $Moderator )
		echo "<OPTION VALUE=\"$Moderator->ID\"".(($Moderator->ID==$Podatek->Administrator)? " SELECTED": "").">$Moderator->Nickname ($Moderator->Name)</OPTION>\n";
?>
		</SELECT>
		</TD>
	</TR>
	<TR>
		<TD ALIGN="right" COLSPAN="2" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="Submit" VALUE=" Save " TABINDEX="1" CLASS="but"></TD>
	</TR>
	</FORM>
	</TABLE>
</FIELDSET>
