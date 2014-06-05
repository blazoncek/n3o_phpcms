<?php
/* __gallery.php - Display single text as a gallery of images.
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

// requires following variables:
// - recordset $Galerija containing images
// - recordset $Besedila containing text
// - $kat & $bid for link creation

if ( isset($Galerija) ) {

	// check if user requested more than 1 row
	$_GET['rows'] = isset($_GET['rows']) ? (int)$_GET['rows'] : 10;
	
	// are we requested do display different page?
	$Page = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;
	
	// display # columns depending on page/frame width
	$Columns = 5; // (int) ($ContentW / $DefThumbSize);
	// determine maximum number of records
	$MaxRows = $Columns * $_GET['rows'];
	
	// number of possible pages
	$NuPg = (int)((count($Galerija)-1) / $MaxRows) + 1;
	
	// fix page number if out of limits
	$Page = max($Page, 1);
	$Page = min($Page, $NuPg);
	
	// start & end page
	$StPg = min(max(1, $Page - 3), max(1, $NuPg - 6));
	$EdPg = min($StPg + 6, min($Page + 6, $NuPg));
	
	// previous and next page numbers
	$PrPg = $Page - 1;
	$NePg = $Page + 1;
	
	// start and end row from recordset
	$StaR = ($Page - 1) * $MaxRows + 1;
	$EndR = min(($Page * $MaxRows),count($Galerija));

	// if there is something to display
	if ( count($Galerija) > 0 ) {

		// display page navigation
		if ( $NuPg > 1 ) {
			echo "<TABLE class=\"navbutton\" BORDER=\"0\" CELLPADDING=\"10\" CELLSPACING=\"0\" WIDTH=\"100%\" HEIGHT=\"44\">\n";
			echo "<TR>\n";
			echo "\t<TD WIDTH=\"50%\">\n";
			if ( $Page > 1 )
				echo "\t<A HREF=\"$WebPath/$kat". $bid ."pg=". $PrPg ."&amp;rows=". $_GET['rows'] ."\">&laquo;&nbsp;". multiLang('<PrevPage>', $lang) ."</A>\n";
			else
				echo "\t&nbsp;\n";
			echo "</TD>\n";
			echo "\t<TD ALIGN=\"right\">\n";
			if ( $Page < $EdPg )
				echo "\t<A HREF=\"$WebPath/$kat". $bid ."pg=". $NePg ."&amp;rows=". $_GET['rows'] ."\">". multiLang('<NextPage>', $lang) ."&nbsp;&raquo;</A>\n";
			else
				echo "\t&nbsp;\n";
			echo "</TD>\n";
			echo "</TR>\n";
			echo "</TABLE>\n";
		}

		// display grid of thumbnails
		echo "<div class=\"gallery fence\">";
		echo "<ul id=\"Gallery\">\n";

		for ( $i=$StaR; $i<=$EndR; $i++ ) {
			// current record number (starting at 1)
			$CurrentRow = ($i-$StaR)+1;

			// determine file (sPath) and URL (rPath) path
			$sFile = $Galerija[$i-1]->Datoteka; // filename stored in UTF-8!!!
			$rPath = $WebPath   ."/". dirname($sFile);
			$sPath = $StoreRoot ."/". dirname($sFile);
			$sFile = basename($sFile);
			// file title exists?
			$sName = $Galerija[$i-1]->Naslov != "" ? $Galerija[$i-1]->Naslov ."/". $sFile ."/". $Galerija[$i-1]->MediaID : $sFile;

			echo "<li>";
			// add link tag (using SwipeGalley)
			echo "<a href=\"$rPath/".(fileExists($sPath."/large/".$sFile) ? "large/" : "").$sFile."\" ".($Mobile ? " rel=\"external\"" : "class=\"fancybox\" rel=\"lightbox_gal\"")." TITLE=\"".$sName."\">";
			// link tag using image display template
			//echo "<a href=\"$WebPath/$kat"."&amp;tmpl=Slika&ID=". $_GET['ID'] ."&amp;mID=". $Galerija[$i-1]->MediaID ."&amp;nomenu\" title=\"$sName\">";

			// display image thumbnail
			if ( fileExists($sPath."/thumbs/".$sFile) ) {
				// get metadata
				$Meta = ParseMetadata($Galerija[$i-1]->Meta);
				$IMG_WIDTH  = (int)$Meta['tw'];	// thumbnail width
				$IMG_HEIGHT = (int)$Meta['th']; // thumbnail height
				unset($Meta);
				// display existing thumbnail
				echo "<IMG SRC=\"$rPath/thumbs/$sFile\" ALT=\"$sName\" BORDER=0 class=\"thumb\" retina=\"no\">";
			} else {
				// try to create thumbnail on the fly
				try {
					$thumb = PhpThumbFactory::create($sPath."/".((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? DecodeUTF8($sFile) : $sFile));
					$thumb->adaptiveResize($DefThumbSize, $DefThumbSize);
					$imageAsString = $thumb->getImageAsString(); 
					echo "<IMG SRC=\"data:image/". strtolower($thumb->getFormat()) .";base64,". base64_encode($imageAsString) ."\" ALT=\"$sName\" BORDER=\"0\" class=\"thumb\" retina=\"no\">";
				} catch (Exception $e) {
					// display missing thumbnail image
					echo "<IMG SRC=\"$WebPath/pic/nislike_112.png\" ALT=\"\" BORDER=\"0\" class=\"thumb\">";
				}
			}
			// add closing link tag
			echo "</a>";
			echo "</li>\n";
		}
		echo "</ul></div>\n";

		// display page navigation
		if ( $NuPg > 1 ) {
			echo "<div class=\"pages\">\n";
			echo "<TABLE BORDER=\"0\" CLASS=\"navbutton\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
			echo "<TR>\n";
			echo "\t<TD COLSPAN=\"2\" CLASS=\"a10\">\n";
			echo "&nbsp;". multiLang('<Page>', $lang) .":";
			if ( $StPg > 1 )
				echo "<A HREF=\"$WebPath/$kat". $bid ."pg=". ($StPg-1) ."&amp;rows=". $_GET['rows'] ."\">&laquo;</A>\n";
			if ( $Page > 1 )
				echo "<A HREF=\"$WebPath/$kat". $bid ."pg=$PrPg&amp;rows=". $_GET['rows'] ."\">&lt;</A>\n";
			for ( $i = $StPg; $i <= $EdPg; $i++ ) {
				if ( $i == $Page )
					echo "<FONT COLOR=\"$TxtExColor\"><B>$i</B></FONT>\n";
				else
					echo "<A HREF=\"$WebPath/$kat". $bid ."pg=$i&amp;rows=". $_GET['rows'] ."\">$i</A>\n";
			}
			if ( $Page < $EdPg )
				echo "<A HREF=\"$WebPath/$kat". $bid ."pg=$NePg&amp;rows=". $_GET['rows'] ."\">&gt;</A>\n";
			if ( $NuPg > $EdPg )
				echo "<A HREF=\"$WebPath/$kat". $bid ."pg=". ($EdPg+1) ."&amp;rows=". $_GET['rows'] ."\">&raquo;</A>\n";
			echo "</TD>\n";
			echo "<TD ALIGN=\"right\" CLASS=\"a10\">\n";
/*
			// option for different number of rows
			echo multiLang('<Show>', $lang) . "\n";
			echo "<A HREF=\"$WebPath/$kat". $bid ."rows=5\">5</A>\n";
			echo "<A HREF=\"$WebPath/$kat". $bid ."rows=10\">10</A>\n";
			echo "<A HREF=\"$WebPath/$kat". $bid ."rows=15\">15</A>\n";
			echo multiLang('<rows>', $lang) ."&nbsp;\n";
*/
			echo "</TD>\n";
			echo "</TR>\n";
			echo "</TABLE>\n";
			echo "</div>\n";
		}
	}
}
?>