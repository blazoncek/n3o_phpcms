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

/**************************************
* loop( $menu, $bgcol )
*--------------------------------------
* Recursively loop through categories in Kategorije table.
*	$Menu - start level
*	$BgCol - by ref: background color of table (for recursive call)
**************************************/
function loop($Menu="", &$BgCol="white")
{
	global $db;

	$Kat = $db->get_results(
		"SELECT KategorijaID AS ID, Ime AS Name, ACLID
		FROM Kategorije
		WHERE KategorijaID LIKE '".$Menu."__'
		ORDER BY KategorijaID" );
	
	if ( $Menu == "" && count( $Kat ) == 0 ) {
		echo "<tr><td align=\"center\" valign=\"middle\" height=\"100\"><b>No data!</b></td></tr>\n";
	} else {

		if ( $Kat ) foreach ( $Kat as $K ) {
			$ACL = userACL( $K->ACLID );
			// List access
			if ( contains( $ACL, "L" ) ) {
				if ( $BgCol == "white" )
					$BgCol = "#edf3fe";
				else
					$BgCol = "white";

				echo "<tr bgcolor=\"$BgCol\">\n"; //<! onmouseover="this.style.backgroundColor='whitesmoke';" onmouseout="this.style.backgroundColor='';">
				echo "<td valign=\"bottom\">";
				echo str_repeat( "&nbsp;", strlen($K->ID)-2 );
				echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action'].
					"&ID=".((left($_GET['ID'],strlen($K->ID)) == $K->ID)? left($K->ID,strlen($K->ID)-2): $K->ID)."');\">";
				echo "<img src=\"pic/list.".((left($_GET['ID'],strlen($K->ID))==$K->ID)? "open": "closed").".gif\" height=\"11\" width=\"11\" border=\"0\">&nbsp;";
				echo left($K->Name,25).((strlen($K->Name)>25)?"...":"");
				echo "</a>";
				echo "</td>\n";
				echo "<td align=\"right\" class=\"novo\">";
				if ( contains( $ACL, "W" ) /*&& $K->ID == $_GET['ID']*/ ) {
					echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action'].
						"&KategorijaID=$K->ID');\">";
					echo "Novo...</a>";
				}
				echo "</td>\n";
				echo "</tr>\n";

				// recursively loop
				if ( $_GET['ID'] != "" && left($_GET['ID'], strlen($K->ID)) == $K->ID )
					loop( $K->ID, $BgCol );

				if ( $K->ID == $_GET['ID'] ) {
					// display texts belonging to category
					$List = $db->get_results(
						"SELECT B.BesediloID AS ID, B.Ime, B.Datum, B.Izpis, B.ACLID, KB.ID AS kbID, KB.Polozaj
						FROM Besedila B
							LEFT JOIN KategorijeBesedila KB ON B.BesediloID = KB.BesediloID
						WHERE KB.KategorijaID = '".$db->escape($_GET['ID'])."'
						ORDER BY KB.Polozaj" );

					$CurrentRow = 1;
					$RecordCount = count($List);
					if ( $List ) foreach ( $List as $Item ) {
						$ACL = userACL( $Item->ACLID );
						if ( $BgCol == "white" )
							$BgCol = "#edf3fe";
						else
							$BgCol = "white";
						echo "<tr bgcolor=\"$BgCol\">\n"; //<! onmouseover="this.style.backgroundColor='whitesmoke';" onmouseout="this.style.backgroundColor='';">
						echo "<td>";
						echo str_repeat( "&nbsp;", strlen($K->ID)-2 );
						echo "<img src=\"pic/trans.png\" height=\"14\" width=\"14\" border=\"0\">&nbsp;";
						if ( contains( $ACL, "R" ) )
							echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action']."&ID=$Item->ID');\">";
						// display category name
						echo ($Item->Ime=="")? "(unnamed)": left($Item->Ime,32).((strlen($Item->Ime)>32)? "...": "");
						if ( contains( $ACL, "R" ) )
							echo "</a>";
						// mark disabled items
						if ( !$Item->Izpis )
							echo "*";
						echo "</td>\n";
						echo "<td align=\"right\">";
						// move items up/down
						if ( contains( $ACL, "W" ) ) {
							if ( $CurrentRow > 1 )
								echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Izbor=".$_GET['Izbor'].
									"&Action=".$_GET['Action']."&kbID=$Item->kbID&Smer=-1');\">".
									"<img src=\"pic/list.up.gif\" height=11 width=11 border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
							else
								echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\">";
							if ( $CurrentRow < $RecordCount )
								echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Izbor=".$_GET['Izbor'].
									"&Action=".$_GET['Action']."&kbID=$Item->kbID&Smer=1');\">".
									"<img src=\"pic/list.down.gif\" height=11 width=11 border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
							else 
								echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
						}
						// delete
						if ( contains($ACL,"D") )
							echo "<a href=\"javascript:void(0);\" onclick=\"javascript:check('$Item->ID','$Item->Ime');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Delete\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
						else
							echo "<img src=\"pic/trans.png\" height=\"11\" width=\"11\" border=\"0\" align=\"absmiddle\" class=\"icon\">";
						echo "</td>\n";
						echo "</tr>\n";
						$CurrentRow++;
					}
				}
			}
		}

		// display texts not belonging to any category
		if ( strlen( $Menu ) == 0 ) {
			$List = $db->get_results(
				"SELECT B.BesediloID AS ID, B.Ime, B.Datum, B.Izpis, B.ACLID
				FROM Besedila B
					LEFT JOIN KategorijeBesedila KB ON B.BesediloID = KB.BesediloID
				WHERE KB.KategorijaID IS NULL
				ORDER BY B.Datum DESC" );
			if ( $List ) foreach ( $List as $Item ) {
				$ACL = userACL( $Item->ACLID );
				if ( $BgCol == "white" )
					$BgCol = "#edf3fe";
				else
					$BgCol = "white";
				echo "<tr bgcolor=\"$BgCol\">\n"; //<! onmouseover="this.style.backgroundColor='whitesmoke';" onmouseout="this.style.backgroundColor='';">
				echo "<td>";
				echo str_repeat( "&nbsp;", strlen($K->ID)-2 );
				echo "<img src=\"pic/trans.png\" height=\"14\" width=\"14\" border=\"0\">&nbsp;";
				if ( contains( $ACL, "R" ) ) {
					echo "<a href=\"javascript:void(0);\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action']."&ID=$Item->ID');\">";
					// display category name
					if ( $Item->Ime == "" )
						echo "(unnamed)";
					else
						echo left($Item->Ime,32).((strlen($Item->Ime)>32)? "...": "");
					// mark disabled items
					if ( !$Item->Izpis )
						echo "*";
					echo "</a>";
					echo "</td>\n";
					echo "<td align=\"right\" class=\"novo\">";
					if ( contains($ACL,"D") )
						echo "<a href=\"javascript:void(0);\" onclick=\"javascript:check('$Item->ID','$Item->Name');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Delete\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
					else
						echo "<img src=\"pic/trans.png\" height=\"11\" width=\"11\" border=\"0\" align=\"absmiddle\" class=\"icon\">";
					echo "</td>\n";
					echo "</tr>\n";
				}
			}
		}

	}
}

// define default values for URL ID and Find parameters (in case not defined)
if ( !isset($_GET['ID']) )   $_GET['ID']   = "";
if ( !isset($_GET['Find']) ) $_GET['Find'] = "";
if ( !isset($_GET['Tip']) )  $_GET['Tip']  = "";
if ( !isset($_GET['Sort']) ) $_GET['Sort'] = "";

$Simple = $db->get_var( "SELECT SifLVal1 FROM Sifranti WHERE SifrCtrl = 'PARA' AND SifrText = 'BESESimple'" );
//if ( !$Simple ) $Simple = false;

if ( $Simple && $_GET['Find'] == "" ) {

	$BgCol = "white";
	// hierarchical view
	echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"frame\">\n";
	loop( "", $BgCol );
	echo "</table>\n";
	
} else {
	// define sort order
	$Sort = "B.BesediloID DESC";
	if ( $_GET['Sort'] == "Date" )
		$Sort = "B.Datum DESC";
	elseif ( $_GET['Sort'] == "Name" )
		$Sort = "B.Ime";
	
	$List = $db->get_results(
		"SELECT DISTINCT B.BesediloID AS ID, B.Ime AS Name, B.Datum, B.Izpis, B.ACLID
		FROM Besedila B
			LEFT JOIN BesedilaOpisi BO ON B.BesediloID = BO.BesediloID
		WHERE 1=1 " .
			($_GET['Find']=="" ? "" : "AND (B.Ime LIKE '%".$db->escape(trim($_GET['Find']))."%' OR BO.Naslov LIKE '%".$db->escape(trim($_GET['Find']))."%' OR BO.Povzetek LIKE '%".$db->escape(trim($_GET['Find']))."%')").
			($_GET['Tip']=="" ? "" : "AND B.Tip='".$db->escape($_GET['Tip'])."' ") .
		"ORDER BY $Sort" );

	$RecordCount = count($List);
	
	// override maximum number of rows to display
	if ( isset($_COOKIE['listmax']) ) $MaxRows = (int)$_COOKIE['listmax'];
	
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
	
	// sorting and filtering options
	echo "<TABLE WIDTH=\"100%\" BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\" CLASS=\"novo\">\n";
	echo "<TR>\n";
	echo "<TD>Sort:\n";
	echo "<SELECT NAME=\"Sort\" SIZE=\"1\" ONCHANGE=\"loadTo('List','list.php?Action=".$_GET['Action']."&Tip=".$_GET['Tip']."&Sort='+this[this.selectedIndex].value);\">\n";
	echo "<OPTION VALUE=\"ID\">Entry ID</OPTION>\n";
	echo "<OPTION VALUE=\"Name\"".(($_GET['Sort']=="Name")? " SELECTED": "").">Name</OPTION>\n";
	echo "<OPTION VALUE=\"Date\"".(($_GET['Sort']=="Date")? " SELECTED": "").">Date</OPTION>\n";
	echo "</SELECT>\n";
	echo "</TD>\n";
	echo "<TD ALIGN=\"right\">Type:\n";
	echo "<SELECT NAME=\"Tip\" SIZE=\"1\" ONCHANGE=\"loadTo('List','list.php?Action=".$_GET['Action']."&Sort=".$_GET['Sort']."&Tip='+this[this.selectedIndex].value);\">\n";
	echo "<OPTION VALUE=\"\">- all types -</OPTION>\n";
	$Tipi = $db->get_col( "SELECT SifrText FROM Sifranti WHERE SifrCtrl='BESE' ORDER BY SifrCtrl, SifrZapo" );
	if ( $Tipi ) foreach ( $Tipi as $Tip )
		echo "<OPTION VALUE=\"$Tip\"".(($_GET['Tip']==$Tip)? " SELECTED": "").">$Tip</OPTION>\n";
	echo "</SELECT>\n";
	echo "</TD>\n";
	echo "</TR>\n";
	echo "</TABLE>\n";
	
	if ( count( $List ) == 0 ) {
		echo "<div class=\"frame\" style=\"display: table;height: 100px;width: 100%;\">";
		echo "<div style=\"background-color: white;display: table-cell;text-align: center;vertical-align: middle;\"><b>No data!</b></div>\n";
		echo "</div>\n";
	} else {

		if ( $NuPg > 1 ) {
			echo "<DIV CLASS=\"pg\">\n";
			if ( $StPg > 1 )
				echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&Sort=".$_GET['Sort']."&Tip=".$_GET['Tip']."&pg=".($StPg-1)."');\">&laquo;</A>\n";
			if ( $Page > 1 )
				echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&Sort=".$_GET['Sort']."&Tip=".$_GET['Tip']."&pg=$PrPg');\">&lt;</A>\n";
			for ( $i = $StPg; $i <= $EdPg; $i++ ) {
				if ( $i == $Page )
					echo "<FONT COLOR=\"red\"><B>$i</B></FONT>\n";
				else
					echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&Sort=".$_GET['Sort']."&Tip=".$_GET['Tip']."&pg=$i');\">$i</A>\n";
			}
			if ( $Page < $EdPg )
				echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&Sort=".$_GET['Sort']."&Tip=".$_GET['Tip']."&pg=$NePg');\">&gt;</A>\n";
			if ( $NuPg > $EdPg )
				echo "<A HREF=\"javascript:void(0);\" onclick=\"loadTo('List','list.php?Action=".$_GET['Action']."&Sort=".$_GET['Sort']."&Tip=".$_GET['Tip']."&pg=".($EdPg<$NuPg? $EdPg+1: $EdPg)."');\">&raquo;</A>\n";
			echo "</DIV>\n";
		}
	
		echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"frame\">\n";
		$BgCol = "white";
		$i = $StaR-1;
		while ( $i < $EndR ) {
			// get list item
			$Item = $List[$i++];
			// get ACL
			$ACL = userACL( $Item->ACLID );
			if ( contains( $ACL, "L" ) ) {
				// row background color
				if ( $BgCol == "white" )
					$BgCol="#edf3fe";
				else
					$BgCol = "white";
				echo "<tr bgcolor=\"$BgCol\">\n";
				if ( contains($ACL,"R") )
					echo "<td><a href=\"javascript:void(0);\" title=\"$Item->Name ($Item->ID)\" onclick=\"loadTo('Edit','edit.php?Izbor=".$_GET['Izbor']."&Action=".$_GET['Action']."&ID=$Item->ID');\">".left($Item->Name,30).(strlen($Item->Name)>30?"...":"")."</a></td>\n";
				else
					echo "<td>".left($Item->Name,30).(strlen($Item->Name)>30? "...": "")."</td>\n";
				echo "<td align=\"right\" valign=\"top\" width=\"20\">";
				if ( contains($ACL,"D") )
					echo "<a href=\"javascript:void(0);\" onclick=\"javascript:check('$Item->ID','$Item->Name');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Delete\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
				else
					echo "&nbsp;";
				echo "</td>\n";
				echo "</tr>\n";
			}
		}
		echo "</table>\n";
	}
}
?>