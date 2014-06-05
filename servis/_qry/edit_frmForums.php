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

global $EZSQL_ERROR;

if ( !isset($_GET['ID']) ) $_GET['ID'] = "0";

if ( count($_POST) > 0 ) {

	$db->query( "START TRANSACTION" );
	if ( $_GET['ID'] != "0" ) {
		// update a forum (mobile & classic)
		if ( count($_POST) > 1 )
			$db->query(
				"UPDATE frmForums
				SET NotifyModerator = 0,
					ApprovalRequired = 0,
					AllowFileUploads = 0,
					ViewOnly = 0,
					Hidden = 0,
					Private = 0,
					PollEnabled = 0
				WHERE ID = ". (int)$_GET['ID']
			);
		
		foreach ( $_POST as $name => $value ) {
			switch ( $name ) {
				case "CategoryID":
					$OldCategoryID = $db->get_var( "SELECT CategoryID FROM frmForums WHERE ID=" .(int)$_GET['ID'] );
					if ( (int)$value != (int)$OldCategoryID ) {
						$Polozaj = $db->get_var( "SELECT max(ForumOrder) FROM frmForums WHERE CategoryID = ". (int)$OldCategoryID );
						$_POST['ForumOrder'] = $Polozaj? $Polozaj+1: 1;
					}
				case "Moderator":
				case "MaxUploadSize":
				case "PurgeDays":
				case "ForumOrder":
					$set = (($value!="" && (int)$value>0) ? (int)$value : "NULL");
					break;
				case "NotifyModerator":
				case "ApprovalRequired":
				case "AllowFileUploads":
				case "ViewOnly":
				case "Hidden":
				case "Private":
				case "PollEnabled":
					$set = (strtolower($value)=="yes" ? 1 : 0);
					break;
//				case "Password":
//					$set = ($value!="" ? "'".$db->escape(MD5(PWSALT.$value))."'" : "NULL");
//					break;
				case "ForumName":
					$value = preg_replace("/<[^>]*>/i", "", $value);
					$value = str_replace(chr(38), "&amp;", $value);
					$value = str_replace(chr(34), "&quot;", $value);
					$value = str_replace(chr(60), "&lt;", $value);
					$value = str_replace(chr(62), "&gt;", $value);
					$value = preg_replace("/&[;>]*;/i", "", $value);
					if ( $value == "" )
						$value = "(neimenovan)";
				default :
					$set = ($value!="" ? "'".$db->escape($value)."'" : "NULL");
					break;
			}
			$db->query( "UPDATE frmForums SET $name = $set WHERE ID = ". (int)$_GET['ID'] );
		}
/*
		$db->query(
			"UPDATE frmForums
			SET CategoryID = ".(int)$_POST['CategoryID'].",
				ForumName = '".$_POST['ForumName']."',
				Description = ".($_POST['Description']!=""? "'".$_POST['Description']."'": "NULL").",
				Moderator = ".(int)$_POST['Moderator'].",
				NotifyModerator = ".(isset($_POST['NotifyModerator'])? "1": "0").",
				Password = ".($_POST['Password']!=""? "'".$_POST['Password']."'": "NULL").",
				ApprovalRequired = ".(isset($_POST['ApprovalRequired'])? "1": "0").",
				AllowFileUploads = ".(isset($_POST['AllowFileUploads'])? "1": "0").",
				MaxUploadSize = ".(isset($_POST['AllowFileUploads'])? (int)$_POST['MaxUploadSize']: "NULL").",
				UploadType = ".(isset($_POST['AllowFileUploads'])? "'".$_POST['UploadType']."'": "NULL").",
				ViewOnly = ".(isset($_POST['ViewOnly'])? "1": "0").",
				Hidden = ".(isset($_POST['Hidden'])? "1": "0").",
				Private = ".(isset($_POST['Private'])? "1": "0").",
				PollEnabled = ".(isset($_POST['PollEnabled'])? "1": "0").",".
				(isset($ForumOrder)? "ForumOrder = $ForumOrder": "")."
				PurgeDays = ".((int)$_POST['PurgeDays'] > 0? (int)$_POST['PurgeDays']: "NULL")."
			WHERE ID = ".(int)$_GET['ID']
		);
*/
		if ( isset($_POST['Moderator']) && (int)$_POST['Moderator'] > 0 ) {
			// try to insert moderator to list
			@$db->query(
				"INSERT INTO frmModerators (
						ForumID,
						MemberID,
						Permissions
				) VALUES (
					".(int)$_GET['ForumID'].",
					".(int)$_POST['Moderator'].",
					31
				)"
			);
			// update moderator
			if ( $EZSQL_ERROR )
				@$db->query(
					"UPDATE frmModerators
					SET Permissions = 31
					WHERE
						ForumID = ".(int)$_GET['ID']." AND
						MemberID = ".(int)$_POST['Moderator']
				);
		}
	} else {
		// insert forum (classic only)
		$Polozaj = $db->get_var( "SELECT max(ForumOrder) FROM frmForums WHERE CategoryID = ". (int)$_POST['CategoryID'] );
		$db->query(
			"INSERT INTO frmForums (
				CategoryID,
				ForumName,
				Description,
				Moderator,
				NotifyModerator,
				Password,
				ApprovalRequired,
				AllowFileUploads,
				MaxUploadSize,
				UploadType,
				ViewOnly,
				Hidden,
				Private,
				PollEnabled,
				ForumOrder,
				PurgeDays
			) VALUES (".
				(int)$_POST['CategoryID'].",".
				"'".$_POST['ForumName']."',".
				($_POST['Description']!=""? "'".$_POST['Description']."'": "NULL").",".
				(int)$_POST['Moderator'].",".
				(isset($_POST['NotifyModerator'])? "1": "0").",".
				($_POST['Password']!=""? "'".$_POST['Password']."'": "NULL").",".
				(isset($_POST['ApprovalRequired'])? "1": "0").",".
				(isset($_POST['AllowFileUploads'])? "1": "0").",".
				(isset($_POST['AllowFileUploads'])? (int)$_POST['MaxUploadSize']: "NULL").",".
				(isset($_POST['AllowFileUploads'])? "'".$_POST['UploadType']."'": "NULL").",".
				(isset($_POST['ViewOnly'])? "1": "0").",".
				(isset($_POST['Hidden'])? "1": "0").",".
				(isset($_POST['Private'])? "1": "0").",".
				(isset($_POST['PollEnabled'])? "1": "0").",".
				($Polozaj? $Polozaj+1: "1").",".
				((int)$_POST['PurgeDays'] > 0? (int)$_POST['PurgeDays']: "NULL").
			")"
		);
		$ID = $db->insert_id;
		
		if ( (int)$_POST['Moderator'] > 0 ) {
			$db->query(
				"INSERT INTO frmModerators (
					ForumID,
					MemberID,
					Permissions
				) VALUES (
					". $ID .",
					".(int)$_POST['Moderator'].",
					31
				)"
			);
		}
		// get inserted ID
		$_GET['ID'] = $ID;
		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace( "/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING'] ) . "&ID=" . $_GET['ID'];
	}
	$db->query( "COMMIT" );
}
?>
