<?php
/*~ inc_catTexts.php - kategorije content
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
if ( isset($_GET['BesediloID']) && $_GET['BesediloID'] != "" ) {
	$db->query("START TRANSACTION");
	$Polozaj = $db->get_var("SELECT max(Polozaj) FROM KategorijeBesedila WHERE KategorijaID='". $db->escape($_GET['KategorijaID']) ."'");
	$db->query(
		"INSERT INTO KategorijeBesedila (BesediloID, KategorijaID, Polozaj) ".
		"VALUES (". (int)$_GET['BesediloID'] .",'". $db->escape($_GET['KategorijaID']) ."',". ($Polozaj? $Polozaj+1: 1) .")"
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
			'Attach text',
			'". $db->get_var("SELECT Ime FROM Kategorije WHERE KategorijaID='". $db->escape($_GET['KategorijaID']) ."'")
			.",". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". (int)$_GET['BesediloID']) ."'
		)"
		);
	$db->query("COMMIT");
}

// remove category
if ( isset($_GET['Odstrani']) && $_GET['Odstrani'] != "" ) {
	$db->query("START TRANSACTION");
	$x = $db->get_row("SELECT KategorijaID, BesediloID FROM KategorijeBesedila WHERE ID=". (int)$_GET['Odstrani']);
	$db->query("DELETE FROM KategorijeBesedila WHERE ID=".(int)$_GET['Odstrani']);
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
			'Remove text',
			'". $db->get_var("SELECT Ime FROM Kategorije WHERE KategorijaID='". $x->KategorijaID ."'")
			.",". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". $x->BesediloID) ."'
		)"
		);
	$db->query("COMMIT");
}

// move items up/down
if ( isset($_GET['Smer']) && $_GET['Smer'] != "" ) {
	$db->query("START TRANSACTION");
	if ( $ItemPos = $db->get_var("SELECT Polozaj FROM KategorijeBesedila WHERE ID = ". (int)$_GET['Predmet']) ) {
		// calculate new position
		$ItemNew = $ItemPos + (int)$_GET['Smer'];
		// move
		$db->query("UPDATE KategorijeBesedila SET Polozaj = 9999     WHERE KategorijaID = '".$_GET['KategorijaID']."' AND Polozaj = $ItemNew");
		$db->query("UPDATE KategorijeBesedila SET Polozaj = $ItemNew WHERE KategorijaID = '".$_GET['KategorijaID']."' AND Polozaj = $ItemPos");
		$db->query("UPDATE KategorijeBesedila SET Polozaj = $ItemPos WHERE KategorijaID = '".$_GET['KategorijaID']."' AND Polozaj = 9999");
	}
	$db->query("COMMIT");
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&Smer=[-0-9]+/", "", $_SERVER['QUERY_STRING'] );
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&Predmet=[0-9]+/", "", $_SERVER['QUERY_STRING'] );
}

$ACLID = $db->get_var("SELECT ACLID FROM Kategorije WHERE KategorijaID = '".$_GET['KategorijaID']."'");
if ( $ACLID )
	$ACL = userACL($ACLID);
else
	$ACL = "LRWDX";

// display list of assigned texts
if ( isset($_GET['Find']) && $_GET['Find'] != "" ) {

	if ( $_GET['Find'] == "*" ) $_GET['Find'] = "";
	$List = $db->get_results(
		"SELECT B.BesediloID, B.Ime, B.Tip, B.ACLID, KB.ID
		FROM Besedila B 
			LEFT JOIN KategorijeBesedila KB
				ON B.BesediloID = KB.BesediloID AND KB.KategorijaID = '".$_GET['KategorijaID']."' ".
		($_GET['Find']!=""? "WHERE (B.Ime LIKE '%".$_GET['Find']."%' OR B.Tip LIKE '".$_GET['Find']."%')": "").
		"ORDER BY B.Ime"
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
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divBe').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&BesediloID=$Item->BesediloID');\">";
			echo $Item->Ime;
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
		"SELECT KB.ID, KB.KategorijaID, B.BesediloID, B.Ime, B.Tip, B.ACLID, B.Izpis ".
		"FROM KategorijeBesedila KB ".
		"	LEFT JOIN Besedila B ON KB.BesediloID = B.BesediloID ".
		"WHERE KategorijaID = '".$_GET['KategorijaID']."'
		ORDER BY KB.Polozaj DESC"
	);

	$RecordCount = count($List);
	
	// are we requested do display different page?
	$Page = !isset($_GET['pg']) ? 1 : (int)$_GET['pg'];
	
	// number of possible pages
	$NuPg = (int) (($RecordCount-1) / $MaxRows) + 1;
	
	// fix page number if out of limits
	$Page = min(max($Page, 1), $NuPg);
	
	// start & end page
	$StPg = min(max(1, $Page - 5), max(1, $NuPg - 10));
	$EdPg = min($StPg + 10, min($Page + 10, $NuPg));
	
	// previous and next page numbers
	$PrPg = $Page - 1;
	$NePg = $Page + 1;
	
	// start and end row from recordset
	$StaR = ($Page - 1) * $MaxRows + 1;
	$EndR = min(($Page * $MaxRows), $RecordCount);

	if ( !$List ) {
		echo "<div style=\"display: table;height: 100px;width: 100%;\">";
		echo "<div style=\"table-cell;text-align: center;vertical-align: middle;\">No data!</div>";
		echo "</div>\n";
	} else {
		if ( $NuPg > 1 ) {
			echo "<DIV CLASS=\"pg\" style=\"text-align:center;\">\n";
			if ( $StPg > 1 )
				echo "<A HREF=\"javascript:void(0);\" onclick=\"$('#divBe').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&pg=".($StPg-1)."');\">&laquo;</A>\n";
			if ( $Page > 1 )
				echo "<A HREF=\"javascript:void(0);\" onclick=\"$('#divBe').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&pg=$PrPg');\">&lt;</A>\n";
			for ( $i = $StPg; $i <= $EdPg; $i++ ) {
				if ( $i == $Page )
					echo "<FONT COLOR=\"red\"><B>$i</B></FONT>\n";
				else
					echo "<A HREF=\"javascript:void(0);\" onclick=\"$('#divBe').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&pg=$i');\">$i</A>\n";
			}
			if ( $Page < $EdPg )
				echo "<A HREF=\"javascript:void(0);\" onclick=\"$('#divBe').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&pg=$NePg');\">&gt;</A>\n";
			if ( $NuPg > $EdPg )
				echo "<A HREF=\"javascript:void(0);\" onclick=\"$('#divBe').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&pg=".($EdPg<$NuPg? $EdPg+1: $EdPg)."');\">&raquo;</A>\n";
			echo "</DIV>\n";
		}
	
		echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
		$i = $StaR-1;
		while ( $i < $EndR ) {
			// get list item
			$Item = $List[$i++];

			$rACL = userACL($Item->ACLID);
			echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
			echo "<TD>";
			if ( contains($rACL,"R") )
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=Text&ID=$Item->BesediloID');\">";
			if ( contains($rACL,"L") )
				echo $Item->Ime;
			else
				echo "-- hidden text --";
			if ( contains($rACL,"R") )
				echo "</A>";
			if ( !$Item->Izpis )
				echo "*";
			echo "</TD>\n";

			echo "<TD ALIGN=\"right\" NOWRAP WIDTH=\"40\">";
			// move items up/down
			if ( contains($ACL,"W") ) {
				if ( $i > 1 )
					echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divBe').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&pg=".$Page."&Predmet=$Item->ID&Smer=1');\"><IMG SRC=\"pic/list.up.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni gor\" BORDER=\"0\" CLASS=\"icon\"></A>";
				else
					echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
				if ( $i < $RecordCount )
					echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divBe').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&pg=".$Page."&Predmet=$Item->ID&Smer=-1');\"><IMG SRC=\"pic/list.down.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni dol\" BORDER=\"0\" CLASS=\"icon\"></A>";
				else
					echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divBe').load('inc.php?Izbor=".$_GET['Izbor']."&KategorijaID=".$_GET['KategorijaID']."&pg=".$Page."&Odstrani=$Item->ID');\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" CLASS=\"icon\"></A>";
			}
			echo "</TD>\n";
			echo "</TR>\n";
		}
		echo "</TABLE>\n";
	}
}
?>
