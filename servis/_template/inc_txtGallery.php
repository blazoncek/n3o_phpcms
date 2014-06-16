<?php
/*~ inc_txtGallery.php - display&reorganize list of attached images
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

// delete image from list
if ( isset($_GET['BrisiSliko']) && $_GET['BrisiSliko'] != "" ) {
	$db->query("START TRANSACTION");
	$x = $db->get_row("SELECT BesediloID, MediaID FROM BesedilaSlike WHERE ID=". (int)$_GET['BrisiSliko']);
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
			'Remove image from text gallery',
			'". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". $x->BesediloID)
			.",". $db->get_var("SELECT Naziv FROM Media WHERE MediaID=". $x->MediaID) ."'
		)"
		);
	$db->query("DELETE FROM BesedilaSlike WHERE ID=". (int)$_GET['BrisiSliko']);
	$db->query("COMMIT");
}

// move items up/down
if ( isset($_GET['Smer']) && $_GET['Smer'] != "" ) {
	$db->query("START TRANSACTION");
	if ( $ItemPos = $db->get_var("SELECT Polozaj FROM BesedilaSlike WHERE ID = ". (int)$_GET['Slika']) ) {
		// calculate new position
		$ItemNew = $ItemPos + (int)$_GET['Smer'];
		// move
		$db->query("UPDATE BesedilaSlike SET Polozaj = 9999     WHERE BesediloID = ".(int)$_GET['BesediloID']." AND Polozaj = $ItemNew");
		$db->query("UPDATE BesedilaSlike SET Polozaj = $ItemNew WHERE BesediloID = ".(int)$_GET['BesediloID']." AND Polozaj = $ItemPos");
		$db->query("UPDATE BesedilaSlike SET Polozaj = $ItemPos WHERE BesediloID = ".(int)$_GET['BesediloID']." AND Polozaj = 9999");
	}
	$db->query("COMMIT");
}

$ACLID = $db->get_var("SELECT ACLID FROM Besedila WHERE BesediloID = ". (int)$_GET['BesediloID']);
if ( $ACLID )
	$ACL = userACL($ACLID);
else
	$ACL = "LRWDX";

// seznam slik 
$List = $db->get_results(
	"SELECT
		BS.ID,
		BS.MediaID,
		BS.BesediloID,
		M.Datoteka,
		M.Naziv,
		BS.Polozaj,
		M.Meta
	FROM
		BesedilaSlike BS
		LEFT OUTER JOIN Media M
			ON M.MediaID = BS.MediaID
	WHERE
		BS.BesediloID = ".(int)$_GET['BesediloID']."
	ORDER BY
		BS.Polozaj"
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

?>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
function checkImg(ID, Naziv) {
	if (confirm("Remove image '"+Naziv+"'?"))
		setTimeout("$('#divSlike').load('inc.php?Izbor=<?php echo $_GET['Izbor'] ?>&BesediloID=<?php echo $_GET['BesediloID'] ?>&pg=<?php echo $Page ?>&BrisiSliko="+ID+"')",100);
	return false;
}
//-->
</SCRIPT>
<?php

if ( !$List ) {
	echo "<div style=\"display: table;height: 100px;width: 100%;\">";
	echo "<div style=\"table-cell;text-align: center;vertical-align: middle;\">No data!</div>";
	echo "</div>\n";
} else {
	if ( $NuPg > 1 ) {
		echo "<DIV CLASS=\"pg\" style=\"text-align:center;\">\n";
		if ( $StPg > 1 )
			echo "<A HREF=\"javascript:void(0);\" onclick=\"$('#divSlike').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&pg=".($StPg-1)."');\">&laquo;</A>\n";
		if ( $Page > 1 )
			echo "<A HREF=\"javascript:void(0);\" onclick=\"$('#divSlike').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&pg=$PrPg');\">&lt;</A>\n";
		for ( $i = $StPg; $i <= $EdPg; $i++ ) {
			if ( $i == $Page )
				echo "<FONT COLOR=\"red\"><B>$i</B></FONT>\n";
			else
				echo "<A HREF=\"javascript:void(0);\" onclick=\"$('#divSlike').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&pg=$i');\">$i</A>\n";
		}
		if ( $Page < $EdPg )
			echo "<A HREF=\"javascript:void(0);\" onclick=\"$('#divSlike').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&pg=$NePg');\">&gt;</A>\n";
		if ( $NuPg > $EdPg )
			echo "<A HREF=\"javascript:void(0);\" onclick=\"$('#divSlike').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&pg=".($EdPg<$NuPg? $EdPg+1: $EdPg)."');\">&raquo;</A>\n";
		echo "</DIV>\n";
	}

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	$i = $StaR-1;
	while ( $i < $EndR ) {
		// get list item
		$Item = $List[$i++];
		echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
		echo "<TD ALIGN=\"right\" WIDTH=\"8%\">". $Item->Polozaj .".</TD>\n";
		echo "<TD><A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=Media&ID=$Item->MediaID');\">$Item->Naziv</A></TD>\n";
		echo "<TD ALIGN=\"right\" NOWRAP>\n";
		// move items up/down
		if ( contains($ACL,"W") ) {
			if ( $i > 1 )
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divSlike').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&pg=".$Page."&Slika=$Item->ID&Smer=-1');\" TITLE=\"Gor\"><IMG SRC=\"pic/list.up.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni gor\" BORDER=\"0\" CLASS=\"icon\"></A>";
			else
				echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
			if ( $i < $RecordCount )
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divSlike').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&pg=".$Page."&Slika=$Item->ID&Smer=1');\" TITLE=\"Dol\"><IMG SRC=\"pic/list.down.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni dol\" BORDER=\"0\" CLASS=\"icon\"></A>";
			else
				echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
			echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"javascript:checkImg('$Item->ID','$Item->Datoteka');\" TITLE=\"Delete\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" CLASS=\"icon\">\n";
		}
		echo "</TD>\n";
		echo "</TR>\n";
	}
	echo "</TABLE>\n";
}
?>
