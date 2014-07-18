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

if ( isset($_POST['Naziv']) && $_POST['Naziv'] != "" ) {
	$db->query("START TRANSACTION");
	// only allow inserting new groups and changing custom groups
	if ( $_GET['ID'] > 0 ) {
		$db->query(
			"UPDATE emlGroups
			SET Naziv = '". $db->escape($_POST['Naziv']) ."',
				KtgID = ". ($_POST['KtgID']!="" ? "'". $db->escape($_POST['KtgID']) ."'" : "NULL") ."
			WHERE emlGroupID = ". (int)$_GET['ID']
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
				'Mailing group',
				'Update mailing group',
				'". $db->escape($_POST['Naziv']) ."'
			)"
			);
	} else {
		$db->query(
			"INSERT INTO emlGroups (Naziv, KtgID)
			VALUES ('". $db->escape($_POST['Naziv']) ."',". ($_POST['KtgID']!="" ? "'". $db->escape($_POST['KtgID']) ."'" : "NULL") .")"
			);
		// get inserted ID
		$_GET['ID'] = $db->insert_id;
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
				'Mailing group',
				'Add mailing group',
				'". $db->escape($_POST['Naziv']) ."'
			)"
			);
		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace("/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING']) ."&ID=". $_GET['ID'];
	}
	$db->query("COMMIT");
}

if ( isset($_POST['MemberList']) && $_POST['MemberList'] !== "" && isset($_POST['Action']) ) {
	$db->query("START TRANSACTION");
	if ( $_POST['Action'] == "Add" )
		foreach ( explode( ",", $_POST['MemberList'] ) as $UserID ) {
			$db->query( "INSERT INTO emlMembersGrp (emlGroupID, emlMemberID) VALUES (".(int)$_POST['GroupID'].",$UserID)" );
		}
	if ( $_POST['Action'] == "Remove" )
		$db->query( "DELETE FROM emlMembersGrp WHERE emlGroupID = ".(int)$_POST['GroupID']." AND emlMemberID IN (".$db->escape($_POST['MemberList']).")" );
	if ( $_POST['Action'] == "Set" ) {
		$db->query( "DELETE FROM emlMembersGrp WHERE emlGroupID = ".(int)$_POST['GroupID'] );
		foreach ( explode( ",", $_POST['MemberList'] ) as $UserID ) {
			$db->query( "INSERT INTO emlMembersGrp (emlGroupID, emlMemberID) VALUES (".(int)$_POST['GroupID'].",$UserID)" );
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
			'Mailing user',
			'Change group membership',
			'". $db->get_var("SELECT Naziv FROM emlGroups WHERE emlGroupID=". (int)$_GET['ID'])
			.",". $db->escape($_POST['Action']) .",". $db->escape($_POST['MemberList']) ."'
		)"
		);
	$db->query("COMMIT");
}
?>