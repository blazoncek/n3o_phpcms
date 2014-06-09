<?php
/*
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

if ( !isset($_GET['ForumID']) ) $_GET['ForumID'] = "0";

$BaseModerator = $db->get_var( "SELECT Moderator FROM frmForums WHERE ID = " . (int)$_GET['ForumID'] );
if ( !$BaseModerator )
	$BaseModerator = 1;

// add users to permissions list
if ( isset( $_POST['UserList'] ) && $_POST['UserList'] !== "" && isset( $_POST['Action'] ) ) {
	$db->query( "START TRANSACTION" );
	if ( $_POST['Action'] == "Add" )
		foreach ( explode( ",", $_POST['UserList'] ) as $UserID ) {
			$db->query( "INSERT INTO frmModerators (ForumID, MemberID, Permissions) VALUES (".(int)$_GET['ForumID'].", $UserID, 1)" );
		}
	if ( $_POST['Action'] == "Remove" )
		$db->query( "DELETE FROM frmModerators WHERE ACLID = ".(int)$_GET['ForumID']." AND MemberID IN (".$_POST['UserList'].")" );
	$db->query( "COMMIT" );
}

if ( isset($_POST['UserID']) ) {
	$ACL = 0;
	if ( $_POST['List']=="true" )   $ACL += 1;
	if ( $_POST['Read']=="true" )   $ACL += 2;
	if ( $_POST['Write']=="true" )  $ACL += 4;
	if ( $_POST['Delete']=="true" ) $ACL += 8;
	if ( $_POST['UserID'] == $BaseModerator ) $ACL = 15;	// disable removing base moderator
	//if ( $_POST['Execute']) ) $ACL += 16;	// not yet implemented
	$db->query( "UPDATE frmModerators SET Permissions = $ACL WHERE ForumID = ".(int)$_GET['ForumID']." AND MemberID = ".(int)$_POST['UserID'] );
}

?>
<SCRIPT Language="JAVASCRIPT">
<!--//
function setList(list_obj, select_obj) {
	var count = 0;

	list_obj.value = "";			
    for (i=0; i < select_obj.length; i++) {
		if (select_obj.options[i].selected && select_obj.options[i].value != "") {
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
	// setup group manipulation
	$("#Add").click(function(){
		UserList = document.getElementsByName("UserList");
		NonGroup = document.getElementsByName("NonGroup");
		setList(UserList[0],NonGroup[0]);
		$("form[name='Moderators'] :hidden[name='Action']").val('Add');
		$("form[name='Moderators']").ajaxSubmit({target: '#divModeratorji'});
	});
	$("#Remove").click(function(){
		UserList = document.getElementsByName("UserList");
		Group    = document.getElementsByName("Group");
		setList(GroupList[0],Group[0]);
		$("form[name='Moderators'] :hidden[name='Action']").val('Remove');
		$("form[name='Moderators']").ajaxSubmit({target: '#divModeratorji'});
	});
	// add click events for ACLs
	$("input:checkbox[name='ACL']").click(function(){
		var options = {};
		var UserOff  = this.value.indexOf(":")+1;
		options.UserID  = this.value.substr(UserOff);
		$("input:checkbox[name='ACL']").each(function(){
			if ( this.value.substr(this.value.indexOf(":")+1) != options.UserID ) return;
			if ( this.value.substr(0,1)=="L" ) options.List    = this.checked;
			if ( this.value.substr(0,1)=="R" ) options.Read    = this.checked;
			if ( this.value.substr(0,1)=="W" ) options.Write   = this.checked;
			if ( this.value.substr(0,1)=="D" ) options.Delete  = this.checked;
		});
		$("#divModeratorji").load('<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>',options);
	});
});
//-->
</SCRIPT>

<?php
if ( (int)$_GET['ForumID'] > 0 ) {
	// users
	$Members = $db->get_results(
		"SELECT
			M.ID, M.NickName
		FROM
			frmMembers M
			LEFT JOIN frmModerators MO
				ON M.ID = MO.MemberID AND MO.ForumID = " . (int)$_GET['ForumID'] . "
		WHERE
			MO.MemberID IS NOT NULL
		ORDER BY
			M.NickName"
		);

	$NonMembers = $db->get_results(
		"SELECT
			M.ID, M.NickName
		FROM
			frmMembers M
			LEFT JOIN frmModerators MO
				ON M.ID = MO.MemberID AND MO.ForumID = " . (int)$_GET['ForumID'] . "
		WHERE
			MO.MemberID IS NULL AND
			M.AccessLevel > 1
		ORDER BY
			M.NickName"
		);
?>

<FORM NAME="Moderators" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD ALIGN="right" class="f10" WIDTH="45%">Niso člani:</TD>
	<TD ALIGN="center" class="f10" WIDTH="10%"></TD>
	<TD ALIGN="right" class="f10" WIDTH="45%">So člani:</TD>
</TR>
<TR>
	<TD ALIGN="left">
	<INPUT Name="UserList" Type="HIDDEN" VALUE="">
	<INPUT Name="Action" Type="HIDDEN" VALUE="">
	<SELECT NAME="NonGroup" MULTIPLE SIZE="5" STYLE="width:100%;">
<?php
if ( count( $NonMembers ) > 0 )
	foreach ( $NonMembers as $NonMember )
		echo "\t\t<OPTION VALUE=\"$NonMember->ID\">$NonMember->NickName</OPTION>\n";
?>
	</SELECT>
	</TD>
	<TD ALIGN="center">
	<IMG ID="Add" SRC="pic/icon.arrow_right.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon"><BR><BR>
	<IMG ID="Remove" SRC="pic/icon.arrow_left.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon">
	</TD>
	<TD ALIGN="right">
	<SELECT NAME="Group" MULTIPLE SIZE="5" STYLE="width:100%;">
<?php
/* 
 disable removing default moderator
 do this by removing VALUE property of OPTION
*/
if ( count( $Members ) > 0 )
	foreach ( $Members as $Member )
		echo "\t\t<OPTION VALUE=\"" . (($Member->ID != $BaseModerator) ? "$Member->ID" : "" ) . "\">$Member->NickName</OPTION>\n";
?>
	</SELECT>
	</TD>
</TR>
</TABLE>
</FORM>

<!-- ACLs -->
<?php
$Members = $db->get_results(
	"SELECT
		M.ID,
		M.NickName,
		M.Name,
		MO.Permissions
	FROM
		frmModerators MO
		LEFT JOIN frmMembers M ON MO.MemberID = M.ID
	WHERE
		ForumID = " . (int)$_GET['ForumID'] . "
	ORDER BY
		NickName, Name"
);
?>
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%" STYLE="border:inset 1px;">
<TR>
	<TD>
	<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
	<TR>
		<TD WIDTH="60%" class="f10" STYLE="color: white;background-color: #6699CC;border-bottom:silver solid 1px;">Permissions</TD>
		<TD ALIGN="center" class="f10" STYLE="color: white;background-color: #6699CC;border-bottom:silver solid 1px;">Z/O</TD>
		<TD ALIGN="center" class="f10" STYLE="color: white;background-color: #6699CC;border-bottom:silver solid 1px;">Pr</TD>
		<TD ALIGN="center" class="f10" STYLE="color: white;background-color: #6699CC;border-bottom:silver solid 1px;">Ur</TD>
		<TD ALIGN="center" class="f10" STYLE="color: white;background-color: #6699CC;border-bottom:silver solid 1px;">Br</TD>
	</TR>
	<?php
	if ( $Members ) foreach ( $Members as $Member ) {
		echo "<TR>\n";
		echo "<TD>";
		echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=frmMembers&ID=$Member->ID')\">";
		echo $Member->NickName;
		echo "</A>";
		echo "&nbsp;</TD>\n";
		echo "<TD ALIGN=\"center\"><INPUT TYPE=\"Checkbox\" NAME=\"ACL\" VALUE=\"L:$Member->ID\" ".(($Member->Permissions%2)? "CHECKED": "")." STYLE=\"border:none;width:12px;height:12px;\"></TD>\n";
		echo "<TD ALIGN=\"center\"><INPUT TYPE=\"Checkbox\" NAME=\"ACL\" VALUE=\"R:$Member->ID\" ".((($Member->Permissions/2)%2)? "CHECKED": "")." STYLE=\"border:none;width:12px;height:12px;\"></TD>\n";
		echo "<TD ALIGN=\"center\"><INPUT TYPE=\"Checkbox\" NAME=\"ACL\" VALUE=\"W:$Member->ID\" ".((($Member->Permissions/4)%2)? "CHECKED": "")." STYLE=\"border:none;width:12px;height:12px;\"></TD>\n";
		echo "<TD ALIGN=\"center\"><INPUT TYPE=\"Checkbox\" NAME=\"ACL\" VALUE=\"D:$Member->ID\" ".((($Member->Permissions/8)%2)? "CHECKED": "")." STYLE=\"border:none;width:12px;height:12px;\"></TD>\n";
		echo "</TR>\n";
	}
	?>
	</TABLE>
	</TD>
</TR>
</TABLE>
<?php
}
?>
