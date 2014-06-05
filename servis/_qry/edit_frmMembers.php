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

if ( !isset( $_GET['ID'] ) ) $_GET['ID'] = "0";

if ( isset($_POST['AccessLevel']) && $_GET['ID'] != "0" ) {
	if ( isset($_POST['NewPwd']) ) {
		$xGeslo = chr(rand(65,92)) . rand(0,9) . chr(rand(97,123)) . chr(rand(33,47)) . chr(rand(97,123)) . rand(0,9) . chr(rand(97,123));

		$SMTPServer->AddAddress( $_POST['Email'], $_POST['Ime'] );
		$SMTPServer->Subject = AppName . " : Sprememba gesla";
		$SMTPServer->AltBody = "Pozdravljeni!\n\nAdministrator vam je spremenil geslo,\nki se po novem glasi:\n\n". $xGeslo .
			"\n\nGeslo si lahko spremenite v Nastavitvah znotraj " . AppName . " Diskusij.\n" .
			"V kolikor bi imeli težave, to sporočite na naslov:\n" . $PostMaster;
		$SMTPServer->MsgHTML(
			"<p>Pozdravljeni!</p><p>Administrator vam je spremenil geslo,\nki se po novem glasi:<br><br>". $xGeslo .
			"<br><br>Geslo si lahko spremenite v Nastavitvah znotraj " . AppName . " Diskusij.</p>" .
			"<p>V kolikor bi imeli težave, to sporočite na naslov:<br>" . $PostMaster . "</p>"
		);
		if ( !$SMTPServer->Send() )
			echo "<!-- mail send error (".$Modearor->Email.") -->\n";
		$SMTPServer->ClearAddresses();
	}
	$db->query(
		"UPDATE frmMembers
		SET Name = '".$db->escape($_POST['Ime'])."',
			Nickname = '".$db->escape($_POST['Vzdevek'])."',
			Email = '".$db->escape($_POST['Email'])."',
			Address = ".(($_POST['Address']=="")? "NULL": "'".$db->escape($_POST['Address'])."'").",
			Phone = ".(($_POST['Phone']=="")? "NULL": "'".$db->escape($_POST['Phone'])."'").",
			Signature = ".(($_POST['Signature']=="")? "NULL": "'".$db->escape($_POST['Signature'])."'").",
			".(isset($_POST['NewPwd'])? "Password = '".$db->escape(crypt(PWSALT . xGeslo))."',": "")."
			Enabled = ".(isset($_POST['Enabled'])? 1: 0).",
			ShowEmail = ".(isset($_POST['ShowEmail'])? 1: 0).",
			Patron = ".(isset($_POST['Patron'])? 1: 0).",
			AccessLevel = ".(int)$_POST['AccessLevel']."
		WHERE ID = ".(int)$_GET['ID']
	);
}
?>