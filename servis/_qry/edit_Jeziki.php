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

if ( isset($_POST['Opis']) && $_POST['Opis'] != "" ) {

	if ( isset($_FILES['Slika']) ) {
		// remove old image
		if ( isset($_POST['S1']) ) {
			@unlink($StoreRoot ."/pic/". $_POST['S1']);
		}
//		$Slika = $db->get_var("SELECT Slika FROM Jeziki WHERE Jezik = '". $_GET['Brisi'] ."'");
//		if ( $Slika && $Slika != "" ) {
//			@unlink( $StoreRoot . "/pic/" . $Slika );
//		}
		// if file was uploaded (only .jpg, .gif & .png extensions accepted)
		if ( $_FILES['Slika']['error'] == 0 && contains(".gif,.jpg,.png", right($_FILES['Slika']['name'], 4)) ) {
			// upload file in $_FILES['Slika'] to ../pic/
			$Slika = strtolower(basename($_FILES['Slika']['name']));
			$uploadfile = $StoreRoot . "/pic/" . $Slika;
			// check if image already exists and assign random name if it does
			while ( is_file($uploadfile) ) {
				$Slika = "rfn". rand(10000,99999) . right($_FILES['Slika']['name'], 4);
				$uploadfile = $StoreRoot ."/pic/". $Slika;
			}
			// move file and resize image
			if ( @move_uploaded_file($_FILES['Slika']['tmp_name'], $uploadfile) ) {
				// resize image
				//try {
				//	$thumb = PhpThumbFactory::create($uploadfile, array('jpegQuality' => $jpgPct,'resizeUp' => false));
				//	$thumb->resize(32, 32)->save($uploadfile);
				//} catch (Exception $e) {
				//	$Error = "Resize error!";
				//	@unlink($uploadfile);
				//	$Slika = null;
				//}
			} else {
				$Error = "Upload error!";
				$Slika = null;
			}
		}
	}

	$db->query("START TRANSACTION");
	if ( isset($_POST['DefLang']) )
		$db->query("UPDATE Jeziki SET DefLang = 0");

	if ( $_GET['ID'] != "" && $_GET['ID'] != "0" ) {
		$db->query(
			"UPDATE Jeziki ".
			"SET Opis = '".$db->escape($_POST['Opis'])."',".
			(isset($Slika)? "Ikona = '".$Slika."',": "").
			"	Enabled = ".(isset($_POST['Izpis'])? "1": "0").",".
			"	CharSet = ".(($_POST['CharSet']!="")? "'".$db->escape($_POST['CharSet'])."'": "NULL").",".
			"	LangCode = ".(($_POST['LangCode']!="")? "'".$db->escape($_POST['LangCode'])."'": "NULL").",".
			"	DefLang = ".(isset($_POST['DefLang'])? "1": "0")." ".
			"WHERE Jezik = '".$_POST['Jezik']."'" );
	} else {
		$db->query(
			"INSERT INTO Jeziki (".
			"	Jezik,".
			"	Opis,".
			"	CharSet,".
			"	LangCode,".
			"	Ikona,".
			"	Enabled,".
			"	DefLang".
			") VALUES (".
			"	'".$_POST['Jezik']."',".
			"	'".$db->escape($_POST['Opis'])."',".
			"	".(($_POST['CharSet']!="")? "'".$db->escape($_POST['CharSet'])."'": "NULL").",".
			"	".(($_POST['LangCode']!="")? "'".$db->escape($_POST['LangCode'])."'": "NULL").",".
			"	".(isset($Slika)? "'".$Slika."'": "NULL").",".
			"	".(isset($_POST['Izpis'])? "1": "0").",".
			"	".(isset($_POST['DefLang'])? "1": "0").
			")" );
		$_GET['ID'] = $_POST['Jezik'];
	}
	$db->query("COMMIT");
}
?>