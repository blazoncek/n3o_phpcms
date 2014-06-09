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

// add category
if ( isset($_GET['KategorijaID']) && $_GET['KategorijaID'] != "" ) {
	$db->query( "START TRANSACTION" );
	$Polozaj = $db->get_var( "SELECT max(Polozaj) FROM KategorijeBesedila WHERE KategorijaID = '".$_GET['KategorijaID']."'" );
	$db->query(
		"INSERT INTO KategorijeBesedila (BesediloID, KategorijaID, Polozaj) ".
		"VALUES (".(int)$_GET['BesediloID'].", '".$_GET['KategorijaID']."', ".($Polozaj? $Polozaj+1: 1).")" );
	$db->query( "COMMIT" );
}

// remove category
if ( isset( $_GET['BrisiKategorijo'] ) && $_GET['BrisiKategorijo'] != "" ) {
	$db->query( "DELETE FROM KategorijeBesedila WHERE ID = ".(int)$_GET['BrisiKategorijo'] );
}

// display list of assigned categories
if ( !isset($_GET['Find']) ) {
	$List = $db->get_results(
		"SELECT KB.ID, KB.KategorijaID, K.Ime, K.ACLID ".
		"FROM KategorijeBesedila KB ".
		"	LEFT JOIN Kategorije K ON KB.KategorijaID = K.KategorijaID ".
		"WHERE BesediloID = ".(int)$_GET['BesediloID']
	);
	echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	echo "<TR>\n";
	echo "<TD CLASS=\"novo\" COLSPAN=\"2\" STYLE=\"border-bottom:darkgray solid 1px;\">\n";
	echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#rubrike').load('inc.php?Izbor=BesediloKategorije&BesediloID=".$_GET['BesediloID']."&Find=');\">Izberi rubriko...</A>\n";
	echo "</TD>\n";
	echo "</TR>\n";
	if ( !$List ) 
		echo "<TR><TD ALIGN=\"center\">Ni dodeljenih rubrik!</TD></TR>\n";
	else {
		$CurrentRow = 1;
		$RecordCount = count( $List );
		foreach ( $List as $Item ) {
			$rACL = userACL($Item->ACLID);
			if ( contains($rACL,"L") ) {
				echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
				echo "<TD>&nbsp;";
				if ( contains($rACL,"R") )
					echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=Kategorije&ID=$Item->KategorijaID');\">";
				echo "<b>$Item->Ime</b>";
				if ( contains($rACL,"R") )
					echo "</a>";
				echo "</TD>\n";
				echo "<TD ALIGN=\"right\" NOWRAP>";
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#rubrike').load('inc.php?Izbor=BesediloKategorije&BesediloID=".$_GET['BesediloID']."&BrisiKategorijo=$Item->ID');\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Bri۩\" BORDER=\"0\" CLASS=\"icon\"></A>";
				echo "</TD>\n";
				echo "</TR>\n";
			}
		}
	}
	echo "</TABLE>\n";

} else {

	$List = $db->get_results(
		"SELECT K.KategorijaID, K.Ime, K.ACLID, KB.ID
		FROM Kategorije K 
			LEFT JOIN KategorijeBesedila KB
				ON K.KategorijaID = KB.KategorijaID AND KB.BesediloID = ".(int)$_GET['BesediloID']." ".
		($_GET['Find']!=""? "WHERE Ime LIKE '%".$_GET['Find']."%'": "").
		"ORDER BY K.KategorijaID"
	);

	echo "<script language=\"JavaScript\" type=\"text/javascript\">\n";
	echo "<!-- //\n";
	echo "$(document).ready(function(){\n";
	echo "\tif ($('#txtRuFind').val() != \"\" ) $('#clrRuFind').show();\n";
	echo "\t$('#txtRuFind').change(function(){\n";
	echo "\t\t$('#rubrike').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".(int)$_GET['BesediloID']."&Find='+$('#txtRuFind').val());\n";
	echo "\t});\n";
	echo "\t$('#clrRuFind').click(function(){\n";
	echo "\t\t$('#txtRuFind').val('');\n";
	echo "\t\t$('#clrRuFind').hide();\n";
	echo "\t\t$('#txtRuFind').select();\n";
	echo "\t\t$('#rubrike').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".(int)$_GET['BesediloID']."');\n";
	echo "\t});\n";
	echo "});\n";
	echo "//-->\n";
	echo "</script>\n";

	echo "<div id=\"findRu\" class=\"find\">\n";
	echo "<input id=\"txtRuFind\" type=\"Text\" name=\"Find\" maxlength=\"32\" value=\"".(isset($_GET['Find'])?$_GET['Find']:"")."\" onkeypress=\"$('#clrRuFind').show();\">\n";
	echo "<a id=\"clrRuFind\" href=\"javascript:void(0);\"><img src=\"pic/list.clear.gif\" border=\"0\"></a>\n";
	echo "</div>\n";

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	if ( !$List ) 
		echo "<TR><TD ALIGN=\"center\"><br><br>No data!<br><br></TD></TR>\n";
	else {
		$CurrentRow = 1;
		$RecordCount = count( $List );
		foreach ( $List as $Item ) {
			echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
			echo "<TD>".str_repeat("&nbsp;",(strlen($Item->KategorijaID)-1)*2);
			if ( !$Item->ID )
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#rubrike').load('inc.php?Izbor=BesediloKategorije&BesediloID=".(int)$_GET['BesediloID']."&KategorijaID=$Item->KategorijaID');\">";
			echo "<b>$Item->Ime</b>";
			if ( !$Item->ID )
				echo "</A>";
			echo "</TD>\n";
			echo "</TR>\n";
			$CurrentRow++;
		}
	}
	echo "</TABLE>\n";
}
?>
