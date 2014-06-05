<?php
/*~ index.php - main page of application framework
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
* Creates framework for include files (from 'template' folder) which define
* application design.
******************************************************************************/

// include application variables and settings framework
require_once(dirname(__FILE__) ."/_application.php");

// deal with permalinks
function strip_reqURI($RequestURI, $Pathinfo)
{
	global $WebPath, $WebFile;

	// remove home path from front and path info from end
	$RequestURI = explode('?', $RequestURI); // remove query string from URI
	$RequestURI = trim($RequestURI[0],'/'); // strip slashes
	$RequestURI = trim(str_replace(trim($Pathinfo,'/'), '', $RequestURI), '/'); // remove pathinfo
	$RequestURI = preg_replace('|^'.trim($WebPath,'/').'|i', '', $RequestURI); // remove home path from front
	$RequestURI = str_replace($WebFile, '', $RequestURI); // remove script name
	return trim($RequestURI, '/');
}

function strip_pathinfo($Pathinfo)
{
	global $WebPath;

	$Pathinfo = explode('?', $Pathinfo); // remove query string from pathinfo
	$Pathinfo = trim($Pathinfo[0], '/');
	$Pathinfo = trim(str_replace($WebPath, '', $Pathinfo), '/');
	$Pathinfo = trim(preg_replace('|^'.trim($WebPath,'/').'|i', '', $Pathinfo), '/');
	return $Pathinfo;
}

$Pathinfo = strip_pathinfo(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : "");
$Req_URI  = strip_reqURI($_SERVER['REQUEST_URI'],$Pathinfo);
// The requested permalink is in $Pathinfo for path info requests and $Req_URI for other requests.
$request = !empty($Pathinfo) ? $Pathinfo : $Req_URI;

if ( $request != "" ) {
	$Permalink_arr = explode('/', $request); // [0]=category name,[1]=post name,[2]=invalid
	
	if ( $Permalink_arr[0] != '' ) {
		$katname = urldecode($Permalink_arr[0]);
		$kat = $db->get_var("SELECT KategorijaID FROM Kategorije WHERE Ime='". $db->escape($katname) ."'");
		if ( $kat != "" )
			$_GET['kat'] = $kat;
	}
	
	if ( count($Permalink_arr)>1 && $Permalink_arr[1] != '' ) {
		$ime = urldecode($Permalink_arr[1]);
		if (strtolower($katname) === 'find' || strtolower($katname) === 'iskanje') {
			$_GET['S'] = $ime;
		} elseif (left($ime,3) == 'TAG') {
			$_GET['tag'] = substr($ime, 3);
		} elseif (left($ime,2) == 'AR') {
			$_GET['ar'] = substr($ime, 2);
		} else {
			$ID = (int)$db->get_var("SELECT BesediloID FROM Besedila WHERE Ime = '" . $db->escape($ime) ."'");
			if ( $ID > 0 ) {
				$_GET['ID'] = $ID;
				$kat = $db->get_var(
					"SELECT
						KB.KategorijaID
					FROM
						Besedila B
						LEFT JOIN KategorijeBesedila KB ON B.BesediloID = KB.BesediloID
						LEFT JOIN Kategorije K ON KB.KategorijaID = K.KategorijaID
					WHERE
						B.Izpis <> 0
						AND B.Tip IN ('Besedilo','Blog')
						AND K.Izpis <> 0
						AND B.BesediloID = ". $ID ."
					ORDER BY
						KB.KategorijaID,
						B.Datum DESC,
						B.BesediloID DESC
					LIMIT 1"
					);
				if ( !isset($_GET['kat']) && $kat != "" )
					$_GET['kat'] = $kat;
			}
		}
	}
}

// default to first kategory
if ( !isset($_GET['kat']) ) {
	$_GET['kat'] = '00';
	// process single ID parameter
	if ( isset($_GET['ID']) && $_GET['ID'] > 0 ) {
		$kat = $db->get_var(
			"SELECT
				KB.KategorijaID
			FROM
				Besedila B
				LEFT JOIN KategorijeBesedila KB ON B.BesediloID = KB.BesediloID
				LEFT JOIN Kategorije K ON KB.KategorijaID = K.KategorijaID
			WHERE
				B.BesediloID = ". (int)$_GET['ID'] ."
			ORDER BY
				KB.KategorijaID,
				B.Datum DESC,
				B.BesediloID DESC
			LIMIT 1"
			);
		if ( $kat != "" )
			$_GET['kat'] = $kat;
	}
}

// redirect if necessary
if ( $_GET['kat'] == '00' && !isset($_GET['tmpl']) ) {
	// check if we display Home category (00)
	$redirect = !(bool)$db->get_var("SELECT Izpis FROM Kategorije WHERE KategorijaID='00'");
	// find the Blog category
	$blog = $db->get_var( "SELECT K.KategorijaID FROM Kategorije K WHERE K.Ime IN ('Blog','blog','BLOG') AND K.Izpis<>0" );
	if ( $blog && $redirect ) {
		// jump to found category
		header("Location: $WebURL/". ($TextPermalinks ? ($IsIIS ? $WebFile .'/' : '').'blog/' : '?kat='. $blog));
		die();
	} elseif ( $redirect ) {
		// default to first displayable category
		$kat = $db->get_row( "SELECT K.KategorijaID,K.Ime FROM Kategorije K WHERE K.Izpis<>0 ORDER BY K.KategorijaID LIMIT 1" );
		header("Location: $WebURL/". ($TextPermalinks ? ($IsIIS ? $WebFile .'/' : ''). $rub->Ime .'/' : '?kat='. $kat->KategorijaID));
		die();
	}
}

// include general queries
include_once( dirname(__FILE__) ."/_queries.php" );

echo "<!DOCTYPE HTML>\n";
echo "<HTML>\n";
echo "<HEAD>\n";
if ( isset($_GET['ID']) && (int)$_GET['ID'] > 0 ) {
	$TitleText = multiLang('<Title>', $lang) ." - ". ($Teksti[0]->Naslov=='' ? $Teksti[0]->Ime : $Teksti[0]->Naslov);
} else {
	$TitleText = multiLang('<Title>', $lang) ." - ". ($KatFullText=='' ? $KatText : $KatFullText);
}
include_once( dirname(__FILE__) ."/_htmlheader.php" );
echo "</HEAD>\n";

echo "<BODY>\n";

// EU cookie compliance (Google Analytics & forum support)
if ( !isset($_COOKIE['accept_cookies']) && strncasecmp($WebURL, $_SERVER['HTTP_REFERER'], strlen($WebURL)) == 0 ) {
 	// get ID of special category for cookies disclaimer/description
	$kat = $db->get_var("SELECT KategorijaID FROM Kategorije WHERE Ime = 'Cookies'");
	if ( $_GET['kat'] != $kat ) {
		// continuing browsing -> implicit consent
		setcookie('accept_cookies', 'yes', time()+31536000, $WebPath);
		$_COOKIE['accept_cookies'] = 'yes';
	}
}
if ( !isset($_COOKIE['accept_cookies']) && defined('ANALYTICS_ID') ) {
	// display cookie warning
	include_once(dirname(__FILE__) ."/_cookies.php");
} else if ( isset($_COOKIE['accept_cookies']) && $_COOKIE['accept_cookies'] == "no" ) {
	// redirect to Google if not accepting
	header( "Refresh:1; URL=http://www.google.com?q=http+cookie" );
	die();
}

echo "<div id=\"body\">\n";

echo "<div id=\"head\">\n";
include_once(dirname(__FILE__) ."/_head.php");
echo "</div>\n";

echo "<div id=\"content\">\n";
if ( isset($_GET['tmpl']) && $_GET['tmpl'] != "" ) {
	// template defined explicitly
	if ( strpos($_GET['tmpl'], ".php") > 0 )
		$Datoteka = $_GET['tmpl'];
	else {
		$Datoteka = $db->get_var(
			"SELECT
				Datoteka
			FROM
				Predloge
			WHERE
				Naziv = '" . $db->escape($_GET['tmpl']) . "'
				AND Enabled <> 0"
		);
	}
	if ( is_file(dirname(__FILE__) ."/template/". $Datoteka) ) {
		echo "<!-- $Datoteka -->\n";
		include( dirname(__FILE__) ."/template/". $Datoteka );
	}
} else {
	// template defined implicitly in category
	$Kategorija = $_GET['kat'];
	// loop over category hierarchy, if not defined for current kategory
	do {
		$TemplateContent = $db->get_results(
			"SELECT
				P.Datoteka,
				KV.Polozaj
			FROM
				KategorijeVsebina KV
					LEFT JOIN Predloge P
						ON KV.PredlogaID = P.PredlogaID
			WHERE
				KV.KategorijaID = '$Kategorija'
				AND
				KV.Ekstra=0
				AND
				P.Enabled <> 0
				AND
				(P.Jezik='$lang' OR P.Jezik IS NULL)
			ORDER BY
				KV.Polozaj"
			);
		$Kategorija = left( $Kategorija, strlen( $Kategorija )-2 );
	} while ( count($TemplateContent) == 0 && strlen($Kategorija) >= 2 );

	if ( $TemplateContent ) {
		if ( $db->num_rows != 0 ) {
			foreach ( $TemplateContent as $Template ) {
				echo "\t<!-- $Template->Datoteka -->\n";
				if ( is_file(dirname(__FILE__) ."/template/". $Template->Datoteka) ) {
					include(dirname(__FILE__) ."/template/". $Template->Datoteka);
				}
			}
		}
	}
}
echo "</div>\n";

// include menu items (left extras)
if ( !$Mobile && stripos($_SERVER['QUERY_STRING'], "nomenu") === false ) {
	if ( $TemplateMenu ) { // defined in _application_php
		echo "<div id=\"navigation\">\n";
		if ( count($TemplateMenu) != 0 ) {
			foreach ( $TemplateMenu as $Template ) {
				echo "\t<!-- $Template->Datoteka -->\n";
				if ( is_file(dirname(__FILE__) ."/template/". $Template->Datoteka) ) {
					include(dirname(__FILE__) ."/template/". $Template->Datoteka);
				}
			}
		}
		echo "</div>\n";
	}
}

// include extra items (right extras)
if ( !$Mobile && stripos($_SERVER['QUERY_STRING'], "noextra") === false ) {
	// noextra does not exist in URL
	if ( $TemplateExtra ) { // defined in _application_php
		echo "<div id=\"extras\">\n";
		if ( count($TemplateExtra) != 0 ) {
			foreach ( $TemplateExtra as $Template ) {
				echo "\t<!-- $Template->Datoteka -->\n";
				if ( is_file(dirname(__FILE__) ."/template/". $Template->Datoteka) )
					include(dirname(__FILE__) ."/template/". $Template->Datoteka);
			}
		}
		echo "</div>\n";
	}
}

echo "<div id=\"foot\">\n";
include_once(dirname(__FILE__) ."/_foot.php");
echo "</div>\n";

echo "</div>\n";

if ( defined('ANALYTICS_ID') && isset($_COOKIE['accept_cookies']) && $_COOKIE['accept_cookies']=='yes' ) {
	// google analytics
	echo "<script type=\"text/javascript\">\n";
	echo "var gaJsHost = ((\"https:\" == document.location.protocol) ? \"https://ssl.\" : \"http://www.\");\n";
	echo "document.write(unescape(\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));\n";
	echo "</script>\n";
	echo "<script type=\"text/javascript\">\n";
	echo "try {\n";
	echo "var pageTracker = _gat._getTracker(\"". ANALYTICS_ID ."\");\n";
	echo "pageTracker._trackPageview();\n";
	echo "} catch(err) {}</script>\n";
}
// retina support for mobile devices
if ( $Mobile || $Tablet ) {
	echo "<script language=\"javascript\" type=\"text/javascript\" src=\"$js/retina/retina.js\"></script>\n";
}

echo "</BODY>\n";
echo "</HTML>\n";
?>