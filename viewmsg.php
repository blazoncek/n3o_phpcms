<?php
/*~ viewmsg.php - view message from mailing list
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
| This file is part of N3O CMS (frontend and backend).                      |
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

if ( !isset($_GET['id']) ) {
	header("HTTP/1.1 301 Moved permanently");
	header("Status: 301 Moved permanently");
	header("Refresh:0; URL=./");
	die();
}

// include general application framework
require_once(dirname(__FILE__) ."/_application.php");

$Texts = $db->get_results(
	"SELECT *
	FROM emlMessagesTxt
	WHERE emlMessageTxtID=". (int)$_GET['id']
);

if ( $Texts ) foreach ( $Texts as $Text ) {
	$Lang = $Text->Jezik=='' ? langDefault() : $Text->Jezik;

	$Subject = $Text->Naziv;
	$Body    = $Text->Opis;
	// make absolute URLs
	$Body    = preg_replace("/(src=\")/i", '$1'.$WebURL.'/', $Body);
	$Body    = preg_replace("/(href=\"\.+\/)/i", 'href="'.$WebURL.'/', $Body);
	// convert text smileys into images
	$Body    = ReplaceSmileys($Body, $WebURL."/pic/");

	// add styling
	if ( file_exists('template/_mailTemplate.html') ) {
		$Message = file_get_contents('template/_mailTemplate.html');
	} else {
		$Message = file_get_contents('servis/_mailTemplate.html');
	}
	$Message = str_replace("#TextColor#", $TextColor, $Message);
	$Message = str_replace("#LinkColor#", $LinkColor, $Message);
	$Message = str_replace("#PageColor#", $PageColor, $Message);
	$Message = str_replace("#TxtFrColor#",$TxtFrColor,$Message);
	$Message = str_replace("#TxtExColor#",$TxtExColor,$Message);
	$Message = str_replace("#FrameColor#",$FrameColor,$Message);
	$Message = str_replace("#FrmExColor#",$FrmExColor,$Message);
	$Message = str_replace("#BackgColor#",$BackgColor,$Message);
	$Message = str_replace("#BckLoColor#",$BckLoColor,$Message);
	$Message = str_replace("#BckHiColor#",$BckHiColor,$Message);
	$Message = str_replace("#PageWidth#", $ContentW,  $Message);
	$Message = str_replace("#ContentW#",  $ContentW,  $Message);
	$Message = str_replace("#WebURL#",    $WebURL,    $Message);
	$Message = str_replace("#MsgBody#",   $Body,      $Message);
	$Message = str_replace("#ID#",        $Text->emlMessageTxtID,        $Message);
	$Message = str_replace("#Title#",     multiLang('<Title>',$Lang),    $Message);
	$Message = str_replace("#SubTitle#",  multiLang('<SubTitle>',$Lang), $Message);
	$Message = str_replace("#CopyRight#", multiLang('<CopyRight>',$Lang),$Message);

	echo $Message;
}
?>