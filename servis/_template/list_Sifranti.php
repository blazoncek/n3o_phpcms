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
if ( !isset( $_GET['Tip'] ) )  $_GET['Tip'] = "";

// get all
if ( $_GET['Find'] != "" )
	$List = $db->get_results(
		"SELECT S.SifrantID AS ID, S.SifrText AS Name, S.SifrCtrl AS Tip, S.ACLID
		FROM Sifranti S
			LEFT JOIN SifrantiTxt ST ON S.SifrantID = ST.SifrantID
		WHERE (S.SifrText LIKE '%".$_GET['Find']."%'
				OR ST.SifNaziv LIKE '%".$_GET['Find']."%'
				OR ST.SifCVal1 LIKE '%".$_GET['Find']."%'
				OR ST.SifCVal2 LIKE '%".$_GET['Find']."%'
				OR ST.SifCVal3 LIKE '%".$_GET['Find']."%'
			)
		ORDER BY SifrText" );
else
	$List = $db->get_results(
		"SELECT DISTINCT SifrCtrl AS Name, ACLID
		FROM Sifranti
		ORDER BY SifrCtrl" );

if ( count( $List ) == 0 ) {
	echo "<div class=\"frame\" style=\"display: table;height: 100px;width: 100%;\">";
	echo "<div style=\"background-color: white;display: table-cell;text-align: center;vertical-align: middle;\"><b>Ni podatkov!</b></div>\n";
	echo "</div>\n";
} else {
	echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"frame\">\n";
	$BgCol = "white";
	if ( $_GET['Find'] != "" ) {
		foreach( $List as $Item ) {
			$ACL = userACL( $Item->ACLID );
			if ( contains($ACL,"L") ) {
				if ( $BgCol == "white" )
					$BgCol="#edf3fe";
				else
					$BgCol = "white";
				echo "<tr bgcolor=\"$BgCol\">\n";
				if ( contains($ACL,"W") )
					echo "<td><img src=\"pic/trans.gif\" height=\"14\" width=\"18\" border=\"0\"><a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action']."&ID=$Item->ID');\"><b>".left($Item->Name,30).(strlen($Item->Name)>30?"...":"")."</b></a></td>\n";
				else
					echo "<td><b>".left($Item->Name,30).(strlen($Item->Name)>30?"...":"")."</b></td>\n";
				echo "<td align=\"right\" valign=\"top\" width=\"35\">";
				if ( contains($ACL,"D") )
					echo "<a href=\"javascript:void(0);\" onclick=\"javascript:check('$Item->ID','$Item->Name');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Briši\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
				else
					echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
				echo "</td>\n";
				echo "</tr>\n";
			}
		}
	} else {
		foreach( $List as $Item ) {
			$ACL = userACL( $Item->ACLID );
			if ( contains($ACL,"L") ) {
				if ( $BgCol == "white" )
					$BgCol="#edf3fe";
				else
					$BgCol = "white";
				echo "<tr bgcolor=\"$BgCol\">\n";
				echo "<td>";
				echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action'].(($Item->Name!=$_GET['Tip'])? "&Tip=".$Item->Name: "")."');\">";
				echo "<img src=\"pic/list.".(( $Item->Name == $_GET['Tip'] )? "open": "closed").".gif\" height=\"11\" width=\"11\" border=\"0\" align=\"absmiddle\" class=\"icon\">&nbsp;";
				echo "<b>".left($Item->Name,30).(strlen($Item->Name)>30?"...":"")."</b></a>";
				echo "</td>\n";
				echo "<td align=\"right\" class=\"novo\" width=\"35\">&nbsp;";
				//if ( $Item->Name == $_GET['Tip'] )
				//	echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action']."&Tip=".$Item->Name."&ID=0');\">Novo...</a>";
				echo "</td>\n";
				echo "</tr>\n";
				if ( $Item->Name == $_GET['Tip'] ) {
					$ListDetail = $db->get_results(
						"SELECT SifrantID AS ID, SifrText AS Name, SifrZapo AS Polozaj, SifrCtrl AS Tip
						FROM Sifranti
						WHERE SifrCtrl='".$_GET['Tip']."'
						ORDER BY SifrCtrl, SifrZapo"
					);
					$CurrentRow = 1;
					$RecordCount = count( $ListDetail );
					foreach( $ListDetail as $Item ) {
						if ( $BgCol == "white" )
							$BgCol="#edf3fe";
						else
							$BgCol = "white";
						echo "<tr bgcolor=\"$BgCol\">\n";
						echo "<td><img src=\"pic/trans.gif\" height=\"11\" width=\"11\" border=\"0\" align=\"absmiddle\" class=\"icon\">&nbsp;";
						if ( contains($ACL,"R") ) {
							echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action']."&ID=$Item->ID');\">";
							echo "<b>".left($Item->Name,30).(strlen($Item->Name)>30?"...":"")."</b></a>";
						} else
							echo "<td><b>".left($Item->Name,30).(strlen($Item->Name)>30?"...":"")."</b>";
						echo "</td>\n";
						echo "<td align=\"right\" valign=\"top\" width=\"20\">";
						// move items up/down
						if ( contains($ACL,"W") ) {
							if ( $CurrentRow > 1 )
								echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Izbor=".$_GET['Izbor'].
									"&Action=".$_GET['Action']."&Tip=$Item->Tip&Item=$Item->ID&Smer=-1');\">".
									"<img src=\"pic/list.up.gif\" height=11 width=11 border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
							else
								echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
							if ( $CurrentRow < $RecordCount )
								echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Izbor=".$_GET['Izbor'].
									"&Action=".$_GET['Action']."&Tip=$Item->Tip&Item=$Item->ID&Smer=1');\">".
									"<img src=\"pic/list.down.gif\" height=11 width=11 border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
							else 
								echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
						}
						// delete
						if ( contains($ACL,"D") )
							echo "<a href=\"javascript:void(0);\" onclick=\"javascript:check('$Item->ID','$Item->Name');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Briši\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
						else
							echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
						echo "</td>\n";
						echo "</tr>\n";
						$CurrentRow++;
					}
				}
			}
		}
	}
	echo "</table>\n";
}
?>