<?php
/*~ search.php - AJAX search framework
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

// include application variables and settings framework
require_once(dirname(__FILE__) ."/_application.php");

// include a regular search template
$Kategorija = $db->get_var("SELECT KategorijaID FROM Kategorije WHERE Ime IN ('Iskanje','Search') LIMIT 1");
$_GET['kat'] = $Kategorija;

if ( isset($_GET['format']) && $_GET['format'] == 'JSON' ) {
	// set correct content-type
	header('Content-type: text/JSON');
	// find template; loop over category hierarchy, if not defined for current kategory
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
				KV.KategorijaID = '". $Kategorija ."'
				AND KV.Ekstra=0
				AND P.Enabled <> 0
				AND (P.Jezik='$lang' OR P.Jezik IS NULL)
			ORDER BY
				KV.Polozaj
			LIMIT 1"
			);
		$Kategorija = left($Kategorija, strlen($Kategorija)-2);
	} while ( count($TemplateContent) == 0 && strlen($Kategorija) >= 2 );

	if ( $TemplateContent ) {
		if ( $db->num_rows != 0 ) {
			foreach ( $TemplateContent as $Template ) {
				if ( is_file(dirname(__FILE__) ."/template/". $Template->Datoteka) ) {
					include(dirname(__FILE__) ."/template/". $Template->Datoteka);
				}
			}
		}
	}
} else {
	// duplicate index.php behaviour
	include_once(dirname(__FILE__) ."/_queries.php");

	echo "<!DOCTYPE HTML>\n";
	echo "<HTML>\n";

	echo "<HEAD>\n";
	include_once(dirname(__FILE__) ."/_htmlheader.php");
	echo "</HEAD>\n";

	echo "<BODY>\n";
	echo "<div id=\"body\">\n";

	echo "<!-- head -->\n";
	echo "<div id=\"head\">\n";
	include_once(dirname(__FILE__) ."/_head.php");
	echo "</div>\n";

	echo "<!-- content -->\n";
	echo "<div id=\"content\">\n";
	if ( isset($_GET['tmpl']) && $_GET['tmpl'] != "" ) {
		// use template defined explicitly
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
			echo "\t<!-- $Datoteka -->\n";
			include( dirname(__FILE__) ."/template/". $Datoteka);
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
		echo "<!-- menu -->\n";
		if ( $TemplateMenu ) { // defined in _application_php
			echo "<div id=\"navigation\">\n";
			if ( $db->num_rows != 0 ) {
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
		echo "<!-- extra -->\n";
		if ( $TemplateExtra ) { // defined in _application_php
			echo "<div id=\"extras\">\n";
			if ( $db->num_rows != 0 ) {
				foreach ( $TemplateExtra as $Template ) {
					echo "\t<!-- $Template->Datoteka -->\n";
					if ( is_file(dirname(__FILE__) ."/template/". $Template->Datoteka) )
						include(dirname(__FILE__) ."/template/". $Template->Datoteka);
				}
			}
			echo "</div>\n";
		}
	}

	echo "<!-- foot -->\n";
	echo "<div id=\"foot\">\n";
	include_once(dirname(__FILE__) ."/_foot.php");
	echo "</div>\n";

	if ( defined('ANALYTICS_ID') ) {
		echo "<!-- google analytics -->\n";
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

	echo "</div><!-- body -->\n";
	echo "</BODY>\n";
	echo "</HTML>\n";
}
?>