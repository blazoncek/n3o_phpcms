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
	$db->query("START TRANSACTION");
	$Polozaj = $db->get_var("SELECT max(Polozaj) FROM BesedilaSkupine WHERE BesediloID = ". (int)$_GET['BesediloID']);
	$db->query(
		"INSERT INTO BesedilaSkupine (BesediloID, DodatniID, Polozaj)
		VALUES (". (int)$_GET['BesediloID'] .", ". (int)$_GET['DodatniID'] .", ". ($Polozaj ? $Polozaj+ 1: 1) .")"
		);
	$Polozaj = $db->get_var("SELECT max(Polozaj) FROM BesedilaSkupine WHERE BesediloID = ". (int)$_GET['DodatniID']);
	$db->query(
		"INSERT INTO BesedilaSkupine (BesediloID, DodatniID, Polozaj) ".
		"VALUES (". (int)$_GET['DodatniID'] .", ". (int)$_GET['BesediloID'] .", ". ($Polozaj ? $Polozaj+1 : 1) .")"
		);
	$db->query("COMMIT");
}

// delete additional text from list
if ( isset( $_GET['BrisiDodatni'] ) && $_GET['BrisiDodatni'] != "" ) {
	$db->query("START TRANSACTION");
	$x = $db->get_row("SELECT BesediloID, DodatniID FROM BesedilaSkupine WHERE ID = ". (int)$_GET['BrisiDodatni']);
	if ( $x ) $db->query("DELETE FROM BesedilaSkupine WHERE BesediloID = $x->DodatniID AND DodatniID = $x->BesediloID");
	$db->query("DELETE FROM BesedilaSkupine WHERE ID = ". (int)$_GET['BrisiDodatni']);
	$db->query("COMMIT");
}

// move items up/down
if ( isset($_GET['Smer']) && $_GET['Smer'] != "" ) {
	$db->query("START TRANSACTION");
	if ( $ItemPos = $db->get_var("SELECT Polozaj FROM BesedilaSkupine WHERE ID = ". (int)$_GET['Dodatni']) ) {
		// calculate new position
		$ItemNew = $ItemPos + (int)$_GET['Smer'];
		// move
		$db->query("UPDATE BesedilaSkupine SET Polozaj = 9999     WHERE BesediloID = ".(int)$_GET['BesediloID']." AND Polozaj = $ItemNew");
		$db->query("UPDATE BesedilaSkupine SET Polozaj = $ItemNew WHERE BesediloID = ".(int)$_GET['BesediloID']." AND Polozaj = $ItemPos");
		$db->query("UPDATE BesedilaSkupine SET Polozaj = $ItemPos WHERE BesediloID = ".(int)$_GET['BesediloID']." AND Polozaj = 9999");
	}
	$db->query("COMMIT");
}

$Tip = $db->get_var("SELECT Tip FROM Besedila WHERE BesediloID = ". (int)$_GET['BesediloID']);

$ACLID = $db->get_var("SELECT ACLID FROM Besedila WHERE BesediloID = ". (int)$_GET['BesediloID']);
if ( $ACLID )
	$ACL = userACL($ACLID);
else
	$ACL = "LRWDX";

?>
<script language="JavaScript" type="text/javascript">
<!-- //
$('#edit').live('pageinit', function(event){
	// handle field changes
	$("input[name='Find']").change(function(e){
		e.preventDefault();
		var fObj = this;	// input object
		if (fObj.value.length==0) {return false;}
		URL = '<?php echo $_SERVER['PHP_SELF'] ?>?Izbor=<?php echo $_GET['Izbor'] ?>&BesediloID=<?php echo $_GET['BesediloID'] ?>';
		$.mobile.changePage(URL, {
			reloadPage: true,
			type: "get",
			data: $(this).serialize() // this.name+'='+this.value
		});
		return false;
	});
});
//-->
</script>
<?php

// page head
echo "<div id=\"edit\" data-role=\"page\" data-title=\"Related texts\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Related texts</h1>\n";
echo "<a href=\"edit.php?Izbor=Besedila&ID=". $_GET['BesediloID'] ."\" title=\"Back\" data-role=\"button\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-ajax=\"false\" data-transition=\"slide\">Back</a>\n";
echo "<a href=\"./\" title=\"Home\" class=\"ui-btn-right\" data-role=\"button\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Home</a>\n";
echo "</div>\n";
echo "<div data-role=\"content\">\n";

if ( isset($_GET['Find']) && $_GET['Find'] != "" ) {

	// display search results
	if ( $_GET['Find'] == "*" ) $_GET['Find'] = "";
	$List = $db->get_results(
		"SELECT DISTINCT
			B.BesediloID AS ID,
			B.Tip,
			B.Ime,
			B.ACLID
		FROM
			Besedila B
			LEFT JOIN BesedilaOpisi BO ON B.BesediloID = BO.BesediloID
			LEFT JOIN BesedilaSkupine BS ON B.BesediloID = BS.DodatniID AND BS.BesediloID = ". (int)$_GET['BesediloID'] ."
		WHERE
			BS.BesediloID IS NULL
			AND B.BesediloID <> ". (int)$_GET['BesediloID'] ."
			AND B.Tip = '". (isset($Tip) ? $Tip : "Text") ."'
			AND B.Izpis = 1".
			($_GET['Find']!=""? " AND (BO.Naslov LIKE '%". $_GET['Find'] ."%' OR B.Ime LIKE '%". $_GET['Find'] ."%' OR B.Tip LIKE '". $_GET['Find'] ."%')" : " ").
		"ORDER BY
			B.Ime"
	);

	echo "<ul data-role=\"listview\" data-theme=\"d\">\n";
	echo "<li data-theme=\"c\"><input type=\"text\" name=\"Find\" value=\"". $_GET['Find'] ."\" placeholder=\"Find\"></li>\n";
	if ( !$List ) 
		echo "<li data-theme=\"c\">No data!</li>\n";
	else {
		foreach ( $List as $Item ) {
			if ( $Item->ACLID )
				$rACL = userACL($Item->ACLID);
			else
				$rACL = "LRWDX";
			echo "<li data-icon=\"check\">";
			echo (contains($ACL,"W") ? "<a href=\"". $_SERVER['PHP_SELF'] ."?Izbor=". $_GET['Izbor'] ."&BesediloID=". $_GET['BesediloID'] ."&DodatniID=". $Item->ID ."\" data-ajax=\"false\" data-theme=\"c\">" : "");
			echo $Item->Ime;
			echo (contains($ACL,"W") ? "</a>" : "");
			echo "</li>\n";
		}
	}
	echo "</ul>\n";

} else {

	// seznam dodatnih besedil
	$List = $db->get_results(
		"SELECT
			BS.ID,
			BS.DodatniID,
			BS.Polozaj,
			B.Ime,
			B.ACLID
		FROM BesedilaSkupine BS
			LEFT JOIN Besedila B ON BS.DodatniID = B.BesediloID
		WHERE
			BS.BesediloID = ". (int)$_GET['BesediloID'] ."
		ORDER BY
			BS.BesediloID, BS.Polozaj"
		);

	echo "<ul data-role=\"listview\" data-theme=\"d\">\n";
	echo "<li data-theme=\"c\"><input type=\"text\" name=\"Find\" placeholder=\"Find\"></li>\n";
	$CurrentRow  = 1;
	$RecordCount = count($List);
	foreach ( $List as $Item ) {
		echo "<li data-icon=\"delete\">";
		echo (contains($ACL,"D") ? "<a href=\"". $_SERVER['PHP_SELF'] ."?Izbor=". $_GET['Izbor'] ."&BesediloID=". $_GET['BesediloID'] ."&BrisiDodatni=". $Item->ID ."\" data-ajax=\"false\" data-theme=\"c\">" : "");
		echo $Item->Ime;
		echo (contains($ACL,"D") ? "</a>": "");
		// move items up/down
		if ( contains($ACL,"W") ) {
			echo "<span class=\"ui-li-count ui-icon-nodisc ui-icon-alt\" style=\"margin-top: -1.5em;\">";
			if ( $CurrentRow > 1 )
				echo "<a href=\"inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&Dodatni=$Item->ID&Smer=-1\" title=\"Gor\" class=\"ui-btn-right\" data-role=\"button\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"arrow-u\" data-inline=\"true\" data-theme=\"c\">Gor</a>";
			if ( $CurrentRow < $RecordCount )
				echo "<a href=\"inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&Dodatni=$Item->ID&Smer=1\" title=\"Dol\" class=\"ui-btn-right\" data-role=\"button\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"arrow-d\" data-inline=\"true\" data-theme=\"c\">Dol</A>";
			echo "</span>";
		}
		echo "</li>\n";
		$CurrentRow++;
	}
	echo "</ul>\n";

}
echo "</div>\n";
?>
