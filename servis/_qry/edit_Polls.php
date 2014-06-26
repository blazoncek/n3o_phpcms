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

if ( isset($_POST['D']) || isset($_POST['V']) || (isset($_POST['O1']) && isset($_POST['O2'])) ) {
	// minimum two answers
	$StOdg = 2;
	// check the number of answers
	if ( $_POST['O3'] != "" ) $StOdg = 3;
	if ( $_POST['O4'] != "" ) $StOdg = 4;
	if ( $_POST['O5'] != "" ) $StOdg = 5;
	if ( $_POST['O6'] != "" ) $StOdg = 6;
	if ( $_POST['O7'] != "" ) $StOdg = 7;
	if ( $_POST['O8'] != "" ) $StOdg = 8;
	if ( $_POST['O9'] != "" ) $StOdg = 9;
	if ( $_POST['O10'] != "" ) $StOdg = 10;
	
	$db->query("START TRANSACTION");
	if ( $_GET['ID'] != "0" ) {
		$db->query(
			"UPDATE Ankete ".
			"SET Datum = '".date("Y-m-d",strtotime($_POST['D']))."',".
			"    Vprasanje = '".$db->escape(left($_POST['V'],255))."',".
			"    Komentar = ".(($_POST['K']!="")? "'".$db->escape(left($_POST['K'],255))."'": "NULL").",".
			"    Odg1 = ".(($_POST['O1']!="")? "'".$db->escape($_POST['O1'])."'": "NULL").",".
			"    Odg2 = ".(($_POST['O2']!="")? "'".$db->escape($_POST['O2'])."'": "NULL").",".
			"    Odg3 = ".(($_POST['O3']!="")? "'".$db->escape($_POST['O3'])."'": "NULL").",".
			"    Odg4 = ".(($_POST['O4']!="")? "'".$db->escape($_POST['O4'])."'": "NULL").",".
			"    Odg5 = ".(($_POST['O5']!="")? "'".$db->escape($_POST['O5'])."'": "NULL").",".
			"    Odg6 = ".(($_POST['O6']!="")? "'".$db->escape($_POST['O6'])."'": "NULL").",".
			"    Odg7 = ".(($_POST['O7']!="")? "'".$db->escape($_POST['O7'])."'": "NULL").",".
			"    Odg8 = ".(($_POST['O8']!="")? "'".$db->escape($_POST['O8'])."'": "NULL").",".
			"    Odg9 = ".(($_POST['O9']!="")? "'".$db->escape($_POST['O9'])."'": "NULL").",".
			"    Odg10 = ".(($_POST['O10']!="")? "'".$db->escape($_POST['O10'])."'": "NULL").",".
			"	StOdg = $StOdg,".
			"	Multiple = ".(isset($_POST['Multiple'])? "1": "0")." ".
			"WHERE ID = " . (int)$_GET['ID']
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
				'Poll',
				'Update poll',
				'". $db->escape(left($_POST['V'],255)) ."'
			)"
			);
	} else {
		$db->query(
			"INSERT INTO Ankete (".
			"	Jezik, Datum, Vprasanje, Komentar,".
			"	Odg1, Odg2, Odg3, Odg4, Odg5, Odg6, Odg7, Odg8, Odg9, Odg10, StOdg,".
			"	Rez1, Rez2, Rez3, Rez4, Rez5, Rez6, Rez7, Rez8, Rez9, Rez10, Multiple,".
			"	StGlasov ".
			") VALUES (".
			"	".(($_POST['Jezik']!="")? "'".$db->escape($_POST['Jezik'])."'": "NULL").",".
			"	'".date("Y-m-d",strtotime($_POST['D']))."',".
			"	'".$db->escape(left($_POST['V'],255))."',".
			"	".(($_POST['K']!="")? "'".left($_POST['K'],255)."'": "NULL").",".
			"	".(($_POST['O1']!="")? "'".$db->escape($_POST['O1'])."'": "NULL").",".
			"	".(($_POST['O2']!="")? "'".$db->escape($_POST['O2'])."'": "NULL").",".
			"	".(($_POST['O3']!="")? "'".$db->escape($_POST['O3'])."'": "NULL").",".
			"	".(($_POST['O4']!="")? "'".$db->escape($_POST['O4'])."'": "NULL").",".
			"	".(($_POST['O5']!="")? "'".$db->escape($_POST['O5'])."'": "NULL").",".
			"	".(($_POST['O6']!="")? "'".$db->escape($_POST['O6'])."'": "NULL").",".
			"	".(($_POST['O7']!="")? "'".$db->escape($_POST['O7'])."'": "NULL").",".
			"	".(($_POST['O8']!="")? "'".$db->escape($_POST['O8'])."'": "NULL").",".
			"	".(($_POST['O9']!="")? "'".$db->escape($_POST['O9'])."'": "NULL").",".
			"	".(($_POST['O10']!="")? "'".$db->escape($_POST['O10'])."'": "NULL").",".
			"	$StOdg,".
			"	0,0,0,0,0,0,0,0,0,0,".(isset($_POST['Multiple'])? "1": "0").",0 )"
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
				'Poll',
				'Add new poll',
				'". $db->escape(left($_POST['V'],255)) ."'
			)"
			);
		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace("/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING']) ."&ID=". $_GET['ID'];
	}
	$db->query("COMMIT");
}
?>