<?php
/*~ edit_Predloge.php - site template upload
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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "0";

if ( isset($_POST['Naziv']) && $_POST['Naziv'] !== "" ) {

	$db->query("START TRANSACTION");
	if ( $_GET['ID'] != "0" ) {
		$db->query(
			"UPDATE Tags
			SET TagName = '". $db->escape($_POST['Naziv']) ."'
			WHERE TagID = ". (int)$_GET['ID']
			);
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
				". (int)$_GET['ID'] .",
				'Tags',
				'Update tag name',
				'". $db->escape($_POST['Naziv']) ."'
			)"
			);
	} else {
		// not possible
	}
	$db->query("COMMIT");
}
?>