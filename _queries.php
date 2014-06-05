<?php
/* _queries.php - Common SQL queries.
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend)                                             |
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

// top categories (menu)
$Rubrike = $db->get_results(
	"SELECT
		K.KategorijaID AS ID,
		K.Ime,
		KN.Naziv,
		KN.Povzetek
	FROM
		Kategorije K
		LEFT JOIN KategorijeNazivi KN
			ON K.KategorijaID=KN.KategorijaID
	WHERE
		K.Izpis<>0
		AND K.Ime NOT LIKE '.%'
		AND K.KategorijaID LIKE '__'
		AND (KN.Jezik='". $lang ."' OR KN.Jezik IS NULL)
	ORDER BY
		K.KategorijaID,
		KN.Jezik DESC"
	);

// category title & description
$Kat = $db->get_row(
	"SELECT
		K.KategorijaID,
		K.Ime,
		KN.Naziv,
		KN.Opis,
		KN.Povzetek
	FROM
		Kategorije K
		LEFT JOIN KategorijeNazivi KN
			ON K.KategorijaID = KN.KategorijaID
	WHERE
		K.KategorijaID = '". $db->escape($_GET['kat']) ."'
		AND (KN.Jezik = '". $lang ."' OR KN.Jezik IS NULL)
	ORDER BY
		KN.Jezik DESC
	LIMIT 1"
	);
// get kategory text for permalinks
$KatText     = $Kat->Ime;
$KatFullText = $Kat->Naziv;

// subcategories, first level (non displayable are shown) :-)
$PodRubrike = $db->get_results(
	"SELECT
		K.KategorijaID,
		K.Ime,
		K.Slika,
		KN.Naziv,
		KN.Opis,
		KN.Povzetek
	FROM
		Kategorije K
		LEFT JOIN KategorijeNazivi KN
			ON K.KategorijaID = KN.KategorijaID
	WHERE
		K.Izpis = 0
		AND K.Ime NOT LIKE '.%'
		AND K.KategorijaID LIKE '". $db->escape($_GET['kat']) ."__'
		AND (KN.Jezik = '". $lang ."' OR KN.Jezik IS NULL)
	ORDER BY
		K.KategorijaID,
		KN.Jezik DESC"
	);

// image thumbnail parameters (for resizing & ad-hoc thumbnails)
$GalleryBase  = 'gallery';
$DefPicSize   = 640;
$DefThumbSize = 64;
$MaxPicSize   = 1024;
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
		ST.Jezik
	LIMIT 1"
	);
if ( $x ) {
	$GalleryBase  = $x->GalleryBase;
	$DefPicSize   = (int)$x->DefPicSize;
	$DefThumbSize = (int)$x->DefThumbSize;
	$MaxPicSize   = (int)$x->MaxPicSize;
}
unset($x);

//single post/text
if ( isset($_GET['ID']) && (int)$_GET['ID'] > 0 ) {

	// get text pages
	$Teksti = $db->get_results(
		"SELECT
			B.BesediloID AS ID,
			B.Datum,
			B.DatumObjave,
			B.DatumSpremembe,
			B.Slika,
			B.Ime,
			B.Tip,
			B.ForumTopicID,
			BO.Naslov,
			BO.Podnaslov,
			BO.Povzetek,
			BO.Opis,
			U.Username,
			U.Name,
			U.TwitterName
		FROM
			Besedila B
			LEFT JOIN BesedilaOpisi BO ON B.BesediloID = BO.BesediloID
			LEFT JOIN SMUser U ON B.Avtor = U.UserID
		WHERE
			(BO.Jezik='". $lang ."' OR BO.Jezik IS NULL)
			AND B.BesediloID = ". (int)$_GET['ID'] .
			(isset($_GET['pg']) ? " AND BO.Polozaj = ". max(999,min(1,(int)$_GET['pg'])) : "") ."
		ORDER BY
			BO.Jezik,
			BO.Polozaj"
		);

	// get text tags
	$Tags = $db->get_results(
		"SELECT
			T.TagID,
			T.TagName
		FROM
			BesedilaTags BT
			LEFT JOIN Tags T ON BT.TagID = T.TagID
		WHERE
			BT.BesediloID = ". (int)$_GET['ID'] ."
		ORDER BY
			T.TagName"
		);
	
	$Polozaj = $db->get_var(
		"SELECT
			Polozaj
		FROM
			KategorijeBesedila
		WHERE
			KategorijaID='".  $db->escape($_GET['kat']) ."'
			AND BesediloID=". (int)$_GET['ID']
		);
	// select previous post
	$PrevPost = $db->get_var(
		"SELECT
			KB.BesediloID
		FROM
			KategorijeBesedila KB
			LEFT JOIN BesedilaOpisi BO ON BO.BesediloID = KB.BesediloID
		WHERE
			KB.KategorijaID = '". $db->escape($_GET['kat']) ."'
			AND KB.Polozaj < ". $Polozaj ."
			AND (BO.Jezik = '". $lang ."' OR BO.Jezik IS NULL)
		ORDER BY
			KB.Polozaj DESC
		LIMIT 1"
		);
	// select next post
	$NextPost = $db->get_var(
		"SELECT
			KB.BesediloID
		FROM
			KategorijeBesedila KB
			LEFT JOIN BesedilaOpisi BO ON BO.BesediloID = KB.BesediloID
		WHERE
			KB.KategorijaID = '". $db->escape($_GET['kat']) ."'
			AND KB.Polozaj > ". $Polozaj ."
			AND (BO.Jezik = '". $lang ."' OR BO.Jezik IS NULL)
		ORDER BY
			KB.Polozaj
		LIMIT 1"
		);
	unset($Polozaj);
	
	// get gallery photos
	$Galerija = $db->get_results(
		"SELECT
			BS.ID,
			BS.BesediloID,
			B.Ime,
			M.MediaID,
			M.Datoteka,
			M.Naziv,
			M.Meta,
			MO.Naslov,
			MO.Opis
		FROM
			BesedilaSlike BS
			LEFT JOIN Media M ON BS.MediaID = M.MediaID
			LEFT JOIN MediaOpisi MO	ON M.MediaID = MO.MediaID
			LEFT JOIN Besedila B ON B.BesediloID = BS.BesediloID
		WHERE
			BS.BesediloID = ". (int)$_GET['ID'] ."
			AND (MO.Jezik='". $lang ."' OR MO.Jezik IS NULL)
		ORDER BY
			BS.Polozaj DESC"
		);

	// get attached media
	$Media = $db->get_results(
		"SELECT
			M.MediaID,
			MO.Naslov,
			M.Datoteka,
			M.Velikost,
			M.Slika,
			M.Tip,
			M.Naziv as Ime,
			MO.Opis
		FROM BesedilaMedia BM
			LEFT JOIN Media M
				ON BM.MediaID = M.MediaID
			LEFT JOIN MediaOpisi MO
				ON BM.MediaID = MO.MediaID
		WHERE
			BM.BesediloID = ". (int)$_GET['ID'] ."
			AND (MO.Jezik='". $lang ."' OR MO.Jezik IS NULL)
			AND M.Izpis <> 0
		ORDER BY
			BM.Polozaj"
		);

	// get related texts links
	$Dodatni = $db->get_results(
		"SELECT
			BS.DodatniID AS ID,
			B.Ime,
			BO.Naslov
		FROM
			BesedilaSkupine BS
			LEFT JOIN BesedilaOpisi BO ON BS.DodatniID = BO.BesediloID
			LEFT JOIN Besedila B ON BS.DodatniID = B.BesediloID
		WHERE
			BS.BesediloID = ". (int)$_GET['ID'] ."
			AND (BO.Jezik='". $lang ."' OR BO.Jezik IS NULL)
			AND BO.Polozaj = 1
		ORDER BY
			BS.Polozaj"
		);

} else {

	// dynamic filter
	$Filter = "";
	if ( isset($_GET['ar']) ) {
		$StartD  = mktime(0, 0, 0, (int)substr($_GET['ar'], 0, 2), 1, (int)substr($_GET['ar'], strpos($_GET['ar'], ".")+1, 4));
		$EndD    = mktime(0, 0, 0, (int)substr($_GET['ar'], 0, 2)+1, 1, (int)substr($_GET['ar'], strpos($_GET['ar'], ".")+1, 4));
		$Filter .= "AND B.Datum BETWEEN '". date( "Y-m-d", $StartD ) ."' AND '". date( "Y-m-d", $EndD ) ."'";
	}
	if ( isset( $_GET['tag'] ) ) {
		$Filter .= "AND BT.TagID=".(int)$_GET['tag'];
	}

	// texts from category
	$Besedila = $db->get_results(
		"SELECT DISTINCT
			B.BesediloID AS ID,
			B.Ime,
			B.Slika,
			B.Datum,
			B.Tip,
			B.ForumTopicID
		FROM
			KategorijeBesedila KB
			LEFT JOIN Besedila B
				ON KB.BesediloID = B.BesediloID
				INNER JOIN BesedilaOpisi BO
					ON B.BesediloID = BO.BesediloID
			LEFT JOIN BesedilaTags BT
				ON KB.BesediloID = BT.BesediloID
		WHERE
			B.Izpis <> 0
			AND KB.KategorijaID = '" . $db->escape($_GET['kat']) . "'
			AND (BO.Jezik = '". $lang ."' OR BO.Jezik IS NULL)
			$Filter
		ORDER BY
			KB.Polozaj"
		);
}

// get menu, content & extra structure
$menu = false;
$extra = false;

// get primary sidebar (navigation) structure
if ( !$Mobile && stripos($_SERVER['QUERY_STRING'], "nomenu") === false ) {
	$rub = $_GET['kat'];
	do { // loop over category hierarchy, if not defined for current category
		$TemplateMenu = $db->get_results(
			"SELECT
				P.Datoteka,
				KV.Polozaj
			FROM
				KategorijeVsebina KV
				LEFT JOIN Predloge P ON KV.PredlogaID = P.PredlogaID
			WHERE
				KV.KategorijaID = '". $db->escape($rub) ."'
				AND KV.Ekstra = 2
				AND P.Enabled <> 0
				AND (P.Jezik='". $lang ."' OR P.Jezik IS NULL)
			ORDER BY
				KV.Polozaj"
			);
		$rub = left($rub, strlen($rub)-2);
	} while ( count($TemplateMenu) == 0 && strlen($rub) >= 2 );
	$menu = count($TemplateMenu);
}

// get secondary sidebar (extra) structure
if ( !$Mobile && stripos($_SERVER['QUERY_STRING'], "noextra") === false ) {
	$rub = $_GET['kat'];
	do { // loop over category hierarchy, if not defined for current category
		$TemplateExtra = $db->get_results(
			"SELECT
				P.Datoteka,
				KV.Polozaj
			FROM
				KategorijeVsebina KV
				LEFT JOIN Predloge P ON KV.PredlogaID = P.PredlogaID
			WHERE
				KV.KategorijaID = '". $db->escape($rub) ."'
				AND KV.Ekstra=1
				AND P.Enabled <> 0
				AND (P.Jezik='". $lang ."' OR P.Jezik IS NULL)
			ORDER BY
				KV.Polozaj"
			);
		$rub = left($rub, strlen($rub)-2);
	} while ( count($TemplateExtra) == 0 && strlen($rub) >= 2 );
	$extra = count($TemplateExtra);
}

// define $PageWidth (for CSS and other calculations)
$PageWidth = $ContentW + ($menu?$MenuW:0) + ($extra?$ExtraW:0);
