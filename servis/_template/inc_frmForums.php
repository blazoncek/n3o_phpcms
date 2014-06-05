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

if ( isset($_GET['delForum']) ) {
	$Moderator = $db->get_var( "SELECT Moderator FROM frmForums WHERE ID = ".(int)$_GET['delForum'] );
	if ( $Moderator && $Moderator != (int)$_GET['ID'] )
		$db->query( 
			"DELETE FROM frmModerators
			WHERE
				ForumID = ".(int)$_GET['delForum']." AND
				MemberID = ".(int)$_GET['ID']
		);
}

$getForums = $db->get_results(
	"SELECT
		M.ForumID AS ID,
		F.ForumName
	FROM
		frmModerators M,
		frmForums F
	WHERE
		F.ID = M.ForumID AND M.MemberID = ".(int)$_GET['ID']
);

echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
if ( $getForums ) foreach ( $getForums as $Item ) {
	echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='white';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
	echo "<TD><A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=frmForums&ID=$Item->ID');\">$Item->ForumName</A></TD>\n";
	echo "<TD ALIGN=\"right\">";
	echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divNiti').load('inc.php?Izbor=frmForums&ID=".$_GET['ID']."&delForum=$Item->ID');\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Bri۩\" BORDER=\"0\" ALIGN=\"absmiddle\" CLASS=\"icon\"></A>";
	echo "</TD>\n";
	echo "</TR>\n";
} else
	echo "<TR><TD ALIGN=\"center\" COLSPAN=\"3\">Ni moderator nobene niti!</TD></TR>\n";
echo "</TABLE>\n";
?>
