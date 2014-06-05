<?php
/*~ upload_file.php - upload a file and update DB (drag&drop support)
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

// include application variables and settings framework
require_once("../_application.php");

// if file was successfuly uploaded
if ( isset($_FILES['file']) && !$_FILES['file']['error'] ) {

	// upload file in $_FILES['file'] to ../media
	$Datoteka   = strtolower(str_replace(' ','-',CleanString(basename($_FILES['file']['name']))));
	$path       = 'media';
	$uploadfile = $StoreRoot .'/'. $path .'/'. $Datoteka;
	
	if ( @move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile) ) {
		$Size = filesize($uploadfile);
		$Tip  = strtoupper(right($Datoteka,3));

		$db->query( "START TRANSACTION" );
		$db->query(
			"INSERT INTO Media (".
			"	Naziv,".
			"	Datoteka,".
			"	Velikost,".
			"	Tip,".
			"	Slika,".
			"	Datum,".
			"	Izpis,".
			"	Meta".
			") VALUES (".
			"	'". $db->escape($Datoteka) ."',".
			"	'". $db->escape($Datoteka) ."',".
			"	$Size,".
			"	'$Tip',".
			"	NULL,".
			"	'".date("Y-m-d H:i:s")."',".
			"	1,".
			"	NULL )"
		);
		// get inserted ID
		$id = $db->insert_id;
		
		// attach uploaded file to text
		if ( isset($_GET['bid']) && (int)$_GET['bid'] != 0 ) {
			$Polozaj = $db->get_var("SELECT max(Polozaj) FROM BesedilaMedia WHERE BesediloID = ". (int)$_GET['bid']);
			$db->query(
				"INSERT INTO BesedilaMedia (BesediloID, MediaID, Polozaj) ".
				"VALUES (". (int)$_GET['bid'] .",". $id .",".($Polozaj? $Polozaj+1: 1).")" );
		}
		
		// attach uploaded file to category
		if ( isset($_GET['kid']) && $_GET['kid'] != '' ) {
			$Polozaj = $db->get_var("SELECT max(Polozaj)+1 FROM KategorijeMedia WHERE KategorijaID='". $db->escape($_GET['kid']) ."'");
			$db->query(
				"INSERT INTO KategorijeMedia (".
				"	KategorijaID,".
				"	MediaID,".
				"	Polozaj".
				") VALUES (".
				"	'". $db->escape($_GET['kid']) ."',".
				"	". $id .",".
				"	".(($Polozaj)? $Polozaj: "1")." )"
			);
		}
		$db->query( "COMMIT" );
		
		echo json_encode(array('files' => array(
				'name' => $Datoteka,
				'size' => $Size,
				'type' => $Tip
			)));
	} else {
		echo json_encode(array('files' => array(
				'error' => "Upload error!"
			)));
	}
} else {
	echo json_encode(array('files' => array(
			'error' => "Invalid upload."
		)));
}
?>