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

if ( isset( $_GET['Brisi'] ) && $_GET['Brisi'] != "" ) {
	$db->query("START TRANSACTION");
	// delete data
	$db->query( "DELETE FROM frmModerators WHERE ForumID = ". (int)$_GET['Brisi'] );
	$db->query( "DELETE FROM frmForums     WHERE ID      = ". (int)$_GET['Brisi'] );
	$db->query("COMMIT");
}

if ( isset( $_GET['Smer'] ) && $_GET['Smer'] != "" ) {
	$db->query("START TRANSACTION");
	// move up/down
	$KatID = $db->get_var( "SELECT CategoryID FROM frmForums WHERE ID = " . (int)$_GET['ID'] );
	$Staro = $db->get_var( "SELECT ForumOrder FROM frmForums WHERE ID = " . (int)$_GET['ID'] );
	$Novo  = $Staro + (int)$_GET['Smer'];
	@$db->query( "UPDATE frmForums SET ForumOrder = 9999   WHERE CategoryID = $KatID AND ForumOrder = $Novo" );
	@$db->query( "UPDATE frmForums SET ForumOrder = $Novo  WHERE CategoryID = $KatID AND ForumOrder = $Staro" );
	@$db->query( "UPDATE frmForums SET ForumOrder = $Staro WHERE CategoryID = $KatID AND ForumOrder = 9999" );
	$db->query("COMMIT");
}
?>