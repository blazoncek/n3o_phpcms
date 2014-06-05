<?php
/*~ delete.php - delete messages
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
require_once( "../../_application.php" );

include_once( "../_queries.php" );

if ( !$_SESSION['MemberID'] && isset($_COOKIE['Email']) && isset($_COOKIE['Geslo']) ) {
	header( "Refresh:0; URL=login.php?login&reload&referer=". urlencode($_SERVER['PHP_SELF']) .($_SERVER['QUERY_STRING']!="" ? "&querystring=". urlencode($_SERVER['QUERY_STRING']) : "") );
	die();
}

echo "<!DOCTYPE HTML>\n";
echo "<HTML>\n";
echo "<HEAD>\n";
include_once( "../../_htmlheader.php" );
echo "</HEAD>\n";

echo "<BODY>\n";

$banned  = false;
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
		$banned = true;
		break;
	}
}
// is IP || user blacklisted?
if ( $banned ) {

	echo "<div class=\"text\">\n";
	echo "Nimate dovoljenja za ogled teh diskusij.\n";
	echo "</div>\n";

} else {

	$_GET['Force'] = (isset($_GET['Force']) ? (strtolower($_GET['Force']) == "yes") : false);
	$Delete        = false;
	$AccessLevel   = 0;
	if ( $_SESSION['MemberID'] ) {
		// access level: 5 - administrator; 4-super moderator; 3-moderator; 2-lesser moderator; 1-user;
		$AccessLevel = $_SESSION['AccessLevel'];
	} else {
	}

?>
<?php if ( $AccessLevel > 2 ) : ?>
<TABLE ALIGN="center" BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH="100%" HEIGHT="100%">
<TR>
	<TD ALIGN="center" HEIGHT="99%" VALIGN="middle">
	<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" HEIGHT="200" WIDTH="320">
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD ALIGN="left" HEIGHT="20"><FONT COLOR="<?php echo $TxtFrColor ?>">&nbsp;<B>Obvestilo</B></FONT></TD>
	</TR>
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD>
		<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="1" HEIGHT="100%" WIDTH="100%">
		<TR>
			<TD ALIGN="center" BGCOLOR="<?php echo $BackgColor ?>" CLASS="a14" VALIGN="middle">
	<?php if ( isset($_GET['Act']) && $_GET['Act']=="Msg" ) : ?>
		<?php $getMessage = getmessage($_GET['ID']); ?>
		<?php if ( compareDate($getMessage->MessageDate, addDate(now(),-30)) < 0 && !$_GET['Force'] ) : ?>
				<B>Besedilo je mlajše od enega meseca!</B><BR>
				Ali res želiš brisati sporočilo?<BR><BR>
				<INPUT TYPE="Button" CLASS="but" VALUE="&nbsp;&nbsp;DA&nbsp;&nbsp;" ONCLICK="document.location.href='<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $_SERVER['QUERY_STRING'] ?>&Force=Yes';">
				<INPUT TYPE="Button" CLASS="but" VALUE="&nbsp;&nbsp;NE&nbsp;&nbsp;" ONCLICK="window.close()">
		<?php else : ?>
			<?php $Delete = true; ?>
				<B>Besedilo je izbrisano!</B><BR>
				<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
				<!--
				setTimeout("tmp=window.close()",2000);
				window.opener.location.assign(window.opener.location.href);
				window.opener.focus();
				//-->
				</SCRIPT>
		<?php endif ?>
	<?php elseif ( isset($_GET['Act']) && $_GET['Act']=="Top" ) : ?>
		<?php $Delete = true; ?>
				<B>Besedila, starejša od enega meseca, so izbrisana!</B><BR>
				<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
				<!--
				setTimeout("tmp=window.close()",2000);
				window.opener.location.assign(window.opener.location.href);
				window.opener.focus();
				//-->
				</SCRIPT>
	<?php else : ?>
				<B>Napačna akcija!</B>
				<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
				<!--
				setTimeout("tmp=window.close()",2000);
				window.opener.focus();
				//-->
				</SCRIPT>
	<?php endif ?>
			</TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	</TABLE>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" CLASS="a10" VALIGN="bottom"><A HREF="javascript:window.close();"><FONT COLOR="<?php echo $TextColor ?>">Zapri</FONT></A>&nbsp;</TD>
</TR>
</TABLE>
<?php else : ?>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">window.close();</SCRIPT>
<?php endif ?>
<?php

} // $banned

echo "</BODY>\n";
echo "</HTML>\n";

if ( $Delete ) switch ( $_GET['Act'] ) {
	case "Top": delmessages($_GET['Tema'],$_GET['Nit'],$_GET['Force']); break;
	case "Msg": delmessage($_GET['ID'],$_GET['Force']); break;
}
?>
