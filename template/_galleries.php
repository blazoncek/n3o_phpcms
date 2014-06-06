<?php
/* _galerije.php - Display category as gallery (w/ subgalleries).
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

if ( isset($_GET['gID']) ) $_GET['ID'] = $_GET['gID']; // backwards compatibility

// if just one text selected
if ( isset($_GET['ID']) && $_GET['ID'] != "" ) {

	// display text title, excerpt & description
	echo "<div class=\"text\">\n";
	
	if ( $Teksti[0]->Naslov != "" && left($Teksti[0]->Naslov,1) != "." ) {
		echo "<div class=\"title\"><h1>";
		echo $Teksti[0]->Naslov;
		echo "</h1></div>\n";

		if ( !isset($_GET['pg']) || $_GET['pg']==1 ) {
			if ( $Teksti[0]->Povzetek != "" )
				echo "<div class=\"subtitle\">". ReplaceSmileys($Teksti[0]->Povzetek, "$WebPath/pic/") ."</div>\n";
			if ( $Teksti[0]->Opis != "" )
				echo "<div class=\"abstract\">". ReplaceSmileys(PrependImagePath($Teksti[0]->Opis, "$WebPath/"), "$WebPath/pic/") ."</div>\n";
		}
	}
	echo "</div>\n";

	// define type of links
	$kat = $TextPermalinks ? ($IsIIS ? "$WebFile/" : ''). $KatText .'/' : '?kat='. $_GET['kat'];
	$bid = $TextPermalinks ? $Teksti[0]->Ime .'/?' : '&amp;ID='. $_GET['ID'] .'&amp;';

	// include grid display
	include('__gallery.php');

} else {

	// category title & description
	include("__category.php");

	if ( count($Besedila) > 0 || count($PodRubrike) > 0 ) {

		echo "<div class=\"grid fence\">\n";
		echo "<ul>\n";

		$i = 1;
		if ( count($PodRubrike) > 0 ) {
			// najprej izpis podrubrik s pripadajočo sliko
			foreach ( $PodRubrike as $Rub ) {
				echo "<li class=\"g4\">\n";

				$sRoot = $StoreRoot;
				// determine file (sPath) and URL (rPath) path
				if ($Rub->Slika == '') { // no image at category
					// find image from first text
					$Slika = $db->get_var(
						"SELECT
							B.Slika
						FROM
							KategorijeBesedila KB
							LEFT JOIN Besedila B ON B.BesediloID = KB.BesediloID
						WHERE
							KB.KategorijaID='". $Rub->KategorijaID ."'
							AND B.Slika IS NOT NULL
						ORDER BY
							KB.Polozaj
						LIMIT 1"
						);

					if ( $Slika ) {
						// if text exists and has a thumbnail image
						$sPath = dirname($sRoot ."/media/besedila/". $Slika);
						$rPath = str_replace("\\", "/", right($sPath, strlen($sPath)-strlen($sRoot)-1));
						$sFile = basename($Slika);
					} else {
						// otherwise get image from last upload (latest image)
						$Slika = $db->get_var(
							"SELECT DISTINCT
								M.Datoteka
							FROM
								Media M
								LEFT JOIN BesedilaSlike BS ON BS.MediaID = M.MediaID
							WHERE
								M.Tip = 'PIC'
								AND BS.BesediloID IN (
									SELECT DISTINCT
										B.BesediloID
									FROM
										KategorijeBesedila KB
										LEFT JOIN Besedila B ON KB.BesediloID = B.BesediloID
									WHERE
										KB.KategorijaID LIKE '". $db->escape($_GET['kat']) ."%'
								)
							ORDER BY
								M.Datum DESC
							LIMIT 1"
							);
						$sPath = dirname($sRoot ."/". $Slika) .'/thumbs';
						$rPath = str_replace("\\", "/", right($sPath, strlen($sPath)-strlen($sRoot)-1));
						$sFile = basename($Slika);
					}
				} else {
					$sPath = dirname($sRoot ."/media/rubrike/". $Rub->Slika);
					$rPath = str_replace("\\", "/", right($sPath, strlen($sPath)-strlen($sRoot)-1));
					$sFile = basename($Rub->Slika);
				}

				// add link tag
				$kat = ($TextPermalinks) ? ($IsIIS ? "$WebFile/" : ''). $Rub->Ime .'/' : '?kat='. $Rub->KategorijaID;
				$Naziv = $Rub->Naziv == "" ? $Rub->Ime : $Rub->Naziv;
				echo "<A HREF=\"$WebPath/$kat\" title=\"$Naziv\">";
		
				// if file exists display image thumbnail
				if ( fileExists($sPath .'/'. $sFile) )
					echo "<img src=\"". $WebPath ."/". $rPath ."/". $sFile ."\" alt=\"\" border=\"0\" class=\"thumb frame\">";
				else
					echo "<img src=\"". $WebPath ."/pic/nislike_112.png\" alt=\"\" border=\"0\" class=\"thumb frame\">";
		
				// add closing link tag
				echo "</A>\n";
	
				// display subcategory title
				echo "<P><A HREF=\"". $WebPath ."/". $kat ."\"><B>". $Naziv ."</B></A><br>&nbsp;</P>\n";

				echo "</li>\n";
				$i++;
			}
		}
			
		if ( count($Besedila) > 0 ) {
			// nato izpis posameznih galerij v rubriki
			foreach ( $Besedila as $Besedilo ) {
				$Tekst = $db->get_row(
					"SELECT
						BO.Naslov,
						BO.Podnaslov,
						BO.Povzetek
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
				$Slike = $db->get_results(
					"SELECT
						BS.ID,
						M.Datoteka,
						M.Meta
					FROM
						BesedilaSlike BS
						LEFT JOIN Media M ON BS.MediaID = M.MediaID
					WHERE
						BS.BesediloID = ". (int)$Besedilo->ID ."
					ORDER BY
						BS.Polozaj"
					);
	
				echo "<li class=\"g4\">\n";
				
				// add link tag
				$kat = ($TextPermalinks) ? ($IsIIS ? "$WebFile/" : ''). $KatText .'/' : '?kat='. $_GET['kat'];
				$bid = ($TextPermalinks) ? $Besedilo->Ime .'/' : '&amp;ID='. $Besedilo->ID;
				echo "<A HREF=\"$WebPath/$kat". $bid ."\" title=\"$Tekst->Naslov\">";
				//echo "<DIV CLASS=\"slide\" ID=\"Slide$i\"><span></span>";
	
				// display thumbnail
				$Thumb = $WebPath ."/pic/nislike_112.png";
				// use first image from gallery
				if ( $Slike && $Slike[0]->Datoteka!="" ) {
					// parse a filename
					$sRoot = $StoreRoot;
					$sPath = dirname($sRoot . "/" . $Slike[0]->Datoteka);
					$rPath = str_replace("\\", "/", right($sPath, strlen($sPath)-strlen($sRoot)-1));
					$sFile = basename($Slike[0]->Datoteka);
					// if file exists display image thumbnail
					if ( fileExists($sPath .'/thumbs/'. $sFile) ) {
						// get metadata
						$Meta = ParseMetadata($Slike[0]->Meta);
						$IMG_WIDTH  = (int)$Meta['tw'];	// thumbnail width
						$IMG_HEIGHT = (int)$Meta['th']; // thumbnail height
						unset($Meta);
						// set image file
						$Thumb = $WebPath ."/". $rPath ."/thumbs/". $sFile;
					}
				}
				// thumb defined in text
				if ( $Besedilo->Slika!="" && fileExists("media/besedila/". $Besedilo->Slika) )
					$Thumb = $WebPath ."/media/besedila/". $Besedilo->Slika;

				echo "<img src=\"". $Thumb ."\" alt=\"\" border=\"0\" class=\"thumb frame\">";
		
				//echo "</DIV>";
				// add closing link tag
				echo "</A>\n";
	
				// display gallery title and image count
				echo "<P><A HREF=\"". $WebPath ."/". $kat . $bid ."\" title=\"". $Tekst->Naslov ."\"><B>". $Tekst->Naslov ."</B></A><BR>\n";
				echo $db->num_rows ." ". multiLang('<image>', $lang) . koncnica(count($Slike), multiLang('<img_ending>', $lang));
				echo "</P>\n";
	
				echo "</li>\n";
				$i++;
			}
			unset($Slike);
			unset($Tekst);
		}
		echo "</ul>\n";
		echo "</div>\n";
	}
}
