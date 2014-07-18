<?php
/*~ vnos_Images.php - image upload & select framework
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
// deafult values for image upload size
if ( !isset($_GET['base']) ) $_GET['base'] = ($x ?      $x->GalleryBase  : 'gallery');
if ( !isset($_GET['S']) )    $_GET['S']    = ($x ? (int)$x->DefPicSize   : 640);
if ( !isset($_GET['T']) )    $_GET['T']    = ($x ? (int)$x->DefThumbSize : 128);
?>
<script type="text/javascript">
<!--
function deleteimg(img_id, img_name) {
	if (confirm("Delete '"+img_name+"'\n\nThis can affect other texts/galleries!\nAre you sure?")) {
		$('#VnosSlikeD').load("inc.php?Izbor=ImagesRight&delete="+img_id);
		$('#frm_image').resetForm();
		setTimeout("$('#ListSlike').load('inc.php?Izbor=ImagesList&BesediloID=<?php echo $_GET['BesediloID'] ?>');",250);
	}
}

function removeimg(img_id, img_name, img_text) {
	$('#frm_image').resetForm();
	$('#ListSlike').load("inc.php?Izbor=ImagesList&BesediloID=<?php echo $_GET['BesediloID'] ?>&BrisiSliko=" + img_id);
}

function addimg(img_name, text, id, mediaid) {
	if (!text) text='';
	if (!id) id='';
	//move data to the form
	frm_image = document.getElementById("frm_image");
	frm_image.Datoteka.value = text;
	frm_image.ID.value = id;
	frm_image.MediaID.value = mediaid;
	frm_image.Datoteka.focus();
	$(frm_image).ajaxSubmit({target: '#ListSlike'});
}

function fixSlikeSize() {
	$("#VnosSlikeD").height( $(window).height() - $("#VnosSlikeD").position().top );
	$("#ListSlike").height( $(window).height() - $("#ListSlike").position().top );
}

$(document).ready(function(){
	fixSlikeSize();

	// AJAX form submit
	$("#frm_upload").submit(function(){
		$(this).ajaxSubmit({
			target: '#VnosSlikeD',
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (fObj.photo_file.value=='') {alert("Please select an image!"); fObj.photo_file.focus(); return false;}
				if (fObj.maxsize.value=='')    {alert("Please enter the size!"); fObj.maxsize.focus(); return false;}
				$('#VnosSlikeD').html('<div align="center" class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</div>');
				return true;
			} // pre-submit callback
		});
		$(this).resetForm();
		return false;
	});
	$("#frm_image").submit(function(){
		$(this).ajaxSubmit({
			target: '#ListSlike',
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (fObj.Datoteka.value=='') {alert("Please select an image on the right side!"); fObj.Datoteka.focus(); return false;}
				$('#ListSlike').html('<div align="center" class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</div>');
				return true;
			} // pre-submit callback
		});
		return false;
	});

	// load subdata
	$("#VnosSlikeD").load('inc.php?Izbor=ImagesRight');
	$("#ListSlike").load('inc.php?Izbor=ImagesList&BesediloID=<?php echo $_GET['BesediloID'] ?>');
});

$(window).resize(fixSlikeSize);

$(window).unload(function(){
	window.opener.$("#divSlike").load('inc.php?Izbor=txtGallery&BesediloID=<?php echo $_GET['BesediloID'] ?>');
});
//-->
</script>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
	<td width="55%" height="96" valign="top" rowspan="1"><div id="VnosSlikeL" style="padding:5px;">
		<form id="frm_upload" name="frm_upload" action="inc.php?Izbor=ImagesRight" method="post" enctype="multipart/form-data">
		<div><nowrap><font color="red"><B>1.</B></font> Find image,
			<input type="file" name="photo_file" style="border:none;"></nowrap><br>
			<input name="large" type="checkbox" checked style="border:none;padding:0px;margin:0px;"> retain original image
			&gt;<input name="maxsize" type="text" size="4" maxlength="4" value="<?php echo $_GET['S'] ?>">px,
			thumbnail size <input type="Text" name="thumbnail" size="3" maxlength="3" VALUE="<?php echo abs($_GET['T']) ?>">px
			(0=none), make <input name="square" type="checkbox" <?php echo (int)$_GET['T']<0 ? "checked" : "" ?> style="border:none;padding:0px;margin:0px;"> square thumbnail</div>
		<div align="right"><SPAN CLASS="f10">... and click <B>Add &raquo;</B>.</SPAN>
			<input type="submit" value=" Add &raquo; " class="but"></div>
		</form>

		<form id="frm_image" action="inc.php?Izbor=ImagesList&amp;BesediloID=<?php echo $_GET['BesediloID'] ?>" method="post">
		<input type="hidden" name="Datoteka" value="">
		<input type="hidden" name="ID" value="">
		<input type="hidden" name="MediaID" value="">
		</form>
		<div><font color="red"><B>2.</B></font> Click an image on the right.</div>
	</div></td>
	<td width="45%" valign="top" rowspan="2"><div id="VnosSlikeD" style="overflow-y:auto;padding:5px;"></div></td>
</tr>
<tr>
	<td valign="top"><div id="ListSlike" style="background-color:lightgrey;padding:5px;overflow:auto;"></div></td>
</tr>
</table>
