<?php
/*~ edit_ACL.php - Edit ACLs. Add update ACL info and add/remove ACLs.
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
$Podatek = $db->get_row("SELECT * FROM SMACL WHERE ACLID = ". (int)$_GET['ID']);

?>
<script language="JavaScript" type="text/javascript">
<!-- //
function setList( list_obj, form_obj, selector ) {
	var count = 0;

	list_obj.value = "";			
    for (i=0; i < form_obj.elements.length; i++) {
		if (form_obj.elements[i].checked && form_obj.elements[i].value!="" &&
			(!selector || form_obj.elements[i].name==selector)) {
			var startPosition = 0;
			var indexPosition = 0;
			var selectString;

			if (count > 0 ) { list_obj.value += ","; }

			selectString = form_obj.elements[i].value;
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
$('#edit').live('pageinit', function(event){
	// bind to the form's submit event
	$("form[name='Vnos']").submit(function(e){
		// inside event callbacks 'this' is the DOM element so we first
		// wrap it in a jQuery object
		jqObj = $(this);
		setList( this.elements["GroupList"], this, "Grp" );
		setList( this.elements["UserList"], this, "Usr" );
		URL = '<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>';
		//$.mobile.loadPage(URL, {
		//	pageContainer: $("#editUser"),
		//	reloadPage: true,
		//	type: "post",
		//	data: $(this).serialize()
		//});
		return true;
	});
	// add change events for ACLs
	$("input[name^=ACL]:checkbox").bind("click", function(event,ui){
		var options = {};
		var btn = this.value.substr(0,1);
		var id = this.id.substr(5,3);
		
		if ( btn=="L" ) options.List    = this.checked;
		if ( btn=="R" ) options.Read    = this.checked;
		if ( btn=="W" ) options.Write   = this.checked;
		if ( btn=="D" ) options.Delete  = this.checked;
		if ( btn=="X" ) options.Execute = this.checked;
		if ( options.Execute ) {options.Read = true;}
		if ( options.Delete ) {options.Write = true;}
		if ( options.Write ) {options.Read = true;}
		if ( options.Read ) {options.List = true;}
		if ( options.List ) $("#cbxL-"+id).attr("checked",options.List).checkboxradio("refresh");
		if ( options.Read ) $("#cbxR-"+id).attr("checked",options.Read).checkboxradio("refresh");
		if ( options.Write ) $("#cbxW-"+id).attr("checked",options.Write).checkboxradio("refresh");
		if ( options.Delete ) $("#cbxD-"+id).attr("checked",options.Delete).checkboxradio("refresh");
		if ( options.Execute ) $("#cbxX-"+id).attr("checked",options.Execute).checkboxradio("refresh");
		
		var UserOff  = strpos( this.value, ":" )+1;
		var GroupOff = strpos( this.value, ":", UserOff )+1;
		options.UserID  = this.value.substr( UserOff, GroupOff-UserOff-1 );
		options.GroupID = this.value.substr( GroupOff );
		URL = '<?php echo dirname($_SERVER['PHP_SELF']); ?>/upd.php?<?php echo $_SERVER['QUERY_STRING'] ?>';
		$.mobile.loadPage(URL, {
			pageContainer: $("#result"),
			reloadPage: false,
			type: "post",
			data: options
		});
	});
});
//-->
</script>

<?php
echo "<div id=\"edit\" data-role=\"page\" data-title=\"ACL\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>". $Podatek->Name ."</h1>\n";
echo "<a href=\"list.php?Izbor=". $_GET['Izbor'] ."\" title=\"Back\" data-role=\"button\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-rel=\"back\" data-transition=\"slide\">Back</a>\n";
//echo "<a href=\"./\" title=\"Home\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Home</a>\n";
echo "<a href=\"#editUsers\" title=\"ACL\" class=\"ui-btn-right\" data-iconpos=\"notext\" data-icon=\"gear\">ACL</a>\n";
echo "</div>\n";
echo "<div data-role=\"content\">\n";

if ( isset($Error) ) {
	echo "\t<div class=\"ui-body ui-body-d ui-corner-all\" style=\"padding:1em;text-align:center;\">";
	echo "<b>Error!</b><br>Data not saved.";
	echo "</div>\n";
} elseif ( $_GET['ID'] == "0" ) {
		echo "\t<div class=\"ui-body ui-body-d ui-corner-all\" style=\"padding:1em;text-align:center;\">";
		echo "<B>ACL cannot be created from this link!</B>";
		echo "</div>\n";
} else {
	
	$Groups = $db->get_results(
		"SELECT G.GroupID, G.Name, Ar.ACLID
		FROM SMGroup G
			LEFT JOIN SMACLr Ar ON G.GroupID = Ar.GroupID AND Ar.ACLID = ". (int)$_GET['ID'] ."
		ORDER BY G.Name"
	);
	$Users = $db->get_results(
		"SELECT U.UserID, U.UserName, Ar.ACLID
		FROM SMUser U
			LEFT JOIN SMACLr Ar ON U.UserID = Ar.UserID AND Ar.ACLID = ". (int)$_GET['ID'] ."
		ORDER BY U.Username"
		);
?>
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post" data-ajax="false">
	<INPUT Name="GroupList" Type="HIDDEN" VALUE="">
	<INPUT Name="UserList" Type="HIDDEN" VALUE="">
	<INPUT Name="Action" Type="HIDDEN" VALUE="Set">
<?php
	echo "\t<fieldset data-role=\"controlgroup\"><legend>Groups</legend>\n";
	// disable groups 1 (everyone) for anyone and 2 (administrators) for administrator
	if ( count($Groups) > 0 )
		foreach ( $Groups as $Member ) {
			echo "\t\t<input type=\"checkbox\" name=\"Grp\" value=\"$Member->GroupID\" id=\"cbxG-$Member->GroupID\" ". ($Member->GroupID==2 ? "DISABLED" : "") . (($Member->ACLID) ? " CHECKED" : "" ) . "  data-theme=\"d\" />\n";
			echo "\t\t<label for=\"cbxG-$Member->GroupID\">$Member->Name</label>\n";
		}
	echo "\t</fieldset>\n";

	echo "\t<fieldset data-role=\"controlgroup\"><legend>Users</legend>\n";
	// disable groups 1 (everyone) for anyone and 2 (administrators) for administrator
	if ( count($Users) > 0 )
		foreach ( $Users as $Member ) {
			echo "\t\t<input type=\"checkbox\" name=\"Usr\" value=\"$Member->UserID\" id=\"cbxU-$Member->UserID\" ". ($Member->UserID==1 ? "DISABLED" : "") . (($Member->ACLID) ? " CHECKED" : "" ) . "  data-theme=\"d\" />\n";
			echo "\t\t<label for=\"cbxU-$Member->UserID\">$Member->UserName</label>\n";
		}
	echo "\t</fieldset>\n";
?>
<?php if ( contains($ActionACL,"W") ) : ?>
	<INPUT TYPE="submit" VALUE="Save" data-iconpos="left" data-icon="check" data-theme="a">
<?php endif ?>
</FORM>
<?php
}

echo "</div>\n";
//echo "\t<div data-role=\"footer\" data-position=\"fixed\" class=\"ui-bar\" style=\"text-align:center;\">\n";
//echo "\t</div>\n";
echo "</div>\n"; // page

echo "<div id=\"editUsers\" data-role=\"page\" data-title=\"ACL\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>". $Podatek->Name ."</h1>\n";
echo "<a href=\"list.php?Izbor=". $_GET['Izbor'] ."\" title=\"Back\" data-role=\"button\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-rel=\"back\" data-transition=\"slide\">Back</a>\n";
echo "<a href=\"#edit\" title=\"ACL\" class=\"ui-btn-right\" data-iconpos=\"notext\" data-icon=\"info\" data-direction=\"reverse\">ACL</a>\n";
echo "</div>\n";
echo "<div data-role=\"content\">\n";

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

if ( contains($ActionACL,"W") ) {
	if ( count($Members) > 0 ) {
		echo "\t<div data-role=\"fieldcontain\">\n";
		$i=0;
		foreach ( $Members as $Member ) {
			$i++;
			echo "\t<fieldset data-role=\"controlgroup\" data-type=\"horizontal\">";
			echo "<legend>".(($Member->Username=="*Group*")? "<B>": "").$Member->Name.(($Member->Username=="*Group*")? "</B>": " (<i>".$Member->Username."</i>)")."</legend>\n";
			echo "\t\t<INPUT TYPE=\"checkbox\" ID=\"cbxL-".$i."\" VALUE=\"L:".(int)$Member->UserID.":".(int)$Member->GroupID."\" NAME=\"ACLList-".$i."\" ".(substr($Member->MemberACL,0,1)=="L"? "CHECKED": "").">\n";
			echo "\t\t<label for=\"cbxL-".$i."\">L</label>\n";
			echo "\t\t<INPUT TYPE=\"checkbox\" ID=\"cbxR-".$i."\" VALUE=\"R:".(int)$Member->UserID.":".(int)$Member->GroupID."\" NAME=\"ACLRead-".$i."\" ".(substr($Member->MemberACL,1,1)=="R"? "CHECKED": "").">\n";
			echo "\t\t<label for=\"cbxR-".$i."\">R</label>\n";
			echo "\t\t<INPUT TYPE=\"checkbox\" ID=\"cbxW-".$i."\" VALUE=\"W:".(int)$Member->UserID.":".(int)$Member->GroupID."\" NAME=\"ACLWrite-".$i."\" ".(substr($Member->MemberACL,2,1)=="W"? "CHECKED": "").">\n";
			echo "\t\t<label for=\"cbxW-".$i."\">W</label>\n";
			echo "\t\t<INPUT TYPE=\"checkbox\" ID=\"cbxD-".$i."\" VALUE=\"D:".(int)$Member->UserID.":".(int)$Member->GroupID."\" NAME=\"ACLDelete-".$i."\" ".(substr($Member->MemberACL,3,1)=="D"? "CHECKED": "").">\n";
			echo "\t\t<label for=\"cbxD-".$i."\">D</label>\n";
			echo "\t\t<INPUT TYPE=\"checkbox\" ID=\"cbxX-".$i."\" VALUE=\"X:".(int)$Member->UserID.":".(int)$Member->GroupID."\" NAME=\"ACLExecute-".$i."\" ".(substr($Member->MemberACL,4,1)=="X"? "CHECKED": "").">\n";
			echo "\t\t<label for=\"cbxX-".$i."\">X</label>\n";
			echo "\t</fieldset>\n";
		}
		echo "\t</div>\n";
	}
}

echo "</div>\n";
//echo "\t<div data-role=\"footer\" data-position=\"fixed\" class=\"ui-bar\" style=\"text-align:center;\">\n";
//echo "\t</div>\n";
echo "</div>\n"; // page
echo "<div id=\"result\" data-role=\"page\"></div>\n";
?>
