<?php
/*~ select_text.php - select a text from DB and insert local link
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

// include application variables and settings framework
require_once("../_application.php");

// ACL retrieval function
require_once("_userACL.php");

if ( !isset($_GET['Find']) ) $_GET['Find'] = "";
if ( !isset($_GET['Tip']) )  $_GET['Tip'] = "";
if ( !isset($_GET['Sort']) ) $_GET['Sort'] = "";

// build search links
$FindURL = dirname($_SERVER['PHP_SELF']) ."/". basename($_SERVER['PHP_SELF']) ."?";
foreach ( explode("&", $_SERVER['QUERY_STRING']) as $Param ) {
	// prevent empty parameters (double &)
	if ( $Param == "") continue;
	// split parameter to name and value: x=[name,value]
	$x = explode( "=", $Param );
	// check if preprocessing changed parameter
	if ( $_GET[$x[0]] != $x[1] )
		$Param = $x[0] . "=" . $_GET[$x[0]];
	else
		$Param = $x[0] . "=" . $x[1];
	// remove certain parameters
	switch ($x[0]) {
		case "Find":
		case "pg":
			break;
		default:
			$FindURL .= $Param . "&";
			break;
	}
}
if ( substr($FindURL,-1) == "&" )
	$FindURL = substr($FindURL,0,strlen($FindURL)-1);

?>
<!DOCTYPE HTML>
<HTML>
<head>
<meta name="Author" content="Blaž Kristan (blaz@kristan-sp.si)">
<link rel="stylesheet" type="text/css" href="style.css">
<style>
INPUT.text { border:silver solid 1px; font-size: 10px; }
INPUT.check { border: none; padding:0px; margin:0px; }
</style>
<script type="text/javascript" src="<?php echo $js ?>/jquery/jquery.js"></script>
<script type="text/javascript" src="<?php echo $js ?>/tiny_mce/tiny_mce_popup.js"></script>
<script type="text/javascript">
<!-- //
function fixSize() {
	var list  = $("#divList").width(0).height(0);
	list.width( $(window).width() ).height( $(window).height() - list.position().top );
}
$(document).ready(fixSize);
$(window).resize(fixSize);

function insertURL(url) {
	var refWin = tinyMCEPopup.getWindowArg("window");
	var refFld = refWin.document.getElementById(tinyMCEPopup.getWindowArg("input"));
	// insert information now
	refFld.value = url;
	// Try to fire the onchange event
	try {
		refFld.onchange();
	} catch (e) {
		// Skip it
	}
	// close popup window
	tinyMCEPopup.close();
//	window.close();
}

var FileBrowserDialogue = {
	init : function () {
		// Here goes your code for setting your custom things onLoad.
		var res = tinyMCEPopup.getWindowArg("resizable");
		var inline = tinyMCEPopup.getWindowArg("inline");
	},
	mySubmit : function () {
		// Here goes your code to insert the retrieved URL value into the original dialogue window.
		var URL = document.my_form.my_field.value;
		var win = tinyMCEPopup.getWindowArg("window");
		var input = tinyMCEPopup.getWindowArg("input");

		// insert information now
		win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

	}
}
tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);
//-->
</script>
</head>
<body style="background-color:lightgrey;">
<div class="find" style="margin-top:5px;border-top:1px solid black;">
<form name="ListFind" action="<?php echo $FindURL ?>" method="get">
<input type="Text" name="Find" id="inpFind" maxlength="32" value="<?php echo $_GET['Find']; ?>" onkeypress="$('#clrFind').show();" onfocus="if ($('#inpFind').val()!='') $('#clrFind').show();">
<a id="clrFind" href="javascript:void(0);" onclick="$(this).hide();$('#inpFind').val('').select();"><img src="pic/list.clear.gif" border="0"></a>
</form>
</div>
<?php
	// define sort order
	$Sort = "B.BesediloID DESC";
	if ( $_GET['Sort'] == "date" )
		$Sort = "B.Datum DESC";
	elseif ( $_GET['Sort'] == "name" )
		$Sort = "B.Ime";
	
	$List = $db->get_results(
		"SELECT DISTINCT B.BesediloID AS ID, B.Ime AS Name, B.Datum, B.Izpis, B.ACLID
		FROM Besedila B
			LEFT JOIN BesedilaOpisi BO ON B.BesediloID = BO.BesediloID
		WHERE 1=1 " .
			(($_GET['Find']=="")? "": "AND (B.Ime LIKE '%".trim($_GET['Find'])."%' OR BO.Naslov LIKE '%".trim($_GET['Find'])."%' OR BO.Povzetek LIKE '%".trim($_GET['Find'])."%')").
			(($_GET['Tip']=="")? "": "AND B.Tip='".$_GET['Tip']."' ") .
		"ORDER BY $Sort"
	);

	$RecordCount = count($List);
	
	// determine maximum number of rows to display
	$MaxRows = $db->get_var( "SELECT SifNVal1 FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='ListMax'" );
	if ( !$MaxRows ) $MaxRows = 25; // default value

	// are we requested do display different page?
	$Page = !isset($_GET['pg']) ? 1 : (int) $_GET['pg'];
	
	// number of possible pages
	$NuPg = (int) (($RecordCount-1) / $MaxRows) + 1;
	
	// fix page number if out of limits
	$Page = min(max($Page, 1), $NuPg);
	
	// start & end page
	$StPg = min(max(1, $Page - 5), max(1, $NuPg - 10));
	$EdPg = min($StPg + 10, min($Page + 10, $NuPg));
	
	// previous and next page numbers
	$PrPg = $Page - 1;
	$NePg = $Page + 1;
	
	// start and end row from recordset
	$StaR = ($Page - 1) * $MaxRows + 1;
	$EndR = min(($Page * $MaxRows), $RecordCount);
	
	echo "<DIV ID=\"divSort\" style=\"text-align: center; background-color:whitesmoke;margin-top:5px;border-top: silver 1px solid; border-bottom: silver 1px solid;\">\n";

	// sorting and filtering options
	echo "<TABLE WIDTH=\"100%\" BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\" CLASS=\"novo\">\n";
	echo "<TR>\n";
	echo "<TD>Sort:\n";
	echo "<SELECT NAME=\"Sort\" SIZE=\"1\" ONCHANGE=\"document.location.href='". preg_replace("/&Sort=[^&]/i","",$FindURL) .($_GET['Find']!='' ? '&Find='.$_GET['Find'] : '') ."&Sort='+this[this.selectedIndex].value;\">\n";
	echo "<OPTION VALUE=\"id\">Zaporedje vnosa</OPTION>\n";
	echo "<OPTION VALUE=\"name\"".(($_GET['Sort']=="name")? " SELECTED": "").">Ime</OPTION>\n";
	echo "<OPTION VALUE=\"date\"".(($_GET['Sort']=="date")? " SELECTED": "").">Datum</OPTION>\n";
	echo "</SELECT>\n";
	echo "</TD>\n";
	echo "<TD ALIGN=\"right\">Type:\n";
	echo "<SELECT NAME=\"Tip\" SIZE=\"1\" ONCHANGE=\"document.location.href='". preg_replace("/&Tip=[^&]/i","",$FindURL) .($_GET['Find']!='' ? '&Find='.$_GET['Find'] : '') ."&Tip='+this[this.selectedIndex].value;\">\n";
	echo "<OPTION VALUE=\"\">- vsi tipi -</OPTION>\n";
	$Tipi = $db->get_col( "SELECT SifrText FROM Sifranti WHERE SifrCtrl='BESE' ORDER BY SifrCtrl, SifrZapo" );
	if ( $Tipi ) foreach ( $Tipi as $Tip )
		echo "<OPTION VALUE=\"$Tip\"".(($_GET['Tip']==$Tip)? " SELECTED": "").">$Tip</OPTION>\n";
	echo "</SELECT>\n";
	echo "</TD>\n";
	echo "</TR>\n";
	echo "</TABLE>\n";

	if ( $NuPg > 1 ) {
		echo "<DIV CLASS=\"pg\">\n";
		if ( $StPg > 1 )
			echo "<A HREF=\"". $FindURL .($_GET['Find']!='' ? '&Find='.$_GET['Find'] : '') ."&pg=".($StPg-1)."\">&laquo;</A>\n";
		if ( $Page > 1 )
			echo "<A HREF=\"". $FindURL .($_GET['Find']!='' ? '&Find='.$_GET['Find'] : '') ."&pg=$PrPg\">&lt;</A>\n";
		for ( $i = $StPg; $i <= $EdPg; $i++ ) {
			if ( $i == $Page )
				echo "<FONT COLOR=\"red\"><B>$i</B></FONT>\n";
			else
				echo "<A HREF=\"". $FindURL .($_GET['Find']!='' ? '&Find='.$_GET['Find'] : '') ."&pg=$i\">$i</A>\n";
		}
		if ( $Page < $EdPg )
			echo "<A HREF=\"". $FindURL .($_GET['Find']!='' ? '&Find='.$_GET['Find'] : '') ."&pg=$NePg\">&gt;</A>\n";
		if ( $NuPg > $EdPg )
			echo "<A HREF=\"". $FindURL .($_GET['Find']!='' ? '&Find='.$_GET['Find'] : '') ."&pg=".($EdPg<$NuPg? $EdPg+1: $EdPg)."\">&raquo;</A>\n";
		echo "</DIV>\n";
	}
	echo "</DIV>\n";

	if ( count( $List ) == 0 ) {
		echo "<div class=\"frame\" style=\"display:table;height:100px;width:308px;margin:5px;\">";
		echo "<div style=\"background-color: white;display: table-cell;text-align: center;vertical-align: middle;\"><b>No data!</b></div>\n";
		echo "</div>\n";
	} else {
		echo "<DIV id=\"divList\" style=\"overflow-y:auto;\">\n";
		echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n";
		$BgCol = "white";
		$i = $StaR-1;
		while ( $i < $EndR ) {
			// get list item
			$Item = $List[$i++];
			// get ACL
			$ACL = userACL( $Item->ACLID );
			if ( contains($ACL, "L") ) {
				echo "<tr onmouseover=\"this.style.backgroundColor='white';\" onmouseout=\"this.style.backgroundColor='';\">\n";
				echo "<td><a href=\"javascript:insertURL('?ID=". $Item->ID ."')\" title=\"$Item->Name ($Item->ID)\">".left($Item->Name,30).(strlen($Item->Name)>30?"...":"")."</a></td>\n";
				echo "</tr>\n";
			}
		}
		echo "</table>\n";
		echo "</DIV>\n";
	}
?>
</body>
</HTML>