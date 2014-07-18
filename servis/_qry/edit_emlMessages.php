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

if ( isset($_POST['Naziv']) && $_POST['Naziv'] != "" ) {
	$db->query("START TRANSACTION");
	if ( $_GET['ID']=="0" ) {
		$db->query("INSERT INTO emlMessages (Naziv) VALUES ('". $db->escape($_POST['Naziv']) ."')");
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
				'Mailing message',
				'Add mailing mesage',
				'". $db->escape($_POST['Naziv']) ."'
			)"
			);
		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace("/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING']) ."&ID=". $_GET['ID'];
	} else {
		$db->query(
			"UPDATE emlMessages
			SET Naziv = '".$db->escape($_POST['Naziv'])."'
			WHERE emlMessageID = ". (int)$_GET['ID']
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
				'Mailing message',
				'Update mailing mesage',
				'". $db->escape($_POST['Naziv']) ."'
			)"
			);
	}
	$db->query("COMMIT");
}

if ( isset($_POST['MemberList']) && $_POST['MemberList'] !== "" && isset($_POST['Action']) ) {
	$db->query("START TRANSACTION");
	if ( $_POST['Action'] == "Add" )
		foreach ( explode( ",", $_POST['MemberList'] ) as $UserID ) {
			$db->query(
				"INSERT INTO emlMessagesGrp (emlMessageID, emlGroupID)
				VALUES (".(int)$_POST['MessageID'].",$UserID)"
			);
		}
	if ( $_POST['Action'] == "Remove" )
		$db->query(
			"DELETE FROM emlMessagesGrp
			WHERE emlMessageID = ".(int)$_POST['MessageID']."
			  AND emlGroupID IN (".$db->escape($_POST['MemberList']).")"
		);
	if ( $_POST['Action'] == "Set" ) {
		$db->query(
			"DELETE FROM emlMessagesGrp
			WHERE emlMessageID = ".(int)$_POST['MessageID']
		);
		foreach ( explode(",", $_POST['MemberList']) as $UserID ) {
			$db->query(
				"INSERT INTO emlMessagesGrp (emlMessageID, emlGroupID)
				VALUES (".(int)$_POST['MessageID'].",$UserID)"
			);
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
			'Mailing message',
			'Update recipients',
			'". $db->get_var("SELECT Naziv FROM emlMessages WHERE emlMessageID=". (int)$_GET['ID'])
			.",". $db->escape($_POST['Action']) .",". $db->escape($_POST['MemberList']) ."'
		)"
		);
	$db->query("COMMIT");
}

//delete title/description
if ( isset($_GET['BrisiOpis']) ) {
	$db->query("START TRANSACTION");
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
			'Mailing message',
			'Delete message content',
			'". $db->get_var("SELECT Naziv FROM emlMessages WHERE emlMessageID=". (int)$_GET['ID'])
			.",". $db->get_var("SELECT Naziv FROM emlMessageTxt WHERE emlMessageTxtID=". (int)$_GET['BrisiOpis']) ."'
		)"
		);
	$db->query("DELETE FROM emlMessagesTxt WHERE emlMessageTxtID = ". (int)$_GET['BrisiOpis']);
	$db->query("COMMIT");
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace("/\&BrisiOpis=[0-9]+/", "", $_SERVER['QUERY_STRING']);
}

// adding mesage content
if ( isset($_POST['Subject']) && $_POST['Subject']!="" ) {
	// cleanup
	$_POST['Subject'] = $db->escape(str_replace("\"", "&quot;", left($_POST['Subject'],128)));
	$_POST['Opis']    = str_replace("\\\"","\&quot;",$db->escape(CleanupTinyMCE($_POST['Opis'])));

	$db->query("START TRANSACTION");
	if ( isset($_POST['OpisID']) ) {
		$db->query(
			"UPDATE emlMessagesTxt
			SET Naziv = '". $_POST['Subject'] ."',
				Opis = '". $_POST['Opis'] ."'
			WHERE emlMessageTxtID = ".(int)$_POST['OpisID']
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
				'Mailing message',
				'Update message content',
				'". $db->get_var("SELECT Naziv FROM emlMessages WHERE emlMessageID=". (int)$_GET['ID'])
				.",". $_POST['Subject'] ."'
			)"
			);
	} else {
		$db->query(
			"INSERT INTO emlMessagesTxt (
				Jezik,
				emlMessageID,
				Naziv,
				Opis
			) VALUES (
				". ($_POST['Jezik']!="" ? "'".$_POST['Jezik']."'" : "NULL") .",
				". (int)$_GET['ID'] .",
				'". $_POST['Subject'] ."',
				'". $_POST['Opis'] ."'
			)"
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
				'Mailing message',
				'Add message content',
				'". $db->get_var("SELECT Naziv FROM emlMessages WHERE emlMessageID=". (int)$_GET['ID'])
				.",". $_POST['Subject'] ."'
			)"
			);
	}
	$db->query("COMMIT");
}
?>
