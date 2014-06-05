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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "0";

if ( $_GET['ID'] == "0" ) {
	if ( isset($_POST['Naziv']) ) {
		$db->query( "START TRANSACTION" );
		$db->query( "INSERT INTO emlMembers (Naziv, Podjetje, Naslov, Posta, Telefon, Fax, GSM, Email, Jezik, Aktiven, Datum)
			VALUES ('".$db->escape($_POST['Naziv'])."',
				'".$db->escape($_POST['Podjetje'])."',
				'".$db->escape($_POST['Naslov'])."',
				'".$db->escape($_POST['Posta'])."',
				'".$db->escape($_POST['Telefon'])."',
				'".$db->escape($_POST['Fax'])."',
				'".$db->escape($_POST['GSM'])."',
				'".$db->escape($_POST['Email'])."',
				".($_POST['Jezik']=='' ? 'NULL' : "'".$db->escape($_POST['Jezik'])."'").",
				".((isset($_POST['Aktiven']) && $_POST['Aktiven']=="yes") ? "1" : "0").",
				'".date("Y-n-j H:m:s") ."')" );
		// get inserted ID
		$_GET['ID'] = $db->insert_id;
		$db->query( "COMMIT" );
		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace( "/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING'] ) . "&ID=" . $_GET['ID'];
	}
} else {
	if ( count($_POST) && !isset($_POST['GroupList']) ) {
		$db->query( "START TRANSACTION" );
		foreach ( $_POST as $name => $value ) {
			switch ( $name ) {
				case "Aktiven":
					$set = ($value=="yes" ? 1 : 0);
					break;
				//case "Geslo":
				//	$set = ($value!="" ? "'".$db->escape(MD5(PWSALT.$value))."'" : "NULL");
				//	break;
				default :
					$set = ($value!="" ? "'".$db->escape($value)."'" : "NULL");
					break;
			}
			$db->query( "UPDATE emlMembers SET $name = $set WHERE emlMemberID = ".(int)$_GET['ID'] );
		}
		// fix for standard checkbox (undefined if not checked)
		if ( isset($_POST['Naziv']) && !isset($_POST['Aktiven']) )
			$db->query( "UPDATE emlMembers SET Aktiven = 0 WHERE emlMemberID = ".(int)$_GET['ID'] );
		$db->query( "COMMIT" );

	} else if ( isset($_POST['GroupList']) && $_POST['GroupList'] !== "" && isset($_POST['Action']) ) {

		$db->query( "START TRANSACTION" );
		if ( $_POST['Action'] == "Add" )
			foreach ( explode( ",", $_POST['GroupList'] ) as $GroupID ) {
				$db->query( "INSERT INTO emlMembersGrp (emlGroupID, emlMemberID) VALUES ($GroupID,".(int)$_POST['UserID'].")" );
			}
		if ( $_POST['Action'] == "Remove" )
			$db->query( "DELETE FROM emlMembersGrp WHERE emlMemberID = ".(int)$_POST['UserID']." AND emlGroupID IN (".$_POST['GroupList'].")" );
		if ( $_POST['Action'] == "Set" ) {
			$db->query( "DELETE FROM emlMembersGrp WHERE emlMemberID = ".(int)$_POST['UserID'] );
			foreach ( explode( ",", $_POST['GroupList'] ) as $GroupID ) {
				$db->query( "INSERT INTO emlMembersGrp (emlGroupID, emlMemberID) VALUES ($GroupID,".(int)$_POST['UserID'].")" );
			}
		}
		$db->query( "COMMIT" );
	}
}
?>