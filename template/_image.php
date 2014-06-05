<?php
/* _image.php - Display a single image uploaded to gallery or other media folder.
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

if ( isset($_GET['gID']) ) $_GET['ID'] = $_GET['gID']; // backwards compatibility
if ( !isset($_GET['ID']) ) {
	if ( isset($_GET['pID']) )
		$_GET['mID'] = (int)$db->get_var( "SELECT MediaID FROM Media WHERE Datoteka LIKE '%". $db->escape($_GET['pID']) ."' LIMIT 1" );
	if ( isset($_GET['mID']) && $_GET['mID'] )
		$_GET['ID'] = (int)$db->get_var( "SELECT BesediloID FROM BesedilaSlike WHERE MediaID = ". (int)$_GET['mID'] ." ORDER BY BesediloID LIMIT 1" );
}

if ( $Slika = $db->get_row(
	"SELECT
		M.MediaID,
		M.Naziv,
		M.Datoteka,
		M.Meta,
		MO.Naslov,
		MO.Opis,
		BS.Polozaj
	FROM
		Media M
		LEFT JOIN MediaOpisi MO ON M.MediaID = MO.MediaID
		LEFT JOIN BesedilaSlike BS ON BS.MediaID = M.MediaID
	WHERE
		(MO.Jezik='$lang' OR MO.Jezik IS NULL) AND ".
		(isset($_GET['mID']) ? "M.MediaID = ". (int)$_GET['mID'] : "M.Datoteka LIKE '%". $db->escape($_GET['pID']) ."'") .
		(isset($_GET['ID']) ? " AND BS.BesediloID = ". (int)$_GET['ID'] : "") ." ".
	"ORDER BY
		MO.Jezik"
	) ) {

	if ( isset($_GET['ID']) ) {
		$Prev = $db->get_var(
			"SELECT M.MediaID
			FROM Media M
				LEFT JOIN BesedilaSlike BS ON BS.MediaID = M.MediaID
			WHERE BS.Polozaj > $Slika->Polozaj AND BS.BesediloID = ". (int)$_GET['ID'] ."
			ORDER BY BS.Polozaj LIMIT 1"
			);
		$Next = $db->get_var(
			"SELECT M.MediaID
			FROM Media M
				LEFT JOIN BesedilaSlike BS ON BS.MediaID = M.MediaID
			WHERE BS.Polozaj < $Slika->Polozaj AND BS.BesediloID = ". (int)$_GET['ID'] ."
			ORDER BY BS.Polozaj DESC LIMIT 1"
			);
	} else {
		$Prev = $db->get_var("SELECT M.MediaID FROM Media M WHERE M.MediaID > $Slika->MediaID ORDER BY M.Datum LIMIT 1");
		$Next = $db->get_var("SELECT M.MediaID FROM Media M WHERE M.MediaID < $Slika->MediaID ORDER BY M.Datum DESC LIMIT 1");
	}

	// determine file (sPath) and URL (rPath) path
	$sRoot = $StoreRoot;
	$sPath = dirname($sRoot ."/". $Slika->Datoteka);
	$rPath = str_replace("\\", "/", right($sPath, strlen($sPath)-strlen($sRoot)-1));
	$sFile = basename($Slika->Datoteka);

	// get image metadata
	$Meta = ParseMetadata($Slika->Meta);
	$IMG_WIDTH  = (int)$Meta['rw'];	// reduced width
	$IMG_HEIGHT = (int)$Meta['rh']; // reduced height

	$kat = $TextPermalinks ? ($IsIIS ? "$WebFile/" : ''). $KatText .'/' : '?kat='. $_GET['kat'];
	if ( isset($_GET['ID']) && $_GET['ID'] )
		$bid = $TextPermalinks ? $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID = ". (int)$_GET['ID']) .'/?' : '&amp;ID='. $_GET['ID'] .'&amp;';
	else
		$bid = "";

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	echo "<TR>\n";

	if ( !$Mobile ) {
		echo "<TD ALIGN=\"center\" VALIGN=\"middle\" WIDTH=\"40\">\n";
		if ( $Prev) {
			echo "<A HREF=\"$WebPath/$kat". $bid ."tmpl=Slika&amp;mID=". $Prev ."&amp;nomenu&amp;noextra\">";
			echo "<IMG SRC=\"$WebPath/pic/prev_wht.png\" ALT=\"\" BORDER=\"0\" style=\"height:40px;width:40px;\">";
			echo "</A>\n";
		} else
			echo "&nbsp;\n";
		echo "</TD>\n";
	}
	echo "<TD ALIGN=\"center\" VALIGN=\"middle\">\n";
	if ( fileExists($sPath .'/large/'. $sFile) )
		echo "<a href=\"$WebPath/$rPath/large/$sFile\" class=\"fancybox\" title=\"". $Slika->Naslov ."\">";
	else
		echo "<A HREF=\"$WebPath/$kat". $bid ."\">";

	// if file exists display image thumbnail
	if ( fileExists($sPath .'/'. $sFile) )
		echo "<IMG SRC=\"$WebPath/$rPath/$sFile\" ALT=\"\" BORDER=0 style=\"width:".$IMG_WIDTH."px;height:".$IMG_HEIGHT."px;\">";
	else
		echo "<IMG SRC=\"$WebPath/pic/nislike@4x.png\" ALT=\"Missing image\" BORDER=\"0\" retina=\"no\">";

	echo "</A>\n";
	echo "</TD>\n";

	if ( !$Mobile ) {
		echo "<TD ALIGN=\"center\" VALIGN=\"middle\" WIDTH=\"40\">\n";
		if ( $Next ) {
			echo "<A HREF=\"$WebPath/$kat". $bid ."tmpl=Slika&amp;mID=". $Next ."&amp;nomenu\">";
			echo "<IMG SRC=\"$WebPath/pic/next_wht.png\" ALT=\"\" BORDER=\"0\" style=\"height:40px;width:40px;\">";
			echo "</A>\n";
		} else
			echo "&nbsp;\n";
		echo "</TD>\n";
	}
	echo "</TR>\n";

	if ( $Slika->Naslov != "" || $Slika->Opis != "" ) {
		echo "<TR>\n";
		echo "<TD ALIGN=\"center\"".(!$Mobile?" COLSPAN=\"3\"":"").">\n";
		echo "<B>". $Slika->Naslov ."</B>\n";
		echo "<div class=\"a10\">". str_replace("\\\"","\"",$Slika->Opis) ."</div>\n";
		if ( array_key_exists('EUR',$Meta) )
			echo "<div class=\"price\">". multiLang('<Price>', $lang) .": ". number_format((float)$Meta['EUR'],2,',','.') ."&euro;</div>\n";
		echo "</TD>\n";
		echo "</TR>\n";
	}

	echo "</TABLE>\n";

	// add PayPal's Buy now button
	if ( array_key_exists('EUR',$Meta) ) {
		echo "<div style=\"text-align:center;margin:15px 0;\">\n";
		echo "<form name=\"_xclick\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">\n";
		echo "<input type=\"hidden\" name=\"business\" value=\"". $PostMaster ."\">\n";
		echo "<input type=\"hidden\" name=\"currency_code\" value=\"EUR\">\n";
		echo "<input type=\"hidden\" name=\"item_name\" value=\"". $Slika->Naslov ."\">\n";
		echo "<input type=\"hidden\" name=\"amount\" value=\"". number_format((float)$Meta['EUR'],2,'.','') ."\">\n";
		echo "<input type=\"image\" src=\"http://www.paypal.com/en_US/i/btn/btn_buynow_LG.gif\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\">\n";
		echo "</form>\n";
		echo "</DIV>\n";
	}
	unset($Meta);

} else if ( isset($_GET['pID']) ) {

	// determine file (sPath) and URL (rPath) path
	$sRoot = $StoreRoot;
	$sPath = dirname($sRoot . "/" . $_GET['pID']);
	$rPath = str_replace("\\", "/", right($sPath, strlen($sPath)-strlen($sRoot)-1));
	$sFile = basename($_GET['pID']);

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	echo "<TR>\n";
	echo "<TD ALIGN=\"center\" VALIGN=\"middle\">\n";

	// if file exists display image thumbnail
	if ( fileExists($sPath .'/'. $sFile) )
		echo "<IMG SRC=\"$WebPath/$rPath/$sFile\" ALT=\"\" BORDER=0>";
	else
		echo "<IMG SRC=\"$WebPath/pic/nislike@2x.png\" ALT=\"Missing image.\" BORDER=\"0\" retina=\"no\">";

	echo "</TD>\n";
	echo "</TR>\n";
	echo "</TABLE>\n";

} else {

?>
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD ALIGN="center" VALIGN="middle">
	<IMG SRC="<?php echo $WebPath ?>/pic/nislike@4x.png" ALT="Wrong parameters" BORDER="0" retina="no">
	</TD>
</TR>
</TABLE>
<?php
}
?>