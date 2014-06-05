<?php
/*~ RSS.php - RSS feed generation
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

/******************************************************************************
* Generate RSS feed from recent blog posts (kat=01)
******************************************************************************/

// include general application framework
include(dirname(__FILE__) .'/_application.php');

// set the HTTP header
header("Content-Type: application/rss+xml; charset=utf-8");

// get the current timestamp in RFC 2822 format
$nowdt = date('r', time());

echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
echo "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
echo "\t<channel>\n";
echo "\t\t<atom:link href=\"". $WebURL ."/RSS.php\" rel=\"self\" />\n";
echo "\t\t<title>". multiLang('<Title>', $lang) ."</title>\n";
echo "\t\t<link>". $WebURL ."</link>\n";
echo "\t\t<description>". multiLang('<Description>', $lang) ."</description>\n";
echo "\t\t<lastBuildDate>". $nowdt ."</lastBuildDate>\n";
echo "\t\t<language>". langCode(langDefault()) ."</language>\n";
echo "\t\t<managingEditor>". $PostMaster ." (". $PMasterRealName .")</managingEditor>\n";
echo "\t\t<webMaster>". $PostMaster ." (". $PMasterRealName .")</webMaster>\n";

$novosti = $db->get_var( "SELECT K.KategorijaID FROM Kategorije K WHERE K.Ime IN ('Novosti','News')" );

if ( $novosti != "" ) {
	$Slike = $db->get_results(
		"SELECT
			M.MediaID,
			M.Datoteka,
			M.Datum
		FROM
			Media M
		WHERE
			M.Tip = 'PIC'
		ORDER BY
			M.Datum DESC");

	$dst = date('r', sqldate2time($Slike[0]->Datum));

	echo "\t\t<item>\n";
	echo "\t\t\t<title>Zadnje slike</title>\n";
	echo "\t\t\t<link>". $WebURL ."/?kat=". $novosti ."&amp;pg=1</link>\n";
	echo "\t\t\t<guid isPermaLink=\"true\">". $WebURL ."/?kat=". $novosti ."&amp;pg=1</guid>\n";
	echo "\t\t\t<pubDate>$dst</pubDate>\n";
	echo "\t\t\t<description><![CDATA[\n";

	if ( $Slike ) {
		$i = 5;
		foreach ($Slike as $Slika) {
			$Galerija = $db->get_row(
				"SELECT
					BS.ID,
					BS.BesediloID
				FROM
					Media M
					LEFT JOIN BesedilaSlike BS
						ON BS.MediaID = M.MediaID
				WHERE
					M.MediaID = ". $Slika->MediaID);
		
			$sRoot = dirname(__FILE__);
			$sPath = dirname($sRoot ."/". $Slika->Datoteka);
			$rPath = str_replace("\\", "/", right($sPath, strlen($sPath)-strlen($sRoot)-1));
			$sFile = basename($Slika->Datoteka);
			echo "\t\t\t<A HREF=\"". $WebURL ."/?kat=". $novosti ."\"><img src=\"". $WebURL ."/". $rPath ."/thumbs/". $sFile ."\"></A>\n";
			if ( --$i < 1 ) break;
		}
	}

	echo "\t\t\t]]></description>\n";
	echo "\t\t</item>\n";
}

$BlogMaxPosts = 7;
$BlogMaxDays = 30;
$x = $db->get_row(
	"SELECT
		S.SifNVal1 AS MaxPosts,
		S.SifNVal2 AS MaxDays
	FROM
		Sifranti S
		LEFT JOIN SifrantiTxt ST ON S.SifrantID=ST.SifrantID
	WHERE
		S.SifrCtrl = 'PARA'
		AND S.SifrText = 'BlogPosts'
		AND (ST.Jezik='" . $lang . "' OR ST.Jezik IS NULL)");

if ( $x && isset($x->MaxPosts) ) $BlogMaxPosts = $x->MaxPosts;
if ( $x && isset($x->MaxDays) )  $BlogMaxDays = $x->MaxDays;

$blog = $db->get_var( "SELECT K.KategorijaID FROM Kategorije K WHERE K.Ime = 'Blog'" );

$Lista = $db->get_results(
	"SELECT DISTINCT
		B.BesediloID,
		B.Ime,
		B.DatumSpremembe,
		B.Slika
	FROM
		KategorijeBesedila KB
		LEFT JOIN Besedila B
			ON KB.BesediloID = B.BesediloID
			INNER JOIN BesedilaOpisi BO
				ON B.BesediloID = BO.BesediloID
	WHERE
		KB.KategorijaID = '". $blog ."'
		AND B.Izpis <> 0
		AND (BO.Jezik='Sl' OR BO.Jezik IS NULL)
	ORDER BY
		KB.Polozaj DESC");

foreach ( $Lista as $dslist ) {
	// get text pages (for mobile client just get first page of text)
	$Tekst = $db->get_row(
		"SELECT
			BO.Naslov,
			BO.Podnaslov,
			BO.Povzetek
		FROM
			BesedilaOpisi BO
		WHERE
			BO.BesediloID = ". (int)$dslist->BesediloID ."
			AND (BO.Jezik='$lang' OR BO.Jezik IS NULL)
		ORDER BY
			BO.Jezik,
			BO.Polozaj
		LIMIT 1"
		);

	if ( left($Tekst->Naslov,1) == '.' ) continue;
	$kat = ($TextPermalinks) ? ($IsIIS ? $WebFile .'/' : ''). 'blog/' : '?kat='. $blog;
	$bid = ($TextPermalinks) ? $dslist->Ime .'/?lng='. langDefault() : '&amp;ID='. $dslist->BesediloID .'&amp;lng='. langDefault();
	$dst = date("r", sqldate2time($dslist->DatumSpremembe));
	echo "\t\t<item>\n";
	echo "\t\t\t<title>". htmlspecialchars($Tekst->Naslov) ."</title>\n";
	echo "\t\t\t<link>". $WebURL ."/". $kat . $bid ."</link>\n";
	echo "\t\t\t<guid isPermaLink=\"true\">" . $WebURL . "/index.php?kat=". $blog ."&amp;ID=". $dslist->BesediloID ."&amp;lng=". langDefault() ."</guid>\n";
	echo "\t\t\t<pubDate>$dst</pubDate>\n";
	echo "\t\t\t<description><![CDATA[". $Tekst->Povzetek ."]]></description>\n";
	echo "\t\t</item>\n";
	if ( --$BlogMaxPosts < 1 ) break;
}

echo "\t</channel>\n";
echo "</rss>\n";
?>
