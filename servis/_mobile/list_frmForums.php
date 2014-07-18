<?php
/*~ list_frmForums.php - List forums
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

// define default values for URL ID and Find parameters (in case not defined)
if ( !isset($_GET['ID']) )   $_GET['ID'] = "0";
if ( !isset($_GET['Find']) ) $_GET['Find'] = "";

// get categories
$List = $db->get_results("SELECT ID, CategoryName FROM frmCategories ORDER BY CategoryOrder");

$RecordCount = count($List);
?>
<SCRIPT Language="JAVASCRIPT">
<!--//
$('#list').live('pageinit', function(event){
	$("input[name=Find]").bind("change", function(event,ui){
		var URL = '<?php echo $_SERVER['PHP_SELF']; ?>?Action=<?php echo $_GET['Action']; ?>';
		$("select").each(function(index){
			if ( this[this.selectedIndex].value != "" )
				URL += '&' + this.name + '=' + this[this.selectedIndex].value;
		});
		if ( this.value != "" ) URL += '&Find='+this.value;
		document.location.href = URL;
	});
	$("select").bind("change", function(event,ui){
		var URL = '<?php echo $_SERVER['PHP_SELF']; ?>?Action=<?php echo $_GET['Action'] . ($_GET['Find']!=""? "&Find=".$_GET['Find']: ""); ?>';
		$("select").each(function(index){
			if ( this[this.selectedIndex].value != "" )
				URL += '&' + this.name + '=' + this[this.selectedIndex].value;
		});
		document.location.href = URL;
	});
});
//-->
</SCRIPT>
<?php

echo "<div id=\"list\" data-role=\"page\" data-title=\"Forums\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Forums</h1>\n";
echo "<a href=\"./#menu". left($_GET['Action'],2) ."\" title=\"Back\" class=\"ui-btn-left\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-ajax=\"false\" data-transition=\"slide\">Back</a>\n";
echo "<a href=\"./\" title=\"Home\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Home</a>\n";
echo "</div>\n";
echo "<div data-role=\"content\">\n";

// display results
if ( count( $List ) == 0 ) {
	echo "<div class=\"ui-body ui-body-d ui-corner-all\" style=\"color:red;padding:1em;text-align:center;\">\n";
	echo "<B>No data!</B>\n";
	echo "</div>\n";
} else {
	echo "<ul data-role=\"listview\" data-filter-test=\"true\" data-theme=\"d\" data-divider-theme=\"d\" data-split-icon=\"delete\" data-split-theme=\"e\">\n";
	foreach ( $List as $Item ) {
		$Forums = $db->get_results(
			"SELECT ID, ForumName, NotifyModerator, ApprovalRequired, AllowFileUploads, ViewOnly, Hidden, PollEnabled, Private,
				(SELECT count(*) FROM frmTopics WHERE ForumID = f.ID) AS MaxTopics
			FROM frmForums f
			WHERE CategoryID = " . (int)$Item->ID ."
			ORDER BY ForumOrder, ForumName"
			);

		echo "<li data-role=\"list-divider\">";
		//echo "<a href=\"edit.php?Izbor=".$_GET['Izbor']."&ID=$Item->ID\" data-ajax=\"false\">";
		echo "<h3>". $Item->CategoryName ."</h3>";
		echo "</li>\n";

		if ( $Forums ) foreach ( $Forums as $Forum ) {
			echo "<li>";
			echo "<a href=\"edit.php?Izbor=". $_GET['Izbor'] ."&Action=". $_GET['Action'] ."&ID=". $Forum->ID ."\" data-ajax=\"false\">";
			echo "<h3>".$Forum->ForumName."</h3>";
			
			echo "<p class=\"ui-li-aside\">";
			echo ($Forum->NotifyModerator)?  "<span style=\"color:red;\">N</span>": "";
			echo ($Forum->ApprovalRequired)? "<span style=\"color:orange;\">O</span>": "";
			echo ($Forum->AllowFileUploads)? "<span style=\"color:Navy;\">U</span>": "";
			echo ($Forum->ViewOnly)?         "<span style=\"color:DeepSkyBlue;\">V</span>": "";
			echo ($Forum->Hidden)?           "<span style=\"color:DarkGray;\">H</span>": "";
			echo ($Forum->PollEnabled)?      "<span style=\"color:MediumTurquoise;\">P</span>": "";
			echo ($Forum->Private)?          "<span style=\"color:IndianRed;\">Z</span>": "";
			echo "</p>";

			echo "<span class=\"ui-li-count\">";
			echo $Forum->MaxTopics;
			echo "</span>";
			echo "</a>";
			
			if ( contains($ActionACL,"D") )
				echo "<a href=\"#\" onclick=\"check('$Forum->ID','$Forum->ForumName');\">Delete</a>";
			echo "</li>\n";
		}
	}
	echo "</ul>\n";
}
echo "</div>\n";
?>
