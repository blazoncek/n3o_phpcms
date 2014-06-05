<?php
/* repeat - Display category title and description.
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

// Requires $Kat recordset

// display category title, excerpt & description
if ( count($Kat) && $Kat->Naziv != "" && $Kat->Opis != "" ) {
	echo "<div class=\"text\">\n";
	echo "<h1>". $Kat->Naziv ."</h1>\n";
	if ( $Kat->Povzetek != "" )
		echo "<div class=\"abstract\">". ReplaceSmileys($Kat->Povzetek, "$WebPath/pic/") ."</div>\n";
	echo "<div class=\"body\">";
	echo ReplaceSmileys(PrependImagePath(str_replace("\\\"","\"",$Kat->Opis), "$WebPath/"), "$WebPath/pic/");
	echo "</div>\n";
	echo "</div>\n";
}
?>