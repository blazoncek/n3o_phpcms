<?php
/*~ _application.php - application initialization script
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend and backend)                                 |
|   Version: 2.2.2                                                          |
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

/******************************************************************************
* Initializes application environment (global variables, cookies, sessions, ...)
* Also opens database connection and reads configuration from database.
* Requires public modules: ezSQL, PHPMailer and Thumbs.
*******************************************************************************/

// base configuration (mandatory in all files!)
require_once(dirname(__FILE__) .'/_config.php');

// include common functions
require_once(dirname(__FILE__) .'/_functions.php');

// fix escaped quotes on some hosting providers
if ( get_magic_quotes_gpc() ) {
	foreach ( $_GET as $key => $value ) {
		$_GET[$key] = stripslashes($value);
	}
	foreach ( $_POST as $key => $value ) {
		$_POST[$key] = stripslashes($value);
	}
	foreach ( $_COOKIE as $key => $value ) {
		$_COOKIE[$key] = stripslashes($value);
	}
}

// some globals without dependencies
$StoreRoot = dirname(__FILE__);	// without trailing slash
$WebServer = $_SERVER['SERVER_NAME'];
$WebFile   = basename($_SERVER['SCRIPT_NAME']);

// Get base folder name but take into account only top folder in case of permalinks
// http://www.domain.com/index.php         -> /
// http://www.domain.com/folder/index.php  -> folder/
// http://www.domain.com/folder/subfolder/ -> folder/
$WebPath   = explode("/",trim(dirname($_SERVER['SCRIPT_NAME']),'/'));
$WebPath   = $WebPath[0]!='' ?  '/'.$WebPath[0] : '';
$WebPath   = $WebPath=='/servis' ? '' : $WebPath; // disregard admin folder

// create full root URL address
$WebURL    = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])!="off") ? "https://" : "http://";
$WebURL   .= $_SERVER['SERVER_NAME'];
$WebURL   .= $_SERVER['SERVER_PORT']=="80" ? "" : ":".$_SERVER['SERVER_PORT'];
$WebURL   .= $WebPath; // complete root URL without file

// IIS & mod_rewrite detection (for permalinks)
if (function_exists('apache_get_modules')) {
	$modules = apache_get_modules();
	$IsIIS   = !in_array('mod_rewrite', $modules);
} else {
	$IsIIS   = getenv('HTTP_MOD_REWRITE')=='On' ? false : contains($_SERVER['SERVER_SOFTWARE'], 'IIS');
}

// detect mobile device
require_once($StoreRoot .'/inc/mobile_device_detect.php');
$Tablet = $Mobile = mobile_device_detect();
if ( $Mobile && preg_match('/(ipad|tab|SM-T|GT-P|GT-N)/i',$_SERVER['HTTP_USER_AGENT']) ) { // inc. Samsung tablets
	$Mobile = false;
	$Tablet = true;
}

/*------------------------------------------------------------------------------
D A T A B A S E   S U P P O R T
------------------------------------------------------------------------------*/
// Include ezSQL core
require_once($StoreRoot .'/inc/ezSQL/ezsql_core.php');

// Initialise database object and establish a connection
// at the same time - db_user / db_password / db_name / db_host
switch (SQLType) {
	case "MySQL":
		// Include ezSQL database specific component (in this case mySQL)
		require_once($StoreRoot .'/inc/ezSQL/ezsql_mysql.php');
		// create DB object (& connect to DB)
		$db = new ezSQL_mysql(DBUS, DBPW, DSN, DBHOST);
		$db->hide_errors();
		// fix UTF8 handling
		$db->query("SET NAMES '". DBCS ."' COLLATE '". DBCOLLATE ."';");
		if ( $db->last_error ) {
			// display connect error
			//file_put_contents($StoreRoot .'/media/_errors_.txt', date("Y-m-d H:i:s ") .'Query '. $db->last_error ."\n", FILE_APPEND);
			header("Refresh:1; URL=". $WebURL ."/error.php");
			die();
		}
		break;
	case "MsSQL":
		// Include ezSQL database specific component (in this case msSQL)
		require_once($StoreRoot .'/inc/ezSQL/ezsql_mssql.php');
		// create DB object (& connect to DB)
		$db = new ezSQL_mssql(DBUS, DBPW, DSN, DBHOST);
		break;
	default:
		//file_put_contents($StoreRoot .'/media/_errors_.txt', date("Y-m-d H:i:s ") ."Wrong DB type selected.\n", FILE_APPEND);
		header( "Refresh:1; URL=". $WebURL ."/error.php" );
		die();
}
$db->hide_errors();

// get application database version
$N3OVersion = $db->get_var( "SELECT ParamValue FROM n3oParameters WHERE ParamName = 'Version'");

if ( $db->last_error ) {
	// display error message in case DB error
	//file_put_contents($StoreRoot .'/media/_errors_.txt', date("Y-m-d H:i:s ") .'Query '. $db->last_error ."\n", FILE_APPEND);
	header("Refresh:1; URL=". $WebURL ."/error.php");
	die();
}

/*------------------------------------------------------------------------------
M A I L   S E R V E R   S E T T I N G S
------------------------------------------------------------------------------*/
// Include PHPmailing support
require_once($StoreRoot .'/inc/PHPMailer/class.phpmailer.php');
require_once($StoreRoot .'/inc/PHPMailer/class.smtp.php');
require_once($StoreRoot .'/inc/pop3.php');

// Mailing settings (default)
$PostMaster      = PostMaster;
$PMasterRealName = 'Administrator';
$mailServer      = 'localhost';
$mailUser        = '';
$mailPass        = '';
$mailSSL         = false;

// get mail server settings from database
$MailSrv = $db->get_row(
	"SELECT
		ST.SifNaziv AS MServer,
		ST.SifCVal1 AS MUser,
		ST.SifCVal2 AS MPass,
		ST.SifCVal3 AS MPostmaster,
		S.SifNVal1  AS MPort,
		S.SifLVal1  AS MSSL
	FROM
		Sifranti S
		LEFT JOIN SifrantiTxt ST ON S.SifrantID = ST.SifrantID
	WHERE
		S.SifrCtrl = 'PARA' AND
		S.SifrText = 'MailSrv' AND
		ST.Jezik IS NULL AND
		ST.ID IS NOT NULL" );

if ( $MailSrv ) {
	$mailServer = $MailSrv->MServer;
	$mailUser   = $MailSrv->MUser;
	$mailPass   = $MailSrv->MPass;
	$mailPort   = (int)$MailSrv->MPort;
	$mailSSL    = (bool)$MailSrv->MSSL;
	$PostMaster = $MailSrv->MPostmaster;
}

// apply settings to SMTP object
$SMTPServer = new PHPMailer();
$SMTPServer->IsSMTP(true);
$SMTPServer->Host = $mailServer;
if ( $mailPort ) $SMTPServer->Port = $mailPort;
if ( $mailUser != '' ) {
	$SMTPServer->SMTPAuth = true;
	$SMTPServer->Username = $mailUser;
	$SMTPServer->Password = $mailPass;
}
$SMTPServer->CharSet = 'utf-8';
$SMTPServer->SetFrom($PostMaster, $PMasterRealName);
$SMTPServer->ClearReplyTos();

unset($MailSrv);

/*------------------------------------------------------------------------------
L D A P   S E R V E R   S E T T I N G S
------------------------------------------------------------------------------*/
$LDAPServer = LDAPSERVER;
$LDAPCheck  = LDAPCHECK;

$LDAP = $db->get_results(
	"SELECT
		S.SifrText,
		ST.SifNaziv
	FROM
		Sifranti S
		LEFT JOIN SifrantiTxt ST ON S.SifrantID = ST.SifrantID
	WHERE
		S.SifrCtrl = 'PARA' AND
		S.SifrText LIKE 'AppLDAP%' AND
		ST.Jezik IS NULL AND
		ST.ID IS NOT NULL"
	);

// retrieve custom settings
if ( $LDAP ) foreach ( $LDAP as $Par ) {
	switch ( $Par->SifrText ) {
		case "AppLDAPSrv": $LDAPServer = $Par->SifNaziv; break;
		case "AppLDAPSvr": $LDAPServer = $Par->SifNaziv; break;
		case "AppLDAPChk": $LDAPCheck  = $Par->SifNaziv; break;
	}
}

// create search domain name (from $LDAPCheck)
$AuthDomain = "@";
if ( count(explode(",",$LDAPCheck)) ) foreach ( explode(",",$LDAPCheck) as $i ) {
	if ( left($i,3) == "DC=" ) {
		$AuthDomain .= substr($i, 3, 99) .'.';
	}
}
// trimm last "."
if ( right($AuthDomain,1) == "." ) {
	$AuthDomain = left($AuthDomain,strlen($AuthDomain)-1);
}

unset($LDAP);

/*------------------------------------------------------------------------------
S E S S I O N   V A R S
------------------------------------------------------------------------------*/
// Session variables are held in $_SESSION['session_var']

// start session tracking
session_start();

if ( isset($_SESSION['LastVisit']) ) {
	// get session variable
	$LastVisit = $_SESSION['LastVisit'];
}

/*------------------------------------------------------------------------------
C O O K I E S
------------------------------------------------------------------------------*/
// Cookie variables are available later as $_COOKIE['cookie_var']

// check if LastVisit cookie is defined
if ( !isset($LastVisit) && isset($_COOKIE['LastVisit']) ) {
	// get cookie value if session var is not defined
	$LastVisit = date("Y-m-d H:i:s", strtotime($_COOKIE['LastVisit']));
	// assign it to session value
	$_SESSION['LastVisit'] = $LastVisit;
} elseif ( !isset($LastVisit) ) {
	$LastVisit = date("Y-m-d H:i:s");
}

// set/update cookie to persist for 1 year
if ( isset($_COOKIE['accept_cookies']) && $_COOKIE['accept_cookies']=='yes' ) {
	setcookie("LastVisit", date("Y-m-d H:i:s"), time()+31536000, $WebPath); //date("Y-m-d H:i:s")
}

/*------------------------------------------------------------------------------
M U L T I   L A N G U A G E   S U P P O R T
------------------------------------------------------------------------------*/
include_once($StoreRoot .'/_multiLang.php');

/*------------------------------------------------------------------------------
M I S C   S E T T I N G S
------------------------------------------------------------------------------*/
// default values for HTML include files
$js     = $WebURL .'/js';  // general purpose JavaScript
$pic    = $WebURL .'/pic'; // misc images
$inc    = $WebURL .'/inc'; // misc includes
$jpgPct = 100; // JPEG quality (for resize)

// get parameters from database
$Params = $db->get_results(
	"SELECT
		S.SifrText,
		S.SifNVal1,
		ST.SifNaziv,
		ST.SifCVal1,
		ST.SifCVal2,
		ST.SifCVal3
	FROM
		Sifranti S
		LEFT JOIN SifrantiTxt ST ON S.SifrantID = ST.SifrantID
	WHERE
		S.SifrCtrl = 'PARA'
		AND S.SifrText LIKE 'App%'
		AND ST.Jezik IS NULL
		AND ST.ID IS NOT NULL"
	);
if ( $Params ) foreach ( $Params as $Param ) {
	switch ($Param->SifrText) {
		case "AppJs":
			$js = $Param->SifNaziv;
			break;
		case "AppPic":
			$pic    = $Param->SifNaziv;
			$jpgPct = max(min((int)$Param->SifNVal1,100),50);
			break;
		case "AppInc":
			$inc = $Param->SifNaziv;
			break;
		case "AppPMaster":
			$PostMaster      = $Param->SifNaziv;
			$PMasterRealName = ($Param->SifCVal1 == '' ? 'Administrator' : $Param->SifCVal1);
			$TwitterName     = $Param->SifCVal2; // site's Twitter name
			$TwitterWdgt     = $Param->SifCVal3; // site's Twitter widget id
			$SMTPServer->ClearReplyTos();
			$SMTPServer->Sender = '';
			$SMTPServer->SetFrom($PostMaster, $PMasterRealName);
			$SMTPServer->AddReplyTo($PostMaster, $PMasterRealName);
			break;
	}
}

// fix relative path
if ( left($js ,1)=='.' ) $js  = $WebPath . ltrim($js ,'.');
if ( left($pic,1)=='.' ) $pic = $WebPath . ltrim($pic,'.');
if ( left($inc,1)=='.' ) $inc = $WebPath . ltrim($inc,'.');

// define global variables for page layout colors
$ColorSchemes = $db->get_row(
	"SELECT
		S.SifrText,
		S.SifNVal1,
		S.SifNVal2,
		S.SifNVal3,
		S.SifLVal1,
		S.SifLVal2,
		ST.SifNaziv,
		ST.SifCVal1,
		ST.SifCVal2,
		ST.SifCVal3
	FROM
		Sifranti S
		LEFT JOIN SifrantiTxt ST
			ON S.SifrantID = ST.SifrantID
	WHERE
		S.SifrCtrl = 'PARA'
		AND S.SifrText LIKE 'Page%'
		AND (ST.Jezik = '$lang' OR ST.Jezik IS NULL)
		AND ST.ID IS NOT NULL
	ORDER BY
		S.SifrZapo,
		ST.Jezik
	LIMIT 1"
	);

if ( $db->num_rows==0 ) { // default values
	$ColorScheme = "Default;640;170;170;0;black;white;#666600;#cccccc;#999999;black;#ffffcc;white;#6699ff;black;#999933; ";
	$TextPermalinks = false;
} else {
	$ColorScheme = $ColorSchemes->SifNaziv . ";" .
		(int) $ColorSchemes->SifNVal1 . ";" .
		(int) $ColorSchemes->SifNVal2 . ";" .
		(int) $ColorSchemes->SifNVal3 . ";" .
		(int) $ColorSchemes->SifLVal1 . ";" .
		$ColorSchemes->SifCVal1 . ";" .
		$ColorSchemes->SifCVal2 . ";" .
		$ColorSchemes->SifCVal3 . ";#;#;#;#;#;#;#;#;#;#;#;#;#;#;#; ";
	$TextPermalinks = (bool)(int)$ColorSchemes->SifLVal2;
}

// parse string into values
$CSarr = explode(";", $ColorScheme, 17);

// NOTE: should be put into CSS
$ContentW   = (int)$CSarr[1];
$MenuW      = (int)$CSarr[2];
$ExtraW     = (int)$CSarr[3];
$PageAlign  = (int)$CSarr[4];
$PageColor  = "$CSarr[5]";
$FrameColor = "$CSarr[6]";
$FrmExColor = "$CSarr[7]";
$BackgColor = "$CSarr[8]";
$BckLoColor = "$CSarr[9]";
$BckHiColor = "$CSarr[10]";
$BckExColor = "$CSarr[11]";
$TextColor  = "$CSarr[12]";
$LinkColor  = "$CSarr[13]";
$TxtFrColor = "$CSarr[14]";
$TxtExColor = "$CSarr[15]";
$TitleText  = AppName;

unset($CSarr);
unset($ColorSchemes);
unset($Params);
