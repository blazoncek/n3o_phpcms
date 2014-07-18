<?php
/*
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

// add content template
if ( isset($_GET['Dodaj']) && $_GET['Dodaj'] != "" ) {
	$db->query("START TRANSACTION");
	$Polozaj = $db->get_var(
		"SELECT max(Polozaj) FROM KategorijeVsebina ".
		"WHERE KategorijaID='". $db->escape($_GET['KategorijaID']) ."' AND Ekstra=".(int)$_GET['Ekstra']
		);
	$db->query(
		"INSERT INTO KategorijeVsebina (PredlogaID, KategorijaID, Polozaj, Ekstra) ".
		"VALUES (". (int)$_GET['Dodaj'] .", '". $db->escape($_GET['KategorijaID']) ."',". ($Polozaj? $Polozaj+1: 1) .",". (int)$_GET['Ekstra'] .")"
		);
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
			NULL,
			'Category',
			'Attach template to category',
			'". $db->get_var("SELECT Ime FROM Kategorije WHERE KategorijaID='". $db->escape($_GET['KategorijaID']) ."'")
			.",". $db->get_var("SELECT Naziv FROM Predloge WHERE PredlogaID=". (int)$_GET['Dodaj']) ."'
		)"
		);
	$db->query("COMMIT");
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace("/\&Dodaj=[0-9]+/", "", $_SERVER['QUERY_STRING']);
}

// delete additional text from list
if ( isset($_GET['Odstrani']) && $_GET['Odstrani'] != "" ) {
	$db->query("START TRANSACTION");
	$x = $db->get_row("SELECT PredlogaID, KategorijaID FROM KategorijeVsebina WHERE ID=".(int)$_GET['Odstrani']);
	$db->query("DELETE FROM KategorijeVsebina WHERE ID=". (int)$_GET['Odstrani']);
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
			NULL,
			'Category',
			'Remove template from category',
			'". $db->get_var("SELECT Ime FROM Kategorije WHERE KategorijaID='". $x->KategorijaID ."'")
			.",". $db->get_var("SELECT Naziv FROM Predloge WHERE PredlogaID=". $x->PredlogaID) ."'
		)"
		);
	$db->query("COMMIT");
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace("/\&Odstrani=[0-9]+/", "", $_SERVER['QUERY_STRING']);
}

// move items up/down
if ( isset($_GET['Smer']) && $_GET['Smer'] != "" ) {
	$db->query("START TRANSACTION");
	if ( $ItemPos = $db->get_var("SELECT Polozaj FROM KategorijeVsebina WHERE ID = ". (int)$_GET['Predloga']) ) {
		// calculate new position
		$ItemNew = $ItemPos + (int)$_GET['Smer'];
		// move
		$db->query("UPDATE KategorijeVsebina SET Polozaj=9999 WHERE KategorijaID='". $db->escape($_GET['KategorijaID']) ."' AND Ekstra=". (int)$_GET['Ekstra'] ." AND Polozaj=". $ItemNew);
		$db->query("UPDATE KategorijeVsebina SET Polozaj=". $ItemNew ." WHERE KategorijaID='". $db->escape($_GET['KategorijaID']) ."' AND Ekstra=". (int)$_GET['Ekstra'] ." AND Polozaj=". $ItemPos);
		$db->query("UPDATE KategorijeVsebina SET Polozaj=". $ItemPos ." WHERE KategorijaID='". $db->escape($_GET['KategorijaID']) ."' AND Ekstra=". (int)$_GET['Ekstra'] ." AND Polozaj=9999");
	}
	$db->query("COMMIT");
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace("/\&Smer=[-0-9]+/", "", $_SERVER['QUERY_STRING']);
	$_SERVER['QUERY_STRING'] = preg_replace("/\&Predloga=[0-9]+/", "", $_SERVER['QUERY_STRING']);
}

$ACLID = $db->get_var("SELECT ACLID FROM Kategorije WHERE KategorijaID = '". $db->escape($_GET['KategorijaID']) ."'");
if ( $ACLID )
	$ACL = userACL($ACLID);
else
	$ACL = "LRWDX";

switch ( $_GET['Ekstra'] ) {
	case 0 : $div = 'Ce'; break;
	case 1 : $div = 'De'; break;
	case 2 : $div = 'Le'; break;
}

// display list of assigned media
if ( isset($_GET['Find']) && $_GET['Find'] != "" ) {

	if ( $_GET['Find'] == "*" ) $_GET['Find'] = "";
	// seznam dodatnih besedil
	$List = $db->get_results(
		"SELECT P.PredlogaID, P.Jezik, P.Naziv, P.ACLID
		FROM Predloge P
			LEFT JOIN KategorijeVsebina KV
				ON P.PredlogaID = KV.PredlogaID AND KV.KategorijaID='". $db->escape($_GET['KategorijaID']) ."'
		WHERE P.Tip = ".(int)$_GET['Ekstra']."
			AND KV.PredlogaID IS NULL
			AND P.Enabled = 1".
			($_GET['Find']!=""? " AND (P.Naziv LIKE '%". $db->escape($_GET['Find']) ."%') ": " ").
		"ORDER BY P.Naziv"
	);

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	if ( !$List ) 
		echo "<TR><TD ALIGN=\"center\"><br><br>No data!<br><br></TD></TR>\n";
	else {
		$CurrentRow = 1;
		$RecordCount = count( $List );
		foreach ( $List as $Item ) {
			if ( $Item->ACLID )
				$rACL = userACL($Item->ACLID);
			else
				$rACL = "LRWDX";
			if ( contains($rACL,"L") ) {
				echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
				echo "<TD>";
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#div".$div."').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&Ekstra=".(int)$_GET['Ekstra']."&Dodaj=$Item->PredlogaID');\">";
				echo "<b>$Item->Naziv</b>";
				echo "</A>";
				echo "</TD>\n";
				echo "</TR>\n";
			}
			$CurrentRow++;
		}
	}
	echo "</TABLE>\n";

} else {

	// seznam dodatnih besedil
	$List = $db->get_results(
		"SELECT KV.ID, KV.KategorijaID, KV.PredlogaID, KV.Polozaj, KV.Ekstra,
			P.Naziv, P.Jezik, P.ACLID, P.Enabled
		FROM KategorijeVsebina KV
			LEFT JOIN Predloge P
				ON KV.PredlogaID = P.PredlogaID
		WHERE KV.KategorijaID = '". $db->escape($_GET['KategorijaID']) ."'
			AND KV.Ekstra = ". (int)$_GET['Ekstra'] ."
		ORDER BY KV.Polozaj"
		);

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\" WIDTH=\"99%\">\n";
	if ( !$List ) 
		echo "<TR><TD ALIGN=\"center\" COLSPAN=\"3\">No selected templates!</TD></TR>\n";
	else {
		$CurrentRow = 1;
		$RecordCount = count( $List );
		foreach ( $List as $Item ) {
			if ( $Item->ACLID )
				$rACL = userACL($Item->ACLID);
			else
				$rACL = "LRWDX";
			echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
			echo "<TD>";
			if ( contains($rACL,"R") )
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=sysTemplates&ID=$Item->PredlogaID');\">";
			if ( contains($rACL,"L") )
				echo $Item->Naziv;
			else
				echo "-- hidden template --";
			if ( contains($rACL,"R") )
				echo "</A>";
			if ( !$Item->Enabled )
				echo "*";
			echo "</TD>\n";
			echo "<TD ALIGN=\"right\" NOWRAP>\n";
			// move items up/down
			if ( contains($ACL,"W") ) {
				if ( $CurrentRow > 1 )
					echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#div".$div."').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&Ekstra=".(int)$_GET['Ekstra']."&Predloga=$Item->ID&Smer=-1');\"><IMG SRC=\"pic/list.up.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni gor\" BORDER=\"0\" CLASS=\"icon\"></A>";
				else
					echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
				if ( $CurrentRow < $RecordCount )
					echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#div".$div."').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&Ekstra=".(int)$_GET['Ekstra']."&Predloga=$Item->ID&Smer=1');\"><IMG SRC=\"pic/list.down.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni dol\" BORDER=\"0\" CLASS=\"icon\"></A>";
				else
					echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#div".$div."').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&Ekstra=".(int)$_GET['Ekstra']."&Odstrani=$Item->ID');\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" CLASS=\"icon\">\n";
			}
			echo "</TD>\n";
			echo "</TR>\n";
			$CurrentRow++;
		}
	}
}
?>
