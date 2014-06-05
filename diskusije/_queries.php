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

/*------------------------------*
 * query support functions
 *------------------------------*/

// top categories (menu)
$Rubrike = $db->get_results(
	"SELECT
		K.KategorijaID AS ID,
		K.Ime,
		KN.Naziv,
		KN.Povzetek
	FROM
		Kategorije K
		LEFT JOIN KategorijeNazivi KN
			ON K.KategorijaID=KN.KategorijaID
	WHERE
		K.Izpis<>0
		AND K.KategorijaID LIKE '__'
		AND (KN.Jezik='$lang' || KN.Jezik IS NULL)
	ORDER BY
		K.KategorijaID,
		KN.Jezik DESC"
	);
// category title & description
$Kat = $db->get_row(
	"SELECT
		K.KategorijaID,
		K.Ime,
		KN.Naziv,
		KN.Opis,
		KN.Povzetek
	FROM
		Kategorije K
		LEFT JOIN KategorijeNazivi KN
			ON K.KategorijaID = KN.KategorijaID
	WHERE
		K.KategorijaID = '". $db->escape($_GET['kat']) ."'
		AND (KN.Jezik = '$lang' OR KN.Jezik IS NULL)
	ORDER BY
		KN.Jezik DESC
	LIMIT 1"
	);
// get kategory text for permalinks
$KatText     = $Kat->Ime;
$KatFullText = $Kat->Naziv;

// get forum parameters from database
$AllowAnonymous = strtolower($db->get_var("SELECT ParamValue FROM frmParameters WHERE ParamName='AllowAnonymous'")) == "yes";
$ReadOnly       = strtolower($db->get_var("SELECT ParamValue FROM frmParameters WHERE ParamName='ReadOnly'")) == "yes";
$ForumChat      = strtolower($db->get_var("SELECT ParamValue FROM frmParameters WHERE ParamName='ForumChat'")) == "yes";
$ForumTitle     = $db->get_var("SELECT ParamValue FROM frmParameters WHERE ParamName='ForumTitle'");
$ForumTitle     = !$ForumTitle || $ForumTitle=="" ? AppName : $ForumTitle;
$TopicSort      = $db->get_var("SELECT ParamValue FROM frmParameters WHERE ParamName='TopicSort'");
$TopicSort      = !$TopicSort || $TopicSort=="" ? "Date" : $TopicSort;
$TopicsPerPage  = max(25,min(100,(int)$db->get_var("SELECT ParamValue FROM frmParameters WHERE ParamName='TopicsPerPage'")));

// delete a single message
function delmessage($ID, $Force=0) {
	global $db, $StoreRoot;
	$getMessage = getmessage($ID);

	if ( $getMessage->AttachedFile!="" && !$getMessage->Locked )
		@unlink($StoreRoot .'/diskusije/datoteke/'. $getMessage->AttachedFile);

	$db->query("DELETE FROM frmMessages WHERE ID=". (int)$ID .(!$Force ? " AND Locked=0 AND AND MessageDate<'". addDate(now(),-30) ."'" : ""));

	// update topic stats
	updtopiccount($getMessage->TopicID);
}

// delete multiple messages for a given topic
function delmessages($ForumID, $TopicID=0, $Force=0) {
	global $db, $StoreRoot;
	$getForum = getforum($ForumID);
	$date = (int)$getForum->PurgeDays ? addDate(now(),-$getForum->PurgeDays) : addDate(now(),-30);
	$getMessages = $db->get_results("SELECT ID, ForumID, TopicID, AttachedFile, Locked FROM frmMessages WHERE 1=1
		". ($IsApproved ? " AND IsApproved=1" : "") ."
		". ($ForumID ? " AND ForumID=". (int)$ForumID : "") ."
		". ($TopicID ? " AND TopicID=". (int)$TopicID : "") ."
		". (!$Force ? " AND MessageDate<'". $date ."'" : "") );

	try {
		$db->query("START TRANSACTION");
		if ( count($getMessages) ) foreach ( $getMessages AS $msg ) {
			if ( !$msg->Locked ) {
				if ( $msg->AttachedFile!="" ) @unlink($StoreRoot .'/diskusije/datoteke/'. $msg->AttachedFile);
				$db->query("DELETE FROM frmMessages WHERE Locked=0 AND ID=". $msg->ID );
				updtopiccount($msg->TopicID);
			}
		}
		$db->query("COMMIT");
	} catch (Exception $e) {
		$db->query("ROLLBACK");
	}

	// update topic stats
	updtopiccount($TopicID);
}

// find messages
function findmessages($Find, $IsApproved=1, $Date="", $All=0) {
	global $db, $StoreRoot;
	return $db->get_results("SELECT M.*
		FROM frmMessages M
			LEFT JOIN frmTopics T ON T.ID=M.TopicID
			LEFT JOIN frmForums F ON F.ID=T.ForumID
			LEFT JOIN frmMembers U ON U.ID=M.MemberID
		WHERE 1=1
		". ($IsApproved ? "AND M.IsApproved=1 " : "") ."
		". (!$All ? "AND (F.Password IS NULL AND F.Hidden=0) " : "") ."
		". (isDate($Date) ? " AND M.MessageDate>='". $Date ."'" : "") ."
		". ($Find!="" ? " AND (". $Find .")" : "") ."
		ORDER BY M.MessageDate DESC
		LIMIT 500");
}

// Select maxDate from messages
function getmaxmsgdate($TopicID) {
	global $db, $StoreRoot;
	return $db->get_row("SELECT max(ID) AS LastMsg, max(MessageDate) AS MaxDate, count(*) AS MsgCount
		FROM frmMessages WHERE TopicID=". (int)$TopicID);
}

// select single messages
function getmessage($ID=0) {
	global $db, $StoreRoot;
	return $db->get_row("SELECT * FROM frmMessages WHERE ID=". (int)$ID);
}

// select messages
function getmessages($ForumID=0, $TopicID=0, $IsApproved=1, $Date="") {
	global $db, $StoreRoot;
	return $db->get_results("SELECT * FROM frmMessages WHERE
		IsApproved=". ($IsApproved ? "1" : "0") ."
		". ($ForumID ? " AND ForumID=". (int)$ForumID : "") ."
		". ($TopicID ? " AND TopicID=". (int)$TopicID : "") ."
		". (isDate($Date) ? " AND MessageDate>='". $Date ."'" : "") ."
		ORDER BY MessageDate");
}

// select messages from a single user
function getmessagesbyuser($MemberID=0, $Limit=10, $All=0) {
	global $db, $StoreRoot;
	return $db->get_results("SELECT * FROM frmMessages M
			LEFT JOIN frmTopics T ON T.ID=M.TopicID
			LEFT JOIN frmForums F ON F.ID=T.ForumID
		WHERE M.IsApproved=1
		  AND M.MemberID=". (int)$MemberID ."
		". (!$All ? "AND (F.Password IS NULL AND F.Hidden=0) " : "") ."
		ORDER BY M.MessageDate DESC". ($Limit ? " LIMIT ". $Limit : ""));
}

// get moderators for forum
function getmoderators($ForumID, $MemberID=0) {
	global $db, $StoreRoot;
	if ( $MemberID )
		return $db->get_row("SELECT M.ID, M.NickName, M.Name, M.DisplayName, M.ShowPersonalData, M.AccessLevel, MO.Permissions
			FROM frmModerators MO
				LEFT JOIN frmMembers M ON MO.MemberID = M.ID
			WHERE MemberID=". (int)$MemberID ."
			  AND ForumID=". (int)$ForumID );
	else
		return $db->get_results("SELECT M.ID, M.NickName, M.Name, M.DisplayName, M.ShowPersonalData, M.AccessLevel, MO.Permissions
			FROM frmModerators MO
				LEFT JOIN frmMembers M ON MO.MemberID = M.ID
			WHERE ForumID=". (int)$ForumID);
}

// select # of messages to approve
function getmsgtoapprove($ForumID, $TopicID=0) {
	global $db, $StoreRoot;
	return $db->get_var("SELECT count(*) AS MsgCount FROM frmMessages WHERE IsApproved=0
		". ($ForumID ? " AND ForumID=". (int)$ForumID : "") ."
		". ($TopicID ? " AND TopicID=". (int)$TopicID : ""));
}

// select active visitors
function getvisitors() {
	global $db, $StoreRoot;
	return $db->get_results("SELECT M.*, V.InChat
		FROM frmVisitors V
		LEFT JOIN frmMembers M ON M.ID=V.MemberID
		WHERE V.MemberID IS NOT NULL
		ORDER BY V.LastVisit DESC");
}

// approve a message
function updapprovemsg($ID, $UserID) {
	global $db, $StoreRoot;
	$db->query("UPDATE frmMessages SET IsApproved=1, ApprovedBy=". (int)$UserID ." WHERE ID=". (int)$ID);
	$getMessage = getmessage($ID);
	updtopiccount($getMessage->TopicID);
}

// ---------- members -----------
// Select a member
function getmember($ID=0) {
	global $db, $StoreRoot;
	return $db->get_row("SELECT * FROM frmMembers WHERE ID=". (int)$ID);
}
function getmemberbyemail($email) {
	global $db, $StoreRoot;
	return $db->get_row("SELECT * FROM frmMembers WHERE Email='". $db->escape($email) ."'");
}

// Select multiple members
function getmembers($Active=1, $Find="", $OrderBy="") {
	global $db, $StoreRoot;
	switch ( $OrderBy ) {
		case "Name"      : $Sort = "Name"; break;
		case "Nickname"  : $Sort = "Nickname"; break;
		case "Moderator" : $Sort = "AccessLevel DESC, Name"; break;
		case "ID"        : $Sort = "ID"; break;
		case "LastVisit" : $Sort = "LastVisit DESC"; break;
		case "Posts"     : $Sort = "Posts DESC"; break;
		default          : $Sort = ""; break;
	}
	return $db->get_results("SELECT * FROM frmMembers WHERE 1=1
		". ($Active ? " AND Enabled<>0 AND LastVisit IS NOT NULL" : "") ."
		". ($Find!="" ? " AND (Name LIKE '%". $db->escape($Find) ."%' OR NickName LIKE '%". $db->escape($Find) ."%')" : "") ."
		". ($Sort!="" ? " ORDER BY ". $Sort : ""));
}

// update members last visit date & IP address
function updmemberlastvisit($ID=0) {
	global $db, $StoreRoot;
	$db->query("START TRANSACTION");
	// update active visitors
	if ( $db->get_var("SELECT count(*) FROM frmVisitors WHERE SessionID='". session_id() ."'") == 0 ) {
		$db->query("INSERT INTO frmVisitors (SessionID,MemberID,LastVisit) VALUES ('". session_id() ."',". (int)$ID .",'". now() ."')");
	} else {
		$db->query("UPDATE frmVisitors SET MemberID=". (int)$ID .",LastVisit='". now() ."' WHERE SessionID='". session_id() ."'");
	}
	$db->query("UPDATE frmMembers SET LastVisit='". now() ."',LastIPAddress='". left($_SERVER['REMOTE_ADDR'], 15) ."' WHERE ID=". (int)$ID);
	$db->query("COMMIT");
}

// ---------- forums -----------
// select all categories
function getcategories() {
	global $db, $StoreRoot;
	return $db->get_results("SELECT * FROM frmCategories");
}

// select single forum
function getforum($ID) {
	global $db, $StoreRoot;
	return $db->get_row("SELECT C.CategoryName, C.Administrator AS CatAdmin, F.*
		FROM frmForums F, frmCategories C
		WHERE C.ID = F.CategoryID AND F.ID=". (int)$ID);
}

// select forums grouped by Category
function getforums($Category=0, $ShowAll=0, $OrderBy="") {
	global $db, $StoreRoot;
	switch ( $OrderBy ) {
		case "Name"      : $order = ",F.ForumName"; break;
		case "Moderator" : $order = ",F.Moderator"; break;
		case "ID"        : $order = ",F.ID"; break;
		case "Order"     : $order = ",F.ForumOrder"; break;
		default          : $order = ",F.ForumOrder"; break;
	}
	return $db->get_results("SELECT C.CategoryName, C.Administrator AS CatAdmin, F.*
		FROM frmForums F, frmCategories C
		WHERE C.ID = F.CategoryID
		". ($Category ? " AND F.CategoryID=". (int)$Category : "") ."
		". (!$ShowAll ? " AND F.Hidden=0" : "") ."
		ORDER BY C.CategoryOrder". $order);
}

// get maximum/last forum date
function getmaxforumdate() {
	global $db, $StoreRoot;
	return $db->get_var("SELECT max(LastMessageDate) AS MaxDate FROM frmForums");
}

// ----------- topics -------------
// add a topic
function addtopic($ForumID, $TopicName, $UserID=0) {
	global $db, $StoreRoot;
	$TopicName = preg_replace("/<[^>]*>/", "", $TopicName);
	$TopicName = str_replace(chr(38), "&amp;", $TopicName);
	$TopicName = str_replace(chr(34), "&quot;", $TopicName);
	$TopicName = str_replace(chr(60), "&lt;", $TopicName);
	$TopicName = str_replace(chr(62), "&gt;", $TopicName);

	if ( !($id=(int)$db->get_var("SELECT ID FROM frmTopics WHERE ForumID=". (int)$ForumID ." AND TopicName='". $db->escape($TopicName) ."'")) ) {
		$db->query("INSERT INTO frmTopics (ForumID, TopicName, MessageCount, StartedBy)
			VALUES (". (int)$ForumID .",'". $db->escape($TopicName) ."',0,". ($UserID ? (int)$UserID : "NULL") .")");
		$id = $db->insert_id;
	}
	return $id;
}

// delete a topic
function deltopic($ID) {
	global $db, $StoreRoot;
	try {
		$db->query("START TRANSACTION");
		$db->query("UPDATE frmPvtMessages SET TopicID=NULL WHERE TopicID=". (int)$ID);
		$db->query("DELETE FROM frmPollVotes WHERE TopicID=". (int)$ID);
		$db->query("DELETE FROM frmPoll WHERE TopicID=". (int)$ID);
		$db->query("DELETE FROM frmNotify WHERE TopicID=". (int)$ID);
		$db->query("DELETE FROM frmTopics WHERE ID=". (int)$ID);
		$db->query("COMMIT");
	} catch (Exception $e) {
		$db->query("ROLLBACK");
	}
}

// get last post
function getlastpost($ForumID=0) {
	global $db, $StoreRoot;
	return $db->get_row("SELECT ForumID, ID AS TopicID, LastMessageDate, LastPostBy
		FROM frmTopics WHERE 1=1
		". ($ForumID ? " AND ForumID=". (int)$ForumID : "") ."
		ORDER BY LastMessageDate DESC
		LIMIT 1");
}

// get message count
function getmsgcount($ForumID=0) {
	global $db, $StoreRoot;
	return $db->get_var("SELECT sum(MessageCount) AS MsgCount FROM frmTopics ". ($ForumID ? " WHERE ForumID=". (int)$ForumID : ""));
}

//Select a topic
function gettopic($ID) {
	global $db, $StoreRoot;
	return $db->get_row("SELECT T.*, P.Votes
		FROM frmTopics T
			LEFT JOIN frmPoll P ON T.ID = P.TopicID
		WHERE ID=". (int)$ID);
}

//Select topics from a forum sorted by ...
function gettopics($ForumID=0, $TopicName="", $Date="", $Sort="Date") {
	global $db, $StoreRoot;
	switch ( $Sort ) {
		case "Count"    : $order = "MessageCount DESC"; break;
		case "Date"     : $order = "LastMessageDate DESC"; break;
		case "Member"   : $order = "StartedBy"; break;
		case "Name"     : $order = "TopicName"; break;
		default         : $order = "LastMessageDate DESC"; break;
	}
	return $db->get_results("SELECT T.*, P.Votes
		FROM frmTopics T
			LEFT JOIN frmPoll P ON T.ID = P.TopicID
		WHERE 1=1
		". ($ForumID ? " AND ForumID=". (int)$ForumID : "") ."
		". ($TopicName!="" ? " AND TopicName='". $TopicName ."'" : "") ."
		". ($Date!="" ? " AND LastMessageDate>='". $Date ."'" : "") ."
		ORDER BY Sticky DESC, ". $order);
}

// rename a topic
function rentopic($TopicID, $TopicName="--prazno--") {
	global $db, $StoreRoot;
	$TopicName = preg_replace("/<[^>]*>/", "", $TopicName);
	$TopicName = str_replace(chr(38), "&amp;", $TopicName);
	$TopicName = str_replace(chr(34), "&quot;", $TopicName);
	$TopicName = str_replace(chr(60), "&lt;", $TopicName);
	$TopicName = str_replace(chr(62), "&gt;", $TopicName);

	$db->query("UPDATE frmTopics SET TopicName='". $db->escape($TopicName) ."' WHERE ID=". (int)$TopicID);
}

// update topic stats
function updtopiccount($TopicID) {
	global $db, $StoreRoot;
	$getMaxMsgDate = getmaxmsgdate($TopicID);
	if ( isDate($getMaxMsgDate->MaxDate) ) {
		$LastPostBy = (int)$db->get_var("SELECT MemberID FROM frmMessages WHERE ID=". (int)$getMaxMsgDate->LastMsg);
		$db->query("UPDATE frmTopics
			SET MessageCount=". (int)$getMaxMsgDate->MsgCount .",
				LastMessageDate='". $getMaxMsgDate->MaxDate ."',
				LastPostBy=". ($LastPostBy ? $LastPostBy : "NULL") ."
			WHERE ID=". (int)$TopicID);
	} else
		deltopic($TopicID);
	return $getMaxMsgDate->MsgCount;
}

// ---------- topic polls ----------
// Select topic poll
function getpoll($TopicID, $UserID=0) {
	global $db, $StoreRoot;
	return $db->get_row("SELECT P.*, PV.VoteDate
		FROM frmPoll P
			LEFT JOIN frmPollVotes PV ON P.TopicID = PV.TopicID AND PV.MemberID=". (int)$UserID ."
		WHERE P.TopicID=". (int)$TopicID);
}

// update topic poll
function updpoll($TopicID, $UserID, $Vote) {
	global $db, $StoreRoot;
	$db->query("START TRANSACTION");
	$db->query("UPDATE frmPoll SET Votes=Votes+1,R". (int)$Vote ."=R". (int)$Vote ."+1 WHERE TopicID = ". (int)$TopicID ." AND Locked = 0");
	$db->query("INSERT frmPollVotes (TopicID,MemberID,Answer,VoteDate)
		VALUES (
			". (int)$TopicID .",
			". (int)$UserID .",
			". (int)$Vote .",
			'". now() ."'
		)");
	$db->query("COMMIT");
}

// ------------- notifications -------------
// add notification flag 
function addnotify($TopicID, $UserID) {
	global $db, $StoreRoot;
	if ( !$db->get_var("SELECT ID FROM frmNotify WHERE TopicID=". (int)$TopicID ." AND MemberID=". (int)$UserID) )
		$db->query("INSERT INTO frmNotify (TopicID, MemberID) VALUES (". (int)$TopicID .", ". (int)$UserID .")");
}

// delete a topic subscription
function delnotify($ID, $MemberID=0) {
	global $db, $StoreRoot;
	$db->query("DELETE FROM frmNotify WHERE ID=". (int)$ID . ($MemberID ? " AND MemberID=". (int)$MemberID : ""));
}

// get all notifys for a selected user/topic
function getmembernotifys($MemberID=0) {
	global $db, $StoreRoot;
	return $db->get_results("SELECT N.ID,T.ID AS TopicID, T.TopicName
		FROM frmNotify N, frmTopics T
		WHERE N.MemberID=". (int)$MemberID ."
		AND T.ID=N.TopicID");
}
function gettopicnotifys($TopicID=0) {
	global $db, $StoreRoot;
	return $db->get_results("SELECT N.ID, M.ID AS MemberID, M.Name, M.Nickname, M.Email
		FROM frmNotify N, frmMembers M
		WHERE N.TopicID=". (int)$TopicID ."
		AND M.ID=N.MemberID
		AND M.Enabled<>0");
}

// ------------- private messages -------------
// delete private message
function delpvtmessage($UserID, $ID=0) {
	global $db, $StoreRoot;
	$db->query("DELETE FROM frmPvtMessages WHERE ID=" .(int)$ID . " AND ToID=". (int)$UserID);
}

// purge deleted private messages
function delpvtmessages($UserID=0) {
	global $db, $StoreRoot;
	if ( (int)$UserID ) {
		// purge deleted messages
		$db->query("DELETE FROM frmPvtMessages WHERE ToID=". (int)$UserID ." AND IsDeleted<>0");
	} else {
		// purge old messages
		$db->query("DELETE FROM frmPvtMessages WHERE IsDeleted<>0 AND MessageDate<'" .addDate(now(),0,-3) ."'");
	}
}

// Select single private message
function getpvtmessage($ToID, $ID) {
	global $db, $StoreRoot;
	return $db->get_row("SELECT PM.*, M.NickName AS FromNickName, T.TopicName, T.ForumID
		FROM frmPvtMessages PM
			LEFT JOIN frmMembers M ON PM.FromID = M.ID
			LEFT JOIN frmTopics T ON PM.TopicID = T.ID
		WHERE PM.ID=". (int)$ID ." AND PM.ToID=". (int)$ToID);
}

// Select private messages
function getpvtmessages($ToID, $Sort="Date", $SortDir="", $FromID=0, $TopicID=0, $IsRead=null, $IsDeleted=null) {
	global $db, $StoreRoot;
	switch ( $Sort ) {
		case "From"      : $order = "M.NickName"; break;
		case "Topic"     : $order = "T.TopicName"; break;
		case "Subj"      : $order = "PM.MessageSubject"; break;
		case "Date"      : $order = "PM.MessageDate"; break;
		default          : $order = "PM.MessageDate"; break;
	}
	switch ( $SortDir ) {
		case "Up"   : $SortDir = "ASC"; break;
		case "Down" : $SortDir = "DESC"; break;
		default     : $SortDir = ""; break;
	}
	if ( $SortDir!="" ) $order .= " ". $SortDir;
	
	return $db->get_results("SELECT PM.*, M.NickName AS FromNickName, T.TopicName, T.ForumID
		FROM frmPvtMessages PM
			LEFT JOIN frmMembers M ON PM.FromID = M.ID
			LEFT JOIN frmTopics T ON PM.TopicID = T.ID
		WHERE PM.ToID=". (int)$ToID ."
			". ($FromID ? " AND PM.FromID=". (int)$FromID : "") ."
			". ($TopicID ? " AND PM.TopicID=". (int)$TopicID : "") ."
			". (isset($IsRead) ? " AND PM.IsRead=". (int)$IsRead : "") ."
			". (isset($IsDeleted) ? " AND PM.IsDeleted=". (int)$IsDeleted : "") ."
		ORDER BY ". $order );
}

// get unread message count
function getpvtunread($UserID) {
	global $db, $StoreRoot;
	return $db->get_var("SELECT count(*) FROM frmPvtMessages WHERE IsRead=0 AND ToID=". (int)$UserID);
}

// update private message
function updpvtmessages($UserID, $ID, $action="") {
	global $db, $StoreRoot;
	switch ( $action ) {
		case "undelete" : $action = ",IsDeleted=0"; break;
		case "delete"   : $action = ",IsDeleted=1"; break;
		case "reply"    : $action = ",IsReply=1"; break;
		default         : $action = ""; break;
	}
	$db->query("UPDATE frmPvtMessages SET IsRead=1". $action ." WHERE ToID=". (int)$UserID ." AND ID=". (int)$ID);
}
?>