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

if ( isset( $_GET['Brisi'] ) && (int)$_GET['Brisi'] != "" ) {
	$db->query( "START TRANSACTION" );

	// delete image
	$Slika    = $db->get_var("SELECT Slika FROM Media WHERE MediaID = ".(int)$_GET['Brisi']);
	if ( $Slika && $Slika != "" ) {
		$e = right($Slika, 4);
		$b = left($Slika, strlen($Slika)-4);
		@unlink($StoreRoot ."/media/media/". $Slika);
		@unlink($StoreRoot ."/media/media/". $b .'@2x'. $e);
	}

	// delete main file
	$Datoteka = $db->get_var("SELECT Datoteka FROM Media WHERE MediaID = ".(int)$_GET['Brisi']);
	if ( $Datoteka && $Datoteka != "" ) {
		$tPath = $StoreRoot . (contains($Datoteka,"/")? "/": "/media/");
		$tDir = dirname($tPath . $Datoteka);   // get full path
		$tFile = basename($tPath . $Datoteka); // get filename

		// remove file
		@unlink($tDir ."/". $tFile);

		// for image files delete eventual thumbs and originals
		$e = right($tFile, 4);
		$b = left($tFile, strlen($tFile)-4);
		@unlink($tDir ."/". $b .'@2x'. $e);
		@unlink($tDir ."/thumbs/". $tFile);
		@unlink($tDir ."/thumbs/". $b .'@2x'. $e);
		@unlink($tDir ."/large/". $tFile);
	}

	$db->query("DELETE FROM BesedilaMedia   WHERE MediaID = ".(int)$_GET['Brisi']);
	$db->query("DELETE FROM BesedilaSlike   WHERE MediaID = ".(int)$_GET['Brisi']);
	$db->query("DELETE FROM KategorijeMedia WHERE MediaID = ".(int)$_GET['Brisi']);
	$db->query("DELETE FROM MediaOpisi      WHERE MediaID = ".(int)$_GET['Brisi']);
	$db->query("DELETE FROM Media           WHERE MediaID = ".(int)$_GET['Brisi']);

	$db->query("COMMIT");
}
?>