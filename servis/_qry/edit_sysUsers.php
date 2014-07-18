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

if ( $_GET['ID'] == "0" ) {
	if ( isset($_POST['Name']) ) {
		$db->query("START TRANSACTION");
		$db->query( "INSERT INTO SMUser (Name, Email, Phone, TwitterName, Username, Password, Active, DefGrp)
			VALUES ('".$db->escape($_POST['Name'])."', '".
				$db->escape($_POST['Email'])."', '".
				$db->escape($_POST['Phone'])."', '".
				$db->escape($_POST['TwitterName'])."', '".
				$db->escape($_POST['Username'])."', '".
				$db->escape(crypt(PWSALT . $_POST['Password']))."',".
				( (isset($_POST['Active']) && $_POST['Active'] == "yes") ? "1," : "0," ).
				( $_POST['DefGrp']!=""? (int)$_POST['DefGrp'] : "NULL" ).")" );
		// get inserted ID
		$_GET['ID'] = $db->insert_id;
		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace( "/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING'] ) . "&ID=" . $_GET['ID'];
		// add new user to everyone group
		$db->query("INSERT INTO SMUserGroups (GroupID, UserID) VALUES (1, $db->insert_id)");
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
				'SMUser',
				'Add user',
				'".$db->escape($_POST['Name']).",".
				$db->escape($_POST['Email']).",".
				$db->escape($_POST['Phone']).",".
				$db->escape($_POST['TwitterName']).",".
				$db->escape($_POST['Username']).",".
				((isset($_POST['Active']) && $_POST['Active'] == "yes") ? "1" : "0") ."'
			)"
			);
		$db->query("COMMIT");
	}
} else {
	if ( count($_POST) && !isset($_POST['GroupList']) ) {
		$db->query("START TRANSACTION");
		foreach ( $_POST as $name => $value ) {
			switch ( $name ) {
				case "DefGrp":
					$set = ($value!="" ? (int)$value : "NULL");
					break;
				case "Active":
					$set = ($value=="yes" ? 1 : 0);
					break;
				case "Password":
					$set = ($value!="" ? "'".$db->escape(crypt(PWSALT . $value))."'" : NULL); // empty password -> no change
					break;
				default :
					$set = ($value!="" ? "'".$db->escape($value)."'" : "NULL");
					break;
			}
			if ( $set )
				$db->query( "UPDATE SMUser SET $name = $set WHERE UserID = ".(int)$_GET['ID'] );
		}
		if ( isset($_POST['Name']) && isset($_POST['Password']) )
			$db->query( "UPDATE SMUser SET Active = ".( (isset($_POST['Active']) && $_POST['Active']=="yes") ? "1" : "0" )." WHERE UserID = " . (int)$_GET['ID'] );
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
				'SMUser',
				'Update user',
				'".$db->escape($_POST['Name']).",".
				$db->escape($_POST['Email']).",".
				$db->escape($_POST['Phone']).",".
				$db->escape($_POST['TwitterName']).",".
				$db->escape($_POST['Username']).",".
				((isset($_POST['Active']) && $_POST['Active'] == "yes") ? "1" : "0") ."'
			)"
			);
		$db->query("COMMIT");

	} else if ( isset($_POST['GroupList']) && $_POST['GroupList'] !== "" && isset( $_POST['Action'] ) ) {

		$db->query("START TRANSACTION");
		if ( $_POST['Action'] == "Add" )
			foreach ( explode(",", $_POST['GroupList']) as $GroupID ) {
				$db->query( "INSERT INTO SMUserGroups (GroupID, UserID) VALUES ($GroupID,".(int)$_POST['UserID'].")" );
			}
		if ( $_POST['Action'] == "Remove" )
			$db->query("DELETE FROM SMUserGroups WHERE UserID=".(int)$_POST['UserID']." AND GroupID IN (".$db->escape($_POST['GroupList']).")");
		if ( $_POST['Action'] == "Set" ) {
			$db->query("DELETE FROM SMUserGroups WHERE UserID=".(int)$_POST['UserID']);
			foreach ( explode(",", $_POST['GroupList']) as $GroupID ) {
				$db->query( "INSERT INTO SMUserGroups (GroupID, UserID) VALUES ($GroupID,".(int)$_POST['UserID'].")" );
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
				". (int)$_GET['ID'] .",
				'SMUserGroups',
				'Change user membership',
				'". (int)$_POST['UserID'] .",". $db->escape($_POST['Action']) .",". $db->escape($_POST['GroupList']) ."'
			)"
			);
		$db->query("COMMIT");
	}
}
?>