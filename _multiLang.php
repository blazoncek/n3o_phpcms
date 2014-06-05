<?php
/*~ _multiLang.php
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
///////////////////////////////////////
// Language UDFs
///////////////////////////////////////

// this function converts ColdFusion style dynamic text to PHP compatible dynamic text and evaluates it
function evaluate($str)
{
	global $PostMaster;

	$str = str_ireplace( '[year]', date( 'Y' ), $str ); // replace year variable
	$str = str_ireplace( '[PostMaster]', $PostMaster, $str ); // replace postmaster variable

	// global variables are inaccessible, so eval() does not work as expected
	//$str = ereg_replace( '\#([_a-zA-Z0-9()]+)\#', '$\\1', $str ); // convert marked variables to PHP
	//$str = ereg_replace( '\#([_a-zA-Z0-9()]+)\.([_a-zA-Z0-9]+)\#', '$\\1->\\2', $str ); // convert marked objects to PHP
	//eval('$str = "' . "$str" . '";');

	return $str;
}

// this function returns total number of enabled languages
function langTotal()
{
	global $db;

	return $db->get_var("SELECT count(*) FROM Jeziki WHERE Enabled <> 0");
}

//this function will return a default language
function langDefault()
{
	global $db, $langTotal;

	if ( $langTotal == 0 ) return "";
	return $db->get_var("SELECT Jezik FROM Jeziki WHERE Enabled <> 0 AND DefLang <> 0");
}

//this function checks if language exists
function langExists($lang)
{
	global $db, $langTotal;

	if ( $langTotal == 0 ) return 0;
	return (int) $db->get_var("SELECT count(*) FROM Jeziki WHERE Enabled <> 0 AND Jezik = '". $lang ."'");
}

//this function checks if language exists by language code
function langCodeExists($langCode)
{
	global $db, $langTotal;

	if ( $langTotal == 0 ) return "";
	return $db->get_var("SELECT Jezik FROM Jeziki WHERE Enabled <> 0 AND LangCode = '". $langCode ."'");
}

//this function will return language code
function langCode($lang)
{
	global $db, $langTotal;

	if ( $langTotal == 0 ) return "";
	return $db->get_var("SELECT LangCode FROM Jeziki WHERE Jezik = '". $lang ."'");
}

//this function will return language name
function langName($lang)
{
	global $db, $langTotal;

	if ( $langTotal == 0 ) return "";
	return $db->get_var("SELECT Opis FROM Jeziki WHERE Jezik = '". $lang ."'");
}

//this function will return language character set
function langCharSet($lang)
{
	global $db, $langTotal;

	if ( $langTotal == 0 ) return "";
	return $db->get_var("SELECT Charset FROM Jeziki WHERE Jezik = '". $lang ."'");
}

//multi language UDF interface
function multiLang($c, $l)
{
	global $db, $langTotal;

	if ( !isset($c) || !isset($l) ) return "";
	if ( $langTotal == 0 ) return "";

	$NLSText = $db->get_row(
		"SELECT
			NLSShort,
			NLSLong
		FROM
			NLSText
		WHERE
			Jezik = '$l'
			AND
			NLSToken = '" . str_replace(">", "", str_replace("<", "", $c )) . "'"
		);

	if ( $db->num_rows == 0 )
		return str_replace(">", "&gt;", str_replace("<", "&lt;", $c));
	
	if ( $NLSText->NLSLong == "" ) {
		return $NLSText->NLSShort;
	} else
		return evaluate($NLSText->NLSLong);
}

// get total number of available languages
$langTotal = langTotal();

// get default language from DB (or config if default language is not set in DB)
$lang = ($langDefault = langDefault())=="" ? DefLang : $langDefault;

// check if browser requested a specific language (override default)
if ( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {
	$al = explode(",", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	foreach ( $al as $langCode ) {
		if ( ($lang = langCodeExists($langCode)) != "" ) {
			break;
		}
	}
}

// check if cookie is set (override browser setting)
if ( isset($_COOKIE['lng']) ) $lang = $_COOKIE['lng'];

// check if url parameter exists (override cookie setting)
if ( isset($_GET['lng']) ) {
	$lang = $_GET['lng'];
	// set cookie to remember language setting fo 30 days
	if ( langExists($lang) && isset($_COOKIE['accept_cookies']) && $_COOKIE['accept_cookies']=='yes' )
		setcookie('lng', $lang, time()+60*60*24*30, $WebPath, $_SERVER['SERVER_NAME']);
}

// check if language exists and set default language if not
if ( !langExists($lang) ) $lang = $langDefault;

// set the HTTP header
header("Content-Type: text/html; charset=" . langCharSet($lang) );
