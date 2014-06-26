<?php
/*~ edit_txtMedia.php - text attachments (non-image)
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

// add media
if ( isset($_GET['MediaID']) && $_GET['MediaID'] != "" ) {
	$db->query("START TRANSACTION");
	$Polozaj = $db->get_var("SELECT max(Polozaj) FROM BesedilaMedia WHERE BesediloID=". (int)$_GET['BesediloID']);
	$db->query(
		"INSERT INTO BesedilaMedia (BesediloID, MediaID, Polozaj)
		VALUES (". (int)$_GET['BesediloID'] .",". (int)$_GET['MediaID'] .",". ($Polozaj ? $Polozaj+1 : 1) .")"
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
			'Attach media to text',
			'". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". (int)$_GET['BesediloID'])
			.",". $db->get_var("SELECT Naziv FROM Media WHERE MediaID=". (int)$_GET['MediaID']) ."'
		)"
		);
	$db->query("COMMIT");
}

// delete attachment from list
if ( isset($_GET['BrisiMedia']) && $_GET['BrisiMedia'] != "" ) {
	$db->query("START TRANSACTION");
	$x = $db->get_row("SELECT BesediloID, MediaID FROM BesedilaMedia WHERE ID=". (int)$_GET['BrisiMedia']);
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
			'Remove media from text',
			'". $db->get_var("SELECT Ime FROM Besedila WHERE BesediloID=". $x->BesediloID)
			.",". $db->get_var("SELECT Naziv FROM Media WHERE MediaID=". $x->MediaID) ."'
		)"
		);
	$db->query("DELETE FROM BesedilaMedia WHERE ID=". (int)$_GET['BrisiMedia']);
	$db->query("COMMIT");
}

// move items up/down
if ( isset( $_GET['Smer'] ) && $_GET['Smer'] != "" ) {
	$db->query("START TRANSACTION");
	if ( $ItemPos = $db->get_var("SELECT Polozaj FROM BesedilaMedia WHERE ID = ". (int)$_GET['Media']) ) {
		// calculate new position
		$ItemNew = $ItemPos + (int)$_GET['Smer'];
		// move
		$db->query("UPDATE BesedilaMedia SET Polozaj = 9999     WHERE BesediloID = ".(int)$_GET['BesediloID']." AND Polozaj = $ItemNew");
		$db->query("UPDATE BesedilaMedia SET Polozaj = $ItemNew WHERE BesediloID = ".(int)$_GET['BesediloID']." AND Polozaj = $ItemPos");
		$db->query("UPDATE BesedilaMedia SET Polozaj = $ItemPos WHERE BesediloID = ".(int)$_GET['BesediloID']." AND Polozaj = 9999");
	}
	$db->query("COMMIT");
}
$ACLID = $db->get_var("SELECT ACLID FROM Besedila WHERE BesediloID = ". (int)$_GET['BesediloID']);
if ( $ACLID )
	$ACL = userACL($ACLID);
else
	$ACL = "LRWDX";

echo "<script language=\"JavaScript\" type=\"text/javascript\">\n";
echo "<!-- //\n";
echo "function checkMedia(ID, Naziv) {\n";
echo "\tif (confirm(\"Remove attached file '\"+Naziv+\"'?\"))\n";
echo "\t\tsetTimeout(\"$('#divMe').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&BrisiMedia=\"+ID+\"')\",100);\n";
echo "\treturn false;\n";
echo "}\n";
echo "//-->\n";
echo "</script>\n";

if ( isset($_GET['Find']) && $_GET['Find'] != "" ) {

	// display search results
	if ( $_GET['Find'] == "*" ) $_GET['Find'] = "";
	$List = $db->get_results(
		"SELECT
			M.MediaID,
			M.Naziv
		FROM
			Media M
			LEFT JOIN MediaOpisi MO ON M.MediaID = MO.MediaID
		WHERE
			M.Tip <> 'PIC' AND
			(M.MediaID NOT IN (SELECT BM.MediaID FROM BesedilaMedia BM WHERE BM.BesediloID = ".(int)$_GET['BesediloID'].")) ".
			($_GET['Find']!="" ? "AND (M.Naziv LIKE '%".$db->escape($_GET['Find'])."%' OR M.Tip LIKE '".$db->escape($_GET['Find'])."%' OR MO.Naslov LIKE '%".$db->escape($_GET['Find'])."%' OR MO.Opis LIKE '%".$db->escape($_GET['Find'])."%') " : " ").
		"ORDER BY
			M.Naziv"
		);

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	if ( !$List ) 
		echo "<TR><TD ALIGN=\"center\"><br><br>No data!<br><br></TD></TR>\n";
	else {
		$CurrentRow = 1;
		$RecordCount = count( $List );
		foreach ( $List as $Item ) {
			echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
			echo "<TD>";
			echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divMe').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".(int)$_GET['BesediloID']."&MediaID=$Item->MediaID');\">";
			echo "<b>$Item->Naziv</b>";
			echo "</A>";
			echo "</TD>\n";
			echo "</TR>\n";
			$CurrentRow++;
		}
	}
	echo "</TABLE>\n";

} else {

	// display list of assigned media
	$List = $db->get_results(
		"SELECT
			BM.ID,
			BM.MediaID,
			BM.BesediloID,
			BM.Polozaj,
			M.Naziv,
			M.Datoteka,
			M.ACLID
		FROM
			BesedilaMedia BM
			LEFT JOIN Media M ON BM.MediaID = M.MediaID
		WHERE
			BM.BesediloID = ". (int)$_GET['BesediloID'] ."
		ORDER BY
			BM.Polozaj"
		);
?>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
function checkMedia(ID, Naziv) {
	if (confirm("Remove file '"+Naziv+"'?"))
		setTimeout("$('#divMe').load('inc.php?Izbor=<?php echo $_GET['Izbor'] ?>&BesediloID=<?php echo $_GET['BesediloID'] ?>&BrisiMedia="+ID+"')",100);
	return false;
}
//-->
</SCRIPT>
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="99%">
<?php if ( !$List ) : ?>
<TR><TD ALIGN="center" COLSPAN="3">No attached files!</TD></TR>
<?php else : ?>
	<?php
	$CurrentRow = 1;
	$RecordCount = count( $List );
	foreach ( $List as $Item ) {
		if ( $Item->ACLID )
			$rACL = userACL($$Item->ACLID);
		else
			$rACL = "LRWDX";

		echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
		echo "<TD ALIGN=\"right\" WIDTH=\"8%\">$Item->Polozaj.</TD>\n";
		echo "<TD>";

		if ( contains($rACL,"R") )
			echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=Media&ID=$Item->MediaID');\">";

		if ( contains($rACL,"L") )
			echo $Item->Naziv;
		else
			echo "-- hidden --";

		if ( contains($rACL,"R") )
			echo "</A>";
		echo "</TD>\n";
		echo "<TD ALIGN=\"right\" NOWRAP>\n";
		if ( contains($ACL,"W") ) {
			// move items up/down
			if ( $CurrentRow > 1 )
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divMe').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&Media=$Item->ID&Smer=-1');\" TITLE=\"Gor\"><IMG SRC=\"pic/list.up.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni gor\" BORDER=\"0\" CLASS=\"icon\"></A>";
			else
				echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
			if ( $CurrentRow < $RecordCount )
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divMe').load('inc.php?Izbor=".$_GET['Izbor']."&BesediloID=".$_GET['BesediloID']."&Media=$Item->ID&Smer=1');\" TITLE=\"Dol\"><IMG SRC=\"pic/list.down.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni dol\" BORDER=\"0\" CLASS=\"icon\"></A>";
			else
				echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
			// delete
			echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"javascript:checkMedia('$Item->ID','$Item->Datoteka');\" TITLE=\"Delete\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" CLASS=\"icon\">\n";
		}
		echo "</TD>\n";
		echo "</TR>\n";
		$CurrentRow++;
	}
	?>
<?php endif ?>
</TABLE>
<?php
}
?>
