<?php
/*~ edit_Uporabniki.php - Edit users. Add update user info and add/remove user from groups.
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

$User = $db->get_row("SELECT * FROM SMUser WHERE UserID = ". (int)$_GET['ID']);
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
		$(this).ajaxSubmit({
			target: '#divEdit', // target element(s) to be updated with server response
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (empty(fObj.Name))		{alert("Please enter full name!"); fObj.Name.focus(); return false;}
				if (!emailOK(fObj.Email))	{alert("Inavlid email!"); fObj.Email.focus(); return false;}
				if (empty(fObj.Username))	{alert("Please enter username!"); fObj.Username.focus(); return false;}
				<?php if ( (int)$_GET['ID'] == 0 ) { ?>if (empty(fObj.Password))	{alert("Please enter password!"); fObj.Password.focus(); return false;}<?php } ?>
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

<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0">
<TR>
	<TD VALIGN="top">

	<FIELDSET ID="fldData" style="width:400px;">
	<LEGEND ID="lgdData">Basic&nbsp;information</LEGEND>
	<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?><?php if ( isset($_GET['ID']) && $_GET['ID'] > 0 ) echo "&ID=".$_GET['ID'] ?>" METHOD="post">
	<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
	<TR><TD COLSPAN="2" HEIGHT="10"></TD></TR>
<?php
if ( isset( $Error ) )
	echo "<tr><td colspan=\"2\" height=\"100\"><b><font color=\"red\">Prišlo je do napake!</font><br>Podatki niso vpisani.</b></td></tr>\n";
else {
?>
	<TR>
		<TD ALIGN="right" WIDTH="35%"><B>Full name:</B>&nbsp;</TD>
		<TD><INPUT TYPE="text" NAME="Name" SIZE="43" MAXLENGTH="50" VALUE="<?php if ($User) echo $User->Name ?>" STYLE="width:100%;"></TD>
	</TR>
	<TR>
		<TD ALIGN="right" WIDTH="35%"><B>Email:</B>&nbsp;</TD>
		<TD><INPUT TYPE="text" NAME="Email" SIZE="43" MAXLENGTH="50" VALUE="<?php if ($User) echo $User->Email ?>" STYLE="width:100%;"></TD>
	</TR>
	<TR>
		<TD ALIGN="right" WIDTH="35%"><B>Username:</B>&nbsp;</TD>
		<TD><INPUT TYPE="text" NAME="Username" SIZE="43" MAXLENGTH="255" VALUE="<?php if ($User) echo $User->Username ?>" STYLE="width:100%;"></TD>
<?php if ( $_GET['ID'] == "0" && $AuthDomain != "@") : ?>
		<TD><A HREF="javascript:void(0);" ONCLICK="$('#LDAPsearch').load('vnos.php?Action=<?php echo $Action->Action ?>&Izbor=SelectADUser',{Find: document.Vnos.Username.value});"><IMG SRC="pic/control.find.gif" HEIGHT="14" WIDTH="14" BORDER=0 ALT="AD LookUp" ALIGN="absmiddle"></A></TD>
<?php endif ?>
	</TR>
<?php if ( $_GET['ID'] != "1" ) : ?>
	<TR>
		<TD ALIGN="right" WIDTH="35%"><B>Password:</B>&nbsp;</TD>
		<TD><INPUT TYPE="Password" NAME="Password" SIZE="43" MAXLENGTH="255" VALUE="" STYLE="width:100%;"></TD>
	</TR>
<?php endif ?>
	<TR>
		<TD ALIGN="right" WIDTH="35%">Phone:&nbsp;</TD>
		<TD><INPUT TYPE="text" NAME="Phone" SIZE="43" MAXLENGTH="25" VALUE="<?php if ($User) echo $User->Phone ?>" STYLE="width:100%;"></TD>
	</TR>
	<TR>
		<TD ALIGN="right" WIDTH="35%">Twitter:&nbsp;</TD>
		<TD><INPUT TYPE="text" NAME="TwitterName" SIZE="43" MAXLENGTH="32" VALUE="<?php if ($User) echo $User->TwitterName ?>" STYLE="width:100%;"></TD>
	</TR>
	<TR>
		<TD ALIGN="right" WIDTH="35%">Default group:&nbsp;</TD>
		<TD><SELECT NAME="DefGrp" SIZE="1" STYLE="width:100%;">
			<option value="">
<?php
	$Grupe = $db->get_results("SELECT * FROM SMGroup ORDER BY Name");
	foreach( $Grupe as $Grupa ) {
		echo "\t\t<option value=\"$Grupa->GroupID\"";
		echo (($User && $User->DefGrp == $Grupa->GroupID) || (!$User && $Grupa->GroupID == 1))? " SELECTED" : "";
		echo ">";
		echo $Grupa->Name . "</option>\n";
	}
?>
		</SELECT>
		</TD>
	</TR>
<?php
}
// prevent disabling administrator 
if ( (int)$_GET['ID'] > 1 ) {
?>
	<TR>
		<TD ALIGN="right" WIDTH="35%">Active:&nbsp;</TD>
		<TD><INPUT TYPE="CheckBox" NAME="Active" VALUE="yes" <?php if ( $User && $User->Active ) echo "CHECKED " ?>/></TD>
	</TR>
<?php
} else {
?>
	<INPUT TYPE="Hidden" NAME="Active" VALUE="yes">
<?php
}
?>
	<TR>
		<TD ALIGN="right" WIDTH="35%">Last login:&nbsp;</TD>
		<TD><INPUT TYPE="Text" VALUE="<?php if ( $User && $User->LastLogon != "" ) echo date( "j.n.Y H:i:s", sqldate2time( $User->LastLogon ) ) ?>" DISABLED READONLY></TD>
	</TR>
<?php if ( contains($ActionACL,"W") ) : ?>
	<TR>
		<TD ALIGN="right" COLSPAN="2" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="submit" VALUE=" Save " CLASS="but"></TD>
	</TR>
<?php endif ?>
	</TABLE>
	</FORM>
	</FIELDSET>

<?php
/*
 only administrator can change groups he belongs to,
 for other users anyone with access to this script can
*/

if ( (int)$_GET['ID'] > 1 || ($_SESSION['UserID'] == 1 && (int)$_GET['ID'] == 1) ) {
	$Members = $db->get_results( "
		SELECT
			G.GroupID,
			G.Name
		FROM
			SMGroup G
			LEFT JOIN SMUserGroups UG
				ON G.GroupID = UG.GroupID AND UG.UserID = " . (int)$_GET['ID'] . "
		WHERE
			UG.GroupID IS NOT NULL
			AND G.GroupID > " . (strpos($ActionACL,"D")!==false ? "0" : "1") . "
		ORDER BY G.Name" );

	$NonMembers = $db->get_results( "
		SELECT
			G.GroupID,
			G.Name
		FROM
			SMGroup G
			LEFT JOIN SMUserGroups UG
				ON G.GroupID = UG.GroupID AND UG.UserID = " . (int)$_GET['ID'] . "
		WHERE
			UG.GroupID IS NULL
			" . (strpos($ActionACL,"D")!==false ? "" : "AND G.GroupID > 1") . "
		ORDER BY G.Name" );
?>
	<FIELDSET ID="fldGroups" style="width:400px;">
	<LEGEND ID="lgdGroups">Groups</LEGEND>
	<FORM NAME="Grupe" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
		<INPUT Name="UserID" Type="HIDDEN" VALUE="<?php echo $User->UserID ?>">
		<INPUT Name="GroupList" Type="HIDDEN" VALUE="">
		<INPUT Name="Action" Type="HIDDEN" VALUE="">
	<TABLE ALIGN="center" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
	<TR>
		<TD ALIGN="right" WIDTH="45%">Not a member:</TD>
		<TD ALIGN="center" WIDTH="10%"></TD>
		<TD ALIGN="right" WIDTH="45%">Is a member:</TD>
	</TR>
	<TR>
		<TD ALIGN="left">
		<SELECT NAME="NonUser" MULTIPLE SIZE="10" STYLE="width:100%;">
<?php
	if ( count($NonMembers) > 0 )
		foreach ( $NonMembers as $NonMember )
			echo "\t<OPTION VALUE=\"$NonMember->GroupID\">$NonMember->Name</OPTION>\n";
?>
		</SELECT>
		</TD>
		<TD ALIGN="center">
		<IMG ID="Add" SRC="pic/icon.arrow_right.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon"><BR><BR>
		<IMG ID="Remove" SRC="pic/icon.arrow_left.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon">
		</TD>
		<TD ALIGN="right">
		<SELECT NAME="User" MULTIPLE SIZE="10" STYLE="width:100%;">
<?php
/*
 disable removing groups 1 (everyone) for anyone and 2 (administrators) for administrator
 this is achived by removing VALUE property from a OPTION tag
*/
	if ( count($Members) > 0 )
		foreach ( $Members as $Member )
			echo "\t<OPTION VALUE=\"" . (!($Member->GroupID==1 || ($Member->GroupID==2 && $_GET['ID']==1)) ? "$Member->GroupID" : "" ) . "\">$Member->Name</OPTION>\n";
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
	<TD VALIGN="top"><DIV ID="LDAPsearch"></DIV></TD>

</TR>
</TABLE>
