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

// define default values for URL ID and Find parameters (in case not defined)
if ( !isset($_GET['ID']) )   $_GET['ID'] = "";
if ( !isset($_GET['Find']) ) $_GET['Find'] = "";

if ( isset($_GET['Brisi']) && $_GET['Brisi'] != "" ) {
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
			NULL,
			'Category',
			'Delete category',
			'". $db->get_var("SELECT Ime FROM Kategorije WHERE KategorijaID='". $db->escape($_GET['Brisi']) ."'") ."'
		)"
		);
	
	// delete image file
	$Slika = $db->get_var("SELECT Slika FROM Kategorije WHERE KategorijaID = '". $db->escape($_GET['Brisi']) ."'");
	if ( $Slika && $Slika != "" ) {
		$e = right($Slika, 4);
		$b = left($Slika, strlen($Slika)-4);
		@unlink($StoreRoot ."/media/rubrike/". $Slika);
		@unlink($StoreRoot ."/media/rubrike/". $b .'@2x'. $e);
	}

	// delete subtree of ACLs 
	$List = $db->get_col("SELECT ACLID FROM Kategorije WHERE KategorijaID LIKE '". $db->escape($_GET['Brisi']) ."%' AND ACLID IS NOT NULL", 0);
	if ( $List ) foreach ( $List as $ACLID ) {
		$db->query("DELETE FROM SMACLr WHERE ACLID=". $ACLID);
		$db->query("DELETE FROM SMACL  WHERE ACLID=". $ACLID);
	}

	$db->query("DELETE FROM KategorijeMedia    WHERE KategorijaID LIKE '". $db->escape($_GET['Brisi']) ."%'");
	$db->query("DELETE FROM KategorijeBesedila WHERE KategorijaID LIKE '". $db->escape($_GET['Brisi']) ."%'");
	$db->query("DELETE FROM KategorijeVsebina  WHERE KategorijaID LIKE '". $db->escape($_GET['Brisi']) ."%'");
	$db->query("DELETE FROM KategorijeNazivi   WHERE KategorijaID LIKE '". $db->escape($_GET['Brisi']) ."%'");
	$db->query("DELETE FROM Kategorije         WHERE KategorijaID LIKE '". $db->escape($_GET['Brisi']) ."%'");

	// change URL parameter to reflect deletions
	if ( strlen( $_GET['Brisi'] ) > 2 )
		$_GET['ID'] = left($_GET['Brisi'], strlen($_GET['Brisi'])-2);

	$db->query("COMMIT");
}

// move items up/down
if ( isset($_GET['Smer']) && $_GET['Smer'] != "" ) {
	$len = strlen($_GET['ID']);
	$Start = $len + 1;

	if ( $len > 2 )
		$Prfx = left($_GET['ID'], $len-2);
	else
		$Prfx = "";
	$Nov = $Prfx . sprintf("%02d", (int)right($_GET['ID'],2) + (int)$_GET['Smer']);
	$Zac = $Prfx . "xx";
	if ( right($Nov, 2) != "00" ) {
		$db->query("START TRANSACTION");
		if ( SQLType == "MySQL" ) {
			$db->query( "ALTER TABLE KategorijeNazivi   DROP FOREIGN KEY KTN_FK_KAT" );
			$db->query( "ALTER TABLE KategorijeVsebina  DROP FOREIGN KEY KTV_FK_KAT" );
			$db->query( "ALTER TABLE KategorijeBesedila DROP FOREIGN KEY KTB_FK_KAT" );
			$db->query( "ALTER TABLE KategorijeMedia    DROP FOREIGN KEY KTM_FK_KAT" );

			// NOTE: update ACL names (not mandatory)
			$db->query( "UPDATE SMACL SET Name=CONCAT('KTG-".$Zac."',substring(Name,".($Start+4).",99))        WHERE Name LIKE 'KTG-".$_GET['ID']."%'" );
			$db->query( "UPDATE SMACL SET Name=CONCAT('KTG-".$_GET['ID']."',substring(Name,".($Start+4).",99)) WHERE Name LIKE 'KTG-".$Nov."%'" );
			$db->query( "UPDATE SMACL SET Name=CONCAT('KTG-".$Nov."',substring(Name,".($Start+4).",99))        WHERE Name LIKE 'KTG-".$Zac."%'" );
			// NOTE: end
			$db->query( "UPDATE KategorijeMedia SET KategorijaID=ConCat('".$Zac."',substring(KategorijaID,".$Start.",99))        WHERE KategorijaID LIKE '".$_GET['ID']."%'" );
			$db->query( "UPDATE KategorijeMedia SET KategorijaID=ConCat('".$_GET['ID']."',substring(KategorijaID,".$Start.",99)) WHERE KategorijaID LIKE '".$Nov."%'" );
			$db->query( "UPDATE KategorijeMedia SET KategorijaID=ConCat('".$Nov."',substring(KategorijaID,".$Start.",99))        WHERE KategorijaID LIKE '".$Zac."%'" );
		
			$db->query( "UPDATE KategorijeBesedila SET KategorijaID=ConCat('".$Zac."',substring(KategorijaID,".$Start.",99))        WHERE KategorijaID LIKE '".$_GET['ID']."%'" );
			$db->query( "UPDATE KategorijeBesedila SET KategorijaID=ConCat('".$_GET['ID']."',substring(KategorijaID,".$Start.",99)) WHERE KategorijaID LIKE '".$Nov."%'" );
			$db->query( "UPDATE KategorijeBesedila SET KategorijaID=ConCat('".$Nov."',substring(KategorijaID,".$Start.",99))        WHERE KategorijaID LIKE '".$Zac."%'" );
		
			$db->query( "UPDATE KategorijeVsebina SET KategorijaID=ConCat('".$Zac."',substring(KategorijaID,".$Start.",99))        WHERE KategorijaID LIKE '".$_GET['ID']."%'" );
			$db->query( "UPDATE KategorijeVsebina SET KategorijaID=ConCat('".$_GET['ID']."',substring(KategorijaID,".$Start.",99)) WHERE KategorijaID LIKE '".$Nov."%'" );
			$db->query( "UPDATE KategorijeVsebina SET KategorijaID=ConCat('".$Nov."',substring(KategorijaID,".$Start.",99))        WHERE KategorijaID LIKE '".$Zac."%'" );
		
			$db->query( "UPDATE KategorijeNazivi SET KategorijaID=ConCat('".$Zac."',substring(KategorijaID,".$Start.",99))        WHERE KategorijaID LIKE '".$_GET['ID']."%'" );
			$db->query( "UPDATE KategorijeNazivi SET KategorijaID=ConCat('".$_GET['ID']."',substring(KategorijaID,".$Start.",99)) WHERE KategorijaID LIKE '".$Nov."%'" );
			$db->query( "UPDATE KategorijeNazivi SET KategorijaID=ConCat('".$Nov."',substring(KategorijaID,".$Start.",99))        WHERE KategorijaID LIKE '".$Zac."%'" );
		
			$db->query( "UPDATE Kategorije SET KategorijaID=ConCat('".$Zac."',substring(KategorijaID,".$Start.",99))        WHERE KategorijaID LIKE '".$_GET['ID']."%'" );
			$db->query( "UPDATE Kategorije SET KategorijaID=ConCat('".$_GET['ID']."',substring(KategorijaID,".$Start.",99)) WHERE KategorijaID LIKE '".$Nov."%'" );
			$db->query( "UPDATE Kategorije SET KategorijaID=ConCat('".$Nov."',substring(KategorijaID,".$Start.",99))        WHERE KategorijaID LIKE '".$Zac."%'" );

			$db->query( "ALTER TABLE KategorijeNazivi   ADD CONSTRAINT KTN_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
			$db->query( "ALTER TABLE KategorijeVsebina  ADD CONSTRAINT KTV_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
			$db->query( "ALTER TABLE KategorijeBesedila ADD CONSTRAINT KTB_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
			$db->query( "ALTER TABLE KategorijeMedia    ADD CONSTRAINT KTM_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );

		} elseif ( SQLType == "MsSQL" ) {

			$db->query( "ALTER TABLE KategorijeNazivi   DROP CONSTRAINT KTN_FK_KAT" );
			$db->query( "ALTER TABLE KategorijeVsebina  DROP CONSTRAINT KTV_FK_KAT" );
			$db->query( "ALTER TABLE KategorijeBesedila DROP CONSTRAINT KTB_FK_KAT" );
			$db->query( "ALTER TABLE KategorijeMedia    DROP CONSTRAINT KTM_FK_KAT" );

			// NOTE: update ACL names (not mandatory)
			$db->query( "UPDATE SMACL SET Name='KTG-".$Zac."' + substring(Name,".($Start+4).",99)        WHERE Name LIKE 'KTG-".$_GET['ID']."%'" );
			$db->query( "UPDATE SMACL SET Name='KTG-".$_GET['ID']."' + substring(Name,".($Start+4).",99) WHERE Name LIKE 'KTG-".$Nov."%'" );
			$db->query( "UPDATE SMACL SET Name='KTG-".$Nov."' + substring(Name,".($Start+4).",99)        WHERE Name LIKE 'KTG-".$Zac."%'" );
			// NOTE: end
			$db->query( "UPDATE KategorijeMedia SET KategorijaID='".$Zac."' + substring(KategorijaID,".$Start.",99)        WHERE KategorijaID LIKE '".$_GET['ID']."%'" );
			$db->query( "UPDATE KategorijeMedia SET KategorijaID='".$_GET['ID']."' + substring(KategorijaID,".$Start.",99) WHERE KategorijaID LIKE '".$Nov."%'" );
			$db->query( "UPDATE KategorijeMedia SET KategorijaID='".$Nov."' + substring(ActionID,".$Start.",99)            WHERE KategorijaID LIKE '".$Zac."%'" );
		
			$db->query( "UPDATE KategorijeBesedila SET KategorijaID='".$Zac."' + substring(KategorijaID,".$Start.",99)        WHERE KategorijaID LIKE '".$_GET['ID']."%'" );
			$db->query( "UPDATE KategorijeBesedila SET KategorijaID='".$_GET['ID']."' + substring(KategorijaID,".$Start.",99) WHERE KategorijaID LIKE '".$Nov."%'" );
			$db->query( "UPDATE KategorijeBesedila SET KategorijaID='".$Nov."' + substring(ActionID,".$Start.",99)            WHERE KategorijaID LIKE '".$Zac."%'" );
		
			$db->query( "UPDATE KategorijeVsebina SET KategorijaID='".$Zac."' + substring(KategorijaID,".$Start.",99)        WHERE KategorijaID LIKE '".$_GET['ID']."%'" );
			$db->query( "UPDATE KategorijeVsebina SET KategorijaID='".$_GET['ID']."' + substring(KategorijaID,".$Start.",99) WHERE KategorijaID LIKE '".$Nov."%'" );
			$db->query( "UPDATE KategorijeVsebina SET KategorijaID='".$Nov."' + substring(ActionID,".$Start.",99)            WHERE KategorijaID LIKE '".$Zac."%'" );
		
			$db->query( "UPDATE KategorijeNazivi SET KategorijaID='".$Zac."' + substring(KategorijaID,".$Start.",99)        WHERE KategorijaID LIKE '".$_GET['ID']."%'" );
			$db->query( "UPDATE KategorijeNazivi SET KategorijaID='".$_GET['ID']."' + substring(KategorijaID,".$Start.",99) WHERE KategorijaID LIKE '".$Nov."%'" );
			$db->query( "UPDATE KategorijeNazivi SET KategorijaID='".$Nov."' + substring(ActionID,".$Start.",99)            WHERE KategorijaID LIKE '".$Zac."%'" );
		
			$db->query( "UPDATE Kategorije SET KategorijaID='".$Zac."' + substring(KategorijaID,".$Start.",99)        WHERE KategorijaID LIKE '".$_GET['ID']."%'" );
			$db->query( "UPDATE Kategorije SET KategorijaID='".$_GET['ID']."' + substring(KategorijaID,".$Start.",99) WHERE KategorijaID LIKE '".$Nov."%'" );
			$db->query( "UPDATE Kategorije SET KategorijaID='".$Nov."' + substring(ActionID,".$Start.",99)            WHERE KategorijaID LIKE '".$Zac."%'" );

			$db->query( "ALTER TABLE KategorijeNazivi   ADD CONSTRAINT KTN_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
			$db->query( "ALTER TABLE KategorijeVsebina  ADD CONSTRAINT KTV_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
			$db->query( "ALTER TABLE KategorijeBesedila ADD CONSTRAINT KTB_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
			$db->query( "ALTER TABLE KategorijeMedia    ADD CONSTRAINT KTM_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
		}
		$db->query("COMMIT");
	}
	// prevent opening subcategory
	if ( $len > 2 )
		$_GET['ID'] = left($_GET['ID'], $len-2);
	else
		$_GET['ID'] = "";
}
?>