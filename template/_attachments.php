<?php
/* _attachments.php - Menu: category attachments
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

// get available attachments
$Medias = $db->get_results(
	"SELECT
		M.MediaID,
		MO.Naslov,
		M.Datoteka,
		M.Velikost,
		M.Slika,
		MO.Opis,
		M.Tip
	FROM
		KategorijeMedia KM
		LEFT JOIN Media M
			ON KM.MediaID = M.MediaID
		LEFT JOIN MediaOpisi MO
			ON KM.MediaID = MO.MediaID
	WHERE
		(KM.KategorijaID = '". $db->escape($_GET['kat']) ."' OR KM.KategorijaID = '00')
		AND (MO.Jezik = '$lang' OR MO.Jezik IS NULL)
		AND M.Izpis <> 0
	ORDER BY
		KM.Polozaj"
	);

// if attachments exist
if ( $db->num_rows > 0 ) {
	echo "<div class=\"menu\">\n";
	echo "<div class=\"title\">". multilang("<Attachments>", $lang) ."</div>\n";
	// loop for each attachmentd
	foreach ( $Medias as $Media ) {
		// if title does not exist or starts with "." do not display link
		if ( $Media->Naslov != "" && left($Media->Naslov, 1) != "." ) {
			echo "<div>\n";
			echo "\t<A HREF=\"$WebPath/media/". $Media->Datoteka ."\" TARGET=\"_blank\"><B>". $Media->Naslov ."</B></A><BR>";
			// display image with link if defined
			if ( $Media->Slika != "" && fileExists($StoreRoot ."/media/thumbs/". $Media->Slika) ) {
				echo "\t<A HREF=\"$WebPath/media/". $Media->Datoteka ."\" TARGET=\"_blank\">";
				echo "<img src=\"$WebPath/media/thumbs/". $Media->Slika ."\" align=\"left\" alt=\"\" class=\"thumb\" border=0></A>\n";
			}
			// display description
			echo "\t<SPAN CLASS=\"a10\">". $Media->Opis ."</SPAN>\n";
			echo "</div>\n";
		}
	}
	echo "</div>\n";
}
// free recordset
unset($Medias);
