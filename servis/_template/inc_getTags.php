<?php
/*
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend and backend)                                 |
|   Version: 2.2.2                                                          |
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

// search results in JSON format
$WhereClause = "1=0";
// iskanje besedila: $_GET['term']
if ( isset( $_GET['term'] ) ) {
	$Plus = " OR ";
	$WhereClause .= $Plus . SearchString("TagName", $_GET['term']);
}
$Iskanje = $db->get_results(
	"SELECT
		TagName,
		TagID
	FROM
		Tags
	WHERE
		". $WhereClause ."
	ORDER BY
		TagName"
	);

// maximum # of results on one page
if ( !isset($_GET['num']) )
	$_GET['num'] = 10;
$MaxRows = min(max((int)$_GET['num'], 10), 50);

echo "[";
if ( $Iskanje ) for ( $i=0; $i<$MaxRows && $i<count($Iskanje); $i++ ) {
	echo "{";
	echo "\"id\": \"". $Iskanje[$i]->TagID . "\",";
	echo "\"label\": \"". $Iskanje[$i]->TagName . "\",";
	echo "\"value\": \"" . $Iskanje[$i]->TagName . "\"";
	echo "}";
	if ( $i<$MaxRows-1 && $i<count($Iskanje)-1 ) echo ",";
}
echo "]\n";
?>