<?php
/* _kategorije.php - Menu with categories/subcategories.
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

// get available categories (1st level)
$Kategorije = $db->get_results(
	"SELECT
		K.KategorijaID AS ID,
		K.Ime,
		KN.Naziv,
		KN.Povzetek
	FROM
		Kategorije K
			LEFT JOIN KategorijeNazivi KN
				ON K.KategorijaID=KN.KategorijaID
	WHERE
		K.Izpis <> 0 AND
		K.Ime NOT LIKE '.%' AND
		K.KategorijaID LIKE '__' AND
		(KN.Jezik='" . $lang . "' OR KN.Jezik IS NULL)
	ORDER BY
		K.KategorijaID,
		KN.Jezik DESC"
	);

// if we have more than 1 category
if ( ($RecordCount = count($Kategorije)) > 1 ) {

	// counter
	$CurrentRow = 0;

	echo "\t<UL CLASS=\"menu\">\n";
	// repeat for each category
	foreach ( $Kategorije as $Kat ) {
		// get display name
		$Naslov = $Kat->Naziv == "" ? $Kat->Ime : $Kat->Naziv;

		// hilite the selected menu (open DIV)
		if ( $Kat->ID == left($_GET['kat'],2) )
			echo "\t<LI STYLE=\"background-color:". $BckHiColor .";\"><B>";
		else
			echo "\t<LI>";
		// display menu with link
		$link = ($TextPermalinks) ? ($IsIIS ? $WebFile .'/' : ''). $Kat->Ime .'/' : '?kat='. $Kat->ID;
		echo "<A HREF=\"". $WebPath ."/". $link ."\">". $Naslov ."</A>";
		// close DIV
		if ( $Kat->ID == left($_GET['kat'],2) )
			echo "</B>";
		echo "</LI>\n";

		// if selected category, display subcategories
		if ( left($_GET['kat'], 2) == $Kat->ID ) {
			// get recordset
			$SubKat = $db->get_results(
				"SELECT
					K.KategorijaID AS ID,
					K.Ime,
					KN.Naziv,
					KN.Povzetek
				FROM
					Kategorije K
						LEFT JOIN KategorijeNazivi KN
							ON K.KategorijaID = KN.KategorijaID
				WHERE
					K.Izpis <> 0 AND
					K.Ime NOT LIKE '.%' AND
					K.KategorijaID LIKE '" . $Kat->ID . "__' AND
					(KN.Jezik = '$lang' OR KN.Jezik IS NULL)
				ORDER BY
					K.KategorijaID,
					KN.Jezik DESC"
				);

			// if subcategories exist
			if ( count( $SubKat ) > 0 ) {
				echo "\t<UL CLASS=\"submenu\">";
				foreach ( $SubKat as $Kat2 ) {
					// get display name
					if ( $Kat2->Naziv == "" )
						$Naslov = $Kat2->Ime;	// če je jezikovni opis prazen uporabim ime/sklic
					else
						$Naslov = $Kat2->Naziv;
					// hilite the selected menu (open DIV)
					if ( $_GET['kat'] == $Kat2->ID )
						echo "\t\t<LI STYLE=\"background-color:". $BckHiColor .";\"><B>";
					else
						echo "\t\t<LI>";
					// display menu with link
					$link = ($TextPermalinks) ? ($IsIIS ? $WebFile .'/' : ''). $Kat2->Ime .'/' : '?kat='. $Kat2->ID;
					echo "<A HREF=\"". $WebPath ."/". $link ."\">". $Naslov ."</A>";
					// close DIV
					if ( $_GET['kat'] == $Kat2->ID )
						echo "</B>";
					echo "</LI>\n";
				}
				echo "\t</UL>\n";
			}
			// free recordset
			unset($SubKat);
		}
	}
	echo "\t</UL>\n";
}
// free recordset
unset($Kategorije);
?>
