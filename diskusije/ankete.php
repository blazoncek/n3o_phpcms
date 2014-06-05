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

echo "<!DOCTYPE HTML>\n";
echo "<HTML>\n";
echo "<HEAD>\n";
include_once( "../_htmlheader.php" );
include_once( "_forumheader.php" );
echo "</HEAD>\n";

echo "<BODY>\n";
echo "<div id=\"body\">\n";

echo "<div id=\"head\">\n";
include_once( "../_glava.php" );
echo "</div>\n";

echo "<div id=\"content\">\n";

// display menu bar
include_once("_menu.php");

if ( !isset($_GET['Rows']) ) $_GET['Rows'] = 10;
$_GET['Rows'] = min(50,max(10,(int)$_GET['Rows']));

if ( isset($_GET['Find']) && strlen(trim($_GET['Find'])) > 2 ) {
	$find = SearchString("P.Question",$_GET['Find']);
}
// ANKETE
$getPolls = $db->get_results("SELECT P.*, T.TopicName, T.ForumID
	FROM frmPoll P, frmTopics T
	WHERE P.TopicID = T.ID
	". (isset($find) ? "AND (". $find .")" : "") ."
	ORDER BY T.LastMessageDate DESC");

$MxPg = 10;
$NuPg = (int)(count($getPolls) / $_GET['Rows'] + 1);

$_GET['Page'] = min(max((int)$_GET['Page'], 1), $NuPg);

$StPg = (int)min(max(1, $_GET['Page'] - ($MxPg/2)), max(1, $NuPg - $MxPg + 1));
$EdPg = (int)min($StPg + $MxPg - 1, min($_GET['Page'] + $MxPg - 1, $NuPg));

$PrPg = max(1, $_GET['Page']-1);
$NePg = min($_GET['Page']+1, $EdPg);

$StaR = min(count($getPolls),max(1,($_GET['Page']-1)*$_GET['Rows']+1));
$EndR = min(count($getPolls),max(1,$_GET['Page']*$_GET['Rows']));

$Page = (int)$_GET['Page'];

?>
<?php $query = (isset($_GET['Find']) ? "&amp;Find=". $_GET['Find'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") . (isset($_GET['Rows']) ? "&amp;Rows=". $_GET['Rows'] : "") ?>
<TABLE BORDER=0 CELLPADDING="2" CELLSPACING=0 WIDTH="100%">
<TR>
	<TD CLASS="a10" WIDTH="50%" STYLE="border-bottom:1px solid <?php echo $FrameColor ?>;">
	Stran:
	<?php if ( $StPg > 1 ) : ?>[<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Page=<?php echo $StPg+1 . $query ?>">&laquo;</A>]<?php endif ?>
	<?php if ( $Page > 1 ) : ?>[<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Page=<?php echo $PrPg . $query ?>">&lt;</A>]<?php endif ?>
	<?php for ( $i=$StPg; $i<=$EdPg; $i++ ) { ?>
	[<?php if ( $i == $Page ) : ?><FONT COLOR="<?php echo $TxtExColor ?>"><B><?php echo $i ?></B></FONT><?php else : ?><A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Page=<?php echo $i . $query ?>"><?php echo $i ?></A><?php endif ?>]
	<?php } ?>
	<?php if ( $Page < $EdPg ) : ?>[<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Page=<?php echo $NePg . $query ?>">&gt;</A>]<?php endif ?>
	<?php if ( $NuPg > $EdPg ) : ?>[<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Page=<?php echo $EdPg+1 .$query ?>">&raquo;</A>]<?php endif ?>
	</TD>
	<TD ALIGN="right" CLASS="a10" WIDTH="50%" STYLE="border-bottom:1px solid <?php echo $FrameColor ?>;">
	<?php $query = (isset($_GET['Find']) ? "&amp;Find=". $_GET['Find'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") ?>
	Prikaži |
	<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Rows=10<?php echo $query ?>">10</A> |
	<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Rows=20<?php echo $query ?>">20</A> |
	<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Rows=50<?php echo $query ?>">50</A> |
	anket naenkrat&nbsp;
	</TD>
</TR>
<TR><TD COLSPAN="2" HEIGHT="5"></TD></TR>
</TABLE>

<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<?php
if ( count($getPolls) ) for ( $i=$StaR; $i<=$EndR; $i++ ) {
	$getPoll = $getPolls[$i-1];
	$Txt = ReplaceSmileys($getPoll->Question,'../pic/');
?>
	<?php if ( $i%2 == 1 ) : ?><TR><?php endif ?>
	<TD ALIGN="center" VALIGN="top" WIDTH="49%">
	<TABLE ALIGN="center" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD>
		<TABLE ALIGN="center" BORDER="0" CELLPADDING="2" CELLSPACING="1" WIDTH="100%">
		<TR>
			<TD ALIGN="center" HEIGHT="16"><A HREF="./?Nit=<?php echo $getPoll->ForumID ?>&amp;Tema=<?php echo $getPoll->TopicID ?>"><FONT COLOR="<?php echo $TxtFrColor ?>"><?php echo $getPoll->TopicName ?></FONT></A></TD>
		</TR>
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD ALIGN="center"><P><B><?php echo $Txt ?></B></P>
			<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0">
			<?php
			for ( $j=1; $j<=$getPoll->Answers; $j++ ) {
				$Odg = eval('return $getPoll->A'. $j .';');
				$Rez = eval('return $getPoll->R'. $j .';');
				$Pct = 0;
				if ( $getPoll->Votes > 0)
					$Pct = round($Rez*100 / $getPoll->Votes);
				$NPct= 100 - $Pct;
			?>
				<TR>
					<TD CLASS="a10"><?php echo $Odg ?>&nbsp;</TD>
					<TD ALIGN="center" WIDTH="110"><?php if ( $Pct != 0 ) : ?><IMG SRC="px/red.gif" WIDTH=<?php echo $Pct ?> HEIGHT=10><?php endif ?><?php if ( $NPct != 0 ) : ?><IMG SRC="px/wht.gif" WIDTH=<?php echo $NPct ?> HEIGHT=10><?php endif ?></TD>
					<TD ALIGN="right" CLASS="a10" WIDTH="35"><?php echo $Pct ?>%</TD>
					<TD ALIGN="right" CLASS="a10" WIDTH="35">[<?php echo $Rez ?>]</TD>
				</TR>
			<?php } ?>
			</TABLE>
			<P CLASS="a10">Vseh glasov: <B><?php echo $getPoll->Votes ?></B></P>
			</TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	</TABLE>
	</TD>
	<?php if ( $i%2 == 1 ) : ?><TD WIDTH="2%">&nbsp;</TD><?php endif ?>
	<?php if ( $i%2 != 1 ) : ?></TR><?php if ( $i < $EndR-1 ) : ?><TR><TD COLSPAN="3" HEIGHT="10"></TD></TR><?php endif ?><TR><?php endif ?>
<?php
} else
	echo "<TR><TD ALIGN=\"center\" VALIGN=\"middle\" HEIGHT=\"40\">Ni takih anket.</TD></TR>\n";
?>
</TABLE>
<?php

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