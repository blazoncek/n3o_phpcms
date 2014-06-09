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

// define default values for URL ID and Find parameters (in case not defined)
if ( !isset( $_GET['ID'] ) )   $_GET['ID'] = "0";
if ( !isset( $_GET['Find'] ) ) $_GET['Find'] = "";

// get categories
$List = $db->get_results( "SELECT ID, CategoryName FROM frmCategories ORDER BY CategoryOrder" );

// display results
if ( count( $List ) == 0 ) {
	echo "<div class=\"frame\" style=\"display: table;height: 100px;width: 100%;\">";
	echo "<div style=\"background-color: white;display: table-cell;text-align: center;vertical-align: middle;\"><b>No data!</b></div>\n";
	echo "</div>\n";
} else {
	echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"frame\">\n";
	foreach ( $List as $Item ) {
		echo "<TR BGCOLOR=\"DimGray\">\n";
		echo "<TD ALIGN=\"center\" COLSPAN=\"3\"><FONT COLOR=\"White\"><B>$Item->CategoryName</B></FONT></TD>\n";
		echo "</TR>\n";
		
		$Forums = $db->get_results(
			" SELECT ID, ForumName, NotifyModerator, ApprovalRequired, AllowFileUploads, ViewOnly, Hidden, PollEnabled, Private,".
			"	(SELECT count(*) FROM frmTopics WHERE ForumID = f.ID) AS MaxTopics".
			" FROM frmForums f".
			" WHERE CategoryID = " . (int)$Item->ID .
			" ORDER BY ForumOrder, ForumName" );

		$BgCol = "white";
		$CurrentRow = 1;
		$RecordCount = count( $Forums );
		if ( $Forums ) foreach ( $Forums as $Forum ) {
			// row background color
			if ( $BgCol == "white" )
				$BgCol="#edf3fe";
			else
				$BgCol = "white";
			echo "<tr bgcolor=\"$BgCol\">\n";
			echo "<td><a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor'].
				"&Action=".$_GET['Action']."&ID=$Forum->ID');\">".
				left($Forum->ForumName,30).(strlen($Forum->ForumName)>30?"...":"").
				"</a> (". $Forum->MaxTopics .")</td>\n";
			echo "<td align=\"center\" class=\"red\">";
			echo ($Forum->NotifyModerator)?  "<FONT COLOR=\"red\">N</FONT>": "&nbsp;";
			echo ($Forum->ApprovalRequired)? "<FONT COLOR=\"orange\">O</FONT>": "&nbsp;";
			echo ($Forum->AllowFileUploads)? "<FONT COLOR=\"Navy\">U</FONT>": "&nbsp;";
			echo ($Forum->ViewOnly)?         "<FONT COLOR=\"DeepSkyBlue\">V</FONT>": "&nbsp;";
			echo ($Forum->Hidden)?           "<FONT COLOR=\"DarkGray\">H</FONT>": "&nbsp;";
			echo ($Forum->PollEnabled)?      "<FONT COLOR=\"MediumTurquoise\">P</FONT>": "&nbsp;";
			echo ($Forum->Private)?          "<FONT COLOR=\"IndianRed\">Z</FONT>": "&nbsp;";
			echo "</td>\n";
			echo "<td align=\"right\" valign=\"top\" width=\"36\">";
			// move items up/down
			if ( $CurrentRow > 1 )
				echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Izbor=".$_GET['Izbor'].
					"&Action=".$_GET['Action']."&ID=$Forum->ID&Smer=-1');\">".
					"<img src=\"pic/list.up.gif\" height=11 width=11 border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
			else
				echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
			if ( $CurrentRow < $RecordCount )
				echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Izbor=".$_GET['Izbor'].
					"&Action=".$_GET['Action']."&ID=$Forum->ID&Smer=1');\">".
					"<img src=\"pic/list.down.gif\" height=11 width=11 border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
			else 
				echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
			// delete
			if ( contains($ActionACL,"D") )
				echo "<a href=\"javascript:void(0);\" onclick=\"javascript:check('$Forum->ID','$Forum->ForumName');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Brši\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
			else
				echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
			echo "</td>\n";
			echo "</tr>\n";
			$CurrentRow++;
		}
		else {
			echo "<TR BGCOLOR=\"white\">\n";
			echo "<TD ALIGN=\"center\" COLSPAN=\"3\">Ne vsebuje niti...</TD>\n";
			echo "</TR>\n";
		}
	}
	echo "</table>\n";
}
?>