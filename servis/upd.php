<?php
/*~ upd.php - just update a record based on template (no special output)
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

// include application variables and settings framework
require_once("../_application.php");

// ACL retrieval function
include_once("_userACL.php");

// check if session still active
if ( !(isset($_SESSION['Authenticated']) && $_SESSION['Authenticated']) ) {
	$Error = "<B>Session expired!</b><br>Please login again.";
} else

// get action & ACL
if ( isset($_GET['Izbor']) )
	$Action = $db->get_row("SELECT Action, ACLID, ActionID FROM SMActions WHERE Action = '". $db->escape($_GET['Izbor']) ."'");
elseif ( isset($_GET['Action']) )
	$Action = $db->get_row("SELECT Action, ACLID, ActionID FROM SMActions WHERE ActionID = '". $db->escape($_GET['Action']) ."'");

if ( isset($Action) && $Action ) {
	$_GET['Izbor']  = $Action->Action;
	$_GET['Action'] = $Action->ActionID;
	$ActionACL      = userACL($Action->ACLID);
} else if ( isset($_GET['Izbor']) &&
	(is_file("qry/edit_". $_GET['Izbor'] .".php") ||
	 is_file("_qry/edit_". $_GET['Izbor'] .".php")) ) {
	// file exists but action does not
	$ActionACL      = "RWDX";
} else {
	$_GET['Izbor']  = "";
	$Error = "Not implemented!";
}

// include a template (template contains all data manipulation & formatting)
if ( !isset($Error) && $_GET['Izbor'] != "" ) {
	if ( is_file("qry/edit_". $_GET['Izbor'] .".php") ) {
		// load user/custom template
		include("qry/edit_". $_GET['Izbor'] .".php");
	} elseif ( is_file("_qry/edit_". $_GET['Izbor'] .".php") ) {
		// load system template
		include("_qry/edit_". $_GET['Izbor'] .".php");
	} else {
		$Error = "Not implemented!";
	}
}
echo "<div data-role=\"page\">". (isset($Error) ? $Error : "OK") ."</div>\n"; // page
?>