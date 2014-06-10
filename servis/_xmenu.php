<?php
/* _xmenu.php - Create administration menu structure from database.
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

/**************************************
* requires: xmenu.css and xmenu.js
*--------------------------------------
* Menu structure is defined in database table called SmActions like this:
* 00   "System" (submenu)
* 0001 "Users" (action)
* 0002 "Groups" (action)
* 0003 "" (separator)
* 0004 "Languages" (action)
* 01   "Content" (submenu)
* 0101 "Categories" (action)
* ...
**************************************/

// Access Control List checkup function
include_once("_userACL.php");

// loopXmenu: creates Xmenu structure
// for 1st, 2nd, ... level submenus, top level menus are created in body
function loopXmenu($menuID="00")
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
			ActionID LIKE '". $menuID ."__'
			AND
			Enabled <> 0
		ORDER BY
			ActionID"
		);

	// submenu object
	echo "var Menu$menuID = new WebFXMenu;\n";

	// build submenu structure from DB
	foreach ( $Menus as $Menu ) {

		// get user's ACL (implement security)
		$ACL = userACL((int)$Menu->ACLID);

		// extract menu icon
		$icon = $Menu->Icon=="" ? "" : "icon.". $Menu->Icon .".png";

		if ( contains($ACL,"X") ) {
			// user has Execute access
			if ( $Menu->Action == "" ) {
				// Action=="" means: separator or submenu
				if ( $Menu->Name == "" ) {
					// add separator
					echo "Menu$menuID.add(new WebFXMenuSeparator());\n";
				} else {
					// create submenu
					loopXmenu($Menu->ActionID); // recursive call
					// write JS code
					echo "Menu$menuID.add(new WebFXMenuItem(\"$Menu->Name\", null, null, \"$icon\", Menu$Menu->ActionID));\n";
				}
			} else {
				// action defined
				if ( contains($Menu->Action, ".") )
					// explicitly defined PHP file to call
					$xURL = "javascript:loadTo('Edit','template/$Menu->Action')";
				elseif ( contains($Menu->Action, "javascript") )
					// JavaScript action
					$xURL = $Menu->Action;
				else
					// implicit template (called from viev.php)
					//$xURL = "view.php?Action=$Menu->ActionID";
					$xURL = "javascript:loadTo('List','list.php?Action=$Menu->ActionID')";
				
				// check if user has eXecute ACL
				$xURL = contains($ACL,"X") ? $xURL : "";
				
				// write JS code
				echo "Menu$menuID.add(new WebFXMenuItem(\"$Menu->Name\", \"$xURL\", null, \"$icon\"));\n";
			}
		}
	}
}

// check if app and DB version mismatch
$vDBInfo = explode('.', $N3OVersion);
$vAPInfo = explode('.', AppVer);
if ( $vDBInfo[0] != $vAPInfo[0] || $vDBInfo[1] != $vAPInfo[1] ) {
	// show only SQL menu so DB can be upgraded

?>
<script type="text/javascript">
<!-- //
webfxMenuImagePath = "./pic/";
webfxMenuDefaultWidth = 155;

// Menu bar
var myBar = new WebFXMenuBar;

myBar.add(new WebFXMenuButton("&nbsp;SQL", "javascript:loadTo('Edit','inc.php?Izbor=SQL')", "", null, null));

var sysMenu = new WebFXMenu;
sysMenu.add(new WebFXMenuItem("Odjava", "./?logout", null, "icon.shut_down.png"));
sysMenu.add(new WebFXMenuSeparator())
sysMenu.add(new WebFXMenuItem("v<?php echo AppVer; ?> / <?php echo $N3OVersion; ?>", null, null, 'icon.info.png'));
myBar.add(new WebFXMenuButton("&nbsp;", null, null, 'icon.tools.png', sysMenu));

document.write(myBar);
//-->
</script>
<?php

} else {

?>
<script type="text/javascript">
<!--
webfxMenuImagePath = "./pic/";
webfxMenuDefaultWidth = 155;

// Menu bar
var myBar = new WebFXMenuBar;
<?php
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
		ORDER BY
			ActionID"
		);

	// build top level Xmenu structure
	foreach ( $ServisMenus as $Servis ) {
		// get user's ACL (implement security)
		$ACL = userACL((int)$Servis->ACLID);
	
		if ( strpos($Servis->Action, ".") > 0 ) // there is "." in action (indicates "file.php?params" as action)
			// explicitly defined PHP file to call
			$xURL = "javascript:loadTo('Edit','template/$Servis->Action')";
		elseif ( strpos($Servis->Action, "javascript") === 0 )
			// javascript code to execute
			$xURL = $Servis->Action;
		else
			// implicitly defined template to call)
			$xURL = "javascript:loadTo('List','list.php?Action=$Servis->ActionID')";

		// check if user has eXecute ACL
		$xURL = contains($ACL,"X")? $xURL: "";

		// if user has Execute access add menu
		if ( contains($ACL,"X") )
			if ( $Servis->Action == "" ) {
				loopXmenu($Servis->ActionID); // recursive function (top level call)
				// write JS code
				echo "myBar.add(new WebFXMenuButton(\"&nbsp;$Servis->Name\", null, null, null, Menu$Servis->ActionID));\n";
			} else {
				// write JS code
				echo "myBar.add(new WebFXMenuButton(\"&nbsp;$Servis->Name\", \"$xURL\", null, null, null));\n";
			}
	}
?>
var sysMenu = new WebFXMenu;
sysMenu.add(new WebFXMenuItem("Logout", "./?logout", null, "icon.shut_down.png"));
sysMenu.add(new WebFXMenuItem("View page", "../", "_blank", "icon.home.png"));
<?php
	$Password = $db->get_var("SELECT Password FROM SMUser WHERE UserID = ". $_SESSION['UserID']);
	
	// if not using LDAP logon, allow user to change password
	if ( $Password != "" && left($Password,1) != "@" )
		echo "sysMenu.add(new WebFXMenuItem(\"Change password\", \"javascript:loadTo('Edit','inc.php?Izbor=Password')\", null, \"icon.lock.png\"));\n";
?>
//sysMenu.add(new WebFXMenuItem("SQL", "javascript:loadTo('Edit','vnos.php?Izbor=SQL')", null, "icon.process.png"));
sysMenu.add(new WebFXMenuSeparator());
<?php if ( CheckEmails($mailServer, $mailUser, $mailPass) || CheckUploads($StoreRoot ."/media/upload") ) : ?>
sysMenu.add(new WebFXMenuItem("Obvestila", "javascript:loadTo('List','list.php?Izbor=Msg')", null, 'icon.warning.png'));
sysMenu.add(new WebFXMenuSeparator());
<?php endif ?>
sysMenu.add(new WebFXMenuItem("v<?php echo AppVer; ?> / <?php echo $N3OVersion; ?>", null, null, 'icon.info.png'));
myBar.add(new WebFXMenuButton("&nbsp;<?php echo $_SESSION['Name']; ?>", null, null, 'icon.tools.png', sysMenu));

document.write(myBar);
//-->
</script>
<?php
}
?>