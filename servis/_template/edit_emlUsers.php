<?php
/*~ edit_emlUporabniki.php - Edit mail subscribers. Add update user info and add/remove user from groups.
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

$User = $db->get_row("SELECT * FROM emlMembers WHERE emlMemberID = ". (int)$_GET['ID']);

?>
<script language="JavaScript" type="text/javascript">
<!-- //
function setAction( form_obj, action_str ) {
	form_obj.Action.value = action_str;
}

function setList( list_obj, select_obj ) {
	var count = 0;

	list_obj.value = "";			
    for (i=0; i < select_obj.length; i++) {
		if (select_obj.options[i].selected && select_obj.options[i].value!="") {
			var startPosition = 0;
			var indexPosition = 0;
			var selectString;

			if (count > 0 ) { list_obj.value += ","; }

			selectString = select_obj.options[i].value;
			indexPosition = selectString.indexOf(",");

			for (; indexPosition > 0; indexPosition = selectString.indexOf(",", startPosition)) {
				list_obj.value += selectString.substring(startPosition, indexPosition);
				list_obj.value += "~";	
				startPosition = indexPosition + 1;
			}					

			list_obj.value += selectString.substring(startPosition, selectString.length);
			count++;
		}
	}
}

$(document).ready(function(){
	// bind to the form's submit event
	$("form[name='Vnos']").submit(function(){
		// inside event callbacks 'this' is the DOM element so we first
		// wrap it in a jQuery object and then invoke ajaxSubmit
		$(this).ajaxSubmit({
			target: '#divEdit', // target element(s) to be updated with server response
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (empty(fObj.Naziv))		{alert("Vnesite ime in priimek uporabnika!"); fObj.Naziv.focus(); return false;}
				if (!emailOK(fObj.Email))	{alert("Nepravilen email naslov!"); fObj.Email.focus(); return false;}
				$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
				return true;
			}
		});
		return false;
	});
	$("form[name='Grupe']").submit(function(){
		$(this).ajaxSubmit({target: '#divEdit'});
		return false;
	});
	$("#Add").click(function(){
		GroupList = document.getElementsByName("GroupList");
		NonUser = document.getElementsByName("NonUser");
		setList(GroupList[0],NonUser[0]);
		$("form[name='Grupe'] :hidden[name='Action']").val('Add');
		$("form[name='Grupe']").ajaxSubmit({target: '#divEdit'});
	});
	$("#Remove").click(function(){
		GroupList = document.getElementsByName("GroupList");
		User = document.getElementsByName("User");
		setList(GroupList[0],User[0]);
		$("form[name='Grupe'] :hidden[name='Action']").val('Remove');
		$("form[name='Grupe']").ajaxSubmit({target: '#divEdit'});
	});
	// refresh list
	listRefresh();
});
//-->
</script>

<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD VALIGN="top" WIDTH="50%">
	<FIELDSET ID="fldData">
		<LEGEND ID="lgdData">Basic&nbsp;information</LEGEND>
		<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?><?php if ( isset($_GET['ID']) && $_GET['ID'] > 0 ) echo "&ID=".$_GET['ID'] ?>" METHOD="post">
		<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
		<TR><TD COLSPAN="2" HEIGHT="10"></TD></TR>
<?php
if ( isset( $Error ) )
	echo "\t\t<tr><td colspan=\"2\" height=\"100\"><b><font color=\"red\">Pri�lo je do napake!</font><br>Podatki niso vpisani.</b></td></tr>\n";
else {
?>
		<TR>
			<TD ALIGN="right"><B>Full name:</B></TD>
			<TD><INPUT TYPE="text" NAME="Naziv" SIZE="30" MAXLENGTH="50" VALUE="<?php if ($User) echo $User->Naziv ?>"></TD>
		</TR>
		<TR>
			<TD ALIGN="right"><B>Email:</B></TD>
			<TD><INPUT TYPE="text" NAME="Email" SIZE="30" MAXLENGTH="255" VALUE="<?php if ($User) echo $User->Email ?>"></TD>
		</TR>
		<TR>
			<TD ALIGN="right">Language:</TD>
			<TD>
			<SELECT NAME="Jezik" SIZE="1">
<?php
		$Jeziki = $db->get_results("SELECT Jezik, Opis FROM Jeziki WHERE Enabled=1");
		echo "\t\t\t<OPTION VALUE=\"\">- all -</OPTION>\n";
		if ( $Jeziki ) foreach ( $Jeziki as $Jezik )
			echo "\t\t\t<OPTION VALUE=\"$Jezik->Jezik\"".($User && $User->Jezik == $Jezik->Jezik ? " SELECTED" : "").">$Jezik->Opis</OPTION>\n";
?>
			</SELECT>
			</TD>
		</TR>
		<TR>
			<TD ALIGN="right">Company</TD>
			<TD><INPUT TYPE="text" NAME="Podjetje" SIZE="30" MAXLENGTH="50" VALUE="<?php if ($User) echo $User->Podjetje ?>"></TD>
		</TR>
		<TR>
			<TD ALIGN="right">Address:</TD>
			<TD><INPUT TYPE="text" NAME="Naslov" SIZE="30" MAXLENGTH="50" VALUE="<?php if ($User) echo $User->Naslov ?>"></TD>
		</TR>
		<TR>
			<TD ALIGN="right">Postal code/Town:</TD>
			<TD><INPUT TYPE="text" NAME="Posta" SIZE="30" MAXLENGTH="50" VALUE="<?php if ($User) echo $User->Posta ?>"></TD>
		</TR>
		<TR>
			<TD ALIGN="right">Phone:</TD>
			<TD><INPUT TYPE="text" NAME="Telefon" SIZE="15" MAXLENGTH="20" VALUE="<?php if ($User) echo $User->Telefon ?>"></TD>
		</TR>
		<TR>
			<TD ALIGN="right">Fax:</TD>
			<TD><INPUT TYPE="text" NAME="Fax" SIZE="15" MAXLENGTH="20" VALUE="<?php if ($User) echo $User->Fax ?>"></TD>
		</TR>
		<TR>
			<TD ALIGN="right">Mobile</TD>
			<TD><INPUT TYPE="text" NAME="GSM" SIZE="15" MAXLENGTH="20" VALUE="<?php if ($User) echo $User->GSM ?>"></TD>
		</TR>
		<TR>
			<TD ALIGN="right">Active:&nbsp;</TD>
			<TD><INPUT TYPE="CheckBox" NAME="Aktiven" VALUE="yes" <?php if ( $User && $User->Aktiven ) echo "CHECKED " ?>/></TD>
		</TR>
<?php if ( contains($ActionACL,"W") ) : ?>
		<TR>
			<TD ALIGN="right" COLSPAN="2" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="submit" VALUE=" Save " CLASS="but"></TD>
		</TR>
<?php endif ?>
<?php
}
?>
		</TABLE>
		</FORM>
	</FIELDSET>
	</TD>

	<TD VALIGN="top" WIDTH="50%">
<?php
if ( (int)$_GET['ID'] > 0 && contains($ActionACL,"W") ) {
	$Members = $db->get_results(
		"SELECT
			G.emlGroupID AS GroupID,
			G.Naziv AS Name
		FROM
			emlGroups G
			LEFT JOIN emlMembersGrp UG ON G.emlGroupID = UG.emlGroupID
		WHERE
			UG.emlGroupID IS NOT NULL
			AND UG.emlMemberID = ". (int)$_GET['ID'] ."
		ORDER BY G.Naziv"
	);
	$NonMembers = $db->get_results(
		"SELECT
			G.emlGroupID AS GroupID,
			G.Naziv AS Name
		FROM
			emlGroups G
			LEFT JOIN emlMembersGrp UG ON G.emlGroupID = UG.emlGroupID AND UG.emlMemberID = ". (int)$_GET['ID'] ."
		WHERE
			UG.emlGroupID IS NULL
		ORDER BY G.Naziv"
	);
?>
	<FIELDSET ID="fldGroups">
		<LEGEND ID="lgdGroups">Groups</LEGEND>
		<FORM NAME="Grupe" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
			<INPUT Name="UserID" Type="HIDDEN" VALUE="<?php echo $User->emlMemberID ?>">
			<INPUT Name="GroupList" Type="HIDDEN" VALUE="">
			<INPUT Name="Action" Type="HIDDEN" VALUE="">
		<TABLE ALIGN="center" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
		<TR>
			<TD ALIGN="right" WIDTH="45%">Not a member:</TD>
			<TD ALIGN="center" WIDTH="10%"></TD>
			<TD ALIGN="right" WIDTH="45%">Member:</TD>
		</TR>
		<TR>
			<TD ALIGN="left">
			<SELECT NAME="NonUser" MULTIPLE SIZE="15" STYLE="width:100%;">
<?php
	if ( count($NonMembers) > 0 )
		foreach ( $NonMembers as $NonMember )
			echo "\t\t\t<OPTION VALUE=\"$NonMember->GroupID\">$NonMember->Name</OPTION>\n";
?>
			</SELECT>
			</TD>
			<TD ALIGN="center">
			<IMG ID="Add" SRC="pic/icon.arrow_right.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon"><BR><BR>
			<IMG ID="Remove" SRC="pic/icon.arrow_left.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon">
			</TD>
			<TD ALIGN="right">
			<SELECT NAME="User" MULTIPLE SIZE="15" STYLE="width:100%;">
<?php
	if ( count($Members) > 0 )
		foreach ( $Members as $Member )
			echo "\t\t\t<OPTION VALUE=\"$Member->GroupID\">$Member->Name</OPTION>\n";
?>
			</SELECT>
			</TD>
		</TR>
		</TABLE>
		</FORM>
		</FIELDSET>
<?php
}
?>
	</TD>
</TR>
</TABLE>
