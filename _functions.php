<?php
/*~ _functions.php
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

/**************************************
* fileExists - case (in)sensitive file_exists variant
*--------------------------------------
* @parameter string - filename with full path
* @parameter boolean - case sensitivity toggle
* @returns string/boolean - filename if found/false otherwise
**************************************/
function fileExists($fileName, $caseSensitive=false)
{
	if ( left($fileName,5)=='data:' || left($fileName,4)=='http' || left($fileName,3)=='ftp' ) {
		return false;
	}

	// handle UTF8 in filename on Windows systems
	$fileName = ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? DecodeUTF8($fileName) : $fileName);

    if ( file_exists($fileName) ) {
        return basename($fileName);
    }
    if ( $caseSensitive ) return false;

    // Handle case insensitive requests
    $directoryName = dirname($fileName);
    $fileArray = glob($directoryName . '/*', GLOB_NOSORT);
    $fileNameLowerCase = strtolower($fileName);
    if ( $fileArray ) foreach ($fileArray as $file) {
        if (strtolower($file) == $fileNameLowerCase) {
            return $file;
        }
    }
    return false;
}

/**************************************
* retinaImgExists - check if retina image exists
*--------------------------------------
* @parameter string - filename with full path
* @parameter boolean - case sensitivity toggle
* @returns string/boolean - filename if found/false otherwise
**************************************/
function retinaImgExists($file, $caseSensitive=false)
{
	$ext    = strrchr($file, '.');
	$name   = left($file, strlen($file)-4);
	if ( right($name, 3) === '@2x' ) return false;
	return fileExists($name.'@2x'.$ext, $caseSensitive);
}

/**************************************
* AddLightboxLink - find <IMG> tag in string and surround it with <A HREF=... REL="fancybox">
*--------------------------------------
* @parameter string - input string
* @parameter string - additional ID for lightbox grouping
* @parameter string - (relative) folder with larger images
* @returns string - input string with inserted <A ...> tags
**************************************/
function AddLightboxLink($str, $ID="", $folder="large")
{
	global $StoreRoot, $WebPath, $WebURL;

	if ( $ID != "" ) $ID = "_" . $ID . "_";

	preg_match_all("/<img[^>]*>/i", $str, $aRes);	// find all instances of <IMG> in string
	foreach ( $aRes[0] as $img ) { // $aRes[0] contains all instances, there is no $aRes[1] since we do not have () in search
		preg_match("/ALT=\"([^\"]*)\"/i", $img, $aAlt); // find ALT= content
		preg_match("/SRC=\"((?!(?:http|data))[^\"]*)\"/i", $img, $aSrc); // find SRC= content
		$sSrc = $aSrc[1]; // SRC="" content (without SRC="")
		$sAlt = $aAlt[1]; // ALT="" content
		if ( $sSrc != "" ) {
			$rPath = dirname($sSrc); // web relative path
			if ( $WebPath!="" ) $sSrc  = str_replace($WebPath, '', $sSrc); // remove base
			$sPath = dirname($StoreRoot .'/'. $sSrc); // filesystem path
			$sName = basename($sSrc); // filename
			$sAlt  = $sAlt=="" ? $sName : $sAlt;
			// check if large file exists
			if ( fileExists($sPath .'/'. $folder .'/'. $sName) ) {
				// add "lightbox" link to large image
				$str = str_replace($img, "<a href=\"". $rPath .'/'. $folder .'/'. $sName ."\" class=\"fancybox\" rel=\"lightbox$ID\" title=\"". $sAlt ."\">". $img ."</a>", $str);
			}
		}
	}
	return $str;
}

/**************************************
* AddImageLink - find <IMG> tag in string and surround it with <A HREF=...>
*--------------------------------------
* @parameter string - input string
* @parameter string - HREF location
* @parameter string - (relative) folder with larger images
* @returns string - input string with inserted <A ...> tags
**************************************/
function AddImageLink($str, $link="", $folder="large")
{
	global $StoreRoot, $WebPath;

	preg_match_all("/<img[^>]*>/i", $str, $aRes);	// find all instances of <IMG> in string
	foreach ( $aRes[0] as $img ) { // $aRes[0] contains all instances, there is no $aRes[1] since we do not have () in search
		preg_match("/ALT=\"([^\"]*)\"/i", $img, $aAlt); // find ALT= content
		preg_match("/SRC=\"((?!(?:http|data))[^\"]*)\"/i", $img, $aSrc); // find SRC= content
		$sSrc = $aSrc[1]; // SRC="" content (without SRC="")
		$sAlt = $aAlt[1]; // ALT="" content
		if ( $sSrc != "" ) {
			$rPath = dirname($sSrc); // web relative path
			if ( $WebPath!="" ) $sSrc  = str_replace($WebPath, '', $sSrc); // remove base
			$sPath = dirname($StoreRoot .'/'. $sSrc); // filesystem path
			$sName = basename($sSrc); // filename
			$sAlt  = $sAlt=="" ? $sName : $sAlt;
			// check if large file exists
			if ( fileExists($sPath .'/'. $folder .'/'. $sName) ) {
				// add link to large image
				$str = str_replace($img, "<a href=\"". $link . $rPath .'/'. $folder .'/'. $sName ."\" title=\"". $sAlt ."\">". $img ."</a>", $str);
			}
		}
	}
	return $str;
}

/**************************************
* PrependImagePath - find <IMG> tag in string and insert path at beginning
*--------------------------------------
* @parameter string - input string
* @parameter string - path to insert
* @returns string - input string with inserted paths
**************************************/
function PrependImagePath($str, $path="")
{
	preg_match_all("/<img[^>]*>/i", $str, $aRes);	// find all instances of <IMG> in string
	foreach ( $aRes[0] as $img ) { // $aRes[0] contains all instances, there is no $aRes[1] since we do not have () in search
		$imgABS = preg_replace( "/(SRC=\")(?!(?:http|data|\/))([^\"]*\")/i", '$1'. $path .'$2', $img );	// find SRC= content
		$str = str_replace( $img, $imgABS, $str );
	}
	return $str;
}

/**************************************
* koncnica - return word ending (plural form) for literal numbers
*--------------------------------------
* @parameter integer - input number
* @parameter string - comma separated entries wor word endings
* @returns string - word ending corresponding to input number
* ex: 1 zmaj - echo $zmaji . " zmaj(" . koncnica($zmaji," ,a,i,ev") . ")"
* ex: 2 zmaj(a) - echo $zmaji . " zmaj(" . koncnica($zmaji," ,a,i,ev") . ")"
* ex: 3 zmaj(i) - echo $zmaji . " zmaj(" . koncnica($zmaji," ,a,i,ev") . ")"
* ex: 7 zmaj(ev) - echo $zmaji . " zmaj(" . koncnica($zmaji," ,a,i,ev") . ")"
**************************************/
function koncnica($kolicina=0, $koncnice=" ,a,e,ov")
{
	$koncnice = explode(",", $koncnice);
	switch ( $kolicina % 100 ) {
		case 1:  $Koncnica = $koncnice[0]; break;
		case 2:  $Koncnica = $koncnice[1]; break;
		case 3:
		case 4:  $Koncnica = $koncnice[2]; break;
		default: $Koncnica = $koncnice[3]; break;
	}
	return trim( $Koncnica );
}

function left($str, $count=1)  { return substr($str, 0, $count); }
function right($str, $count=1) { return substr($str, -$count); }

function contains($haystack, $needle)
{
	return strpos($haystack, $needle)!==false;
}

function rfind($c,$s)
{
	while (right($s,1) != $c && strlen($s) > 2) {
		$s = left($s,strlen($s)-1);
	}
	return strlen($s)-1;
}

function ArrayLen($arr)
{
	return count($arr);
}

function now()
{
	return date("Y-m-d H:i:s");
}

function isLeapYear($year)
{
	return ((($year%4==0) && ($year%100)) || $year%400==0) ? (true):(false);
}

function isDate($date, $format='Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format)==$date;
}

function addDate($givendate, $day=0, $mth=0, $yr=0, $format='Y-m-d H:i:s')
{
    $cd = strtotime($givendate);
    $hr = abs($day)<1 && abs($day)>0 ? (int)($day * 24) : 0;
	return date($format, mktime(date('H',$cd)+$hr,
		date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
		date('d',$cd)+(int)$day, date('Y',$cd)+$yr));
}

function compareDate($date1, $date2, $format='Y-m-d H:i:s')
{
	$datetime1 = DateTime::createFromFormat($format, $date1);
	$datetime2 = DateTime::createFromFormat($format, $date2);
	return round($datetime1->diff($datetime2)->format("%r%a.%H%I%S"),4);
}

function formatDate($date, $format='Y-m-d H:i:s')
{
	$cd = strtotime($date);
	return date($format, mktime((int)date('H',$cd), (int)date('i',$cd), (int)date('s',$cd),
		(int)date('m',$cd), (int)date('d',$cd), (int)date('Y',$cd)));
}

function repeatstring($str, $count=1)
{
	$out = "";
	while ( $count-- > 0 )
		$out .= $str;
	return $out;
}

/**************************************
* EncodeUTF8 - convert Windows-1250 encoded string to UTF8 (slovenian letters only)
**************************************/
function EncodeUTF8($str)
{
	$str = str_replace( "\xE8", "\xC4\x8D", $str );
	$str = str_replace( "\xC8", "\xC4\x8C", $str );
	$str = str_replace( "\x9A", "\xC5\xA1", $str );
	$str = str_replace( "\x8A", "\xC5\xA0", $str );
	$str = str_replace( "\x9E", "\xC5\xBE", $str );
	$str = str_replace( "\x8E", "\xC5\xBD", $str );
	$str = str_replace( "\xE6", "\xC4\x87", $str );
	$str = str_replace( "\xC6", "\xC4\x86", $str );
	$str = str_replace( "\xF0", "\xC4\x91", $str );
	$str = str_replace( "\xD0", "\xC4\x90", $str );
	return $str;
}
function DecodeUTF8($str)
{
	$str = str_replace( "\xC4\x8D", "\xE8", $str );
	$str = str_replace( "\xC4\x8C", "\xC8", $str );
	$str = str_replace( "\xC5\xA1", "\x9A", $str );
	$str = str_replace( "\xC5\xA0", "\x8A", $str );
	$str = str_replace( "\xC5\xBE", "\x9E", $str );
	$str = str_replace( "\xC5\xBD", "\x8E", $str );
	$str = str_replace( "\xC4\x87", "\xE6", $str );
	$str = str_replace( "\xC4\x86", "\xC6", $str );
	$str = str_replace( "\xC4\x91", "\xF0", $str );
	$str = str_replace( "\xC4\x90", "\xD0", $str );
	return $str;
}

/**************************************
* Returns an string clean of UTF8, Win1252 & HTML characters.
* It will convert them to a similar ASCII character or strip them.
* (from www.unexpectedit.com)
**************************************/
function CleanString($text, $removepunct=false)
{
	// 1) convert á ô => a o
	$text = preg_replace("/[áàâãªä]/u","a",$text);
	$text = preg_replace("/[ÁÀÂÃÄ]/u","A",$text);
	$text = preg_replace("/[čćç]/u","c",$text);
	$text = preg_replace("/[ČĆÇ]/u","C",$text);
	$text = preg_replace("/[đ]/u","d",$text);
	$text = preg_replace("/[Đ]/u","D",$text);
	$text = preg_replace("/[éèêë]/u","e",$text);
	$text = preg_replace("/[ÉÈÊË]/u","E",$text);
	$text = preg_replace("/[ÍÌÎÏ]/u","I",$text);
	$text = preg_replace("/[íìîï]/u","i",$text);
	$text = preg_replace("/[óòôõºö]/u","o",$text);
	$text = preg_replace("/[ÓÒÔÕÖ]/u","O",$text);
	$text = preg_replace("/[úùûü]/u","u",$text);
	$text = preg_replace("/[ÚÙÛÜ]/u","U",$text);
	$text = preg_replace("/[šś]/u","s",$text);
	$text = preg_replace("/[ŠŚ]/u","S",$text);
	$text = preg_replace("/[žż]/u","z",$text);
	$text = preg_replace("/[ŽŻ]/u","Z",$text);
	$text = preg_replace("/[’‘‹›‚]/u","'",$text);
	$text = preg_replace("/[“”«»„]/u",'"',$text);
	$text = str_replace("–","-",$text);
	$text = str_replace(" "," ",$text);
	$text = str_replace("ł","l",$text);
	$text = str_replace("Ł","L",$text);
	$text = str_replace("ñ","n",$text);
	$text = str_replace("Ñ","N",$text);

	//2) Translation CP1252. &ndash; => -
	$trans = get_html_translation_table(HTML_ENTITIES);
	$trans[chr(130)] = '&sbquo;';    // Single Low-9 Quotation Mark
	$trans[chr(131)] = '&fnof;';    // Latin Small Letter F With Hook
	$trans[chr(132)] = '&bdquo;';    // Double Low-9 Quotation Mark
	$trans[chr(133)] = '&hellip;';    // Horizontal Ellipsis
	$trans[chr(134)] = '&dagger;';    // Dagger
	$trans[chr(135)] = '&Dagger;';    // Double Dagger
	$trans[chr(136)] = '&circ;';    // Modifier Letter Circumflex Accent
	$trans[chr(137)] = '&permil;';    // Per Mille Sign
	$trans[chr(138)] = '&Scaron;';    // Latin Capital Letter S With Caron
	$trans[chr(139)] = '&lsaquo;';    // Single Left-Pointing Angle Quotation Mark
	$trans[chr(140)] = '&OElig;';    // Latin Capital Ligature OE
	$trans[chr(145)] = '&lsquo;';    // Left Single Quotation Mark
	$trans[chr(146)] = '&rsquo;';    // Right Single Quotation Mark
	$trans[chr(147)] = '&ldquo;';    // Left Double Quotation Mark
	$trans[chr(148)] = '&rdquo;';    // Right Double Quotation Mark
	$trans[chr(149)] = '&bull;';    // Bullet
	$trans[chr(150)] = '&ndash;';    // En Dash
	$trans[chr(151)] = '&mdash;';    // Em Dash
	$trans[chr(152)] = '&tilde;';    // Small Tilde
	$trans[chr(153)] = '&trade;';    // Trade Mark Sign
	$trans[chr(154)] = '&scaron;';    // Latin Small Letter S With Caron
	$trans[chr(155)] = '&rsaquo;';    // Single Right-Pointing Angle Quotation Mark
	$trans[chr(156)] = '&oelig;';    // Latin Small Ligature OE
	$trans[chr(159)] = '&Yuml;';    // Latin Capital Letter Y With Diaeresis
	$trans['euro'] = '&euro;';    // euro currency symbol
	ksort($trans);

	foreach ($trans as $k => $v) {
		$text = str_replace($v, $k, $text);
	}

	// 3) remove <p>, <br/> ...
	$text = strip_tags($text);
/*
	$search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
				   '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
				   '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
				   '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
					);
	$text = preg_replace($search, '', $text);
*/
	// 4) &amp; => & &quot; => '
	$text = html_entity_decode($text);

	// 5) remove Windows-1252 symbols like "TradeMark", "Euro"...
	$text = preg_replace('/[^(\x20-\x7F)]*/','', $text);

	// 6) remove ASCII punctuators (except -_@)
	if ( $removepunct ) $text = preg_replace('/[\x21-\x2C\x2E-\x2F\x3A-\x3F\x5B-\x5E\x7B-\x7D]/','',$text);

	$targets=array('\r\n','\n','\r','\t');
	$results=array(" "," "," "," ");
	$text = str_replace($targets,$results,$text);

	return ($text);
}

/**************************************
* CleanupTinyMCE - cleanup TinyMCE edits
*--------------------------------------
* @parameter string - input string
* @returns string - converted input string
**************************************/
function CleanupTinyMCE($str)
{
	$str = str_replace("&scaron;", "š", $str);
	$str = str_replace("&Scaron;", "Š", $str);
	$str = str_replace("&ccaron;", "č", $str);
	$str = str_replace("&Ccaron;", "Č", $str);
	$str = preg_replace("/(SRC=\")\.\.\//i", '$1', $str);
	$str = preg_replace("/<([\/]*)EM>/i", '<$1I>', $str);
	$str = preg_replace("/<([\/]*)STRONG>/i", '<$1B>', $str);
	return $str;
}

/**************************************
* ReplaceSmileys - replace character smileys in string with images
*--------------------------------------
* @parameter string - input string containing character smileys
* @parameter string - optional: folder with images
* @returns string - converted input string
**************************************/
function ReplaceSmileys($str, $folder="./pic/")
{
	$smileys = array();
	$smileys[] = array('/([ >])(\:\-*\()([ <])/',        's01.png'); // :-(
	$smileys[] = array('/([ >])(\:\-*\))([ <])/',        's02.png'); // :-)
	$smileys[] = array('/([ >])(\:\-*D)([ <])/',         's03.png'); // :-D
	$smileys[] = array('/([ >])(\;\-*\))([ <])/',        's04.png'); // ;-)
	$smileys[] = array('/([ >])(\:\-*O)([ <])/i',        's05.png'); // :-O
	$smileys[] = array('/([ >])(\:\-*S)([ <])/i',        's06.png'); // :-S
	$smileys[] = array('/([ >])(\:\-*\$)([ <])/',        's07.png'); // :-$
	$smileys[] = array('/([ >])(\:\'\()([ <])/',         's08.png'); // :'(
	$smileys[] = array('/([ >])(\:\-*\|)([ <])/',        's09.png'); // :-|
	$smileys[] = array('/([ >])(\:\-*P)([ <])/',         's10.png'); // :-P
	$smileys[] = array('/([ >])(8\-*\))([ <])/',         's11.png'); // 8-)
	$smileys[] = array('/([ >])(\:\-*\{\})([ <])/',      's12.png'); // :-{}
	$smileys[] = array('/([ >])(\:\*\)*)([ <])/',        's12.png'); // :* :*)
	$smileys[] = array('/([ >])(\<3)([ <])/i',           's13.png'); // <3
	$smileys[] = array('/([ >])(\:\-*[Xx#!])([ <])/',    's14.png'); // :-X :-# :-!
	$smileys[] = array('/([ >])(o\:\-*\))([ <])/i',      's15.png'); // o:-)
	$smileys[] = array('/([ >])(>*\:\-*[>)])([ <])/',    's16.png'); // >:-> >:-)
	$smileys[] = array('/([ >])(\-[_.]\-)([ <])/i',      's17.png'); // -_- -.-
	$smileys[] = array('/([ >])(\(\@\))([ <])/',         's18.png'); // (@)
	$smileys[] = array('/([ >])(\:\@)([ <])/',           's18.png'); // :@
	$smileys[] = array('/([ >])(&gt;*\:\-*&gt;)([ <])/', 's16.png'); // >:->
	$smileys[] = array('/([ >])(\:\-*[\/])([ <])/',      's19.png'); // :-/ :-\
	$smileys[] = array('/([ >])([^ps]\:\-*[\/])([ <])/', 's19.png'); // :-/ :-\
	$smileys[] = array('/([ >])(\;\-*\()([ <])/',        's20.png'); // ;-(
	$smileys[] = array('/([ >])(\@\}\-[>;]\-+)([ <])/',  's21.png'); // @}->-- @}-;--
	$smileys[] = array('/([ >])(\@\}\-(&gt;)\-+)([ <])/','s21.png'); // @}->--
	$smileys[] = array('/([ >])(\*FLOWER\*)([ <])/i',    's21.png'); // @}->--
	$smileys[] = array('/([ >])(\:\.\()([ <])/',         's08.png'); // :.(
	$smileys[] = array('/([ >])(B\-*\))([ <])/',         's11.png'); // B-)
	$smileys[] = array('/([ >])(O\.o)([ <])/',           's22.png'); // O.o
	$smileys[] = array('/([ >])(\*\.\*)([ <])/',         's20.png'); // *.*
	for ( $i=0; $i < count($smileys); $i++ ) {
 		$str = preg_replace($smileys[$i][0], "$1<IMG CLASS=\"smiley\" ALT=\"$2\" SRC=\"" . $folder . $smileys[$i][1] . "\" BORDER=0>$3", $str);
	}
	return $str;
}

/**************************************
* SearchString
*--------------------------------------
* Attributes:
* DBFIELD - database field name
* STRING - a search string to be parsed
*
* Returns:
* SearchString
*
* A custom function to parse a complete search string in keywords (tokens)
* used by SQL LIKE statement. to search using
* OR technique. AND technique is achieved as a single OR token, composed of two
* or more words combined with '+' (plus) sign.
*
* Examples:
*   STRING: good manners
*   RESULT: DBFIELD like '%good%' and DBFIELD like '%manners%'
*
*   STRING: good OR manners
*   STRING: good ALI manners (slovenian implementation)
*   STRING: good,manners
*   STRING: good, manners
*   RESULT: DBFIELD like '%good%' or DBFIELD like '%manners%'
*
*   STRING: -good -manners
*   RESULT: DBFIELD not like '%good%' and DBFIELD not like '%manners%'
*
*   STRING: "good manners"
*   RESULT: DBFIELD like '%good manners%'
* -------------------------------------
* Developed by Blaz Kristan (blaz@kristan-sp.si)
**************************************/
function SearchString($DBField, $String="")
{

	// preoblikujem iskalno besedilo
	$String = str_replace("[",  "[[]", $String);
	$String = str_replace("%",  "[%]", $String);
	$String = str_replace("_",  "[_]", $String);
	$String = str_replace("*",  "",    $String);
	$String = str_replace("'",  "",    $String);
	$String = str_replace("?",  "_",   $String);
	$String = str_replace("-",  " -",  $String);
	$String = str_replace("- ", " ",   $String);
	$String = str_replace("+ ", " ",   $String);
	$String = preg_replace("/[[:space:]]+/", " ", $String);

	// loim iskane besede za iskanje z ALI
	$String = str_replace(", "   , " +", $String);
	$String = str_replace(","    , " +", $String);
	$String = str_replace(" ALI ", " +", $String);
	$String = str_replace(" OR " , " +", $String);

	// loim iskane besede za iskanje z IN
	$String = str_replace(" IN " , " ", $String);
	$String = str_replace(" AND ", " ", $String);

	// parsam iskalno besedilo v seznam iskanih besed
	$SearchSubstrings = explode(" ", $String);
	$SearchList = array();
	$Nest = ""; // zaasno za zdruevanje citiranih stringov
	foreach ( $SearchSubstrings as $SearchSubstring ) {

		$Nest .= $SearchSubstring .' ';

		// imamo citiran string (quote)
		if ( left($SearchSubstring, 1)=='"' && right($SearchSubstring, 1) != '"' )
			continue; // skok na naslednjo besedo

		// zakljuek citiranega stringa
		if ( trim($Nest)!=$SearchSubstring && right($SearchSubstring, 1) != '"' )
			continue; // skok na naslednjo besedo

		// dodam v seznam iskanih stringov
		$SearchList[] = trim($Nest);
		$Nest = ""; // pocistim zacasno variablo
	}

	// bildam SQL WHERE stavek, ki je zaradi varnosti v oklepajih
	if ( count($SearchList)>0 ) {

		$SearchString = "(";
		foreach ( $SearchList as $Niz ) {
			if ( left($Niz, 1) == "+" ) {
				// e e nekaj imam v iskalnem stringu dodam oklepaje in OR
				if ( strlen($SearchString) > 1 )
					$SearchString = left($SearchString, strlen($SearchString)-5) . ") OR (";

				if ( substr($Niz, 1, 1)=="-" ) // e imam "+-" dodam NOT LIKE
					$SearchString .= "$DBField NOT LIKE '%" . preg_replace('/["+-]/', "", $Niz) . "%' AND "; // odstranim ["+-]
				else // drugae dodam samo LIKE
					$SearchString .= "$DBField LIKE '%" . preg_replace('/["+-]/', "", $Niz) . "%' AND "; // odstranim ["+-]

			} elseif ( left( $Niz, 1 ) == "-" ) // - pomeni, da te besede noemo
				$SearchString .= "$DBField NOT LIKE '%" . preg_replace('/["+-]/', "", $Niz) . "%' AND "; // odstranim ["+-]

			else // zgolj dodamo besedo
				$SearchString .= "$DBField LIKE '%" . preg_replace('/["]/', "", $Niz) . "%' AND "; // odstranim ["]
		}
		// e elim iskati samo cele besede moram v SQL string dodati [^a-z], npr:
		//(FIELD like '%[^a-z]STRING[^a-z]%' or FIELD like '%[^a-z]STRING%' or FIELD like '%STRING[^a-z]%' or FIELD like '%STRING%')

		// odstranim zadnji " AND " in zakljuim z ")"
		return left($SearchString, strlen($SearchString)-5) .")";

	}
	return "";
}

/**************************************
* ImageResize
*--------------------------------------
* Attributes:
*  filefield="[filefield]"					%Name of form field where photo is uploaded or
*                                           % actual filename prefixed by '->' (rename) or '=>' (keep name)
*  imagepath="[photopath]"					%Server path to the image directory
*  thumbprefix="[prefix]"      /optional	%Server path to the thumbnail directory	or thumb prefix
*  largeprefix="[largeprefix]" /optional	%Server path to the original image directory or large prefix
*  maxsize=[number,array]      /optional	%Maximum width/height of image
*  thumbsize=[number]          /optional	%Width/height of the generated thumbnail (<0 = square thumbnail (cropped))
*  jpegquality=[number]        /optional	%JPEG quality % setting [0-100]
*  nameconflict="[text]"       /optional	%Action to take if uploaded image's name is already on the server
*
* Returns:
*  array(name, width, height, reduced width, reduced height, thumb width, thumb height, size)
*
* Upload a $_FILES file and resize and/or create thumbnail image.
* Requires PhpThumbFactory library by Ian Selby/Gen X Design <http://phpthumb.gxdlabs.com>
**************************************/
require_once(dirname(__FILE__) .'/inc/thumb/ThumbLib.inc.php');

function ImageResize(
	$filefield,
	$imagepath,
	$thumbprefix='_t_',
	$largeprefix='_l_',
	$maxsize = 0,
	$thumbsize = 0,
	$jpegquality = 90,
	$nameconflict = "makeunique"
) {

	// Error Checking
	$doit = true;
	$message = "";

	if ( !isset($imagepath) || !is_dir($imagepath) ) {
		$doit = false;
		$message = "You need to specify a valid image path!";
	} else {}

	if ( !isset($filefield) ) {
		$doit = false;
		$message = "You need to specify a file field!";
	} else {}

	if ( !isset($thumbprefix) || !isset($thumbsize) ) {
		$doit = false;
		$message = "Wrong thumbnail size and prefix.";
	} else {}

	if ( !isset($largeprefix) ) {
		$doit = false;
		$message = "Wrong large image prefix.";
	} else {}

	if ( !(strtolower($nameconflict)=="makeunique"
		|| strtolower($nameconflict)=="overwrite"
		|| strtolower($nameconflict)=="error") ) {
		$nameconflict = "makeunique";
	} else {}

	// parse $filefield
	if ( left($filefield,2)=='->' || left($filefield,2)=='=>' ) {

		// '->' or '=>' in front of actual name means already uploaded file (existing in the app folder)
		// -> change the name of file
		// => do not change the name (except uppercase and space)
		$tmpfile = substr($filefield,2);
		$photo   = (left($filefield,1)=='=' ? basename($tmpfile) : "photo". date("-Ymd-His") . strrchr(basename($tmpfile),'.'));
		$photo   = strtolower(str_replace(' ', '-', $photo));

		if ( !contains(".gif,.jpg,.png", right($photo, 4)) ) {
			$doit    = false;
			$message = "Wrong image type. Only GIF, JPEG and PNG allowed.";
		}

	} elseif ( !$_FILES[$filefield]['error'] ) {

		// file being uploaded
		$tmpfile = $_FILES[$filefield]['tmp_name'];
		$photo   = strtolower(str_replace(' ','-',CleanString(basename($_FILES[$filefield]['name']))));

		if ( !contains(".gif,.jpg,.png", right($photo, 4)) ) {
			$doit    = false;
			$message = "Wrong image type. Only GIF, JPG and PNG allowed.";
		}

	} else {

		$doit    = false;
		$message = "File upload error.";

	}

	if ( $doit ) {

		$ext    = strrchr($photo, '.');
		$name   = left($photo, strlen($photo)-4);
		$retina = (right($name, 3) === '@2x'); // check if 'retina' size/intent upload
		if ( $retina ) {
			$name = left($name, strlen($name)-3); // remove retina designator from name
		}

		switch ( strtolower($nameconflict) ) {
			case "makeunique":
				// if file exists make filename unique
				for ( $i=1; fileExists($imagepath .'/'. $name . $ext); )
					$name = "photo". date("-Ymd-His-") . $i++;
				break;
			case "error":
				if ( fileExists($imagepath .'/'. $name . $ext) ) {
					$doit    = false;
					$message = "File already exists and NAMECONFLICT=ERROR specified.";
				}
				break;
		}

		$uploadfile = $imagepath .'/'. $name . $ext;
		$thumbfile  = $imagepath .'/'. $thumbprefix . $name . $ext;
		$largefile  = $imagepath .'/'. ($maxsize > 0 ? $largeprefix : '') . $name . $ext;
	}

	if ( $doit ) {

		// uploaded image limits
		if ( is_array($maxsize) ) {
			$limit   = max(abs($maxsize[0]),abs($maxsize[1])) * ($retina ? 2 : 1);
			$maxsize = min(abs($maxsize[0]),abs($maxsize[1]));
		} else
			$limit = $retina ? 2048 : 1024;

		// move file and resize image
		if ( (left($filefield,2)=='->' ? rename($tmpfile, $largefile) : @move_uploaded_file($tmpfile, $largefile)) ) {
			// resize image
			try {
				$thumb = PhpThumbFactory::create($largefile, array('jpegQuality' => $jpegquality,'resizeUp' => false));

				// get image dimensions
				$size = $thumb->getCurrentDimensions();

				// limit original image size
				if ( $size['width'] > $limit || $size['height'] > $limit ) {
					$thumb->resize($limit, $limit)->save();
					$size = $thumb->getCurrentDimensions();
				}
				$r_width  = $i_width  = $size['width'];
				$r_height = $i_height = $size['height'];

				// resize image if larger than limits
				if ( isset($maxsize) && $maxsize > 0 && ($i_width > $maxsize || $i_height > $maxsize) ) {

					// resize retina image
					if ( $retina ) $thumb->resize($maxsize*2, $maxsize*2)->save($imagepath .'/'. $name .'@2x'. $ext);
					$thumb->resize($maxsize, $maxsize)->save($uploadfile);

					// get resized image dimesions
					$size     = $thumb->getCurrentDimensions();
					$r_width  = $size['width'];
					$r_height = $size['height'];

				} else {

					// if resizing not specified or image is smaller than limits
					if ( $retina ) { // handle retina upload
						@copy($largefile, $imagepath .'/'. $name .'@2x'. $ext); // copy retina original
						$size = $thumb->getCurrentDimensions(); // $thumb == $largefile
						$max  = max($size['width'],$size['height']);
						$thumb->resize($max/2, $max/2)->save($uploadfile); // resize non-retina

						// get image dimensions
						$size = $thumb->getCurrentDimensions();
						$r_width  = $i_width  = $size['width'];
						$r_height = $i_height = $size['height'];
					} else {
						if ( $largefile != $uploadfile )
							@copy($largefile, $uploadfile);
					}
					// remove original image
					if ( $largefile != $uploadfile )
						@unlink($largefile);
				}

				// create thumbnail (<0 crop it square)
				$square    = $thumbsize < 0;
				$thumbsize = abs($thumbsize);
				if ( $thumbprefix != '' && isset($thumbsize) && $thumbsize!=0 ) {
					// adaptiveResize=square (crop&resize), resize=regular
					if ( $square ) {
						// allways create retina thumbnail if original image large enough
						if ( $retina || ($r_width>$thumbsize*2 || $r_height>$thumbsize*2) ) // resize retina image
							$thumb->adaptiveResize($thumbsize*2, $thumbsize*2)->save($imagepath .'/'. $thumbprefix . $name .'@2x'. $ext);
						$thumb->adaptiveResize($thumbsize, $thumbsize)->save($thumbfile);
					} else {
						// allways create retina thumbnail if original image large enough
						if ( $retina || ($r_width>$thumbsize*2 || $r_height>$thumbsize*2) ) // resize retina image
							$thumb->resize($thumbsize*2, $thumbsize*2)->save($imagepath .'/'. $thumbprefix . $name .'@2x'. $ext);
						$thumb->resize($thumbsize, $thumbsize)->save($thumbfile);
					}
					$size     = $thumb->getCurrentDimensions();
					$t_width  = $size['width'];
					$t_height = $size['height'];
				} else {
					$t_width  = $t_height = 0;
				}

				// get file size
				$stat = stat($uploadfile);
				$fileSize = (int)$stat['size'];

			} catch (Exception $e) {

				// cleanup
				@unlink($imagepath .'/'. $thumbprefix . $name .'@2x'. $ext); // retina thumbnail
				@unlink($imagepath .'/'. $thumbprefix . $name . $ext); // thumbnail
				@unlink($imagepath .'/'. $name .'@2x'. $ext); // resized retina image
				@unlink($uploadfile); // resized image
				@unlink($largefile); //original image

				trigger_error("Resize error!", E_USER_ERROR);
				return false;
			}
		} else {

			// cleanup
			@unlink($largefile);

			trigger_error("Move error!", E_USER_ERROR);
			return false;
		}

		// return metadata
		return array('name' => $name . $ext,
			'iw' => $i_width, 'ih' => $i_height,
			'rw' => $r_width, 'rh' => $r_height,
			'tw' => $t_width, 'th' => $t_height,
			'size' => $fileSize);

	} else {

		trigger_error($message, E_USER_NOTICE);
		return false;
	}
}

/**************************************
* ParseMetadata
*--------------------------------------
* Attributes:
*  metafield="[metafield]"				%string with metadata in the form: variable=value;variable=value;...
*  separator=';'			/OPTIONAL	% metadata field separator
*
* Returns:
*  array(variable, value)
**************************************/
function ParseMetadata($metafield, $separator=";")
{
	if ( $metafield == "" ) return null;
	$keys   = array();
	$values = array();
	foreach ( explode($separator, $metafield) As $item ) {
		if ( $item=="" ) continue;
		$j = explode("=", $item);
		$keys[]   = $j[0];
		$values[] = $j[1];
	}
	$Meta = array_combine($keys, $values);
	unset($keys);
	unset($values);
	return $Meta;
}
?>
