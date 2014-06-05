<?php
/* _poll.php - Display a single (current) poll. (With voting logic.)
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
| This file is part of N3O CMS (frontend).                                  |
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

// preventfake votes
if ( strpos($_SERVER['HTTP_REFERER'], $WebURL) === 0 && isset($_POST['aID']) ) { // referer contains (base) server URL
	if ( isset($_POST['O']) ) {
		$db->query("
			UPDATE Ankete
			SET StGlasov = StGlasov + 1
			,   Rez". $_POST['O'] ." = Rez". $_POST['O'] ." + 1
			WHERE ID = ". (int)$_POST['aID']
		);
	} else {
		$sql = "UPDATE Ankete SET StGlasov = StGlasov + 1";
		for( $i=1; $i<=10; $i++ )
			$sql .= ",Rez". $i ." = Rez". $i .(isset($_POST['O'.$i]) ? "+1" : " ");
		$sql .= "WHERE ID = ". (int)$_POST['aID'];
		$db->query($sql);
	}
	setcookie("VoteDate", date("Y-m-d H:i:s"), time()+31536000, $WebPath);
	$_COOKIE['VoteDate'] = date("Y-m-d H:i:s");
}

// get the active poll
$Anketa = $db->get_row(
	"SELECT *
	FROM Ankete
	WHERE Datum <= '". date("Y-m-d H:i:s") ."'
		AND (Jezik = '". $lang ."' OR Jezik IS NULL)
	ORDER BY Datum DESC, Jezik
	LIMIT 1"
	);

// check if there are polls in the queue
$CakajoceAnkete = (int)$db->get_var(
	"SELECT count(*)
	FROM Ankete
	WHERE Datum > '". date("Y-m-d H:i:s") ."'
		AND (Jezik = '". $lang ."' OR Jezik IS NULL)"
	);

// if there is an active poll, display it
// but for at most 30 days if it is the last and no polls in the queue
if ( $Anketa && ($CakajoceAnkete || compareDate(addDate($Anketa->Datum,30),now()) <= 0) ) {

	$Vote = !isset($_COOKIE['VoteDate']) || (compareDate($Anketa->Datum, $_COOKIE['VoteDate']) <= 0);

	echo "<div class=\"menu\">\n";

	echo "<div class=\"title\">";
	// add link tag
	$rub = $db->get_var("SELECT KategorijaID FROM Kategorije WHERE Ime IN ('ankete','poll')"); // get ID of special category for polls
	$kat = $TextPermalinks ? ($IsIIS ? "$WebFile/" : ''). $db->get_var("SELECT Ime FROM Kategorije WHERE KategorijaID='".$rub."'") .'/' : '?kat='. $rub;
	if ( $rub != "") echo "<a href=\"$WebPath/$kat\" title=\"". multiLang('<Poll>', $lang) ."\">";
	echo multiLang('<Poll>', $lang); // display text
	if ( $rub != "") echo "</a>";
	echo "</div>\n";

	$Txt        = ReplaceSmileys($Anketa->Vprasanje, "$WebPath/pic/");
	$VsiGlasovi = $Anketa->Rez1 + $Anketa->Rez2 + $Anketa->Rez3 + $Anketa->Rez4 + $Anketa->Rez5 + $Anketa->Rez6 + $Anketa->Rez7 + $Anketa->Rez8 + $Anketa->Rez9 + $Anketa->Rez10;
	$Size       = 100;

	echo "<div class=\"poll\">\n";
	if ( (!$Vote || contains($_SERVER['QUERY_STRING'],'ViewPoll')) && $VsiGlasovi>0 ) {

		echo "<p align=\"center\"><i>". $Txt ."</i></p>\n";
		for ( $i=1; $i<=$Anketa->StOdg; $i++ ) {

			$Odg  = eval("return \$Anketa->Odg". $i .";");
			$Rez  = eval("return \$Anketa->Rez". $i .";");
			$Pct  = ($VsiGlasovi > 0) ? round($Rez*100 / $VsiGlasovi) : 0;
			$NPct = 100 - $Pct;
			$red  = $Size * $Pct/100;
			$wht  = $Size * $NPct/100;

			echo "<div>". $Odg ."<br>\n";
			echo "&nbsp;&nbsp;";
			if ( $Pct <> 0) echo "<div style=\"display:inline-block;background-color:red;width:". $red ."px;height:10px;\"></div>";
			if ( $NPct<> 0) echo "<div style=\"display:inline-block;background-color:white;width:". $wht ."px;height:10px;\"></div>";
			echo "&nbsp;&nbsp;<span class=\"a10\">". $Pct ."%</span>";
			echo "</div>\n";
		}
		echo "<p>". multiLang('<PollAllVotes>', $lang) .": <B>". $VsiGlasovi ."</B></p>";

	} else {
?>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
function validatePoll(fObj) {
	if (!testCookie()) {alert("Cookies are not enabled. You cannot vote!");return false;}
	return true;
}
//-->
</SCRIPT>
<?php
		$kat = $TextPermalinks ? ($IsIIS ? "$WebFile/" : ''). $KatText .'/' : '?kat='. $_GET['kat'];
		$bid = $TextPermalinks ? '?' : '&amp;';
		echo "<FORM ACTION=\"". $WebURL .'/'. $kat . (isset($_GET['ID']) ? $bid ."ID=". $_GET['ID'] : "") ."\" METHOD=\"post\" ONSUBMIT=\"return validatePoll(this);\">\n";
		echo "<input type=\"Hidden\" name=\"aID\" value=\"". $Anketa->ID ."\">\n";
		echo "<p align=\"center\"><i>". $Txt ."</i></p>\n";

		echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=\"100%\">\n";
		for ( $i=1; $i<=$Anketa->StOdg; $i++ ) {
			$Odg = eval("return \$Anketa->Odg". $i .";");
			echo "<TR>\n";
			echo "<TD CLASS=\"a10\" VALIGN=\"middle\" WIDTH=\"10%\">";
			if ( $Anketa->Multiple )
				echo "<INPUT TYPE=\"Checkbox\" NAME=\"O". $i ."\">";
			else
				echo "<INPUT TYPE=\"Radio\" NAME=\"O\" VALUE=\"". $i ."\"". ($i==1? " CHECKED>" : ">");
			echo "</TD>\n";
			echo "<TD CLASS=\"a10\" VALIGN=\"middle\" WIDTH=\"90%\">". $Odg ."</TD>\n";
			echo "</TR>\n";
		}
		echo "</TABLE>\n";

		echo "<p align=\"center\"><INPUT TYPE=\"Submit\" value=\"Glasuj\" class=\"but\"></p>\n";
		echo "</FORM>\n";

		echo "<p align=\"center\" class=\"a10\">";
		echo "<a href=\"". $WebURL .'/'. $kat . $bid .'ViewPoll'. (isset($_GET['ID']) ? "&amp;ID=". $_GET['ID'] : "") ."\">";
		echo multiLang('<PollShowResults>', $lang);
		echo "</a></p>\n";
	}
	echo "</div>\n";

	echo "</div>\n";
}

// free recordset
unset($Anketa);
?>
