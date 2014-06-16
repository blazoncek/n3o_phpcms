<?php
/*~ inc_txtTags.php - adding tags
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

// add category
if ( isset($_GET['newtag']) && $_GET['newtag'] != "" ) {
	try {
		$db->query("START TRANSACTION");
		$ID = $db->get_var("SELECT TagID FROM Tags WHERE TagName='". $db->escape($_GET['newtag']) ."'");
		if ( $ID ) {
			if ( !$db->get_var("SELECT ID FROM BesedilaTags WHERE BesediloID=". (int)$_GET['BesediloID'] ." AND TagID=". $ID) )
				$db->query("INSERT INTO BesedilaTags (BesediloID, TagID) VALUES (". (int)$_GET['BesediloID'] .",". $ID .")");
		} else {
			$db->query("INSERT INTO Tags (TagName) VALUES ('". $db->escape($_GET['newtag']) ."')");
			$ID = $db->get_var("SELECT TagID FROM Tags WHERE TagName='". $db->escape($_GET['newtag'])."'");
			$db->query("INSERT INTO BesedilaTags (BesediloID, TagID) VALUES (". (int)$_GET['BesediloID'] .",". $ID .")");
		}
		// audit action
		$db->query(
			"INSERT INTO SMAudit (
				UserID,
				ObjectID,
				ObjectType,
				Action,
				Description
			) VALUES (
				". $_SESSION['UserID'] .",
				". (int)$_GET['BesediloID'] .",
				'Text',
				'Add tag',
				'". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". (int)$_GET['BesediloID'])
				.",". $db->escape($_GET['newtag']) ."'
			)"
			);
		$db->query("COMMIT");
	} catch (Exception $e) {
		$db->query("ROLLBACK TRANSACTION");
	}
}

// remove category
if ( isset($_GET['deltag']) && $_GET['deltag'] != "" ) {
	try {
		$db->query("START TRANSACTION");
		$ID = $db->get_var("SELECT TagID FROM BesedilaTags WHERE ID=". (int)$_GET['deltag']);
		$db->query("DELETE FROM BesedilaTags WHERE ID=". (int)$_GET['deltag']);
		// audit action
		$db->query(
			"INSERT INTO SMAudit (
				UserID,
				ObjectID,
				ObjectType,
				Action,
				Description
			) VALUES (
				". $_SESSION['UserID'] .",
				". (int)$_GET['BesediloID'] .",
				'Text',
				'Delete tag',
				'". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". (int)$_GET['BesediloID'])
				.",". $db->get_var("SELECT TagName FROM Tags WHERE TagID=". (int)$ID) ."'
			)"
			);
		try {
			@$db->query("DELETE FROM Tags WHERE TagID=". $ID); // try to delete tag itself (will succeede only if not used)
		} catch (Exception $e) {}
		$db->query("COMMIT");
	} catch (Exception $e) {
		$db->query("ROLLBACK TRANSACTION");
	}
}

// display list of assigned tags
$List = $db->get_results(
	"SELECT BT.ID, T.TagName
	FROM BesedilaTags BT
		LEFT JOIN Tags T ON BT.TagID = T.TagID
	WHERE BT.BesediloID = ". (int)$_GET['BesediloID'] ."
	ORDER BY T.TagName"
	);

echo "<script language=\"JavaScript\" type=\"text/javascript\">\n";
echo "<!-- //\n";
echo "$(document).ready(function(){\n";
echo "$(\"form[name='AddTag']\").submit(function(){\n";
echo "$(this).ajaxSubmit({\n";
echo "target: '#tags',\n";
echo "beforeSubmit: function( formDataArr, jqObj, options ){\n";
echo "var fObj = jqObj[0];	// form object\n";
echo "return true;\n";
echo "} // pre-submit callback\n";
echo "});\n";
echo "return false;\n";
echo "});\n";
echo "$('#newtag').select().autocomplete({\n";
echo "source: \"json.php?Izbor=getTags&BesediloID=".(int)$_GET['BesediloID']."\",\n";
echo "minLength: 2,\n";
echo "delay: 500,\n";
echo "select: function( event, ui ) {\n";
echo "$('#tags').load('inc.php?Izbor=txtTags&BesediloID=".(int)$_GET['BesediloID']."&newtag='+ui.item.value);\n";
echo "}\n";
echo "});\n";
//echo "$('#newtag').change(function(){\n";
//echo "$('#tags').load('inc.php?Izbor=txtTags&BesediloID=".(int)$_GET['BesediloID']."&newtag='+$('#newtag').val());\n";
//echo "});\n";
//echo "$('#newtag').width($('#tags').parent().width()-24);\n";
echo "});\n";
echo "//-->\n";
echo "</script>\n";

echo "<div style=\"margin:5px 0;\">\n";
echo "<form name=\"AddTag\" id=\"addtag\" action=\"inc.php\" method=\"get\">";
echo "<input name=\"Izbor\" value=\"txtTags\" type=\"Hidden\">";
echo "<input name=\"BesediloID\" value=\"".(int)$_GET['BesediloID']."\" type=\"Hidden\">";
echo "<input id=\"newtag\" type=\"Text\" name=\"newtag\" maxlength=\"64\" style=\"width:99%\" value=\"\">";
echo "</form>\n";
echo "</div>\n";

echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
if ( !$List ) 
	echo "<TR><TD ALIGN=\"center\">No assigned tags!</TD></TR>\n";
else {
	$CurrentRow = 1;
	$RecordCount = count( $List );
	foreach ( $List as $Item ) {
		echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
		echo "<TD>&nbsp;";
		echo "<b>". $Item->TagName ."</b>";
		echo "</TD>\n";
		echo "<TD ALIGN=\"right\" NOWRAP>";
		echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#tags').load('inc.php?Izbor=txtTags&BesediloID=".$_GET['BesediloID']."&deltag=$Item->ID');\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Bri۩\" BORDER=\"0\" CLASS=\"icon\"></A>";
		echo "</TD>\n";
		echo "</TR>\n";
	}
}
echo "</TABLE>\n";
?>
