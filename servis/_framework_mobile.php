<?php
/*~ _framework.php - HTML framework for administration (mobile version)
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
// include application variables and settings framework
require_once("../_application.php");
require_once("../inc/thumb/PhpThumb.inc.php");

// global vars
global $db, $mailServer, $mailUser, $mailPass, $mailSSL, $StoreRoot;

function CheckUploads($folder)
{
	// get uploaded files
	$Slike = scandir($folder);
	if ( $Slike ) foreach ( $Slike As $Slika ) {
		if ( substr($Slika,1,1) == "." || !contains("jpg,png,gif", strtolower(right($Slika,3))) ) continue;
		return true;
	}
	return false;
}

// Check email server for messages
// Messagess need to be in certain format and from currently logged user
function CheckEmails($mailServer, $mailUser, $mailPass, $mailSSL=false)
{
	global $db;
	
	// safety check
	if ( !isset($mailServer) ) return false;

	// get logged-in user details
	if ( $_SESSION['Authenticated'] ) {
		$UserEmail = $db->get_var("SELECT Email FROM SMUser WHERE UserID = " . (int)$_SESSION['UserID']);
		if ( $UserEmail == "" )
			return false;
	} else
		return false;

	//  Connect
	$conn = new POP3;
	if ( @$conn->Connect($mailUser, $mailPass, $mailServer, 110, $mailSSL) ) {
		$count = 0;

		// get list of messages
		$list = $conn->GetMessageList();
	
		// process  each message
		if ( $list ) foreach ( $list as $mail ) {
			// extract senders email address
			preg_match("/<([^>]+)>/", $mail['from'], $email);
			$email = substr($email[0], 1, strlen($email[0])-2);
			// find user messages
			if ( $email == $UserEmail && substr($mail['subject'], 0, 3) == 'ID:' ) {
				$count++;
			}
		}

		//  We need to disconnect
		$conn->Disconnect();
	
		return $count;
	}
	return false;
}

// get database version
$Version = $db->get_var( "SELECT ParamValue FROM n3oParameters WHERE ParamName = 'Version'" );

/**************************************
* Menu structure is defined in database table called SmActions like this:
* 00   "Servis" (submenu)
* 0001 "Uporabniki" (action)
* 0002 "Skupine" (action)
* 0003 "" (separator)
* 0004 "Jeziki" (action)
* 01   "Podatki" (submenu)
* 0101 "Rubrike" (action)
* ...
*--------------------------------------
* Menu structure created like:
*
<div data-role="page" id="menu">
	<div data-role="header">
		<h1>Title</h1>
		<a data-ajax="false" data-iconpos="left" data-icon="delete" data-theme="e" href="index.php?logout" title="Logout" class="ui-btn-left">Logout</a>
	</div>
	<div data-role="content">
		<ul data-role="listview" data-inset="true">
			<li><a href="#menu00">Servis</a></li>
			<li><a href="#menu01">Podatki</a></li>
			<li data-role="list-divider"></li>
			<li><a href="#menu02">....</a></li>
			....
		</ul>
	</div>
	<div data-role="footer">
		Copyright &copy; 2012 Blaž Kristan
	</div>
</div>
<div data-role="page" id="menu00">
	<div data-role="header" data-theme="b">
		<h1>Servis</h1>
		<a data-direction="reverse" data-iconpos="notext" data-icon="home" data-rel="back" href="#menu" title="Home" class="ui-btn-left">Home</a>
	</div>
	<div data-role="content">
		<ul data-role="listview" data-inset="true">
			<li><a href="users.php">Uporabniki</a></li>
			<li><a href="groups.php">Skupine</a></li>
			<li data-role="list-divider"></li>
			<li><a href="languages.php">Jeziki</a></li>
		</ul>
	</div>
	<div data-role="footer">
	</div>
</div>
<div data-role="page" id="menu01">
	<div data-role="header" data-theme="b">
		<h1>Podatki</h1>
		<a data-direction="reverse" data-iconpos="notext" data-icon="home" data-rel="back" href="#menu" title="Home" class="ui-btn-left">Home</a>
	</div>
	<div data-role="content">
		<ul data-role="listview" data-inset="true">
			<li><a href="rubrike.php">Rubrike</a></li>
			...
		</ul>
	</div>
	<div data-role="footer">
	</div>
</div>
...
**************************************/

// Access Control List checkup function
include_once("_userACL.php");

// loopXmenu: creates submenu structure
// for 1st, 2nd, ... level submenus, top level menus are created in body
function loopXmenu($menuID="00", $Name="")
{
	global $db; // database object
	global $WebPath;

	// get submenu entries
	$Menus = $db->get_results(
		"SELECT
			ActionID,
			Name,
			Action,
			Icon,
			ACLID
		FROM
			SMActions
		WHERE
			ActionID LIKE '" . $menuID . "__'
			AND
			Enabled <> 0
			AND
			MobileCapable <> 0
		ORDER BY
			ActionID" );

	if ( $Menus ) {
		// submenu object
		echo "<div id=\"menu$menuID\" data-role=\"page\">\n";

		echo "\t<div data-role=\"header\" data-theme=\"b\">\n";
		echo "\t\t<h1>". $Name ."</h1>\n";
		if ( strlen($menuID)>2 )
			echo "\t\t<a href=\"./#menu".substr($menuID,strlen($menuID)-2)."\" title=\"Back\" class=\"ui-btn-left\" data-direction=\"reverse\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-ajax=\"false\">Back</a>\n";
		else
			echo "\t\t<a href=\"./\" title=\"Home\" class=\"ui-btn-left\" data-iconpos=\"notext\" data-icon=\"home\" data-ajax=\"false\">Home</a>\n";
		echo "\t</div>\n";

		echo "\t<div data-role=\"content\">\n";
		echo "\t\t<ul data-role=\"listview\" data-inset=\"true\" data-theme=\"c\" data-dividertheme=\"b\">\n";

		// build submenu structure from DB
		foreach ( $Menus as $Menu ) {
			// get user's ACL (implement security)
			$ACL = userACL( (int) $Menu->ACLID );
			// extract menu icon
			$icon = $Menu->Icon=="" ? "" : "icon." . $Menu->Icon . ".png";

			if ( contains($ACL,"X") ) {
				// user has Execute access
				if ( $Menu->Action == "" ) {
					// Action=="" means: separator or submenu
					if ( $Menu->Name == "" ) {
						// add separator
						//echo "\t\t\t<li data-role=\"list-divider\"></li>\n";
						echo "\t\t</ul>\n";
						echo "\t\t<ul data-role=\"listview\" data-inset=\"true\" data-theme=\"c\" data-dividertheme=\"b\">\n";
					} else {
						// link to submenu
						echo "\t\t\t<li data-role=\"list-divider\">";
						echo "<a href=\"#menu$Menu->ActionID\">";
						echo "<img src=\"pic/$icon\" class=\"ui-li-icon\">";
						echo $Menu->Name;
						echo "</a></li>\n";
					}
				} else {
					// action defined
					if ( contains( $Menu->Action, "." ) )
						// explicitly defined PHP file to call
						$xURL = "mobile/$Menu->Action";
					elseif ( contains( $Menu->Action, "javascript" ) )
						// JavaScript action
						$xURL = $Menu->Action;
					else
						$xURL = "list.php?Action=$Menu->ActionID";
					
					// check if user has eXecute ACL
					$xURL = contains($ACL,"X")? $xURL: "";
					
					echo "\t\t\t<li><a href=\"$xURL\" data-ajax=\"false\">";
					echo "<img src=\"pic/$icon\" class=\"ui-li-icon\">";
					echo $Menu->Name;
					echo "</a></li>\n";
				}
			}
		}
		echo "\t\t</ul>\n";
		echo "\t</div>\n";

		echo "</div>\n"; // page
	}
}

?>
<!DOCTYPE html>
<html>
<head>
<title>[Servis] <?php echo AppName ?></title>
<meta name="Author" content="Blaž Kristan (blaz@kristan-sp.si)" />
<meta name="viewport" content="initial-scale=1, maximum-scale=1.0, minimum-scale=1, user-scalable=no, width=device-width" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<!-- Mobile IE allows us to activate ClearType technology for smoothing fonts for easy reading -->
<meta http-equiv="cleartype" content="on" />
<!-- For mobile browsers that do not recognize the viewport tag -->
<meta name="MobileOptimized" content="320" />
<link rel="apple-touch-icon" href="pic/servis-icon-precomposed-57.png" />
<link rel="apple-touch-icon" sizes="57x57" href="pic/servis-icon-precomposed-57.png" />
<link rel="apple-touch-icon" sizes="72x72" href="pic/servis-icon-precomposed-72.png" />
<link rel="apple-touch-icon" sizes="114x114" href="pic/servis-icon-precomposed-114.png" />
<link rel="apple-touch-icon" sizes="144x144" href="pic/servis-icon-precomposed-144.png" />
<link rel="icon" type="image/png" href="pic/servis-icon-128.png" />
<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.2/jquery.mobile.min.css" media="screen" />
<script language="javascript" type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.2/jquery.mobile.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $WebPath ?>/js/funcs.js"></script>
</head>
<body>
<?php
// check if app and DB version mismatch (repair database if so)
if (   substr($Version,0,1) != substr(AppVer,0,1)
	|| substr($Version,2,2) != substr(AppVer,2,2) ) {
	// spremenjena verzija/baza

?>
<div id="menu" data-role="page">
	<div data-role="header" data-theme="e">
		<h1><?php echo AppName ?></h1>
		<a data-direction="reverse" data-iconpos="notext" data-icon="home" data-rel="back" href="#menu" title="Home" class="ui-btn-left jqm-home">Home</a>
	</div>
	<div data-role="content">
		<ul data-role="listview" data-inset="true">
			<li><a href="inc.php?Izbor=SQL">Update DB Manually</a></li>
		</ul>
	</div>
	<div data-role="footer" style="text-align:center;">
		Copyright &copy; 2012-<?php echo date('Y'); ?> Blaž Kristan (v<?php echo AppVer; ?>)
	</div>
</div>
<?php

} else {

	// get top level menus
	$ServisMenus = $db->get_results(
		"SELECT
			ActionID,
			Name,
			Action,
			ACLID
		FROM
			SMActions
		WHERE
			Enabled <> 0
			AND
			ActionID LIKE '__'
			AND
			MobileCapable <> 0
		ORDER BY
			ActionID" );
	
?>
<div id="menu" data-role="page">
	<div data-role="header">
		<h1><?php echo AppName ?></h1>
		<a href="index.php?logout" title="Logout" class="ui-btn-left" data-ajax="false" data-iconpos="left" data-icon="delete" data-theme="a">Logout</a>
<?php if ( CheckEmails($mailServer, $mailUser, $mailPass) || CheckUploads($StoreRoot ."/media/upload") ) : ?>
		<a href="list.php?Izbor=Msg" title="Obvestila" class="ui-btn-right" data-iconpos="notext" data-icon="alert" data-theme="e" data-ajax="false" data-transition="slideup">Obvestila</a>
<?php endif ?>
	</div>
	<div data-role="content">
<?php

	if ($ServisMenus) {
		echo "\t<ul data-role=\"listview\" data-inset=\"true\" data-theme=\"c\" data-dividertheme=\"b\">\n";
		echo "\t\t<li data-role=\"list-divider\">Administracija</li>\n";
		// build top level menu structure
		foreach ( $ServisMenus as $Servis ) {
			// get user's ACL (implement security)
			$ACL = userACL( (int) $Servis->ACLID );
			// if user has Execute access add menu
			if ( contains($ACL,"X") )
				echo "\t\t<li><a href=\"#menu$Servis->ActionID\">$Servis->Name</a></li>\n";
		}
		echo "\t</ul>\n";
	}
	
	echo "\t<ul data-role=\"listview\" data-inset=\"true\" data-theme=\"c\" data-dividertheme=\"b\">\n";
	// enable change password
	$Password = $db->get_var( "SELECT Password FROM SMUser WHERE UserID = " . $_SESSION['UserID'] );
	// if not using LDAP logon, allow user to change password
	if ( $Password != "" AND left($Password,1) != "@" ) {
		echo "\t\t<li data-role=\"list-divider\">Orodja</li>\n";
		echo "\t\t<li><a href=\"inc.php?Izbor=Password\">Menjava gesla</a></li>\n";
	}
	if ( $_SESSION['UserID'] == 1  ) {
		echo "\t\t<li><a href=\"inc.php?Izbor=SQL\" data-ajax=\"false\">SQL</a></li>\n";
	}
	echo "\t</ul>\n";
?>
	</div>
	<div data-role="footer" data-position="fixed" style="text-align:center;">
		Copyright &copy; 2012-<?php echo date('Y'); ?> Blaž Kristan (v<?php echo AppVer; ?>)
	</div>
</div>
<?php

	// get all submenus
	$ServisMenus = $db->get_results(
		"SELECT
			ActionID,
			Name
		FROM
			SMActions
		WHERE
			Enabled <> 0
			AND
			Action IS null
			AND
			(Name IS NOT null AND Name != '')
		ORDER BY
			ActionID" );
	
	// build submenu pages (structure)
	foreach ( $ServisMenus as $Servis ) {
		loopXmenu($Servis->ActionID, $Servis->Name);
	}
}
?>
</body>
</html>
