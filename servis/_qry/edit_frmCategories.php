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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "0";

// creating new room
if ( isset($_POST['Name']) && $_POST['Name'] != "" ) {
	if ( $_GET['ID'] != "0" ) {
		$db->query(
			"UPDATE frmCategories
			SET CategoryName = '". $db->escape($_POST['Name']) ."',
				Administrator = ". ((int)$_POST['Admin'] ? (int)$_POST['Admin'] : "NULL")."
			WHERE ID = ". (int)$_GET['ID']
			);
	} else {
		$Polozaj = $db->get_var( "SELECT max(CategoryOrder) FROM frmCategories" );
		$db->query(
			"INSERT INTO frmCategories (
				CategoryName,
				Administrator,
				CategoryOrder
			) VALUES (
				'". $db->escape($_POST['Name']) ."',
				". ((int)$_POST['Admin'] ? (int)$_POST['Admin'] : "NULL") .",
				". ($Polozaj? $Polozaj+1: 1) ."
			)"
			);
		//retreive ACL's ID
		$_GET['ID'] = $db->insert_id;
		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace("/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING']) ."&ID=". $_GET['ID'];
	}
}
?>
