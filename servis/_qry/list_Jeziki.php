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

if ( isset($_GET['Brisi']) && $_GET['Brisi'] != "" ) {
	$db->query("START TRANSACTION");
	// remove image
	$Slika = $db->get_var("SELECT Ikona FROM Jeziki WHERE Jezik = '".$_GET['Brisi']."'");
	if ( $Slika && $Slika != "" ) {
		@unlink($StoreRoot ."/pic/". $Slika);
	}
	$db->query("DELETE FROM SifrantiTxt WHERE Jezik = '".$_GET['Brisi']."'");
	$db->query("DELETE FROM NLSText     WHERE Jezik = '".$_GET['Brisi']."'");
	$db->query("DELETE FROM Predloge    WHERE Jezik = '".$_GET['Brisi']."'");
	$db->query("DELETE FROM Ankete      WHERE Jezik = '".$_GET['Brisi']."'");
	$db->query("DELETE FROM MediaOpisi  WHERE Jezik = '".$_GET['Brisi']."'");
	$db->query("DELETE FROM KategorijeNazivi WHERE Jezik = '".$_GET['Brisi']."'");
	$db->query("DELETE FROM BesedilaOpisi    WHERE Jezik = '".$_GET['Brisi']."'");
	$db->query("DELETE FROM emlMessagesTxt   WHERE Jezik = '".$_GET['Brisi']."'");
	$db->query("DELETE FROM Jeziki WHERE Jezik = '".$_GET['Brisi']."'");
	$db->query("COMMIT");
}
?>