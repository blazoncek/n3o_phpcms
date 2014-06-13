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

if ( isset($_GET['Brisi']) && $_GET['Brisi'] != "" ) {
	$db->query("START TRANSACTION");

	// delete image file
	$Slika = $db->get_var("SELECT Slika FROM Besedila WHERE BesediloID = ".(int)$_GET['Brisi']);
	if ( $Slika && $Slika != "" ) {
		$e = right($Slika, 4);
		$b = left($Slika, strlen($Slika)-4);
		@unlink($StoreRoot ."/media/besedila/". $Slika);
		@unlink($StoreRoot ."/media/besedila/". $b .'@2x'. $e);
	}

	// delete ACL
	$ACLID = $db->get_var("SELECT ACLID FROM Besedila WHERE BesediloID = ".(int)$_GET['Brisi']);
	if ( $ACLID ) {
		$db->query("DELETE FROM SMACLr WHERE ACLID = $ACLID");
		$db->query("DELETE FROM SMACL  WHERE ACLID = $ACLID");
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
			". (int)$_GET['Brisi'] .",
			'Text',
			'Delete text',
			'". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". (int)$_GET['Brisi']) ."'
		)"
		);

	// delete data
	$db->query("DELETE FROM KategorijeBesedila WHERE BesediloID = ". (int)$_GET['Brisi']);
	$db->query("DELETE FROM BesedilaSlike      WHERE BesediloID = ". (int)$_GET['Brisi']);
	$db->query("DELETE FROM BesedilaOpisi      WHERE BesediloID = ". (int)$_GET['Brisi']);
	$db->query("DELETE FROM Besedila           WHERE BesediloID = ". (int)$_GET['Brisi']);

	$db->query("COMMIT");
}

if ( isset($_GET['Smer']) && $_GET['Smer'] != "" ) {
	$db->query("START TRANSACTION");
	$KatID = $db->get_var("SELECT KategorijaID FROM KategorijeBesedila WHERE ID = " . (int)$_GET['kbID']);
	$Staro = $db->get_var("SELECT Polozaj      FROM KategorijeBesedila WHERE ID = " . (int)$_GET['kbID']);
	$Novo  = $Staro + (int)$_GET['Smer'];
	@$db->query("UPDATE KategorijeBesedila SET Polozaj = 9999   WHERE KategorijaID = '$KatID' AND Polozaj = $Novo");
	@$db->query("UPDATE KategorijeBesedila SET Polozaj = $Novo  WHERE KategorijaID = '$KatID' AND Polozaj = $Staro");
	@$db->query("UPDATE KategorijeBesedila SET Polozaj = $Staro WHERE KategorijaID = '$KatID' AND Polozaj = 9999");
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
			'Text',
			'Move text position',
			'". $KatID .",". $Staro ."->". $Novo .",". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=(SELECT BesediloID FROM KategorijeBesedila WHERE ID=". (int)$_GET['kbID'] .")") ."'
		)"
		);
	$db->query("COMMIT");
}
?>