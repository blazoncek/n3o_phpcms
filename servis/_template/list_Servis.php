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

/**************************************
* loop( $menu, $bgcol )
*--------------------------------------
* Recursively loop through categories in SMActions table.
*   $Menu - start level
*   $BgCol - by ref: background color of table (for recursive call)
**************************************/
function loop($Menu="", &$BgCol="white")
{
	global $db;

	$Kat = $db->get_results(
		"SELECT ActionID AS ID, Name, Enabled, ACLID
		FROM SMActions
		WHERE ActionID LIKE '".$Menu."__'
		ORDER BY ActionID" );
	
	if ( $Menu == "" && count( $Kat ) == 0 ) {
		echo "<tr><td align=\"center\" valign=\"middle\" height=\"100\"><b>No data!</b></td></tr>\n";
	} else {
		$CurrentRow = 1;
		$RecordCount = count( $Kat );
		if ( $Kat ) foreach ( $Kat as $K ) {
			$ACL = userACL( $K->ACLID );
			// List access
			if ( contains($ACL,"L") ) {
				if ( $BgCol == "white" )
					$BgCol = "#edf3fe";
				else
					$BgCol = "white";
				echo "<tr bgcolor=\"$BgCol\">\n"; //<! onmouseover="this.style.backgroundColor='whitesmoke';" onmouseout="this.style.backgroundColor='';">
				echo "<td valign=\"bottom\">";
				echo str_repeat( "&nbsp;", strlen($K->ID)-2 );
				if ( strlen($K->ID) < 10 && $K->Name != "" ) {
					echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Izbor=".$_GET['Izbor'].
						"&Action=".$_GET['Action'].
						"&ID=".((left($_GET['ID'],strlen($K->ID)) == $K->ID)? left($K->ID,strlen($K->ID)-2): $K->ID)."');\">";
					echo "<img src=\"pic/list.".((left($_GET['ID'],strlen($K->ID))==$K->ID)? "open": "closed").".gif\" height=\"11\" width=\"11\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a>&nbsp;";
				} else
					echo "<img src=\"pic/trans.gif\" height=\"11\" width=\"11\" border=\"0\" align=\"absmiddle\" class=\"icon\">";
				// Read access
				if ( contains($ACL,"R") )
					echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action']."&ID=$K->ID');\">";
				// display category name
				if ( $K->Name == "" )
					echo "---separator---";
				else
					echo left($K->Name,25).((strlen($K->Name)>25)?"...":"");
				// Read access
				if ( contains($ACL,"R") )
					echo "</a>";
				// mark disabled items
				if ( !$K->Enabled )
					echo "*";
				echo "</td>\n";
				echo "<td align=\"right\" class=\"novo\">";
				if ( contains($ACL,"W") && $K->ID == $_GET['ID'] && strlen( $K->ID ) < 10 ) {
					$N = $db->get_var(
						"SELECT MAX(ActionID)
						FROM SMActions
						WHERE ActionID LIKE '".$K->ID."__'
						ORDER BY ActionID" );
					if ( $N == "" )
						$M = $K->ID . "01";
					else
						$M = sprintf("%0".strlen($N)."d", (int)$N + 1);
					echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action']."&ID=$M');\">";
					echo "Nov podmenu...</a>";
				} else {
					// move up/down
					if ( strlen($_GET['ID'])+2 == strlen($K->ID) ) {
						if ( $CurrentRow > 1 )
							echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Izbor=".$_GET['Izbor'].
								"&Action=".$_GET['Action']."&ID=$K->ID&Smer=-1');\">".
								"<img src=\"pic/list.up.gif\" height=11 width=11 border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
						else
							echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
						if ( $CurrentRow < $RecordCount )
							echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Izbor=".$_GET['Izbor'].
								"&Action=".$_GET['Action']."&ID=$K->ID&Smer=1');\">".
								"<img src=\"pic/list.down.gif\" height=11 width=11 border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
						else 
							echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
					}
					// Delete access
					if ( contains($ACL,"D") && $K->ID != "00" )
						echo "<a href=\"javascript:void(0);\" onclick=\"javascript:check('$K->ID','$K->Name');\">".
							"<img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Briši\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
					else
						echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
				}
				echo "</td>\n";
				echo "</tr>\n";
				$CurrentRow++;
			}
			// recursively loop
			if ( $_GET['ID'] != "" && left($_GET['ID'], strlen($K->ID)) == $K->ID )
				loop( $K->ID, $BgCol );
		}
	}
}

$BgCol = "white";
if ( $_GET['Find'] != "" ) {
	// search
	$List = $db->get_results(
		"SELECT ActionID AS ID, Name, Enabled, ACLID
		FROM  SMActions
		WHERE Name LIKE '%".trim($_GET['Find'])."%'
		ORDER BY ActionID" );
	
	if ( count( $List ) == 0 ) {
		echo "<div class=\"frame\" style=\"background-color:white;height:100px;text-align:center;vertical-align:middle;\"><b>No data!</b></div>\n";
	} else {
		echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"frame\">\n";
		foreach( $List as $L ) {
			$ACL = userACL( $L->ACLID);
			if ( contains($ACL,"L") ) {
				if ( $BgCol == "white" )
					$BgCol = "#edf3fe";
				else
					$BgCol = "white";
				echo "<tr bgcolor=\"$BgCol\">\n";
				echo "<td>";
				if ( contains($ACL,"R") )
					echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action']."&ID=$L->ID');>".
						(($L->Name=="")? "---separator---": left($L->Name,25).((strlen($L->Name>25)? "...": "")))."</a>";
				// mark disabled items
				if ( !$K->Enabled )
					echo "*";
				echo "</td>\n";
				echo "<td align=\"right\">\n";
				if ( contains($ACL,"D") && left( $L->ID, 2 ) != "00" )
					echo "<a href=\"javascript:void(0);\" onclick=\"javascript:check('$L->ID','$L->Name');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Briši\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a>\n";
				else
					echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">\n";
				echo "</td>\n";
				echo "</tr>\n";
			}
		}
		echo "</table>\n";
	}
} else {
	// hierarchical view
	echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"frame\">\n";
	loop( "", $BgCol );
	echo "</table>\n";
}
?>