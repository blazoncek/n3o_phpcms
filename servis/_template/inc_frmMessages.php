<?php
/*
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

if ( isset($_POST['MessageBody']) ) {
	// cleanup HTML
	$_POST['MessageBody'] = str_replace("< ", "&lt; ",   $_POST['MessageBody']);
	$_POST['MessageBody'] = str_replace(" >", " &gt;",   $_POST['MessageBody']);
	$_POST['MessageBody'] = preg_replace("/<([^>]*)>/i", '', $_POST['MessageBody']); // remove all HTML tags
	$_POST['MessageBody'] = str_replace("'",  "&#39;",   $_POST['MessageBody']);
	$_POST['MessageBody'] = str_replace("\"", "&quot;",  $_POST['MessageBody']);
	$_POST['MessageBody'] = str_ireplace("[b]",  "<b>",  $_POST['MessageBody']);
	$_POST['MessageBody'] = str_ireplace("[/b]", "</b>", $_POST['MessageBody']);
	$_POST['MessageBody'] = str_ireplace("[i]",  "<i>",  $_POST['MessageBody']);
	$_POST['MessageBody'] = str_ireplace("[/i]", "</i>", $_POST['MessageBody']);
	$_POST['MessageBody'] = str_ireplace("\n",   "<br>", $_POST['MessageBody']);
	$_POST['MessageBody'] = preg_replace("/[[:space:]]+/", " ", $_POST['MessageBody']);
	$_POST['MessageBody'] = substr($_POST['MessageBody'], 0, 512); // shorten the text

	$db->query("START TRANSACTION");
	// get forum details (comments are stored in forum data)
	$getTopics = $db->get_row( "SELECT T.ForumID, T.TopicName FROM frmTopics T WHERE T.ID = ". (int)$_GET['TopicID'] );

	if ( $getTopics ) {
		// insert comment into database
		$db->query("
			INSERT INTO frmMessages (
				ForumID,
				TopicID,
				UserName,
				UserEmail,
				MessageDate,
				MessageBody,
				IsApproved,
				ApprovedBy,
				IPAddr
			) VALUES (
				". $getTopics->ForumID .",
				". (int)$_GET['TopicID'] . ",
				'". (($_POST['UserName'] != "") ? $db->escape($_POST['UserName']) : "Anonymous") ."',
				". (($_POST['UserEmail'] != "") ? "'". $db->escape($_POST['UserEmail']) . "'" : "NULL") .",
				'". date("Y-m-d H:i:s") ."',
				'". $db->escape($_POST['MessageBody']) ."',
				1,
				1,
				'". $db->escape($_SERVER['REMOTE_ADDR']) ."'
			)"
			);
		$db->query( "UPDATE frmTopics SET MessageCount = MessageCount + 1 WHERE ID = ".(int)$_GET['TopicID'] );
	}
	$db->query("COMMIT");
}

if ( isset($_GET['DelMessage']) ) {
	$db->query("START TRANSACTION");
	$AttachedFile = $db->get_var( "SELECT AttachedFile FROM frmMessages WHERE ID = ".(int)$_GET['DelMessage'] );
	$TopicID = $db->get_var( "SELECT TopicID FROM frmMessages WHERE ID = ".(int)$_GET['DelMessage'] );
	if ( $AttachedFile )
		@unlink( $StoreRoot . '/diskusije/datoteke/' . $AttachedFile );

	$db->query( "DELETE FROM frmMessages WHERE ID = ".(int)$_GET['DelMessage'] );
	$db->query( "UPDATE frmTopics SET MessageCount = MessageCount - 1 WHERE ID = ".(int)$TopicID );
	$db->query("COMMIT");
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&DelMessage=[0-9]+/", "", $_SERVER['QUERY_STRING'] );
}

if ( isset($_GET['DelAttachment']) ) {
	$db->query("START TRANSACTION");
	$AttachedFile = $db->get_var( "SELECT AttachedFile FROM frmMessages WHERE ID = ".(int)$_GET['DelAttachment'] );
	if ( $AttachedFile )
		@unlink( $StoreRoot . '/diskusije/datoteke/' . $AttachedFile );

	$db->query( "UPDATE frmMessages SET AttachedFile = NULL WHERE ID = ".(int)$_GET['DelAttachment'] );
	$db->query("COMMIT");
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&DelAttachment=[0-9]+/", "", $_SERVER['QUERY_STRING'] );
}

if ( isset($_GET['Approve']) ) {
	$db->query( "UPDATE frmMessages SET IsApproved=1, ApprovedBy=1 WHERE ID = ".(int)$_GET['Approve'] );
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&Approve=[0-9]+/", "", $_SERVER['QUERY_STRING'] );
}

$db->query("START TRANSACTION");
$getMaxMsgDate = $db->get_row(
	"SELECT
		max(ID) AS LastMsg,
		max(MessageDate) AS MaxDate,
		count(*) AS MsgCount
	FROM
		frmMessages
	WHERE
		1=1
		AND TopicID = ".(int)$_GET['TopicID']."
		AND IsApproved = 1"
);
if ( $getMaxMsgDate ) $db->query(
	"UPDATE
		frmTopics
	SET
		MessageCount = ". $getMaxMsgDate->MsgCount . ",
		LastMessageDate = ". ($getMaxMsgDate->MaxDate? "'".date('Y-n-j H:i:s',sqldate2time($getMaxMsgDate->MaxDate))."'": "NULL")."
	WHERE
		ID = ".(int)$_GET['TopicID']
);
$db->query("COMMIT");

$getMessages = $db->get_results( "SELECT * FROM frmMessages WHERE TopicID = ".(int)$_GET['TopicID'] );
?>
<script language="javascript" type="text/javascript">
<!--
$(document).ready(function(){
	// bind to the form's submit event
	$("form[name='Message']").submit(function(){
		$(this).ajaxSubmit({
			target: '#divTopics',
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (empty(fObj.UserName))	{alert("Please enter username!"); fObj.UserName.focus(); return false;}
				if (empty(fObj.UserEmail))	{alert("Please enter email address!"); fObj.UserEmail.focus(); return false;}
				return true;
			} // pre-submit callback
		});
		return false;
	});
});
//-->
</script>
<?php
echo "<form name=\"Message\" action=\"". $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'] ."\" method=\"post\">\n";
echo "<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" width=\"100%\" style=\"margin:0 0 5px 0\">\n";
echo "<tr>\n";
echo "<td align=\"center\" valign=\"middle\">\n";
echo "Odgovor:<br>\n";
echo "<input name=\"UserName\" value=\"". $_SESSION['Name'] ."\" type=\"Text\" size=\"32\" readonly><br>";
echo "<input name=\"UserEmail\" value=\"". $_SESSION['Email'] ."\" type=\"Text\" size=\"32\" readonly><br>";
echo "<p><input value=\"Oddaj\" type=\"Submit\" class=\"but\"></p>\n";
echo "</td>\n";
echo "<td valign=\"top\">\n";
echo "<textarea name=\"MessageBody\" cols=\"80\" rows=\"4\" style=\"width:100%\"></textarea>\n";
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";

$BgCol="lightgrey";
// izpis zadnjih nekaj sporočil
if ( $getMessages ) foreach ( $getMessages as $msg ) {
	if ( $BgCol == "white" )
		$BgCol = "lightgrey";
	else
		$BgCol = "white";
	if ( preg_match("/<P[^>]*>|<DIV/i",left($msg->MessageBody,100)) ) {
		$Bes = preg_replace("/<P([^>]*)>/i", "<DIV\1>", $msg->MessageBody);
		$Bes = str_replace("</P>","</DIV>",$Bes);
	} else {
		$Bes = str_replace("<BR>", "\n", $msg->MessageBody);
		$Bes = str_replace("\n", "<BR>\n", $Bes);
	}
	$Bes =  str_replace("&scaron;", "š", $Bes);
	$Bes =  str_replace("&Scaron;", "Š", $Bes);
?>
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%" CLASS="frame" style="margin:5px 0">
<TR BGCOLOR="Gainsboro">
	<TD>
	<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
	<TR>
		<TD ALIGN="center" CLASS="a10" WIDTH="20">
	<?php if ( $msg->Icon != "" ) : ?>
		<?php
			switch ( $msg->Icon ) {
				case "question" : $Alt = "Vprašanje"; break;
				case "note" : $Alt = "Zabeležka"; break;
				case "lightbulb" : $Alt = "Nasvet"; break;
				case "statement" : $Alt = "POZOR!"; break;
				case "thumbsdown" : $Alt = "Buuuuu"; break;
				case "thumbsup" : $Alt = "Bravo!"; break;
				case "flag" : $Alt = "Zastavica"; break;
				case "tools" : $Alt = "Drži kot pribito!"; break;
				default : $Alt = ""; break;
			}
		?>
		<IMG SRC="../diskusije/px/<?php echo $msg->Icon ?>.gif" ALIGN="absmiddle" ALT="<?php echo $Alt ?>" BORDER="0" WIDTH="12" HEIGHT="12">
	<?php else : ?>
		<IMG SRC="../pic/trans.gif" ALIGN="absmiddle" BORDER="0" HEIGHT="12" WIDTH="12">
	<?php endif ?>
		</TD>
		<TD ALIGN="right" CLASS="a10" WIDTH="70">Napisal:</TD>
		<TD CLASS="a10" VALIGN="top">&nbsp;<?php echo ($msg->UserEmail=="" ? "" : '<a href="mailto:'. $msg->UserEmail .'">') ?><B><?php echo $msg->UserName ?></B><?php echo ($msg->UserEmail=="" ? "" : '</a>') ?>,
		<?php echo date( "j.n.Y \o\b H:i", sqldate2time($msg->MessageDate)) ?>
		(<?php echo $msg->IPaddr ?>)
	<?php if ( $msg->Locked ) : ?>
		<IMG SRC="../diskusije/px/note-lock.gif" ALIGN="absmiddle" ALT="Trajno sporočilo!" BORDER="0" HEIGHT=12 WIDTH=12>
	<?php endif ?>
		</TD>
		<TD ALIGN="right" CLASS="a10" WIDTH="25%">
	<?php if ( $msg->IsApproved != 1 ) : ?>
		<IMG SRC="../diskusije/px/note-check.gif" WIDTH=12 HEIGHT=12 ALIGN="absmiddle" ALT="" BORDER="0">
		<A HREF="javascript:void(0);" ONCLICK="$('#divTopics').load('<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $_SERVER['QUERY_STRING'] ?>&Approve=<?php echo $msg->ID ?>');" TITLE="Odobri">Odobri</A>&nbsp;
	<?php endif ?>
		<IMG SRC="../diskusije/px/note-del.gif" ALIGN="absmiddle" ALT="Delete sporočilo" BORDER="0" HEIGHT=12 WIDTH=12>
		<A HREF="javascript:void(0);" ONCLICK="$('#divTopics').load('<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $_SERVER['QUERY_STRING'] ?>&DelMessage=<?php echo $msg->ID ?>');" TITLE="Delete">Delete</A>&nbsp;
		</TD>
	</TR>
	</TABLE>
	</TD>
</TR>
<TR BGCOLOR="White">
	<TD VALIGN="top">
	<?php if ( $msg->AttachedFile != "" ) : ?>
	<TABLE ALIGN="right" BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="130">
	<TR BGCOLOR="Black">
		<TD>
		<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
			<TR BGCOLOR="WhiteSmoke">
				<TD CLASS="a10">Pripeta datoteka:</TD>
				<TD ALIGN="right"><A HREF="javascript:void(0);" ONCLICK="$('#divTopics').load('<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $_SERVER['QUERY_STRING'] ?>&DelAttachment=<?php echo $msg->ID ?>');" TITLE="Delete sporočilo"><IMG SRC="../diskusije/px/note-del.gif" ALIGN="absmiddle" ALT="Delete sporočilo" BORDER="0" HEIGHT=12 WIDTH=12></A></TD>
			</TR>
			<TR BGCOLOR="White">
				<TD CLASS="a10" COLSPAN="2"><IMG SRC="../diskusije/px/attachedfile.gif" WIDTH=12 HEIGHT=12 ALIGN="absmiddle" ALT="Pripeta datoteka" BORDER="0">&nbsp;<?php echo $msg->AttachedFile ?></TD>
			</TR>
		</TABLE>
		</TD>
	</TR>
	</TABLE>
	<?php endif ?>
	<?php echo $Bes ?>
	</TD>
</TR>
</TABLE>
<?php
}
?>
