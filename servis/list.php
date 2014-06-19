<?php
/*~ list.php - build framework for list of objects (left side window)
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

header('Cache-Control: no-cache');
header('Pragma: no-cache');

/******************************************************************************
* Creates framework for include files (from 'template' folder) which define
* application design. Also invokes _login.php and/or _logout.php depending
* on URL parameters.
******************************************************************************/

// include application variables and settings framework
require_once("../_application.php");

// ACL retrieval function
include_once("_userACL.php");

// determine maximum number of rows to display
$MaxRows = $db->get_var("SELECT SifNVal1 FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='ListMax'");
if ( !$MaxRows ) $MaxRows = 25; // default value

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
} elseif ( isset($_GET['Izbor']) &&
	(is_file(($Mobile ? "mobile": "template") ."/list_". $_GET['Izbor'] .".php") ||
	 is_file(($Mobile ? "_mobile": "_template") ."/list_". $_GET['Izbor'] .".php")) ) {
	$ActionACL      = "LRWDX";
} else {
	$_GET['Izbor']  = "Error";
	$Error = "<p align=\"center\"><b style=\"color:red;\">Not implemented!</b></p>\n";
}

if ( isset($Error) ) header("Refresh:1; URL=./"); // no, login

if ( $Mobile ) {
	echo "<html>\n";
	echo "<head>\n";
	echo "<title>" . $_GET['Izbor'] . "</title>\n";
	echo "<meta name=\"viewport\" content=\"initial-scale=1, maximum-scale=1.0, minimum-scale=1, user-scalable=no, width=device-width\" />\n";
	echo "<meta name=\"MobileOptimized\" content=\"320\" />\n";
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.2/jquery.mobile.min.css\" media=\"screen\" />\n";
	echo "<script language=\"javascript\" type=\"text/javascript\" src=\"".$WebPath ."/js/funcs.js\"></script>\n";
	echo "<script language=\"javascript\" type=\"text/javascript\" src=\"//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js\"></script>\n";
	echo "<script language=\"javascript\" type=\"text/javascript\" src=\"//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.2/jquery.mobile.min.js\"></script>\n";
	echo "</head>\n";
	echo "<body>\n";
} else {}

// error handling
if ( isset($Error) ) {
	echo "<div id=\"list\" data-role=\"page\">\n";
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

// include user query template (preprocessing and updateable queries)
if ( is_file("qry/list_". $_GET['Izbor'] .".php") )
	// load user template
	include("qry/list_". $_GET['Izbor'] .".php");
elseif ( is_file("_qry/list_". $_GET['Izbor'] .".php") )
	// load system template
	include("_qry/list_". $_GET['Izbor'] .".php");

// build "delete" & search links
$DelURL = $FindURL = dirname($_SERVER['PHP_SELF']) . "/list.php?";
foreach ( explode("&", $_SERVER['QUERY_STRING']) as $Param ) {
	// prevent empty parameters (double &)
	if ( $Param == "") continue;
	// split parameter to name and value: x=[name,value]
	$x = explode("=", $Param);
	// check if preprocessing changed parameter
	if ( $_GET[$x[0]] != $x[1] )
		$Param = $x[0] . "=" . $_GET[$x[0]];
	else
		$Param = $x[0] . "=" . $x[1];
	// remove certain parameters
	if ( $x[0] != "Brisi" && $x[0] != "Smer" )
		$DelURL .= $Param . "&";
	if ( $x[0] != "Brisi" && $x[0] != "Smer" && $x[0] != "Find" )
		$FindURL .= $Param . "&";
}
if ( substr($FindURL,-1) == "&" )
	$FindURL = substr($FindURL,0,strlen($FindURL)-1);

if( $Mobile ) {

	echo "<script language=\"javascript\" type=\"text/javascript\">\n";
	echo "<!-- //\n";
	echo "function check(ID, Naziv) {\n";
	echo "if (confirm(\"Do you really want to delete '\"+Naziv+\"'?\")) {\n";
	echo "$.mobile.changePage(window.reloadURL+'&Brisi='+ID, {transition:\"slideup\", reloadPage: true});\n";
	echo "}\n";
	echo "return false;\n";
	echo "}\n";
	echo "function setRefreshURL(URL) {window.reloadURL=URL;}\n";
	echo "$('#list').live('pageinit', function(event) {\n";
	echo "if ( window.tReload ) clearTimeout( window.tReload );\n";
	echo "if (!window.reloadURL) setRefreshURL(\"". substr($DelURL,0,strlen($DelURL)-1) ."\");\n";
	echo "});\n";
	echo "// -->\n";
	echo "</script>\n";

	// include a template (template contains all data manipulation & formatting)
	if ( $_GET['Izbor'] != "" ) {
		if ( is_file("mobile/list_". $_GET['Izbor'] .".php") )
			// load user/custom template
			include("mobile/list_". $_GET['Izbor'] .".php");
		elseif ( is_file("_mobile/list_". $_GET['Izbor'] .".php") )
			// load system template
			include("_mobile/list_". $_GET['Izbor'] .".php");
		else {
			echo "<div id=\"list\" data-role=\"page\">\n";
			echo "\t<div data-role=\"header\" data-theme=\"e\">\n";
			echo "\t\t<h1>Error</h1>\n";
			echo "\t\t<a href=\"#\" title=\"Back\" class=\"ui-btn-left\" data-direction=\"reverse\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-rel=\"back\" data-transition=\"slide\">Back</a>\n";
			echo "\t\t<a href=\"./\" title=\"Home\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Home</a>\n";
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
<script language="JavaScript" type="text/javascript">
<!-- //
var listURL;

function check(ID, Item)
{
	if ( confirm("Do you really want to delete '"+Item+"'?") ) {
		$("#imgClose").hide();
		$("#imgSpinner").show();
		// use setTimeout because of some nasty jQuery bug
		setTimeout(function(){$("#divEdit").text(" ")}, 250); // clear editing template
		$("#divList").load(listURL+"&Brisi="+ID); // automatically corrects spinner (from above)
	}
	return false;
}
window.check = check;

function listRefresh()
{
	if ( window.check ) window.check = null;
	if ( window.tReload ) clearTimeout(window.tReload);
	$("#divList").load(listURL);
}
window.listRefresh = listRefresh;

$(document).ready(function(){
	listURL = "<?php echo substr($DelURL,0,strlen($DelURL)-1); ?>"
	
	$("#imgClose").show();
	$("#imgSpinner").hide();
	// bind to the form's submit event
	$("form[name=ListFind]").submit(function(){
		$(this).ajaxSubmit({
			target: '#divList'
		});
		return false;
	});
	if ( $("#inpFind").val() != "" ) $('#clrFind').show();
	// refresh DIV every 2 min
	if ( window.tReload ) clearTimeout( window.tReload );
	window.tReload = setTimeout(listRefresh,120000);
	// enable scroller/fix DIV size
	fixSize();
});
// -->
</script>

<div id="divNew" class="subtitle">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr class="novo">
	<td>&nbsp;<?php if ( contains($ActionACL, "W") ) { echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$Action->Action."&Action=".$_GET['Action']."&ID=0".(isset($_GET['Tip'])?"&Tip=".$_GET['Tip']:"")."');\">New...</a>"; } ?></td>
	<td width="16"><a href="javascript:toggleFrame()" title="Zapri"><img id="imgClose" src="pic/control.x.gif" height=14 width=14 border="0" alt="Close" class="icon"><img id="imgSpinner" src="pic/control.spinner.gif" height=14 width=14 border="0" alt="" class="icon" style="display:none;"></a></TD>
</tr>
</table>
</div>
<div class="find">
<form name="ListFind" action="<?php echo $FindURL ?>" method="get">
<input type="Text" name="Find" id="inpFind" maxlength="32" value="<?php echo isset($_GET['Find']) ? $_GET['Find'] : ''; ?>" onkeypress="$('#clrFind').show();">
<a id="clrFind" href="javascript:void(0);" onclick="$(this).hide();$('#inpFind').val('').select();$('form[name=ListFind]').submit();"><img src="pic/list.clear.gif" border="0"></a>
</form>
</div>
<div id="divSeznam" style="padding:5px;overflow:auto;">
<?php
if ( is_file("template/list_". $_GET['Izbor'] .".php") )
	// load user template
	include("template/list_". $_GET['Izbor'] .".php");
elseif ( is_file("_template/list_". $_GET['Izbor'] .".php") )
	// load system template
	include("_template/list_". $_GET['Izbor'] .".php");
?>
</div>

<?php
}
?>
