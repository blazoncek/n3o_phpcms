<?php
/* _arhiv_left.php - Archive month links.
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

// get months with data
$Objave = $db->get_results(
	"SELECT DISTINCT
		month(B.Datum) AS M,
		year(B.Datum) AS Y
	FROM
		KategorijeBesedila KB
		LEFT JOIN Besedila B ON KB.BesediloID = B.BesediloID
	WHERE
		B.Izpis<>0
		AND
		KB.KategorijaID = '" . $_GET['kat'] . "'
	ORDER BY
		2 DESC,
		1 DESC"
	);
	
if ( count($Objave) > 0 ) {

?>
	<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
	function expand_shrink(y) {
		if (document.getElementById("results"+y).style.display == "") {
			document.getElementById("collapse"+y).src = "<?php echo $WebPath; ?>/pic/arrow.right.png";
			document.getElementById("results"+y).style.display = "none";
		} else {
			document.getElementById("collapse"+y).src = "<?php echo $WebPath; ?>/pic/arrow.down.png";
			document.getElementById("results"+y).style.display = "";
		}
	}
	</SCRIPT>
<?php

	echo "<div class=\"menu\">\n";
	echo "<div class=\"title\">". multiLang('<Archive>', $lang) ."</div>\n";
	echo "<blockquote>\n";
	$Leto = "";
	foreach( $Objave as $Arhiv ) {
		// close DIV at year change but not for 1st
		if ( $Arhiv->Y != $Leto ) {
			if ( $Leto != "" )
				echo "</div>\n";
			// display year group
			echo "<a href=\"javascript:expand_shrink($Arhiv->Y)\"><img id=\"collapse$Arhiv->Y\" src=\"$WebPath/pic/arrow." . (($Arhiv->Y == date( "Y" )) ? "down" : "right") .".png\" alt=\"\" width=\"7\" height=\"7\" border=\"0\" retina=\"no\">$Arhiv->Y</a><br>\n";

			// open running DIV (at year change)
			if ( isset( $_GET['ar'] ) && $_GET['ar'] != "" ) {
				echo "<div id=\"results$Arhiv->Y\" style=\"margin-left:20px;". (".". $Arhiv->Y != strstr($_GET['ar'], ".") ? "display:none;" : "") ."\">\n";
			} else {
				echo "<div id=\"results$Arhiv->Y\" style=\"margin-left:20px;". ($Arhiv->Y != date("Y") ? "display:none;" : "") ."\">\n";
			}
		}
		// display archive link
		$link = ($TextPermalinks) ? ($IsIIS ? $WebFile .'/' : ''). $KatText .'/AR' : '?kat='. $_GET['kat'] .'&amp;ar=';
		echo "<a href=\"". $WebPath ."/". $link . $Arhiv->M .'%2E'. $Arhiv->Y ."\">" . date("M Y", mktime(0, 0, 0, $Arhiv->M, 1, $Arhiv->Y)) . "</a><br>\n";
		$Leto = $Arhiv->Y;
	}
	// close running (year) DIV
	echo "</div>\n";
	//close global DIV
	echo "</blockquote>\n";
	echo "</div>\n";
}

?>
