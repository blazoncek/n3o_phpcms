<?php
/*~ inc_BesediloTags.php - adding tags
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
		$db->query( "START TRANSACTION" );
		$ID = $db->get_var( "SELECT TagID FROM Tags WHERE TagName='".$_GET['newtag']."'" );
		if ( $ID ) {
			if ( !$db->get_var( "SELECT ID FROM BesedilaTags ".
				"WHERE BesediloID=".(int)$_GET['BesediloID']." AND TagID=".$ID ) )
				$db->query(
					"INSERT INTO BesedilaTags (BesediloID, TagID) ".
					"VALUES (".(int)$_GET['BesediloID'].",".$ID.")" );
		} else {
			$db->query(
				"INSERT INTO Tags (TagName) ".
				"VALUES ('".$_GET['newtag']."')" );
			$ID = $db->get_var( "SELECT TagID FROM Tags WHERE TagName='".$_GET['newtag']."'" );
			$db->query(
				"INSERT INTO BesedilaTags (BesediloID, TagID) ".
				"VALUES (".(int)$_GET['BesediloID'].",".$ID.")" );
		}
		$db->query( "COMMIT" );
	} catch (Exception $e) {
		$db->query( "ROLLBACK TRANSACTION" );
	}
}

// remove category
if ( isset($_GET['deltag']) && $_GET['deltag'] != "" ) {
	try {
		$db->query( "START TRANSACTION" );
		$ID = $db->get_var( "SELECT TagID FROM BesedilaTags WHERE ID=".(int)$_GET['deltag'] );
		$db->query( "DELETE FROM BesedilaTags WHERE ID=".(int)$_GET['deltag'] );
		try {
			@$db->query( "DELETE FROM Tags WHERE TagID=".$ID );
		} catch (Exception $e) {}
		$db->query( "COMMIT" );
	} catch (Exception $e) {
		$db->query( "ROLLBACK TRANSACTION" );
	}
}

// display list of assigned tags
$List = $db->get_results(
	"SELECT BT.ID, T.TagName ".
	"FROM BesedilaTags BT ".
	"	LEFT JOIN Tags T ON BT.TagID = T.TagID ".
	"WHERE BT.BesediloID = ".(int)$_GET['BesediloID']." ".
	"ORDER BY T.TagName" );

echo "<script language=\"JavaScript\" type=\"text/javascript\">\n";
echo "<!-- //\n";
echo "$(document).ready(function(){\n";
echo "\t// bind to the form's submit event\n";
echo "\t$(\"form[name='AddTag']\").submit(function(){\n";
echo "\t\t$(this).ajaxSubmit({\n";
echo "\t\t\ttarget: '#tags',\n";
echo "\t\t\tbeforeSubmit: function( formDataArr, jqObj, options ){\n";
echo "\t\t\t\tvar fObj = jqObj[0];	// form object\n";
echo "\t\t\t\treturn true;\n";
echo "\t\t\t} // pre-submit callback\n";
echo "\t\t});\n";
echo "\t\treturn false;\n";
echo "\t});\n";
echo "\t$('#newtag').select().autocomplete({\n";
echo "\t\tsource: \"json.php?Izbor=getTags&BesediloID=".(int)$_GET['BesediloID']."\",\n";
echo "\t\tminLength: 2,\n";
echo "\t\tdelay: 500,\n";
echo "\t\tselect: function( event, ui ) {\n";
echo "\t\t\t$('#tags').load('inc.php?Izbor=BesediloTags&BesediloID=".(int)$_GET['BesediloID']."&newtag='+ui.item.value);\n";
echo "\t\t}\n";
echo "\t});\n";
//echo "\t$('#newtag').change(function(){\n";
//echo "\t\t$('#tags').load('inc.php?Izbor=BesediloTags&BesediloID=".(int)$_GET['BesediloID']."&newtag='+$('#newtag').val());\n";
//echo "\t});\n";
echo "\t$('#newtag').width($('#tags').parent().width()-16);\n";
echo "});\n";
echo "//-->\n";
echo "</script>\n";

echo "<div style=\"margin:5px;\">\n";
echo "<form name=\"AddTag\" id=\"addtag\" action=\"inc.php\" method=\"get\">";
echo "<input name=\"Izbor\" value=\"BesediloTags\" type=\"Hidden\">";
echo "<input name=\"BesediloID\" value=\"".(int)$_GET['BesediloID']."\" type=\"Hidden\">";
echo "<input id=\"newtag\" type=\"Text\" name=\"newtag\" maxlength=\"64\" value=\"\">";
echo "</form>\n";
echo "</div>\n";

echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
if ( !$List ) 
	echo "<TR><TD ALIGN=\"center\">Ni dodeljenih oznak!</TD></TR>\n";
else {
	$CurrentRow = 1;
	$RecordCount = count( $List );
	foreach ( $List as $Item ) {
		echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
		echo "<TD>&nbsp;";
		echo "<b>$Item->TagName</b>";
		echo "</TD>\n";
		echo "<TD ALIGN=\"right\" NOWRAP>";
		echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#tags').load('inc.php?Izbor=BesediloTags&BesediloID=".$_GET['BesediloID']."&deltag=$Item->ID');\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Bri۩\" BORDER=\"0\" CLASS=\"icon\"></A>";
		echo "</TD>\n";
		echo "</TR>\n";
	}
}
echo "</TABLE>\n";
?>