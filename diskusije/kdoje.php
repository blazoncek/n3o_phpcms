<?php
/*~ kdoje.php - display details of a single user
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

// check if we entered correctly
if ( isset($_GET['ID']) )
	$getMember = getmember($_GET['ID']);
else if ( isset($_GET['Email']) )
	$getMembeR = getmemberbyemail($_GET['Email']);
else {
	die();
}

echo "<!DOCTYPE HTML>\n";
echo "<HTML>\n";
echo "<HEAD>\n";
$TitleText = $ForumTitle ." : Kdo je ". $getMember->Nickname ."?";
include_once( "../_htmlheader.php" );
include_once( "_forumheader.php" );
echo "<SCRIPT LANGUAGE=\"JavaScript\" TYPE=\"text/javascript\">\n";
echo "<!--\n";
echo "window.focus();\n";
echo "function findMsgs(member) {\n";
echo "window.top.opener.document.location.href='./?What=Nickname&Find=' + member;\n";
echo "window.close();\n";
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

// inactivity timeout (1 hour)
$db->query("DELETE FROM frmVisitors WHERE LastVisit<'". addDate(now(),-1/24) ."'");

$s     = ParseMetadata($getMember->Settings,',');
$Slika = $s['Slika']=="" ? "default" : $s['Slika'];
$Color = $s['Color']=="" ? "blue" : $s['Color'];
?>
	<TABLE BGCOLOR="<?php echo $FrameColor ?>" BORDER="0" CELLPADDING="1" CELLSPACING="1" HEIGHT="100%" WIDTH="100%">
	<TR>
		<TD COLSPAN="2">
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" HEIGHT="100%" WIDTH="100%">
		<TR>
			<TD COLSPAN="2">&nbsp;<B><FONT COLOR="<?php echo $TxtFrColor ?>"><?php echo $getMember->Nickname ?></FONT></B></TD>
			<TD ALIGN="right" HEIGHT="18">
			<?php if ( $getMember->ICQUIN ) : ?><A HREF="http://wwp.icq.com/<?php echo $getMember->ICQUIN ?>" TARGET="_blank"><IMG SRC="http://online.mirabilis.com/scripts/online.dll?icq=<?php echo $getMember->ICQUIN ?>&img=5" BORDER=0 ALT="<?php echo $getMember->ICQUIN ?>"></A><?php endif ?>
			<?php if ( $getMember->Patron ) : ?><IMG SRC="px/patron.png" BORDER=0 ALT="Donator!" WIDTH="16" HEIGHT="16"><?php endif ?>
			</TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	<TR BGCOLOR="<?php echo $BckHiColor ?>">
		<TD ALIGN="center" VALIGN="middle" WIDTH="17%"><IMG SRC="px/face/<?php echo $Slika ?>.gif" BORDER="0"></TD>
		<TD WIDTH="83%">
		<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" HEIGHT="100%" WIDTH="100%">
		<?php if ( $getMember->ShowPersonalData ) : ?>
		<TR BGCOLOR="<?php echo $BckLoColor ?>">
			<TD CLASS="a10">&nbsp;<B><?php echo $getMember->Name ?></B></TD>
		</TR>
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD CLASS="a10">&nbsp;<?php echo left($getMember->Address,25) . (strlen($getMember->Address)>25 ? "..." : "") ?></TD>
		</TR>
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD CLASS="a10">&nbsp;<?php echo $getMember->Phone ?></TD>
		</TR>
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD CLASS="a10">&nbsp;<?php if ( $getMember->Sex=="M" ) : ?>Moški<?php elseif ( $getMember->Sex=="F" ) : ?>Ženska<?php else : ?>ni podatka<?php endif ?></TD>
		</TR>
		<?php else : ?>
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD CLASS="a10">&nbsp;Ne želi prikaza osebnih podatkov.</TD>
		</TR>
		<?php endif ?>
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD CLASS="a10">&nbsp;<?php if ( $getMember->ShowEmail ) : ?><A HREF="mailto:<?php echo $getMember->Email ?>"><?php echo $getMember->Email ?></A><?php else : ?>Ne želi prikaza epošte.<?php endif ?></TD>
		</TR>
		<?php if ( $getMember->WebPage!="" && $getMember->Enabled ) : ?>
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD CLASS="a10">&nbsp;<A HREF="<?php echo $getMember->WebPage ?>" TARGET="_blank"><?php echo $getMember->WebPage ?></A></TD>
		</TR>
		<?php endif ?>
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD CLASS="a10">&nbsp;Sporočil: <B><?php echo $getMember->Posts ?></B></TD>
		</TR>
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD CLASS="a10">&nbsp;Včlanjen: <?php echo isDate($getMember->SignIn) ? formatDate($getMember->SignIn,"d.m.Y") : "<I>Ni podatka</I>" ?></TD>
		</TR>
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD CLASS="a10">&nbsp;Zadnji obisk: <?php echo formatDate($getMember->LastVisit,"d.m.Y") ?></TD>
		</TR>
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD CLASS="a10">&nbsp;Status: <B><?php switch ( $getMember->AccessLevel ) {
					case 5: echo "Administrator foruma"; break;
					case 4: echo "Administrator skupine"; break;
					case 3: echo "Moderator"; break;
					case 2: echo "Moderator pripravnik"; break;
					default: echo "Navaden uporabnik"; break;
				} ?></B></TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	</TABLE>
	<P ALIGN="center">
	<A HREF="javascript:findMsgs('<?php echo $getMember->Nickname ?>')">Sporočila, ki jih je napisal <?php echo $getMember->Nickname ?>.</A><BR>
	<A HREF="javascript:dialogOpen('oddaj.php?act=Pvt&amp;Nit=0&amp;Tema=0&amp;ToID=<?php echo $getMember->ID ?>')">Napiši zasebno sporočilo.</A>
	</P>
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
