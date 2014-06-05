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

if ( !isset($_SESSION['MemberID']) && !$_SESSION['MemberID'] && isset($_COOKIE['Email']) && isset($_COOKIE['Password']) ) {
	header( "Refresh:1; URL=". $WebURL ."/login.php?login&referer=". urlencode($_SERVER['PHP_SELF']) ."&amp;querystring=". urlencode($_SERVER['QUERY_STRING']) );
	die();
}

// customized HTML cleanup
function CleanHTML( $text ) {
	$text = preg_replace("/<BLOCKQUOTE +CITE=\"([^\"]*)\"([^>]*)>/i", "<P STYLE=\"margin-left:25px;\"><B>".'$1'."</B> je napisal(a):</P><BLOCKQUOTE>", $text);
	$text = preg_replace("/<([\/]*)BLOCKQUOTE([^>]*)>/i",   "<".'$1'."BLOCKQUOTE>", $text);
	$text = preg_replace("/<A HREF=\"([^\"]*)\"([^>]*)>/i", "<A HREF=\"".'$1'."\" TARGET=\"_blank\" ".'$2'.">", $text);
	$text = ReplaceSmileys($text, '../pic/');
	return $text;
}

echo "<!DOCTYPE HTML>\n";
echo "<HTML>\n";
echo "<HEAD>\n";
$TitleText = $ForumTitle ." - ". ($KatFullText=='' ? $KatText : $KatFullText);
include_once( "../_htmlheader.php" );
include_once( "_forumheader.php" );
echo "</HEAD>\n";

echo "<BODY>\n";

//echo "<div id=\"body\">\n";
/*
echo "<div id=\"head\">\n";
include_once( "../_glava.php" );
echo "</div>\n";
*/
//echo "<div id=\"content\">\n";
// display menu bar
//include_once("_menu.php");
?>

<?php if ( !(isset($_GET['What']) && (isset($_GET['ID']) || (isset($_GET['Tema']) && isset($_GET['Nit'])))) ) : ?>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
//window.close();
// -->
</SCRIPT>
<?php else : ?>

<?php
switch ( $_GET['What'] ) {
	case "Diskusije":
		if ( isset($_GET['ID']) ) {
			$getMessages    = array();
			$getMessages[0] = getmessage($_GET['ID']);
			$getTopic       = gettopic($getMessages[0]->TopicID);
			$getForum       = getforum($getMessages[0]->ForumID);
		} elseif ( isset($_GET['Tema']) && isset($_GET['Nit']) ) {
			$getMessages = getmessages($_GET['Nit'],$_GET['Tema']);
			$getTopic    = gettopic($_GET['Tema']);
			$getForum    = getforum($_GET['Nit']);
		}
		break;
}
?>
<?php
echo "<DIV style=\"text-align:center;\">";
if ( fileExists("../pic/title.png") )
	echo "<IMG SRC=\"../pic/title.png\" ALT=\"\" BORDER=\"0\"><BR>";
else if ( fileExists("../pic/title.gif") )
	echo "<IMG SRC=\"../pic/title.gif\" ALT=\"\" BORDER=\"0\"><BR>";
else if ( fileExists("../pic/title.jpg") )
	echo "<IMG SRC=\"../pic/title.jpg\" ALT=\"\" BORDER=\"0\"><BR>";
echo $TitleText;
echo "</DIV>\n";

switch ( $_GET['What'] ) {

	case "Diskusije":
		echo "<H1 style=\"text-align:center;\">". $getForum->ForumName ." : ". $getTopic->TopicName ."</H1>\n";
		if ( count($getMessages) ) {
			foreach ( $getMessages AS $getMessage ) {
				echo "<div class=\"post\">\n";
				$getMember = getmember($getMessage->MemberID);
				echo "<div style=\"background-color:silver;\">";
				echo "<IMG SRC=\"px/". ($getMessage->Icon!="" ? $getMessage->Icon : "trans") .".gif\" WIDTH=\"12\" HEIGHT=\"12\" ALIGN=\"absmiddle\" BORDER=\"0\" HSPACE=\"3\">";
				echo "Napisal: <B>". ($getMessage->MemberID && $getMessage->MemberID>1 ? $getMember->Nickname : $getMessage->UserName) ."</B>, ";
				echo formatDate($getMessage->MessageDate,"j.n.y \o\b H:i");
				echo "</div>\n";
				echo CleanHTML($getMessage->MessageBody) ."\n";
				if ( $getMessage->ChangeDate != "" ) {
					$getMember = getmember($getMessage->ChangeMemberID);
					echo "<div class=\"a10\" style=\"border-top:silver solid 1px;margin:5px 0;padding-top:3px;\">";
					echo "(Spremenil: <B>". $getMember->Nickname ."</B>, ";
					echo formatDate($getMessage->ChangeDate,"j.n.y \o\b H:i");
					echo "</div>\n";
				}
				echo "</div>\n";
				echo "<HR COLOR=\"gray\" NOSHADE SIZE=\"1\" STYLE=\"margin:10px 0;\">\n";
			}
		}
	break;

}
?>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
window.print();
// -->
</SCRIPT>

<?php endif ?>

<?php
// display forum footer
//include_once("_foot.php");

//echo "</div>\n";
/*
echo "<div id=\"foot\">\n";
include_once( "../_noga.php" );
echo "</div>\n";
*/
//echo "</div>\n";

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
?>
</BODY>
</HTML>
