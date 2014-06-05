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

if ( isset($_POST['Naziv']) && $_POST['Naziv'] != "" ) {
	$db->query("START TRANSACTION");
	if ( $_GET['ID']=="0" ) {
		$db->query(
			"INSERT INTO emlMessages (Naziv)
			VALUES ('".$db->escape($_POST['Naziv'])."')"
		);
		// get inserted ID
		$_GET['ID'] = $db->insert_id;
		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace( "/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING'] ) . "&ID=" . $_GET['ID'];
	} else {
		$db->query(
			"UPDATE emlMessages
			SET Naziv = '".$db->escape($_POST['Naziv'])."'
			WHERE emlMessageID = ". $_GET['ID']
		);
	}
	$db->query("COMMIT");
}

if ( isset($_POST['MemberList']) && $_POST['MemberList'] !== "" && isset($_POST['Action']) ) {
	$db->query( "START TRANSACTION" );
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
		foreach ( explode( ",", $_POST['MemberList'] ) as $UserID ) {
			$db->query(
				"INSERT INTO emlMessagesGrp (emlMessageID, emlGroupID)
				VALUES (".(int)$_POST['MessageID'].",$UserID)"
			);
		}
	}
	$db->query( "COMMIT" );
}

//delete title/description
if ( isset($_GET['BrisiOpis']) ) {
	$db->query(
		"DELETE FROM emlMessagesTxt
		WHERE emlMessageTxtID = ".(int)$_GET['BrisiOpis'] );
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&BrisiOpis=[0-9]+/", "", $_SERVER['QUERY_STRING'] );
}

// adding mesage content
if ( isset($_POST['Subject']) && $_POST['Subject']!="" ) {
	// cleanup
	$_POST['Subject'] = $db->escape(str_replace( "\"", "&quot;", left($_POST['Subject'],128) ));
	$_POST['Opis']    = str_replace("\\\"","\"",$db->escape(CleanupTinyMCE($_POST['Opis'])));

	// note: adding image no longer supported
	if ( isset($_POST['OpisID']) ) {
		$db->query(
			"UPDATE emlMessagesTxt
			SET Naziv = '".$_POST['Subject']."',
				Opis = '".$_POST['Opis']."'
			WHERE emlMessageTxtID = ".(int)$_POST['OpisID']
		);
	} else {
		$db->query(
			"INSERT INTO emlMessagesTxt (
				Jezik,
				emlMessageID,
				Naziv,
				Opis
			) VALUES (
				".(($_POST['Jezik']!="")? "'".$_POST['Jezik']."'": "NULL").",
				".(int)$_GET['ID'].",
				'".$_POST['Subject']."',
				'".$_POST['Opis']."'
			)"
		);
	}
}
?>
<?php
/*
<CFIF IsDefined("URL.BrisiDoc") AND URL.BrisiDoc NEQ "">
	<CFQUERY DATASOURCE="#DSN#">
		DELETE FROM emlMessagesDoc
		WHERE emlMessageDocID = #val(URL.BrisiDoc)#
	</CFQUERY>
	<CFTRY>
	<CFFILE ACTION="DELETE" FILE="#ExpandPath('..\datoteke\')##Form.Datoteka#">
	<CFCATCH TYPE="Any"></CFCATCH>
	</CFTRY>
	<CFHEADER NAME="Refresh" VALUE="0; URL=#cgi.script_name#?izbor=#URL.Izbor#&ID=#URL.ID#">
	<CFABORT>
</CFIF>

<CFIF IsDefined("Form.Datoteka") AND Form.Datoteka IS NOT "">
	<CFTRY>
	<CFFILE ACTION="Upload" FILEFIELD="Datoteka" DESTINATION="#ExpandPath('..\datoteke\')#" NAMECONFLICT="MAKEUNIQUE">
	<CFQUERY DATASOURCE="#DSN#">
		INSERT INTO emlMessagesDoc (emlMessageID, Datoteka)
		VALUES (#URL.ID#, '#File.ServerFile#')
	</CFQUERY>
	<CFCATCH TYPE="Any"></CFCATCH>
	</CFTRY>
	<CFHEADER NAME="Refresh" VALUE="0; URL=#cgi.script_name#?izbor=#URL.Izbor#&ID=#URL.ID#">
	<CFABORT>
</CFIF>
*/
?>