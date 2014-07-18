<?php
/*~ inc_medDescription.php - Editing of media text descriptions.
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

if ( !isset($_GET['Jezik']) ) $_GET['Jezik'] = "Novo";

$Podatek = $db->get_row(
	"SELECT MO.ID, MO.Naslov, MO.Opis, MO.Jezik, MO.MediaID, M.ACLID
	FROM MediaOpisi MO
		LEFT JOIN Media M ON MO.MediaID = M.MediaID
	WHERE MO.ID = ". (int)$_GET['ID']
	);

if ( $Podatek ) {
	$ACL = userACL($Podatek->ACLID);
	$_GET['Jezik'] = $Podatek->Jezik;
} else
	$ACL = "LRWDX";

echo "<div id=\"editText\" data-role=\"page\" data-title=\"Titles &amp; descriptions\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Titles &amp; descriptions</h1>\n";
echo "<a href=\"edit.php?Izbor=Media&ID=".(int)$_GET['MediaID']."\" class=\"ui-btn-left\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-ajax=\"false\">Back</a>\n";
echo "<a href=\"./\" title=\"Home\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Home</a>\n";
echo "</div>\n";
echo "<div id=\"editData\" data-role=\"content\">\n";

echo "\t<FORM NAME=\"medDescription\" ACTION=\"edit.php?Izbor=Media&ID=". $_GET['MediaID'] ."\" METHOD=\"post\" data-ajax=\"false\">\n";
?>
<?php if ( $Podatek ) : ?>
<INPUT NAME="OpisID" TYPE="Hidden" VALUE="<?php echo $Podatek->ID ?>">
<?php endif ?>
<div data-role="fieldcontain">
	<label for="frmNaslov"><B>Title:</B></label>
	<INPUT TYPE="text" ID="frmNaslov" NAME="Naslov" MAXLENGTH="128" VALUE="<?php echo $Podatek ? $Podatek->Naslov : "" ?>" data-theme="d"><br>
</div>
<div data-role="fieldcontain">
	<label for="frmJezik">Language:</label>
	<SELECT ID="frmJezik" NAME="Jezik" SIZE="1" <?php echo (($Podatek)? "DISABLED": "NAME=\"Jezik\"") ?> data-theme="d">
		<OPTION VALUE="" DISABLED STYLE="background-color:whitesmoke;">Select...</OPTION>
<?php
$Jeziki = $db->get_results(
	"SELECT J.Jezik, J.Opis
	FROM Jeziki J
		LEFT JOIN MediaOpisi MO ON J.Jezik = MO.Jezik AND MO.MediaID = ". (int)$_GET['MediaID']."
	WHERE
		J.Enabled=1". (!$Podatek ? " AND MO.Jezik IS NULL" : "")
	);
$All = $db->get_var("SELECT count(*) FROM MediaOpisi WHERE MediaID=". (int)$_GET['MediaID'] ." AND Jezik IS NULL");

if ( !($All) )
	echo "<OPTION VALUE=\"\"".(($Podatek && $Podatek->Jezik == "")? " SELECTED": "").">- all -</OPTION>\n";

if ( $Jeziki ) foreach ( $Jeziki as $Jezik )
	echo "<OPTION VALUE=\"$Jezik->Jezik\"".($Podatek && $Podatek->Jezik == $Jezik->Jezik? " SELECTED": "").">$Jezik->Opis</OPTION>\n";
?>
	</SELECT><br>
</div>
<div data-role="fieldcontain">
	<label for="HTMLeditor"><B>Description:</B></label>
<?php
	$Opis = $Podatek ? $Podatek->Opis : "";
	$Opis = preg_replace( "/(src=\")/i", '$1../', $Opis );
?>
	<TEXTAREA NAME="Opis" ID="HTMLeditor" data-theme="d"><?php echo ($Podatek)? $Podatek->Opis: "" ?></TEXTAREA>
</div>

<?php if ( contains($ACL,"W") ) : ?>
	<INPUT TYPE="submit" VALUE="Save" data-iconpos="left" data-icon="check" data-theme="a">
<?php endif ?>
<?php
echo "\t</FORM>\n";
echo "\t</div>\n";
//echo "\t<div data-role=\"footer\" data-position=\"fixed\" class=\"ui-bar\" style=\"text-align:center;\">\n";
//echo "\t</div>\n";
echo "</div>\n"; // page
?>
