<?php
/*~ inc_MediaOpis.php - Editing of media text descriptions.
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

if ( !isset( $_GET['Jezik'] ) ) $_GET['Jezik'] = "Novo";

$Podatek = $db->get_row(
	"SELECT MO.ID, MO.Naslov, MO.Opis, MO.Jezik, MO.MediaID, M.ACLID ".
	"FROM MediaOpisi MO ".
	"	LEFT JOIN Media M ON MO.MediaID = M.MediaID ".
	"WHERE MO.ID = ".(int)$_GET['ID']
);
if ( $Podatek ) {
	$ACL = userACL( $Podatek->ACLID );
	$_GET['Jezik'] = $Podatek->Jezik;
} else
	$ACL = "LRWDX";

echo "<div id=\"editText\" data-role=\"page\" data-title=\"Opisi & nazivi\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Opisi & nazivi</h1>\n";
echo "<a href=\"edit.php?Izbor=Media&ID=".(int)$_GET['MediaID']."\" title=\"Podatki\" class=\"ui-btn-left\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-ajax=\"false\">Nazaj</a>\n";
echo "<a href=\"./\" title=\"Domov\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Domov</a>\n";
echo "</div>\n";
echo "<div id=\"editData\" data-role=\"content\">\n";

echo "\t<FORM NAME=\"MediaOpis\" ACTION=\"edit.php?Izbor=Media&ID=". $_GET['MediaID'] ."\" METHOD=\"post\" data-ajax=\"false\">\n";
?>
<?php if ( $Podatek ) : ?>
<INPUT NAME="OpisID" TYPE="Hidden" VALUE="<?php echo $Podatek->ID ?>">
<?php endif ?>
<div data-role="fieldcontain">
	<label for="frmNaslov"><B>Naslov:</B></label>
	<INPUT TYPE="text" ID="frmNaslov" NAME="Naslov" MAXLENGTH="128" VALUE="<?php echo ($Podatek)? $Podatek->Naslov: "" ?>" data-theme="d"><br>
</div>
<div data-role="fieldcontain">
	<label for="frmJezik">Jezik:</label>
	<SELECT ID="frmJezik" NAME="Jezik" SIZE="1" <?php echo (($Podatek)? "DISABLED": "NAME=\"Jezik\"") ?> data-theme="d">
		<OPTION VALUE="" DISABLED STYLE="background-color:whitesmoke;">Izberi...</OPTION>
<?php
$Jeziki = $db->get_results(
	"SELECT J.Jezik, J.Opis ".
	"FROM Jeziki J ".
	"	LEFT JOIN MediaOpisi MO ON J.Jezik = MO.Jezik AND MO.MediaID = '".$_GET['MediaID']."'".
	((!$Podatek)? " WHERE MO.Jezik IS NULL": "")
);
$All = $db->get_var(
	"SELECT count(*) ".
	"FROM MediaOpisi ".
	"WHERE MediaID = '".$_GET['MediaID']."'".
	"	AND Jezik IS NULL"
);
	if ( !($All) )
		echo "<OPTION VALUE=\"\"".(($Podatek && $Podatek->Jezik == "")? " SELECTED": "").">- za vse -</OPTION>\n";
	if ( $Jeziki ) foreach ( $Jeziki as $Jezik )
		echo "<OPTION VALUE=\"$Jezik->Jezik\"".($Podatek && $Podatek->Jezik == $Jezik->Jezik? " SELECTED": "").">$Jezik->Opis</OPTION>\n";
?>
	</SELECT><br>
</div>
<div data-role="fieldcontain">
	<label for="HTMLeditor"><B>Opis:</B></label>
<?php
	$Opis = $Podatek ? $Podatek->Opis : "";
	$Opis = preg_replace( "/(src=\")/i", '$1../', $Opis );
?>
	<TEXTAREA NAME="Opis" ID="HTMLeditor" data-theme="d"><?php echo ($Podatek)? $Podatek->Opis: "" ?></TEXTAREA>
</div>

<?php if ( contains($ACL,"W") ) : ?>
	<INPUT TYPE="submit" VALUE="Shrani" data-iconpos="left" data-icon="check" data-theme="a">
<?php endif ?>
<?php
echo "\t</FORM>\n";
echo "\t</div>\n";
//echo "\t<div data-role=\"footer\" data-position=\"fixed\" class=\"ui-bar\" style=\"text-align:center;\">\n";
//echo "\t</div>\n";
echo "</div>\n"; // page

/*
	// $_GET['ID'] not defined
	$Podatek = $db->get_row( "SELECT * FROM Media WHERE MediaID = " . (int)$_GET['MediaID'] );
	// get ACL
	if ( $Podatek )
		$ACL = userACL( $Podatek->ACLID );
	else
		$ACL = $ActionACL;

	echo "<div id=\"editText\" data-role=\"page\" data-title=\"Opisi & nazivi\">\n";
	echo "<div data-role=\"header\" data-theme=\"b\">\n";
	echo "<h1>Opisi & nazivi</h1>\n";
	echo "<a href=\"#edit\" title=\"Podatki\" class=\"ui-btn-left\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-direction=\"reverse\">Nazaj</a>\n";
	echo "<a href=\"inc.php?Izbor=MediaOpis&MediaID=".(int)$_GET['MediaID']."&ID=0\" title=\"Opis\" class=\"ui-btn-right\" data-iconpos=\"notext\" data-icon=\"plus\">Dodaj</a>\n";
	echo "</div>\n";
	echo "<div id=\"editData\" data-role=\"content\">\n";

	echo "<ul data-role=\"listview\" data-filter-test=\"true\" data-theme=\"d\" data-split-icon=\"delete\" data-split-theme=\"d\">\n";
	$Nazivi = $db->get_results(
		"SELECT ID, Naslov AS Naziv, Jezik ".
		"FROM MediaOpisi ".
		"WHERE MediaID = ".(int)$_GET['MediaID']." ".
		"ORDER BY Jezik"
	);

	if ( !$Nazivi ) {
		echo "<li>Ni opisov!</li>\n";
	} else {
		foreach ( $Nazivi as $Naziv ) {
			echo "<li>";
			echo (contains($ACL,"W")? "<a href=\"inc.php?Izbor=MediaOpis&MediaID=".(int)$_GET['MediaID']."&ID=$Naziv->ID\">": "");
			echo "<b>". $Naziv->Naziv ."</b>";
			echo "<span class=\"ui-li-count\">".(($Naziv->Jezik=="")? "vsi": $Naziv->Jezik)."</span>";
			echo (contains($ACL,"W")? "</a>": "");
			if ( contains($ACL,"D") )
				echo "<a href=\"#\" onclick=\"checkTxt('$Naziv->ID','$Naziv->Naziv');\">Briši</a>";
			echo "</li>\n";
		}
	}
	echo "</ul>\n";

	echo "</div>\n";
	//echo "\t<div data-role=\"footer\" data-position=\"fixed\" class=\"ui-bar\" style=\"text-align:center;\">\n";
	//echo "\t</div>\n";
	echo "</div>\n"; // page
*/
?>