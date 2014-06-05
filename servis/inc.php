<?php
/* inc.php - build framework for simpler editing (included in edit window)
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

header('Cache-Control: no-cache');
header('Pragma: no-cache');

/******************************************************************************
* Creates framework for include files (from 'template' folder) which define
* application design.
******************************************************************************/

// include application variables and settings framework
require_once("../_application.php");

// ACL retrieval function
include_once("_userACL.php");

// determine maximum number of rows to display
$MaxRows = $db->get_var("SELECT SifNVal1 FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='ListMax'");
if ( !$MaxRows ) $MaxRows = 25; // default value

// check if session still active
if ( !(isset($_SESSION['Authenticated']) && $_SESSION['Authenticated']) ) {
	$Error = "<p align=\"center\" style=\"color:red;\"><B>Session expired!</b><br>Please login again.</p>\n";
} else {

	// get action & ACL
	if ( isset($_GET['Izbor']) )
		$Action = $db->get_row("SELECT Action, ACLID, ActionID FROM SMActions WHERE Action = '". $db->escape($_GET['Izbor']) ."'");
	elseif ( isset($_GET['Action']) )
		$Action = $db->get_row("SELECT Action, ACLID, ActionID FROM SMActions WHERE ActionID = '". (int)$_GET['Action'] ."'");

	if ( isset($Action) && $Action ) {
		$_GET['Izbor']  = $Action->Action;
		$_GET['Action'] = $Action->ActionID;
		$ActionACL      = userACL( $Action->ACLID );
	} else if ( isset($_GET['Izbor']) &&
		(is_file(($Mobile ? "mobile": "template") ."/inc_". $_GET['Izbor'] .".php") ||
		 is_file(($Mobile ? "_mobile": "_template") ."/inc_". $_GET['Izbor'] . ".php")) ) {
		$ActionACL      = "RWDX";
	} else {
		$_GET['Izbor']  = "Error";
		$Error = "<p align=\"center\"><b style=\"color:red;\">Not implemented!</b></p>\n";
	}
}

if ( isset($Error) ) header( "Refresh:1; URL=./" ); // no, login

if ( $Mobile ) {
	echo "<html>\n";
	echo "<head>\n";
	echo "<title>" . $_GET['Izbor'] . "</title>\n";
	echo "<meta name=\"viewport\" content=\"initial-scale=1, maximum-scale=1.0, minimum-scale=1, user-scalable=no, width=device-width\" />\n";
	echo "<meta name=\"MobileOptimized\" content=\"320\" />\n";
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.2/jquery.mobile.min.css\" media=\"screen\" />\n";
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$WebPath ."/js/jquery/css/jqm-datebox.min.css\" />\n";
	echo "<script language=\"javascript\" type=\"text/javascript\" src=\"".$WebPath ."/js/funcs.js\"></script>\n";
	echo "<script language=\"javascript\" type=\"text/javascript\" src=\"//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js\"></script>\n";
	echo "<script language=\"javascript\" type=\"text/javascript\" src=\"//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.2/jquery.mobile.min.js\"></script>\n";
	echo "<script type=\"text/javascript\" src=\"".$WebPath ."/js/jquery/jqm-datebox.core.min.js\"></script>\n";
	echo "<script type=\"text/javascript\" src=\"".$WebPath ."/js/jquery/jqm-datebox.mode.calbox.min.js\"></script>\n";
	echo "<script type=\"text/javascript\" src=\"".$WebPath ."/js/jquery/jquery.mobile.datebox.i18n.en_US.utf8.js\"></script>\n";
	//echo "<script type=\"text/javascript\" src=\"".$WebPath ."/js/jquery/jquery.mobile.datebox.i18n.sl.utf8.js\"></script>\n";
	echo "</head>\n";
	echo "<body>\n";
} else {}

if ( isset( $Error ) ) {
	echo "<div id=\"inc\" data-role=\"page\">\n";
	echo "\t<div data-role=\"header\" data-theme=\"e\">\n";
	echo "\t\t<h1>". $_GET['Izbor'] ."</h1>\n";
	echo "\t\t<a href=\"./\" title=\"Home\" class=\"ui-btn-left\" data-iconpos=\"notext\" data-icon=\"home\" data-ajax=\"false\">Home</a>\n";
	echo "\t</div>\n";
	echo "\t<div data-role=\"content\">\n";
	echo "\t" . $Error;
	echo "\t</div>\n";
	echo "</div>\n"; // page
	if ( $Mobile ) {
		echo "</body>\n";
		echo "</html>\n";
	}
	die();
}

// include a template (template contains all data manipulation & formatting)
if ( $_GET['Izbor'] != "" ) {
	if ( is_file(($Mobile ? "mobile": "template") ."/inc_". $_GET['Izbor'] .".php") )
		// load user/custom template
		include(($Mobile ? "mobile": "template") ."/inc_". $_GET['Izbor'] .".php");
	elseif ( is_file(($Mobile ? "_mobile": "_template") ."/inc_". $_GET['Izbor'] .".php") )
		// load system template
		include(($Mobile ? "_mobile": "_template") ."/inc_". $_GET['Izbor'] .".php");
	else {
		if( $Mobile ) {
			echo "<div id=\"inc\" data-role=\"page\">\n";
			echo "\t<div data-role=\"header\" data-theme=\"e\">\n";
			echo "\t\t<h1>Error</h1>\n";
			echo "\t\t<a href=\"#\" title=\"Back\" class=\"ui-btn-left\" data-direction=\"reverse\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-rel=\"back\" data-transition=\"slide\">Back</a>\n";
			echo "\t\t<a href=\"./\" title=\"Home\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Home</a>\n";
			echo "\t</div>\n";
			echo "\t<div data-role=\"content\">\n";
			echo "\t<div class=\"ui-body ui-body-d ui-corner-all\" style=\"padding:1em;text-align:center;\">\n";
		}
		echo "<p align=\"center\" style=\"color:red;\"><B>Not implemented!</b></p>\n";
		if( $Mobile ) {
			echo "\t</div>\n";
			echo "\t</div>\n";
			echo "</div>\n"; // page
		}
	}
}

if ( $Mobile ) {
	echo "</body>\n";
	echo "</html>\n";
}
?>