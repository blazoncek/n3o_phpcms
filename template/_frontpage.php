<?php
/* _frontpage.php - special implementation (usually for kat=00)
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

// find the Blog category
$blog = $db->get_var("SELECT K.KategorijaID FROM Kategorije K WHERE K.Ime = 'blog'");
if ( $blog ) {
	// jump to found category
	header("Location: $WebURL/". ($TextPermalinks ? ($IsIIS ? $WebFile .'/' : '').'blog/' : '?kat='. $blog));
	//die();
} else {
	// default to 01
	$ime = $db->get_var("SELECT K.Ime FROM Kategorije K WHERE K.KategorijaID = '01'");
	header("Location: $WebURL/". ($TextPermalinks ? ($IsIIS ? $WebFile .'/' : '')."$ime/" : '?kat=01'));
	//die();
}
