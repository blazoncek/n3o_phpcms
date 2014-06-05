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

// add additional text
if ( isset($_GET['DodatniID']) && $_GET['DodatniID'] != "" ) {
	$db->query( "START TRANSACTION" );
	$Polozaj = $db->get_var( "SELECT max(Polozaj) FROM BesedilaSkupine WHERE BesediloID = ".(int)$_GET['BesediloID'] );
	$db->query(
		"INSERT INTO BesedilaSkupine (BesediloID, DodatniID, Polozaj) ".
		"VALUES (".(int)$_GET['BesediloID'].", ".(int)$_GET['DodatniID'].", ".($Polozaj? $Polozaj+1: 1).")" );
	$Polozaj = $db->get_var( "SELECT max(Polozaj) FROM BesedilaSkupine WHERE BesediloID = ".(int)$_GET['DodatniID'] );
	$db->query(
		"INSERT INTO BesedilaSkupine (BesediloID, DodatniID, Polozaj) ".
		"VALUES (".(int)$_GET['DodatniID'].", ".(int)$_GET['BesediloID'].", ".($Polozaj? $Polozaj+1: 1).")" );
	$db->query( "COMMIT" );
}

// delete additional text from list
if ( isset( $_GET['BrisiDodatni'] ) && $_GET['BrisiDodatni'] != "" ) {
	$db->query( "START TRANSACTION" );
	$x = $db->get_row( "SELECT BesediloID, DodatniID FROM BesedilaSkupine WHERE ID = ".(int)$_GET['BrisiDodatni'] );
	if ( $x ) $db->query( "DELETE FROM BesedilaSkupine WHERE BesediloID  = $x->DodatniID AND DodatniID = $x->BesediloID" );
	$db->query( "DELETE FROM BesedilaSkupine WHERE ID = ".(int)$_GET['BrisiDodatni'] );
	$db->query( "COMMIT" );
}

// move items up/down
if ( isset( $_GET['Smer'] ) && $_GET['Smer'] != "" ) {
	$db->query( "START TRANSACTION" );
	if ( $ItemPos = $db->get_var( "SELECT Polozaj FROM BesedilaSkupine WHERE ID = ". (int)$_GET['Dodatni'] ) ) {
		// calculate new position
		$ItemNew = $ItemPos + (int)$_GET['Smer'];
		// move
		$db->query( "UPDATE BesedilaSkupine SET Polozaj = 9999     WHERE BesediloID = ".(int)$_GET['BesediloID']." AND Polozaj = $ItemNew" );
		$db->query( "UPDATE BesedilaSkupine SET Polozaj = $ItemNew WHERE BesediloID = ".(int)$_GET['BesediloID']." AND Polozaj = $ItemPos" );
		$db->query( "UPDATE BesedilaSkupine SET Polozaj = $ItemPos WHERE BesediloID = ".(int)$_GET['BesediloID']." AND Polozaj = 9999" );
	}
	$db->query( "COMMIT" );
}

$Tip = $db->get_var( "SELECT Tip FROM Besedila WHERE BesediloID = ".(int)$_GET['BesediloID'] );

$ACLID = $db->get_var( "SELECT ACLID FROM Besedila WHERE BesediloID = ".(int)$_GET['BesediloID'] );
if ( $ACLID )
	$ACL = userACL( $ACLID );
else
	$ACL = "LRWDX";

echo "<script language=\"JavaScript\" type=\"text/javascript\">\n";
echo "<!-- //\n";
echo "function checkDodatno(ID, Naziv) {\n";
echo "\tif (confirm(\"Odstranim povezano besedilo '\"+Naziv+\"'?\"))\n";
echo "\t\tsetTimeout(\"$('#divSk').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&BrisiDodatni=\"+ID+\"')\",100);\n";
echo "\treturn false;\n";
echo "}\n";
echo "//-->\n";
echo "</script>\n";

// display list of assigned media
if ( isset($_GET['Find']) && $_GET['Find'] != "" ) {

	if ( $_GET['Find'] == "*" ) $_GET['Find'] = "";
	$List = $db->get_results(
		"SELECT DISTINCT B.BesediloID, B.Tip, B.Ime, B.ACLID
		FROM Besedila B
			LEFT JOIN BesedilaOpisi BO
				ON B.BesediloID = BO.BesediloID
			LEFT JOIN BesedilaSkupine BS
				ON B.BesediloID = BS.DodatniID AND BS.BesediloID = ".(int)$_GET['BesediloID']."
		WHERE BS.BesediloID IS NULL
			AND B.BesediloID <> ".(int)$_GET['BesediloID']."
			AND B.Tip = '".(isset($Tip)? $Tip: "Besedilo")."'
			AND B.Izpis = 1".
			($_GET['Find']!=""? " AND (BO.Naslov LIKE '%".$_GET['Find']."%' OR B.Ime LIKE '%".$_GET['Find']."%' OR B.Tip LIKE '".$_GET['Find']."%')": " ").
		"ORDER BY B.Ime"
	);

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	if ( !$List ) 
		echo "<TR><TD ALIGN=\"center\"><br><br>Ni podatkov!<br><br></TD></TR>\n";
	else {
		$CurrentRow = 1;
		$RecordCount = count( $List );
		foreach ( $List as $Item ) {
			if ( $Item->ACLID )
				$rACL = userACL( $Item->ACLID );
			else
				$rACL = "LRWDX";
			echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
			echo "<TD>";
			echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divSk').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".(int)$_GET['BesediloID']."&DodatniID=$Item->BesediloID');\">";
			echo "<b>$Item->Ime</b>";
			echo "</A>";
			echo "</TD>\n";
			echo "</TR>\n";
			$CurrentRow++;
		}
	}
	echo "</TABLE>\n";

} else {

	// seznam dodatnih besedil
	$List = $db->get_results(
		"SELECT BS.ID, BS.DodatniID, BS.Polozaj, B.Ime, B.ACLID ".
		"FROM BesedilaSkupine BS ".
		"	LEFT JOIN Besedila B ON BS.DodatniID = B.BesediloID ".
		"WHERE BS.BesediloID = ".(int)$_GET['BesediloID']." ".
		"ORDER BY BS.BesediloID, BS.Polozaj"
	);
?>
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="99%">
<?php if ( !$List ) : ?>
<TR><TD ALIGN="center" COLSPAN="3">Ni povezanih besedil!</TD></TR>
<?php else : ?>
	<?php
	$CurrentRow = 1;
	$RecordCount = count( $List );
	foreach ( $List as $Item ) {
		if ( $Item->ACLID )
			$rACL = userACL( $Item->ACLID );
		else
			$rACL = "LRWDX";
		echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
		echo "<TD ALIGN=\"right\" WIDTH=\"8%\">$Item->Polozaj.</TD>\n";
		echo "<TD>";
		if ( contains($rACL,"R") )
			echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=Besedila&ID=$Item->DodatniID');\">";
		if ( contains($rACL,"L") )
			echo $Item->Ime;
		else
			echo "-- skrito besedilo --";
		if ( contains($rACL,"R") )
			echo "</A>";
		echo "</TD>\n";
		echo "<TD ALIGN=\"right\" NOWRAP>\n";
		// move items up/down
		if ( contains($ACL,"W") ) {
			if ( $CurrentRow > 1 )
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divSk').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&Dodatni=$Item->ID&Smer=-1');\" TITLE=\"Gor\"><IMG SRC=\"pic/list.up.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni gor\" BORDER=\"0\" CLASS=\"icon\"></A>";
			else
				echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
			if ( $CurrentRow < $RecordCount )
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divSk').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&Dodatni=$Item->ID&Smer=1');\" TITLE=\"Dol\"><IMG SRC=\"pic/list.down.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni dol\" BORDER=\"0\" CLASS=\"icon\"></A>";
			else
				echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
			echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"javascript:checkDodatno('$Item->ID','$Item->Ime');\" TITLE=\"Briši\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Briši\" BORDER=\"0\" CLASS=\"icon\">\n";
		}
		echo "</TD>\n";
		echo "</TR>\n";
		$CurrentRow++;
	}
	?>
<?php endif ?>
</TABLE>
<?php
}
?>
