<?php
/*~ edit_Predloge.php - site template upload
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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "0";

if ( isset($_POST['Naziv']) && $_POST['Naziv'] !== "" ) {

	$db->query("START TRANSACTION");
	if ( $_GET['ID'] != "0" ) {
		$db->query(
			"UPDATE Predloge ".
			"SET Naziv = '".$db->escape($_POST['Naziv'])."',".
			"	Opis = '".$db->escape($_POST['Opis'])."',".
			"	Jezik = ".(($_POST['Jezik']!="")? "'".$db->escape($_POST['Jezik'])."'": "NULL").",".
			"	Datoteka = ".(($_POST['Datoteka']!="")? "'".$db->escape($_POST['Datoteka'])."'": "NULL").",".
			"	Tip = ".(isset($_POST['Tip'])? $_POST['Tip']: "0").",".
			"	Enabled = ".(isset($_POST['Enabled'])? "1": "0")." ".
			"WHERE PredlogaID = " . (int)$_GET['ID']
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
				". (int)$_GET['ID'] .",
				'Templates',
				'Update template',
				'". $db->escape($_POST['Naziv']) .",". $db->escape($_POST['Opis']) .",". $db->escape($_POST['Datoteka']) ."'
			)"
			);
	} else {
		$db->query(
			"INSERT INTO Predloge (".
			"	Naziv,".
			"	Opis,".
			"	Jezik,".
			"	Datoteka,".
			"	Tip,".
			"	Enabled".
			") VALUES (".
			"	'".$db->escape($_POST['Naziv'])."',".
			"	'".$db->escape($_POST['Opis'])."',".
			"	".(($_POST['Jezik']!="")? "'".$db->escape($_POST['Jezik'])."'": "NULL").",".
			"	".(($_POST['Datoteka']!="")? "'".$db->escape($_POST['Datoteka'])."'": "NULL").",".
			"	".(isset($_POST['Tip'])? $_POST['Tip']: "0").",".
			"	".(isset($_POST['Enabled'])? "1": "0").
			")"
			);

		// get inserted ID
		$_GET['ID'] = $db->insert_id;

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
				". (int)$_GET['ID'] .",
				'SMActions',
				'Add template',
				'". $db->escape($_POST['Naziv']) .",". $db->escape($_POST['Opis']) .",". $db->escape($_POST['Datoteka']) ."'
			)"
			);

		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace( "/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING'] ) . "&ID=" . $_GET['ID'];

		if ( isset($_GET['KategorijaID']) && $_GET['KategorijaID'] != "" ) {
			$Polozaj = (int)$db->get_var(
				"SELECT max(Polozaj)+1 ".
				"FROM KategorijeVsebina ".
				"WHERE KategorijaID = '".$_GET['KategorijaID']."' AND Ekstra =".(int)$_GET['Ekstra']
			);
			$db->query(
				"INSERT INTO KategorijeVsebina (PredlogaID, KategorijaID, Polozaj, Ekstra) ".
				"VALUES (".(int)$_GET['ID'].", '".$_GET['KategorijaID']."', ".(($Polozaj)? $Polozaj: "1").", ".(int)$_GET['Ekstra'].")"
			);
		}
	}
	$db->query("COMMIT");
}

if ( isset($_FILES['Dodaj']) ) {
	$uploadfile = $StoreRoot . "/template/_" . basename($_FILES['Dodaj']['name']);
	// upload file in $_FILES['Dodaj'] to ../template/ if .php
	if ( $_FILES['Dodaj']['error'] == 0 && contains(".php,html",strtolower(right($_FILES['Dodaj']['name'], 4))) ) {
		if ( !@move_uploaded_file($_FILES['Dodaj']['tmp_name'], $uploadfile) ) {
			$Error = "Upload error!";
		} else {
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
					'SMActions',
					'Upload template',
					'". basename($_FILES['Dodaj']['name']) ."'
				)"
				);
		}
	}
}
?>