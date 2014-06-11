<?php
/*~ inc_SlikeRight.php - image upload & select (part of vnos_Slike.php)
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

if ( isset($_FILES['photo_file']) ) {

	// image parameters
	$x = $db->get_row(
		"SELECT
			ST.SifNaziv AS GalleryBase,
			S.SifNVal1 AS DefPicSize,
			S.SifNVal2 AS DefThumbSize,
			S.SifNVal3 AS MaxPicSize
		FROM
			Sifranti S
			LEFT JOIN SifrantiTxt ST ON S.SifrantID=ST.SifrantID
		WHERE
			S.SifrCtrl = 'BESE'
			AND S.SifrText = 'Gallery'
		ORDER BY
			ST.Jezik DESC"
		);

	// Hard Code path here
	$URLpath    = $WebPath   .'/media/'. ($x ? $x->GalleryBase  : 'gallery') .'/'. date("Y");
	$UPLOADpath = $StoreRoot .'/media/'. ($x ? $x->GalleryBase  : 'gallery') .'/'. date("Y");

	// try to create upload folders
	@mkdir($UPLOADpath, 0777, true);
	@mkdir($UPLOADpath."/thumbs");
	@mkdir($UPLOADpath."/large");

	if ( isset($_POST['thumbnail']) && (int)$_POST['thumbnail']!=0 ) {
		$T = min(128,max(48,abs((int)$_POST['thumbnail']))); // limit thumbnail size
		if ( isset($_POST['square']) && strtolower($_POST['square'])=='on' )
			$T = -$T; // <0 = square
	} else
		$T = 0; // no thumbnail

	// adjust resized image size
	if ( !isset($_POST['maxsize']) || $_POST['maxsize']=="" )
		$_POST['maxsize'] = "640"; // default value
	$M = min(1024,max(480,(int)$_POST['maxsize']));

	// original image limits
	$limit = ($x && $x->MaxPicSize ? $x->MaxPicSize : 1024);

	// upload & resize image
	$photo = ImageResize(
		'photo_file', // $_FILE field
		$UPLOADpath,  // upload path
		'thumbs/',    // thumbnail prefix
		'large/',     // original image prefix
		array($M,$limit), // resized image limits
		$T,           // [square] thumbnail size
		$jpgPct);     // JPEG quality

	if ( $photo ) { // successful upload & resize

		// insert info into Media table
		$db->query(
			"INSERT INTO Media (
				Naziv,
				Datoteka,
				Meta,
				Velikost,
				Tip,
				Slika,
				Datum,
				Izpis
			) VALUES (
				'". $photo['name'] ."',
				'". substr($UPLOADpath,strlen($StoreRoot)+1) .'/'. $photo['name'] ."',
				'f=". substr($UPLOADpath,strlen($StoreRoot)+1) .";w=". $photo['iw'] .";h=". $photo['ih'] .";rw=". $photo['rw'] .";rh=". $photo['rh'] .";tw=". $photo['tw'] .";th=". $photo['th'] ."',
				". $photo['size'] .",
				'PIC',
				NULL,
				'".date("Y-m-d H:i:s")."',
				1
			)"
		);
		// create shortcut link
		echo "<a href=\"javascript:parent.parseform('".substr($UPLOADpath,strlen($StoreRoot)+1)."/". $photo['name'] ."','". $photo['name'] ."',null,". $db->insert_id .");\" class=\"red\">Slika dodana!</a><br>\n";
	} else { // error during upload or resize
		echo "<div class=\"red\">Error uploadin or resizing image!</div>\n";
	}
}

// delete media (remove reference and file)
if ( isset($_GET['delete']) && (int)$_GET['delete'] != "" ) {
	$db->query("START TRANSACTION");
	$Slika    = $db->get_var( "SELECT Slika FROM Media WHERE MediaID = ".(int)$_GET['delete'] );
	$Datoteka = $db->get_var( "SELECT Datoteka FROM Media WHERE MediaID = ".(int)$_GET['delete'] );

	// BRISANJE DATOTEK
	if ( $Slika && $Slika != "" ) {
		$e = right($Slika, 4);
		$b = left($Slika, strlen($Slika)-4);
		@unlink($StoreRoot ."/media/". $Slika);
		@unlink($StoreRoot ."/media/". $b .'@2x'. $e);
		@unlink($StoreRoot ."/media/thumbs/". $Slika);
		@unlink($StoreRoot ."/media/thumbs/". $b .'@2x'. $e);
		@unlink($StoreRoot ."/media/large/". $Slika);
	}

	if ( $Datoteka && $Datoteka != "" ) {
		$tPath = $StoreRoot . (contains($Datoteka,"/")? "/": "/media/");
		// for image files delete eventual thumbs and originals
		$tDir  = dirname($tPath . $Datoteka);  // get full path
		$tFile = basename($tPath . $Datoteka); // get filename
		$e = right($tFile, 4);
		$b = left($tFile, strlen($Slika)-4);
		
		@unlink($tDir ."/". $tFile);
		@unlink($tDir ."/". $b .'@2x'. $e);
		@unlink($tDir ."/thumbs/". $tFile);
		@unlink($tDir ."/thumbs/". $b .'@2x'. $e);
		@unlink($tDir ."/large/". $tFile);
	}

	$db->query( "DELETE FROM BesedilaMedia   WHERE MediaID = ".(int)$_GET['delete'] );
	$db->query( "DELETE FROM BesedilaSlike   WHERE MediaID = ".(int)$_GET['delete'] );
	$db->query( "DELETE FROM KategorijeMedia WHERE MediaID = ".(int)$_GET['delete'] );
	$db->query( "DELETE FROM MediaOpisi      WHERE MediaID = ".(int)$_GET['delete'] );
	$db->query( "DELETE FROM Media           WHERE MediaID = ".(int)$_GET['delete'] );
	$db->query("COMMIT");
	echo "<div class=\"red\">Slika zbrisana!</div>\n";
}

// define default values for URL ID and Find parameters (in case not defined)
if ( !isset( $_GET['Sort'] ) ) $_GET['Sort'] = "Datum";

// define sort order
$Sort = "M.Datum DESC";
if ( $_GET['Sort'] == "Datum" )
	$Sort = "M.Datum DESC";
if ( $_GET['Sort'] == "Datum2" )
	$Sort = "M.Datum ASC";
elseif ( $_GET['Sort'] == "Velikost" )
	$Sort = "M.Velikost";
elseif ( $_GET['Sort'] == "Velikost2" )
	$Sort = "M.Velikost DESC";
elseif ( $_GET['Sort'] == "Naziv" )
	$Sort = "M.Naziv";
elseif ( $_GET['Sort'] == "Naziv2" )
	$Sort = "M.Naziv DESC";

// get media
$List = $db->get_results(
	"SELECT
		M.MediaID,
		M.Naziv,
		M.Izpis,
		M.Datoteka,
		M.Datum,
		M.Velikost
	FROM Media M
	WHERE Tip='PIC' ".
	(isset($_GET['find']) && $_GET['find']!='' ? "AND M.Naziv LIKE '%".$_GET['find']."%' " : '') ."
	ORDER BY $Sort"
);

$RecordCount = count($List);

// are we requested do display different page?
$Page = !isset($_GET['pg']) ? 1 : (int)$_GET['pg'];

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

?>
<script language="JavaScript" type="text/javascript">
<!-- //
$(document).ready(function(){
	$("form[name='ListFind']").submit(function(){
		$(this).ajaxSubmit({
			iframe: false, // fix for listRefresh
			target: '#VnosSlikeD',
			beforeSubmit: function( formDataArr, jqObj, options ) {
				$('#VnosSlikeD').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
				return true;
			} // pre-submit callback
		});
		return false;
	});
});
//-->
</script>
<?php

// display results
if ( count($List) == 0 ) {
	echo "<div class=\"frame\" style=\"display: table;height: 100px;width: 100%;\">";
	echo "<div style=\"background-color: white;display: table-cell;text-align: center;vertical-align: middle;\"><b>No data!</b></div>\n";
	echo "</div>\n";
} else {

	$link = "". $_SERVER['PHP_SELF']."?Izbor=".$_GET['Izbor'];

	// display filter field
	echo "<div class=\"find\" style=\"margin-top:5px;border-top:1px solid black;\">\n";
	echo "<form name=\"ListFind\" action=\"". $link ."\" method=\"get\">\n";
	echo "<input name=\"Sort\" type=\"Hidden\" value=\"". $_GET['Sort'] ."\">\n";
	echo "<input type=\"Text\" name=\"find\" id=\"inpFind\" maxlength=\"32\" value=\"". (isset($_GET['find']) ? $_GET['find'] : '') ."\" onkeypress=\"$('#clrFind').show();\" onfocus=\"if ($('#inpFind').val()!='') $('#clrFind').show();\">\n";
	echo "<a id=\"clrFind\" href=\"javascript:void(0);\" onclick=\"$(this).hide();$('#inpFind').val('').select();\"><img src=\"pic/list.clear.gif\" border=\"0\"></a>\n";
	echo "</form>\n";
	echo "</div>\n";

	// sorting options
	$link .= (isset($_GET['find']) && $_GET['find']!='' ? '&find='.$_GET['find'] : '');
	echo "<DIV ALIGN=\"center\">Sort: \n";
	echo "<A HREF=\"#\" ONCLICK=\"$('#VnosSlikeD').load('". $link ."&Sort=".(($_GET['Sort']=="Naziv")? "Naziv2": "Naziv")."');\">ime</A> |\n";
	echo "<A HREF=\"#\" ONCLICK=\"$('#VnosSlikeD').load('". $link ."&Sort=".(($_GET['Sort']=="Datum")? "Datum2": "Datum")."');\">datum</A> |\n";
	echo "<A HREF=\"#\" ONCLICK=\"$('#VnosSlikeD').load('". $link ."&Sort=".(($_GET['Sort']=="Velikost")? "Velikost2": "Velikost")."');\">velikost</A>\n";
	echo "</DIV>\n";

	$link .= (isset($_GET['Sort']) && $_GET['Sort']!='' ? '&Sort='.$_GET['Sort'] : '');
	if ( $NuPg > 1 ) {
		echo "<DIV CLASS=\"pg\" style=\"border-bottom: darkgrey solid 1px; border-top: darkgrey solid 1px; padding: 5px 0px;\">\n";
		if ( $StPg > 1 )
			echo "<a href=\"#\" onclick=\"$('#VnosSlikeD').load('". $link ."&pg=".($StPg-1)."');\">&laquo;</a>\n";
		if ( $Page > 1 )
			echo "<a href=\"#\" onclick=\"$('#VnosSlikeD').load('". $link ."&pg=". $PrPg ."');\">&lt;</a>\n";
		for ( $i = $StPg; $i <= $EdPg; $i++ ) {
			if ( $i == $Page )
				echo "<FONT COLOR=\"red\"><B>$i</B></FONT>\n";
			else
				echo "<a href=\"#\" onclick=\"$('#VnosSlikeD').load('". $link ."&pg=". $i ."');\">$i</a>\n";
		}
		if ( $Page < $EdPg )
			echo "<a href=\"#\" onclick=\"$('#VnosSlikeD').load('". $link ."&pg=". $NePg ."');\">&gt;</a>\n";
		if ( $NuPg > $EdPg )
			echo "<a href=\"#\" onclick=\"$('#VnosSlikeD').load('". $link ."&pg=".($EdPg<$NuPg? $EdPg+1: $EdPg)."');\">&raquo;</a>\n";
		echo "</DIV>\n";
	}

	echo "<table width=\"100%\" border=\"0\" cellpadding=\"1\" cellspacing=\"0\">\n";
	echo "<col width=\"20\">\n";
	echo "<col>\n";
	echo "<col width=\"12%\">\n";
	echo "<col width=\"20%\">\n";
	echo "<col width=\"24\">\n";
	$i = $StaR-1;
	while ( $i < $EndR ) {
		// get list item
		$Item = $List[$i++];
		echo "<tr onmouseover=\"this.style.backgroundColor='whitesmoke';\" onmouseout=\"this.style.backgroundColor='';\">\n";
		echo "<td><img src=\"../". dirname($Item->Datoteka)."/thumbs/".basename($Item->Datoteka) ."\" width=\"15\" height=\"15\" border=\"0\"></td>\n";
		echo "<td><a href=\"javascript:addimg('$Item->Datoteka','$Item->Naziv',null,$Item->MediaID);\">$Item->Naziv</a></td>\n";
		echo "<td align=\"right\">&nbsp;".(int)($Item->Velikost/1024)."k&nbsp;</td>\n";
		echo "<td align=\"right\">&nbsp;".date("j.n.Y",sqldate2time($Item->Datum))."&nbsp;</td>\n";
		echo "<td align=\"right\" valign=\"top\">";
		echo "<a href=\"#\" onclick=\"window.opener.loadTo('Edit','edit.php?Izbor=Media&ID=$Item->MediaID'),window.parent.close()\" title=\"Uredi\"><IMG SRC=\"pic/list.edit.gif\" WIDTH=11 HEIGHT=11 ALT=\"Uredi\" BORDER=\"0\" ALIGN=\"absmiddle\" CLASS=\"icon\"></a>";
		echo "<a href=\"javascript:deleteimg($Item->MediaID,'$Item->Naziv');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Delete\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a>";
		echo "</td>\n";
		echo "</tr>\n";
	}
	echo "</table>\n";
}
?>
