<?php
/* _logout.php - Logout cleanup.
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

if ( isset($_GET["logout"]) ) {
	$_SESSION['Authenticated'] = false;
	$_SESSION['UserID']        = 0;
	$_SESSION['Username']      = "";
	$_SESSION['Password']      = "";
	$_SESSION['Name']          = "";
	$_SESSION['Groups']        = "";
	unset($_SESSION['Authenticated']);
	unset($_SESSION['UserID']);
	unset($_SESSION['Username']);
	unset($_SESSION['Password']);
	unset($_SESSION['Name']);
	unset($_SESSION['Groups']);
	header("Refresh:10; URL=../");
}
?>
