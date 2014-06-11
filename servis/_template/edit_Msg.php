<?php
/*~ edit_Msg.php - confirmation dialog
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
require_once( "../inc/thumb/PhpThumb.inc.php" );

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
// deafult values for image upload sisze
if ( $x ) {
	$GalleryBase  = $x->GalleryBase;
	$DefPicSize   = (int)$x->DefPicSize;
	$DefThumbSize = (int)$x->DefThumbSize;
	$MaxPicSize   = (int)$x->MaxPicSize;
} else {
	$GalleryBase  = 'gallery';
	$DefPicSize   = 640;
	$DefThumbSize = 128;
	$MaxPicSize   = 1024;
}

?>
<script language="JavaScript" type="text/javascript">
<!-- //
function customResize() {
	// vertically resize edit child divs
	edit = $("#divContent").height(0).height( $("#divEdit").height() + $("#divEdit").position().top - $("#divContent").position().top - 6 );
}

$(document).ready(function(){
	window.customResize = customResize;

	// bind to the form's submit event
	$("form[name='Vnos']").submit(function(){
		$(this).ajaxSubmit({
			target: '#divEdit',
			iframe: false, // fix for listRefresh
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (empty(fObj.T))	{alert("Please enter a dimension!"); fObj.T.focus(); return false;}
				if (empty(fObj.R))	{alert("Please enter a dimesion!"); fObj.R.focus(); return false;}
				$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
				return true;
			} // pre-submit callback
		});
		return false;
	});
	// resize content div
	window.customResize();
	listRefresh();
});
//-->
</script>
<?php
if ( isset($_GET['file']) ) {
	$imageAsString = "";
	// resize image
	try {
		$thumb = PhpThumbFactory::create($StoreRoot ."/media/upload/". $_GET['file'], array('jpegQuality' => $jpgPct,'resizeUp' => false));
		$thumb->resize(128, 128);
		$imageAsString = $thumb->getImageAsString(); 
	} catch (Exception $e) {
	}
	// update URI
	$_SERVER['QUERY_STRING'] = preg_replace( '/\&file=[^&]+/i', '', $_SERVER['QUERY_STRING'] );
?>
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
<TD VALIGN="top" WIDTH="50%">
	<FIELDSET ID="fldData" style="min-height:153px;">
	<LEGEND ID="lgdData">File</LEGEND>
	<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
	<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
	<TR>
	<TD VALIGN="top" rowspan="4">
		<?php echo $imageAsString != "" ? "<p align=\"center\"><img src=\"data:image/png;base64,". base64_encode($imageAsString) ."\"></p>" : ""; ?>
	</TD>
	<TD colspan="2">
		<input type="hidden" name="Izbor" value="Msg">
		<input type="hidden" name="upload" value="<?php echo $_GET['file'] ?>">
	</TD>
	<TR>
	<TD>Icon:</TD>
	<TD>
		<input name="T" value="<?php echo $DefThumbSize ?>">px<br><span class="f10">(<0 kvadratna)</span>
	</TD>
	</TR>
	<TR>
	<TD>Dimension:</TD>
	<TD>
		<input name="R" value="<?php echo $DefPicSize ?>">px
	</TD>
	</TR>
	<TR>
	<TD align="center" colspan="2">
		<input type="submit" value="Upload" class="but">
	</TD>
	</TR>
	</TABLE>
	</FORM>
	</FIELDSET>
</TD>
<TD VALIGN="top" WIDTH="50%"></TD>
</TR>
</TABLE>
<?php
}
?>