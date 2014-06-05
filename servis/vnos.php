<?php
/*~ vnos.php - framework for editing (not ine edit window)
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

/******************************************************************************
* Creates framework for include files (from 'template' folder) which define
* application design.
******************************************************************************/

// include application variables and settings framework
require_once("../_application.php");

// ACL retrieval function
include_once("_userACL.php");

// check if session still active
if ( !isset( $_SESSION['Authenticated'] ) || !$_SESSION['Authenticated'] ) {
	header( "Refresh:1; URL=./" ); // no, login
	die();
}

// get ACL
$Action = $db->get_row("SELECT Action, ACLID FROM SMActions WHERE Action = '". $_GET['Izbor'] ."'");
if ( $Action )
	$ActionACL = userACL( $Action->ACLID );
else
	$ActionACL = "LRWDX";

?>
<!DOCTYPE HTML>
<html>
<head>
<title>Vnos - <?php echo $_GET['Izbor'] ?></title>
<meta name="Author" content="Blaž Kristan (blaz@kristan-sp.si)">
<link rel="stylesheet" type="text/css" href="style.css">
<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/smoothness/jquery-ui.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $js ?>/funcs.js"></script>
<script language="JavaScript" type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script language="JavaScript" type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $js ?>/jquery/jquery.form.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $js ?>/fancybox/jquery.easing-1.3.pack.js"></script>
</head>
<body style="background-color:lightgrey;">
<div id="divVnos">
<?php
if ( $_GET['Izbor'] != "" ) {
	if ( is_file("template/vnos_". $_GET['Izbor'] .".php") )
		// load user template
		include("template/vnos_". $_GET['Izbor'] .".php");
	elseif ( is_file("_template/vnos_" . $_GET['Izbor'] .".php") )
		// load system template
		include("_template/vnos_". $_GET['Izbor'] .".php");
	else
		echo "<br><br><br><br><p align=\"center\" style=\"color:red;\"><B>Template not found!</b></p>\n";
}
?>
</div>
</body>
</html>