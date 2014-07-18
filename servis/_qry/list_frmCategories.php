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

if ( isset( $_GET['Brisi'] ) && (int)$_GET['Brisi'] != "" ) {
	$db->query("START TRANSACTION");
	$db->query( "UPDATE frmForums SET CategoryID = NULL WHERE CategoryID = ". (int)$_GET['Brisi'] );
	$db->query( "DELETE FROM frmCategories WHERE ID = ". (int)$_GET['Brisi'] );
	$db->query("COMMIT");
}

// move items up/down
if ( isset( $_GET['Smer'] ) && $_GET['Smer'] != "" ) {
	$db->query("START TRANSACTION");
	// calculate new position
	$ItemPos = $db->get_var( "SELECT CategoryOrder FROM frmCategories WHERE ID = ".(int)$_GET['Item'] );
	$ItemNew = $ItemPos + (int)$_GET['Smer'];
	// move
	$db->query( "UPDATE frmCategories SET CategoryOrder=9999     WHERE CategoryOrder=$ItemNew" );
	$db->query( "UPDATE frmCategories SET CategoryOrder=$ItemNew WHERE CategoryOrder=$ItemPos" );
	$db->query( "UPDATE frmCategories SET CategoryOrder=$ItemPos WHERE CategoryOrder=9999" );
	$db->query("COMMIT");
}
?>
