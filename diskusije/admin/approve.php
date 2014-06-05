<?php
/*~ approve.php - approve pending messages
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

	$Lock          = false;
	$AccessLevel   = 0;
	if ( $_SESSION['MemberID'] ) {
		// access level: 5 - administrator; 4-super moderator; 3-moderator; 2-lesser moderator; 1-user;
		$AccessLevel = $_SESSION['AccessLevel'];
	} else {
	}

	if ( isset($_GET['Brisi']) && (int)$_GET['Brisi'] ) {
		delmessage($_GET['Brisi'],1);
	} else if ( isset($_GET['Potrdi']) && (int)$_GET['Potrdi'] ) {
		updapprovemsg($_GET['Potrdi'],$_SESSION['MemberID']);
	}
	
	$IsModerator = ($AccessLevel > 4); // administrator is always moderator
	if ( isset($_GET['Nit']) && (int)$_GET['Nit'] ) {
		// user has selected a thread 
		$getModerator = getmoderators($_GET['Nit'], $_SESSION['MemberID']);
		if ( $getModerator->Permissions )
			$IsModerator = true; // user is moderator in current thread
		$Permissions = ($AccessLevel > 4 ? 63 : $getModerator->Permissions); // admin=RLMDx
		$getMessages = getmessages($_GET['Nit'],isset($_GET['Tema']) ? $_GET['Tema'] : 0,0); // unapproved messages in a thread
	} else {
		$Permissions = ($AccessLevel > 4 ? 63 : 0); // admin=RLMDx
		$getMessages = getmessages(0,0,0); // all unaproved messages
	}
	$CanLock   = (bool)( $Permissions     & 1);
	$CanMove   = (bool)(($Permissions>>1) & 1);
	$CanRename = (bool)(($Permissions>>2) & 1);
	$CanDelete = (bool)(($Permissions>>3) & 1);
	
	$AtLeastOne = false;

?>
<?php if ( $AccessLevel > 1 ) : ?>
<TABLE ALIGN="center" BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH="100%" HEIGHT="100%">
<TR>
	<TD ALIGN="center" HEIGHT="99%" VALIGN="top">
	<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="515">
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD ALIGN="center" HEIGHT="20"><FONT COLOR="<?php echo $TxtFrColor ?>"><B>Pregled in odobritev sporočil</B></FONT></TD>
	</TR>
	<TR><TD HEIGHT="5"></TD></TR>
	<?php if ( count($getMessages) ) foreach ( $getMessages AS $getMessage ) : ?>
		<?php
		$getTopic = gettopic($getMessage->TopicID);
		$getForum = getforum($getTopic->ForumID);
		if ( !isset($_GET['Nit']) ) {
			$getModerator = getmoderators($getTopic->ForumID,$_SESSION['MemberID']);
			$IsModerator = ($AccessLevel > 4); // administrator is always moderator
			if ( (int)$getModerator->Permissions )
				$IsModerator = true;
			$Permissions = ($AccessLevel > 4 ? 63 : $getModerator->Permissions); // admin=RLMDx
			$CanLock   = (bool)( $Permissions     & 1);
			$CanMove   = (bool)(($Permissions>>1) & 1);
			$CanRename = (bool)(($Permissions>>2) & 1);
			$CanDelete = (bool)(($Permissions>>3) & 1);
		}
		if ( $IsModerator && $CanLock ) {
			$AtLeastOne = true;
			$getMember  = getmember($getMessage->MemberID);
			
			$query = (isset($_GET['Nit']) ? "&amp;Nit=". $_GET['Nit'] : "") . (isset($_GET['Tema']) ? "&amp;Tema=". $_GET['Tema'] : "");
		?>
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD>
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="1" WIDTH="100%">
		<TR>
			<TD BGCOLOR="<?php echo $BckHiColor ?>">
			<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
			<TR BGCOLOR="<?php echo $BckLoColor ?>">
				<TD COLSPAN="2">
				<B><?php echo $getForum->ForumName ?> : <?php echo $getTopic->TopicName ?></B>
				</TD>
			</TR>
			<TR BGCOLOR="<?php echo $BackgColor ?>">
				<TD CLASS="a10">
				Napisal: <B CLASS="a12"><?php if ( $getMessage->MemberID > 1 ) : ?><A HREF="javascript:loginOpen('../kdoje.php?ID=<?php echo $getMessage->MemberID ?>');"><?php echo $getMember->Nickname ?></A><?php else : ?><?php echo $getMessage->UserName ?><?php endif ?></B>,
				<?php echo formatDate($getMessage->MessageDate,"d.m.Y \o\b H:i"); ?>
				</TD>
				<TD ALIGN="right" CLASS="a10">
				<?php if ( $getMessage->AttachedFile != "" ) : ?>
				Pripeta datoteka:
				<A HREF="../datoteke/<?php echo $getMessages.AttachedFile ?>"><IMG SRC="../px/note.gif" WIDTH=12 HEIGHT=12 ALT="" BORDER="0"></A>
				<?php endif ?>
				</TD>
			</TR>
			<TR>
				<TD COLSPAN="2">
				<?php echo $getMessage->MessageBody ?>
				<?php if ( $getMessage->ChangeDate != "" ) : ?>
				<?php $getChangeMember = getmember($getMessage->ChangeMemberID); ?>
				<DIV CLASS="a10" STYLE="border-top:silver solid 1px;margin-top:5px;padding-top:3px;">
				Spremenil:
				<B><A HREF="javascript:loginOpen('../kdoje.php?ID=<?php echo $getMessage->ChangeMemberID ?>');"><FONT COLOR="<?php echo $TextColor ?>"><?php echo ($getChangeMember->ShowPersonalData && $getChangeMember->DisplayName) ? $getChangeMember->Name : $getChangeMember->Nickname ?></FONT></A></B>,
				<?php echo formatDate($getMessage->ChangeDate,"d.m.Y \o\b H:i") ?>
				</DIV>
				<?php endif ?>
				<DIV ALIGN="center" CLASS="a10" STYLE="border-top:silver 1px solid;padding-top:5px;">
				<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Potrdi=<?php echo $getMessage->ID . $query ?>"><IMG SRC="../px/note-check.gif" WIDTH=12 HEIGHT=12 ALIGN="absmiddle" ALT="" BORDER="0"> Odobri</A> |
				<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Brisi=<?php echo $getMessage->ID . $query ?>"><IMG SRC="../px/note-del.gif" WIDTH=12 HEIGHT=12 ALIGN="absmiddle" ALT="" BORDER="0"> <FONT COLOR="<?php echo $TxtExColor ?>">Briši</FONT></A>&nbsp;
				</DIV>
				</TD>
			</TR>
			</TABLE>
			</TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	<TR><TD HEIGHT="5"></TD></TR>
		<?php } ?>
	<?php endforeach ?>
	<?php if ( !(count($getMessages) && $AtLeastOne) ) : ?>
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD>
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="1" HEIGHT="100%" WIDTH="100%">
		<TR BGCOLOR="<?php echo $BckHiColor ?>">
			<TD ALIGN="center" HEIGHT="100" VALIGN="middle">
			<B>Ni čakajočih sporočil, ki bi jih lahko odobrili!</B>
			<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
			<!--
			setTimeout("window.close()",3000);
			//-->
			</SCRIPT>
			</TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	<?php endif ?>
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
?>
