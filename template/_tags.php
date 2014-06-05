<?php
/* _tags.php - Display all text tags.
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

$Tags = $db->get_results(
	"SELECT
		T.TagID,
		T.TagName,
		count(BT.TagID) as TagCount
	FROM Tags T
		LEFT JOIN BesedilaTags BT ON BT.TagID=T.TagID
	GROUP BY T.TagID, T.TagName
	HAVING count(BT.TagID)>0
	ORDER BY T.TagName"
	);

$MaxTags = $db->get_var(
	"SELECT max(MT.TagCount)
	FROM (
		SELECT count(T.TagID) as TagCount
		FROM Tags T
			LEFT JOIN BesedilaTags BT ON BT.TagID=T.TagID
		GROUP BY T.TagName) MT"
	);

// if tags exist
if ( $Tags ) {
	echo "<div class=\"menu\">\n";
	echo "<div class=\"title\">". multiLang('<Tags>', $lang) ."</div>\n";
	echo "<div class=\"tags\">\n";
	$kat = ($TextPermalinks) ? ($IsIIS ? "$WebFile/" : ''). "$KatText/" : '?kat='. $_GET['kat'];
	if ( $Tags ) for ( $i=0; $i<count($Tags); $i++ ) {
		// select max font size
		$FontSize = (int)(($Tags[$i]->TagCount / $MaxTags)*100)+100;
		$tag = ($TextPermalinks) ? 'TAG'. $Tags[$i]->TagID .'/': '&amp;tag='. $Tags[$i]->TagID;
		echo "<a href=\"$WebPath/$kat". $tag ."\" style=\"font-size:". $FontSize ."%\">";
		echo $Tags[$i]->TagName;
		echo "</a>\n";
	}
	echo "</div>\n";
	echo "</div>\n";
}
// free recordset
unset($Tags);
?>