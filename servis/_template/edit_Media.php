<?php
/*~ edit_Media.php - Add/edit media uploads.
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

if ( !isset( $_GET['ID'] ) ) $_GET['ID'] = "0";

$Podatek = $db->get_row( "SELECT * FROM Media WHERE MediaID = " . (int)$_GET['ID'] );
// get ACL
if ( $Podatek )
	$ACL = userACL( $Podatek->ACLID );
else
	$ACL = $ActionACL;

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
$GalleryBase  = "";
$DefPicSize   = 640;
$DefThumbSize = 128;
$MaxPicSize   = 1024;
if ( $x ) {
	$GalleryBase  = $x->GalleryBase;
	$DefPicSize   = (int)$x->DefPicSize;
	$DefThumbSize = (int)$x->DefThumbSize;
	$MaxPicSize   = (int)$x->MaxPicSize;
}
?>
<script language="JavaScript" type="text/javascript">
<!-- //
function customResize() {
	// vertically resize edit child divs
	edit = $("#divContent").height(0).height( $("#divEdit").height() + $("#divEdit").position().top - $("#divContent").position().top - 6 );
	// fix scroller problem when resizing
	if ( $("#divBe").html() ) $("#divBe").height(0);
	if ( $("#divRu").html() ) $("#divRu").height(0);
	// actualy resize
	if ( $("#divBe").html() ) $("#divBe").height( edit.height() + edit.position().top - $("#divBe").position().top - 10 );
	if ( $("#divRu").html() ) $("#divRu").height( edit.height() + edit.position().top - $("#divRu").position().top - 10 );
	// resize names div
	frame = $("#divNames");
	if ( frame.html() ) frame.height( $("#divData").parent().height() - 8 );
	// vertically resize edit child divs
	edit = $("#HTMLeditor").parent(); // TD element
	if ( edit.html() ) {
		$("#HTMLeditor").width( frame.width() + frame.position().left - edit.position().left - 4 );
		$("#HTMLeditor_ifr").width( $("#HTMLeditor").width() - 2 );
	}
}

// prevent default drop behaviour on drop (outside fileupload area)
$(document).bind('drop dragover', function (e) {
    e.preventDefault();
});

$(document).ready(function(){
	window.customResize = customResize;

	// bind to the form's submit event
	$("form[name='Vnos']").submit(function(){
		$(this).ajaxSubmit({
			target: '#divEdit',
			iframe: false, // fix for listRefresh
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (empty(fObj.Naziv))					{alert("Prosim vnesite naziv!"); fObj.Naziv.focus(); return false;}
				if (fObj.Dodaj && empty(fObj.Dodaj))	{alert("Prosim, izberite ali dodajte datoteko!"); fObj.Dodaj.focus(); return false;}
				$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Posodabljam" border="0" height="14" width="14" align="absmiddle">&nbsp;: Posodabljam ...</span>');
				return true;
			} // pre-submit callback
		});
		return false;
	});
	$("form[name='Datoteka']").submit(function(){
		var html = $('#thumbImage').html();
		$('#thumbImage').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Posodabljam" border="0" height="14" width="14" align="absmiddle">&nbsp;: Posodabljam ...</span>');
		$(this).ajaxSubmit({
			dataType: 'json',
			target: '#thumbImage',
			success: function(data){
				if ( data.files['name'] ) {
					html  = '<a href="../'+data.files['path']+'/'+data.files['name']+'" class="fancybox" rel="lightbox" target="_blank">';
					html += '<img src="../'+data.files['path']+'/'+data.files['name']+'" alt="" border="0" style="max-width:128px;"></a>\n'
				}
				$('#thumbImage').html(html);
			}
		});
		return false;
	});
	// drag&drop image uploading
    $('#fileupload').fileupload({
        dataType: 'json',
		dropZone: $('#thumbImage'),
		pasteZone: null,
		add: function(e, data) {
			// extract drop target from e.originalEvent.currentTarget.id
			// and set url acordingly
			switch ( e.originalEvent.currentTarget.id ) {
			  case 'fileupload' :
			  case 'thumbImage' :
				data.url = 'upload_image.php?mid=<?php echo $_GET['ID'] ?>&p=media';
				data.context = $('#thumbImage').html('<div class="gry center"><img src="pic/control.spinner.gif" alt="Posodabljam" border="0" height="14" width="14" align="absmiddle">&nbsp;: Posodabljam ...</div>');
			  	break;
			}
			data.submit();
		},
        done: function (e, data) {
			switch ( data.context.attr("id") ) {
			  case 'fileupload' :
			  case 'thumbImage' :
				if ( data.result.files['name'] ) {
					html  = '<a href="../'+data.result.files['path']+'/'+data.result.files['name']+'" class="fancybox" rel="lightbox" target="_blank">';
					html += '<img src="../'+data.result.files['path']+'/'+data.result.files['name']+'" alt="" border="0" style="max-width:128px;"></a>\n'
					data.context.html(html);
				} else if ( data.result.files['error'] ) {
					html = '<img src="../pic/nislike.png" alt="'+data.result.files['error']+'" border="0" style="max-width:128px;">\n'
					data.context.html(html);
				}
				break;
			}
        }
    });

	// load subdata
	if ( $("#divNames").html() ) $("#divNames").load('inc.php?Izbor=MediaOpis&MediaID=<?php echo $_GET['ID'] ?>');
	if ( $("#divBe").html() ) $("#divBe").load('inc.php?Izbor=MediaBesedila&MediaID=<?php echo $_GET['ID'] ?>');
	if ( $("#divRu").html() ) $("#divRu").load('inc.php?Izbor=MediaKategorije&MediaID=<?php echo $_GET['ID'] ?>');

	// initialize LigtBox
	$("a.fancybox").fancybox({
		'padding'       :   10,
		'margin'        :   10,
		'overlayOpacity':   0.7,
		'overlayColor'  :   '#000',
		'titlePosition' :   'over',
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'elastic',
		'speedIn'		:	300, 
		'speedOut'		:	200, 
		'overlayShow'	:	true
	});
	
	// resize content div
	window.customResize();
	
	// search fields
	$("form[name^=findFrm]").submit(function(){
		$(this).ajaxSubmit({
			target: '#div'+$(this).attr('name').substr(7,2)
		});
		return false;
	});
	$('input[id^=findTxt]').click(function(){
		$('#findClr'+$(this).attr('id').substr(7,2)).show();
		if ( ($(this).val().substr(0,1) == " " && $(this).val().substr($(this).val().length-1,1) == " ")
			|| $(this).val() == '' ) {
			$(this).css('color','#000').val('');
			$('#findClr'+$(this).attr('id').substr(7,2)).hide();
		}
	}).keypress(function(e){
		if ( e.keyCode == 13 ) {
			//e.preventDefault();
		}
		$('#findClr'+$(this).attr('id').substr(7,2)).show();
	}).keyup(function(e){
		if ( e.keyCode == 13 ) {
			//e.preventDefault();
		}
		if ( $('#findTxt'+$(this).attr('id').substr(7,2)).val() == "" )
			$('#findClr'+$(this).attr('id').substr(7,2)).hide();
	});
	// add clear action
	$('a[id^=findClr]').click(function(){
		$(this).hide();
		$('#findTxt'+$(this).attr('id').substr(7,2)).val('').select();
		$('form[name=findFrm'+$(this).attr('id').substr(7,2)+']').submit();
	});
	// adjust width
	$('div[id^=find]').width(function(){
		return ($(this).parent().parent().width()-46) + "px";
	});

	// refresh list
	listRefresh();
});
//-->
</script>

<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD VALIGN="top" WIDTH="50%">

	<FIELDSET ID="fldData" style="min-height:153px;">
	<LEGEND ID="lgdData">
<?php if ( contains($ACL, "W") && $Podatek ) {
		echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=ACL&ACL=".$Action->Action;
		echo "&MediaID=" . $_GET['ID'] . (($Podatek && $Podatek->ACLID!="")? "&ID=".$Podatek->ACLID: "") . "')\" TITLE=\"Uredi pravice\">";
		echo "<IMG SRC=\"pic/control.permissions.gif\" HEIGHT=\"16\" WIDTH=\"16\" BORDER=0 ALT=\"Dovoljenja\" ALIGN=\"absmiddle\"></A>&nbsp;:";
} ?>
		Osnovni&nbsp;podatki</LEGEND>
	<DIV ID="divData" STYLE="overflow:auto;">
	<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<?php if ( isset($_GET['KategorijaID']) ) : ?>
	<INPUT NAME="KategorijaID" VALUE="<?php echo $_GET['KategorijaID'] ?>" TYPE="Hidden">
<?php endif ?>
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="100%">
	<TR> 
		<TD ALIGN="right"><FONT COLOR="red"><B>Izpis:</B></FONT>&nbsp;</TD>
		<TD><INPUT TYPE="Checkbox" NAME="Izpis" VALUE="yes" <?php echo ($Podatek && $Podatek->Izpis)? " CHECKED": "" ?>></TD>
		<TD ALIGN="right"><?php if ( $Podatek ) echo "Tip:&nbsp;<INPUT TYPE=\"Text\" SIZE=\"4\" VALUE=\"$Podatek->Tip\" DISABLED>" ?></TD>
		<TD ALIGN="right" ID="thumbImage" ROWSPAN="<?php echo ($Podatek? 5: 4) ?>" VALIGN="top" WIDTH="130" HEIGHT="130">
<?php if ( $Podatek && $Podatek->Slika != "" ) : ?>
		<A HREF="../media/media/<?php echo $Podatek->Slika; ?>" CLASS="fancybox" REL="lightbox" TARGET="_blank">
		<IMG SRC="../media/media/<?php echo $Podatek->Slika; ?>" BORDER="0" ALT="" STYLE="max-width:128px;">
		</A>
<?php elseif ( $Podatek && $Podatek->Tip == "PIC" ) : ?>
		<A HREF="../<?php echo $Podatek->Datoteka ?>" CLASS="fancybox" REL="lightbox" TARGET="_blank"><IMG SRC="../<?php echo dirname($Podatek->Datoteka); ?>/thumbs/<?php echo basename($Podatek->Datoteka); ?>" BORDER="0" ALT="" STYLE="max-width:128px;"></A>
<?php elseif ( $Podatek ) : ?>
		<IMG SRC="../pic/nislike.png" BORDER="0" ALT="" style="max-width:128px;">
<?php endif ?>
		</TD>
	</TR>
	<TR>
		<TD ALIGN="right"><B>Ime:</B>&nbsp;</TD>
		<TD COLSPAN="2"><INPUT TYPE="text" NAME="Naziv" MAXLENGTH="32" VALUE="<?php if ( $Podatek ) echo $Podatek->Naziv ?>" STYLE="width:100%;"></TD>
	</TR>
<?php if ( $Podatek ) : ?>
	<TR>
		<TD ALIGN="right">Datoteka:&nbsp;</TD>
		<TD VALIGN="top" COLSPAN="2">
			<INPUT TYPE="Text" VALUE="<?php if ( $Podatek ) echo $Podatek->Datoteka ?>" DISABLED STYLE="width:100%;">
		</TD>
	</TR>
	<TR>
		<TD ALIGN="right">Velikost:&nbsp;</TD>
		<TD>
			<INPUT TYPE="Text" SIZE="4" VALUE="<?php if ( $Podatek ) echo (int)($Podatek->Velikost/1024) ?>" DISABLED STYLE="text-align:right;"> kB
		</TD>
	<?php if ( $Podatek->Tip == "PIC" ) : ?>
		<TD ALIGN="right" CLASS="red f10">
		Obnovi ikono:<INPUT NAME="ObnoviSliko" TYPE="CheckBox" STYLE="border:none;">
		</TD>
	<?php elseif ( $Podatek->Slika != "" ) : ?>
		<TD ALIGN="right" CLASS="red">
		Briši ikono:<INPUT NAME="BrisiSliko" TYPE="CheckBox" STYLE="border:none;">
		</TD>
	<?php endif ?>
	</TR>
<?php else : ?>
	<TR>
		<TD COLSPAN="3" CLASS="f10"><B>Datoteka:</B><br>
		<INPUT TYPE="FILE" NAME="Dodaj" STYLE="border:none;"></TD>
	</TR>
	<TR>
		<TD COLSPAN="2" NOWRAP>Ikona:&nbsp;</TD>
	</TR>
<?php endif ?>
<?php if ( contains($ACL,"W") ) : ?>
	<TR>
		<TD COLSPAN="2" NOWRAP STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;">
		K:<INPUT TYPE="CheckBox" NAME="S" VALUE="Yes" <?php echo (int)$DefThumbSize<0 ? "CHECKED" : "" ?>>
		M:<INPUT TYPE="Text" NAME="T" SIZE="2" MAXLENGTH="3" VALUE="<?php echo abs((int)$DefThumbSize) ?>" TABINDEX="8" TITLE="Velikost male ikone v pikah. (48<x<128)">
		S:<INPUT TYPE="Text" NAME="R" SIZE="3" MAXLENGTH="4" VALUE="<?php echo abs((int)$DefPicSize) ?>" TABINDEX="9" TITLE="Maksimalna velikost slike v pikah. (320<x<1024)">
		</TD>
		<TD ALIGN="right" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="submit" VALUE=" Zapiši " CLASS="but"></TD>
	</TR>
<?php endif ?>
<?php if ( $Podatek && $Podatek->Tip == 'PIC' ) : ?>
	<TR>
		<TD COLSPAN="4" CLASS="f10">Metapodatki o sliki: <span class="gry">(ne spreminjaj, če nisi prepričan kaj delaš)</span><BR>
		<TEXTAREA NAME="Meta" STYLE="width:99%;height:64px;"><?php if ( $Podatek ) echo $Podatek->Meta ?></TEXTAREA>
		</TD>
	</TR>
<?php endif ?>
	</TABLE>
	</FORM>
	</DIV>
	</FIELDSET>
<?php if ( $Podatek && contains($ACL,"W") && $Podatek->Tip!='PIC' ) : ?>
	<FIELDSET>
		<LEGEND>Naloži&nbsp;ikono:</LEGEND>
		<FORM NAME="Datoteka" ACTION="upload_image.php?mid=<?php echo $_GET['ID'] ?>&p=media" METHOD="post" ENCTYPE="multipart/form-data">
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
		<TR>
			<TD><INPUT ID="fileupload" TYPE="FILE" NAME="file" STYLE="border:none;"><INPUT TYPE="Hidden" NAME="BrisiSliko" VALUE="1"></TD>
			<TD ALIGN="right"><INPUT TYPE="submit" VALUE=" Dodaj " CLASS="but"></TD>
		</TR>
		</TABLE>
		</FORM>
	</FIELDSET>
<?php endif ?>
	</TD>
	<TD VALIGN="top" WIDTH="50%">

<?php if ( $Podatek ) : ?>
	<FIELDSET ID="fldContent" style="min-height:200px;">
	<LEGEND ID="lgdContent">
	<?php if ( contains($ACL,"W") ) : ?>
		<A HREF="javascript:void(0);" ONCLICK="loadTo('Names','inc.php?Izbor=MediaOpis&MediaID=<?php echo $_GET['ID'] ?>&ID=0')" TITLE="Dodaj"><IMG SRC="pic/control.add_document.gif" ALIGN="absmiddle" WIDTH=14 HEIGHT=14 ALT="Dodaj" BORDER="0" CLASS="icon"></A>&nbsp;:
	<?php endif ?>
		Nazivi in opisi</LEGEND>
		<DIV ID="divNames" STYLE="overflow:none;">&nbsp;</DIV>
	</FIELDSET>
<?php endif ?>

	</TD>
</TR>
</TABLE>

<?php if ( $Podatek ) : ?>
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD VALIGN="top" WIDTH="50%">

	<FIELDSET>
		<LEGEND>
<?php if ( contains($ACL,"W") ) : ?>
		<div id="findBe" class="find" style="margin:0;">
		<form name="findFrmBe" action="inc.php?Izbor=MediaBesedila&MediaID=<?php echo $_GET['ID'] ?>" method="get">
		<input id="findTxtBe" type="Text" name="Find" maxlength="32" value=" Pripni v besedilo " style="color:#aaa;" onkeypress="$('#clrSkFind').show();">
		<a id="findClrBe" href="javascript:void(0);" onclick="$(this).hide();$('#findTxtBe').val('').select();"><img src="pic/list.clear.gif" border="0"></a>
		</form>
		</div>
<?php else : ?>
		Pripeto v besedila
<?php endif ?>
		</LEGEND>
		<DIV ID="divBe" STYLE="overflow:auto;"><img src="pic/control.spinner.gif" alt="Nalagam" border="0"> Nalagam ...</DIV>
	</FIELDSET>

	</TD>
	<TD VALIGN="top" WIDTH="50%">

	<FIELDSET>
		<LEGEND>Rubrike</LEGEND>
		<DIV ID="divRu" STYLE="overflow:auto;"><img src="pic/control.spinner.gif" alt="Nalagam" border="0"> Nalagam ...</DIV>
	</FIELDSET>

	</TD>
</TR>
</TABLE>
<?php endif ?>
