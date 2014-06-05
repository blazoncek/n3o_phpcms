<?php
/* _gallery_latest.php - Display recent images uploaded to gallery or other media folder.
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

// define type of links
$kat = $TextPermalinks ? ($IsIIS ? $WebFile .'/' : ''). $KatText .'/?' : '?kat='. $_GET['kat'] .'&amp;';
$bid = '';

// select images attached to texts in photography category
$rub = $db->get_var("SELECT KategorijaID FROM Kategorije WHERE Ime IN ('fotografija','photography') LIMIT 1");

$Galerija = $db->get_results(
	"SELECT
		M.MediaID,
		M.Naziv,
		M.Datoteka,
		M.Meta,
		MO.Naslov,
		MO.Opis
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

include('__gallery.php');
