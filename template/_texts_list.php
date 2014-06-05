<?php
/* _besedla.php - Display selectable list of text excerpts.
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
| This file is part of N3O CMS (frontend).                                  |
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

// if just one text selected
if ( isset($_GET['ID']) && $_GET['ID'] != "" ) {

	// display one text
	include("__text.php");

} else {
// show all available texts in a category (list view)

	// category title & description
	include("__category.php");

	// display all texts (title, image & abstract)
	if ( $Besedila ) foreach ( $Besedila as $Besedilo ) {
		$Tekst = $db->get_row(
			"SELECT
				BO.Naslov,
				BO.Podnaslov,
				BO.Povzetek,
				BO.Opis
			FROM
				BesedilaOpisi BO
			WHERE
				BO.BesediloID = ". (int)$Besedilo->ID ."
				AND (BO.Jezik='$lang' OR BO.Jezik IS NULL)
			ORDER BY
				BO.Jezik,
				BO.Polozaj
			LIMIT 1"
			);

		// get 1st gallery photo
		$Galerija = $db->get_row(
			"SELECT
				M.Datoteka
			FROM
				BesedilaSlike BS
				LEFT JOIN Media M ON BS.MediaID = M.MediaID
			WHERE
				BS.BesediloID = ". (int)$Besedilo->ID ."
			ORDER BY
				BS.Polozaj
			LIMIT 1"
			);
		
		echo "<div class=\"text list\">";
		// add link tag
		$kat = $TextPermalinks ? ($IsIIS ? "$WebFile/" : ''). $KatText .'/' : '?kat='. $_GET['kat'];
		$bid = $TextPermalinks ? $Besedilo->Ime .'/' : '&amp;ID='. $Besedilo->ID;
		echo "\t<A HREF=\"$WebPath/$kat". $bid ."\" TITLE=\"". $Tekst->Naslov ."\">\n";

		// find & embed image
		$pic = ""; //default: no image

		if ( $Besedilo->Slika != "" && fileExists($StoreRoot ."/media/besedila/". $Besedilo->Slika) ) {

			// if text has an image and it exists display it
			$pic = "$WebPath/media/besedila/". $Besedilo->Slika;
			// try to generate thumbnail
			try {
				// image thumbnail parameters
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
						AND S.SifrText = '". $Besedilo->Tip ."'
					ORDER BY
						ST.Jezik DESC
					LIMIT 1"
					);
				if ( $x ) {
					$GalleryBase  = $x->GalleryBase;
					$DefPicSize   = (int)$x->DefPicSize;
					$DefThumbSize = (int)$x->DefThumbSize;
					$MaxPicSize   = (int)$x->MaxPicSize;
				}
				unset($x);

				$thumb = PhpThumbFactory::create($StoreRoot ."/media/besedila/". $Besedilo->Slika, array('jpegQuality' => $jpgPct,'resizeUp' => false));
				$size = $thumb->getCurrentDimensions();
				// if size is largerer than thumbnail use thumbnail
				if ( $size['width'] > abs($DefThumbSize) || $size['height'] > abs($DefThumbSize) ) {
					if ( $DefThumbSize < 0 )
						$thumb->adaptiveResize(abs($DefThumbSize), abs($DefThumbSize));
					else
						$thumb->resize($DefThumbSize, $DefThumbSize);
					$imageAsString = $thumb->getImageAsString(); 
					$pic = "data:image/". strtolower($thumb->getFormat()) .";base64,". base64_encode($imageAsString);
				}
			} catch (Exception $e) {
			}

		} else if ( $Galerija ) {

			// 1st image in gallery: determine file (sPath) and URL (rPath) path
			$sFile = $Galerija->Datoteka;
			$rPath = $WebPath   ."/". dirname($sFile);
			$sPath = $StoreRoot ."/". dirname($sFile);
			$sFile = basename($sFile);
	
			if ( fileExists($sPath."/thumbs/".$sFile) ) {
				$pic = $rPath ."/thumbs/". $sFile; // existing thumbnail
			}

		} else {

			// find 1st embeded image
			if ( preg_match("/<img[^>]*>/i", str_replace("\\\"","\"",$Tekst->Opis), $src) ) {
				if ( preg_match("/src=\"(?!http)([^\"]*)\"/i", $src[0], $pic) ) { // find SRC= content
					$sPath = dirname("$StoreRoot/". $pic[1]); // filesystem path
					$rPath = dirname("$WebPath/". $pic[1]); // web relative path
					$sName = basename("$WebPath/". $pic[1]); // filename
			
					// check if thumbnail exists
					if ( fileExists($sPath .'/thumbs/'. $sName) ) {
						$pic = "$rPath/thumbs/". $sName;
					} else {
						// create thumbnail on the fly
						try {
							// image thumbnail parameters
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
									AND S.SifrText = '". $Besedilo->Tip ."'
								ORDER BY
									ST.Jezik DESC
								LIMIT 1"
								);
							if ( $x ) {
								$GalleryBase  = $x->GalleryBase;
								$DefPicSize   = (int)$x->DefPicSize;
								$DefThumbSize = (int)$x->DefThumbSize;
								$MaxPicSize   = (int)$x->MaxPicSize;
							}
							unset($x);

							$thumb = PhpThumbFactory::create($sPath ."/". $sName, array('jpegQuality' => $jpgPct,'resizeUp' => false));
							if ( $DefThumbSize < 0 )
								$thumb->adaptiveResize(abs($DefThumbSize), abs($DefThumbSize));
							else
								$thumb->resize($DefThumbSize, $DefThumbSize);
							$imageAsString = $thumb->getImageAsString(); 
							$pic = "data:image/". strtolower($thumb->getFormat()) .";base64,". base64_encode($imageAsString);
						} catch (Exception $e) {
							$pic = "";
							echo "<!-- Error getting image size! -->\n";
						}
					}
				}
			}
		}
		if ( $pic != "" )
			echo "\t<IMG SRC=\"". $pic ."\" alt=\"\" BORDER=\"0\" CLASS=\"thumb frame\" retina=\"no\">\n";

		// display text title & subtitle
		echo "\t<div class=\"title\"><h2>". $Tekst->Naslov ."</h2></div>\n";
		if ( $Tekst->Podnaslov != "" )
			echo "\t<div class=\"subtitle\">". $Tekst->Podnaslov ."</div>\n";
		echo "\t<div class=\"abstract\">";
		echo ReplaceSmileys(PrependImagePath($Tekst->Povzetek, "$WebPath/"), "$WebPath/pic/");
		echo "</div>\n";

		// add closing link tag
		echo "\t</A>\n";
		echo "</div>\n";
	}
}
?>