<?php
/* _text.php - Display a single text.
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

// Requires
// - $_GET['ID'] to be set
// - recordset $Teksti
// - recordset $Galerija
// - recordset $Media
// - recordset $Dodatni

echo "<div class=\"text\">\n";

// display post image
if ( $Teksti && $Teksti[0]->Slika != "" ) {
	try { // to get image size
		$thumb = PhpThumbFactory::create($StoreRoot ."/media/besedila/". $Teksti[0]->Slika);
		$size = $thumb->getCurrentDimensions();
		echo "<img src=\"$WebPath/media/besedila/". $Teksti[0]->Slika ."\" alt=\"\" border=0 class=\"frame\" style=\"width:".$size['width']."px;height:".$size['height']."px;\">\n";
	} catch (Exception $e) {
		echo "<!-- Error getting image size! -->\n";
		echo "<img src=\"$WebPath/media/besedila/". $Teksti[0]->Slika ."\" alt=\"\" border=0 class=\"frame\" retina=\"no\">\n";
	}
}

$j = 0;
// display a single post (comprised of multiple texts)
if ( $Teksti ) foreach( $Teksti as $Tekst ) {

	echo "\t<div class=\"title\">\n";
	// display text title			
	if ( left($Tekst->Naslov,1) != '.' ) {
		echo "\t".($j==0 ? "<h1>" : "<h2>")."";
		echo $Tekst->Naslov;
		echo ($j==0 ? "</h1>" : "</h2>")."\n";
	}
	echo "\t</div>\n";
	
	// display text abstract
	if ( $Tekst->Povzetek != "" ) {
		$abstract = str_replace('\n', '<br>', $Tekst->Povzetek);
		$abstract = str_replace("&quot;", "\"", $abstract);
		$abstract = ReplaceSmileys(PrependImagePath($abstract, "$WebPath/"), "$WebPath/pic/");
		echo "\t<div class=\"abstract\">\n";
		echo (!$Mobile || (isset($_GET['ID']) && $_GET['ID'] != 0) ? "<b>" : "");
		echo $abstract;
		echo (!$Mobile || (isset($_GET['ID']) && $_GET['ID'] != 0) ? "</b>" : ""). "\n";
		echo "\t</div>\n";
	}

	echo "\t<div class=\"body\">\n";
	$Bes = $Tekst->Opis;
	// correct escaped quotes (some PHP/MySQL combos)
	$Bes = str_replace("\\\"", "\"", $Bes);

	// create embeded google maps
	if ( preg_match_all("/\[googlemaps[^\]]*\]([^[]+)\[\/googlemaps\]/i", $Bes, $locations) ) {
		// $locations = {{'[googlemaps]location1[/googlemaps]',...},{'location1',...}}
		$gmaps = '<iframe class="googlemaps" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=###LOCATION###&amp;ie=UTF8&amp;t=m&amp;z=15&amp;output=embed"></iframe><br><small><a href="https://maps.google.com/maps?q=###LOCATION###&amp;ie=UTF8&amp;t=m&amp;z=15&amp;source=embed">Prikaži večji zemljevid</a></small>';
		if ( count($locations[1]) > 0 ) foreach ( $locations[1] as $location ) {
			$Bes = str_ireplace('[googlemaps]'. $location .'[/googlemaps]', str_ireplace('###LOCATION###', $location, $gmaps), $Bes);
		}
	}

	// fix images for permalinks
	$Bes = PrependImagePath($Bes, "$WebPath/");

	// add <A HREF=...> to images with larger version (in ./large folder)
	if ( !$Mobile )
		$Bes = AddLightboxLink($Bes, $Tekst->ID);
	//else
	//	$Bes = AddImageLink($Bes, "$WebPath/?kat=". $_GET['kat'] ."&nomenu&tmpl=Slika&pID=");

	// replace text smilies with images
	$Bes = ReplaceSmileys($Bes, $WebPath ."/pic/");
	// display text content
	echo $Bes ."\n";
	echo "\t</div>\n";
	
	$j++;
}
// text div
echo "</div>\n";

// display gallery
if ( count($Galerija) > 0 ) {
	echo "<div class=\"gallery fence\">\n";
	echo "<ul id=\"Gallery\">\n";
	$i = 0;
	foreach ( $Galerija as $Slika ) {

		// determine file (sPath) and URL (rPath) path
		$sFile = $Slika->Datoteka;
		$rPath = $WebPath   ."/". dirname($sFile);
		$sPath = $StoreRoot ."/". dirname($sFile);
		$sFile = basename($sFile);
		
		// file title exists?
		$sName = $Slika->Naslov != "" ? $Slika->Naslov : $sFile;

		echo "\t<li>";
		// add link tag
		echo "<A HREF=\"$rPath/".(fileExists($sPath."/large/".$sFile)?"large/":"").$sFile."\" ".($Mobile?" REL=\"external\"":"CLASS=\"fancybox\" REL=\"lightbox_gal\"")." TITLE=\"$sName\">";

		// display image thumbnail
		if ( fileExists($sPath."/thumbs/".$sFile) ) {
			// get metadata
			$Meta = ParseMetadata($Slika->Meta);
			$IMG_WIDTH  = (int)$Meta['tw'];	// thumbnail width
			$IMG_HEIGHT = (int)$Meta['th']; // thumbnail height
			unset($Meta);
			 // existing thumbnail
			echo "<IMG SRC=\"$rPath/thumbs/$sFile\" ALT=\"\" BORDER=0 CLASS=\"thumb\" retina=\"no\">";
		} else {
			// try to create thumbnail on the fly
			try {
				$thumb = PhpThumbFactory::create($sPath."/".((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? DecodeUTF8($sFile) : $sFile));
				$thumb->adaptiveResize($DefThumbSize, $DefThumbSize);
				$imageAsString = $thumb->getImageAsString(); 
				echo "<IMG SRC=\"data:image/". strtolower($thumb->getFormat()) .";base64,". base64_encode($imageAsString) ."\" ALT=\"$sName\" BORDER=\"0\" class=\"thumb\" retina=\"no\">";
			} catch (Exception $e) {
				// display missing thumbnail image
				echo "<IMG SRC=\"$WebPath/pic/nislike_112.png\" ALT=\"\" BORDER=\"0\" class=\"thumb\">";
			}
		}

		// add closing link tag
		echo "</A>";
		echo "</li>\n";
	}
	echo "</ul>";
	echo "</div>\n";
}

// display attachments for non-mobile clients
if ( !$Mobile ) {
	if ( count($Media) > 0 ){
		echo "<div class=\"related\">\n";
		echo "\t<div class=\"head\">". multiLang("<Attachments>", $lang) ."</div>\n";
		echo "\t<div class=\"body\">\n";
		foreach ( $Media as $File ) {
			if ( $File->Tip == 'GPX' ) {
				echo "\t<div id=\"map_". $File->Ime ."\" class=\"googlemapswide\"></div>\n";
				echo "<script>";
				echo "initialize_map('map_". $File->Ime ."','". $WebURL .'/media/'. $File->Datoteka ."');";
				echo "</script>\n";
			} else {
				echo "\t<div class=\"listitem\">";
				echo "<A HREF=\"$WebPath/media/$File->Datoteka\" TITLE=\"". sprintf("%4.1f", ((float)$File->Velikost)/1024) ."kB\" TARGET=\"_blank\">";
				if ( $File->Slika != "" )
					echo "<IMG SRC=\"$WebPath/media/media/". (fileExists($StoreRoot.'/media/media/thumbs/'.$File->Slika) ? 'thumbs/' : '') .$File->Slika ."\" ALIGN=\"right\" ALT=\"\" CLASS=\"thumb\" retina=\"no\">";
				if ( $File->Naslov != "" && left($File->Naslov,1) != "." )
					echo "<B>$File->Naslov</B>";
				elseif ( $File->Ime != "" )
					echo "<B>$File->Ime</B>";
				echo ($File->Opis!='' ? '<div class="a9">'. strip_tags($File->Opis) .'</div>' : '');
				echo "</A>";
				echo "</div>\n";
			}
		}
		echo "\t</div>\n";
		echo "</div>\n";
	}
}

// display related texts links
if ( $Dodatni && count($Dodatni) > 0 ) {
	echo "<div class=\"related\">";
	echo "\t<div class=\"head\">". multiLang("<SeeAlso>", $lang) ."</div>\n";
	echo "\t<div class=\"body\">\n";
	foreach ( $Dodatni as $Dodatno ) {
		// find first category ID of text
		$rub = $db->get_row(
			"SELECT
				KB.KategorijaID,
				K.Ime,
				B.Tip
			FROM
				KategorijeBesedila KB
				LEFT JOIN Kategorije K ON KB.KategorijaID = K.KategorijaID
				LEFT JOIN Besedila B ON KB.BesediloID = B.BesediloID
			WHERE
				KB.BesediloID = ". (int)$Dodatno->ID ."
			ORDER BY
				KB.ID
			LIMIT 1"
			);

		$kat = ($TextPermalinks) ? ($IsIIS ? $WebFile .'/' : ''). $rub->Ime .'/' : '?kat='. $rub->KategorijaID;
		$bid = ($TextPermalinks) ? $Dodatno->Ime .'/' : '&amp;ID='. $Dodatno->ID;
		echo "\t<a href=\"$WebPath/$kat". $bid ."\">". $Dodatno->Naslov ."</a>\n";
		
		unset($rub);
	}
	echo "\t</div>\n";
	echo "</div>\n";
}

// permalinks
$kat = ($TextPermalinks) ? ($IsIIS ? "$WebFile/" : ''). $KatText .'/' : '?kat='. $_GET['kat'];
$bid = ($TextPermalinks) ? $Tekst->Ime .'/' : '&amp;ID='. $_GET['ID'];
$URL = $WebURL .'/'. $kat . $bid;

// add social buttons
echo "<DIV CLASS=\"social\">\n";
// twitter
echo "<a href=\"https://twitter.com/share\" class=\"twitter-share-button\" data-url=\"". $URL ."\"";
if ( isset($TweetText) && $TweetText != "" )
	echo " data-text=\"". $TweetText ."\"";
if ( isset($TwitterName) )
	echo " data-via=\"". $TwitterName ."\"";
echo ">Tweet</a>\n";
// facebook
echo "<iframe src=\"http://www.facebook.com/plugins/like.php?href=". urlencode($URL) ."&amp;layout=button_count&amp;show_faces=false&amp;action=like&amp;colorscheme=light\"";
echo " scrolling=\"no\" frameborder=\"0\" style=\"border:none;overflow:hidden;width:120px;height:20px;\" allowTransparency=\"true\"></iframe>\n";
// google+
echo "<div class=\"g-plusone\" data-size=\"medium\"></div>\n";
echo "<script type=\"text/javascript\">";
echo "(function(){";
echo "var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;";
echo "po.src = 'https://apis.google.com/js/plusone.js';";
echo "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);";
echo "})();";
echo "</script>\n";
echo "</DIV>\n";

// navigation buttons
echo "<TABLE class=\"navbutton list\" BORDER=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
echo "<TR>\n";
echo "\t<TD ALIGN=\"left\">\n";
if ( $PrevPost )
	echo "\t<A HREF=\"$WebPath/$kat". ($TextPermalinks ? '?':'&amp;') ."ID=". $PrevPost ."\">&laquo;&nbsp;". multiLang('<Prev>', $lang) ."</A>\n";
echo "\t</TD>\n";
echo "\t<TD ALIGN=\"right\">\n";
if ( $NextPost )
	echo "\t<A HREF=\"$WebPath/$kat". ($TextPermalinks ? '?':'&amp;') ."ID=". $NextPost ."\">". multiLang('<Next>', $lang) ."&nbsp;&raquo;</A>\n";
echo "\t</TD>\n";
echo "</TR>\n";
echo "</TABLE>\n";
?>