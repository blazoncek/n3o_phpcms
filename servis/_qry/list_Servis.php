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

if ( !isset($_GET['ID']) )   $_GET['ID'] = "";
if ( !isset( $_GET['Find'] ) ) $_GET['Find'] = "";

if ( isset( $_GET['Brisi'] ) && $_GET['Brisi'] != "" ) {
	$db->query("START TRANSACTION");
	// delete subtree of ACLs 
	$List = $db->get_col(
		"SELECT ACLID
		FROM SMActions
		WHERE ActionID LIKE '". $_GET['Brisi'] ."%' AND ACLID IS NOT NULL", 0 );
	if ( $List )
		foreach ( $List as $ACLID ) {
			$db->query( "DELETE FROM SMACLr WHERE ACLID = $ACLID" );
			$db->query( "DELETE FROM SMACL  WHERE ACLID = $ACLID" );
		}

	$db->query( "DELETE FROM SMActions WHERE ActionID LIKE '". $_GET['Brisi'] ."%'" );
	$db->query("COMMIT");
	
	// change URL parameter to reflect deletions
	if ( strlen( $_GET['Brisi'] ) > 2 )
		$_GET['ID'] = left( $_GET['Brisi'], strlen( $_GET['Brisi'] )-2 );
}

if ( isset( $_GET['Smer'] ) && $_GET['Smer'] != "" ) {
	$len = strlen($_GET['ID']);
	$Start = $len + 1;

	if ( $len > 2 )
		$Prfx = left( $_GET['ID'], $len - 2 );
	else
		$Prfx = "";
	$Nov = $Prfx . sprintf( "%02d", (int)right($_GET['ID'],2) + (int)$_GET['Smer'] );
	$Zac = $Prfx . "xx";
	if ( right( $Nov, 2 ) != "00" ) {
		$db->query("START TRANSACTION");
		if ( SQLType == "MySQL" ) {
			// NOTE: update ACL names (not mandatory)
			$db->query( "UPDATE SMACL SET Name=CONCAT('SRV-".$Zac."',substring(Name,".($Start+4).",99))        WHERE Name LIKE 'SRV-".$_GET['ID']."%'" );
			$db->query( "UPDATE SMACL SET Name=CONCAT('SRV-".$_GET['ID']."',substring(Name,".($Start+4).",99)) WHERE Name LIKE 'SRV-".$Nov."%'" );
			$db->query( "UPDATE SMACL SET Name=CONCAT('SRV-".$Nov."',substring(Name,".($Start+4).",99))        WHERE Name LIKE 'SRV-".$Zac."%'" );
			// NOTE: end
			$db->query( "UPDATE SMActions SET ActionID=CONCAT('".$Zac."',substring(ActionID,".$Start.",99))        WHERE left(ActionID,".$len.")='".$_GET['ID']."'" );
			$db->query( "UPDATE SMActions SET ActionID=CONCAT('".$_GET['ID']."',substring(ActionID,".$Start.",99)) WHERE left(ActionID,".$len.")='".$Nov."'" );
			$db->query( "UPDATE SMActions SET ActionID=CONCAT('".$Nov."',substring(ActionID,".$Start.",99))        WHERE left(ActionID,".$len.")='".$Zac."'" );
		} elseif ( SQLType == "MsSQL" ) {
			// NOTE: update ACL names (not mandatory)
			$db->query( "UPDATE SMACL SET Name='SRV-".$Zac."' + substring(Name,".($Start+4).",99)        WHERE Name LIKE 'SRV-".$_GET['ID']."%'" );
			$db->query( "UPDATE SMACL SET Name='SRV-".$_GET['ID']."' + substring(Name,".($Start+4).",99) WHERE Name LIKE 'SRV-".$Nov."%'" );
			$db->query( "UPDATE SMACL SET Name='SRV-".$Nov."' + substring(Name,".($Start+4).",99)        WHERE Name LIKE 'SRV-".$Zac."%'" );
			// NOTE: end
			$db->query( "UPDATE SMActions SET ActionID='".$Zac."' + substring(ActionID,".$Start.",99)        WHERE left(ActionID,".$len.")='".$_GET['ID']."'" );
			$db->query( "UPDATE SMActions SET ActionID='".$_GET['ID']."' + substring(ActionID,".$Start.",99) WHERE left(ActionID,".$len.")='".$Nov."'" );
			$db->query( "UPDATE SMActions SET ActionID='".$Nov."' + substring(ActionID,".$Start.",99)        WHERE left(ActionID,".$len.")='".$Zac."'" );
		}
		$db->query("COMMIT");
	}
	// prevent opening subcategory
	if ( $len > 2 )
		$_GET['ID'] = left( $_GET['ID'], $len - 2 );
	else
		$_GET['ID'] = "";
}
?>