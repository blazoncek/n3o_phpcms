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

if ( isset($_GET['Brisi']) && (int)$_GET['Brisi'] != "" ) {
	$db->query("START TRANSACTION");
	$ACLID    = $db->get_var("SELECT ACLID    FROM Sifranti WHERE SifrantID = ". (int)$_GET['Brisi']);
	$SifrCtrl = $db->get_var("SELECT SifrCtrl FROM Sifranti WHERE SifrantID = ". (int)$_GET['Brisi']);

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
			'Parameters',
			'Delete parameter',
			'". $SifrCtrl ."'
		)"
		);

	$db->query("DELETE FROM SifrantiTxt WHERE SifrantID = ". (int)$_GET['Brisi']);
	$db->query("DELETE FROM Sifranti    WHERE SifrantID = ". (int)$_GET['Brisi']);

	// remove ACL if no parameters exist for this type
	$test = $db->get_var("SELECT SifrantID FROM Sifranti WHERE SifrCtrl = '". $SifrCtrl . "'");
	if ( !$test ) {
		$db->query("DELETE FROM SmACLr WHERE ACLID = ". (int)$ACLID);
		$db->query("DELETE FROM SmACL  WHERE ACLID = ". (int)$ACLID);
		$_GET['Tip'] = "";
	} else
		$_GET['Tip'] = $SifrCtrl;
	$db->query("COMMIT");
}

// move items up/down
if ( isset( $_GET['Smer'] ) && $_GET['Smer'] != "" ) {
	$db->query("START TRANSACTION");
	// calculate new position
	$ItemPos = $db->get_var("SELECT SifrZapo FROM Sifranti WHERE SifrantID = ". (int)$_GET['Item']);
	$ItemNew = $ItemPos + (int)$_GET['Smer'];
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
			". (int)$_GET['Item'] .",
			'Parameters',
			'Move parameter',
			'". $db->get_var("SELECT SifrCtrl FROM Sifranti WHERE SifrantID = ". (int)$_GET['Item']) .",". $ItemPos ."->". $ItemNew ."'
		)"
		);
	// move
	$db->query( "UPDATE Sifranti SET SifrZapo=9999     WHERE SifrCtrl='".$db->escape($_GET['Tip'])."' AND SifrZapo=$ItemNew" );
	$db->query( "UPDATE Sifranti SET SifrZapo=$ItemNew WHERE SifrCtrl='".$db->escape($_GET['Tip'])."' AND SifrZapo=$ItemPos" );
	$db->query( "UPDATE Sifranti SET SifrZapo=$ItemPos WHERE SifrCtrl='".$db->escape($_GET['Tip'])."' AND SifrZapo=9999" );
	$db->query("COMMIT");
}
?>