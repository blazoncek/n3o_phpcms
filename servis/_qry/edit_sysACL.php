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

// creating new ACL from content template
if ( isset($_GET['ACL']) && $_GET['ACL'] != "" ) {

	// ACL does not exist: create a new entry
	if ( $_GET['ID'] == "0" ) {

		$db->query("START TRANSACTION");
	
		$ACLName = "XXX-";
		switch ( $_GET['ACL'] ) {
			case "sysMenus":      $ACLName = "SRV-".$_GET['ActionID'];     break;
			case "sysParameters": $ACLName = "SIF-".$_GET['SifrantID'];    break;
			case "sysTemplates":  $ACLName = "PRE-".$_GET['PredlogaID'];   break;
			case "Categories":    $ACLName = "KTG-".$_GET['KategorijaID']; break;
			case "Media":         $ACLName = "MED-".$_GET['MediaID'];      break;
			case "Text":          $ACLName = "BES-".$_GET['BesediloID'];   break;
			case "Polls":         $ACLName = "ANK-".$_GET['AnketaID'];     break;
			case "emlMessages":   $ACLName = "EMS-".$_GET['emlMessageID']; break;
		}
		
		//try to create new ACL
		@$db->query( "INSERT INTO SMACL (Name) VALUES ('$ACLName')" );
		if ( $db->last_error ) {
			echo "ACL with this name exists! Please contact the admin.";
			$db->query( "ROLLBACK" );
			die();
		}
		//retreive ACL's ID
		$_GET['ID'] = $db->insert_id;
		// update URI
		$_SERVER['QUERY_STRING'] = preg_replace("/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING']) ."&ID=". $_GET['ID'];


		//set everyones privileges
		$db->query("INSERT INTO SMACLr (ACLID, GroupID, MemberACL) VALUES (".(int)$_GET['ID'].", 1, 'LRWDX')");
		//set user's privileges
		$db->query("INSERT INTO SMACLr (ACLID, UserID, MemberACL) VALUES (".$_GET['ID'].", ".$_SESSION['UserID'].", 'LRWDX')");
	
		// update object's ACL
		switch ( $_GET['ACL'] ) {
			case "sysMenus":      $db->query("UPDATE SMActions   SET ACLID = ".(int)$_GET['ID']." WHERE ActionID='".$db->escape($_GET['ActionID'])."'"); break;
			case "sysParameters": $db->query("UPDATE Sifranti    SET ACLID = ".(int)$_GET['ID']." WHERE SifrCtrl=".(int)$_GET['SifrantID']);             break;
			case "sysTemplates":  $db->query("UPDATE Predloge    SET ACLID = ".(int)$_GET['ID']." WHERE PredlogaID=".(int)$_GET['PredlogaID']);          break;
			case "Categories":    $db->query("UPDATE Kategorije  SET ACLID = ".(int)$_GET['ID']." WHERE KategorijaID='".$db->escape($_GET['KategorijaID'])."'"); break;
			case "Media":         $db->query("UPDATE Media       SET ACLID = ".(int)$_GET['ID']." WHERE MediaID=".(int)$_GET['MediaID']);                break;
			case "Text":          $db->query("UPDATE Besedila    SET ACLID = ".(int)$_GET['ID']." WHERE BesdiloID=".(int)$_GET['BesediloID']);           break;
			case "Polls":         $db->query("UPDATE Ankete      SET ACLID = ".(int)$_GET['ID']." WHERE ID=".(int)$_GET['AnketaID']);                    break;
			case "emlMessages":   $db->query("UPDATE emlMessages SET ACLID = ".(int)$_GET['ID']." WHERE emlMessageID=".(int)$_GET['emlMessageID']);      break;
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
				'SMACL',
				'Create ACL',
				'". $ACLName ."'
			)"
			);

		$db->query("COMMIT");
	}
}

// change ACL entry's name
if ( isset($_POST['Name']) && $_POST['Name'] != "" ) {
	$db->query("START TRANSACTION");
	if ( (int)$_GET['ID'] != 0 ) {
		$db->query("UPDATE SMACL SET Name = '". $db->escape($_POST['Name']) ."' WHERE ACLID = " . (int)$_GET['ID']);
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
				'SMACL',
				'Rename ACL',
				'". $db->escape($_POST['Name']) ."'
			)"
			);
	} else {
		$db->query("INSERT INTO SMACL (Name) VALUES ('". $db->escape($_POST['Name']) ."')");
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
				'SMACL',
				'Create ACL',
				'". $db->escape($_POST['Name']) ."'
			)"
			);
	}
	$db->query("COMMIT");
}

// add users to permissions list
if ( isset($_POST['UserList']) && $_POST['UserList'] !== "" && isset($_POST['Action']) ) {
	$db->query("START TRANSACTION");
	if ( $_POST['Action'] == "Add" )
		foreach ( explode(",", $_POST['UserList']) as $UserID ) {
			$db->query( "INSERT INTO SMACLr (ACLID, UserID, MemberACL) VALUES (". (int)$_GET['ID'] .", $UserID, '     ')");
		}
	if ( $_POST['Action'] == "Remove" )
		$db->query("DELETE FROM SMACLr WHERE ACLID = ". (int)$_GET['ID'] ." AND UserID IN (". $db->escape($_POST['UserList']) .")");
	if ( $_POST['Action'] == "Set" ) {
		$db->query("DELETE FROM SMACLr WHERE ACLID = ". (int)$_GET['ID'] ." AND UserID NOT IN (". $db->escape($_POST['UserList']) .")");
		foreach ( explode(",", $_POST['UserList']) as $UserID ) {
			@$db->query("INSERT INTO SMACLr (ACLID, UserID, MemberACL) VALUES (". (int)$_GET['ID'] .", $UserID, '". ($UserID==1? "LRWDX":"     ") ."')");
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
			'SMACL',
			'Change ACL membership',
			'". $db->escape($_POST['Action']) ." user,". $db->escape($_POST['UserList']) ."'
		)"
		);
	$db->query("COMMIT");
}

// add groups to permissions list
if ( isset($_POST['GroupList']) && $_POST['GroupList'] !== "" && isset($_POST['Action']) ) {
	$db->query("START TRANSACTION");
	if ( $_POST['Action'] == "Add" )
		foreach ( explode( ",", $_POST['GroupList'] ) as $GroupID ) {
			$db->query( "INSERT INTO SMACLr (ACLID, GroupID, MemberACL) VALUES (". (int)$_GET['ID'] .", $GroupID, '     ')" );
		}
	if ( $_POST['Action'] == "Remove" )
		$db->query( "DELETE FROM SMACLr WHERE ACLID = ". (int)$_GET['ID'] ." AND GroupID IN (". $db->escape($_POST['GroupList']) .")" );
	if ( $_POST['Action'] == "Set" ) {
		$db->query( "DELETE FROM SMACLr WHERE ACLID = ". (int)$_GET['ID'] ." AND GroupID NOT IN (". $db->escape($_POST['GroupList']) .")" );
		foreach ( explode(",", $_POST['GroupList']) as $GroupID ) {
			@$db->query( "INSERT INTO SMACLr (ACLID, GroupID, MemberACL) VALUES (". (int)$_GET['ID'] .", $GroupID, '". ($GroupID==2? "LRWDX":"     ") ."')" );
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
			'SMACL',
			'Change ACL membership',
			'". $db->escape($_POST['Action']) ." group,". $db->escape($_POST['GroupList']) ."'
		)"
		);
	$db->query("COMMIT");
}

// update user's or group's permissions
if ( isset($_POST['GroupID']) || isset($_POST['UserID']) ) {
	$db->query("START TRANSACTION");
	$ACL = $db->get_var(
		"SELECT MemberACL ".
		"FROM SMACLr ".
		"WHERE ACLID = ".(int)$_GET['ID']." AND (".
		"	".((isset($_POST['UserID']) && $_POST['UserID']!="0")? "UserID = ".(int)$_POST['UserID']: "").
		"	".(((isset($_POST['UserID']) && $_POST['UserID']!="0") && (isset($_POST['GroupID']) && $_POST['GroupID']!="0"))? " OR ": "").
		"	".((isset($_POST['GroupID']) && $_POST['GroupID']!="0")? "GroupID = ".(int)$_POST['GroupID']: "").
		")"
	);
	$ACL = $ACL? substr( $ACL."     ", 0, 5 ): "     ";
	if ( isset($_POST['List']) )    $ACL = ($_POST['List']=="true")? "L".substr($ACL,1,4): "     ";
	if ( isset($_POST['Read']) )    $ACL = ($_POST['Read']=="true")? "LR".substr($ACL,2,3): substr($ACL,0,1)."    ";
	if ( isset($_POST['Write']) )   $ACL = ($_POST['Write']=="true")? "LRW".substr($ACL,3,2): substr($ACL,0,2)."  ".substr($ACL,4,1);
	if ( isset($_POST['Delete']) )  $ACL = ($_POST['Delete']=="true")? "LRWD".substr($ACL,4,1): substr($ACL,0,3)." ".substr($ACL,4,1);
	if ( isset($_POST['Execute']) ) $ACL = ($_POST['Execute']=="true")? "LR".substr($ACL,2,2)."X": substr($ACL,0,4)." ";
	// disable removing administrator privileges for base ACL (ACLID==1)
	if ( isset($_POST['UserID'])  && $_POST['UserID'] == "1"  && $_GET['ID'] == "1") $ACL = "LRWDX";
	if ( isset($_POST['GroupID']) && $_POST['GroupID'] == "2" && $_GET['ID'] == "1") $ACL = "LRWDX";

	$db->query(
		"UPDATE SMACLr ".
		"SET MemberACL = '$ACL' ".
		"WHERE ACLID = ".(int)$_GET['ID']." AND (".
		"	".((isset($_POST['UserID']) && $_POST['UserID']!="0")? "UserID = ".(int)$_POST['UserID']: "").
		"	".(((isset($_POST['UserID']) && $_POST['UserID']!="0") && (isset($_POST['GroupID']) && $_POST['GroupID']!="0"))? " OR ": "").
		"	".((isset($_POST['GroupID']) && $_POST['GroupID']!="0")? "GroupID = ".(int)$_POST['GroupID']: "").
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
			". (int)$_GET['ID'] .",
			'SMACLr',
			'Change ACL permissions',
			'". ((isset($_POST['UserID']) && (int)$_POST['UserID'] != 0) ? "UserID=". (int)$_POST['UserID'] : "") .
				((isset($_POST['GroupID']) && (int)$_POST['GroupID'] != 0) ? "GroupID=". (int)$_POST['GroupID'] : "") .
			", ". $ACL ."'
		)"
		);

	$db->query("COMMIT");
}
?>
