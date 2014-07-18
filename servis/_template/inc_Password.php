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

if ( isset( $_POST['NewPWD'] ) && $_POST['NewPWD'] != "" ) {
	$User = $db->get_row("SELECT Password FROM SMUser WHERE UserID = ". (int)$_SESSION['UserID']);
	if ( $User->Password == crypt(PWSALT.$_POST['OldPWD'],$User->Password) && $_POST['NewPWD'] == $_POST['ConfPWD'] && strlen(trim($_POST['NewPWD'])) >= 4 ) {
		$db->query(
			"UPDATE SMUser
			SET Password = '" . crypt(PWSALT.$_POST['NewPWD']) . "'
			WHERE UserID = " . (int)$_SESSION['UserID'] );
		//header( "Refresh: 3; URL=about:blank" );
	} else {
		$Error = "Password error!";
	}
}
?>
<div class="subtitle">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
	<td><div id="ToggleFrame" style="display:none;">&nbsp;<A HREF="javascript:toggleFrame()"><img src="pic/control.frame.gif" height="14" width="14" alt="Preklop celo/zmanjšano okno" border="0" align="absmiddle" class="icon">&nbsp;List</a></div></td>
	<td align="right">Change password</td>
</tr>
</table>
</div>
<br>
<div id="divContent" style="padding:0px 10px;">
<?php 
if ( isset( $_POST["NewPWD"] ) && $_POST["NewPWD"] != "" ) {
	if ( isset( $Error ) ) {
		echo "<div class=\"warn\"><B>Password NOT changed!</B></div>\n";
	} else {
		echo "<div><B>Password changed!</B></div>\n";
	}
} else {
?>
<script language="JavaScript" type="text/javascript">
<!-- //
$(document).ready(function(){
	var options = {
		target: '#divEdit', // target element(s) to be updated with server response
		beforeSubmit: function(formDataArr, jqObj, options){ // pre-submit callback
			if (empty(jqObj[0].OldPWD))	{alert("Enter old password!"); jqObj[0].OldPWD.focus(); return false;}
			if (empty(jqObj[0].NewPWD))	{alert("Enter new password!"); jqObj[0].NewPWD.focus(); return false;}
			if (empty(jqObj[0].ConfPWD))	{alert("Enter password confirmation!"); jqObj[0].ConfPWD.focus(); return false;}
			if (jqObj[0].ConfPWD.value != jqObj[0].NewPWD.value)	{alert("Password and confirmation do not match!"); jqObj[0].ConfPWD.focus(); return false;}
			return true;
		}
	};
	// bind to the form's submit event
	$("form[name='Vnos']").submit(function(){
		$(this).ajaxSubmit(options);
		return false;
	});
	// resize view
	toggleFrame(0);
});
//-->
</script>
<FORM name="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $_SERVER['QUERY_STRING']; ?>" METHOD="post">
<TABLE 	BORDER="0" CELLPADDING="1" CELLSPACING="0">
<TR>
	<TD>Old password:&nbsp;</TD>
	<TD><INPUT NAME="OldPWD" TYPE="Password" SIZE="20" MAXLENGTH="16"></TD>
</TR>
<TR>
	<TD><B>New password:</B>&nbsp;</TD>
	<TD><INPUT NAME="NewPWD" TYPE="Password" SIZE="20" MAXLENGTH="16"></TD>
</TR>
<TR>
	<TD ALIGN="Right"><B>Confirm password:</B>&nbsp;</TD>
	<TD><INPUT NAME="ConfPWD" TYPE="Password" SIZE="20" MAXLENGTH="16"></TD>
</TR>
<TR>
	<TD ALIGN="center" COLSPAN="2"><BR><INPUT TYPE="submit" VALUE=" Change " CLASS="but"></TD>
</TR>
</TABLE>
</FORM>
<?php
}
?>
</div>