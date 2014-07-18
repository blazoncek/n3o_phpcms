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

if ( isset($_POST['Name']) && $_POST['Name'] != "" ) {
	$db->query("START TRANSACTION");
	// only allow inserting new groups and changing custom groups
	if ( $_GET['ID'] > 4 ) {
		$db->query("UPDATE SMGroup SET Name = '". $db->escape($_POST['Name']) ."' WHERE GroupID = ". (int)$_GET['ID']);
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
				'SMGroup',
				'Change group name',
				'". $db->escape($_POST['Name']) ."'
			)"
			);
	} elseif ( $_GET['ID'] == 0 ) {
		$db->query("INSERT INTO SMGroup (Name) VALUES ('".$db->escape($_POST['Name'])."')");
		// get inserted ID
		$_GET['ID'] = $db->insert_id;
		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace("/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING']) ."&ID=". $_GET['ID'];
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
				'SMGroup',
				'Add group',
				'". $db->escape($_POST['Name']) ."'
			)"
			);
	}
	$db->query("COMMIT");
}

if ( isset($_POST['UserList']) && $_POST['UserList'] !== "" && isset($_POST['Action']) ) {
	$db->query("START TRANSACTION");
	if ( $_POST['Action'] == "Add" )
		foreach ( explode(",", $_POST['UserList']) as $UserID ) {
			$db->query("INSERT INTO SMUserGroups (GroupID, UserID) VALUES (".(int)$_POST['GroupID'].",$UserID)");
		}
	if ( $_POST['Action'] == "Remove" )
		$db->query("DELETE FROM SMUserGroups WHERE GroupID = ".(int)$_POST['GroupID']." AND UserID IN (".$db->escape($_POST['UserList']).")");
	if ( $_POST['Action'] == "Set" ) {
		$db->query("DELETE FROM SMUserGroups WHERE GroupID = ".(int)$_POST['GroupID']);
		foreach ( explode(",", $_POST['UserList']) as $UserID ) {
			$db->query("INSERT INTO SMUserGroups (GroupID, UserID) VALUES (".(int)$_POST['GroupID'].",$UserID)");
		}
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
			". $_GET['ID'] .",
			'SMUserGroups',
			'Change group membership',
			'". (int)$_POST['GroupID'] .",". $db->escape($_POST['Action']) .",". $db->escape($_POST['UserList']) ."'
		)"
		);
	$db->query("COMMIT");
}
?>