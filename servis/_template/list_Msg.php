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

require_once( "../inc/pop3.php" );
require_once( "../inc/thumb/PhpThumb.inc.php" );

// image parameters
$x = $db->get_row(
	"SELECT
		ST.SifNaziv AS GalleryBase,
		S.SifNVal1 AS DefPicSize,
		S.SifNVal2 AS DefThumbSize,
		S.SifNVal3 AS MaxPicSize
	FROM
		Sifranti S
		LEFT JOIN SifrantiTxt ST ON S.SifrantID=ST.SifrantID
	WHERE
		S.SifrCtrl = 'BESE'
		AND S.SifrText = 'Gallery'
	ORDER BY
		ST.Jezik DESC"
);
// deafult values for image upload sisze
if ( $x ) {
	$GalleryBase  = $x->GalleryBase;
	$DefPicSize   = (int)$x->DefPicSize;
	$DefThumbSize = (int)$x->DefThumbSize;
	$MaxPicSize   = (int)$x->MaxPicSize;
} else {
	$GalleryBase  = 'gallery';
	$DefPicSize   = 640;
	$DefThumbSize = 128;
	$MaxPicSize   = 1024;
}

// determine file (sPath) and URL (rPath) path
$rPath = "media". ($GalleryBase=="" ? "" : "/". $GalleryBase) ."/". date("Y");
$sPath = str_replace("\\", "/", $StoreRoot ."/". $rPath);
$sPath = str_replace("//", "/", $sPath);

// create processed media folders
@mkdir($sPath."/thumbs",0777,true);
@mkdir($sPath."/large",0777,true);

// get uploaded files
$List = scandir($StoreRoot ."/media/upload");
$RecordCount = count($List) - 2; // ignore . and ..

// display results
if ( count($List) == 0 ) {
	echo "<div class=\"frame\" style=\"display: table;height: 100px;width: 100%;\">";
	echo "<div style=\"background-color: white;display: table-cell;text-align: center;vertical-align: middle;\"><b>Ni podatkov!</b></div>\n";
	echo "</div>\n";
} else {

	echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"frame\">\n";
	$BgCol = "white";

	foreach ( $List As $Slika ) {
		if ( substr($Slika,1,1) == "." || !contains("jpg,png,gif", strtolower(right($Slika,3))) ) continue; // skip invalid
		
		// resize image
		try {
			$thumb = PhpThumbFactory::create($StoreRoot ."/media/upload/". $Slika, array('jpegQuality' => $jpgPct,'resizeUp' => false) );
			$thumb->adaptiveResize(16, 16);
			$imageAsString = $thumb->getImageAsString(); 
		} catch (Exception $e) {
			$imageAsString = "";
		}
		$stat = stat($StoreRoot ."/media/upload/". $Slika);
		// row background color
		$BgCol= $BgCol == "white" ? "#edf3fe" : "white";
		echo "<tr bgcolor=\"$BgCol\">\n";
		echo "<td width=\"20\" valign=\"middle\">";
		echo $imageAsString != "" ? "<img src=\"data:image/png;base64,". base64_encode($imageAsString) ."\" width=\"16\" height=\"16\" border=\"0\">" : "";
		echo "</td>\n";
		echo "<td valign=\"middle\">&nbsp;<a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&file=$Slika');\">". $Slika ."</a></td>\n";
		echo "<td align=\"right\" valign=\"middle\" width=\"20\">";
		echo "<a href=\"javascript:void(0);\" title=\"Briši\" onclick=\"javascript:check('$Slika','$Slika');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Briši\" border=\"0\" align=\"absmiddle\" vspace=2 hspace=4 class=\"icon\"></a>";
		echo "</td>\n";
		echo "</tr>\n";
	}

	echo "</table>\n";
}
?>