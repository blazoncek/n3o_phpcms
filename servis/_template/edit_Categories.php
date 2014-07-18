<?php
/*~ edit_Kategorije.php - Add/edit page structure (categories)
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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "00";

$Podatek = $db->get_row("SELECT * FROM Kategorije WHERE KategorijaID = '". $db->escape($_GET['ID']) ."'");
// get ACL
if ( $Podatek ) {
	$ACL = userACL($Podatek->ACLID);
} else
	$ACL = $ActionACL;

?>
<script language="JavaScript" type="text/javascript">
<!-- //
function customResize () {
	// vertically resize edit child divs
	edit = $("#divContent").height(0).height( $("#divEdit").height() + $("#divEdit").position().top - $("#divContent").position().top );
	$('#fldContent').height($('#fldData').height()+'px');
	// fix scroller problem when resizing
	if ( $("#divBe").text() ) $("#divBe").height(0);
	if ( $("#divMe").text() ) $("#divMe").height(0);
	// actualy resize
	if ( $("#divBe").text() ) $("#divBe").height( Math.max(96, edit.height() + edit.position().top - $("#divBe").position().top - 16) );
	if ( $("#divMe").text() ) $("#divMe").height( Math.max(96, edit.height() + edit.position().top - $("#divMe").position().top - 16) );
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
				if (empty(fObj.Ime) && empty(fObj.Naslov))	{alert("Please enter name!"); fObj.Ime.focus(); return false;}
				$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
				return true;
			} // pre-submit callback
		});
		return false;
	});
	$("form[name='Datoteka']").submit(function(){
		var html = $('#thumbImage').html();
		$('#thumbImage').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
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
		dropZone: $('#thumbImage,#divMe'),
		pasteZone: null,
		add: function(e, data) {
			// extract drop target from e.originalEvent.currentTarget.id
			// and set url acordingly
			switch ( e.originalEvent.currentTarget.id ) {
			  case 'fileupload' :
			  case 'thumbImage' :
				data.url = 'upload_image.php?kid=<?php echo $_GET['ID'] ?>&p=rubrike';
				data.context = $('#thumbImage').html('<div class="gry center"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</div>');
			  	break;
			  case 'divMe' :
				data.url = 'upload_file.php?kid=<?php echo $_GET['ID'] ?>';
				data.context = $('#divMe').html('<div class="gry center"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</div>');
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
			  case 'divMe' :
			  	data.context.load('inc.php?Izbor=catMedia&KategorijaID=<?php echo $_GET['ID'] ?>');
				break;
			}
        }
    });
	
	// resize content div
	window.customResize();
	
	// load subdata
	if ( $("#divLe").text() ) $("#divLe").load('inc.php?Izbor=catContent&KategorijaID=<?php echo $_GET['ID'] ?>&Ekstra=2');
	if ( $("#divCe").text() ) $("#divCe").load('inc.php?Izbor=catContent&KategorijaID=<?php echo $_GET['ID'] ?>&Ekstra=0');
	if ( $("#divDe").text() ) $("#divDe").load('inc.php?Izbor=catContent&KategorijaID=<?php echo $_GET['ID'] ?>&Ekstra=1');
	if ( $("#divBe").text() ) $("#divBe").load('inc.php?Izbor=catTexts&KategorijaID=<?php echo $_GET['ID'] ?>');
	if ( $("#divMe").text() ) $("#divMe").load('inc.php?Izbor=catMedia&KategorijaID=<?php echo $_GET['ID'] ?>');

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

	// search fields
	$("form[name^=findFrm]").submit(function(){
		$(this).ajaxSubmit({
			target: '#div'+$(this).attr('name').substr(7,2)
		});
		return false;
	});
	$('input[id^=findTxt]').click(function(){
		$('#findClr'+$(this).attr('id').substr(7,2)).show();
		if ( $(this).val().substr(0,1) == " " && $(this).val().substr($(this).val().length-1,1) == " " ) {
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
	<TD ALIGN="left" VALIGN="top" WIDTH="50%">

	<FIELDSET ID="fldData" style="min-height:148px;">
		<LEGEND ID="lgdData">
<?php if ( contains($ACL,"W") && $Podatek ) {
		echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=sysACL&ACL=".$Action->Action;
		echo "&KategorijaID=". $_GET['ID'] . ($Podatek->ACLID!="" ? "&ID=".$Podatek->ACLID: "") ."')\" TITLE=\"Edit permissions\">";
		echo "<IMG SRC=\"pic/control.permissions.gif\" HEIGHT=\"16\" WIDTH=\"16\" BORDER=0 ALT=\"Permissions\" ALIGN=\"absmiddle\"></A>&nbsp;:";
} ?>
			Basic&nbsp;information
		</LEGEND>

		<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post" ENCTYPE="multipart/form-data">
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
		<TR>
			<TD ALIGN="right"><FONT COLOR="Red"><B>Show:</B></FONT>&nbsp;</TD>
			<TD NOWRAP><INPUT TYPE="Checkbox" NAME="Izpis"<?php echo ($Podatek && $Podatek->Izpis)? " CHECKED": "" ?> TABINDEX="1"></TD>
			<TD ALIGN="right" ID="thumbImage" ROWSPAN="<?php echo ($Podatek && $Podatek->Slika!="") ? "5" : "4" ?>" VALIGN="top" WIDTH="130" HEIGHT="130">
			<?php if ($Podatek && $Podatek->Slika != "") : ?>
				<A HREF="../media/rubrike/<?php echo $Podatek->Slika; ?>" CLASS="fancybox" REL="lightbox" TARGET="_blank">
				<IMG SRC="../media/rubrike/<?php echo $Podatek->Slika ?>" BORDER="0" ALT="" STYLE="max-width:128px;">
				</A>
			<?php else : ?>
				<IMG SRC="../pic/nislike.png" BORDER="0" ALT="" hspace="0" vspace="0" style="max-width:128px;">
			<?php endif ?>
			</TD>
		</TR>
		<TR>
			<TD ALIGN="right"><B>Search&nbsp;enabled:</B>&nbsp;</TD>
			<TD><INPUT TYPE="Checkbox" NAME="Iskanje"<?php echo ($Podatek && $Podatek->Iskanje) ? " CHECKED" : "" ?> TABINDEX="2"></TD>
		</TR>
	<?php if ( $Podatek && $Podatek->Slika != "" ) : ?>
		<TR>
			<TD ALIGN="right"><FONT COLOR="red">Delete image:</FONT>&nbsp;</TD>
			<TD><INPUT TYPE="Checkbox" NAME="BrisiSliko" TABINDEX="5" STYLE="border:none;background-color:red;"></TD>
		</TR>
	<?php endif ?>
		<TR>
			<TD ALIGN="right"><B>Name:</B>&nbsp;</TD>
			<TD><INPUT TYPE="text" NAME="Ime" MAXLENGTH="32" VALUE="<?php echo ($Podatek ? $Podatek->Ime : "") ?>" TABINDEX="3" STYLE="width:100%;"></TD>
		</TR>
	<?php if ( contains($ACL,"W") ) : ?>
		<TR>
			<TD STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><?php if ( $Podatek ) : ?><A HREF="javascript:void(0);" ONCLICK="window.open('vnos.php?Izbor=catMove&KategorijaID=<?php echo $_GET['ID'] ?>','mainscreen','scrollbars=no,status=no,menubar=no,toolbar=no,resizable=no,WIDTH=480,HEIGHT=480')" TITLE="Move"><IMG SRC="pic/icon.sitemap.png" WIDTH="16" HEIGHT="16" ALT="Move" BORDER="0"></A><?php endif ?></TD>
			<TD ALIGN="right" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="submit" VALUE=" Save " CLASS="but" TABINDEX="6"></TD>
		</TR>
	<?php endif ?>
		</TABLE>
		</FORM>
	</FIELDSET>
<?php if ( $Podatek && contains($ACL,"W") ) : ?>
	<FIELDSET>
		<LEGEND>Upload&nbsp;image</LEGEND>
		<FORM NAME="Datoteka" ACTION="upload_image.php?kid=<?php echo $_GET['ID'] ?>&p=rubrike" METHOD="post" ENCTYPE="multipart/form-data">
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
		<TR>
			<TD><INPUT ID="fileupload" TYPE="FILE" NAME="file" STYLE="border:none;"></TD>
			<TD ALIGN="right"><INPUT TYPE="submit" VALUE=" Add " CLASS="but"></TD>
		</TR>
		</TABLE>
		</FORM>
	</FIELDSET>
<?php endif ?>
	</TD>

	<TD VALIGN="top" WIDTH="50%">
<?php if ( $Podatek ) : ?>
	<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
	<!--
	function checkLang(ID, Naziv) {
		if (confirm("Do you want to delete '"+Naziv+"'?"))
			loadTo('Edit','edit.php?Action=<?php echo $Action->ActionID ?>&ID=<?php echo $_GET['ID'] ?>&BrisiOpis='+ID);
		return false;
	}
	//-->
	</SCRIPT>

	<FIELDSET ID="fldContent" style="min-height:196px;">
		<LEGEND ID="lgdContent">
<?php if ( contains($ACL,"W") ) : ?>
		<A HREF="javascript:void(0);" ONCLICK="loadTo('Edit','inc.php?Izbor=catDescription&Action=<?php echo $Action->ActionID ?>&KategorijaID=<?php echo $_GET['ID'] ?>')" TITLE="Add"><IMG SRC="pic/control.add_document.gif" ALIGN="absmiddle" WIDTH=14 HEIGHT=14 ALT="Add" BORDER="0" CLASS="icon"></A>&nbsp;:
<?php endif ?>
		Titles &amp; descriptions</LEGEND>
<?php
		$List = $db->get_results("SELECT ID, Naziv, Jezik FROM KategorijeNazivi WHERE KategorijaID='". $db->escape($_GET['ID']) ."' ORDER BY Jezik");
		echo "<TABLE BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
		if ( !$List ) 
			echo "<TR><TD ALIGN=\"center\">No content!</TD></TR>\n";
		else {
			$CurrentRow = 1;
			$RecordCount = count($List);
			foreach ( $List as $Item ) {
				echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
				echo "<TD width=\"8%\">[<b class=\"red\">".($Item->Jezik ? $Item->Jezik : "all")."</b>]</TD>\n";
				echo "<TD><A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','inc.php?Izbor=catDescription&Action=".$Action->ActionID."&KategorijaID=".$_GET['ID']."&ID=".$Item->ID."');\"><B>$Item->Naziv</B></A></TD>\n";
				echo "<TD ALIGN=\"right\" NOWRAP>\n";
				if ( contains($ACL,"W") ) {
					echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"javascript:checkLang('$Item->ID','$Item->Naziv');\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" CLASS=\"icon\">\n";
				}
				echo "</TD>\n";
				echo "</TR>\n";
				$CurrentRow++;
			}
		}
		echo "</TABLE>\n";
?>
	</FIELDSET>
<?php endif ?>
	</TD>
</TR>
</TABLE>

<?php if ( $Podatek ) : ?>
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD VALIGN="top" WIDTH="33%">

	<FIELDSET>
		<LEGEND>
<?php if ( contains($ACL,"W") ) : ?>
		<div id="findLe" class="find" style="margin:0;">
		<form name="findFrmLe" action="inc.php?Izbor=catContent&KategorijaID=<?php echo $_GET['ID'] ?>&Ekstra=2" method="get">
		<input id="findTxtLe" type="Text" name="Find" maxlength="32" value=" Template " style="color:#aaa;">
		<a id="findClrLe" href="javascript:void(0);"><img src="pic/list.clear.gif" border="0"></a>
		</form>
		</div>
<?php else : ?>
		Menu (left)
<?php endif ?>
		</LEGEND>
		<DIV ID="divLe" STYLE="overflow:auto;height:12em;"><img src="pic/control.spinner.gif" alt="Loading" border="0"> Loading ...</DIV>
	</FIELDSET>

	</TD>
	<TD VALIGN="top" WIDTH="34%">

	<FIELDSET>
		<LEGEND>
<?php if ( contains($ACL,"W") ) : ?>
		<div id="findCe" class="find" style="margin:0;">
		<form name="findFrmCe" action="inc.php?Izbor=catContent&KategorijaID=<?php echo $_GET['ID'] ?>&Ekstra=0" method="get">
		<input id="findTxtCe" type="Text" name="Find" maxlength="32" value=" Template " style="color:#aaa;">
		<a id="findClrCe" href="javascript:void(0);"><img src="pic/list.clear.gif" border="0"></a>
		</form>
		</div>
<?php else : ?>
		Content templates
<?php endif ?>
		</LEGEND>
		<DIV ID="divCe" STYLE="overflow:auto;height:12em;"><img src="pic/control.spinner.gif" alt="Loading" border="0"> Loading ...</DIV>
	</FIELDSET>

	</TD>
	<TD VALIGN="top" WIDTH="33%">

	<FIELDSET>
		<LEGEND>
<?php if ( contains($ACL,"W") ) : ?>
		<div id="findDe" class="find" style="margin:0;">
		<form name="findFrmDe" action="inc.php?Izbor=catContent&KategorijaID=<?php echo $_GET['ID'] ?>&Ekstra=1" method="get">
		<input id="findTxtDe" type="Text" name="Find" maxlength="32" value=" Template " style="color:#aaa;">
		<a id="findClrDe" href="javascript:void(0);"><img src="pic/list.clear.gif" border="0"></a>
		</form>
		</div>
<?php else : ?>
		Extra (right)
<?php endif ?>
		</LEGEND>
		<DIV ID="divDe" STYLE="overflow:auto;height:12em;"><img src="pic/control.spinner.gif" alt="Loading" border="0"> Loading ...</DIV>
	</FIELDSET>

	</TD>
</TR>
</TABLE>

<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD VALIGN="top" WIDTH="50%">

	<FIELDSET>
		<LEGEND>
<?php if ( contains($ACL,"W") ) : ?>
		<div id="findBe" class="find" style="margin:0;">
		<form name="findFrmBe" action="inc.php?Izbor=catTexts&KategorijaID=<?php echo $_GET['ID'] ?>" method="get">
		<input id="findTxtBe" type="Text" name="Find" maxlength="32" value=" Texts " style="color:#aaa;">
		<a id="findClrBe" href="javascript:void(0);"><img src="pic/list.clear.gif" border="0"></a>
		</form>
		</div>
<?php else : ?>
		Content texts
<?php endif ?>
		</LEGEND>
		<DIV ID="divBe" STYLE="overflow:auto;"><img src="pic/control.spinner.gif" alt="Loading" border="0"> Loading ...</DIV>
	</FIELDSET>

	</TD>
	<TD VALIGN="top" WIDTH="50%">

	<FIELDSET>
		<LEGEND>
<?php if ( contains($ACL,"W") ) : ?>
		<div id="findMe" class="find" style="margin:0;">
		<form name="findFrmMe" action="inc.php?Izbor=catMedia&KategorijaID=<?php echo $_GET['ID'] ?>" method="get">
		<input id="findTxtMe" type="Text" name="Find" maxlength="32" value=" Attachments " style="color:#aaa;">
		<a id="findClrMe" href="javascript:void(0);"><img src="pic/list.clear.gif" border="0"></a>
		</form>
		</div>
<?php else : ?>
		Attachments
<?php endif ?>
		</LEGEND>
		<DIV ID="divMe" STYLE="overflow:auto;"><img src="pic/control.spinner.gif" alt="Loading" border="0"> Loading ...</DIV>
	</FIELDSET>

	</TD>
</TR>
</TABLE>
<?php endif ?>
