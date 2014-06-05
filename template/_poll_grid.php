<?php
/* _poll_grid.php - Display grid of all polls.
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

// category title & description
include("__category.php");

// display all polls (title, image & abstract)
$getPolls = $db->get_results(
	"SELECT *
	FROM  Ankete
	WHERE Datum <= '". date("Y-m-d") ."'
	  AND (Jezik = '". $lang ."' OR Jezik IS NULL)
	ORDER BY Datum DESC, Jezik"
	);

if ( !isset($_GET['rows']) ) $_GET['rows'] = 10;
$_GET['rows'] = min(50,max(10,(int)$_GET['rows']));

$MxPg = 10;
$NuPg = (int)(count($getPolls) / (3*$_GET['rows']) + 1);

$_GET['pg'] = min(max((int)$_GET['pg'], 1), $NuPg);

$StPg = (int)min(max(1, $_GET['pg'] - ($MxPg/2)), max(1, $NuPg - $MxPg + 1));
$EdPg = (int)min($StPg + $MxPg - 1, min($_GET['pg'] + $MxPg - 1, $NuPg));

$PrPg = max(1, $_GET['pg']-1);
$NePg = min($_GET['pg']+1, $EdPg);

$StaR = min(count($getPolls),max(1,($_GET['pg']-1)*(3*$_GET['rows'])+1));
$EndR = min(count($getPolls),max(1,$_GET['pg']*(3*$_GET['rows'])));

$Page = (int)$_GET['pg'];

if ( count($getPolls) ) {

	// define type of links
	$kat = $TextPermalinks ? ($IsIIS ? "$WebFile/" : ''). $KatText .'/' : '?kat='. $_GET['kat'];
	$bid = $TextPermalinks ? '?' : '&amp;';

	// display page navigation
	if ( $NuPg > 1 ) {
		echo "<TABLE class=\"navbutton\" BORDER=\"0\" CELLPADDING=\"10\" CELLSPACING=\"0\" WIDTH=\"100%\" HEIGHT=\"44\">\n";
		echo "<TR>\n";
		echo "\t<TD WIDTH=\"50%\">\n";
		if ( $Page > 1 )
			echo "\t<A HREF=\"$WebPath/$kat". $bid ."pg=". $PrPg ."&amp;rows=". $_GET['rows'] ."\">&laquo;&nbsp;". multiLang('<PrevPage>', $lang) ."</A>\n";
		else
			echo "\t&nbsp;\n";
		echo "</TD>\n";
		echo "\t<TD ALIGN=\"right\">\n";
		if ( $Page < $EdPg )
			echo "\t<A HREF=\"$WebPath/$kat". $bid ."pg=". $NePg ."&amp;rows=". $_GET['rows'] ."\">". multiLang('<NextPage>', $lang) ."&nbsp;&raquo;</A>\n";
		else
			echo "\t&nbsp;\n";
		echo "</TD>\n";
		echo "</TR>\n";
		echo "</TABLE>\n";
	}

	echo "<div class=\"grid fence\">";
	echo "<ul class=\"poll\">\n";

	for ( $i=$StaR; $i<=$EndR; $i++ ) {

		echo "<li class=\"g3\">\n";

		$Anketa     = $getPolls[$i-1];
		$Txt        = ReplaceSmileys($Anketa->Vprasanje,"$WebPath/pic/");
		$VsiGlasovi = $Anketa->Rez1 + $Anketa->Rez2 + $Anketa->Rez3 + $Anketa->Rez4 + $Anketa->Rez5 + $Anketa->Rez6 + $Anketa->Rez7 + $Anketa->Rez8 + $Anketa->Rez9 + $Anketa->Rez10;
		$Size       = 100;

		echo "<div class=\"a10\">". multiLang('<Date>', $lang) .': '. date('j.n.Y', sqldate2time($Anketa->Datum)) ."</div>\n";;
		echo "<p><i>". $Txt ."</i></p>\n";

		echo "<TABLE BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
		for ( $j=1; $j<=$Anketa->StOdg; $j++ ) {

			$Odg  = eval("return \$Anketa->Odg". $j .";");
			$Rez  = eval("return \$Anketa->Rez". $j .";");
			$Pct  = ($VsiGlasovi > 0) ? round($Rez*100 / $VsiGlasovi) : 0;
			$NPct = 100 - $Pct;
			$red  = $Size * $Pct/100;
			$wht  = $Size * $NPct/100;

			echo "<TR><TD ALIGN=\"left\" COLSPAN=\"3\">". $Odg ."&nbsp;</TD></TR>\n";
			echo "<TR>\n";
			echo "<TD ALIGN=\"left\">";
			echo "&nbsp;&nbsp;";
			if ( $Pct <> 0) echo "<div style=\"display:inline-block;background-color:red;width:". $red ."px;height:10px;\"></div>";
			if ( $NPct<> 0) echo "<div style=\"display:inline-block;background-color:white;width:". $wht ."px;height:10px;\"></div>";
			echo "</TD>\n";
			echo "<TD ALIGN=\"right\" CLASS=\"a10\">&nbsp;". $Pct ."%&nbsp;</TD>\n";
			echo "<TD ALIGN=\"right\" CLASS=\"a10\">&nbsp;[". $Rez ."]&nbsp;</TD>\n";
			echo "</TR>\n";
		}
		echo "</TABLE>\n";

		echo "<p>". multiLang('<PollAllVotes>', $lang) .": <B>". $VsiGlasovi ."</B></p>\n";

		echo "</li>\n";
	}

	echo "</ul>\n";
	echo "</div>\n";

	// display page navigation
	if ( $NuPg > 1 ) {
		echo "<div class=\"pages\">\n";
		echo "<TABLE BORDER=\"0\" CLASS=\"navbutton\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
		echo "<TR>\n";
		echo "\t<TD COLSPAN=\"2\" CLASS=\"a10\">\n";
		echo "&nbsp;". multiLang('<Page>', $lang) .":";
		if ( $StPg > 1 )
			echo "<A HREF=\"$WebPath/$kat". $bid ."pg=". ($StPg-1) ."&amp;rows=". $_GET['rows'] ."\">&laquo;</A>\n";
		if ( $Page > 1 )
			echo "<A HREF=\"$WebPath/$kat". $bid ."pg=$PrPg&amp;rows=". $_GET['rows'] ."\">&lt;</A>\n";
		for ( $i = $StPg; $i <= $EdPg; $i++ ) {
			if ( $i == $Page )
				echo "<FONT COLOR=\"$TxtExColor\"><B>$i</B></FONT>\n";
			else
				echo "<A HREF=\"$WebPath/$kat". $bid ."pg=$i&amp;rows=". $_GET['rows'] ."\">$i</A>\n";
		}
		if ( $Page < $EdPg )
			echo "<A HREF=\"$WebPath/$kat". $bid ."pg=$NePg&amp;rows=". $_GET['rows'] ."\">&gt;</A>\n";
		if ( $NuPg > $EdPg )
			echo "<A HREF=\"$WebPath/$kat". $bid ."pg=". ($EdPg+1) ."&amp;rows=". $_GET['rows'] ."\">&raquo;</A>\n";
		echo "</TD>\n";
		echo "<TD ALIGN=\"right\" CLASS=\"a10\">\n";
/*
		echo multiLang('<Show>', $lang) . "\n";
		echo "<A HREF=\"$WebPath/$kat". $bid ."rows=5\">5</A>\n";
		echo "<A HREF=\"$WebPath/$kat". $bid ."rows=10\">10</A>\n";
		echo "<A HREF=\"$WebPath/$kat". $bid ."rows=15\">15</A>\n";
		echo multiLang('<rows>', $lang) ."&nbsp;\n";
*/
		echo "</TD>\n";
		echo "</TR>\n";
		echo "</TABLE>\n";
		echo "</div>\n";
	}

} else
	echo "<div>Ni anket.</div>\n";

// free recordset
unset($getPolls);
?>
