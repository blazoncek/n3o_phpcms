<?php
/*~ clani.php - main page of application framework
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

echo "<!DOCTYPE HTML>\n";
echo "<HTML>\n";
echo "<HEAD>\n";
include_once( "../_htmlheader.php" );
echo "<SCRIPT LANGUAGE=\"JavaScript\" TYPE=\"text/javascript\">\n";
echo "<!--\n";
echo "window.focus();\n";
echo "function findMsgs(member) {\n";
echo "window.document.location.href='./?What=ID&Find=' + member;\n";
echo "window.close();\n";
echo "}\n";
echo "//-->\n";
echo "</SCRIPT>\n";
echo "</HEAD>\n";

echo "<BODY>\n";
echo "<div id=\"body\">\n";

echo "<div id=\"head\">\n";
include_once( "../_glava.php" );
echo "</div>\n";

echo "<div id=\"content\">\n";

// get user's settings
$StartMsg    = "First";
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

if ( !isset($_GET['Rows']) ) $_GET['Rows'] = 20;
$_GET['Rows'] = min(100,max(5,(int)$_GET['Rows']));

// display menu bar
include_once("_menu.php");

if ( !isset($_GET['Sort']) ) $_GET['Sort']="Nickname";
if ( !isset($_GET['Find']) ) $_GET['Find']="";
if ( !isset($_GET['Rows']) ) $_GET['Rows']=20;
if ( !isset($_GET['Page']) ) $_GET['Page']=1;

// get active registered visitors
if ( isset($_GET['ID']) && (int)$_GET['ID'] ) {
	$getMembers = array();
	$getMembers[0] = getmember($_GET['ID']);
} else if ( isset($_GET['Email']) ) {
	$getMembers = array();
	$getMembers[0] = getmemberbyemail($_GET['Email']);
} else if ( contains($_SERVER['QUERY_STRING'],'online') ) {
	// get active registered visitors
	$getMembers = getvisitors();
} else {
	$getMembers = getmembers(1, $_GET['Find'], $_GET['Sort']);
}

$MxPg = 10;
$NuPg = (int)(count($getMembers) / $_GET['Rows'] + 1);

$_GET['Page'] = min(max((int)$_GET['Page'], 1), $NuPg);

$StPg = (int)min(max(1, $_GET['Page'] - ($MxPg/2)), max(1, $NuPg - $MxPg + 1));
$EdPg = (int)min($StPg + $MxPg - 1, min($_GET['Page'] + $MxPg - 1, $NuPg));

$PrPg = max(1, $_GET['Page']-1);
$NePg = min($_GET['Page']+1, $EdPg);

$StaR = min(count($getMembers),max(1,($_GET['Page']-1)*$_GET['Rows']+1));
$EndR = min(count($getMembers),max(1,$_GET['Page']*$_GET['Rows']));

$Page = (int)$_GET['Page'];

?>
<?php if ( count($getMembers) > 1 ) : ?>
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
	<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Rows=20<?php echo $query ?>">20</A> |
	<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Rows=50<?php echo $query ?>">50</A> |
	<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Rows=100<?php echo $query ?>">100</A> |
	članov naenkrat&nbsp;
	</TD>
</TR>
<TR>
	<TD CLASS="a10">
<?php $query = (isset($_GET['Rows']) ? "?Rows=". $_GET['Rows'] : "?") . (isset($_GET['Find']) ? "&amp;Find=". $_GET['Find'] : "") ?>
	Uredi po:
	<A HREF="<?php echo $_SERVER['PHP_SELF'] . $query ?>&amp;Sort=Nickname">vzdevku</A> |
	<A HREF="<?php echo $_SERVER['PHP_SELF'] . $query ?>&amp;Sort=Posts">št. sporočil</A> |
	<A HREF="<?php echo $_SERVER['PHP_SELF'] . $query ?>&amp;Sort=Name">imenu</A> |
	<A HREF="<?php echo $_SERVER['PHP_SELF'] . $query ?>&amp;Sort=LastVisit">obisku</A>
	</TD>
	<TD ALIGN="right" CLASS="a10"></TD>
</TR>
</TABLE>
<?php endif ?>

<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="100%">
<?php if ( count($getMembers) ) : ?>
<TR>
<?php for ( $i=$StaR; $i<=$EndR; $i++ ) : ?>
	<?php
	$getMember = $getMembers[$i-1];
	$s     = ParseMetadata($getMember->Settings,',');
	$Slika = !FileExists($StoreRoot .'/diskusije/px/face/'. $s['Slika'] .'.gif') ? "default" : $s['Slika'];
	?>
	<TD VALIGN="top" WIDTH="49%">
	<TABLE BGCOLOR="<?php echo $FrameColor ?>" BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
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
		<TD ALIGN="center" VALIGN="middle" WIDTH="70"><IMG SRC="px/face/<?php echo $Slika ?>.gif" BORDER="0"></TD>
		<TD>
		<TABLE BGCOLOR="<?php echo $BackgColor ?>" BORDER="0" CELLPADDING="2" CELLSPACING="0" HEIGHT="100%" WIDTH="100%">
		<?php if ( $getMember->ShowPersonalData ) : ?>
		<TR BGCOLOR="<?php echo $BckLoColor ?>">
			<TD CLASS="a10">&nbsp;<B><?php echo $getMember->Name ?></B></TD>
		</TR>
		<?php if ( trim($getMember->Address) != "" ) : ?>
		<TR>
			<TD CLASS="a10">&nbsp;<?php echo left($getMember->Address,25). (strlen($getMember->Address)>25 ? "..." : "") ?></TD>
		</TR>
		<?php endif ?>
		<?php if ( trim($getMember->Phone) != "" ) : ?>
		<TR>
			<TD CLASS="a10">&nbsp;<?php echo $getMember->Phone ?></TD>
		</TR>
		<?php endif ?>
		<TR>
			<TD CLASS="a10">&nbsp;<?php if ( $getMember->Sex=="M" ) : ?>Moški<?php elseif ( $getMember->Sex=="F" ) : ?>Ženska<?php else : ?>ni podatka o spolu<?php endif ?></TD>
		</TR>
		<?php else : ?>
		<TR>
			<TD CLASS="a10">&nbsp;Ne želi prikaza osebnih podatkov.</TD>
		</TR>
		<?php endif ?>
		<TR>
			<TD CLASS="a10">&nbsp;<?php if ( $getMember->ShowEmail ) : ?><A HREF="mailto:<?php echo $getMember->Email ?>"><?php echo $getMember->Email ?></A><?php else : ?>Ne želi prikaza epošte.<?php endif ?></TD>
		</TR>
		<?php if ( $getMember->WebPage!="" && $getMember->Enabled ) : ?>
		<TR>
			<TD CLASS="a10">&nbsp;<A HREF="<?php echo $getMember->WebPage ?>" TARGET="_blank"><?php echo $getMember->WebPage ?></A></TD>
		</TR>
		<?php endif ?>
		<TR>
			<TD CLASS="a10">&nbsp;Sporočil: <B><?php echo $getMember->Posts ?></B></TD>
		</TR>
		<TR>
			<TD CLASS="a10">&nbsp;Včlanjen: <?php echo isDate($getMember->SignIn) ? formatDate($getMember->SignIn,"d.m.Y") : "<I>Ni podatka</I>" ?></TD>
		</TR>
		<TR>
			<TD CLASS="a10">&nbsp;Obisk: <?php echo formatDate($getMember->LastVisit,"d.m.Y") ?>
			<?php if ( !$getMember->Enabled ) : ?><b>(deaktiviran uporabnik)</b><?php endif ?>
			</TD>
		</TR>
		<TR>
			<TD CLASS="a10">&nbsp;Status: <B><?php switch ( $getMember->AccessLevel ) {
					case 5: echo "Administrator foruma"; break;
					case 4: echo "Administrator skupine"; break;
					case 3: echo "Moderator"; break;
					case 2: echo "Moderator pripravnik"; break;
					default: echo "Navaden uporabnik"; break;
				} ?></B></TD>
		</TR>
		<TR>
			<TD ALIGN="right" CLASS="a10">
			<A HREF="javascript:dialogOpen('oddaj.php?Act=Pvt&amp;Nit=0&Tema=0&ToID=<?php echo $getMember->ID ?>')">Zasebno sporočilo.</A><br>
			<A HREF="javascript:findMsgs(<?php echo $getMember->ID ?>)">Sporočila, ki jih je napisal <?php echo $getMember->Nickname ?>.</A><BR>
			</TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	</TABLE>
	</TD>
	<?php if ( $i%2 == $StaR%2 ) : ?><TD WIDTH="2%"></TD><?php endif ?>
	<?php if ( $i%2 == $StaR%2 && $i == $EndR) : ?>
	<TD WIDTH="49%">
<?php
	if ( isset($_GET['ID']) && (int)$_GET['ID'] ) {

		$getMessages = getmessagesbyuser($_GET['ID'], 5);
		
		if ( count($getMessages) ) {
			echo "<TABLE BGCOLOR=\"". $FrameColor ."\" BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"1\" WIDTH=\"100%\">\n";
			echo "<TR><TD ALIGN=\"center\"><FONT COLOR=\"". $TxtFrColor ."\">Zadnjih 5 sporočil</FONT></TD></TR>\n";
			echo "<TR><TD>\n";
			echo "<TABLE BGCOLOR=\"". $BckHiColor ."\" BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
			// izpis zadnjih nekaj sporočil
			foreach ( $getMessages AS $getMessage ) {
				$getForum = getforum($getMessage->ForumID);
				$getTopic = gettopic($getMessage->TopicID);

				if ( preg_match("/<P[^>]*>|<DIV/i",left($getMessage->MessageBody,100)) ) {
					$Bes = preg_replace("/<P([^>]*)>/i", '<DIV$1>', $getMessage->MessageBody);
					$Bes = str_replace("</P>","</DIV>", $Bes);
				} else {
					$Bes = str_replace("<BR>", chr(13) . chr(10), $getMessage->MessageBody);
					$Bes = str_replace(chr(13) . chr(10), "<BR>" . chr(13) . chr(10), $Bes);
				}
				$Bes = str_ireplace('http://www.akvazin.com/', "", $Bes);
				$Bes = str_ireplace('diskusije/', '', $Bes);
				$Bes = str_ireplace('default.cfm', './', $Bes);
				$Bes = preg_replace("/<BLOCKQUOTE +CITE=\"([^\"]*)\"([^>]*)>/i", "<P STYLE=\"margin-left:25px;\"><B>".'$1'."</B> je napisal(a):</P><BLOCKQUOTE>", $Bes);
				$Bes = preg_replace("/<([\/]*)BLOCKQUOTE([^>]*)>/i", "<".'$1'."BLOCKQUOTE>", $Bes);
				$Bes = str_replace("<P STYLE=\"margin-left:25px;\"><B></B> je napisal(a):</P>", "", $Bes);
				$Bes = preg_replace("/<[^>]*>/i", " ", $Bes);
				$Bes = left($Bes, 128) . (strlen($Bes) > 128 ? "..." : "");
				$Bes = ReplaceSmileys($Bes, "../pic/");
				
				echo "<TR><TD CLASS=\"a10\"><IMG SRC=\"px/bs.gif\" ALT=\"\" BORDER=\"0\" HEIGHT=\"12\" WIDTH=\"12\">\n";
				echo "<A HREF=\"./?Nit=". $getMessage->ForumID ."\"><B>". $getForum->ForumName ."</B></A><br>";
				echo "<A HREF=\"./?Nit=". $getMessage->ForumID ."&amp;Tema=". $getMessage->TopicID ."\"><B>". $getTopic->TopicName ."</B></A> ";
				echo "<br>". formatDate($getMessage->MessageDate,"d.m.Y \o\b H:i") . "<br>";
				echo $Bes;
				echo "</TD></TR>\n";
			}
			echo "</TABLE>\n";
			echo "</TD></TR>\n";
			echo "</TABLE>\n";
		}
	}
?>
	</TD>
	<?php endif ?>
	<?php if ( $i%2 != $StaR%2 ) : ?></TR><TR><TD COLSPAN="3" HEIGHT="10"></TD></TR><TR><?php endif ?>
<?php endfor ?>
</TR>
<?php else : ?>
<TR><TD ALIGN="center" VALIGN="middle" HEIGHT="90">Ni takih včlanjenih uporabnikov.<TD></TR>
<?php endif ?>
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
