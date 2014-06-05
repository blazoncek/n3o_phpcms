<?php
/*
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

// get category id for photography
$rub = $db->get_var("SELECT KategorijaID FROM Kategorije WHERE Ime IN ('fotografija','photography')");

if ( $rub != "" ) {
	// get only images (media) from selected category
	$Slike = $db->get_results(
		"SELECT DISTINCT
			BS.ID,
			BS.MediaID,
			M.Naziv,
			M.Datoteka,
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
			M.Datum DESC"
		);

	srand(mktime(0, 0, 0 /*, (int)date("n"), (int)date("j"), (int)date("Y")*/));
	$StaR = rand(1, $db->num_rows);

	if ( $Slike ) {
		echo "\t<DIV CLASS=\"menu gallery\">\n";

		// determine file (sPath) and URL (rPath) path
		$sRoot = $StoreRoot;
		$sPath = dirname($sRoot ."/". $Slike[$StaR-1]->Datoteka);
		$rPath = str_replace("\\", "/", right($sPath, strlen($sPath)-strlen($sRoot)-1));
		$sFile = basename($Slike[$StaR-1]->Datoteka);
		
		// file title exists?
		$sName = ($Slike[$StaR-1]->Naslov != "" ? $Slike[$StaR-1]->Naslov : $Slike[$StaR-1]->Naziv) ."/". $sFile ."/". $Slike[$StaR-1]->MediaID;

		// add link tag
		echo "\t\t";
		if ( fileExists("$sPath/large/$sFile") )
			echo "<A HREF=\"$WebPath/$rPath/large/$sFile\" CLASS=\"fancybox\" REL=\"lightbox\" TITLE=\"$sName\">";
		elseif ( fileExists("$sPath/$sFile") )
			echo "<A HREF=\"$WebPath/$rPath/$sFile\" CLASS=\"fancybox\" REL=\"lightbox\" TITLE=\"$sName\">";

		echo "<IMG SRC=\"$WebPath/$rPath/thumbs/$sFile\" alt=\"\" BORDER=0 CLASS=\"thumb frame\" HSPACE=0 VSPACE=0 retina=\"no\"></A>\n";

		echo "\t</DIV>\n";
	}
	unset($Slike);
}
