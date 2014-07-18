<?php
/*~ list_Msg.php - list email messages
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend and backend)                                 |
|   Version: 2.2.2                                                          |
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
require_once("../inc/pop3.php");
require_once("../inc/thumb/PhpThumb.inc.php");

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
if ( isset($_GET['upload']) ) {
	
	// adjust thumbnail size
	$T = min(128,max(64,abs(isset($_GET['T']) ? (int)$_GET['T'] : (int)$DefThumbSize)));
	if ( isset($_GET['T']) && (int)$_GET['T'] < 0 ) $T = -$T; // square thumbnail
	// adjust resized image size
	$R = min(1024,max(256,abs(isset($_GET['R']) ? (int)$_GET['R'] : (int)$DefPicSize)));
	
	// upload & resize image
	$photo = ImageResize(
		'->'. $StoreRoot .'/media/upload/'. $_GET['upload'], // uploaded image
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
		@unlink($StoreRoot. '/media/upload/'. $_GET['upload']);
	} else {
		$Error = "Upload error!";
	}

	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace('/\&upload=[^&"]+/', '', $_SERVER['QUERY_STRING']);
}

// delete uploaded file
if ( isset($_GET['delete']) ) {
	@unlink($StoreRoot ."/media/upload/". $_GET['delete']);
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace('/\&delete=[^&"]+/', '', $_SERVER['QUERY_STRING']);
}

// process mail message
if ( isset($_GET['process']) ) {
	// Connect
	$conn = new POP3;
	if ( @$conn->Connect($mailUser, $mailPass, $mailServer, 110, $mailSSL) ) {
		// get message
		$mail = $conn->GetMessage((int)$_GET['process']);

		// extract subject
		$subject = urldecode($mail['Subject']);

		// parse content
		$lines = explode( "\n", $mail['Body'], 4 );

		// insert info into Media table
		$db->query("START TRANSACTION");
		$db->query(
			"INSERT INTO Besedila (
				Ime,
				Datum,
				Izpis,
				Avtor
			) VALUES (
				'". substr($mail['Subject'], 32) ."',
				'". date('Y-n-j H:m:s') ."',
				1,
				". (int)$_SESSION['UserID'] ."
			)"
		);
		$ID = $db->insert_id;
		$db->query(
			"INSERT INTO BesedilaOpisi (
				BesediloID,
				Naslov,
				Podnaslov,
				Povzetek,
				Opis,
				Jezik,
				Polozaj
			) VALUES (
				$ID,
				'". $mail['Subject'] ."',
				'". $lines[0] ."',
				'". $lines[1] ."',
				'". $lines[2] ."',
				'". $lng ."',
				(SELECT ifnull(max(Polozaj)+1,1) FROM BesedilaOpisi WHERE BesediloID=$ID AND Jezik='". $lng ."')
			)"
		);
		$db->query("COMMIT");

		// extract attachments
		$files = $conn->GetMessageAttachments((int)$_GET['process']);
		foreach ( $files as $file ) {
			// get file extension
			$ext = strtolower(strrchr($file['filename'], '.'));

			// if attachment is image, move it to upload folder
			if ( contains(".gif,.jpg,.png", $ext) ) {

				// check if image already exists and assign random name if it does
				$sFile = basename($file['filename']);
				while ( is_file($StoreRoot ."/media/upload/". $sFile) )
					$sFile = "RFN". rand(10000,99999) . strrchr($sFile, '.');

				// save image
				try {
					$thumb = PhpThumbFactory::create($file['data'], array('jpegQuality' => $jpgPct,'resizeUp' => false), true);
					$thumb->save($StoreRoot ."/media/upload/". $sFile, substr($ext,-3));
				} catch (Exception $e) {
				}
			} // image
		} // attachments

		// remove message
		$conn->DeleteMessage((int)$_GET['process']);
		// disconnect
		$conn->Disconnect();
	}

	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace('/\&process=[^&"]+/', '', $_SERVER['QUERY_STRING']);
}

// remove mail message
if ( isset($_GET['remove']) ) {
	$conn = new POP3;
	//  Connect to POP3 server
	if ( @$conn->Connect($mailUser, $mailPass, $mailServer, 110, $mailSSL) ) {
		$conn->DeleteMessage((int)$_GET['remove']);
		//  We need to disconnect
		$conn->Disconnect();
	}
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace('/\&remove=[^&"]+/', '', $_SERVER['QUERY_STRING']);
}

// submenu object
echo "<div id=\"list\" data-role=\"page\" data-title=\"Notifications\">\n";

echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Notifications</h1>\n";
echo "<a href=\"./\" title=\"Home\" class=\"ui-btn-left\" data-iconpos=\"notext\" data-icon=\"home\" data-ajax=\"false\">Home</a>\n";
echo "</div>\n";

echo "<div data-role=\"content\">\n";

//  Connect to POP3 server
$conn = new POP3;

if ( @$conn->Connect($mailUser, $mailPass, $mailServer, 110, $mailSSL) ) {
	// get user's email (for filtering)
	$UserEmail = $db->get_var("SELECT Email FROM SMUser WHERE UserID = ". (int)$_SESSION['UserID']);

	// get list of messages
	$list = $conn->GetMessageList();

	if ( count($list) > 0 ) {
		echo "<ul data-role=\"listview\" data-theme=\"d\" data-split-icon=\"delete\" data-split-theme=\"d\">\n";
		foreach ( $list as $mail ) {
			
			// extract senders email address
			preg_match("/<([^>]+)>/", $mail['from'], $email);
			$email = substr($email[0], 1, strlen($email[0])-2);

			// find user's messages
			if ( !$mail['deleted'] && $email == $UserEmail ) {
				
				// message format: subject = message title, 1st line = subtitle, 2nd line = abstract, 3rd line = body
				$msgTitle = urldecode($mail['subject']);

				$msgBody = $conn->GetMessageBody( $mail['msgno'] );
				$lines = explode( "\n", $msgBody, 3 );
				$msgAbstract = $lines[1]; // subtitle

				$imageAsString = "";
				$att = $conn->GetMessageAttachments( $mail['msgno'] );
				foreach ( $att as $file ) {
					// extract first image
					if ( contains(".gif,.jpg,.png", strtolower(strrchr($file['filename'], '.'))) ) {
						// resize image
						try {
							$thumb = PhpThumbFactory::create($file['data'], array('jpegQuality' => $jpgPct,'resizeUp' => false), true);
							$thumb->adaptiveResize(120, 120);
							$imageAsString = $thumb->getImageAsString(); 
							break;
						} catch (Exception $e) {
						}
					}
				}
				echo "<li>";
				echo "<a href=\"inc.php?Izbor=Msg&message=" . $mail['msgno'] . "\" data-rel=\"dialog\" data-transition=\"slideup\">"; //open confirmation dialog
				echo $imageAsString != "" ? "<img src=\"data:image/png;base64,". base64_encode($imageAsString) ."\">" : "";
				echo "<h3>". $msgTitle ."</h3>";
				echo "<p>". $msgAbstract ."</p>";
				echo "</a>";
				echo "</li>\n";
			}
		}
		echo "</ul>\n";
	}
	//  We need to disconnect
	$conn->Disconnect();
}

// get uploaded files
$Slike = scandir( $StoreRoot . "/media/upload" );

if ( $Slike ) {
	echo "<ul data-role=\"listview\" data-theme=\"d\">\n";
	foreach ( $Slike As $Slika ) {
		if ( substr($Slika,1,1) == "." || !contains("jpg,png,gif", strtolower(right($Slika,3))) ) continue; // skip invalid
		
		$imageAsString = "";
		// resize image
		try {
			$thumb = PhpThumbFactory::create($StoreRoot ."/media/upload/". $Slika, array('jpegQuality' => $jpgPct,'resizeUp' => false) );
			$thumb->adaptiveResize(120, 120);
			$imageAsString = $thumb->getImageAsString(); 
		} catch (Exception $e) {
		}
		$stat = stat($StoreRoot ."/media/upload/". $Slika);
		echo "<li>";
		echo "<a href=\"inc.php?Izbor=Msg&file=". $Slika ."\" data-rel=\"dialog\" data-transition=\"slideup\">"; //open dialog
		echo $imageAsString != "" ? "<img src=\"data:image/png;base64,". base64_encode($imageAsString) ."\">" : "";
		echo "<h3>". $Slika ."</h3>";
		echo "<p>". date("Y-m-d H-i-s",$stat['mtime']) ."</p>";
		echo "</a>";
		echo "</li>\n";
	}
	echo "</ul>\n";
}

echo "</div>\n"; //content

echo "</div>\n"; // page
?>