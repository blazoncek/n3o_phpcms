<?php
/*~ upload_image.php - Uploading images via drag&drop
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

// check URL parameters
if ( !isset($_GET['p']) || !(isset($_FILES['file']) && !$_FILES['file']['error']) ) {
	echo json_encode(array('files'=>array(
			'error' => 'Invalid request!'
		)));
	die();
}

// adjust path for galleries
if ( left($_GET['p'],8) == 'gallery' )
	$_GET['p'] .= '/'. date("Y"); // add current year to path

// set base in media/ folder & prevent dir traversal & remove trailing /
$_GET['p']  = trim('media/'. str_replace('../','',$_GET['p']), '/');
$uploadpath = $StoreRoot .'/'. $_GET['p'];

// create directories
@mkdir($uploadpath, 0777, true);
if ( isset($_GET['t']) ) @mkdir($uploadpath."/thumbs");
if ( isset($_GET['s']) ) @mkdir($uploadpath."/large");

// limit thumbnail and resizing dimensions
if ( isset($_GET['t']) && (int)$_GET['t']!=0 ) //0 = no thumbnail
	$_GET['t'] = max(min(abs((int)$_GET['t']),128),64); // limit thumbnail
else
	$_GET['t'] = 0;

if ( isset($_GET['s']) )
	$_GET['s'] = max(min(abs((int)$_GET['s']),1024),256); // limit resized image
else
	$_GET['s'] = 0;

// do we want square thumbnail
$_GET['t'] *= (isset($_GET['sq']) && (strtolower($_GET['sq'])=='on' || strtolower($_GET['sq'])=='yes')) ? -1 : 1;

// upload & resize image
$photo = ImageResize(
	'file',      // $_FILE field
	$uploadpath, // upload path
	'thumbs/',   // thumbnail prefix
	'large/',    // original image prefix
	$_GET['s'],  // reduced size
	$_GET['t'],  // thumbnail size
	$jpgPct      // JPEG quality
	);

if ( $photo ) { // successful upload & resize

	// update DB media record
	if ( isset($_GET['mid']) ) {
		$db->query("BEGIN TRANSACTION");
		// remove old image
		$old = $db->get_var("SELECT Slika FROM Media WHERE MediaID = ". (int)$_GET['mid']);
		if ( $old && $old != "" ) {
			$e = right($old, 4);
			$b = left($old, strlen($old)-4);
			@unlink($uploadpath .'/'. $old); // remove image
			@unlink($uploadpath .'/'. $b .'@2x'. $e); // remove retina image
			@unlink($uploadpath .'/thumbs/'. $old); // remove thumbnail
			@unlink($uploadpath .'/thumbs/'. $b .'@2x'. $e); // remove retina thumbnail
			@unlink($uploadpath .'/large/'. $old); // remove large original
			$db->query("UPDATE Media SET Slika = NULL WHERE MediaID = ". (int)$_GET['mid']);
		}
		$db->query("UPDATE Media SET Slika = '". $photo['name'] ."' WHERE MediaID = ". (int)$_GET['mid']);
		$db->query("COMMIT");
	}

	// update DB category record
	if ( isset($_GET['kid']) ) {
		$db->query("BEGIN TRANSACTION");
		// remove old image
		$old = $db->get_var("SELECT Slika FROM Kategorije WHERE KategorijaID = '". $db->escape($_GET['kid']) ."'");
		if ( $old && $old != "" ) {
			$e = right($old, 4);
			$b = left($old, strlen($old)-4);
			@unlink($uploadpath .'/'. $old); // remove image
			@unlink($uploadpath .'/'. $b .'@2x'. $e); // remove retina image
			@unlink($uploadpath .'/thumbs/'. $old); // remove thumbnail
			@unlink($uploadpath .'/thumbs/'. $b .'@2x'. $e); // remove retina thumbnail
			@unlink($uploadpath .'/large/'. $old); // remove large original
			$db->query("UPDATE Kategorije SET Slika = NULL WHERE KategorijaID = '". $db->escape($_GET['kid']) ."'");
		}
		$db->query("UPDATE Kategorije SET Slika = '". $photo['name'] ."' WHERE KategorijaID = '". $db->escape($_GET['kid']) ."'");
		$db->query("COMMIT");
	}

	// update DB text record
	if ( isset($_GET['id']) ) {
		$db->query("BEGIN TRANSACTION");
		// remove old image
		$old = $db->get_var("SELECT Slika FROM Besedila WHERE BesediloID = ". (int)$_GET['id']);
		if ( $old && $old != "" ) {
			$e = right($old, 4);
			$b = left($old, strlen($old)-4);
			@unlink($uploadpath .'/'. $old); // remove image
			@unlink($uploadpath .'/'. $b .'@2x'. $e); // remove retina image
			@unlink($uploadpath .'/thumbs/'. $old); // remove thumbnail
			@unlink($uploadpath .'/thumbs/'. $b .'@2x'. $e); // remove retina thumbnail
			@unlink($uploadpath .'/large/'. $old); // remove large original
			$db->query("UPDATE Besedila SET Slika = NULL WHERE BesediloID = ".(int)$_GET['id']);
		}
		$db->query("UPDATE Besedila SET Slika = '". $photo['name'] ."' WHERE BesediloID = ". (int)$_GET['id']);
		$db->query("COMMIT");
	}

	// update DB gallery record
	if ( isset($_GET['gid']) ) {
		$db->query("BEGIN TRANSACTION");
		$polozaj = $db->get_var("SELECT max(Polozaj) FROM BesedilaSlike WHERE BesediloID = ". (int)$_GET['gid']);
		$db->query(
			"INSERT INTO Media (
				Naziv,
				Datoteka,
				Meta,
				Velikost,
				Tip,
				Slika,
				Datum,
				Izpis
			) VALUES (
				'". $photo['name'] ."',
				'". $db->escape($_GET['p']) .'/'. $photo['name'] ."',
				'f=". $db->escape($_GET['p']) .";w=". $photo['iw'] .";h=". $photo['ih'] .";rw=". $photo['rw'] .";rh=". $photo['rh'] .";tw=". $photo['tw'] .";th=". $photo['th'] ."',
				". $photo['size'] .",
				'PIC',
				NULL,
				'". date("Y-m-d H:i:s") ."',
				1
			)"
		);
		$db->query(
			"INSERT INTO BesedilaSlike (
				BesediloID,
				Polozaj,
				MediaID
			) VALUES (
				". (int)$_GET['gid'] .",
				". ($polozaj ? $polozaj+1 : 1) .",
				". $db->insert_id ."
			)"
		);
		$db->query("COMMIT");
	}

	// rename the file if text id known (embeded image)
	if ( isset($_GET['bid']) && (int)$_GET['bid'] ) {
		$n = strtolower($db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". (int)$_GET['bid']));
		$o = $photo['name'];
		$e = strrchr($o, '.');
		$b = left($o, strlen($o)-strlen($e));
		$i = 1;
		while ( is_file($uploadpath.'/'.$n.'_'.$i.$e) && $i<1000 ) {
			$i++;
		}
		$n = $n.'_'.$i;

		if ( rename($uploadpath.'/'.$b.$e, $uploadpath.'/'.$n.$e) ) {
			$photo['name'] = $n.$e;
			if ( is_file($uploadpath.'/'.$b.'@2x'.$e) )        rename($uploadpath.'/'.$b.'@2x'.$e, $uploadpath.'/'.$n.'@2x'.$e);
			if ( is_file($uploadpath.'/thumbs/'.$b.$e) )       rename($uploadpath.'/thumbs/'.$b.$e, $uploadpath.'/thumbs/'.$n.$e);
			if ( is_file($uploadpath.'/thumbs/'.$b.'@2x'.$e) ) rename($uploadpath.'/thumbs/'.$b.'@2x'.$e, $uploadpath.'/thumbs/'.$n.'@2x'.$e);
			if ( is_file($uploadpath.'/large/'.$b.$e) )        rename($uploadpath.'/large/'.$b.$e, $uploadpath.'/large/'.$n.$e);
		}
	}

	echo json_encode(array('files'=>array(
			'name'  => $photo['name'],
			'path'  => $_GET['p'],
			'size'  => $photo['size']
		)));
} else {
	// error during upload or resize
	echo json_encode(array('files'=>array(
			'error' => 'Error uploading or resizing image!'
		)));
}
