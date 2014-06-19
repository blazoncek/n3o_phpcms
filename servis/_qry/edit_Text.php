<?php
/*~ edit_Besedila.php - text metadata editing (queries and image manipulation)
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
if ( !isset($_GET['Tip']) ) $_GET['Tip'] = "Text";

// remove old image
if ( isset($_POST['BrisiSliko']) || (isset($_FILES['file']) && !$_FILES['file']['error']) ) {
	$Slika    = $db->get_var("SELECT Slika FROM Besedila WHERE BesediloID = ".(int)$_GET['ID']);
	if ( $Slika && $Slika != "" ) {
		$imgpath = $StoreRoot ."/media/besedila";
		$e = right($Slika, 4);
		$b = left($Slika, strlen($Slika)-4);
		@unlink($imgpath .'/'. $Slika);               // remove image
		@unlink($imgpath .'/'. $b .'@2x'. $e);        // remove retina image
		@unlink($imgpath .'/thumbs/'. $Slika);        // remove thumbnail
		@unlink($imgpath .'/thumbs/'. $b .'@2x'. $e); // remove retina thumbnail
		@unlink($imgpath .'/large/'. $Slika);         // remove large original

		$db->query("START TRANSACTION");
		if ( isset($_POST['BrisiSliko']) ) {
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
					'Text',
					'Remove text image',
					'". $Slika .",". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". (int)$_GET['ID']) ."'
				)"
				);
		}
		$db->query("UPDATE Besedila SET Slika=NULL WHERE BesediloID=". (int)$_GET['ID']);
		$db->query("COMMIT");
	}
	unset($Slika);
}

// if file was uploaded (only .jpg, .gif & .png extensions accepted)
if ( (isset($_FILES['file']) && !$_FILES['file']['error']) ) {

	// create directories
	$imagepath = $StoreRoot .'/media/besedila';
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
		$db->query("START TRANSACTION");
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
				'Text',
				'Upload text image',
				'". $Slika .",". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". (int)$_GET['ID']) ."'
			)"
			);
		$db->query("UPDATE Besedila SET Slika = '". $Slika ."' WHERE BesediloID = ". (int)$_GET['ID']);
		$db->query("COMMIT");
	} else {
		$Error = "Upload error!";
	}
}

// if no error and we did post something
if ( !isset($Error) && count($_POST) ) {
	
	// cleanup Ime (used for permalinks)
	if ( isset($_POST['Ime']) ) {
		$_POST['Ime'] = CleanString($_POST['Ime'], true);
		$_POST['Ime'] = str_replace(' ','-',$_POST['Ime']);
		$_POST['Ime'] = preg_replace('/\-+/','-',$_POST['Ime']); // reduce multiple dashes
	}

	// if we have WYSIWYG editor cleanup text
	if ( isset($_POST['Naslov']) ) {
		$_POST['Naslov']    = $db->escape(str_replace( "\"", "&quot;", $_POST['Naslov'] ));
		$_POST['Podnaslov'] = $db->escape(str_replace( "\"", "&quot;", $_POST['Podnaslov'] ));
		$_POST['Povzetek']  = $db->escape(str_replace( "\"", "&quot;", $_POST['Povzetek'] ));
		$_POST['Opis']      = str_replace("\\\"","\\&quot;",$db->escape(CleanupTinyMCE($_POST['Opis'])));
	}

	// update database
	$db->query("START TRANSACTION");
	if ( $_GET['ID'] != "0" ) {

		// update metadata
		if ( isset($_POST['BrisiSliko']) ) $db->query("UPDATE Besedila SET Slika = NULL WHERE BesediloID = ". (int)$_GET['ID']);
		if ( isset($Slika) )               $db->query("UPDATE Besedila SET Slika = '".$Slika."' WHERE BesediloID = ". (int)$_GET['ID']);
		if ( !isset($_POST['Izpis']) && !contains($_SERVER['PHP_SELF'],'upd.php') )
			$db->query("UPDATE Besedila SET Izpis = 0 WHERE BesediloID = ".(int)$_GET['ID']);

		foreach ( $_POST as $name => $value ) {
			if ( contains("BrisiSliko,file,Jezik,Naslov,Podnaslov,Povzetek,Opis,newtag,deltag,",$name.',') ) continue; //ignore
			switch ( $name ) {
				case "ForumTopicID":
					$set = ($value!="" ? (int)$value : "NULL");
					break;
				case "Izpis": // logical value
					$set = ($value=="yes" ? 1 : 0);
					break;
				case "Center": // logical value
					$set = ($value=="yes" ? 1 : 0);
					break;
				//case "Password":
				//	$set = ($value!="" ? "'".$db->escape(MD5(PWSALT.$value))."'" : "NULL"); // salted
				//	break;
				case "Ime":
					$set = ($value!="" ? "'".$db->escape(left($value,128))."'" : "'neimenovan-".rand(10000,99999)."'");
					break;
				case "Tip":
					$set = ($value!="" ? "'".$db->escape($value)."'" : "'Besedilo'");
					break;
				case "Datum": // date value
					$set = "'". date("Y-m-d",strtotime($value)) ."'";
					break;
				default : // string value
					$set = ($value!="" ? "'".$db->escape($value)."'" : "NULL");
					break;
			}
			$db->query( "UPDATE Besedila SET $name = $set WHERE BesediloID = ".(int)$_GET['ID'] );
		}
		$db->query("UPDATE Besedila SET DatumSpremembe = '". date('Y-m-d H:i:s') ."' WHERE BesediloID = ". (int)$_GET['ID']);

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
				'Text',
				'Update text',
				'". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". (int)$_GET['ID']) ."'
			)"
			);

	} else {

		// if text can have comments (SifLVal1=1) create new comment topic in forum named as text type
		$Comments = $db->get_var("SELECT SifLVal1 FROM Sifranti WHERE SifrCtrl = 'BESE' AND SifrText = '". $_POST['Tip'] ."'");
		if ( $Comments && $_POST['ForumTopicID'] == "" ) {
			/// get thread ID
			$ForumID = $db->get_var("SELECT ID FROM frmForums WHERE ForumName = '". $_POST['Tip'] ."'");
			if ( $ForumID ) {
				// create new topic
				$db->query(
					"INSERT INTO frmTopics (".
					"	ForumID,".
					"	TopicName,".
					"	MessageCount".
					") VALUES (".
					"	$ForumID,".
					"	".(($_POST['Ime']!="")? "'".$db->escape(left($_POST['Ime'],128))."'": "'neimenovan-".rand(10000,99999)."'").",".
					"	0 )"
				);
				// remember inserted topic ID
				$TopicID = $db->insert_id;
			}
		}
		// insert new record including rich text
		$db->query(
			"INSERT INTO Besedila (
				Datum,
				DatumObjave,
				DatumSpremembe,
				Izpis,
				Ime,
				Slika,
				URL,
				ForumTopicID,
				Avtor,
				Tip
			) VALUES (
				'". date("Y-m-d",strtotime($_POST['Datum'])) ."',
				'". date('Y-n-j H:i:s') ."',
				'". date('Y-n-j H:i:s') ."',
				". (isset($_POST['Izpis'])? 1: 0) .",
				'". ($_POST['Ime']!="" ? $db->escape(left($_POST['Ime'],64)) : "(unnamed)") ."',
				". (isset($Slika)? "'". $Slika ."'" : "NULL") .",
				". ($_POST['URL']!="" ? "'". $db->escape($_POST['URL']) ."'" : "NULL") .",
				". (isset($TopicID) ? $TopicID : "NULL") .",
				". $_SESSION['UserID'] .",
				". ($_POST['Tip']!="" ? "'". $db->escape($_POST['Tip']) ."'" : "'Text'")."
			)"
			);
		$ID = $db->insert_id;

		// rich text
		$db->query(
			"INSERT INTO BesedilaOpisi (
				BesediloID,
				Jezik,
				Polozaj,
				Naslov,
				Podnaslov,
				Povzetek,
				Opis
			) VALUES (
				". $ID .",
				". ($_POST['Jezik']!="" ? "'". $db->escape($_POST['Jezik']) ."'" : "NULL").",
				1,
				". ($_POST['Naslov']!="" ? "'". $db->escape($_POST['Naslov']) ."'" : "'(unnamed)'") .",
				". ($_POST['Podnaslov']!="" ? "'". left($_POST['Podnaslov'],128) ."'" : "NULL") .",
				". ($_POST['Povzetek']!="" ? "'". left($_POST['Povzetek'],511) ."'" : "NULL") .",
				". ($_POST['Opis']!="" ? "'". $db->escape($_POST['Opis']) ."'" : "NULL")."
			)"
			);

		// if category is included append text to category
		if ( isset($_POST['KategorijaID']) && $_POST['KategorijaID']!='' ) {
			$Polozaj = (int)$db->get_var("SELECT max(Polozaj) FROM KategorijeBesedila WHERE KategorijaID = '". $db->escape($_POST['KategorijaID']) ."'");
			$db->query(
				"INSERT INTO KategorijeBesedila (
					KategorijaID,
					BesediloID,
					Polozaj
				) VALUES (
					'". $db->escape($_POST['KategorijaID']) ."',
					". $ID .",
					". ($Polozaj ? $Polozaj+1 : 1) ."
				)"
				);
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
				". $ID .",
				'Text',
				'Add text',
				'". $db->escape(left($_POST['Ime'],64)) ."'
			)"
			);

		// get inserted ID
		$_GET['ID'] = $ID;
		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace("/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING']) ."&ID=". $_GET['ID'];
	}
	$db->query("COMMIT");
}

// delete text content (title & description)
if ( isset($_GET['BrisiOpis']) ) {
	$db->query("START TRANSACTION");
	$x = $db->get_row("SELECT BesediloID, Polozaj, Jezik, Naslov FROM BesedilaOpisi WHERE ID = ". (int)$_GET['BrisiOpis']);
	if ( $x ) {
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
				". $x->BesediloID .",
				'Text',
				'Delete text content',
				'". $x->Naslov ."'
			)"
			);
		$db->query("DELETE FROM BesedilaOpisi WHERE ID = ". (int)$_GET['BrisiOpis']);
		// optionally update subsequent rows
		$db->query(
			"UPDATE BesedilaOpisi
			SET Polozaj = Polozaj - 1
			WHERE BesediloID = ". $x->BesediloID ."
				AND Jezik ". ($x->Jezik ? "='". $x->Jezik ."'" : "IS NULL") ."
				AND Polozaj > ". $x->Polozaj
			);
	}
	$db->query("COMMIT");

	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&BrisiOpis=[0-9]+/", "", $_SERVER['QUERY_STRING'] );
}

// move items up/down
if ( isset($_GET['Smer']) && $_GET['Smer'] != "" ) {
	$db->query("START TRANSACTION");
	if ( $ItemPos = $db->get_var("SELECT Polozaj FROM BesedilaOpisi WHERE ID = ". (int)$_GET['Opis']) ) {
		// calculate new position
		$ItemNew = $ItemPos + (int)$_GET['Smer'];
		// move
		$db->query("UPDATE BesedilaOpisi SET Polozaj = 9999     WHERE BesediloID = ". (int)$_GET['ID'] ." AND Polozaj = $ItemNew");
		$db->query("UPDATE BesedilaOpisi SET Polozaj = $ItemNew WHERE BesediloID = ". (int)$_GET['ID'] ." AND Polozaj = $ItemPos");
		$db->query("UPDATE BesedilaOpisi SET Polozaj = $ItemPos WHERE BesediloID = ". (int)$_GET['ID'] ." AND Polozaj = 9999");
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
				'Text',
				'Move text content',
				'". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". (int)$_GET['ID']) ."'
			)"
			);
	}
	$db->query("COMMIT");

	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace("/\&Opis=[0-9]+/", "", $_SERVER['QUERY_STRING']);
	$_SERVER['QUERY_STRING'] = preg_replace("/\&Smer=[-0-9]+/", "", $_SERVER['QUERY_STRING']);
}
