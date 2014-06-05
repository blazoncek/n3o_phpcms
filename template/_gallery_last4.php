<?php
/* _galerija_zadnje4.php - Display last 4 (or 5) images uploaded to gallery. From newest to oldest.
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

// display # rows
$Rows = isset($_GET['rows']) ? (int)$_GET['rows'] : 1;
// display # columns
$Columns = 5;
// determine maximum number of records
$MaxRows = $Columns * max(min($Rows,25),1);

// select images attached to texts in photography category
$rub = $db->get_var("SELECT KategorijaID FROM Kategorije WHERE Ime IN ('fotografija','photography') LIMIT 1");

$Slike = $db->get_results(
	"SELECT
		M.MediaID,
		M.Datoteka,
		M.Meta,
		MO.Naslov
	FROM
		Media M
		LEFT JOIN MediaOpisi MO ON M.MediaID = MO.MediaID
		LEFT JOIN BesedilaSlike BS ON BS.MediaID = M.MediaID
	WHERE
		M.Tip = 'PIC'
		AND (MO.Jezik='$lang' OR MO.Jezik IS NULL)
		AND BS.BesediloID IN (
			SELECT DISTINCT
				B.BesediloID
			FROM
				KategorijeBesedila KB
				LEFT JOIN Besedila B ON KB.BesediloID = B.BesediloID
			WHERE
				KB.KategorijaID LIKE '". $rub ."%'
		)
	ORDER BY
		M.Datum DESC
	LIMIT ". $MaxRows
	);

// are we requested do display different page?
$Page = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;

// number of possible pages
$NuPg = (int) ((count( $Slike )-1) / $MaxRows) + 1;

// fix page number if out of limits
$Page = max($Page, 1);
$Page = min($Page, $NuPg);

// start & end page
$StPg = min(max(1, $Page - 5), max(1, $NuPg - 10));
$EdPg = min($StPg + 10, min($Page + 10, $NuPg));

// previous and next page numbers
$PrPg = $Page - 1;
$NePg = $Page + 1;

// start and end row from recordset
$StaR = ($Page - 1) * $MaxRows + 1;
$EndR = min(($Page * $MaxRows),count( $Slike ));

// select category with latest images (for link)
$rub = $db->get_var("SELECT KategorijaID FROM Kategorije WHERE Ime IN ('latest','novosti') AND KategorijaID LIKE '".$rub."%' LIMIT 1");

// if there is something to display
if ( !isset($_GET['ID']) && count( $Slike ) > 0 ) {

	echo "<div class=\"gallery\">";
	echo "<ul style=\"margin:0;\">\n";
	$i = 0;
	while ( $i < $EndR && $i < count( $Slike ) ) {
		// are we at start row yet?
		if ( $i++ < $StaR-1 ) continue;
		// current record number (starting at 1)
		$CurrentRow = ($i-$StaR)+1;

		echo "\t<li>";

		// determine file (sPath) and URL (rPath) path
		$sRoot = $StoreRoot;
		$sPath = dirname($sRoot ."/". $Slike[$i-1]->Datoteka);
		$rPath = str_replace("\\", "/", right($sPath, strlen($sPath)-strlen($sRoot)-1));
		$sFile = basename($Slike[$i-1]->Datoteka);
		
		// file title exists?
		if ( $Slike[$i-1]->Naslov != "")
			$sName = $Slike[$i-1]->Naslov ." (". $sFile .")";
		else
			$sName = $sFile;

		// add link tag
		if ( $rub ) {
			$link = $TextPermalinks ? ($IsIIS ? "$WebFile/" : ''). $db->get_var("SELECT Ime FROM Kategorije WHERE KategorijaID='".$rub."'").'/' : '?kat='. $rub;
			echo "<A HREF=\"$WebPath/$link\" TITLE=\"$sName\">";
		} else {
			echo "<A HREF=\"$WebPath/$rPath/".(fileExists("$sPath/large/$sFile")?"large/":"")."$sFile\" ".($Mobile?" REL=\"external\"":"CLASS=\"fancybox\" REL=\"lightbox_last\"")." TITLE=\"$sName\">";
		}

		// display image thumbnail
		if ( fileExists($sPath .'/thumbs/'. $sFile) ) {
			// get metadata
			$Meta = ParseMetadata($Slike[$i-1]->Meta);
			$IMG_WIDTH  = (int)$Meta['tw'];	// thumbnail width
			$IMG_HEIGHT = (int)$Meta['th']; // thumbnail height
			unset($Meta);
			// display existing thumbnail
			echo "<IMG SRC=\"$WebPath/$rPath/thumbs/$sFile\" ALT=\"$sName\" BORDER=\"0\" CLASS=\"thumb\" retina=\"no\">";
		} else {
			try {
				// create thumbnail on the fly
				$thumb = PhpThumbFactory::create("$sPath/$sFile");
				if ( $DefThumbSize < 0 )
					$thumb->adaptiveResize(abs($DefThumbSize), abs($DefThumbSize));
				else
					$thumb->resize(abs($DefThumbSize), abs($DefThumbSize));
				$imageAsString = $thumb->getImageAsString(); 
				echo "<IMG SRC=\"data:image/". strtolower($thumb->getFormat()) .";base64,". base64_encode($imageAsString) ."\" ALT=\"$sName\" BORDER=\"0\" CLASS=\"thumb\" retina=\"no\">";
			} catch (Exception $e) {
				echo "<IMG SRC=\"$WebPath/pic/nislike_112.png\" alt=\"\" BORDER=\"0\" CLASS=\"thumb\">";
			}
		}

		// add closing link tag
		echo "</A>";

		echo "</li>\n";
	}
	echo "</ul>";
	echo "</div>\n";
}
// free recordset
unset($Slike);
?>