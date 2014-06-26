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
if ( !isset($_GET['ID']) )   $_GET['ID'] = "0";
if ( !isset( $_GET['Find'] ) ) $_GET['Find'] = "";

// get all messages
$List = $db->get_results(
	"SELECT DISTINCT
		M.emlMessageID,
		M.Naziv,
		M.Datum,
		M.ACLID
	FROM
		emlMessages M
		LEFT JOIN emlMessagesTxt MT ON M.emlMessageID=MT.emlMessageID "
	.($_GET['Find'] == "" ? " " : "WHERE M.Naziv LIKE '%".$db->escape($_GET['Find'])."%' OR MT.Naziv LIKE '%".$db->escape($_GET['Find'])."%' OR MT.Opis LIKE '%".$db->escape($_GET['Find'])."%' ").
	"ORDER BY M.Naziv" );

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
	echo "<div style=\"background-color: white;display: table-cell;text-align: center;vertical-align: middle;\"><b>No data!</b></div>\n";
	echo "</div>\n";
} else {

	if ( $NuPg > 1 ) {
		echo "<div class=\"pg\">\n";
		if ( $StPg > 1 )
			echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&pg=".($StPg-1)."');\">&laquo;</a>\n";
		if ( $Page > 1 )
			echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&pg=$PrPg');\">&lt;</a>\n";
		for ( $i = $StPg; $i <= $EdPg; $i++ ) {
			if ( $i == $Page )
				echo "<FONT COLOR=\"red\"><B>$i</B></FONT>\n";
			else
				echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&pg=$i');\">$i</a>\n";
		}
		if ( $Page < $EdPg )
			echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&pg=$NePg');\">&gt;</a>\n";
		if ( $NuPg > $EdPg )
			echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&pg=".($EdPg<$NuPg? $EdPg+1: $EdPg)."');\">&raquo;</a>\n";
		echo "</div>\n";
	}

	echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"frame\">\n";
	$BgCol = "white";
	$i = $StaR-1;
	while ( $i < $EndR ) {
		// get list item
		$Item = $List[$i++];
		// get ACL
		$ACL = userACL( $Item->ACLID );
		if ( contains($ACL,"L") ) {
			// row background color
			$BgCol = $BgCol=="white" ? "#edf3fe" : "white";
			echo "<tr bgcolor=\"$BgCol\">\n";
			echo "<td><a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action']."&ID=$Item->emlMessageID');\"><b>".left($Item->Naziv,30).(strlen($Item->Naziv)>30?"...":"")."</b></a></td>\n";
			echo "<td align=\"center\" class=\"f10\">". ($Item->Datum=="" ? "" : date("j.n.y",sqldate2time($Item->Datum))). "</td>\n";
			echo "<td align=\"right\" valign=\"top\" width=\"20\">";
			if ( contains($ACL,"D") )
				echo "<a href=\"javascript:void(0);\" onclick=\"javascript:check('$Item->emlMessageID','$Item->Naziv');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Delete\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
			else
				echo "&nbsp;";
			echo "</td>\n";
			echo "</tr>\n";
		}
	}
	echo "</table>\n";
}
?>