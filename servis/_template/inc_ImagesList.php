<?php
/*
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

// attach selected image to text
if ( isset($_POST['MediaID']) && $_POST['MediaID'] != "" ) {
	$db->query("START TRANSACTION");
	$polozaj = $db->get_var("SELECT max(Polozaj) FROM BesedilaSlike WHERE BesediloID = ". (int)$_GET['BesediloID']);
	$db->query(
		"INSERT INTO BesedilaSlike (
			BesediloID,
			Polozaj,
			MediaID
		) VALUES (
			".(int)$_GET['BesediloID'].",
			".($polozaj? $polozaj+1: 1).",
			".(int)$_POST['MediaID']."
		)"
	);
	// audit action
	$db->query(
		"INSERT INTO SMAudit (
			UserID,
			ObjectID,
			ObjectType,
			Action,
			Description
		) VALUES (
			". $_SESSION['UserID'] .",
			". (int)$_GET['BesediloID'] .",
			'Text',
			'Attach image to text gallery',
			'". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". (int)$_GET['BesediloID'])
			.",". $db->get_var("SELECT Naziv FROM Media WHERE MediaID=". (int)$_POST['MediaID']) ."'
		)"
		);
	$db->query("COMMIT");
	$Changed = true;
}

// remove image
if ( isset($_GET['BrisiSliko']) && $_GET['BrisiSliko'] != "" ) {
	$db->query("START TRANSACTION");
	$x = $db->get_row("SELECT BesediloID, MediaID FROM BesedilaSlike WHERE ID=". (int)$_GET['BrisiSliko']);
	// audit action
	$db->query(
		"INSERT INTO SMAudit (
			UserID,
			ObjectID,
			ObjectType,
			Action,
			Description
		) VALUES (
			". $_SESSION['UserID'] .",
			". $x->BesediloID .",
			'Text',
			'Remove image from text gallery',
			'". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". $x->BesediloID)
			.",". $db->get_var("SELECT Naziv FROM Media WHERE MediaID=". $x->MediaID) ."'
		)"
		);
	$db->query("DELETE FROM BesedilaSlike WHERE ID=". (int)$_GET['BrisiSliko']);
	$db->query("COMMIT");
	$Changed = true;
}

// seznam slik 
$List = $db->get_results(
	"SELECT".
	"	BS.ID,".
	"	BS.MediaID,".
	"	BS.BesediloID,".
	"	M.Datoteka,".
	"	M.Naziv,".
	"	BS.Polozaj,".
	"	M.Meta ".
	"FROM".
	"	BesedilaSlike BS".
	"	LEFT OUTER JOIN Media M".
	"		ON M.MediaID = BS.MediaID ".
	"WHERE".
	"	BS.BesediloID = ".(int)$_GET['BesediloID']." ".
	"ORDER BY".
	"	BS.Polozaj DESC"
);
?>
<table bgcolor="white" border="0" cellpadding="10" cellspacing="0" width="100%" class="frame">
<?php if ( !$List ) : ?>
<TR><TD ALIGN="center">No added images!</TD></TR>
<?php else : ?>
	<?php
	$MaxCols = 3;
	$MaxRows = 3;
	$CurrentRow = 1;
	$RecordCount = count( $List );
	foreach ( $List as $Item ) {
		if ( $CurrentRow > $MaxCols * $MaxRows )
			break;

		// thumbnail dimensions
		$TWidth = 0;
		$THeight = 0;
		// get absolute file path
		$sPath = $StoreRoot .'/'. $Item->Datoteka;
		// get filename
		$sFile = basename($Item->Datoteka);
		// get relative path
		$sDir = dirname($Item->Datoteka);
		// extract metadata (for thumbnail size)
		$MetaData = explode(";", $Item->Meta);
		for ( $i=0; $i<count($MetaData); $i++ ){
			if (left($MetaData[$i],2) == "tw") $TWidth = (int)substr($MetaData[$i],3,4);
			if (left($MetaData[$i],2) == "th") $THeight= (int)substr($MetaData[$i],3,4);
		}

		if ( $CurrentRow % $MaxCols == 1 )
			echo "<tr>\n";
		echo "<td align=\"center\" valign=\"middle\" width=\"33%\" onmouseover=\"this.style.backgroundColor='whitesmoke';\" onmouseout=\"this.style.backgroundColor='';\">";
		echo "<div style=\"position:relative;display:inline-block;\">";
		echo "<img src=\"../" . (is_file($sPath)? $sDir."/thumbs/".$sFile: "pic/nislike.png") ."\" alt=\"Remove\" border=0 hspace=0 vspace=0 style=\"border:none;max-width:128px;\">";
		echo "<a href=\"javascript:removeimg($Item->ID,'$Item->Datoteka','$Item->Naziv')\" title=\"Remove\">";
		echo "<img src=\"pic/list.delete.gif\" alt=\"Remove\" border=0 style=\"position:absolute;top:1px;right:1px;\"></a>";
		echo "</div>";
		echo "</td>\n";
		if ( $CurrentRow == $RecordCount || $CurrentRow % $MaxCols == 0 )
			echo "</tr>\n";

		$CurrentRow++;
	}
?>
	<?php if ( $RecordCount > $MaxCols * $MaxRows ) : ?>
	<tr>
	<td align="center" colspan="3">...</td>
	</tr>
	<?php endif ?>
<?php endif ?>
</table>
