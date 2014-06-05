<?php
/*~ index.php - main page of application framework
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
| This file is part of N3O CMS (frontend).                                  |
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

// include application variables && settings framework
require_once( "../_application.php" );

include_once( "_queries.php" );

// check for blacklisted IPs
$IPBanList = $db->get_col("SELECT IP FROM frmBanList WHERE IP IS NOT NULL");
if ( count($IPBanList) ) foreach ( $IPBanList AS $IP ) {
	if ( right($IP,1)=="*" ) {
		$banIP    = left($IP, strchr("*",$IP)-1);
		$clientIP = left($_SERVER['REMOTE_ADDR'],strlen($banIP));
	} else {
		$banIP = $IP;
		$clientIP = $_SERVER['REMOTE_ADDR'];
	}
	if ( !strcmp($clientIP,$banIP) ) {
		// IP address is blacklisted
		header( "Refresh:0; URL=../" );
		die();
	}
}

if ( !$_SESSION['MemberID'] && isset($_COOKIE['Email']) && isset($_COOKIE['Geslo']) ) {
	header( "Refresh:0; URL=login.php?login&reload&referer=". urlencode($_SERVER['PHP_SELF']) .($_SERVER['QUERY_STRING']!="" ? "&querystring=". urlencode($_SERVER['QUERY_STRING']) : "") );
	die();
}

// customized HTML cleanup
function CleanHTML( $text ) {
	$text = preg_replace("/<BLOCKQUOTE +CITE=\"([^\"]*)\"([^>]*)>/i", "<P STYLE=\"margin-left:25px;\"><B>".'$1'."</B> je napisal(a):</P><BLOCKQUOTE>", $text);
	$text = preg_replace("/<([\/]*)BLOCKQUOTE([^>]*)>/i",   "<".'$1'."BLOCKQUOTE>", $text);
	$text = preg_replace("/<A HREF=\"([^\"]*)\"([^>]*)>/i", "<A HREF=\"".'$1'."\" TARGET=\"_blank\" ".'$2'.">", $text);
	//$text = ReplaceSmileys($text, '../pic/');
	return $text;
}

echo "<!DOCTYPE HTML>\n";
echo "<HTML>\n";
echo "<HEAD>\n";
$TitleText = multiLang('<Title>', $lang) ." - ". ($KatFullText=='' ? $KatText : $KatFullText);
include_once( "../_htmlheader.php" );
include_once( "_forumheader.php" );
echo "</HEAD>\n";

echo "<BODY>\n";

// EU cookie compliance (Google Analytics & forum support)
if ( !isset($_COOKIE['accept_cookies']) && strncasecmp($WebURL, $_SERVER['HTTP_REFERER'], strlen($WebURL)) == 0 ) {
 	// get ID of special category for cookies disclaimer/description
	$ktg = $db->get_var("SELECT KategorijaID FROM Kategorije WHERE Ime = 'Cookies'");
	if ( $_GET['kat'] != $ktg ) {
		// continuing browsing -> implicit consent
		setcookie('accept_cookies', 'yes', time()+31536000, $WebPath);
		$_COOKIE['accept_cookies'] = 'yes';
	}
	unset($ktg);
}
if ( !isset($_COOKIE['accept_cookies']) && (defined('ANALYTICS_ID') || is_file(dirname(__FILE__) ."/diskusije/index.php")) ) {
	// display cookie warning
	include_once(dirname(__FILE__) ."/_cookies.php");
} else if ( isset($_COOKIE['accept_cookies']) && $_COOKIES['accept_cookies'] == "no" ) {
	// redirect to Google if not accepting
	header( "Refresh:1; URL=http://www.google.com?q=http+cookie" );
	die();
}

echo "<div id=\"body\">\n";

echo "<div id=\"head\">\n";
include_once( "../_glava.php" );
echo "</div>\n";

echo "<div id=\"content\" class=\"forum\">\n";

// wellcome message
if ( !$_SESSION['MemberID'] && $_SERVER['QUERY_STRING']=='' && FileExists("../_forumHello.php" ) )
	include_once("../_forumHello.php");

// password protected forum 
if ( isset($_POST['Geslo']) ) $_SESSION['frmPassword'] = $_POST['Geslo'];

// get user's settings
$StartMsg    = "First";
$Sort        = isset($_GET['Sort']) && $_GET['Sort']!="" ? $_GET['Sort'] : $TopicSort; // topic/message sort
$AccessLevel = 1;
$MaxMsg      = 10;
if ( $_SESSION['MemberID'] ) {
	$settings = ParseMetadata($_SESSION['Settings'],',');
	if ( isset($settings['Rows']) )  $MaxMsg   = max(10,min(100,(int)$settings['Rows']));
	if ( isset($settings['Start']) ) $StartMsg = $settings['Start'];
	if ( isset($settings['Sort']) )  $Sort     = $settings['Sort'];

	// access level: 5 - forum administrator; 4-category administrator; 3-moderator; 2-lesser moderator; 1-user;
	$AccessLevel = $_SESSION['AccessLevel'];

	updmemberlastvisit($_SESSION['MemberID']);
} else {
	if ( !@$db->query("INSERT INTO frmVisitors (SessionID,LastVisit) VALUES ('". session_id() ."','". now() ."')") )
		$db->query("UPDATE frmVisitors SET LastVisit='". now() ."' WHERE SessionID='". session_id() ."'");
}

// inactivity timeout (1 hour)
$db->query("DELETE FROM frmVisitors WHERE LastVisit<'". addDate(now(),-1/24) ."'");

if ( !isset($_GET['Rows']) ) $_GET['Rows'] = isset($_GET['Tema']) ? $MaxMsg : 25;
$_GET['Rows'] = min(100,max(5,(int)$_GET['Rows']));

// $Datum - last visit date, used in submodules
if ( isset($_GET['D']) ) {
	if ( $_GET['D'] == "LastVisit" ) {
		$Datum = $_SESSION['LastVisit'];
	} else if ( $_GET['D'] == "all" ) {
		$Datum = now();
		unset($_GET['D']);
	} else if ( isDate($_GET['D'],'d.m.Y') ) {
		$Datum = DateTime::createFromFormat('d.m.Y', $_GET['D']);
		$Datum = $Datum->format('Y-m-d H:i:s');
	}
} else {
	$Datum = $_SESSION['LastVisit'] ? $_SESSION['LastVisit'] : now();
}

// get topic data 
if ( isset($_GET['Tema']) ) {
	$getTopic = gettopic($_GET['Tema']);
	if ( !$getTopic ) {
		unset($_GET['Tema']);
	} else {
		$_GET['Nit'] = (int)$getTopic->ForumID;
	}
}

// get forum data 
if ( isset($_GET['Nit']) ) {
	$getForum = getforum($_GET['Nit']);
	if ( !$getForum ) {
		unset($_GET['Nit']);
		unset($_GET['Tema']);
	} else {
		// purge old messages if requested
		if ( $getForum->PurgeDays ) delmessages($_GET['Nit']);
	}
}

// get message(s) 
if ( isset($_GET['ID']) ) {
	// only one message selected 
	$getMessage   = getmessage((int)$_GET['ID']);
	$_GET['Nit']  = $getMessage->ForumID;
	$_GET['Tema'] = $getMessage->TopicID;
	$getTopic = gettopic($_GET['Tema']);
	$getForum = getforum($_GET['Nit']);
}

// $Permissions used in menu and footer
$IsModerator = false;
$Permissions = 0;
if ( isset($_GET['Nit']) && (int)$_GET['Nit'] ) {
	if ( $AccessLevel > 4 ) {
		// user is forum administrator
		$Permissions = 63; // admin=RLMDx
		$IsModerator = true;
	} else if ( $AccessLevel == 4 && $getForum->CatAdmin == $_SESSION['MemberID'] ) {
		// user is category administrator
		$Permissions = 63; // admin=RLMDx
		$IsModerator = true;
	} else if ( $AccessLevel > 1 ) {
		// determine if user is moderator in the thread
		$getModerator = getmoderators($_GET['Nit'], $_SESSION['MemberID']);
		if ( $getModerator->Permissions ) {
			$IsModerator = true; // user is moderator in current thread
			$Permissions = $getModerator->Permissions;
		}
	}
}
$CanLock   = (bool)( $Permissions     & 1) && !$ReadOnly;
$CanMove   = (bool)(($Permissions>>1) & 1) && !$ReadOnly;
$CanRename = (bool)(($Permissions>>2) & 1) && !$ReadOnly;
$CanDelete = (bool)(($Permissions>>3) & 1) && !$ReadOnly;

// display menu bar
include_once("_menu.php");

if ( (isset($_GET['Find']) && $_GET['Find']!="") || isset($_GET['D']) ) {

	include_once('_sporocila.php');

} else if ( ((isset($_GET['Tema']) && (int)$_GET['Tema']) || isset($_GET['ID'])) ) {
	// topic is selected 
	if ( $getForum->Password!="" ) {
		// password for forum is required 
		if ( strcmp($getForum->Password,$_SESSION['frmPassword']) ) {
			// no password supplied: ask for password 
			include_once('_geslo.php');
		} else if ( $AccessLevel>1 ) {
			// user is moderator || administrator 
			include_once('_sporocila.php');
		} else {
			// reset selected topic && forum: user cannot view 
			unset($_GET['Nit']);
			unset($_GET['Tema']);
		}
	} else {
		// display messages && topic list 
		include_once('_sporocila.php');
	}
} else if ( isset($_GET['Nit']) && (int)$_GET['Nit'] && !(isset($_GET['Tema']) || isset($_GET['ID'])) ) {
	// forum is selected && topic is not 
	if ( $getForum->Password!="" ) {
		// password for forum is required 
		if ( strcmp($getForum->Password,$_SESSION['frmPassword']) ) {
			// no password supplied: ask for password 
			include_once('_geslo.php');
		} else if ( $AccessLevel>1 ) {
			// user is moderator || administrator 
			include_once('_teme.php');
		} else {
			// reset selected topic && forum: user cannot view 
			unset($_GET['Nit']);
		}
	} else {
		// display topic list 
		include_once('_teme.php');
	}
} else if ( !(isset($_GET['Nit']) && (int)$_GET['Nit']) ) {
	// display forums 
	include_once('_niti.php');
}

// display forum footer
include_once("_foot.php");

echo "</div>\n";

echo "<div id=\"foot\">\n";
include_once( "../_noga.php" );
echo "</div>\n";

echo "</div>\n";

if ( defined('ANALYTICS_ID') && isset($_COOKIE['accept_cookies']) && $_COOKIE['accept_cookies']=='yes' ) {
	// google analytics
	echo "<script type=\"text/javascript\">\n";
	echo "var gaJsHost = ((\"https:\" == document.location.protocol) ? \"https://ssl.\" : \"http://www.\");\n";
	echo "document.write(unescape(\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));\n";
	echo "</script>\n";
	echo "<script type=\"text/javascript\">\n";
	echo "try {\n";
	echo "var pageTracker = _gat._getTracker(\"". ANALYTICS_ID ."\");\n";
	echo "pageTracker._trackPageview();\n";
	echo "} catch(err) {}</script>\n";
}
// retina support for mobile devices
if ( $Mobile || $Tablet ) {
	echo "<script language=\"javascript\" type=\"text/javascript\" src=\"$js/retina/retina.js\"></script>\n";
}

echo "</BODY>\n";
echo "</HTML>\n";
?>