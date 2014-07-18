<?php
/*~ edit_ACL.php - Edit ACLs. Add update ACL info and add/remove ACLs.
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
$Podatek = $db->get_row("SELECT * FROM SMACL WHERE ACLID = ". (int)$_GET['ID']);

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

function strpos (haystack, needle, offset) {
	var i = (haystack+'').indexOf(needle, (offset ? offset : 0));
	return i === -1 ? false : i;
}
function substr (str, start, len) {
	str += '';
	var end = str.length;
	if (start < 0) {
		start += end;
	}
	end = typeof len === 'undefined' ? end : (len < 0 ? len + end : len + start);
	return start >= str.length || start < 0 || start > end ? !1 : str.slice(start, end);
}

function customResize () {
	// vertically resize edit child divs
	edit = $("#divContent").height(0).height( $("#divEdit").height() + $("#divEdit").position().top - $("#divContent").position().top );
	// fix scroller problem when resizing
	if ( $("#divACL").text() ) $("#divSlike").height(0);
	// actualy resize
	if ( $("#divACL").text() ) $("#divACL").height( edit.height() + edit.position().top - $("#divACL").position().top - 20 );
}

$(document).ready(function(){
	window.customResize = customResize;

<?php if ( count($_POST) > 0 ) : ?>
	$('#fldData').css('background-color','#FFCCCC');
	setTimeout("$('#fldData').css('background-color','')",750);
<?php endif ?>

	// bind to the form's submit event
	$("form[name='Vnos']").submit(function(){
		$(this).ajaxSubmit({
			target: '#divEdit', // target element(s) to be updated with server response
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (empty(fObj.Name))	{alert("Please enter ACL name!"); fObj.Name.focus(); return false;}
				return true;
			}
		});
		return false;
	});
	// adjust form submition
	$("form[name!='Vnos']").submit(function(){
		$(this).ajaxSubmit({target: '#editTekst'});
		return false;
	});
	// setup group manipulation
	$("#AddGrp").click(function(){
		GroupList = document.getElementsByName("GroupList");
		NonGroup  = document.getElementsByName("NonGroup");
		setList(GroupList[0],NonGroup[0]);
		$("form[name='Groups'] :hidden[name='Action']").val('Add');
		$("form[name='Groups']").ajaxSubmit({target: '#divEdit'});
	});
	$("#RemoveGrp").click(function(){
		GroupList = document.getElementsByName("GroupList");
		Group     = document.getElementsByName("Group");
		setList(GroupList[0],Group[0]);
		$("form[name='Groups'] :hidden[name='Action']").val('Remove');
		$("form[name='Groups']").ajaxSubmit({target: '#divEdit'});
	});
	// setup user manipulation
	$("#AddUsr").click(function(){
		UserList = document.getElementsByName("UserList");
		NonUser = document.getElementsByName("NonUser");
		setList(UserList[0],NonUser[0]);
		$("form[name='Users'] :hidden[name='Action']").val('Add');
		$("form[name='Users']").ajaxSubmit({target: '#divEdit'});
	});
	$("#RemoveUsr").click(function(){
		UserList = document.getElementsByName("UserList");
		User     = document.getElementsByName("User");
		setList(UserList[0],User[0]);
		$("form[name='Users'] :hidden[name='Action']").val('Remove');
		$("form[name='Users']").ajaxSubmit({target: '#divEdit'});
	});
	// add click events for ACLs
	$("input:checkbox").click(function(){
		var options = {};
		if ( substr(this.value,0,1)=="L" ) options.List    = this.checked;
		if ( substr(this.value,0,1)=="R" ) options.Read    = this.checked;
		if ( substr(this.value,0,1)=="W" ) options.Write   = this.checked;
		if ( substr(this.value,0,1)=="D" ) options.Delete  = this.checked;
		if ( substr(this.value,0,1)=="X" ) options.Execute = this.checked;
		var UserOff  = strpos( this.value, ":" )+1;
		var GroupOff = strpos( this.value, ":", UserOff )+1;
		options.UserID  = substr( this.value, UserOff, GroupOff-UserOff-1 );
		options.GroupID = substr( this.value, GroupOff );
		loadTo('Edit','<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>',options);
	});
	
	// resize content div
	window.customResize();

	// refresh list
	listRefresh();
});
//-->
</script>

<?php if ( $_GET['ID'] == "0" ) : ?>
<DIV ALIGN="center"><BR><BR><BR><B>ACL creation is not possible from this link!</B></DIV>
<?php else : ?>
<DIV STYLE="width:430px;margin-top:10px;padding-left:10px;"><B>ACL name:</B>&nbsp;<SPAN CLASS="red"><?php echo (($Podatek) ? $Podatek->Name : "") ?></SPAN></DIV>
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" width="100%">
<TR>
	<TD width="50%">
<?php
	$Members = $db->get_results(
		"SELECT G.GroupID, G.Name
		FROM SMGroup G
			LEFT JOIN SMACLr Ar ON G.GroupID = Ar.GroupID
		WHERE Ar.GroupID IS NOT NULL
		  AND Ar.ACLID = ".(int)$_GET['ID']."
		ORDER BY G.Name"
	);

	$NonMembers = $db->get_results(
		"SELECT G.GroupID, G.Name
		FROM SMGroup G
			LEFT JOIN SMACLr Ar ON G.GroupID = Ar.GroupID AND Ar.ACLID = ".(int)$_GET['ID']."
		WHERE Ar.GroupID IS NULL
		ORDER BY G.Name"
	);
?>
<FIELDSET ID="fldGroup">
<LEGEND ID="lgdGroup"><B>Groups</B></LEGEND>
<FORM NAME="Groups" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
	<INPUT Name="GroupList" Type="HIDDEN" VALUE="">
	<INPUT Name="Action" Type="HIDDEN" VALUE="">
<TABLE ALIGN="center" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD ALIGN="right" WIDTH="45%">Not included:</TD>
	<TD ALIGN="center" WIDTH="10%"></TD>
	<TD ALIGN="right" WIDTH="45%">Included:</TD>
</TR>
<TR>
	<TD ALIGN="left">
	<SELECT NAME="NonGroup" MULTIPLE SIZE="12" STYLE="width:100%;">
<?php
	if ( count($NonMembers) > 0 )
		foreach ( $NonMembers as $NonMember )
			echo "\t<OPTION VALUE=\"$NonMember->GroupID\">$NonMember->Name</OPTION>\n";
?>
	</SELECT>
	</TD>
	<TD ALIGN="center">
	<IMG ID="AddGrp" SRC="pic/icon.arrow_right.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon"><BR><BR>
	<IMG ID="RemoveGrp" SRC="pic/icon.arrow_left.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon" />
	</TD>
	<TD ALIGN="right">
	<SELECT NAME="Group" MULTIPLE SIZE="12" STYLE="width:100%;">
<?php
/*
 disable removing group 2 (administrators) for administrator ACL
 this is achived by removing VALUE property from a OPTION tag
*/
	if ( count($Members) > 0 )
		foreach ( $Members as $Member )
			echo "\t<OPTION VALUE=\"" . (!($Member->GroupID==2 && $_GET['ID']==1) ? "$Member->GroupID" : "" ) . "\">$Member->Name</OPTION>\n";
?>
	</SELECT>
	</TD>
</TR>
</TABLE>
</FORM>
</FIELDSET>

	</TD>
	<TD width="50%">

<?php
	$Members = $db->get_results(
		"SELECT U.UserID, U.UserName
		FROM SMUser U
			LEFT JOIN SMACLr Ar ON U.UserID = Ar.UserID
		WHERE Ar.UserID IS NOT NULL
		  AND Ar.ACLID = ".(int)$_GET['ID']."
		ORDER BY U.Username"
		);

	$NonMembers = $db->get_results(
		"SELECT U.UserID, U.UserName
		FROM SMUser U
			LEFT JOIN SMACLr Ar ON U.UserID = Ar.UserID AND Ar.ACLID = ".(int)$_GET['ID']."
		WHERE Ar.UserID IS NULL
		ORDER BY U.Username"
		);
?>
<FIELDSET ID="fldUser">
<LEGEND ID="lgdUser"><B>Users</B></LEGEND>
<FORM NAME="Users" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD ALIGN="right" WIDTH="45%">Not included:</TD>
	<TD ALIGN="center" WIDTH="10%"></TD>
	<TD ALIGN="right" WIDTH="45%">Included:</TD>
</TR>
<TR>
	<TD ALIGN="left">
	<INPUT Name="UserList" Type="HIDDEN" VALUE="">
	<INPUT Name="Action" Type="HIDDEN" VALUE="">
	<SELECT NAME="NonUser" MULTIPLE SIZE="12" STYLE="width:100%;">
<?php
if ( count( $NonMembers ) > 0 )
	foreach ( $NonMembers as $NonMember )
		echo "\t\t<OPTION VALUE=\"$NonMember->UserID\">$NonMember->UserName</OPTION>\n";
?>
	</SELECT>
	</TD>
	<TD ALIGN="center">
	<IMG ID="AddUsr" SRC="pic/icon.arrow_right.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon"><BR><BR>
	<IMG ID="RemoveUsr" SRC="pic/icon.arrow_left.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon">
	</TD>
	<TD ALIGN="right">
	<SELECT NAME="User" MULTIPLE SIZE="12" STYLE="width:100%;">
<?php
/* 
 disable removing administrator from administrators ACL
 do this by removing VALUE property of OPTION
*/
if ( count( $Members ) > 0 )
	foreach ( $Members as $Member )
		echo "\t\t<OPTION VALUE=\"" . (!($Member->UserID == 1 && $_GET['ID']==1) ? "$Member->UserID" : "" ) . "\">$Member->UserName</OPTION>\n";
?>
	</SELECT>
	</TD>
</TR>
</TABLE>
</FORM>
</FIELDSET>

	</TD>
</TR>
</TABLE>

<?php
	$Members = $db->get_results(
		"SELECT NULL AS UserID, G.GroupID, G.Name, '*Group*' AS Username, A.MemberACL
		FROM SMACLr A
			INNER JOIN SMGroup G ON A.GroupID = G.GroupID
		WHERE A.ACLID = ".(int)$_GET['ID']."
		
		UNION ALL
		
		SELECT U.UserID, NULL AS GroupID, U.Name, U.Username, A.MemberACL
		FROM SMACLr A
			INNER JOIN SMUser U ON A.UserID = U.UserID
		WHERE A.ACLID = ".(int)$_GET['ID']."
		
		ORDER BY Username, Name"
	);
?>
<FIELDSET ID="fldACL">
<LEGEND ID="lgdACL">Permissions</LEGEND>
<DIV ID="divACL" STYLE="overflow:auto;">
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<THEAD>
<TR>
	<TH WIDTH="70%">&nbsp;</TH>
	<TH ALIGN="center" CLASS="grn">L</TH>
	<TH ALIGN="center" CLASS="grn">R</TH>
	<TH ALIGN="center" CLASS="ylw">W</TH>
	<TH ALIGN="center" CLASS="red">D</TH>
	<TH ALIGN="center" CLASS="blu">X</TH>
</TR>
</THEAD>
<?php
	$BgCol = "";
	foreach ( $Members as $Member ) {
		// row background color
		if ( $BgCol == "whitesmoke" )
			$BgCol="lightgrey";
		else
			$BgCol = "whitesmoke";
		echo "<tr bgcolor=\"$BgCol\">\n";
		echo "<TD WIDTH=\"70%\">&nbsp;".(($Member->Username=="*Group*")? "<B>": "").$Member->Name.(($Member->Username=="*Group*")? "<B>": " (<i>".$Member->Username."</i>)")."&nbsp;</TD>\n";
		echo "<TD ALIGN=\"center\"><INPUT TYPE=\"Checkbox\" VALUE=\"L:".(int)$Member->UserID.":".(int)$Member->GroupID."\" NAME=\"List\" ".(substr($Member->MemberACL,0,1)=="L"? "CHECKED": "")."></TD>\n";
		echo "<TD ALIGN=\"center\"><INPUT TYPE=\"Checkbox\" VALUE=\"R:".(int)$Member->UserID.":".(int)$Member->GroupID."\" NAME=\"Read\" ".(substr($Member->MemberACL,1,1)=="R"? "CHECKED": "")."></TD>\n";
		echo "<TD ALIGN=\"center\"><INPUT TYPE=\"Checkbox\" VALUE=\"W:".(int)$Member->UserID.":".(int)$Member->GroupID."\" NAME=\"Write\" ".(substr($Member->MemberACL,2,1)=="W"? "CHECKED": "")."></TD>\n";
		echo "<TD ALIGN=\"center\"><INPUT TYPE=\"Checkbox\" VALUE=\"D:".(int)$Member->UserID.":".(int)$Member->GroupID."\" NAME=\"Delete\" ".(substr($Member->MemberACL,3,1)=="D"? "CHECKED": "")."></TD>\n";
		echo "<TD ALIGN=\"center\"><INPUT TYPE=\"Checkbox\" VALUE=\"X:".(int)$Member->UserID.":".(int)$Member->GroupID."\" NAME=\"Execute\" ".(substr($Member->MemberACL,4,1)=="X"? "CHECKED": "")."></TD>\n";
		echo "</TR>\n";
	}
?>
</TABLE>
</DIV>
</FIELDSET>
<?php endif ?>
