<?php
/*~ inc_Msg.php - confirmation dialog
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



require_once( "../inc/pop3.php" );
require_once( "../inc/thumb/PhpThumb.inc.php" );

if ( isset($_GET['file']) ) {
	echo "<div data-role=\"page\">\n";

	echo "<div data-role=\"header\" data-theme=\"a\">\n";
	echo "<h1>Datoteka</h1>\n";
	echo "</div>\n";

	echo "<div data-role=\"content\">\n";
	$imageAsString = "";
	// resize image
	try {
		$thumb = PhpThumbFactory::create($StoreRoot ."/media/upload/". $_GET['file'], array('jpegQuality' => $jpgPct,'resizeUp' => false));
		$thumb->adaptiveResize(120, 120);
		$imageAsString = $thumb->getImageAsString(); 
	} catch (Exception $e) {
	}
	echo "<form action=\"list.php?Izbor=Msg\" method=\"get\" data-ajax=\"false\">\n";
	//echo "<h3>". $_GET['file'] ."</h3>\n";
	echo $imageAsString != "" ? "<p align=\"center\"><img src=\"data:image/png;base64,". base64_encode($imageAsString) ."\"></p>" : "";
	echo "<input type=\"hidden\" name=\"Izbor\" value=\"Msg\">";
	echo "<input type=\"hidden\" name=\"upload\" value=\"". $_GET['file'] ."\">";
	echo "<input name=\"T\" value=\"". $DefThumbSize ."\" placeholder=\"Thumbnail size (px; <0 square)\">";
	echo "<input name=\"R\" value=\"". $DefPicSize ."\" placeholder=\"Resized dimension (px)\">";
	echo "<input type=\"submit\" value=\"Naloži\" data-iconpos=\"left\" data-icon=\"check\" data-theme=\"b\">";
	//echo "<a href=\"list.php?Izbor=Msg&upload=". $_GET['file'] ."\" data-role=\"button\" data-ajax=\"false\" data-theme=\"b\">Naloži</a>\n";
	echo "<a href=\"list.php?Izbor=Msg&delete=". $_GET['file'] ."\" data-role=\"button\" data-icon=\"minus\" data-ajax=\"false\" data-theme=\"e\">Briši</a>\n";
	echo "</form>\n";
	echo "</div>\n";

	echo "</div>\n";

	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( '/\&file=[^&]+/i', '', $_SERVER['QUERY_STRING'] );
}

if ( isset($_GET['message']) ) {
	$imageAsString = "";
	$msgTitle = $msgSubtitle = $msgAbstract = $msgBody = "";

	//  Connect
	$conn = new POP3;
	if ( @$conn->Connect($mailUser, $mailPass, $mailServer, 110, $mailSSL) ) {
		// get message
		$mail = $conn->GetMessage((int)$_GET['message']);
		
		// extract subject
		$msgTitle = $mail['Subject'];
		
		// parse content
		$lines = explode("\n", $mail['Body'], 3);
		$msgSubtitle = $lines[0];
		$msgAbstract = $lines[1];
		//$msgBody     = $lines[2];

		// extract attachments (1st image)
		$att = $conn->GetMessageAttachments( (int)$_GET['message'] );
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

		//  We need to disconnect
		$conn->Disconnect();
	}

	echo "<div data-role=\"page\">\n";

	echo "<div data-role=\"header\" data-theme=\"a\">\n";
	echo "<h1>Sporočilo</h1>\n";
	echo "</div>\n";

	echo "<div data-role=\"content\">\n";
	echo $imageAsString != "" ? "<img src=\"data:image/png;base64,". base64_encode($imageAsString) ."\" style=\"float:left;\">" : "";
	echo "<h3>". $msgTitle ."</h3>";
	echo "<p><i>". $msgSubtitle ."</i></p>";
	echo "<p>". $msgAbstract ."</p>";
	echo "<a href=\"list.php?Izbor=Msg&process=". $_GET['message'] ."\" data-role=\"button\" data-ajax=\"false\" data-theme=\"b\">Naloži</a>\n";
	echo "<a href=\"list.php?Izbor=Msg&remove=". $_GET['message'] ."\" data-role=\"button\" data-ajax=\"false\" data-theme=\"e\">Briši</a>\n";
	echo "</div>\n";

	echo "</div>\n";

	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( '/\&message=[^&]+/i', '', $_SERVER['QUERY_STRING'] );
}
?>