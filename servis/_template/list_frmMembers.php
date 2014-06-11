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
if ( !isset($_GET['ID']) )   $_GET['ID']   = "0";
if ( !isset($_GET['Find']) ) $_GET['Find'] = "";
if ( !isset($_GET['Kdo']) )  $_GET['Kdo']  = "all";

switch ( $_GET['Kdo'] ) {
	case "new":        $filter = 'AND lastvisit IS NULL'; break;
	case "inactive":   $filter = 'AND enabled = 0';       break;
	case "donators":   $filter = 'AND patron <> 0';       break;
	case "moderators": $filter = 'AND accesslevel > 1';   break;
	default:           $filter = '';                      break;
}
// get members
$List = $db->get_results(
	"SELECT ID,
		Nickname AS Ime,
		Name,
		AccessLevel,
		LastVisit,
		Enabled,
		Posts,
		Patron
	FROM frmMembers
	WHERE 1=1 $filter ".
	(($_GET['Find']=="")? "": "AND (Name LIKE '%".$_GET['Find']."%' OR Nickname LIKE '%".$_GET['Find']."%' OR Email LIKE '%".$_GET['Find']."%' OR ID = ".(int)$_GET['Find'].") ").
	"ORDER BY AccessLevel DESC, Nickname"
);
$RecordCount = count($List);

// override maximum number of rows to display
if ( isset($_COOKIE['listmax']) ) $MaxRows = (int)$_COOKIE['listmax'];

// are we requested do display different page?
$Page = !isset($_GET['pg']) ? 1 : (int)$_GET['pg'];

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

//emailing and filtering options
echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\n";
echo "<tr>\n";
echo "<td>";
echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','inc.php?Action=".$_GET['Action']."&Izbor=frmEmail&Who=".$_GET['Kdo']."');\">Message</A>";
echo "</td>\n";
echo "<td align=\"right\">\n";
echo "Users:";
echo "<SELECT NAME=\"Kdo\" SIZE=\"1\" ONCHANGE=\"loadTo('List','list.php?Action=".$_GET['Action']."&Kdo='+this[this.selectedIndex].value);\">\n";
echo "<OPTION VALUE=\"all\"".(($_GET['Kdo']=="all")? " SELECTED": "").">all</OPTION>\n";
echo "<OPTION VALUE=\"new\"".(($_GET['Kdo']=="new")? " SELECTED": "").">new</OPTION>\n";
echo "<OPTION VALUE=\"inactive\"".(($_GET['Kdo']=="inactive")? " SELECTED": "").">inactive</OPTION>\n";
echo "<OPTION VALUE=\"donators\"".(($_GET['Kdo']=="donators")? " SELECTED": "").">donators</OPTION>\n";
echo "<OPTION VALUE=\"moderators\"".(($_GET['Kdo']=="moderators")? " SELECTED": "").">moderators</OPTION>\n";
echo "</SELECT>\n";
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";

// display results
if ( count( $List ) == 0 ) {
	echo "<div class=\"frame\" style=\"display: table;height: 100px;width: 100%;\">";
	echo "<div style=\"background-color: white;display: table-cell;text-align: center;vertical-align: middle;\"><b>No data!</b></div>\n";
	echo "</div>\n";
} else {

	if ( $NuPg > 1 ) {
		echo "<DIV CLASS=\"pg\">\n";
		if ( $StPg > 1 )
			echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&Kdo=".$_GET['Kdo']."&pg=".($StPg-1)."');\">&laquo;</A>\n";
		if ( $Page > 1 )
			echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&Kdo=".$_GET['Kdo']."&pg=$PrPg');\">&lt;</A>\n";
		for ( $i = $StPg; $i <= $EdPg; $i++ ) {
			if ( $i == $Page )
				echo "<FONT COLOR=\"red\"><B>$i</B></FONT>\n";
			else
				echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&Kdo=".$_GET['Kdo']."&pg=$i');\">$i</A>\n";
		}
		if ( $Page < $EdPg )
			echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&Kdo=".$_GET['Kdo']."&pg=$NePg');\">&gt;</A>\n";
		if ( $NuPg > $EdPg )
			echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&Kdo=".$_GET['Kdo']."&pg=".($EdPg<$NuPg? $EdPg+1: $EdPg)."');\">&raquo;</A>\n";
		echo "</DIV>\n";
	}

	echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"frame\">\n";
	$BgCol = "white";
	$i = $StaR-1;
	while ( $i < $EndR ) {
		// get list item
		$Item = $List[$i++];
		$Title = $Item->Ime;

		if ( $Item->Ime == "")
			$Title = $Item->Name;

		// row background color
		if ( $BgCol == "white" )
			$BgCol="#edf3fe";
		else
			$BgCol = "white";

		echo "<tr bgcolor=\"$BgCol\">\n";
		echo "<td>";

		if ( contains($ActionACL,"W") )
			echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action']."&ID=$Item->ID');\">$Title</a>" . (!$Item->Enabled? '*': '');
		else
			echo $Title . (!$Item->Enabled? '*': '');

		echo "</td>\n";
		echo "<td align=\"right\">".(int)$Item->Posts."</td>\n";
		echo "<td align=\"center\">";

		if ( $Item->Patron )
			echo "<B CLASS=\"blu\">P</B>";
		else
			echo "&nbsp;";

		if ( $Item->AccessLevel >= 5 )
			echo "<B CLASS=\"red\">A</B>";
		elseif ( $Item->AccessLevel >= 3 )
			echo "<B COLOR=\"grn\">M</B>";
		else
			echo "&nbsp;";

		echo "</td>\n";
		echo "<td align=\"right\">".($Item->LastVisit? date('j.n.y',sqldate2time($Item->LastVisit)): '')."</td>\n";
		echo "<td align=\"right\" valign=\"top\" width=\"20\">";

		if ( contains($ActionACL,"D") && $Item->ID > 1 )
			echo "<a href=\"javascript:void(0);\" onclick=\"javascript:check('$Item->ID','$Item->Name');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Delete\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
		else
			echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";

		echo "</td>\n";
		echo "</tr>\n";
	}
	echo "</table>\n";
}
?>
