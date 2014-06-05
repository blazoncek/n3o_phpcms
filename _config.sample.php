<?php
/*
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

/**
 * The base configurations for N3O site.
 */
define('AppName',    'N3O-CMS'); // change to your liking
define('AppVer',     '2.2.0');   // DO NOT CHANGE until DB changes
define('PostMaster', 'admin@domain.com');
define('DefLang',    'En');      // default language (must exist in DB)

/**
 * SQL settings - You can get this info from your web host
 */
define('SQLType',  'MySQL');	 // only MySQL implemented fully (MsSQL, Ora)
define('DSN',      'N3OCMS');    // DB name
define('DBUS',     'dbuser');    // username
define('DBPW',     'dbpass');    // password
define('DBHOST',   'localhost'); // host
define('DBCS',     'utf8');      // character set
define('DBCOLLATE','utf8_general_ci'); // collation

/**
 * Authentication Unique Keys and Salts.
 */
define('PWSALT', 'N3O_CMS:');  // be carefull to update passwords in DB

/**
 * Absolute path to home directory.
 */
define('ABSPATH', dirname(__FILE__));
	
/**
 * LDAP settings
 */
define('LDAPSERVER', "srv.domain.com");
define('LDAPCHECK',  "OU=ou,DC=domain,DC=com");

/**
 * Google Analytics ID
 */
//define('ANALYTICS_ID', 'UA-xxxxxxx-x');

/**
 * Apache development settings
 */
//ini_set('error_reporting', E_ALL-E_NOTICE);
//ini_set('display_errors', 1);
?>