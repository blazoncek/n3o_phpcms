<?php
/*~ edit_Media.php - media metadata editing
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

// if (new) file was successfuly uploaded
if ( isset($_FILES['Dodaj']) && !$_FILES['Dodaj']['error'] ) {

	$Datoteka   = strtolower(str_replace(' ','-',CleanString(basename($_FILES['Dodaj']['name']))));
	$ext        = right($Datoteka, 4);

	// upload file in $_FILES['Dodaj'] to ../media
	$path       = 'media/media';
	$uploadfile = $StoreRoot .'/'. $path .'/'. $Datoteka;
	
	// move file
	if ( contains(".gif,.jpg,.png", $ext) ) {
	
		// upload file in $_FILES['Dodaj'] to ../media
		$path       = 'media/'. ($GalleryBase==''? '' : $GalleryBase .'/') . date("Y");
		$UPLOADpath = $StoreRoot .'/'. $path;
		
		// create upload folders
		@mkdir($UPLOADpath, 0777, true);
		@mkdir($UPLOADpath ."/large");
		@mkdir($UPLOADpath ."/thumbs");

		// adjust thumbnail size
		$T = min(128,max(64,abs(isset($_POST['T'])?(int)$_POST['T']:(int)$DefThumbSize)));
		if ( isset($_POST['S']) && strtolower($_POST['S'])=='yes' )
			$T = -$T; // square thumbnail
		// adjust resized image size
		$R = min(1024,max(256,abs(isset($_POST['R'])?(int)$_POST['R']:(int)$DefPicSize)));

		// upload & resize image
		$photo = ImageResize(
			'Dodaj',     // $_FILE field
			$UPLOADpath, // upload path
			'thumbs/',   // thumbnail prefix
			'large/',    // original image prefix
			array($R,$MaxPicSize), // reduced size
			$T,          // thumbnail size
			$jpgPct);    // JPEG quality

		if ( $photo ) { // successful upload & resize
			$Datoteka = $path .'/'. $photo['name'];
			$Size = $photo['size'];
			$Tip  = 'PIC';
			// set metadata
			$_POST['Meta'] = "f=". $path .";w=". $photo['iw'] .";h=". $photo['ih'] .";rw=". $photo['rw'] .";rh=". $photo['rh'] .";tw=". $photo['tw'] .";th=". $photo['th'] .";";
		} else {
			$Error    = "Upload error!";
			$Datoteka = null;
		}
	} else if ( @move_uploaded_file($_FILES['Dodaj']['tmp_name'], $uploadfile) ) {
		$Size = filesize($uploadfile);
		$Tip  = strtoupper(right($Datoteka,3));
	} else {
		$Error    = "Upload error!";
		$Datoteka = null;
	}
}

// resize thumbnail & image from large
if ( isset($_POST['ObnoviSliko']) ) {
	// adjust thumbnail size
	$T = min(128,max(48,isset($_POST['T'])?(int)$_POST['T']:(int)$DefThumbSize));
	// adjust resized image size
	$M = min(1024,max(320,isset($_POST['R'])?(int)$_POST['R']:(int)$DefPicSize));
	// square?
	$S = isset($_POST['S']) && strtolower($_POST['S'])=="yes" ? true : false;

	$Slika = $db->get_var( "SELECT Datoteka FROM Media WHERE MediaID = ".(int)$_GET['ID'] );
	$Slika = $StoreRoot .'/'. $Slika;
	$path  = dirname($Slika);
	$ext   = strrchr(basename($Slika), '.'); // extension
	$name  = left(basename($Slika), strlen(basename($Slika))-strlen($ext));

	$largefile = $path .'/large/'. $name . $ext;

	// resize image
	try {
		if ( is_file($largefile) ) {
			$thumb = PhpThumbFactory::create($largefile, array('jpegQuality' => $jpgPct,'resizeUp' => false));
			$size = $thumb->getCurrentDimensions();
			$i_width = $size['width'];
			$i_height = $size['height'];
			// if image size is smaller than required maximum copy large file
			if ( $size['width'] <= $M && $size['height'] <= $M ) {
				// copy uploaded file to base directory
				if ( !@copy($largefile, $path .'/'. $name . $ext) )
					throw new RuntimeException ("File copy error!");
			} else {
				// resize image
				if ( $size['width'] > $M*2 || $size['height'] > $M*2 ) // "retina" sized image
					$thumb->resize($M*2, $M*2)->save($path .'/'. $name .'@2x'. $ext);
				$thumb->resize($M, $M)->save($path .'/'. $name . $ext);
				$size = $thumb->getCurrentDimensions();
			}
		} else {
			$thumb = PhpThumbFactory::create($path .'/'. $name . $ext, array('jpegQuality' => $jpgPct,'resizeUp' => false));
			$size = $thumb->getCurrentDimensions();
			$i_width = $size['width'];
			$i_height = $size['height'];
		}
		$r_width = $size['width'];
		$r_height = $size['height'];

		// generate thumbnail
		$t_width = $t_height = 0;
		if ( $T > 0 ) {
			if ( $r_width > $T*2 || $r_height > $T*2 ) {
				// "retina" sized thumbnail
				if ( $S )
					$thumb->adaptiveResize($T*2,$T*2)->save($path .'/thumbs/'. $name .'@2x'. $ext);
				else
					$thumb->resize($T*2,$T*2)->save($path .'/thumbs/'. $name .'@2x'. $ext);
			}
			if ( $S )
				$thumb->adaptiveResize($T, $T)->save($path .'/thumbs/'. $name . $ext);
			else
				$thumb->resize($T, $T)->save($path .'/thumbs/'. $name . $ext);
			$size = $thumb->getCurrentDimensions();
			$t_width = $size['width'];
			$t_height = $size['height'];
		}

		// get file size
		$stat = stat($Slika);
		$_POST['Velikost'] = $Size = (int)$stat['size'];
		$path = substr(dirname($Slika), strlen($StoreRoot)-strlen(dirname($Slika)));
		$Tip  = 'PIC';
		
		// set metadata
		$_POST['Meta'] = "f=". $path .";w=$i_width;h=$i_height;rw=$r_width;rh=$r_height;tw=$t_width;th=$t_height;";
	} catch (Exception $e) {
		$Error    = "Resize error!";
	}
}

// remove old image
if ( isset($_POST['BrisiSliko']) || (isset($_FILES['file']) && !$_FILES['file']['error']) ) {
	$Slika = $db->get_var( "SELECT Slika FROM Media WHERE MediaID = ".(int)$_GET['ID'] );
	if ( $Slika && $Slika != "" ) {
		$imgpath = $StoreRoot ."/media/media";
		$e = right($Slika, 4);
		$b = left($Slika, strlen($Slika)-4);
		@unlink($imgpath .'/'. $Slika);               // remove image
		@unlink($imgpath .'/'. $b .'@2x'. $e);        // remove retina image
		@unlink($imgpath .'/thumbs/'. $Slika);        // remove thumbnail
		@unlink($imgpath .'/thumbs/'. $b .'@2x'. $e); // remove retina thumbnail
		@unlink($imgpath .'/large/'. $Slika);         // remove large original
		$db->query( "UPDATE Media SET Slika = NULL WHERE MediaID = ".(int)$_GET['ID'] );
	}
	unset( $Slika );
}

// if thumbnail image/icon was successfuly uploaded
if ( isset($_FILES['file']) && !$_FILES['file']['error'] ) {

	// create directories
	$imagepath = $StoreRoot .'/media/media';
	@mkdir($imagepath, 0777, true);

	$photo = ImageResize(
		'file',    // $_FILE field
		$imagepath, // upload path
		'',         // thumbnail prefix
		'',         // original image prefix
		0,          // don't resized size
		0,          // no thumbnail size
		$jpgPct);   // JPEG quality

	if ( $photo ) {
		$Slika = $photo['name'];
		$db->query( "UPDATE Media SET Slika = '".$Slika."' WHERE MediaID = ".(int)$_GET['ID'] );
	} else {
		$Error = "Upload error!";
	}
}

if ( !isset($Error) && count($_POST) && !isset($_POST['Naslov']) ) {
	$db->query( "START TRANSACTION" );
	
	if ( $_GET['ID'] != "0" ) {

		// update record
		if ( !isset($_POST['Izpis']) && !contains($_SERVER['PHP_SELF'],'upd.php') )
			$db->query("UPDATE Media SET Izpis = 0 WHERE MediaID = ".(int)$_GET['ID']);

		foreach ( $_POST as $name => $value ) {
			if ( contains("BrisiSliko,ObnoviSliko,S,T,R,",$name.',') ) continue; //ignore
			switch ( $name ) {
				//case "numerical": // numerical value
				//	$set = ($value!="" ? (int)$value : "NULL");
				//	break;
				case "Izpis": // logical value
					$set = ($value=="yes" ? 1 : 0);
					break;
				//case "Password":
				//	$set = ($value!="" ? "'".$db->escape(MD5(PWSALT.$value))."'" : "NULL"); // salted
				//	break;
				case "Naziv":
					$file    = $db->get_var("SELECT Datoteka FROM Media WHERE MediaID = ". (int)$_GET['ID']);
					$path    = dirname($StoreRoot .'/'. $file); // full path
					$ext     = strtolower(strrchr(basename($file), '.')); // extension
					$oldname = left(basename($file), strlen(basename($file))-strlen($ext)); // old name
					$newname = strtolower(str_replace(' ','-',CleanString(preg_replace('/(gif|jpg|png)$/i','',$value),true))); // new name from input field
					// rename file
					if ( !is_file($path .'/'. $newname.$ext) && rename($path .'/'. $oldname.$ext, $path .'/'. $newname.$ext) ) {
						// if file is a picture
						if ( contains(".gif,.jpg,.png", $ext) ) {
							// replace display name
							$value = $newname.$ext;
							// adjust retina name
							if ( is_file($path .'/'. $oldname.'@2x'.$ext) )
								rename($path .'/'. $oldname.'@2x'.$ext, $path .'/'. $newname.'@2x'.$ext);
							// adjust thumbnail name
							if ( is_file($path .'/thumbs/'. $oldname.$ext) )
								rename($path .'/thumbs/'. $oldname.$ext, $path .'/thumbs/'. $newname.$ext);
							if ( is_file($path .'/thumbs/'. $oldname.'@2x'.$ext) )
								rename($path .'/thumbs/'. $oldname.'@2x'.$ext, $path .'/thumbs/'. $newname.'@2x'.$ext);
							// adjust original image name
							if ( is_file($path .'/large/'. $oldname.$ext) )
								rename($path .'/large/'. $oldname.$ext, $path .'/large/'. $newname.$ext);
							if ( is_file($path .'/large/'. $oldname.'@2x'.$ext) )
								rename($path .'/large/'. $oldname.'@2x'.$ext, $path .'/large/'. $newname.'@2x'.$ext);
						}
						// update DB
						$db->query("UPDATE Media SET Datoteka = '". dirname($file).'/'.$newname.$ext ."' WHERE MediaID = ".(int)$_GET['ID']);
					}
					// continue updating DB (with default)
				default : // string value
					$set = ($value!="" ? "'".$db->escape($value)."'" : "NULL");
					break;
			}
			$db->query("UPDATE Media SET $name = $set WHERE MediaID = ".(int)$_GET['ID']);
		}

	} else {

		// insert new record
		$db->query("
			INSERT INTO Media (
				Naziv,
				Datoteka,
				Velikost,
				Tip,
				Slika,
				Datum,
				Izpis,
				Meta
			) VALUES (
				'". $db->escape($_POST['Naziv']) ."',
				'". $Datoteka ."',
				$Size,
				'$Tip',
				". (isset($Slika)? "'".$Slika."'": "NULL") .",
				'". date("Y-m-d H:i:s") ."',
				". (isset($_POST['Izpis'])? "1": "0") .",
				". (($_POST['Meta']!="")? "'".$db->escape($_POST['Meta'])."'": "NULL") ."
			)");
		// get inserted ID
		$_GET['ID'] = $db->insert_id;
		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace( "/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING'] ) . "&ID=" . $_GET['ID'];

		if ( isset($_POST['KategorijaID']) ) {
			$Polozaj = $db->get_var( "SELECT max(Polozaj)+1 FROM KategorijeMedia WHERE KategorijaID='".$_POST['KategorijaID']."'" );
			$db->query("
				INSERT INTO KategorijeMedia (
					KategorijaID,
					MediaID,
					Polozaj
				) VALUES (
					'". $_POST['KategorijaID'] ."',
					". (int)$_GET['ID'] .",
					". (($Polozaj)? $Polozaj: "1") ."
				)");
		}
	}
	$db->query("COMMIT");
}

if ( isset($_GET['BrisiOpis']) && $_GET['BrisiOpis'] != "" ) {
	$db->query( "DELETE FROM MediaOpisi WHERE ID = " . (int) $_GET['BrisiOpis'] );
}

// VPISOVANJE PODATKOV O JEZIKOVNIH VARIANTAH
if ( isset($_POST['Naslov']) ) {
	// cleanup
	$_POST['Naslov'] = str_replace( "\"", "'", $_POST['Naslov'] );
	$Opis = $_POST['Opis'];
	$Opis = str_replace( "&nbsp;", " ", $Opis );
	$Opis = str_replace( "&scaron;", "š", $Opis );
	$Opis = str_replace( "&Scaron;", "Š", $Opis );
	$Opis = preg_replace( "/(SRC=\")\.\.\//i", '$1./', $Opis );
	$Opis = preg_replace( "/<([\/]*)EM>/i", '<$1I>', $Opis );
	$Opis = preg_replace( "/<([\/]*)STRONG>/i", '<$1B>', $Opis );
	$_POST['Opis'] = $db->escape( $Opis );

	if ( isset($_POST['OpisID']) ) {
		$db->query("
			UPDATE MediaOpisi
			SET Naslov = ". (($_POST['Naslov']!="")? "'". $_POST['Naslov'] ."'": "NULL") .",
				Opis = ". (($_POST['Opis']!="")? "'". $_POST['Opis'] ."'": "NULL") ."
			WHERE ID = ". (int)$_POST['OpisID']
			);
	} else {
		$db->query("
			INSERT INTO MediaOpisi (
				Jezik,
				MediaID,
				Naslov,
				Opis
			) VALUES (
				". (($_POST['Jezik']!="")? "'".$db->escape($_POST['Jezik'])."'": "NULL") .",
				". (int)$_GET['ID'] .",
				". (($_POST['Naslov']!="")? "'". $_POST['Naslov'] ."'": "'(neimenovan)'") .",
				". (($_POST['Opis']!="")? "'". $_POST['Opis'] ."'": "NULL") ."
			)");
	}
}
?>