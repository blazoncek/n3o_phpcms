<?php
/*~ edit_frmMembers - edit forum member data
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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "0";

// get data
$Podatek = $db->get_row("SELECT * FROM frmMembers WHERE ID = ". (int)$_GET['ID']);

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
				if ( empty(fObj.Vzdevek) ) { alert("Please enter nickname!"); fObj.Vzdevek.focus(); return false; }
				if ( empty(fObj.Email) ) { alert("Please enter email!"); fObj.Email.focus(); return false; }
				if ( empty(fObj.Ime) ) { alert("Please enter name!"); fObj.Ime.focus(); return false; }
				$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
				return true;
			}
		});
		return false;
	});
	// refresh list
	listRefresh();
	// load subdata
	if ( $("#divTeme").text() ) $("#divTeme").load('inc.php?Izbor=frmNotify&ID=<?php echo $_GET['ID'] ?>');
	if ( $("#divNiti").text() ) $("#divNiti").load('inc.php?Izbor=frmForums&ID=<?php echo $_GET['ID'] ?>');
});
//-->
</script>
<?php if ( $_GET['ID'] == "0" ) : ?>
<DIV ALIGN="center"><BR><BR><BR><B>Adding forum members not possible!</B></DIV>
<?php else : ?>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD VALIGN="top" WIDTH="65%">
	<FIELDSET ID="fldData">
	<LEGEND ID="lgdData">Basic&nbsp;information</LEGEND>
	<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post" ENCTYPE="multipart/form-data">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="100%">
	<TR>
		<TD ALIGN="right"><FONT COLOR="Red"><B>Active:</B></FONT>&nbsp;</TD>
		<TD><INPUT TYPE="Checkbox" NAME="Enabled"<?php echo ($Podatek && $Podatek->Enabled) ? " CHECKED" : "" ?>></TD>
		<TD ALIGN="right">Member of:&nbsp;</TD>
		<TD><INPUT TYPE="Checkbox" DISABLED <?php echo ($Podatek && $Podatek->MailList) ? " CHECKED" : "" ?>></TD>
	</TR>
	<TR>
		<TD ALIGN="right"><B>Nickname:</B>&nbsp;</TD>
		<TD><INPUT TYPE="Text" NAME="Vzdevek" VALUE="<?php echo ($Podatek) ? $Podatek->Nickname : "" ?>" MAXLENGTH="16" CLASS="txt" STYLE="width:100%;"></TD>
		<TD ALIGN="right">Donator:&nbsp;</TD>
		<TD><INPUT TYPE="Checkbox" NAME="Patron"<?php echo ($Podatek && $Podatek->Patron) ? " CHECKED" : "" ?>></TD>
	</TR>
	<TR>
		<TD ALIGN="right"><B>Email:</B>&nbsp;</TD>
		<TD><INPUT TYPE="Text" NAME="Email" VALUE="<?php echo ($Podatek)? $Podatek->Email: "" ?>" MAXLENGTH="64" CLASS="txt" STYLE="width:100%;"></TD>
		<TD ALIGN="right">Show email:&nbsp;</TD>
		<TD><INPUT TYPE="Checkbox" NAME="ShowEmail" <?php echo ($Podatek && $Podatek->ShowEmail) ? " CHECKED" : "" ?>></TD>
	</TR>
	<TR>
		<TD ALIGN="right"><B>Full name:</B>&nbsp;</TD>
		<TD><INPUT TYPE="Text" NAME="Ime" VALUE="<?php echo ($Podatek) ? $Podatek->Name : "" ?>" MAXLENGTH="64" CLASS="txt" STYLE="width:100%;"></TD>
		<TD ALIGN="right">Sex:&nbsp;</TD>
		<TD><B><?php echo ($Podatek) ? $Podatek->Sex : "" ?></B></TD>
	</TR>
	<TR>
		<TD ALIGN="right">Address:&nbsp;</TD>
		<TD><INPUT TYPE="Text" NAME="Address" VALUE="<?php echo ($Podatek)? $Podatek->Address: "" ?>" MAXLENGTH="64" CLASS="txt" STYLE="width:100%;"></TD>
		<TD COLSPAN="2" ROWSPAN="2">
		</TD>
	</TR>
	<TR>
		<TD ALIGN="right">Phone:&nbsp;</TD>
		<TD COLSPAN="3"><INPUT TYPE="Text" NAME="Phone" VALUE="<?php echo ($Podatek)? $Podatek->Phone: "" ?>" MAXLENGTH="24" CLASS="txt" STYLE="width:100%;"></TD>
	</TR>
	<TR>
		<TD ALIGN="right" VALIGN="baseline"><B>Password:</B>&nbsp;</TD>
		<TD COLSPAN="3">
		<INPUT TYPE="Checkbox" NAME="NewPwd"> Assign new password.</TD>
	</TR>
	<TR>
		<TD ALIGN="right">Signed-up:&nbsp;</TD>
		<TD><B><?php echo ($Podatek)? date('j.n.y',sqldate2time($Podatek->SignIn)): "" ?></B></TD>
		<TD ALIGN="right" COLSPAN="2">Obisk:&nbsp;<B><?php echo ($Podatek && $Podatek->LastVisit)? date('j.n.y',sqldate2time($Podatek->LastVisit)): "<i>nikoli</i>" ?></B></TD>
	</TR>
	<TR>
		<TD ALIGN="right" VALIGN="top">Signature:&nbsp;</TD>
		<TD COLSPAN="3"><TEXTAREA NAME="Signature" ROWS="4" CLASS="txt" STYLE="width:100%;"><?php echo ($Podatek)? $Podatek->Signature: "" ?></TEXTAREA></TD>
	</TR>
	<TR>
		<TD ALIGN="right"><B>Status:</B>&nbsp;</TD>
		<TD COLSPAN="2"><SELECT NAME="AccessLevel" SIZE="1">
			<OPTION VALUE="1" <?php echo ($Podatek && $Podatek->AccessLevel==1)? "SELECTED": "" ?>>User
			<OPTION VALUE="2" <?php echo ($Podatek && $Podatek->AccessLevel==2)? "SELECTED": "" ?>>Apprentice moderator
			<OPTION VALUE="3" <?php echo ($Podatek && $Podatek->AccessLevel==3)? "SELECTED": "" ?>>Moderator
			<OPTION VALUE="4" <?php echo ($Podatek && $Podatek->AccessLevel==4)? "SELECTED": "" ?>>Category administrator
			<OPTION VALUE="5" <?php echo ($Podatek && $Podatek->AccessLevel==5)? "SELECTED": "" ?>>Forum administrator
		</SELECT></TD>
	</TR>
	<TR>
		<TD ALIGN="right">Last IP:&nbsp;</TD>
		<TD><B><?php echo ($Podatek) ? $Podatek->LastIPAddress : "" ?></B></TD>
	</TR>
	<TR>
		<TD STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><A HREF="javascript:void(0);" ONCLICK="loadTo('Edit','inc.php?Action=<?php echo $_GET['Action'] ?>&Izbor=frmEmail&ID=<?php echo $_GET['ID'] ?>');">Send message</A>&nbsp;</TD>
		<TD ALIGN="right" COLSPAN="3" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="Submit" NAME="what" VALUE=" Save " CLASS="but"></TD>
	</TR>
	</FORM>
	</TABLE>
	</FIELDSET>
	</TD>
	
	<TD VALIGN="top" WIDTH="35%">
	<FIELDSET>
	<LEGEND>Moderator of</LEGEND>
		<DIV ID="divNiti" STYLE="overflow:auto;height:156px;"> </DIV>
	</FIELDSET>
	<FIELDSET>
	<LEGEND>Subscribed topics</LEGEND>
		<DIV ID="divTeme" STYLE="overflow:auto;height:156px;"> </DIV>
	</FIELDSET>
	</TD>
</TR>
</TABLE>
<?php endif ?>
