<?php
/*~ inc_sysTemplateCategories.php - Display page template assignment
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

// remove category
if ( isset( $_GET['Odstrani'] ) && $_GET['Odstrani'] != "" ) {
	$db->query( "DELETE FROM KategorijeVsebina WHERE ID = ".(int)$_GET['Odstrani'] );
}

$ACLID = $db->get_var( "SELECT ACLID FROM Predloge WHERE PredlogaID = ".(int)$_GET['PredlogaID'] );
if ( $ACLID )
	$ACL = userACL($ACLID);
else
	$ACL = "LRWDX";

$List = $db->get_results(
	"SELECT KV.ID, KV.KategorijaID, KV.PredlogaID, KV.Ekstra, K.Ime, K.ACLID, K.Izpis ".
	"FROM KategorijeVsebina KV ".
	"	LEFT JOIN Kategorije K ON KV.KategorijaID = K.KategorijaID ".
	"WHERE KV.PredlogaID = ".(int)$_GET['PredlogaID']." ".
	"ORDER BY KV.KategorijaID"
);
echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
if ( !$List ) 
	echo "<TR><TD ALIGN=\"center\">Not attached to any category!</TD></TR>\n";
else {
	$CurrentRow = 1;
	$RecordCount = count( $List );
	foreach ( $List as $Item ) {
		$rACL = userACL($Item->ACLID);
		echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
		echo "<TD WIDTH=\"18\">";
		switch ( $Item->Ekstra ) {
			case 0: echo "V"; break;
			case 1: echo "E"; break;
			case 2: echo "M"; break;
		}
		echo "</TD>\n";
		echo "<TD>";
		if ( contains($rACL,"R") )
			echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=Categories&ID=$Item->KategorijaID');\">";
		if ( contains($rACL,"L") )
			echo $Item->Ime;
		else
			echo "-- skrita rubrika --";
		if ( contains($rACL,"R") )
			echo "</A>";
		if ( !$Item->Izpis )
			echo "*";
		echo "</TD>\n";
		echo "<TD ALIGN=\"right\" NOWRAP WIDTH=\"40\">";
		if ( contains($ACL,"W") ) {
			echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"$('#divRubrike').load('inc.php?Izbor=".$_GET['Izbor']."&PredlogaID=".$_GET['PredlogaID']."&Odstrani=$Item->ID');\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" CLASS=\"icon\"></A>";
		}
		echo "</TD>\n";
		echo "</TR>\n";
		$CurrentRow++;
	}
}
echo "</TABLE>\n";
?>
