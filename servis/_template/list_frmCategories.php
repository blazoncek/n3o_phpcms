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

// get all
$List = $db->get_results( "SELECT ID, CategoryName AS Name FROM frmCategories"
	.($_GET['Find'] == "" ? " " : " WHERE CategoryName LIKE '%".$_GET['Find']."%'" ).
	"ORDER BY CategoryOrder"
);

$RecordCount = count( $List );

// override maximum number of rows to display
if ( isset($_COOKIE['listmax']) ) $MaxRows = (int)$_COOKIE['listmax'];

// are we requested do display different page?
$Page = !isset($_GET['pg']) ? 1 : (int) $_GET['pg'];

// number of possible pages
$NuPg = (int) (($RecordCount-1) / $MaxRows) + 1;

// fix page number if out of limits
$Page = min(max($Page, 1), $NuPg);

// start & end page
$StPg = min(max(1, $Page - 5), max(1, $NuPg - 10));
$EdPg = min($StPg + 10, min($Page + 10, $NuPg));

// previous and next page numbers
$PrPg = $Page - 1;
$NePg = $Page + 1;

// start and end row from recordset
$StaR = ($Page - 1) * $MaxRows + 1;
$EndR = min(($Page * $MaxRows), $RecordCount);

// display results
if ( count( $List ) == 0 ) {
	echo "<div class=\"frame\" style=\"display: table;height: 100px;width: 100%;\">";
	echo "<div style=\"background-color: white;display: table-cell;text-align: center;vertical-align: middle;\"><b>Ni podatkov!</b></div>\n";
	echo "</div>\n";
} else {

	if ( $NuPg > 1 ) {
		echo "<DIV CLASS=\"pg\">\n";
		if ( $StPg > 1 )
			echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&pg=".($StPg-1)."');\">&laquo;</A>\n";
		if ( $Page > 1 )
			echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&pg=$PrPg');\">&lt;</A>\n";
		for ( $i = $StPg; $i <= $EdPg; $i++ ) {
			if ( $i == $Page )
				echo "<FONT COLOR=\"red\"><B>$i</B></FONT>\n";
			else
				echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&pg=$i');\">$i</A>\n";
		}
		if ( $Page < $EdPg )
			echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&pg=$NePg');\">&gt;</A>\n";
		if ( $NuPg > $EdPg )
			echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&pg=".($EdPg<$NuPg? $EdPg+1: $EdPg)."');\">&raquo;</A>\n";
		echo "</DIV>\n";
	}

	echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"frame\">\n";
	$BgCol = "white";
	$i = $StaR-1;
	while ( $i < $EndR ) {
		// get list item
		$Item = $List[$i++];
		// row background color
		if ( $BgCol == "white" )
			$BgCol="#edf3fe";
		else
			$BgCol = "white";
		echo "<tr bgcolor=\"$BgCol\">\n";
		echo "<td><a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action']."&ID=$Item->ID');\"><b>".left($Item->Name,30).(strlen($Item->Name)>30?"...":"")."</b></a></td>\n";
		echo "<td align=\"right\" valign=\"top\" width=\"35\">";
		// move items up/down
		if ( contains($ActionACL,"W") ) {
			if ( $i > $StaR )
				echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Izbor=".$_GET['Izbor'].
					"&Action=".$_GET['Action']."&Item=$Item->ID&Smer=-1');\">".
					"<img src=\"pic/list.up.gif\" height=11 width=11 border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
			else
				echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
			if ( $i < $EndR )
				echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Izbor=".$_GET['Izbor'].
					"&Action=".$_GET['Action']."&Item=$Item->ID&Smer=1');\">".
					"<img src=\"pic/list.down.gif\" height=11 width=11 border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
			else 
				echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
		}
		if ( contains($ActionACL,"D") )
			echo "<a href=\"javascript:void(0);\" onclick=\"javascript:check('$Item->ID','$Item->Name');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Briši\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
		else
			echo "<img src=\"pic/trans.gif\" width=11 height=11 alt=\"\" border=\"0\" align=\"absmiddle\" class=\"icon\">";
		echo "</td>\n";
		echo "</tr>\n";
	}
	echo "</table>\n";
}
?>
