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
if ( isset($_GET['MediaID']) && $_GET['MediaID'] != "" ) {
	$db->query("START TRANSACTION");
	$Polozaj = $db->get_var("SELECT max(Polozaj) FROM KategorijeMedia WHERE KategorijaID = '".$_GET['KategorijaID']."'");
	$db->query(
		"INSERT INTO KategorijeMedia (MediaID, KategorijaID, Polozaj) ".
		"VALUES (".(int)$_GET['MediaID'].", '".$_GET['KategorijaID']."', ".($Polozaj? $Polozaj+1: 1).")"
		);
	$db->query("COMMIT");
}

// remove category
if ( isset( $_GET['Odstrani'] ) && $_GET['Odstrani'] != "" ) {
	$db->query("DELETE FROM KategorijeMedia WHERE ID = ".(int)$_GET['Odstrani']);
}

// move items up/down
if ( isset( $_GET['Smer'] ) && $_GET['Smer'] != "" ) {
	$db->query("START TRANSACTION");
	if ( $ItemPos = $db->get_var("SELECT Polozaj FROM KategorijeMedia WHERE ID = ". (int)$_GET['Predmet']) ) {
		// calculate new position
		$ItemNew = $ItemPos + (int)$_GET['Smer'];
		// move
		$db->query("UPDATE KategorijeMedia SET Polozaj = 9999     WHERE KategorijaID = '".$_GET['KategorijaID']."' AND Polozaj = $ItemNew");
		$db->query("UPDATE KategorijeMedia SET Polozaj = $ItemNew WHERE KategorijaID = '".$_GET['KategorijaID']."' AND Polozaj = $ItemPos");
		$db->query("UPDATE KategorijeMedia SET Polozaj = $ItemPos WHERE KategorijaID = '".$_GET['KategorijaID']."' AND Polozaj = 9999");
	}
	$db->query("COMMIT");
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace("/\&Smer=[-0-9]+/", "", $_SERVER['QUERY_STRING']);
	$_SERVER['QUERY_STRING'] = preg_replace("/\&Predmet=[0-9]+/", "", $_SERVER['QUERY_STRING']);
}

$ACLID = $db->get_var("SELECT ACLID FROM Kategorije WHERE KategorijaID = '".$_GET['KategorijaID']."'");
if ( $ACLID )
	$ACL = userACL( $ACLID );
else
	$ACL = "LRWDX";

// display list of assigned media
if ( isset($_GET['Find']) && $_GET['Find'] != "" ) {

	if ( $_GET['Find'] == "*" ) $_GET['Find'] = "";
	$List = $db->get_results(
		"SELECT M.MediaID, M.Naziv, M.Tip, M.ACLID, KB.ID
		FROM Media M
			LEFT JOIN KategorijeMedia KB
				ON M.MediaID = KB.MediaID AND KB.KategorijaID = '".$_GET['KategorijaID']."'
			LEFT JOIN MediaOpisi MO
				ON M.MediaID = MO.MediaID
		WHERE M.Tip <> 'PIC' ".
		($_GET['Find']!=""? " AND (M.Naziv LIKE '%".$_GET['Find']."%' OR M.Tip LIKE '".$_GET['Find']."%' OR MO.Naslov LIKE '%".$_GET['Find']."%' OR MO.Opis LIKE '%".$_GET['Find']."%') ": " ").
		"ORDER BY M.Naziv"
		);

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	if ( !$List ) 
		echo "<TR><TD ALIGN=\"center\"><br><br>No data!<br><br></TD></TR>\n";
	else {
		$CurrentRow = 1;
		$RecordCount = count($List);
		foreach ( $List as $Item ) {
			$rACL = userACL($Item->ACLID);
			echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
			echo "<TD>";
			if ( !$Item->ID )
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divMe').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&MediaID=$Item->MediaID');\">";
			if ( contains($rACL,"L") )
				echo $Item->Naziv;
			else
				echo "-- skrita priponka --";
			if ( !$Item->ID )
				echo "</A>";
			echo "</TD>\n";
			echo "<TD ALIGN=\"right\">";
			echo  "(".$Item->Tip.")";
			echo "</TD>";
			echo "</TR>\n";
			$CurrentRow++;
		}
	}
	echo "</TABLE>\n";

} else {

	$List = $db->get_results(
		"SELECT KM.ID, KM.KategorijaID, M.MediaID, M.Naziv, M.Tip, M.ACLID, M.Izpis ".
		"FROM KategorijeMedia KM ".
		"	LEFT JOIN Media M ON KM.MediaID = M.MediaID ".
		"WHERE KategorijaID = '".$_GET['KategorijaID']."'
		ORDER BY KM.Polozaj"
	);
	echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	if ( !$List ) 
		echo "<TR><TD ALIGN=\"center\">No attachments!</TD></TR>\n";
	else {
		$CurrentRow = 1;
		$RecordCount = count( $List );
		foreach ( $List as $Item ) {
			$rACL = userACL($Item->ACLID);
			echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
			echo "<TD>";
			if ( contains($rACL,"R") )
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=Media&ID=$Item->MediaID');\">";
			if ( contains($rACL,"L") )
				echo $Item->Naziv;
			else
				echo "-- hidden attachment --";
			if ( contains($rACL,"R") )
				echo "</A>";
			if ( !$Item->Izpis )
				echo "*";
			echo "</TD>\n";
			//echo "<TD ALIGN=\"right\">";
			//echo  "(".$Item->Tip.")";
			//echo "</TD>";
			echo "<TD ALIGN=\"right\" NOWRAP WIDTH=\"40\">";
			// move items up/down
			if ( contains($ACL,"W") ) {
				if ( $CurrentRow > 1 )
					echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divMe').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&Predmet=$Item->ID&Smer=-1');\"><IMG SRC=\"pic/list.up.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni gor\" BORDER=\"0\" CLASS=\"icon\"></A>";
				else
					echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
				if ( $CurrentRow < $RecordCount )
					echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divMe').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&Predmet=$Item->ID&Smer=1');\"><IMG SRC=\"pic/list.down.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni dol\" BORDER=\"0\" CLASS=\"icon\"></A>";
				else
					echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divMe').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&Odstrani=$Item->ID');\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" CLASS=\"icon\"></A>";
			}
			echo "</TD>\n";
			echo "</TR>\n";
			$CurrentRow++;
		}
	}
	echo "</TABLE>\n";
}
?>
