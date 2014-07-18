<?php
/* index.php - main page of administration framework
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

header('Cache-Control: no-cache');
header('Pragma: no-cache');

/******************************************************************************
* Creates framework for include files (from 'template' folder) which define
* application design. Also invokes _login.php and/or _logout.php depending
* on URL parameters.
******************************************************************************/

// include application variables and settings framework
require_once("../_application.php");

/* (moved to _xmenu.php for better UXP)
// version mismatch handling
$vDBInfo = explode('.',$N3OVersion);
$vAPInfo = explode('.',AppVer);
if ( $vDBInfo[0] != $vAPInfo[0] || $vDBInfo[1] != $vAPInfo[1] ) {
	// update DB if DB version and App version differ
	header( "Refresh:1; URL=db-init.php?update" );
	die();
} else */

if ( isset($_GET['logout']) ) {
	// clear session variables and prompt login
	include_once("_logout.php");
	include_once("_login.php");

} elseif ( isset($_SESSION['Authenticated']) && $_SESSION['Authenticated'] ) {
	//user authenticated, build framework
	if ( $Mobile )
		include_once("_framework_mobile.php");
	else
		include_once("_framework.php");

} else {
	// prompt user to login
	include_once("_login.php");

}
?>
