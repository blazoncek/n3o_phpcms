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

if ( isset($_GET['delNotify']) )
	$db->query("DELETE FROM frmNotify WHERE ID = ".(int)$_GET['delNotify']);

$getNotifys = $db->get_results(
	"SELECT N.ID, T.ID AS TopicID, T.TopicName
	FROM frmNotify N
		INNER JOIN frmTopics T ON T.ID = N.TopicID
	WHERE N.MemberID = ".(int)$_GET['ID']
);

echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
if ( $getNotifys ) foreach ( $getNotifys as $Item ) {
	echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='white';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
	echo "<TD>&nbsp;$Item->TopicName</TD>\n";
	echo "<TD ALIGN=\"right\">";
	echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divTeme').load('inc.php?Izbor=frmNotify&ID=".$_GET['ID']."&delNotify=$Item->ID');\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" ALIGN=\"absmiddle\" CLASS=\"icon\"></A>";
	echo "</TD>\n";
	echo "</TR>\n";
} else
	echo "<TR><TD ALIGN=\"center\" COLSPAN=\"3\">No subscriptions!</TD></TR>\n";
echo "</TABLE>\n";
?>
