<?php
/*~ inc_frmMessages.php - Approve/delete forum messages
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

if ( isset($_GET['DelMessage']) ) {
	$db->query( "START TRANSACTION" );
	$AttachedFile = $db->get_var( "SELECT AttachedFile FROM frmMessages WHERE ID = ".(int)$_GET['DelMessage'] );
	$TopicID = $db->get_var( "SELECT TopicID FROM frmMessages WHERE ID = ".(int)$_GET['DelMessage'] );
	if ( $AttachedFile )
		@unlink( $StoreRoot . '/diskusije/datoteke/' . $AttachedFile );

	$db->query( "DELETE FROM frmMessages WHERE ID = ".(int)$_GET['DelMessage'] );
	$db->query( "UPDATE frmTopics SET MessageCount = MessageCount - 1 WHERE ID = ".(int)$TopicID );
	$db->query( "COMMIT" );
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&DelMessage=[0-9]+/", "", $_SERVER['QUERY_STRING'] );
}

if ( isset($_GET['Approve']) ) {
	$db->query( "UPDATE frmMessages SET IsApproved=1, ApprovedBy=1 WHERE ID = ".(int)$_GET['Approve'] );
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&Approve=[0-9]+/", "", $_SERVER['QUERY_STRING'] );
}

$db->query( "START TRANSACTION" );
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
$db->query( "COMMIT" );

if ( isset($_GET['Check']) ) {

	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( "/\&Check=[0-9]+/i", "", $_SERVER['QUERY_STRING'] );

	$IsApproved = $db->get_var( "SELECT IsApproved FROM frmMessages WHERE ID = ".(int)$_GET['Check'] );
	$Msg = $db->get_var( "SELECT MessageBody FROM frmMessages WHERE ID = ".(int)$_GET['Check'] );
	$Msg = str_replace("<BR>", "\n", $Msg);
	$Msg = preg_replace("/<([^>]*)>/i", "", $Msg); // remove all tags
	$Msg = left($Msg,127);

	echo "<div data-role=\"page\">\n";

	echo "<div data-role=\"header\" data-theme=\"a\">\n";
	echo "<h1>Sporočilo</h1>\n";
	echo "</div>\n";

	echo "<div data-role=\"content\">\n";
	//echo "<h4>Odobri sporočilo?</h4>\n";
	echo "<p>". $Msg ."</p>\n";
	if ( !$IsApproved )
		echo "<a href=\"". $_SERVER['PHP_SELF'] ."?". $_SERVER['QUERY_STRING'] ."&Approve=". (int)$_GET['Check'] ."\" data-role=\"button\" data-ajax=\"false\" data-theme=\"b\">Odobri</a>\n";
	echo "<a href=\"". $_SERVER['PHP_SELF'] ."?". $_SERVER['QUERY_STRING'] ."&DelMessage=". (int)$_GET['Check'] ."\" data-role=\"button\" data-ajax=\"false\" data-theme=\"e\">Briši</a>\n";
	echo "</div>\n";

	echo "</div>\n";

} else {

	echo "<div id=\"messages\" data-role=\"page\">\n";
	
	echo "<div data-role=\"header\" data-theme=\"b\">\n";
	echo "<h1>Sporočila</h1>\n";
	echo "<a href=\"edit.php?Action=". $_GET['Action'] ."&ID=". $_GET['ID'] ."\" data-ajax=\"false\" data-role=\"button\" data-iconpos=\"left\" data-icon=\"arrow-l\">Nazaj</a>\n";
	echo "<a href=\"./\" title=\"Domov\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Domov</a>\n";
	echo "</div>\n";

	echo "\t<div data-role=\"content\">\n";

	// izpis zadnjih nekaj sporočil
	$List = $db->get_results( "SELECT * FROM frmMessages WHERE TopicID = ".(int)$_GET['TopicID'] );
	if ( $List ) {
		// display results
		echo "<ul data-role=\"listview\" data-filter-test=\"true\" data-theme=\"d\" data-count-theme=\"e\" data-divider-theme=\"d\" data-split-icon=\"delete\" data-split-theme=\"e\">\n";
		foreach ( $List as $Item ) {
			if ( preg_match("/<P[^>]*>|<DIV/i",left($Item->MessageBody,100)) ) {
				$Bes = preg_replace("/<P([^>]*)>/i", "<DIV\1>", $Item->MessageBody);
				$Bes = str_replace("</P>","</DIV>",$Bes);
				$Bes = str_replace("<BR>", "\n", $Bes);
			} else {
				$Bes = str_replace("<BR>", "\n", $Item->MessageBody);
			}
			$Bes = preg_replace("/<([^>]*)>/i", "", $Bes); // remove all tags

			echo "<li>";
			if ( $Item->Locked )
				echo "<img src=\"pic/icon.lock.png\" alt=\"Zaklenjeno\" class=\"ui-li-icon\">";
			else 
				echo "<a href=\"". $_SERVER['PHP_SELF'] ."?". $_SERVER['QUERY_STRING'] ."&Check=". $Item->ID ."\" data-rel=\"dialog\" data-transition=\"slideup\">";
			echo "<h3>". $Item->UserName ."</h3>";
			echo "<p>". $Bes ."</p>";

			echo "<p class=\"ui-li-aside\">";
			echo date( "j.n.Y \@ H:i", sqldate2time($Item->MessageDate));
			echo "</p>";

			if ( !$Item->IsApproved ) {
				echo "<span class=\"ui-li-count\">";
				echo "!";
				echo "</span>";
			}
			if ( !$Item->Locked )
				echo "</a>";
			echo "</li>";
		} 
		echo "</ul>\n";
	} else {
		echo "<div class=\"ui-body ui-body-d ui-corner-all\" style=\"color:red;padding:1em;text-align:center;\">\n";
		echo "<B>Ni podatkov!</B>\n";
		echo "</div>\n";
	}

	echo "</div>\n";
}
?>
