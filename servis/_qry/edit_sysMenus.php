<?php
/* SQL queries and preprocessing for admin page menu layout.
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

// get first available top level menu ID
if ( !isset($_GET['ID']) || $_GET['ID'] == "" || $_GET['ID'] === "0" ) {
	$ActionID = $db->get_var("SELECT max(ActionID) FROM SMActions WHERE ActionID LIKE '__'");

	$_GET['ID'] = sprintf( "%0".strlen($ActionID)."d", (int)$ActionID + 1 );
	$_SERVER['QUERY_STRING'] = preg_replace("/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING']) ."&ID=". $_GET['ID'];
}

if ( isset($_POST['Name']) ) {
	$db->query("START TRANSACTION");
	$ActionID = $db->get_var("SELECT ActionID FROM SMActions WHERE ActionID='". $db->escape($_GET['ID']) ."'");

	if ( $ActionID ) {
		$db->query(
			"UPDATE SMActions
			SET Name   =" . (($_POST['Name'] != "")   ? "'".$db->escape($_POST['Name'])."'" : "NULL" ) . ",
				Enabled=" . (isset($_POST['Show']) && $_POST['Show'] == "yes" ? "1" : "0" ) . ",
				MobileCapable=" . (isset($_POST['Mobile']) && $_POST['Mobile'] == "yes" ? "1" : "0" ) . ",
				Action =" . (($_POST['Action'] != "") ? "'".$db->escape($_POST['Action'])."'" : "NULL" ) . ",
				Icon   =" . (($_POST['Icon'] != "")   ? "'".$db->escape($_POST['Icon'])."'" : "NULL" ) . "
			WHERE ActionID = '".$_GET['ID']."'"
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
				NULL,
				'SMActions',
				'Update',
				'". $db->escape($_GET['ID']) .",". $db->escape($_POST['Name']) .",". $db->escape($_POST['Action']) .",". (isset($_POST['Show']) && $_POST['Show'] == "yes" ? "1" : "0" ) ."'
			)"
			);
	} else {
		$db->query(
			"INSERT INTO SMActions (ActionID, Name, Action, Enabled, MobileCapable, Icon)
			VALUES ('".$_GET['ID']."',"
				. (($_POST['Name'] != "")   ? "'".$db->escape($_POST['Name'])."'" : "NULL" ) . ","
				. (($_POST['Action'] != "") ? "'".$db->escape($_POST['Action'])."'" : "NULL" ) . ","
				. (isset($_POST['Show']) && $_POST['Show'] == "yes" ? "1" : "0" ) . ","
				. (isset($_POST['Mobile']) && $_POST['Mobile'] == "yes" ? "1" : "0" ) . ","
				. (($_POST['Icon'] != "")   ? "'".$db->escape($_POST['Icon'])."'" : "NULL" ) . ")" );
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
				NULL,
				'SMActions',
				'Insert',
				'". $db->escape($_GET['ID']) .",". $db->escape($_POST['Name']) .",". $db->escape($_POST['Action']) .",". (isset($_POST['Show']) && $_POST['Show'] == "yes" ? "1" : "0" ) ."'
			)"
			);
	}
	$db->query("COMMIT");
}

// delete access control list (ACL)
if ( isset( $_GET['BrisiACL'] ) && $_GET['BrisiACL'] != "" ) {
	$db->query("START TRANSACTION");
	$db->query("UPDATE SMActions SET ACLID = NULL WHERE ACLID = " . $_GET['BrisiACL']);
	$db->query("DELETE FROM SMACLr WHERE ACLID = " . $_GET['BrisiACL']);
	$db->query("DELETE FROM SMACL  WHERE ACLID = " . $_GET['BrisiACL']);
	$db->query("COMMIT");
}
?>