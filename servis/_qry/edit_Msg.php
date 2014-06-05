<?php
/*~ edit_Msg.php - upload processing
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

require_once( "../inc/thumb/PhpThumb.inc.php" );

// image parameters
$x = $db->get_row(
	"SELECT
		ST.SifNaziv AS GalleryBase,
		S.SifNVal1 AS DefPicSize,
		S.SifNVal2 AS DefThumbSize,
		S.SifNVal3 AS MaxPicSize
	FROM
		Sifranti S
		LEFT JOIN SifrantiTxt ST ON S.SifrantID=ST.SifrantID
	WHERE
		S.SifrCtrl = 'BESE'
		AND S.SifrText = 'Gallery'
	ORDER BY
		ST.Jezik DESC"
);
// deafult values for image upload sisze
if ( $x ) {
	$GalleryBase  = $x->GalleryBase;
	$DefPicSize   = (int)$x->DefPicSize;
	$DefThumbSize = (int)$x->DefThumbSize;
	$MaxPicSize   = (int)$x->MaxPicSize;
} else {
	$GalleryBase  = 'gallery';
	$DefPicSize   = 640;
	$DefThumbSize = 128;
	$MaxPicSize   = 1024;
}

// determine file (sPath) and URL (rPath) path
$rPath = "media". ($GalleryBase=="" ? "" : "/". $GalleryBase) ."/". date("Y");
$sPath = str_replace("\\", "/", $StoreRoot ."/". $rPath);
$sPath = str_replace("//", "/", $sPath);

// create processed media folders
@mkdir($sPath."/thumbs",0777,true);
@mkdir($sPath."/large",0777,true);

// move uploaded file into media
if ( isset($_POST['upload']) ) {
	
	// adjust thumbnail size
	$T = min(128,max(64,abs(isset($_POST['T']) ? (int)$_POST['T'] : (int)$DefThumbSize)));
	if ( isset($_POST['T']) && (int)$_POST['T'] < 0 ) $T = -$T; // square thumbnail
	// adjust resized image size
	$R = min(1024,max(256,abs(isset($_POST['R']) ? (int)$_POST['R'] : (int)$DefPicSize)));
	
	// upload & resize image
	$photo = ImageResize(
		'->'. $StoreRoot .'/media/upload/'. $_POST['upload'], // uploaded image
		$sPath,    // upload path
		'thumbs/', // thumbnail prefix
		'large/',  // original image prefix
		array($R, $MaxPicSize), // reduced size
		$T,        // thumbnail size
		$jpgPct);  // JPEG quality

	if ( $photo ) {

		// set metadata
		$Meta = "f=". $rPath .";w=". $photo['iw'] .";h=". $photo['ih'] .";rw=". $photo['rw'] .";rh=". $photo['rh'] .";tw=". $photo['tw'] .";th=". $photo['th'] .";";
		
		$db->query(
			"INSERT INTO Media (
				Izpis,
				Datum,
				Naziv,
				Datoteka,
				Velikost,
				Tip,
				Meta
			) VALUES (
				1,
				'". date('Y-n-j H:m:s') ."',
				'". $photo['name'] ."',
				'". $rPath .'/'. $photo['name'] ."',
				". $photo['size'] .",
				'PIC',
				'". $Meta ."'
			)"
		);
		
		//remove original
		@unlink($StoreRoot. '/media/upload/'.$_POST['upload']);
	} else {
		$Error = "Upload error!";
	}
}
?>