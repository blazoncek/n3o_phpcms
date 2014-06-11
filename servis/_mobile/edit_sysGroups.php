<?php
/*~ edit_Grupe.php - Editing group members.
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

$Group = $db->get_row("SELECT GroupID, Name FROM SMGroup WHERE GroupID = ". (int)$_GET['ID']);

?>
<SCRIPT Language="JAVASCRIPT">
<!--//
$('#edit').live('pageinit', function(event){
	// add change events for Name
	$("input[name=Name]").bind("change", function(event,ui){
		var options = {};
		if (this.value=="")	{alert("Please enter group name!"); this.focus(); return false;}
		options.Name = this.value;
		URL = '<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>';
		$.mobile.changePage(URL, {
			transition: "pop",
			reloadPage: true,
			type: "post",
			data: options
		});
	});
	// add change events for members
	$("input[^name=Grp-]:checkbox").bind("click", function(event,ui){
		var options = {};
		options.UserList = this.value;
		options.Action   = (this.checked ? "Add" : "Remove");
		options.GroupID  = this.name.substr(4,4);
		URL = '<?php echo dirname($_SERVER['PHP_SELF'])?>/upd.php?Izbor=<?php echo $_GET['Izbor'] ?>&ID='+options.GroupID;
		$.mobile.loadPage(URL, {
			pageContainer: $("#result"),
			transition: "pop",
			reloadPage: false,
			type: "post",
			data: options
		});
	});
});
//-->
</SCRIPT>

<?php
echo "<div id=\"edit\" data-role=\"page\" data-title=\"Skupine\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Skupine</h1>\n";
echo "<a href=\"list.php?Izbor=". $_GET['Izbor'] ."\" title=\"Back\" data-role=\"button\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-rel=\"back\" data-transition=\"slide\">Back</a>\n";
echo "<a href=\"./\" title=\"Home\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Home</a>\n";
echo "</div>\n";
echo "<div data-role=\"content\">\n";

if ( isset( $Error ) ) {
	echo "\t<div class=\"ui-body ui-body-d ui-corner-all\" style=\"padding:1em;text-align:center;\">";
	echo "<b>Error!</b><br>Data not saved.";
	echo "</div>\n";
} else {

?>
	<fieldset class="ui-hide-label" data-role="fieldcontain"><legend>Basic&nbsp;information:</legend>
		<LABEL FOR="frmGroupName"><B>Name</B></LABEL>
		<INPUT TYPE="text" NAME="Name" ID="frmGroupName" MAXLENGTH="50" VALUE="<?php echo ($Group ? $Group->Name : "") ?>" <?php echo ((int)$_GET['ID'] <= 4 && (int)$_GET['ID'] > 0) ? "READONLY" : "" ?> placeholder="Name" data-theme="d"><br />
	</fieldset>
<?php
	if ( (int)$_GET['ID'] > 0 ) {
		// users
		$Members = $db->get_results(
			"SELECT
				U.UserID,
				U.UserName,
				UG.ID
			FROM
				SMUser U
				LEFT JOIN SMUserGroups UG
					ON U.UserID = UG.UserID AND UG.GroupID = ". (int)$_GET['ID'] ."
			ORDER BY
				U.UserName"
			);

		echo "\t<fieldset data-role=\"controlgroup\"><legend>Members</legend>\n";
		// disable groups 1 (everyone) for anyone and 2 (administrators) for administrator
		if ( count($Members) > 0 )
			foreach ( $Members as $Member ) {
				echo "\t\t<input type=\"checkbox\" name=\"Grp-". (int)$_GET['ID'] ."\" value=\"$Member->UserID\" id=\"cbx-$Member->UserID\"" . (($Member->UserID==1 && $_GET['ID']==2) || $_GET['ID']==1 || !contains($ActionACL,"W") ? " DISABLED" : "" ) . (($Member->ID) ? " CHECKED" : "" ) . "  data-theme=\"d\" />\n";
				echo "\t\t<label for=\"cbx-$Member->UserID\">$Member->UserName</label>\n";
			}
		echo "\t</fieldset>\n";
	}
	echo "</div>\n";
	//echo "\t<div data-role=\"footer\" data-position=\"fixed\" class=\"ui-bar\" style=\"text-align:center;\">\n";
	//echo "\t</div>\n";
	echo "</div>\n"; // page
	echo "<div id=\"result\" data-role=\"page\"></div>\n"; // page
}
?>
