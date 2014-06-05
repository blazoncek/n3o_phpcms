<?php
/*~ _foot.php - page footer
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend)                                             |
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
| N3O CMS (frontend) is free software: you can redistribute it and/or       |
| modify it under the terms of the GNU Lesser General Public License as     |
| published by the Free Software Foundation, either version 3 of the        |
| License, or (at your option) any later version.                           |
|                                                                           |
| N3O CMS (frontend) is distributed in the hope that it will be useful,     |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU Lesser General Public License for more details.                       |
'---------------------------------------------------------------------------'
*/
/**
 * Content template (footer)
 */
?>
<div class="find"><form action="<?php echo $WebPath; ?>/search.php" method="get" onsubmit="return (this.S.value.length>2);">
<input id="text_search" type="text" name="S" value="" onkeyup="this.value==''?$('#clear_search').hide():$('#clear_search').show();">
<a id="clear_search" href="javascript:void(0);" onclick="$('#clear_search').hide();$('#text_search').val('').select();"><img src="<?php echo $WebPath; ?>/pic/clear.png" alt="" border="0" style="width:16px;height:16px;"></a>
</form></div>
<?php

// categories
$kat = ($TextPermalinks) ? ($IsIIS ? 'index.php/' : ''). $KatText .'/?' : '?kat='. $_GET['kat'] .'&amp;';
echo "<div class=\"footmenu\">\n";
//echo "<h2>". multiLang('<Categories>', $lang) ."</h2>\n";
if ( $Rubrike ){
	echo "<ul>\n";
	foreach ( $Rubrike as $rub ) {
		$kat    = ($TextPermalinks) ? ($IsIIS ? 'index.php/' : ''). $rub->Ime .'/' : '?kat='. $rub->ID;
		$Naslov = ($rub->Naziv == "") ? $rub->Ime : $rub->Naziv;	// če je jezikovni opis prazen uporabim ime/sklic
		echo "<li>";
		echo "<a href=\"". $WebPath ."/". $kat ."\" title=\"". $Naslov ."\">". $Naslov ."</A>";
		echo "</li>\n";
	}
	echo "</ul>\n";
}
echo "</div>\n";
/*
// tags
$Tags = $db->get_results(
	"SELECT
		T.TagID,
		T.TagName,
		count(BT.TagID) as TagCount
	FROM Tags T
		LEFT JOIN BesedilaTags BT ON BT.TagID=T.TagID
	GROUP BY T.TagID, T.TagName
	HAVING count(BT.TagID)>0
	ORDER BY 3 DESC
	LIMIT 10"
	);
echo "<div class=\"links\">\n";
echo "<h2>". multiLang('<Tags>', $lang) ."</h2>\n";
if ( $Tags ){
	echo "<ul>\n";
	for ( $i=0; $i<9; $i++ ) {
		$tag = ($TextPermalinks) ? 'TAG'. $Tags[$i]->TagID .'/': '&amp;tag='. $Tags[$i]->TagID;
		echo "<li>";
		echo "<a href=\"$WebPath/$kat". $tag ."\" title=\"". $Tags[$i]->TagName ."\">";
		echo "<img src=\"pic/tag.png\" alt=\"\" align=\"absmiddle\" border=\"0\" class=\"symbol\"> ";
		echo $Tags[$i]->TagName;
		echo "</a>";
		echo "</li>\n";
	}
	echo "<li>...</li>\n";
	echo "</ul>\n";
}
echo "</div>\n";

// social icons
$Social = $db->get_results(
	"SELECT
		S.SifrText,
		ST.SifNaziv
	FROM
		Sifranti S
		INNER JOIN SifrantiTxt ST ON S.SifrantID = ST.SifrantID
	WHERE
		S.SifrCtrl = 'SOCI'
		AND ST.Jezik IS NULL
	ORDER BY
		S.SifrZapo"
);
echo "<div class=\"links\" style=\"float:right;\">\n";
echo "<h2>". multiLang('<FollowMe>', $lang) ."</h2>\n";
if ( count($Social) > 1 ) {
	echo "<ul>\n";
	foreach ( $Social as $s ) {
		echo "<li>";
		if ( left($s->SifNaziv,4) == 'http' )
			echo "<a href=\"". $s->SifNaziv ."\" title=\"". $s->SifrText ."\" target=\"_blank\">";
		else
			echo "<a href=\"$WebPath/". $s->SifNaziv ."\" title=\"". $s->SifrText ."\">";
		echo "<img src=\"$WebPath/pic/soc/". strtolower($s->SifrText) .".png\" border=\"0\" align=\"absmiddle\" alt=\"\">";
		echo " ". $s->SifrText;
		echo "</a>";
		echo "</li>\n";
	}
	echo "</ul>\n";
}
echo "</div>\n";

echo "<div style=\"clear:both;\"></div>\n";
*/
?>
<div class="copyright">
<?php echo multiLang( '<CopyRight>', $lang ); ?>
</div>
<div class="icons">
<?php
// language selection
$kat = ($TextPermalinks) ? ($IsIIS ? 'index.php/' : ''). $KatText .'/?' : '?kat='. $_GET['kat'] .'&amp;';
$Jeziki = $db->get_results("SELECT Jezik, Opis, Ikona, LangCode FROM Jeziki WHERE Enabled=1 ORDER BY Jezik");
if ( count($Jeziki) > 1 ) {
	echo "<div class=\"lang\" style=\"\">\n";
	echo "<ul>\n";
	$i = $db->num_rows;
	foreach ( $Jeziki as $Jezik ) {
		echo "<li>";
		echo "<a href=\"$WebPath/". $kat ."lng=$Jezik->Jezik\" title=\"$Jezik->Opis\">";
		echo "<img src=\"$WebPath/pic/". ($Jezik->Ikona=="" ? "lng/".$Jezik->LangCode.".png" : $Jezik->Ikona) ."\" border=\"0\" alt=\"\" class=\"icon24\">";
		echo "</a>";
		echo "</li>\n";
	}
	echo "</ul>\n";
	echo "</div>\n";
}

// social icons
$Social = $db->get_results(
	"SELECT
		S.SifrText,
		ST.SifNaziv
	FROM
		Sifranti S
		INNER JOIN SifrantiTxt ST ON S.SifrantID = ST.SifrantID
	WHERE
		S.SifrCtrl = 'SOCI'
		AND ST.Jezik IS NULL
	ORDER BY
		S.SifrZapo DESC"
);
echo "<div class=\"soci\" style=\"\">\n";
if ( count($Social) > 1 ) {
	echo "<ul>\n";
	foreach ( $Social as $s ) {
		echo "<li>";
		if ( left($s->SifNaziv,4) == 'http' )
			echo "<a href=\"". $s->SifNaziv ."\" title=\"". $s->SifrText ."\" target=\"_blank\">";
		else
			echo "<a href=\"$WebPath/". $s->SifNaziv ."\" title=\"". $s->SifrText ."\">";
		echo "<img src=\"$WebPath/pic/soc/". strtolower($s->SifrText) .".png\" border=\"0\" align=\"absmiddle\" alt=\"\" class=\"badge\">";
		echo "</a>";
		echo "</li>\n";
	}
	echo "</ul>\n";
} else {
	echo "<ul>\n";
	// Twitter link
	if ( isset($TwitterName) )
		echo "<li><a href=\"http://twitter.com/". $TwitterName ."\" title=\"". multiLang('<Title>', $lang) ."\"><img src=\"$WebPath/pic/twitter.png\" border=\"0\" alt=\"Twitter\" class=\"badge\"></a></li>\n";
	// RSS link
	echo "<li><a href=\"$WebPath/RSS.php\" title=\"". multiLang('<Subscribe>', $lang) ."\"><img src=\"$WebPath/pic/RSS.png\" border=\"0\" alt=\"RSS\" class=\"badge\"></a></li>\n";
	echo "</ul>\n";
}
echo "</div>\n";
?>
</div>
