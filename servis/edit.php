<?php
/*~ edit.php - main editing window framework (called by xmenu selection)
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

header('Cache-Control: no-store');
header('Cache-Control: no-cache');
header('Pragma: no-cache');

// include application variables and settings framework
require_once("../_application.php");

// ACL retrieval function
include_once("_userACL.php");

// check if session still active
if ( !(isset($_SESSION['Authenticated']) && $_SESSION['Authenticated']) ) {
	$Error = "<p align=\"center\" style=\"color:red;\"><B>Session expired!</b><br>Please login again.</p>\n";
} else

// get action & ACL
if ( isset($_GET['Action']) )
	$Action = $db->get_row("SELECT Action, ACLID, ActionID FROM SMActions WHERE ActionID = '". $_GET['Action'] ."'");
elseif ( isset($_GET['Izbor']) )
	$Action = $db->get_row("SELECT Action, ACLID, ActionID FROM SMActions WHERE Action = '". $_GET['Izbor'] ."'");

if ( isset($Action) && $Action ) {
	$_GET['Izbor']  = $Action->Action;
	$_GET['Action'] = $Action->ActionID;
	$ActionACL      = userACL( $Action->ACLID );
} else if ( isset($_GET['Izbor']) &&
	(is_file(($Mobile ? "mobile": "template") ."/edit_". $_GET['Izbor'] .".php") ||
	 is_file(($Mobile ? "_mobile": "_template") ."/edit_". $_GET['Izbor'] .".php") ) ) {
	$ActionACL      = "LRWDX";
} else {
	$_GET['Izbor']  = "Error";
	$Error = "<p align=\"center\"><b style=\"color:red;\">Not implemented!</b></p>\n";
}

if ( isset($Error) ) header("Refresh:1; URL=./"); // no, login

if ( $Mobile ) {
	echo "<html>\n";
	echo "<head>\n";
	echo "<title>". $_GET['Izbor'] ."</title>\n";
	echo "<meta name=\"viewport\" content=\"initial-scale=1, maximum-scale=1.0, minimum-scale=1, user-scalable=no, width=device-width\" />\n";
	echo "<meta name=\"MobileOptimized\" content=\"320\" />\n";
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.2/jquery.mobile.min.css\" media=\"screen\" />\n";
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$WebPath ."/js/jquery/css/jqm-datebox.min.css\" />\n";
	echo "<script language=\"javascript\" type=\"text/javascript\" src=\"".$WebPath ."/js/funcs.js\"></script>\n";
	echo "<script language=\"javascript\" type=\"text/javascript\" src=\"//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js\"></script>\n";
	echo "<script language=\"javascript\" type=\"text/javascript\" src=\"//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.2/jquery.mobile.min.js\"></script>\n";
	echo "<script type=\"text/javascript\" src=\"".$WebPath ."/js/jquery/jqm-datebox.core.min.js\"></script>\n";
	echo "<script type=\"text/javascript\" src=\"".$WebPath ."/js/jquery/jqm-datebox.mode.calbox.min.js\"></script>\n";
	echo "<script type=\"text/javascript\" src=\"".$WebPath ."/js/jquery/jquery.mobile.datebox.i18n.en_US.utf8.js\"></script>\n";
	//echo "<script type=\"text/javascript\" src=\"".$WebPath ."/js/jquery/jquery.mobile.datebox.i18n.sl.utf8.js\"></script>\n";
	echo "</head>\n";
	echo "<body>\n";
} else {}

// error handling
if ( isset( $Error ) ) {
	echo "<div id=\"edit\" data-role=\"page\">\n";
	echo "\t<div data-role=\"header\" data-theme=\"e\">\n";
	echo "\t\t<h1>". $_GET['Izbor'] ."</h1>\n";
	echo "\t\t<a href=\"./\" title=\"Home\" class=\"ui-btn-left\" data-iconpos=\"notext\" data-icon=\"home\" data-ajax=\"false\">Home</a>\n";
	echo "\t</div>\n";
	echo "\t<div data-role=\"content\">\n";
	echo "\t<div class=\"ui-body ui-body-d ui-corner-all\" style=\"padding:1em;text-align:center;\">\n";
	echo "\t" . $Error;
	echo "\t</div>\n";
	echo "\t</div>\n";
	echo "</div>\n"; // page

	if ( $Mobile ) {
		echo "</body>\n";
		echo "</html>\n";
	}
	die();
}

// load preprocessing/DB query template
if ( $_GET['Izbor'] != "" ) {
	if ( is_file("qry/edit_". $_GET['Izbor'] .".php") )
		// load user template
		include("qry/edit_". $_GET['Izbor'] .".php");
	elseif ( is_file("_qry/edit_". $_GET['Izbor'] .".php") )
		// load system template
		include("_qry/edit_". $_GET['Izbor'] .".php");
}

if( $Mobile ) {

	// include a template (template contains all data manipulation & formatting)
	if ( $_GET['Izbor'] != "" ) {
		if ( is_file("mobile/edit_". $_GET['Izbor'] .".php") )
			// load user/custom template
			include("mobile/edit_". $_GET['Izbor'] .".php");
		elseif ( is_file("_mobile/edit_". $_GET['Izbor'] .".php") )
			// load system template
			include("_mobile/edit_". $_GET['Izbor'] .".php");
		else {
			echo "<div id=\"edit\" data-role=\"page\">\n";
			echo "\t<div data-role=\"header\" data-theme=\"e\">\n";
			echo "\t\t<h1>Napaka</h1>\n";
			echo "\t\t<a href=\"./\" title=\"Home\" class=\"ui-btn-left\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Home</a>\n";
			echo "\t</div>\n";
			echo "\t<div data-role=\"content\">\n";
			echo "\t<div class=\"ui-body ui-body-d ui-corner-all\" style=\"padding:1em;text-align:center;\">\n";

			echo "<b style=\"color:red;\">Not implemented!</b>\n";

			echo "\t</div>\n";
			echo "\t</div>\n";

			echo "</div>\n"; // page
		}
	}
	echo "</body>\n";
	echo "</html>\n";

} else {

?>
<div class="subtitle">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
	<td><div id="ToggleFrame" style="display:none;">&nbsp;<A HREF="javascript:toggleFrame()"><img src="pic/control.frame.gif" height="14" width="14" alt="Preklop celo/zmanjšano okno" border="0" align="absmiddle" class="icon">&nbsp;List</a></div></td>
	<td id="editNote" align="right"><?php echo $_GET['Izbor'] ?> - EDIT&nbsp;&nbsp;</td>
</tr>
</table>
</div>
<div id="divContent" style="padding: 5px; overflow: auto;">
<?php
// load main editing template
if ( $_GET['Izbor'] != "" ) {
	if ( is_file("template/edit_". $_GET['Izbor'] .".php") )
		// load user template
		include("template/edit_". $_GET['Izbor'] .".php");
	elseif ( is_file("_template/edit_". $_GET['Izbor'] .".php") )
		// load system template
		include("_template/edit_". $_GET['Izbor'] .".php");
	else
		echo "<br><br><br><br><p align=\"center\" style=\"color:red;\"><B>Template not found!</b></p>\n";
}
?>
</div>
<?php
}
?>
