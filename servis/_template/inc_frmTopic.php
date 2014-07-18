<?php
/*
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend and backend)                                 |
|   Version: 2.2.2                                                          |
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

if ( isset($_POST['NitID']) ) {
	$db->query("START TRANSACTION");
	$db->query( "UPDATE frmTopics   SET ForumID = ".(int)$_POST['NitID']." WHERE ID = ".(int)$_POST['TemaID'] );
	$db->query( "UPDATE frmMessages SET ForumID = ".(int)$_POST['NitID']." WHERE TopicID = ".(int)$_POST['TemaID'] );
	$db->query("COMMIT");
}

if ( isset($_GET['Sticky']) ) {
	$db->query( "UPDATE frmTopics SET Sticky = !Sticky WHERE ID = ".(int)$_GET['Sticky'] );
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&Sticky=[0-9]+/", "", $_SERVER['QUERY_STRING'] );
}

if ( isset($_GET['DelTopic']) ) {
	// delete attachments first
	$DelMessages = $db->get_col( "SELECT AttachedFile FROM frmMessages WHERE TopicID = ".(int)$_GET['DelTopic']." AND AttachedFile IS NOT NULL" );
	if ( $DelMessages ) foreach ( $DelMessages as $DelMessage ) {
		if ( $DelMessage->AttachedFile != "")
			@unlink( $StoreRoot . '/diskusije/datoteke/' . $DelMessage->AttachedFile );
	}
	$db->query("START TRANSACTION");
	$db->query( "UPDATE Besedila       SET ForumTopicID = NULL WHERE ForumTopicID = ".(int)$_GET['DelTopic'] );
	$db->query( "UPDATE frmPvtMessages SET TopicID = NULL      WHERE TopicID = ".(int)$_GET['DelTopic'] );
	$db->query( "DELETE FROM frmNotify   WHERE TopicID = ".(int)$_GET['DelTopic'] );
	$db->query( "DELETE FROM frmMessages WHERE TopicID = ".(int)$_GET['DelTopic'] );
	$db->query( "DELETE FROM frmTopics   WHERE ID = ".(int)$_GET['DelTopic'] );
	$db->query("COMMIT");

	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&DelTopic=[0-9]+/", "", $_SERVER['QUERY_STRING'] );
}

$List = $db->get_results(
	"SELECT
		T.ID,
		T.TopicName,
		T.LastMessageDate,
		T.MessageCount,
		(SELECT count(*) FROM frmMessages M WHERE M.ForumID = T.ForumID AND M.TopicID = T.ID) AS TotalMessageCount,
		T.Sticky,
		P.Votes
	FROM
		frmTopics T
		LEFT JOIN frmPoll P ON T.ID = P.TopicID
	WHERE
		ForumID = ".(int)$_GET['ForumID']."
	HAVING
		TotalMessageCount > 0
	ORDER BY
		T.Sticky DESC,
		T.LastMessageDate DESC,
		TotalMessageCount DESC,
		T.TopicName"
);

?>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
function checkTopic(ID, Naziv) {
	if (confirm("Ali res želite brisati celotno temo '"+Naziv+"'?"))
		$("#divTopics").load("<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>&DelTopic="+ID);
	return false;
}
//-->
</SCRIPT>
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<?php if ( !$List ) : ?>
<TR BGCOLOR="white">
	<TD ALIGN="center" VALIGN="middle">
	<BR><BR><B>No data!</B><BR><BR><BR>
	</TD>
</TR>
<?php else : ?>
	<?php
	$CurrentRow = 1;
	$RecordCount = count( $List );
	foreach ( $List as $Item ) {
		$Title = $Item->TopicName;
		if ( $Title == "" )
			$Title = "(no title)";
		echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
		echo "<TD ALIGN=\"center\" WIDTH=\"11\">";
		if ( $Item->Sticky )
			echo "<IMG SRC=\"pic/list.pin.gif\" WIDTH=11 HEIGHT=11 ALT=\"Lepljiva nit.\" BORDER=\"0\" ALIGN=\"absmiddle\" CLASS=\"icon\">";
		else
			echo "<IMG SRC=\"pic/trans.gif\" WIDTH=11 HEIGHT=11 ALT=\"\" BORDER=\"0\" ALIGN=\"absmiddle\" CLASS=\"icon\">";
		echo "</TD>\n";
		echo "<TD><A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divTopics').load('inc.php?Izbor=frmMessages&TopicID=$Item->ID');\">$Title</A></TD>\n";
		echo "<TD ALIGN=\"right\" CLASS=\"a10\" WIDTH=\"5%\" VALIGN=\"top\">";
		if ( $Item->TotalMessageCount > $Item->MessageCount ) {
			echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divTopics').load('inc.php?Izbor=frmMessages&TopicID=$Item->ID');\">";
			echo "<IMG SRC=\"pic/list.info.gif\" WIDTH=11 HEIGHT=11 ALT=\"Novo\" BORDER=\"0\" ALIGN=\"absmiddle\" CLASS=\"icon\">";
			echo "</A>&nbsp;";
		}
		echo $Item->MessageCount . "&nbsp;";
		echo "</TD>\n";
		echo "<TD ALIGN=\"right\" CLASS=\"a10\" WIDTH=\"20%\" VALIGN=\"top\">";
		if ( sqldate2time($Item->LastMessageDate) )
			echo date("j.n.y \o\b H:i",sqldate2time($Item->LastMessageDate)) . "&nbsp;";
		echo "</TD>\n";
		echo "<TD ALIGN=\"right\" WIDTH=\"80\">";
		if ( $Item->Votes )
			echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','inc.php?Izbor=frmPoll&TopicID=$Item->ID')\"><IMG SRC=\"pic/list.taskpad.gif\" WIDTH=11 HEIGHT=11 ALT=\"Anketa\" BORDER=\"0\" ALIGN=\"absmiddle\" CLASS=\"icon\"></A>&nbsp;";
		echo "&nbsp;<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divTopics').load('".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&Sticky=$Item->ID')\" TITLE=\"Pripni\"><IMG SRC=\"pic/list.pinned.gif\" WIDTH=11 HEIGHT=11 ALT=\"Lepljiva nit.\" BORDER=\"0\" ALIGN=\"absmiddle\" CLASS=\"icon\"></A>&nbsp;";
		echo "&nbsp;<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divTopics').load('inc.php?Izbor=frmTopicMove&ForumID=".$_GET['ForumID'] . "&ID=$Item->ID');\" TITLE=\"Premakni\"><IMG SRC=\"pic/list.extern.gif\" WIDTH=11 HEIGHT=11 ALT=\"Premakni temo v drugo nit\" BORDER=\"0\" ALIGN=\"absmiddle\" CLASS=\"icon\"></A>&nbsp;";
		echo "&nbsp;<A HREF=\"javascript:void(0);\" ONCLICK=\"checkTopic($Item->ID,'$Item->TopicName');\" TITLE=\"Delete\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" ALIGN=\"absmiddle\" CLASS=\"icon\"></A>";
		echo "</TD>\n";
		echo "</TR>\n";
		$CurrentRow++;
	}
	?>
<?php endif ?>
</TABLE>
