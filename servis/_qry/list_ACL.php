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

if ( isset( $_GET['Brisi'] ) && (int)$_GET['Brisi'] > 1 ) {
	$db->query( "START TRANSACTION" );
	$db->query( "UPDATE SMActions   SET ACLID = NULL WHERE ACLID = ".(int)$_GET['Brisi'] );
	$db->query( "UPDATE Sifranti    SET ACLID = NULL WHERE ACLID = ".(int)$_GET['Brisi'] );
	$db->query( "UPDATE Kategorije  SET ACLID = NULL WHERE ACLID = ".(int)$_GET['Brisi'] );
	$db->query( "UPDATE Predloge    SET ACLID = NULL WHERE ACLID = ".(int)$_GET['Brisi'] );
	$db->query( "UPDATE Besedila    SET ACLID = NULL WHERE ACLID = ".(int)$_GET['Brisi'] );
	$db->query( "UPDATE Media       SET ACLID = NULL WHERE ACLID = ".(int)$_GET['Brisi'] );
	$db->query( "UPDATE Ankete      SET ACLID = NULL WHERE ACLID = ".(int)$_GET['Brisi'] );
	$db->query( "UPDATE emlMessages SET ACLID = NULL WHERE ACLID = ".(int)$_GET['Brisi'] );
	$db->query( "DELETE FROM SMACLr WHERE ACLID = ".(int)$_GET['Brisi'] );
	$db->query( "DELETE FROM SMACL  WHERE ACLID = ".(int)$_GET['Brisi'] );
	$db->query( "COMMIT" );
}
?>