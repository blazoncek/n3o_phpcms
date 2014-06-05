<?php
/*~ narocila.php - main page of application framework
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

if ( isset($_GET['Add']) && (int)$_GET['Add'] )       addnotify($_GET['Add'], $_SESSION['MemberID']);
if ( isset($_GET['Delete']) && (int)$_GET['Delete'] ) delnotify($_GET['Delete'], $_SESSION['MemberID']);

echo "<!DOCTYPE HTML>\n";
echo "<HTML>\n";
echo "<HEAD>\n";
include_once( "../_htmlheader.php" );
echo "<SCRIPT LANGUAGE=\"JavaScript\" TYPE=\"text/javascript\">\n";
echo "<!--\n";
echo "window.focus();\n";
echo "}\n";
echo "//-->\n";
echo "</SCRIPT>\n";
echo "</HEAD>\n";

echo "<BODY>\n";

// get user's settings
$AccessLevel = 0;
if ( $_SESSION['MemberID'] ) {
	// access level: 5 - administrator; 4-super moderator; 3-moderator; 2-lesser moderator; 1-user;
	$AccessLevel = $_SESSION['AccessLevel'];

	updmemberlastvisit($_SESSION['MemberID']);
} else {
	if ( !@$db->query("INSERT INTO frmVisitors (SessionID,LastVisit) VALUES ('". session_id() ."','". now() ."')") )
		$db->query("UPDATE frmVisitors SET LastVisit='". now() ."' WHERE SessionID='". session_id() ."'");
}

?>
<TABLE CELLPADDING="2" CELLSPACING="0" BORDER="0" WIDTH="100%" HEIGHT="100%">
<TR>
	<TD ALIGN="center" HEIGHT="90%">
<?php if ( isset($_GET['Add']) ) : ?>
	<?php $getTopic = gettopic($_GET['Add']); ?>
	<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" HEIGHT="200" WIDTH="320">
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD HEIGHT="20" VALIGN="middle"><FONT COLOR="<?php echo $TxtFrColor ?>">&nbsp;<B>Obvestilo</B></FONT></TD>
	</TR>
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD HEIGHT="180">
		<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="1" HEIGHT="100%" WIDTH="100%">
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD ALIGN="center">
			Naročilo na diskusijsko temo<BR>
			<B><?php echo $getTopic->TopicName ?></B><BR>
			sprejeto!
			</TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	</TABLE>
	<SCRIPT LANGUAGE="JavaScript">
	<!--
	setTimeout("tmp=window.close();", 3000);
	// -->
	</SCRIPT>
<?php else : ?>
	<?php $getNotifys = getmembernotifys($_SESSION['MemberID']); $Color=""; ?>
	<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="320">
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD HEIGHT="20" VALIGN="middle"><FONT COLOR="<?php echo $TxtFrColor ?>">&nbsp;<B>Naročene teme</B></FONT></TD>
	</TR>
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD>
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="1" HEIGHT="100%" WIDTH="100%">
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD ALIGN="center">
			<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%" HEIGHT="100%">
	<?php if ( count($getNotifys) ) : ?>
		<?php foreach ( $getNotifys AS $getNotify ) : ?>
			<?php $Color = ($Color==$BckHiColor ? $BackgColor : $BckHiColor) ?>
			<TR BGCOLOR="<?php echo $Color ?>">
				<TD><A HREF="narocila.php?Delete=<?php echo $getNotify->ID ?>"><?php echo $getNotify->TopicName ?></A></TD>
				<TD ALIGN="right"><A HREF="narocila.php?Delete=<?php echo $getNotify->ID ?>"><IMG SRC="px/trash.gif" WIDTH=12 HEIGHT=12 ALT="Odstrani" BORDER="0"></A></TD>
			</TR>
		<?php endforeach ?>
	<?php else : ?>
			<TR BGCOLOR="<?php echo $BackgColor ?>">
				<TD ALIGN="center" HEIGHT="180"><B>Ni naročil!</B></TD>
			</TR>
			<SCRIPT LANGUAGE="JavaScript">
			<!--
			setTimeout("tmp=window.close();", 2500);
			// -->
			</SCRIPT>
	<?php endif ?>
			</TABLE>
			</TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	</TABLE>
<?php endif ?>
	</TD>
</TR>
</TABLE>
<?php

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
