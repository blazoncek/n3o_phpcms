<?php
/*~ inc_BesediloTags.php - adding tags
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

// add category
if ( isset($_GET['newtag']) && $_GET['newtag'] != "" ) {
	try {
		$db->query( "START TRANSACTION" );
		$ID = $db->get_var( "SELECT TagID FROM Tags WHERE TagName='".$_GET['newtag']."'" );
		if ( $ID ) {
			if ( !$db->get_var( "SELECT ID FROM BesedilaTags ".
				"WHERE BesediloID=".(int)$_GET['BesediloID']." AND TagID=".$ID ) )
				$db->query(
					"INSERT INTO BesedilaTags (BesediloID, TagID) ".
					"VALUES (".(int)$_GET['BesediloID'].",".$ID.")" );
		} else {
			$db->query(
				"INSERT INTO Tags (TagName) ".
				"VALUES ('".$_GET['newtag']."')" );
			$ID = $db->get_var( "SELECT TagID FROM Tags WHERE TagName='".$_GET['newtag']."'" );
			$db->query(
				"INSERT INTO BesedilaTags (BesediloID, TagID) ".
				"VALUES (".(int)$_GET['BesediloID'].",".$ID.")" );
		}
		$db->query( "COMMIT" );
	} catch (Exception $e) {
		$db->query( "ROLLBACK TRANSACTION" );
	}
}

// remove category
if ( isset($_GET['deltag']) && $_GET['deltag'] != "" ) {
	try {
		$db->query( "START TRANSACTION" );
		$ID = $db->get_var( "SELECT TagID FROM BesedilaTags WHERE ID=".(int)$_GET['deltag'] );
		$db->query( "DELETE FROM BesedilaTags WHERE ID=".(int)$_GET['deltag'] );
		try {
			@$db->query( "DELETE FROM Tags WHERE TagID=".$ID );
		} catch (Exception $e) {}
		$db->query( "COMMIT" );
	} catch (Exception $e) {
		$db->query( "ROLLBACK TRANSACTION" );
	}
}

$ACLID = $db->get_var( "SELECT ACLID FROM Besedila WHERE BesediloID = ".(int)$_GET['BesediloID'] );
if ( $ACLID )
	$ACL = userACL( $ACLID );
else
	$ACL = "LRWDX";

?>
<script language="JavaScript" type="text/javascript">
<!-- //
/*
$("#autocomplete").on("listviewbeforefilter", function(e, data) {
    var $ul = $(this),
		$input = $(data.input),
		value = $input.val(),
		html = "";
	$ul.html("");
	if ( value && value.length > 1 ) {
		$ul.html( "<li><div class='ui-loader'><span class='ui-icon ui-icon-loading'></span></div></li>" );
		$ul.listview("refresh");
		$.ajax({
			url: "<?php echo $_SERVER['PHP_SELF'] ?>?Izbor=<?php echo $_GET['Izbor'] ?>&BesediloID=<?php echo $_GET['BesediloID'] ?>&f=jsonp",
			dataType: "jsonp",
			crossDomain: false,
			data: {
				Find: $input.val()
			}
		})
		.then(function(response) {
			$.each(response, function(i, val) {
				html += "<li>" + val + "</li>";
			});
			$ul.html(html);
			$ul.listview("refresh");
			$ul.trigger("updatelayout");
		});
	}
});
*/
$('#edit').live('pageinit', function(event){
	// handle field changes
	$("input[name='Find']").change(function(e){
		e.preventDefault();
		var fObj = this;	// input object
		if (fObj.value.length==0) {return false;}
		URL = '<?php echo $_SERVER['PHP_SELF'] ?>?Izbor=<?php echo $_GET['Izbor'] ?>&BesediloID=<?php echo $_GET['BesediloID'] ?>';
		$.mobile.changePage(URL, {
			reloadPage: true,
			type: "get",
			data: $(this).serialize() // this.name+'='+this.value
		});
		return false;
	});
});
//-->
</script>
<?php

// page head
echo "<div id=\"edit\" data-role=\"page\" data-title=\"Oznake\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Oznake</h1>\n";
echo "<a href=\"edit.php?Izbor=Besedila&ID=". $_GET['BesediloID'] ."\" title=\"Back\" data-role=\"button\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-ajax=\"false\" data-transition=\"slide\">Back</a>\n";
echo "<a href=\"./\" title=\"Home\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Home</a>\n";
echo "</div>\n";
echo "<div data-role=\"content\">\n";

if ( isset($_GET['Find']) && $_GET['Find'] != "" ) {

	// display search results
	if ( $_GET['Find'] == "*" ) $_GET['Find'] = "";
	$List = $db->get_results(
		"SELECT
			T.TagID AS ID,
			T.TagName AS Ime
		FROM
			Tags T
		WHERE
			T.TagName LIKE '%". $_GET['Find']."%'
		ORDER BY
			T.TagName"
	);

	echo "<ul data-role=\"listview\" data-theme=\"d\">\n";
	echo "<li data-theme=\"c\"><input type=\"text\" name=\"Find\" value=\"". $_GET['Find'] ."\" placeholder=\"Find\"></li>\n";
	if ( $_GET['Find'] != "" && contains($ACL,"W") ) {
		echo "<li data-theme=\"c\" data-icon=\"add\">";
		echo "<a href=\"". $_SERVER['PHP_SELF'] ."?Izbor=". $_GET['Izbor'] ."&BesediloID=". $_GET['BesediloID'] ."&newtag=". $_GET['Find'] ."\" data-ajax=\"false\" data-theme=\"c\">";
		echo $_GET['Find'];
		echo "</a>";
		echo "</li>\n";
	}
	foreach ( $List as $Item ) {
		if ( $Item->Ime === $_GET['Find'] ) continue;
		echo "<li data-icon=\"check\">";
		echo (contains($ACL,"W") ? "<a href=\"". $_SERVER['PHP_SELF'] ."?Izbor=". $_GET['Izbor'] ."&BesediloID=". $_GET['BesediloID'] ."&newtag=". $Item->Ime ."\" data-ajax=\"false\" data-theme=\"c\">" : "");
		echo $Item->Ime;
		echo (contains($ACL,"W") ? "</a>" : "");
		echo "</li>\n";
	}
	echo "</ul>\n";

} else {

	// display list of assigned tags
	$List = $db->get_results(
		"SELECT
			BT.ID,
			T.TagName AS Ime
		FROM
			BesedilaTags BT
			LEFT JOIN Tags T ON BT.TagID = T.TagID
		WHERE
			BT.BesediloID = ".(int)$_GET['BesediloID']."
		ORDER
			BY T.TagName"
	);

	echo "<ul data-role=\"listview\" data-theme=\"d\">\n";
	echo "<li data-theme=\"c\"><input type=\"text\" name=\"Find\" placeholder=\"Find\"></li>\n";
	foreach ( $List as $Item ) {
		echo "<li data-icon=\"delete\">";
		echo (contains($ACL,"D") ? "<a href=\"". $_SERVER['PHP_SELF'] ."?Izbor=". $_GET['Izbor'] ."&BesediloID=". $_GET['BesediloID'] ."&deltag=". $Item->ID ."\" data-ajax=\"false\" data-theme=\"c\">" : "");
		echo $Item->Ime;
		echo (contains($ACL,"D") ? "</a>": "");
		echo "</li>\n";
	}
	echo "</ul>\n";

}
echo "</div>\n";
?>
