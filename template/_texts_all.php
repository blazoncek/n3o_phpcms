<?php
/* _besedila_v_kat.php - Display all (full) texts in a category.
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

// category title & description
include("__category.php");

if ( $Besedila ) foreach( $Besedila as $Besedilo ) {

	// izbor ene strani besedila
	$Teksti = $db->get_results(
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
			BO.Polozaj"
		);

	echo "<div class=\"text\">\n";
	$j = 0;
	// display a single post (comprised of multiple texts)
	if ( $Teksti ) foreach( $Teksti as $Tekst ) {

		echo "\t<div class=\"title\">\n";
		// display text title			
		if ( left($Tekst->Naslov,1) != '.' ) {
			echo "\t".($j==0?"<h1>":"<h2>")."";
			echo $Tekst->Naslov;
			echo ($j==0?"</h1>":"</h2>")."\n";
		}
		echo "\t</div>\n";

		if ( $Tekst->Podnaslov != "" )
			echo "<div class=\"subtitle\"><h3>". ReplaceSmileys($Tekst->Podnaslov, "$WebPath/pic/") ."</h3></div>\n";
		// display text abstract
		if ( $Tekst->Povzetek != "" ) {
			echo "\t<div class=\"abstract\">\n";
			echo ReplaceSmileys(PrependImagePath($Tekst->Povzetek, "$WebPath/"), "$WebPath/pic/");
			echo "\t</div>\n";
		}

		echo "\t<div class=\"body\">\n";
		$Bes = $Tekst->Opis;
		// correct escaped quotes (some PHP/MySQL combos)
		$Bes = str_replace("\\\"","\"",$Bes);
		// create google maps
		if ( preg_match_all("/\[googlemaps\]([^[]+)\[\/googlemaps\]/i", $Bes, $locations) ) {
			// $locations = {{'[googlemaps]location1[/googlemaps]',...},{'location1',...}}
			$gmaps = '<iframe class="googlemaps" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=###LOCATION###&amp;ie=UTF8&amp;t=m&amp;z=15&amp;output=embed"></iframe><br><small><a href="https://maps.google.com/maps?q=###LOCATION###&amp;ie=UTF8&amp;t=m&amp;z=15&amp;source=embed">Prikaži večji zemljevid</a></small>';
			if ( count($locations[1]) > 0 ) foreach ( $locations[1] as $location ) {
				$Bes = str_ireplace('[googlemaps]'. $location .'[/googlemaps]', str_ireplace('###LOCATION###', $location, $gmaps), $Bes);
			}
		}
		// fix image path for permalinks
		$Bes = PrependImagePath($Bes, "$WebPath/");
		// add <A HREF=...> to images with larger version (in ./large folder)
		if ( !$Mobile ) $Bes = AddLightboxLink($Bes, $Besedilo->ID);
		// replace text smilies with images
		$Bes = ReplaceSmileys($Bes, "$WebPath/pic/");
		// display text content
		echo "$Bes\n";
		echo "\t</div>\n";
		
		$j++;
	}
	echo "</div>\n";

	// display gallery
	$Galerija = $db->get_results(
		"SELECT
			BS.ID,
			BS.BesediloID,
			B.Ime,
			M.MediaID,
			M.Datoteka,
			M.Naziv,
			M.Meta,
			MO.Naslov,
			MO.Opis
		FROM
			BesedilaSlike BS
			LEFT JOIN Media M ON BS.MediaID = M.MediaID
			LEFT JOIN MediaOpisi MO	ON M.MediaID = MO.MediaID
			LEFT JOIN Besedila B ON B.BesediloID = BS.BesediloID
		WHERE
			BS.BesediloID = ". (int)$Besedilo->ID ."
			AND (MO.Jezik='". $lang ."' OR MO.Jezik IS NULL)
		ORDER BY
			BS.Polozaj DESC"
		);

	if ( count($Galerija) > 0 ) {
		echo "<div class=\"gallery fence\">";
		echo "<ul id=\"Gallery". $Besedilo->ID ."\">\n";
		$i = 0;
		foreach ( $Galerija as $Slika ) {

			// determine file (sPath) and URL (rPath) path
			$sRoot = $StoreRoot;
			$sPath = dirname($sRoot ."/". $Slika->Datoteka);
			$rPath = str_replace("\\", "/", right($sPath, strlen($sPath)-strlen($sRoot)-1));
			$sFile = basename($Slika->Datoteka);
			
			// file title exists?
			if ( $Slika->Naslov != "")
				$sName = $Slika->Naslov;
			else
				$sName = $sFile;
	
			echo "\t<li>";
			// add link tag
			echo "<A HREF=\"$WebPath/$rPath/".(fileExists("$sPath/large/$sFile")?"large/":"")."$sFile\" ".($Mobile?" REL=\"external\"":"CLASS=\"fancybox\" REL=\"lightbox_gal\"")." TITLE=\"$sName\">";

			// display image thumbnail
			if ( fileExists($sPath."/thumbs/".$sFile) ) {
				// get metadata
				$Meta = ParseMetadata($Slika->Meta);
				$IMG_WIDTH  = (int)$Meta['tw'];	// thumbnail width
				$IMG_HEIGHT = (int)$Meta['th']; // thumbnail height
				unset($Meta);
				// display existing thumbnail
				echo "<IMG SRC=\"$WebPath/$rPath/thumbs/$sFile\" alt=\"\" BORDER=0>";
			} else {
				try {
					// create thumbnail on the fly
					$thumb = PhpThumbFactory::create($sPath."/".$sFile);
					$thumb->adaptiveResize(112, 112);
					$imageAsString = $thumb->getImageAsString(); 
					echo "<IMG SRC=\"data:image/png;base64,". base64_encode($imageAsString) ."\" alt=\"\" BORDER=\"0\">";
				} catch (Exception $e) {
					echo "<IMG SRC=\"$WebPath/pic/nislike_112.png\" alt=\"\" BORDER=\"0\" class=\"thumb\">";
				}
			}
	
			// add closing link tag
			echo "</A>";
			echo "</li>\n";
		}
		echo "</ul>";
		echo "</div>\n";
	}

	// display related texts links
	$Dodatni = $db->get_results(
		"SELECT
			BS.DodatniID AS ID,
			B.Ime,
			BO.Naslov
		FROM
			BesedilaSkupine BS
			LEFT JOIN BesedilaOpisi BO ON BS.DodatniID = BO.BesediloID
			LEFT JOIN Besedila B ON BS.DodatniID = B.BesediloID
		WHERE
			BS.BesediloID = ". $Besedilo->ID ."
			AND (BO.Jezik='". $lang ."' OR BO.Jezik IS NULL)
			AND BO.Polozaj = 1
		ORDER BY
			BS.Polozaj"
		);

	if ( $Dodatni && count($Dodatni) > 0 ) {
		echo "<div class=\"related\">";
		echo "\t<div class=\"head\">". multiLang("<SeeAlso>", $lang) ."</div>\n";
		foreach ( $Dodatni as $Dodatno ) {
			// find first category ID of text
			$rub = $db->get_row(
				"SELECT
					KB.KategorijaID,
					K.Ime,
					B.Tip
				FROM
					KategorijeBesedila KB
					LEFT JOIN Kategorije K
						ON KB.KategorijaID = K.KategorijaID
					LEFT JOIN Besedila B
						ON KB.BesediloID = B.BesediloID
				WHERE
					KB.BesediloID = ". (int)$Dodatno->ID ."
				ORDER BY
					KB.ID
				LIMIT 1"
				);

			echo "\t<div class=\"body\">";
			$kat = ($TextPermalinks) ? ($IsIIS ? $WebFile .'/' : ''). $rub->Ime .'/' : '?kat='. $rub->KategorijaID;
			$bid = ($TextPermalinks) ? $Dodatno->Ime .'/' : '&amp;ID='. $Dodatno->ID;
			echo "<a href=\"$WebPath/$kat". $bid ."\">". $Dodatno->Naslov ."</a>";
			echo "</div>\n";
			
			unset($rub);
		}
		echo "</div>\n";
	}

	// display attachments for non-mobile clients
	if ( !$Mobile ) {
		$Media = $db->get_results(
			"SELECT
				M.MediaID,
				MO.Naslov,
				M.Datoteka,
				M.Velikost,
				M.Slika,
				M.Tip,
				M.Naziv as Ime,
				MO.Opis
			FROM BesedilaMedia BM
				LEFT JOIN Media M
					ON BM.MediaID = M.MediaID
				LEFT JOIN MediaOpisi MO
					ON BM.MediaID = MO.MediaID
			WHERE
				BM.BesediloID = ". $Besedilo->ID ."
				AND (MO.Jezik='". $lang ."' OR MO.Jezik IS NULL)
				AND M.Izpis <> 0
			ORDER BY
				BM.Polozaj"
			);

		if ( count($Media) > 0 ){
			echo "<DIV CLASS=\"related\">\n";
			echo "<div class=\"head\">". multiLang("<Attachments>", $lang) ."</div>\n";
			echo "<TABLE BORDER=\"0\" CELLPADDING=\"3\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
			foreach ( $Media as $File ) {
				echo "<TR>\n";
				echo "\t<TD ALIGN=\"center\" ROWSPAN=\"2\" VALIGN=\"middle\" WIDTH=\"130\">\n";
				if ( $File->Slika != "" )
					echo "\t<IMG SRC=\"$WebPath/media/media/$File->Slika\" ALIGN=\"top\" alt=\"\" VSPACE=2 HSPACE=2>\n";
				echo "\t</TD>\n";
				echo "\t<TD>\n";
				if ( $File->Naslov != "" && left($File->Naslov,1) != "." )
					echo "\t<A HREF=\"$WebPath/media/$File->Datoteka\" TARGET=\"_blank\"><B>$File->Naslov</B></A>\n";
				echo "\t</TD>\n";
				echo "\t<TD ALIGN=\"right\" WIDTH=\"96\">". sprintf( "%4.1f", ((float)$File->Velikost)/1024 ) . "&nbsp;kB <A HREF=\"media/$File->Datoteka\" TARGET=\"_blank\">";
				if ( fileExists($StoreRoot ."pic/$File->Tip.gif") )
					echo "<IMG SRC=\"$WebPath/pic/$File->Tip.gif\" ALIGN=\"absmiddle\" alt=\"\" BORDER=\"0\" VSPACE=\"0\" HSPACE=\"4\">";
				else
					echo "<IMG SRC=\"$WebPath/pic/dl.gif\" ALIGN=\"absmiddle\" alt=\"\" BORDER=\"0\" VSPACE=\"0\" HSPACE=\"4\">";
				echo "</A>\n\t</TD>\n";
				echo "</TR>\n";
				echo "<TR>\n";
				echo "\t<TD class=\"a9\">\n";
				echo "\t". str_replace("\\\"","\"",$File->Opis) ."\n";
				echo "\t</TD>\n";
				echo "</TR>\n";
			}
			echo "</TABLE>\n";
			echo "</DIV>\n";
		}
	}
	// free recordset
	unset($Teksti);
}

// display category attachments for non-mobile clients
if ( !$Mobile ) {
	$MediaKat = $db->get_results(
		"SELECT
			M.MediaID,
			MO.Naslov,
			M.Datoteka,
			M.Velikost,
			M.Slika,
			M.Tip,
			MO.Opis
		FROM KategorijeMedia KM
			LEFT JOIN Media M
				ON KM.MediaID = M.MediaID
			LEFT JOIN MediaOpisi MO
				ON KM.MediaID = MO.MediaID
		WHERE
			KM.KategorijaID = '" . $db->escape($_GET['kat']) . "'
			AND (MO.Jezik='$lang' OR MO.Jezik IS NULL)
			AND M.Izpis <> 0
		ORDER BY
			KM.Polozaj"
		);

	// prikaz priponk
	if ( count($MediaKat) > 0 ){
		echo "<DIV CLASS=\"related\">\n";
		echo "<div class=\"head\">". multiLang("<Attachments>", $lang) ."</div>\n";
		echo "<TABLE BORDER=\"0\" CELLPADDING=\"3\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
		foreach ( $MediaKat as $File ) {
			echo "<TR>\n";
			echo "\t<TD ALIGN=\"center\" ROWSPAN=\"2\" VALIGN=\"top\">\n";
			if ( $File->Slika != "" )
				echo "\t<IMG SRC=\"$WebPath/media/media/thumbs/$File->Slika\" ALIGN=\"top\" alt=\"\" VSPACE=2 HSPACE=2>\n";
			echo "\t</TD>\n";
			echo "\t<TD>\n";
			if ( $File->Naslov != "" && left($File->Naslov,1) != "." )
				echo "\t<A HREF=\"$WebPath/media/$File->Datoteka\" TARGET=\"_blank\"><B>$File->Naslov</B></A>\n";
			echo "\t</TD>\n";
			echo "\t<TD ALIGN=\"right\">". sprintf( "%4.1f", ((float)$File->Velikost)/1024 ) . "&nbsp;kB <A HREF=\"media/$File->Datoteka\" TARGET=\"_blank\">";
			if ( fileExists($StoreRoot ."pic/$File->Tip.gif") )
				echo "<IMG SRC=\"$WebPath/pic/$File->Tip.gif\" ALIGN=\"absmiddle\" alt=\"\" BORDER=\"0\" VSPACE=\"0\" HSPACE=\"0\">";
			else
				echo "<IMG SRC=\"$WebPath/pic/dl.gif\" ALIGN=\"absmiddle\" alt=\"\" BORDER=\"0\" VSPACE=\"0\" HSPACE=\"0\">";
			echo "</A>\n\t</TD>\n";
			echo "</TR>\n";
			echo "<TR>\n";
			echo "\t<TD class=\"a9\" COLSPAN=\"2\">\n";
			echo "\t<BLOCKQUOTE>". PrependImagePath(str_replace("\\\"","\"",$File->Opis), "$WebPath/") ."</BLOCKQUOTE>\n";
			echo "\t</TD>\n";
			echo "</TR>\n";
		}
		echo "</TABLE>\n";
		echo "</DIV>\n";
	}
	unset($MediaKat);
}
?>