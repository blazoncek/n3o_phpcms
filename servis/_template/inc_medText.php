<?php
/*~ edit_medText.php - Display/remove media attachments.
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

// get mediy type
$Tip = $db->get_var( "SELECT Tip FROM Media WHERE MediaID = ".(int)$_GET['MediaID'] );

// add media
if ( isset($_GET['BesediloID']) && $_GET['BesediloID'] != "" ) {
	$db->query("START TRANSACTION");
	if ( $Tip == 'PIC' ) {
		$Polozaj = $db->get_var( "SELECT max(Polozaj) FROM BesedilaSlike WHERE BesediloID = ".(int)$_GET['BesediloID'] );
		$db->query(
			"INSERT INTO BesedilaSlike (BesediloID, MediaID, Polozaj) ".
			"VALUES (".(int)$_GET['BesediloID'].", ".(int)$_GET['MediaID'].", ".($Polozaj? $Polozaj+1: 1).")" );
	} else {
		$Polozaj = $db->get_var( "SELECT max(Polozaj) FROM BesedilaMedia WHERE BesediloID = ".(int)$_GET['BesediloID'] );
		$db->query(
			"INSERT INTO BesedilaMedia (BesediloID, MediaID, Polozaj) ".
			"VALUES (".(int)$_GET['BesediloID'].", ".(int)$_GET['MediaID'].", ".($Polozaj? $Polozaj+1: 1).")" );
	}
	$db->query("COMMIT");
}

// delete media from text
if ( isset( $_GET['Odstrani'] ) && $_GET['Odstrani'] != "" ) {
	$db->query("START TRANSACTION");
	if ( $Tip == 'PIC' ) {
		$db->query( "DELETE FROM BesedilaSlike WHERE ID = ".(int)$_GET['Odstrani'] );
	} else {
		$db->query( "DELETE FROM BesedilaMedia WHERE ID = ".(int)$_GET['Odstrani'] );
	}
	$db->query("COMMIT");
}

$ACLID = $db->get_var( "SELECT ACLID FROM Media WHERE MediaID = ".(int)$_GET['MediaID'] );
if ( $ACLID )
	$ACL = userACL($ACLID);
else
	$ACL = "LRWDX";

if ( isset($_GET['Find']) && $_GET['Find'] != "" ) {

	// display search results
	if ( $_GET['Find'] == "*" ) $_GET['Find'] = "";
	$List = $db->get_results(
		"SELECT DISTINCT
			B.BesediloID,
			B.Ime
		FROM
			Besedila B
			LEFT JOIN ". ($Tip=='PIC' ? 'BesedilaSlike' : 'BesedilaMedia') ." BM ON B.BesediloID = BM.BesediloID
			LEFT JOIN BesedilaOpisi BO ON B.BesediloID = BO.BesediloID
		WHERE
			(BM.MediaID IS NULL OR BM.MediaID <> ".(int)$_GET['MediaID'].") ".
			($_GET['Find']!=""? "AND (B.Ime LIKE '%".$_GET['Find']."%' OR BO.Naslov LIKE '%".$_GET['Find']."%' OR BO.Opis LIKE '%".$_GET['Find']."%') ": " ").
		"ORDER BY
			B.Ime"
	);

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	if ( !$List ) 
		echo "<TR><TD ALIGN=\"center\"><br><br>No data!<br><br></TD></TR>\n";
	else {
		$CurrentRow = 1;
		$RecordCount = count( $List );
		foreach ( $List as $Item ) {
			echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
			echo "<TD>";
			echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divBe').load('inc.php?Izbor=".$_GET['Izbor']."&MediaID=".(int)$_GET['MediaID']."&BesediloID=$Item->BesediloID');\">";
			echo "<b>$Item->Ime</b>";
			echo "</A>";
			echo "</TD>\n";
			echo "</TR>\n";
			$CurrentRow++;
		}
	}
	echo "</TABLE>\n";

} else {

	// display list of assigned media
	$List = $db->get_results(
		"SELECT".
		"	BM.ID,".
		"	BM.MediaID,".
		"	BM.BesediloID,".
		"	BM.Polozaj,".
		"	B.Ime,".
		"	B.ACLID ".
		"FROM".
		"	BesedilaMedia BM".
		"	LEFT JOIN Besedila B ON BM.BesediloID = B.BesediloID ".
		"WHERE".
		"	BM.MediaID = ".(int)$_GET['MediaID']." ".

		"UNION ".

		"SELECT".
		"	BS.ID,".
		"	BS.MediaID,".
		"	BS.BesediloID,".
		"	BS.Polozaj,".
		"	B.Ime,".
		"	B.ACLID ".
		"FROM".
		"	BesedilaSlike BS".
		"	LEFT JOIN Besedila B ON BS.BesediloID = B.BesediloID ".
		"WHERE".
		"	BS.MediaID = ".(int)$_GET['MediaID']
	);

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	if ( !$List ) 
		echo "<TR><TD ALIGN=\"center\">Not attached to any text!</TD></TR>\n";
	else {
		$CurrentRow = 1;
		$RecordCount = count( $List );
		foreach ( $List as $Item ) {
			if ( $Item->ACLID )
				$rACL = userACL( $$Item->ACLID );
			else
				$rACL = "LRWDX";
			echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
			echo "<TD>";
			if ( contains($rACL,"R") )
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=Text&ID=$Item->BesediloID');\">";
			if ( contains($rACL,"L") )
				echo $Item->Ime;
			else
				echo "-- skrita rubrika --";
			if ( contains($rACL,"R") )
				echo "</A>";
			echo "</TD>\n";
			echo "<TD ALIGN=\"right\" WIDTH=\"16\">";
			if ( contains($rACL,"W") ) {
				// delete
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divBe').load('inc.php?Izbor=".$_GET['Izbor']."&MediaID=".$_GET['MediaID']."&Odstrani=$Item->ID');\" TITLE=\"Odstrani\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" CLASS=\"icon\">\n";
			}
			echo "</TD>\n";
			echo "</TR>\n";
			$CurrentRow++;
		}
	}
	echo "</TABLE>\n";
}
?>
