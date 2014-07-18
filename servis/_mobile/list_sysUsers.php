<?php
/*~ list_Uporabniki.php - List admin users
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

// define default values for URL ID and Find parameters (in case not defined)
if ( !isset($_GET['Find']) ) $_GET['Find'] = "";

// get all users
$List = $db->get_results(
	"SELECT UserID, UserName, Name, Active
	FROM SMUser"
	.($_GET['Find'] == "" ? " " : " WHERE Name LIKE '%".$db->escape($_GET['Find'])."%' OR Username LIKE '%".$db->escape($_GET['Find'])."%' OR Email LIKE '%".$db->escape($_GET['Find'])."%'" ).
	"ORDER BY Name"
	);

$RecordCount = count($List);
?>
<SCRIPT Language="JAVASCRIPT">
<!--//
$('#list').live('pageinit', function(event){
	$("input[name=Find]").bind("change", function(event,ui){
		var URL = '<?php echo $_SERVER['PHP_SELF']; ?>?Action=<?php echo $_GET['Action']; ?>';
		$("select").each(function(index){
			if ( this[this.selectedIndex].value != "" )
				URL += '&' + this.name + '=' + this[this.selectedIndex].value;
		});
		if ( this.value != "" ) URL += '&Find='+this.value;
		document.location.href = URL;
	});
	$("select").bind("change", function(event,ui){
		var URL = '<?php echo $_SERVER['PHP_SELF']; ?>?Action=<?php echo $_GET['Action'] . ($_GET['Find']!=""? "&Find=".$_GET['Find']: ""); ?>';
		$("select").each(function(index){
			if ( this[this.selectedIndex].value != "" )
				URL += '&' + this.name + '=' + this[this.selectedIndex].value;
		});
		document.location.href = URL;
	});
});
//-->
</SCRIPT>
<?php
// are we requested do display different page?
$Page = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;
// number of possible pages
$NuPg = (int) (($RecordCount-1) / $MaxRows) + 1; // $MaxRows defined in list.php
// fix page number if out of limits
$Page = min(max($Page, 1), $NuPg);
// start & end page
$StPg = min(max($Page - 2,1), max(1, $NuPg - 4));
$EdPg = min($StPg + 4, min($Page + 4, $NuPg));
// previous and next page numbers
$PrPg = $Page - 1; // <1 == no previous page
$NePg = $Page + 1; // >$NuPg == no next page

// start and end row from recordset
$StaR = ($Page - 1) * $MaxRows + 1;
$EndR = min(($Page * $MaxRows), $RecordCount);

echo "<div id=\"list\" data-role=\"page\" data-title=\"Users\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Users</h1>\n";
echo "<a href=\"./#menu". left($_GET['Action'],2) ."\" title=\"Back\" class=\"ui-btn-left\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-ajax=\"false\" data-transition=\"slide\">Back</a>\n";
echo "<a href=\"edit.php?Izbor=".$_GET['Izbor']."&ID=0\" title=\"Add\" class=\"ui-btn-right\" data-iconpos=\"notext\" data-icon=\"plus\" data-ajax=\"false\">Add</a>\n";
echo "</div>\n";
echo "<div data-role=\"content\">\n";
echo "<div style=\"margin-bottom:30px;\"><input type=\"search\" name=\"Find\" id=\"search\" value=\"". ($_GET['Find']!=""? $_GET['Find']: "") ."\" data-theme=\"d\" /></div>\n";

// display results
if ( $RecordCount == 0 ) {

	echo "<div class=\"ui-body ui-body-d ui-corner-all\" style=\"color:red;padding:1em;text-align:center;\">\n";
	echo "<B>No data!</B>\n";
	echo "</div>\n";

} else {
	echo "<ul data-role=\"listview\" data-theme=\"d\" data-split-icon=\"delete\" data-split-theme=\"d\">\n";
	//foreach ( $List as $Item ) {
	$i = $StaR-1;
	while ( $i < $EndR ) {
		// get list item
		$Item = $List[$i++];
		if ( $Item->UserID != 1 || contains($ActionACL,"D") ) {
			echo "<li>";
			echo "<a href=\"edit.php?Izbor=".$_GET['Izbor']."&ID=$Item->UserID\" data-ajax=\"false\">";
			echo "<h3>". $Item->Name ."</h3>";
			echo "<p>". $Item->UserName ."</p>";
			echo (($Item->Active)? "":"<span class=\"ui-li-count\">neaktiven</span>");
			echo "</a>";
			if ( $Item->UserID > 1 && contains($ActionACL,"D") )
				echo "<a href=\"#\" onclick=\"check('" . $Item->UserID . "','" . $Item->Name . "');\">Delete</a>";
			echo "</li>\n";
		}
	}
	echo "</ul>\n";
}

echo "</div>\n";

if ( $NuPg > 1 ) {
	echo "<div data-role=\"footer\">\n";
	echo "<div data-role=\"navbar\" data-theme=\"a\">\n";
	echo "<ul>\n";
	for ( $i = $StPg; $i <= $EdPg; $i++ ) {
		echo "<li>";
		echo "<a href=\"list.php?Action=". $_GET['Action'] . ($_GET['Find']!=""? "&Find=".$_GET['Find']: "") ."&pg=$i\" data-ajax=\"false\"". ( $i == $Page ? " data-theme=\"b\"" : "" ) .">";
		if ( $i == $EdPg && $EdPg < $NuPg )
			echo "&gt;";
		else if ( $i == $StPg && $StPg > 1 )
			echo "&lt;";
		else
			echo "$i";
		echo "</a>";
		echo "</li>\n";
	}
	echo "</ul>\n";
	echo "</div>\n";
	echo "</div>\n";
}
echo "</div>\n"; // page
?>