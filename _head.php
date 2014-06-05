<?php
/*~ _head.php - page head
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
 * Content template (head).
 */
?>
<!--[if lt IE 8]><div id="badbrowser" class="warn"><?php echo multiLang('<Browser>', $lang); ?></div><![endif]-->
<NOSCRIPT><DIV ID="nojs" CLASS="warn"><?php echo multiLang('<JavaScript>', $lang); ?></DIV></NOSCRIPT>
<A NAME="top"></A>
<DIV CLASS="maintitle">
	<IMG ID="logo" SRC="<?php echo $WebPath ?>/pic/logo.png" ALT="" BORDER="0">
	<div style="position:absolute;top:0;left:0;padding: 5px 10px;">
	<div id="quickFind" class="find" onclick="$(this).animate({width:'116px'}, 250),$('#quickFindTxt').select();">
	<form name="quickFindFrm" action="<?php echo $WebPath; ?>/search.php" method="get" onsubmit="return (this.S.value.length>2);">
		<input id="quickFindTxt" type="Text" name="S" maxlength="32" value="" onfocus="$('#quickFind').animate({width:'116px'}, 250);" onkeyup="this.value==''?$('#quickFindClr').hide():$('#quickFindClr').show();">
		<a id="quickFindClr" href="javascript:void(0);" onclick="$(this).hide();$('#quickFindTxt').val('').select();"><img src="<?php echo $WebPath; ?>/pic/clear.png" alt="" border="0" style="width:16px;height:16px;"></a>
	</form>
	</div>
	</div>
	<h1><?php echo multiLang('<Title>', $lang); ?></h1>
	<h2><?php echo multiLang('<SubTitle>', $lang); ?></h2>
</DIV>
<DIV CLASS="mainmenu">
<?php
if ( $Rubrike ){
	$i = $db->num_rows;
	echo "<ul>\n";
	foreach ( $Rubrike as $rub) {
		$kat    = ($TextPermalinks) ? ($IsIIS ? 'index.php/' : ''). $rub->Ime .'/' : '?kat='. $rub->ID;
		$Naslov = ($rub->Naziv == "") ? $rub->Ime : $rub->Naziv;	// use short name if translation does not exist
		echo "<li>";
		if ( $rub->ID == left($_GET['kat'], 2) ) echo "<B>";
		echo "<A HREF=\"". $WebPath ."/". $kat ."\">". $Naslov ."</A>";
		if ( $rub->ID == left($_GET['kat'], 2) ) echo "</B>";
		echo "</li>\n";
	}
	echo "</ul>\n";
}
?>
</DIV>
