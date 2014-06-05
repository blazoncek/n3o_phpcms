<?php
/*~ edit_Kategorije.php - menu/site structure
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

// get first available top level menu ID
if ( !isset($_GET['ID']) || $_GET['ID'] == "" || $_GET['ID'] === "0" ) {
	$KatID = $db->get_var( "SELECT max(KategorijaID) FROM Kategorije WHERE KategorijaID LIKE '__'" );

	$_GET['ID'] = sprintf( "%0".strlen($KatID)."d", (int)$KatID + 1 );
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING'] ) . "&ID=" . $_GET['ID'];
}

// remove old image
if ( isset($_POST['BrisiSliko']) || (isset($_FILES['file']) && !$_FILES['file']['error']) ) {
	$Slika    = $db->get_var( "SELECT Slika FROM Kategorije WHERE KategorijaID = '".$db->escape($_GET['ID'])."'" );
	if ( $Slika && $Slika != "" ) {
		$imgpath = $StoreRoot ."/media/rubrike/";
		$e = right($Slika, 4);
		$b = left($Slika, strlen($Slika)-4);
		@unlink($imgpath .'/'. $Slika);               // remove image
		@unlink($imgpath .'/'. $b .'@2x'. $e);        // remove retina image
		@unlink($imgpath .'/thumbs/'. $Slika);        // remove thumbnail
		@unlink($imgpath .'/thumbs/'. $b .'@2x'. $e); // remove retina thumbnail
		@unlink($imgpath .'/large/'. $Slika);         // remove large original
		$db->query( "UPDATE Kategorije SET Slika=NULL WHERE KategorijaID = '".$db->escape($_GET['ID'])."'" );
	}
	unset($Slika);
}

// if file was uploaded (only .jpg, .gif & .png extensions accepted)
if ( (isset($_FILES['file']) && !$_FILES['file']['error']) ) {

	// create directories
	$imagepath = $StoreRoot .'/media/rubrike';
	@mkdir($imagepath, 0777, true);

	$photo = ImageResize(
		'file',     // $_FILE field
		$imagepath, // upload path
		'',         // thumbnail prefix
		'',         // original image prefix
		0,          // don't resize
		0,          // no thumbnail
		$jpgPct);   // JPEG quality

	if ( $photo ) {
		$Slika = $photo['name'];
		$db->query( "UPDATE Kategorije SET Slika='". $Slika ."' WHERE KategorijaID = '". $db->escape($_GET['ID']) ."'" );
	} else {
		$Error = "Upload error!";
	}
}

// vpis nove/popravek kategorije
if ( isset($_POST['Ime']) && $_POST['Ime'] != "" ) {

	// cleanup Ime (used for permalinks)
	$_POST['Ime'] = (left($_POST['Ime'],1)=='.' ? '.' : '') . CleanString($_POST['Ime'], true);
	$_POST['Ime'] = str_replace(' ','-',$_POST['Ime']);

	$db->query( "START TRANSACTION" );
	$KatID  = $db->get_var( "SELECT KategorijaID FROM Kategorije WHERE KategorijaID = '".$_GET['ID']."'" );
	if ( !$KatID ) {
		$db->query(
			"INSERT INTO Kategorije (
				KategorijaID,
				Izpis,
				Iskanje,
				Ime,
				Slika
			) VALUES (
				'".$_GET['ID']."',
				".(isset($_POST['Izpis'])? 1: 0).",
				".(isset($_POST['Iskanje'])? 1: 0).",
				".(($_POST['Ime']!="")? "'".$db->escape(left($_POST['Ime'],32))."'": "'(neimenovan)'").",
				".(isset($Slika)? "'".$Slika."',": "NULL")."
			)"
		);
	} else {
		$db->query(
			"UPDATE Kategorije
			SET Izpis = ".(isset($_POST['Izpis'])? 1: 0).",".
			"	Iskanje = ".(isset($_POST['Iskanje'])? 1: 0).",".
				(isset($Slika)? "Slika = '".$Slika."',": (isset($_POST['BrisiSliko'])? "Slika=NULL,": "")).
			"	Ime = ".(($_POST['Ime']!="")? "'".$db->escape(left($_POST['Ime'],64))."'": "'(neimenovan)'")." ".
			"WHERE KategorijaID = '".$_GET['ID']."'"
		);
	}
	$db->query( "COMMIT" );
}

//delete title/description
if ( isset($_GET['BrisiOpis']) ) {
	$db->query( "DELETE FROM KategorijeNazivi WHERE ID = ".(int)$_GET['BrisiOpis'] );
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&BrisiOpis=[0-9]+/", "", $_SERVER['QUERY_STRING'] );
}

// adding category description
if ( isset($_POST['Naziv']) && $_POST['Naziv'] != "" ) {
	// cleanup
	$_POST['Naziv']    = $db->escape(str_replace( "\"", "&quot;", $_POST['Naziv'] ));
	$_POST['Povzetek'] = $db->escape(str_replace( "\"", "&quot;", $_POST['Povzetek'] ));
	$_POST['Opis']     = str_replace("\\\"","\"",$db->escape(CleanupTinyMCE($_POST['Opis'])));

	// note: adding image no longer supported
	if ( isset($_POST['OpisID']) ) {
		$db->query(
			"UPDATE KategorijeNazivi
			SET Naziv = ".(($_POST['Naziv']!="")? "'".left($_POST['Naziv'],128)."'": "'(neimenovan)'").",
				Povzetek = ".(($_POST['Povzetek']!="")? "'".left($_POST['Povzetek'],511)."'": "NULL").",
				Opis = ".(($_POST['Opis']!="")? "'".$_POST['Opis']."'": "NULL")."
			WHERE ID = ".(int)$_POST['OpisID']
		);
	} else {
		$db->query(
			"INSERT INTO KategorijeNazivi (
				Jezik,
				KategorijaID,
				Naziv,
				Povzetek,
				Opis
			) VALUES (
				".(($_POST['Jezik']!="")? "'".$_POST['Jezik']."'": "NULL").",
				'".$_GET['ID']."',
				".(($_POST['Naziv']!="")? "'".left($_POST['Naziv'],128)."'": "'(neimenovan)'").",
				".(($_POST['Povzetek']!="")? "'".left($_POST['Povzetek'],511)."'": "NULL").",
				".(($_POST['Opis']!="")? "'".$_POST['Opis']."'": "NULL")."
			)"
		);
	}
}
?>
