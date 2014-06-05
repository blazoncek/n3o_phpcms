<?php
/*~ move.php - move message/topic
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

	if ( isset($_POST['Tema']) && trim($_POST['Tema']) != "" )
		$_POST['TemaID'] = addtopic($_POST['NitID'], $_POST['Tema'], $_SESSION['MemberID']);

	if  ( isset($_POST['NitID']) && (int)$_POST['NitID'] ) {
		if ( isset($_GET['ID']) && (int)$_GET['ID'] ) {
			$getMessage = getmessage($_GET['ID']);
			$db->query("UPDATE frmMessages
				SET ForumID=". (int)$_POST['NitID'] .",
					TopicID=". (int)$_POST['TemaID'] ."
				WHERE ID=". (int)$_GET['ID']);
			// update old topic
			updtopiccount($getMessage->TopicID);
		} else {
			$db->query("START TRANSACTION");
			$db->query("UPDATE frmTopics
				SET ForumID = ". (int)$_POST['NitID'] ."
				WHERE ID = ". (int)$_POST['TemaID']);
			$db->query("UPDATE frmMessages
				SET ForumID=". (int)$_POST['NitID'] ."
				WHERE TopicID=". (int)$_POST['TemaID']);
			$db->query("COMMIT");
		}
		// update new topic
		updtopiccount($_POST['TemaID']);
	}

	// get message(s) 
	if ( isset($_GET['ID']) ) {
		// only one message selected 
		$getMessage   = getmessage((int)$_GET['ID']);
		$_GET['Nit']  = $getMessage->ForumID;
		$_GET['Tema'] = $getMessage->TopicID;
	}
	// get forum data 
	if ( isset($_GET['Nit']) ) {
		$getForum = getforum($_GET['Nit']);
		if ( !$getForum ) {
			$_GET['Nit']  = 0;
			$_GET['Tema'] = 0;
		} else {
		}
	}

	$getForums = getforums(0,1);
?>
<?php if ( $AccessLevel > 2 ) : ?>
	<?php if ( isset($_POST['NitID']) ) : ?>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
setTimeout("tmp=window.close()",100);
window.opener.location.assign(window.opener.location.href);
window.opener.focus();
//-->
</SCRIPT>
	<?php else : ?>
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['SCRIPT_NAME'] ."?". $_SERVER['QUERY_STRING'] ?>" METHOD="post" ONSUBMIT="return confirm('Si prepričan?');">
<TABLE ALIGN="center" BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH="100%" HEIGHT="100%">
<TR>
	<TD ALIGN="center" HEIGHT="99%">
	<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD HEIGHT="20"><FONT COLOR="<?php echo $TxtFrColor ?>">&nbsp;<B>Premakni</B></FONT></TD>
	</TR>
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD>
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="1" WIDTH="100%">
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD HEIGHT="100" VALIGN="middle">

			<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
			<!--
			function updList(sObj,ID) {
	<?php if ( isset($_GET['ID']) ) : ?>
				var newOpt;
				for (var i=sObj.length; i; i--) {sObj.remove(i-1);}
				switch (ID) {
	<?php if ( count($getForums) ) foreach ( $getForums AS $getForum ) : ?>
		<?php if ( count($getTopics = gettopics($getForum->ID)) ) : ?>
				case "<?php echo $getForum->ID ?>": {
			<?php foreach ( $getTopics AS $getTopic ) : ?>
					newOpt = document.createElement("OPTION"); newOpt.text = "<?php echo str_replace(chr(13).chr(10),'',$getTopic->TopicName); ?>"; newOpt.value = "<?php echo $getTopic->ID ?>"; sObj.add(newOpt,sObj.length+1);
			<?php endforeach ?>
					break; }
		<?php endif ?>
	<?php endforeach ?>
				}
	<?php endif ?>
			}
			//-->
			</SCRIPT>
			<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
			<TR>
				<TD ALIGN="right" CLASS="a10" WIDTH="14%"><B>Nit:</B>&nbsp;</TD>
				<TD WIDTH="36%">
				<SELECT NAME="NitID" SIZE="1" CLASS="a10" STYLE="width:200px;" ONCHANGE="updList(this.form.TemaID,this.item(this.selectedIndex).value);">
	<?php if ( count($getForums) ) foreach ( $getForums AS $getForum ) : ?>
					<OPTION VALUE="<?php echo $getForum->ID ?>" <?php if ( $getForum->ID == $_GET['Nit'] ) : ?>SELECTED<?php endif ?>><?php echo $getForum->ForumName ?></OPTION>
	<?php endforeach ?>
				</SELECT>&nbsp;
				</TD>
			</TR>
	<?php if ( isset($_GET['ID']) ) : ?>
		<?php $getTopics = gettopics($_GET['Nit']); ?>
			<TR>
				<TD ALIGN="right" CLASS="a10" WIDTH="14%"><B>Tema:</B>&nbsp;</TD>
				<TD WIDTH="36%">
				<SELECT NAME="TemaID" SIZE="1" CLASS="a10" STYLE="width:200px;">
		<?php if ( count($getTopics) ) foreach ( $getTopics AS $getTopic ) : ?>
					<OPTION VALUE="<?php echo $getTopic->ID ?>" <?php if ($getTopic->ID == $getMessage->TopicID ) : ?>SELECTED<?php endif ?>><?php echo $getTopic->TopicName ?></OPTION>
		<?php endforeach ?>
				</SELECT>&nbsp;
				</TD>
			</TR>
			<TR>
				<TD ALIGN="right" CLASS="a10" WIDTH="14%"><B>Nova tema:</B>&nbsp;</TD>
				<TD WIDTH="36%"><INPUT NAME="Tema" TYPE="Text" STYLE="border:<?php echo $FrameColor ?> solid 1px;width:200px;">&nbsp;</TD>
			</TR>
			<TR>
				<TD BGCOLOR="<?php echo $BckHiColor ?>" CLASS="a10" COLSPAN="2">
				<DIV STYLE="border-bottom:silver solid 1px;">Napisal: <B><?php echo $getMessage->UserName ?></B>, <?php echo formatDate($getMessage->MessageDate,"d.m.Y \o\b H:i"); ?></DIV>
				<DIV STYLE="height:150px;overflow:scroll;"><?php echo left(preg_replace("/<STYLE.*\/STYLE>/i", "", $getMessage->MessageBody),500) ?>...</DIV>
				</TD>
			</TR>
	<?php else : ?>
			<INPUT NAME="TemaID" TYPE="Hidden" VALUE="<?php echo $_GET['Tema'] ?>">
	<?php endif ?>
			</TABLE>

			</TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	</TABLE>
	<BR>
	<INPUT VALUE="Zapiši" TYPE="Submit" CLASS="but">
	</TD>
</TR>
</TABLE>
</FORM>
	<?php endif ?>
<?php else : ?>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">window.close();</SCRIPT>
<?php endif ?>
<?php

} // $banned

echo "</BODY>\n";
echo "</HTML>\n";
?>
