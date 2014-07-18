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

if ( isset($_POST['NLSToken']) && $_POST['NLSToken'] != "" ) {

	$db->query("START TRANSACTION");
	$db->query(
		"INSERT INTO NLSText (".
		"	NLSToken,".
		"	Jezik,".
		"	NLSShort,".
		"	NLSLong".
		") VALUES (".
		"	'". $db->escape($_POST['NLSToken']) ."',".
		"	". ($_POST['Jezik']!="" ? "'".$db->escape($_POST['Jezik'])."'" : "NULL").",".
		"	". ($_POST['NLSShort']!="" ? "'".$db->escape($_POST['NLSShort'])."'" : "NULL").",".
		"	". ($_POST['NLSLong']!="" ? "'".$db->escape($_POST['NLSLong'])."'" : "NULL").
		")" );
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
			'NLS Text',
			'Add NLS text',
			'". $db->escape($_POST['NLSToken']) .",". $db->escape($_POST['Jezik']) .",". ($_POST['NLSShort']!="" ? $db->escape($_POST['NLSShort']) : $db->escape($_POST['NLSLong'])) ."'
		)"
		);
	$db->query("COMMIT");

} elseif ( isset($_POST['Jezik']) && $_POST['Jezik'] != "" ) {

	$db->query("START TRANSACTION");
	$db->query(
		"UPDATE NLSText ".
		"SET NLSShort = ".(($_POST['NLSShort']!="")? "'".$db->escape($_POST['NLSShort'])."'": "NULL").",".
		"	NLSLong = ".(($_POST['NLSLong']!="")? "'".$db->escape($_POST['NLSLong'])."'": "NULL")." ".
		"WHERE NLSToken = '".$db->escape($_GET['ID'])."' AND".
		"	Jezik = '".$db->escape($_POST['Jezik'])."'" );
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
			'NLS Text',
			'Update NLS text',
			'". $db->escape($_GET['ID']) .",". $db->escape($_POST['Jezik']) .",". ($_POST['NLSShort']!="" ? $db->escape($_POST['NLSShort']) : $db->escape($_POST['NLSLong'])) ."'
		)"
		);
	$db->query("COMMIT");
}
?>