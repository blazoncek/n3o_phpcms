<?php
/*~ db-init.php - database initialization/update
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
ini_set('display_errors', '1');
error_reporting(E_ALL);

/***************************************************************
* Depending on URL parameter "build" and/or "update"           *
* opens _qry/_baseTables.sql or qry/_updateTables.sql          *
* SQL script and executes statement by statement (; delimited) *
* against default database (usualy MySQL).                     *
***************************************************************/

// base configuration
require_once('../_config.php');
require_once('../inc/mobile_device_detect.php');

$Tablet = $Mobile = mobile_device_detect();
if ( $Mobile && preg_match('/(ipad|tab)/i',$_SERVER['HTTP_USER_AGENT']) ) {
	$Mobile = false;
	$Tablet = true;
}

/*------------------------------------------------------------------------------
D A T A B A S E   S U P P O R T
------------------------------------------------------------------------------*/
// Include ezSQL core
require_once(ABSPATH .'/inc/ezSQL/ezsql_core.php');

// Initialise database object and establish a connection
// at the same time - db_user / db_password / db_name / db_host
switch (SQLType) {
	case "MySQL":
		// Include ezSQL database specific component (in this case mySQL)
		require_once(ABSPATH .'/inc/ezSQL/ezsql_mysql.php');
		// create DB object (& connect to DB)
		$db = new ezSQL_mysql(DBUS, DBPW, DSN, DBHOST);
		// fix UTF8 handling
		$db->query("SET NAMES '". DBCS ."' COLLATE '". DBCOLLATE ."';");
		if ( $db->last_error ) {
			// display connect error
			file_put_contents(ABSPATH .'/media/_errors_.txt', date("Y-m-d H:i:s ") .'Query '. $db->last_error ."\n", FILE_APPEND);
			header("Refresh:1; URL=". $WebURL ."/error.php");
			die();
		}
		$db->hide_errors();
		break;
	case "MsSQL":
		// Include ezSQL database specific component (in this case msSQL)
		require_once(ABSPATH .'/inc/ezSQL/ezsql_mssql.php');
		// create DB object (& connect to DB)
		$db = new ezSQL_mssql(DBUS, DBPW, DSN, DBHOST);
		$db->hide_errors();
		break;
	default:
		file_put_contents(ABSPATH .'/media/_errors_.txt', date("Y-m-d H:i:s ") ."Wrong DB type selected.\n", FILE_APPEND);
		header( "Refresh:1; URL=". $WebURL ."/error.php" );
		die();
}

function left($str, $count=1)
{
	return substr( $str, 0, $count );
}

function db_execSQLfile($file)
{
	global $db;

	$SQL = file_get_contents($file, FILE_TEXT);

	// parse SQL string into statements
	$SQL = str_replace("\r\n",    "\n",      $SQL); // remove CR
	$SQL = str_replace(";\n",     ":,:.:\n", $SQL);
	$SQL = str_replace(";",       ",.",      $SQL); // NOTE: see below
	$SQL = str_replace(":,:.:\n", ";\n",     $SQL);

	$SQLcmds = explode(";\n", $SQL);

	// execute each SQL statement
	foreach ( $SQLcmds as $SQLcmd ) {
		// cleanup SQL statement
		$SQLcmd = preg_replace("/--[^\r\n]*/i", '', $SQLcmd); // strip comment
		$SQLcmd = preg_replace('/\s\s+/', ' ', $SQLcmd); // reduce whitespace
		$SQLcmd = str_replace(",.", ";", $SQLcmd); // correct semicolon (from above)

		// if nonempty string
		if ( rtrim(ltrim($SQLcmd)) != "" ) {
			// execute query (cache results for SELECT)
			$db->query($SQLcmd);
/*
			if ( $db->last_error ) {
				echo $db->last_error ." | ". $SQLcmd ."<br>\n";
				$db->last_error = null;
			}
*/
		}
	}
}
?>
<!DOCTYPE HTML>
<HTML>
<HEAD>
<TITLE>Servis</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
<META NAME="Author" CONTENT="Blaž Kristan (blaz@kristan-sp.si)">
<LINK REL=stylesheet TYPE="text/css" HREF="style.css">
<STYLE>
HTML, BODY {
	margin:0; padding:0;
	height:100%;
}
BODY {
	color: black;
	font-family: Verdana,Arial,Helvetica;
	font-size: 11px;
}
BODY {
	background-color: #ECEBE7;
}
A { color: #6699CC;text-decoration: none; }
A:Hover { text-decoration: underline; }
INPUT { border: inset 1px; color: black; }
INPUT.but {
	color:white;
	background-color: #6699CC;
	border-top: solid #99CCFF 1px;
	border-left: solid #99CCFF 1px; 
	border-bottom: solid #003366 1px;
	border-right: solid #003366 1px;
	padding: 3px 5px;
	cursor: hand;
}
#nojs {
	padding:5px;
	background-color:#ff0000;
	font-size:16px;
	color:white;
	text-align:center;
}
#float {
	height: 50%;
	margin-bottom: <?php echo ($Mobile ? "-128px" : "-200px") ?>;
}
#loginframe {
	background: white url( pic/ozadje.jpg ) bottom right no-repeat;
	border: #174A7D solid 1px;
	border-radius: 5px;
	clear: left;
	width: <?php echo (!$Mobile ? "412px" : "100%") ?>;
	height: <?php echo (!$Mobile ? "325px" : "256px") ?>;
	margin: 0 auto;
	overflow: hidden;
	position: relative;
	-webkit-border-radius: 5px;
	-webkit-box-shadow: rgba(0,0,0, .5) 3px 3px 5px;
	-moz-border-radius: 5px;
	-moz-box-shadow: rgba(0,0,0, .5) 3px 3px 5px;
}
#loginframe FORM {
	border: #6699CC solid 1px;
	background: WhiteSmoke;
	margin-left: auto;
	margin-right: auto;
	width: 70%;
	padding: 10px;
}
#loginframe INPUT.txt {
	width: 90%;
}
#img {
	background: transparent url( pic/servis.gif ) top left no-repeat;
	height: 160px;
	margin-bottom: <?php echo ($Mobile ? "-80px" : "0") ?>;
	border-top-right-radius: 3px;
	border-top-left-radius: 3px;
	-webkit-border-top-right-radius: 3px;
	-webkit-border-top-left-radius: 3px;
	-moz-border-top-right-radius: 3px;
	-moz-border-top-left-radius: 3px;
}
#copy {
	position: absolute;
	bottom: 0px;
	background-color: #174A7D;
	color: white;
	width: 100%;
	padding: 3px 0;
	text-align: center;
	border-bottom-right-radius: 3px;
	border-bottom-left-radius: 3px;
	-webkit-border-bottom-right-radius: 3px;
	-webkit-border-bottom-left-radius: 3px;
	-moz-border-bottom-right-radius: 3px;
	-moz-border-bottom-left-radius: 3px;
}
#copy A { color: white; }
#msg {
	color: red;
	text-align: center;
	margin: 20px 0;
	font-size: 14px;
}
@media only screen and (max-width: 480px) {
	#float {
		margin-bottom: -85px;
	}
	#loginframe {
		width: 400px;
		height: 170px;
	}
	#img {
		margin-bottom: -133px;
		background-size: 194px auto;
	}
	#msg {
		margin-top: 40px;
	}
}
@media only screen and (max-width: 450px) {
	#float {
		margin-bottom: -100px;
	}
	#loginframe {
		width: 270px;
		height: 200px;
	}
	#img {
		margin-bottom: -110px;
		background-size: 194px auto;
	}
	#msg {
		margin-top: 40px;
	}
}
@media only screen and (-webkit-min-device-pixel-ratio: 1.5),
       only screen and (min--moz-device-pixel-ratio: 1.5),
       only screen and (min-resolution: 240dpi) {
	BODY {
		background-size: 128px auto;
	}
	#loginframe {
		background-size: 206px auto;
	}
}
</STYLE>
<SCRIPT LANGUAGE="javascript" TYPE="text/javascript">
<!-- do not allow to run in a frame
if (self.parent.frames.length != 0)
	self.parent.location=document.location;
//-->
</SCRIPT>
</HEAD>
<BODY>
<noscript><div id="nojs"><b>Please enable JavaScript for this site to function properly.</b></div></noscript>
<?php
	if (stristr($_SERVER['QUERY_STRING'],'build')) {

		if (file_exists(dirname(__FILE__) ."/_qry/_baseTables". SQLType .".sql"))
			db_execSQLfile(dirname(__FILE__) ."/_qry/_baseTables". SQLType .".sql");
		if (file_exists(dirname(__FILE__) . "/qry/_customTables". SQLType .".sql"))
			db_execSQLfile(dirname(__FILE__) ."/qry/_customTables". SQLType .".sql");
		if (stristr($_SERVER['QUERY_STRING'],'populate') && (file_exists(dirname(__FILE__) ."/_qry/_populateTables". SQLType .".sql")))
			db_execSQLfile(dirname(__FILE__) ."/_qry/_populateTables". SQLType .".sql");
		if ( !$db->captured_errors ) {
			header("Refresh: 15; URL=./");
			echo "<B>Database created!</B><br>";
		} else {
			foreach ( $db->captured_errors as $error ) {
				echo $error['error_str'] .' | '. $error['query'] ."<br>\n";
			}
			echo "<B>Errors creating database!</B><br>";
		}

	} elseif (stristr($_SERVER['QUERY_STRING'],'populate')) {
	
		if ((file_exists(dirname(__FILE__) ."/_qry/_populateTables". SQLType .".sql")))
			db_execSQLfile(dirname(__FILE__) ."/_qry/_populateTables". SQLType .".sql");
		if ( !$db->captured_errors ) {
			header("Refresh: 15; URL=./");
			echo "<B>Database populated!</B><br>";
		} else {
			foreach ( $db->captured_errors as $error ) {
				echo $error['error_str'] .' | '. $error['query'] ."<br>\n";
			}
			echo "<B>Errors populating database!</B><br>";
		}

	} elseif (stristr($_SERVER['QUERY_STRING'],'update')) {

		if (file_exists( dirname(__FILE__) ."/qry/_updateTables.sql")) {
			db_execSQLfile(dirname(__FILE__) ."/qry/_updateTables.sql");
			// rename original file after the update (can't be run twice)
			@rename(dirname(__FILE__) ."/qry/_updateTables.sql", dirname(__FILE__) ."/qry/_updateTables_". date("Ymd") .".sql" );
		}
		if ( !$db->captured_errors ) {
			header("Refresh: 15; URL=./");
			echo "<B>Database updated!</B><br>";
		} else {
			foreach ( $db->captured_errors as $error ) {
				echo $error['error_str'] .' | '. $error['query'] ."<br>\n";
			}
			echo "<B>Errors updating database!</B><br>";
		}

	} else {

?>
<div id="float"></div>
<div id="loginframe"><div id="img"></div>
<div style="margin: 0 20px 64px; min-height: 64px;">
	<FONT COLOR="red"><B>Database not properly initialized.</B></FONT><br>
	Do you want to initialize the database?<BR><BR>
	<DIV ALIGN="center">
	<INPUT TYPE="Button" VALUE=" Yes " ONCLICK="document.location.href='db-init.php?build&populate'" CLASS="but"> &nbsp;
	<INPUT TYPE="Button" VALUE=" Create " ONCLICK="document.location.href='db-init.php?build'" CLASS="but"> &nbsp;
	<INPUT TYPE="Button" VALUE=" Populate " ONCLICK="document.location.href='db-init.php?populate'" CLASS="but"> &nbsp;
	<INPUT TYPE="Button" VALUE=" Update " ONCLICK="document.location.href='db-init.php?update'" CLASS="but"> &nbsp;
	<INPUT TYPE="Button" VALUE=" No " ONCLICK="document.location.href='./'" CLASS="but">
	</DIV>
</div>
<div id="copy">Copyright &copy; 2007-<?php echo date('Y') ?>, <a href="http://blaz.at/home/" target="_top">Blaž Kristan</a></div>
<?php

	}
?>
</div>
</BODY>
</HTML>
