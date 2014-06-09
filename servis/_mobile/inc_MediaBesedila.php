<?php
/*~ edit_MediaBesedila.php - Display/remove media attachments.
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

// get mediy type
$Tip = $db->get_var( "SELECT Tip FROM Media WHERE MediaID = ".(int)$_GET['MediaID'] );

// add media
if ( isset($_GET['DodajBesedilo']) && $_GET['DodajBesedilo'] != "" ) {
	$db->query( "START TRANSACTION" );
	if ( $Tip == 'PIC' ) {
		$Polozaj = $db->get_var( "SELECT max(Polozaj) FROM BesedilaSlike WHERE BesediloID = ".(int)$_GET['DodajBesedilo'] );
		$db->query(
			"INSERT INTO BesedilaSlike (BesediloID, MediaID, Polozaj) ".
			"VALUES (".(int)$_GET['DodajBesedilo'].", ".(int)$_GET['MediaID'].", ".($Polozaj? $Polozaj+1: 1).")" );
	} else {
		$Polozaj = $db->get_var( "SELECT max(Polozaj) FROM BesedilaMedia WHERE BesediloID = ".(int)$_GET['DodajBesedilo'] );
		$db->query(
			"INSERT INTO BesedilaMedia (BesediloID, MediaID, Polozaj) ".
			"VALUES (".(int)$_GET['DodajBesedilo'].", ".(int)$_GET['MediaID'].", ".($Polozaj? $Polozaj+1: 1).")" );
	}
	$db->query( "COMMIT" );
}

// delete media from text
if ( isset($_GET['Odstrani']) && $_GET['Odstrani'] != "" ) {
	$db->query( "START TRANSACTION" );
	if ( $Tip == 'PIC' ) {
		$db->query( "DELETE FROM BesedilaSlike WHERE ID = ".(int)$_GET['Odstrani'] );
	} else {
		$db->query( "DELETE FROM BesedilaMedia WHERE ID = ".(int)$_GET['Odstrani'] );
	}
	$db->query( "COMMIT" );
}

$ACLID = $db->get_var( "SELECT ACLID FROM Media WHERE MediaID = ".(int)$_GET['MediaID'] );
if ( $ACLID )
	$ACL = userACL( $ACLID );
else
	$ACL = "LRWDX";

?>
<script language="JavaScript" type="text/javascript">
<!-- //
$('#edit').live('pageinit', function(event){
	// handle field changes
	$("input[name='Find']").change(function(e){
		e.preventDefault();
		var fObj = this;	// input object
		if (fObj.value.length==0) {return false;}
		URL = '<?php echo $_SERVER['PHP_SELF'] ?>?Izbor=<?php echo $_GET['Izbor'] ?>&MediaID=<?php echo $_GET['MediaID'] ?>';
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
echo "<div id=\"edit\" data-role=\"page\" data-title=\"Besedila\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Izberi besedilo</h1>\n";
echo "<a href=\"edit.php?Izbor=Media&ID=". $_GET['MediaID'] ."\" title=\"Back\" data-role=\"button\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-ajax=\"false\" data-transition=\"slide\">Back</a>\n";
echo "<a href=\"./\" title=\"Home\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Home</a>\n";
echo "</div>\n";
echo "<div data-role=\"content\">\n";

if ( isset($_GET['Find']) && $_GET['Find'] != "" ) {

	// display search results
	if ( $_GET['Find'] == "*" ) $_GET['Find'] = "";
	$List = $db->get_results(
		"SELECT DISTINCT
			B.BesediloID AS ID,
			B.Ime
		FROM
			Besedila B
			LEFT JOIN ". ($Tip=='PIC' ? 'BesedilaSlike' : 'BesedilaMedia') ." BM ON B.BesediloID = BM.BesediloID
			LEFT JOIN BesedilaOpisi BO ON B.BesediloID = BO.BesediloID
		WHERE
			(BM.MediaID IS NULL OR BM.MediaID <> ".(int)$_GET['MediaID'].") ".
			($_GET['Find']!=""? "AND (B.Ime LIKE '%".$_GET['Find']."%' OR BO.Naslov LIKE '%".$_GET['Find']."%' OR BO.Opis LIKE '%".$_GET['Find']."%') ": " ").
		"ORDER BY
			B.Ime"
	);

	echo "<ul data-role=\"listview\" data-theme=\"d\">\n";
	echo "<li data-theme=\"c\"><input type=\"text\" name=\"Find\" value=\"". $_GET['Find'] ."\" placeholder=\"Find\"></li>\n";
	if ( !$List )
		echo "<li data-theme=\"c\">No data!</li>\n";
	else {
		foreach ( $List as $Item ) {
			echo "<li data-icon=\"check\">";
			echo (contains($ACL,"W") ? "<a href=\"". $_SERVER['PHP_SELF'] ."?Izbor=". $_GET['Izbor'] ."&MediaID=". $_GET['MediaID'] ."&DodajBesedilo=". $Item->ID ."\" data-ajax=\"false\" data-theme=\"c\">" : "");
			echo $Item->Ime;
			echo (contains($ACL,"W") ? "</a>" : "");
			echo "</li>\n";
		}
	}
	echo "</ul>\n";

} else {

	// display list of assigned media
	$List = $db->get_results(
		"SELECT".
		"	BM.ID,".
		"	BM.MediaID,".
		"	BM.BesediloID,".
		"	BM.Polozaj,".
		"	B.Ime,".
		"	B.ACLID ".
		"FROM".
		"	BesedilaMedia BM".
		"	LEFT JOIN Besedila B ON BM.BesediloID = B.BesediloID ".
		"WHERE".
		"	BM.MediaID = ".(int)$_GET['MediaID']." ".

		"UNION ".

		"SELECT".
		"	BS.ID,".
		"	BS.MediaID,".
		"	BS.BesediloID,".
		"	BS.Polozaj,".
		"	B.Ime,".
		"	B.ACLID ".
		"FROM".
		"	BesedilaSlike BS".
		"	LEFT JOIN Besedila B ON BS.BesediloID = B.BesediloID ".
		"WHERE".
		"	BS.MediaID = ".(int)$_GET['MediaID']
	);

	echo "<ul data-role=\"listview\" data-theme=\"d\">\n";
	echo "<li data-theme=\"c\"><input type=\"text\" name=\"Find\" placeholder=\"Find\"></li>\n";
	foreach ( $List as $Item ) {
		echo "<li data-icon=\"delete\">";
		echo (contains($ACL,"D") ? "<a href=\"". $_SERVER['PHP_SELF'] ."?Izbor=". $_GET['Izbor'] ."&MediaID=". $_GET['MediaID'] ."&Odstrani=". $Item->ID ."\" data-ajax=\"false\" data-theme=\"c\">" : "");
		echo $Item->Ime;
		echo (contains($ACL,"D") ? "</a>": "");
		echo "</li>\n";
	}
	echo "</ul>\n";

}
echo "</div>\n";
?>
