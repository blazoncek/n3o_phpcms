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

if ( isset($_POST['Ctrl']) && $_POST['Ctrl'] !== "" ) {

	$db->query("START TRANSACTION");
	if ( $_GET['ID'] != "0" ) {
		$db->query(
			"UPDATE Sifranti ".
			"SET SifrText = '".$db->escape($_POST['Text'])."',".
			"	SifNVal1 = ".($_POST['NVal1']!="" ? val($_POST['NVal1']) : "NULL").",".
			"	SifNVal2 = ".($_POST['NVal2']!="" ? val($_POST['NVal2']) : "NULL").",".
			"	SifNVal3 = ".($_POST['NVal3']!="" ? val($_POST['NVal3']) : "NULL").",".
			"	SifDVal1 = ".($_POST['DVal1']!="" ? "'".date("Y-m-d",strtotime($_POST['DVal1']))."'" : "NULL").",".
			"	SifDVal2 = ".($_POST['DVal2']!="" ? "'".date("Y-m-d",strtotime($_POST['DVal2']))."'" : "NULL").",".
			"	SifDVal3 = ".($_POST['DVal3']!="" ? "'".date("Y-m-d",strtotime($_POST['DVal3']))."'" : "NULL").",".
			"	SifLVal1 = ".(isset($_POST['LVal1']) ? "1" : "0").",".
			"	SifLVal2 = ".(isset($_POST['LVal2']) ? "1" : "0")." ".
			"WHERE SifrantID = " . (int)$_GET['ID']
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
				'Parameters',
				'Update parameter',
				'". $db->escape($_POST['Ctrl']) .",". $db->escape($_POST['Text']) .",".
				(($_POST['NVal1']!="") ? $db->escape($_POST['NVal1']) : "") .",".
				(($_POST['NVal2']!="") ? $db->escape($_POST['NVal2']) : "") .",".
				(($_POST['NVal3']!="") ? $db->escape($_POST['NVal3']) : "") .",".
				(($_POST['DVal1']!="") ? date("Y-m-d",strtotime($_POST['DVal1'])) : "") .",".
				(($_POST['DVal2']!="") ? date("Y-m-d",strtotime($_POST['DVal2'])) : "") .",".
				(($_POST['DVal3']!="") ? date("Y-m-d",strtotime($_POST['DVal3'])) : "") .",".
				(isset($_POST['LVal1']) ? "1" : "0") .",".
				(isset($_POST['LVal2']) ? "1" : "0") ."'
			)"
			);
	} else {
		if ($_POST['NVal1Desc']==" field description") $_POST['NVal1Desc']="";
		if ($_POST['NVal2Desc']==" field description") $_POST['NVal2Desc']="";
		if ($_POST['NVal3Desc']==" field description") $_POST['NVal3Desc']="";
		if ($_POST['DVal1Desc']==" field description") $_POST['DVal1Desc']="";
		if ($_POST['DVal2Desc']==" field description") $_POST['DVal2Desc']="";
		if ($_POST['DVal3Desc']==" field description") $_POST['DVal3Desc']="";
		if ($_POST['LVal1Desc']==" field description") $_POST['LVal1Desc']="";
		if ($_POST['LVal2Desc']==" field description") $_POST['LVal2Desc']="";
		$ACL  = $db->get_var( "SELECT min(ACLID) FROM Sifranti WHERE SifrCtrl='".$_POST['Ctrl']."'" );
		$Zapo = $db->get_var( "SELECT max(SifrZapo)+1 FROM Sifranti WHERE SifrCtrl='".$_POST['Ctrl']."'" );
		$db->query(
			"INSERT INTO Sifranti (".
			"	SifrCtrl,".
			"	SifrZapo,".
			"	SifrText,".
			"	SifNVal1,".
			"	SifNVal2,".
			"	SifNVal3,".
			"	SifDVal1,".
			"	SifDVal2,".
			"	SifDVal3,".
			"	SifLVal1,".
			"	SifLVal2,".
			"	SifNVal1Desc,".
			"	SifNVal2Desc,".
			"	SifNVal3Desc,".
			"	SifDVal1Desc,".
			"	SifDVal2Desc,".
			"	SifDVal3Desc,".
			"	SifLVal1Desc,".
			"	SifLVal2Desc,".
			"	ACLID".
			") VALUES (".
			"	'".$db->escape($_POST['Ctrl'])."',".
			"	".(($Zapo)? $Zapo: "1").",".
			"	'".$db->escape($_POST['Text'])."',".
			"	".(($_POST['NVal1']!="")? val($_POST['NVal1']): "NULL").",".
			"	".(($_POST['NVal2']!="")? val($_POST['NVal2']): "NULL").",".
			"	".(($_POST['NVal3']!="")? val($_POST['NVal3']): "NULL").",".
			"	".(($_POST['DVal1']!="")? "'".date("Y-m-d",strtotime($_POST['DVal1']))."'": "NULL").",".
			"	".(($_POST['DVal2']!="")? "'".date("Y-m-d",strtotime($_POST['DVal2']))."'": "NULL").",".
			"	".(($_POST['DVal3']!="")? "'".date("Y-m-d",strtotime($_POST['DVal3']))."'": "NULL").",".
			"	".(isset($_POST['LVal1'])? "1": "0").",".
			"	".(isset($_POST['LVal2'])? "1": "0").",".
			"	".(($_POST['NVal1Desc']!="")? "'".$db->escape($_POST['NVal1Desc'])."'": "NULL").",".
			"	".(($_POST['NVal2Desc']!="")? "'".$db->escape($_POST['NVal2Desc'])."'": "NULL").",".
			"	".(($_POST['NVal3Desc']!="")? "'".$db->escape($_POST['NVal3Desc'])."'": "NULL").",".
			"	".(($_POST['DVal1Desc']!="")? "'".$db->escape($_POST['DVal1Desc'])."'": "NULL").",".
			"	".(($_POST['DVal2Desc']!="")? "'".$db->escape($_POST['DVal2Desc'])."'": "NULL").",".
			"	".(($_POST['DVal3Desc']!="")? "'".$db->escape($_POST['DVal3Desc'])."'": "NULL").",".
			"	".(($_POST['LVal1Desc']!="")? "'".$db->escape($_POST['LVal1Desc'])."'": "NULL").",".
			"	".(($_POST['LVal2Desc']!="")? "'".$db->escape($_POST['LVal2Desc'])."'": "NULL").",".
			"	".(($ACL)? $ACL: "NULL")."".
			")"
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
				'Parameters',
				'Add parameter',
				'". $db->escape($_POST['Ctrl']) .",". $db->escape($_POST['Text']) .",".
				($_POST['NVal1']!="" ? $db->escape($_POST['NVal1']) : "") .",".
				($_POST['NVal2']!="" ? $db->escape($_POST['NVal2']) : "") .",".
				($_POST['NVal3']!="" ? $db->escape($_POST['NVal3']) : "") .",".
				($_POST['DVal1']!="" ? date("Y-m-d",strtotime($_POST['DVal1'])) : "") .",".
				($_POST['DVal2']!="" ? date("Y-m-d",strtotime($_POST['DVal2'])) : "") .",".
				($_POST['DVal3']!="" ? date("Y-m-d",strtotime($_POST['DVal3'])) : "") .",".
				(isset($_POST['LVal1']) ? "1" : "0") .",".
				(isset($_POST['LVal2']) ? "1" : "0") ."'
			)"
			);
		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace( "/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING'] ) . "&ID=" . $_GET['ID'];
	}
	$db->query("COMMIT");
}

if ( isset( $_GET['BrisiTxt'] ) && $_GET['BrisiTxt'] != "" ) {
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
			". (int)$_GET['BrisiTxt'] .",
			'Parameters',
			'Delete parameter text',
			'". $db->get_var("SELECT SifNaziv FROM SifrantiTxt WHERE ID = ". (int)$_GET['BrisiTxt']) ."'
		)"
		);
	$db->query("DELETE FROM SifrantiTxt WHERE ID = ". (int)$_GET['BrisiTxt']);
	$db->query("COMMIT");
}

// insert/update text value
if ( isset($_POST['TxtID']) && $_POST['TxtID'] != "" ) {

	$db->query("START TRANSACTION");
	$ID = $db->get_var(
		"SELECT ID ".
		"FROM SifrantiTxt ".
		"WHERE SifrantID = ". (int)$_POST['TxtID'] ." AND ".
		"	Jezik ".(($_POST['Jezik']!="")? "='".$_POST['Jezik']."'": "IS NULL")
	);
	if ( !$ID ) {
		if ($_POST['CVal1Desc']==" field description") $_POST['CVal1Desc']="";
		if ($_POST['CVal2Desc']==" field description") $_POST['CVal2Desc']="";
		if ($_POST['CVal3Desc']==" field description") $_POST['CVal3Desc']="";
		if ($_POST['NazivDesc']==" field description") $_POST['NazivDesc']="";
		$db->query(
			"INSERT INTO SifrantiTxt (".
			"	SifrantID,".
			"	Jezik,".
			"	SifNaziv,".
			"	SifCVal1,".
			"	SifCVal2,".
			"	SifCVal3,".
			"	SifNazivDesc,".
			"	SifCVal1Desc,".
			"	SifCVal2Desc,".
			"	SifCVal3Desc".
			") VALUES (".
			"	".$_POST['TxtID'].",".
			"	".(($_POST['Jezik']!="")? "'".$db->escape($_POST['Jezik'])."'": "NULL").",".
			"	".(($_POST['Naziv']!="")? "'".$db->escape($_POST['Naziv'])."'": "'(empty)'").",".
			"	".(($_POST['CVal1']!="")? "'".$db->escape($_POST['CVal1'])."'": "NULL").",".
			"	".(($_POST['CVal2']!="")? "'".$db->escape($_POST['CVal2'])."'": "NULL").",".
			"	".(($_POST['CVal3']!="")? "'".$db->escape($_POST['CVal3'])."'": "NULL").",".
			"	".(($_POST['NazivDesc']!="")? "'".$db->escape($_POST['NazivDesc'])."'": "NULL").",".
			"	".(($_POST['CVal1Desc']!="")? "'".$db->escape($_POST['CVal1Desc'])."'": "NULL").",".
			"	".(($_POST['CVal2Desc']!="")? "'".$db->escape($_POST['CVal2Desc'])."'": "NULL").",".
			"	".(($_POST['CVal3Desc']!="")? "'".$db->escape($_POST['CVal3Desc'])."'": "NULL").
			")"
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
				". (int)$_POST['TxtID'] .",
				'Parameters',
				'Add parameter text',
				'". ($_POST['Naziv']!="" ? $db->escape($_POST['Naziv']) : "(empty)") ."'
			)"
			);
	} else
		$db->query(
			"UPDATE SifrantiTxt ".
			"SET SifNaziv = ".(($_POST['Naziv']!="")? "'".$db->escape($_POST['Naziv'])."'": "'(empty)'").", ".
			"	SifCVal1 = ".(($_POST['CVal1']!="")? "'".$db->escape($_POST['CVal1'])."'": "NULL").", ".
			"	SifCVal2 = ".(($_POST['CVal2']!="")? "'".$db->escape($_POST['CVal2'])."'": "NULL").", ".
			"	SifCVal3 = ".(($_POST['CVal3']!="")? "'".$db->escape($_POST['CVal3'])."'": "NULL")." ".
			"WHERE ID= " . $ID
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
				". $ID .",
				'Parameters',
				'Update parameter text',
				'". ($_POST['Naziv']!="" ? $db->escape($_POST['Naziv']) : "(empty)") ."'
			)"
			);
	$db->query("COMMIT");
}
?>
