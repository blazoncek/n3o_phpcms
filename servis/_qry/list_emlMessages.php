<?php
/*
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend and backend)                                 |
|   Version: 2.2.2                                                          |
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

if ( isset($_GET['Brisi']) && (int)$_GET['Brisi'] ) {
	$db->query("START TRANSACTION");
	$Datoteke = $db->get_results("SELECT Datoteka FROM emlMessagesDoc WHERE emlMessageID=". (int)$_GET['Brisi']);

	// BRISANJE DATOTEK
	if ( $Datoteke ) foreach ( $Datoteke as $Datoteka ) {
		$tPath = $StoreRoot . (contains($Datoteka,"/") ? "/" : "/media/");
		@unlink($tPath . $Datoteka);
		
		// for image files delete eventual thumbs and originals
		$tDir = dirname($tPath . $Datoteka); // get full path
		$tFile = basename($tPath . $Datoteka); // get filename
		if ( is_file($tDir ."/large/". $tFile) )
			@unlink($tDir ."/large/". $tFile);
		if ( is_file($tDir ."/thumbs/". $tFile) )
			@unlink($tDir ."/thumbs/". $tFile);
	}

	// audit action
	$db->query(
		"INSERT INTO SMAudit (
			UserID,
			ObjectID,
			ObjectType,
			Action,
			Description
		) VALUES (
			". $_SESSION['UserID'] .",
			". (int)$_GET['Brisi'] .",
			'Mailing message',
			'Delete mail message',
			'". $db->get_var("SELECT Naziv FROM emlMessages WHERE emlMessageID=". (int)$_GET['Brisi']) ."'
		)"
		);
	$db->query("DELETE FROM emlMessagesDoc WHERE emlMessageID=". (int)$_GET['Brisi']);
	$db->query("DELETE FROM emlMessagesGrp WHERE emlMessageID=". (int)$_GET['Brisi']);
	$db->query("DELETE FROM emlMessagesTxt WHERE emlMessageID=". (int)$_GET['Brisi']);
	$db->query("DELETE FROM emlMessages    WHERE emlMessageID=". (int)$_GET['Brisi']);
	$db->query("COMMIT");
}
?>