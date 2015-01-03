<?php
/*~ edit_Besedila.php - text metadata editing
.---------------------------------------------------------------------------.
|  Software: N3O CMS                                                        |
|   Version: 2.2.2                                                          |
|   Contact: contact author (also http://blaz.at/home)                      |
| ------------------------------------------------------------------------- |
|    Author: Blaž Kristan (blaz@kristan-sp.si)                              |
| Copyright (c) 2007-2014, Blaž Kristan. All Rights Reserved.               |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (LGPL)     |
|            http://www.gnu.org/copyleft/lesser.html                        |
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
'---------------------------------------------------------------------------'
*/

if ( !isset($_GET['ID']) ) $_GET['ID'] = "0";
if ( !isset($_GET['Tip']) || $_GET['Tip'] == "" ) $_GET['Tip'] = "Text";

$Podatek = $db->get_row("SELECT * FROM Besedila WHERE BesediloID = ". (int)$_GET['ID']);
// get ACL
if ( $Podatek ) {
	$ACL = userACL($Podatek->ACLID);
	$_GET['Tip'] = $Podatek->Tip;
} else
	$ACL = $ActionACL;

// image upload parameters (default)
$GalleryBase  = "";
$DefPicSize   = 640;
$DefThumbSize = 64;
$MaxPicSize   = 1024;

// image gallery defaults
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
		ST.Jezik DESC
	LIMIT 1"
);
$GalleryBase  = ($x && $x->GalleryBase!='')   ? $x->GalleryBase       : $GalleryBase;
$DefPicSize   = ($x && (int)$x->DefPicSize)   ? (int)$x->DefPicSize   : $DefPicSize;
$DefThumbSize = ($x && (int)$x->DefThumbSize) ? (int)$x->DefThumbSize : $DefThumbSize;
$MaxPicSize   = ($x && (int)$x->MaxPicSize)   ? (int)$x->MaxPicSize   : $MaxPicSize;
// text type specific defaults
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
		AND S.SifrText = '". $db->escape($_GET['Tip']) ."'
	ORDER BY
		ST.Jezik DESC
	LIMIT 1"
);
$TxtImageBase = ($x && $x->GalleryBase!='')   ? $x->GalleryBase       : $GalleryBase;
$TxtPicSize   = ($x && (int)$x->DefPicSize)   ? (int)$x->DefPicSize   : $DefPicSize;
$TxtThumbSize = ($x && (int)$x->DefThumbSize) ? (int)$x->DefThumbSize : $DefThumbSize;
$TxtMaxPSize  = ($x && (int)$x->MaxPicSize)   ? (int)$x->MaxPicSize   : $MaxPicSize;

setcookie("img_upload","1");
setcookie("img_path", $TxtImageBase);

?>
<script language="JavaScript" type="text/javascript">
<!-- //
window.thumbSize = <?php echo $TxtThumbSize ?>;
window.imageSize = <?php echo $TxtPicSize ?>;
window.idDocument = <?php echo $_GET['ID'] ?>;

function changeTip(sObj) {
	loadTo('Edit','edit.php?Action=<?php echo $_GET['Action'] ?>&ID=<?php echo $_GET['ID'] ?><?php echo (isset($_GET['KategorijaID'])? "&KategorijaID=".$_GET['KategorijaID']: "") ?>&Tip='+sObj.options[sObj.selectedIndex].value);
}

function customResize () {
	// vertically resize edit child divs
	edit = $("#divContent").height(0).height( $("#divEdit").height() + $("#divEdit").position().top - $("#divContent").position().top );
	// fix scroller problem when resizing
	if ( $("#divSlike").html() ) $("#divSlike").height(0);
	if ( $("#divSk").html() )    $("#divSk").height(0);
	if ( $("#divMe").html() )    $("#divMe").height(0);
	// actualy resize
	if ( $("#divSlike").html() ) $("#divSlike").height( edit.height() + edit.position().top - $("#divSlike").position().top - 16 );
	if ( $("#divSk").html() )    $("#divSk").height( edit.height() + edit.position().top - $("#divSk").position().top - 16 );
	if ( $("#divMe").html() )    $("#divMe").height( edit.height() + edit.position().top - $("#divMe").position().top - 16 );
	// resize editing box
	if ( $("#HTMLeditor").parent().html() ) {
		$("#HTMLeditor").height(0); // fix for TD resizing
		$("#HTMLeditor_ifr").height(0); // fix for TD resizing
		$("#HTMLeditor").parent().height(0).height( edit.height() + edit.position().top - $("#HTMLeditor").parent().position().top - 16 );
		$("#HTMLeditor").height( $("#HTMLeditor").parent().innerHeight() - 9 );
		$("#HTMLeditor_ifr").height( $("#HTMLeditor").height() - $("#HTMLeditor_toolbargroup").height() - $("#HTMLeditor_path_row").height() );
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
		if ( this.Opis ) this.Opis.value = $("textarea[name='Opis']").html();
		$(this).ajaxSubmit({
			target: '#divEdit',
			iframe: false, // fix for listRefresh
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (fObj.Tip.selectedIndex && fObj.Tip.selectedIndex==0)	{alert("Select text type!"); fObj.Tip.focus(); return false;}
				if (empty(fObj.Ime) && empty(fObj.Naslov))	{alert("Enter name or title!"); fObj.Ime.focus(); return false;}
				if (empty(fObj.Ime))	{fObj.Ime.value = fObj.Naslov.value.substring(0,128); fObj.Ime.focus(); fObj.Ime.select(); return false;}
				if (fObj.Naslov && empty(fObj.Naslov))	{fObj.Naslov.value = fObj.Ime.value; fObj.Naslov.focus(); fObj.Naslov.select(); return false;}
				if (fObj.ForumTopicID && !empty(fObj.ForumTopicID) && !IsNumeric(fObj.ForumTopicID)) {alert("Invalid topic!"); fObj.ForumTopicID.value=""; fObj.ForumTopicID.focus();return false;}
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
		dropZone: $('#thumbImage,#divSlike,#divMe'),
		pasteZone: null,
		add: function(e, data) {
			// extract drop target from e.originalEvent.currentTarget.id
			// and set url acordingly
			switch ( e.originalEvent.currentTarget.id ) {
			  case 'divSlike' :
				data.url = 'upload_image.php?gid=<?php echo $_GET['ID'] ?>&p=<?php echo $GalleryBase ."/". date("Y") ?>&t=<?php echo $DefThumbSize ?>&s=<?php echo $DefPicSize ?>&sq=on';
				data.context = $('#divSlike').html('<div class="gry center"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</div>');
				break;
			  case 'thumbImage' :
				data.url = 'upload_image.php?id=<?php echo $_GET['ID'] ?>&p=besedila';
				data.context = $('#thumbImage').html('<div class="gry center"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</div>');
			  	break;
			  case 'divMe' :
				data.url = 'upload_file.php?bid=<?php echo $_GET['ID'] ?>';
				data.context = $('#divMe').html('<div class="gry center"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</div>');
				break;
			}
			data.submit();
		},
        done: function (e, data) {
			switch ( data.context.attr("id") ) {
			  case 'divSlike' :
			  	data.context.load('inc.php?Izbor=txtGallery&BesediloID=<?php echo $_GET['ID'] ?>');
				break;
			  case 'divMe' :
			  	data.context.load('inc.php?Izbor=txtMedia&BesediloID=<?php echo $_GET['ID'] ?>');
				break;
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

	// setup tabs (before resizing divs)
	$("#tabs").tabs({
		//event: 'mouseover'
	});
	// select default language
	if ( $("#tabs").html() ) $("#tabs").tabs('select','<?php echo $db->get_var("SELECT Jezik FROM Jeziki WHERE DefLang=1") ?>');

	// set popup calendar
	var options = {
		dateFormat: 'd.m.yy',
		firstDay: 1,
		//changeMonth: true,
		//changeYear: true,
		//dayNamesMin: ['Ne','Po','To','Sr','Če','Pe','So'],
		//monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun','Jul','Avg','Sep','Okt','Nov','Dec'],
		//monthNames: ['Januar','Februar','Marec','April','Maj','Junij','Julij','Avgust','September','Oktober','November','December'],
		duration: ''
	};
	$("input[name='Datum']").datepicker(options);

	// enable TinyMCE
	$("textarea[name='Opis']").tinymce({
//		setup : function(ed) {
//			ed.onLoadContent.add(function(ed, o) {
//				...
//			});
//		},
		script_url : '<?php echo $js ?>/tiny_mce/tiny_mce.js',
		mode : "exact",
		//language : "si",
		elements : "HTMLeditor",
		element_format : "html",
		theme : "advanced",
		content_css : "editor_css.php",
		plugins : "inlinepopups,safari,table,advimage,advhr,contextmenu",
		auto_cleanup_word : true,
		extended_valid_elements : "a[href|target|title],img[src|border=0|alt|class|hspace|vspace|width|height|align|style],hr[size|noshade],font[face|size|color|style],div[class|align|style],span[class|style],ol[type],ul[type]",
		invalid_elements : "iframe,layer,script,link",
		file_browser_callback : "fileBrowserCallBack",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "none",
		theme_advanced_buttons1 : "bold,italic,underline,sub,sup,separator,bullist,numlist,outdent,indent,blockquote,separator",
		theme_advanced_buttons1_add : "justifyleft,justifycenter,justifyright,justifyfull,separator,advhr,separator,table,link,unlink,separator,code",
		theme_advanced_buttons2 : "styleselect,formatselect,fontselect,fontsizeselect,forecolor,backcolor,separator,removeformat",
		theme_advanced_buttons3 : "",
		theme_advanced_styles : "Koda=code;Citat=quote;Slika=imgcenter;Slika (levo)=imgleft;Slika (desno)=imgright"
	});

	// load subdata
	if ( $("#divSlike").text() ) $("#divSlike").load('inc.php?Izbor=txtGallery&BesediloID=<?php echo $_GET['ID'] ?>');
	if ( $("#divSk").text() )    $("#divSk").load('inc.php?Izbor=txtRelated&BesediloID=<?php echo $_GET['ID'] ?>');
	if ( $("#divMe").text() )    $("#divMe").load('inc.php?Izbor=txtMedia&BesediloID=<?php echo $_GET['ID'] ?>');
	if ( $("#rubrike").text() )  $("#rubrike").load('inc.php?Izbor=txtCategories&BesediloID=<?php echo $_GET['ID'] ?>');
	if ( $("#tags").text() )     $("#tags").load('inc.php?Izbor=txtTags&BesediloID=<?php echo $_GET['ID'] ?>');

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

	// resize elements
	window.customResize();
});
//-->
</script>

<?php if ( !$Podatek ) : ?>

<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post" ENCTYPE="multipart/form-data">
<INPUT NAME="ForumTopicID" VALUE="" TYPE="Hidden">
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD ALIGN="right" width="10%"><FONT COLOR="red"><B>Show:</B></FONT>&nbsp;</TD>
	<TD width="10%"><INPUT TYPE="Checkbox" NAME="Izpis" VALUE="yes" TABINDEX="1"></TD>
	<TD ALIGN="right" NOWRAP width="10%"><B>Type:</B>&nbsp;</TD>
	<TD width="10%"><SELECT NAME="Tip" SIZE="1" TABINDEX="2" ONCHANGE="changeTip(this);">
	<OPTION VALUE="" DISABLED STYLE="background-color:whitesmoke;">Select...</OPTION>
<?php
	$Tipi = $db->get_col("SELECT SifrText FROM Sifranti WHERE SifrCtrl='BESE' ORDER BY SifrCtrl, SifrZapo");
	if ( $Tipi ) foreach ( $Tipi as $Tip )
		echo "<OPTION VALUE=\"$Tip\"".(($_GET['Tip']==$Tip)? " SELECTED STYLE=\"background-color: #99CCFF;\"": "").">$Tip</OPTION>\n";
?>
	</SELECT>
	</TD>
	<TD ALIGN="right" width="10%"><B>Name:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="Ime" MAXLENGTH="127" VALUE="" STYLE="width:100%;" TABINDEX="3"></TD>
	<TD ALIGN="right"><?php if ( contains($ActionACL,"W") ) : ?><INPUT TYPE="submit" VALUE=" Save " CLASS="but"><?php endif ?></TD>
</TR>
<TR>
	<TD ALIGN="right"><B>Date:</B>&nbsp;</TD>
	<?php $Datum = date( "j.n.Y", time() ); ?>
	<TD><INPUT TYPE="Text" NAME="Datum" SIZE="10" MAXLENGTH="10" VALUE="<?php echo $Datum ?>" CLASS="txt" TABINDEX="4"></TD>
	<TD ALIGN="right" NOWRAP><B>Category:</B>&nbsp;</TD>
	<TD><SELECT NAME="KategorijaID" SIZE="1" TABINDEX="5">
	<OPTION VALUE="" DISABLED STYLE="background-color:whitesmoke;">Select...</OPTION>
<?php
	$Kategorije = $db->get_results("SELECT KategorijaID AS ID, Ime, Izpis FROM Kategorije ORDER BY KategorijaID");
	if ( $Kategorije ) foreach ( $Kategorije as $Kat ) {
		echo "<OPTION VALUE=\"$Kat->ID\"".( isset($_GET['KategorijaID']) && $_GET['KategorijaID']==$Kat->ID? " SELECTED": "" ).">";
		echo str_repeat("&nbsp;", strlen($Kat->ID)-2) . $Kat->Ime;
		echo (!$Kat->Izpis ? "*" : "") . "</OPTION>\n";
	}
?>
	</SELECT>
	</TD>
	<TD ALIGN="right">URL:&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="URL" MAXLENGTH="128" VALUE="" STYLE="width:100%;" TABINDEX="6"></TD>
</TR>
<TR>
	<TD COLSPAN="7"><HR SIZE="1" NOSHADE></TD>
</TR>
<TR>
	<TD NOWRAP><B>Title:</B>&nbsp;</TD>
	<TD colspan="5"><INPUT TYPE="text" NAME="Naslov" MAXLENGTH="128" VALUE="" STYLE="width:100%;" TABINDEX="7"></TD>
	<TD ALIGN="right" nowrap>Language:
	<SELECT NAME="Jezik" SIZE="1" TABINDEX="8">
		<OPTION VALUE="">- all -</OPTION>
<?php
	$Jeziki = $db->get_results("SELECT Jezik, Opis, DefLang FROM Jeziki WHERE Enabled = 1");
	if ( $Jeziki ) foreach ( $Jeziki as $Jezik )
		echo "<OPTION VALUE=\"$Jezik->Jezik\"".($Jezik->DefLang ? " SELECTED" : "").">$Jezik->Opis</OPTION>\n";
?>
	</SELECT>
	</TD>
</TR>
<TR>
	<TD NOWRAP><SPAN CLASS="f10">Subtitle:&nbsp;<BR>Abstract:</SPAN></TD>
	<TD colspan="6"><INPUT TYPE="text" NAME="Podnaslov" MAXLENGTH="128" VALUE="" STYLE="width:99%;" TABINDEX="9"></TD>
</TR>
<TR>
	<TD COLSPAN="7" VALIGN="top"><TEXTAREA NAME="Povzetek" ROWS="3" STYLE="width:99%;" TABINDEX="10"></TEXTAREA></TD>
</TR>
<TR>
	<TD COLSPAN="7"><B>Description:</B> <SPAN CLASS="f10 gry">(Copy/Paste from Word is not recommended)</SPAN></TD>
</TR>
<TR>
	<TD COLSPAN="7" VALIGN="top"><TEXTAREA NAME="Opis" ID="HTMLeditor" TABINDEX="11" STYLE="width:99%;"></TEXTAREA></TD>
</TR>
</TABLE>
</FORM>

<?php else : ?>

<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD VALIGN="top" WIDTH="390">

	<FIELDSET ID="fldData" style="height:144px;">
		<LEGEND ID="lgdData">
<?php if ( contains($ACL,"W") ) {
		echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=sysACL&ACL=".$Action->Action;
		echo "&BesediloID=" . $_GET['ID'] . (($Podatek->ACLID!="")? "&ID=".$Podatek->ACLID: "") . "')\" TITLE=\"Edit permissions\">";
		echo "<IMG SRC=\"pic/control.permissions.gif\" HEIGHT=\"16\" WIDTH=\"16\" BORDER=0 ALT=\"Permissions\" ALIGN=\"absmiddle\"></A>&nbsp;:";
} ?>
			Basic&nbsp;information</LEGEND>

	<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post" ENCTYPE="multipart/form-data">
<?php if ( isset($_GET['KategorijaID']) ) : ?>
	<INPUT NAME="KategorijaID" VALUE="<?php echo $_GET['KategorijaID'] ?>" TYPE="Hidden">
<?php endif ?>
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="100%">
	<TR>
		<TD ALIGN="right"><FONT COLOR="red"><B>Show:</B></FONT>&nbsp;</TD>
		<TD><INPUT TYPE="Checkbox" NAME="Izpis" VALUE="yes"<?php echo ($Podatek && $Podatek->Izpis)? " CHECKED": "" ?> TABINDEX="1"></TD>
		<TD ALIGN="right">
		Type: <b class="red"><?php echo $Podatek->Tip ?></b><INPUT NAME="Tip" VALUE="<?php echo $Podatek->Tip ?>" TYPE="Hidden">
		</TD>
		<TD ALIGN="right" ROWSPAN="5" VALIGN="top" HEIGHT="130" WIDTH="130" id="thumbImage">
	<?php if ( $Podatek->Slika != "" ) : ?>
		<A HREF="../media/besedila/<?php echo $Podatek->Slika; ?>" CLASS="fancybox" REL="lightbox" TARGET="_blank">
		<IMG SRC="../media/besedila/<?php echo $Podatek->Slika ?>" BORDER="0" ALT="" STYLE="max-width:128px;max-height:128px;">
		</A>
	<?php else : ?>
		<IMG SRC="../pic/nislike.png" BORDER="0" ALT="" hspace="0" vspace="0" style="max-width:128px;">
	<?php endif ?>
		</TD>
	</TR>
	<TR>
		<TD ALIGN="right"><B>Date:</B>&nbsp;</TD>
		<?php $Datum = date( "j.n.Y", $Podatek? sqldate2time($Podatek->Datum): time() ); ?>
		<TD><INPUT TYPE="Text" NAME="Datum" SIZE="10" MAXLENGTH="10" VALUE="<?php echo $Datum ?>" CLASS="txt" TABINDEX="3"></TD>
		<?php
		// if text can have comments (SifLVal1=1)
		$Comments = $db->get_var("SELECT SifLVal1 FROM Sifranti WHERE SifrCtrl = 'BESE' AND SifrText = '".$Podatek->Tip."'");
		?>
		<TD ALIGN="right" nowrap>Topic:
		<INPUT NAME="ForumTopicID" TYPE="Text" VALUE="<?php if ( $Podatek ) echo $Podatek->ForumTopicID ?>" SIZE="3" MAXLENGTH="4" READONLY>
		<?php if ( $Comments && $Podatek->ForumTopicID == "" ) : ?><A HREF="javascript:void(0);" ONCLICK="window.open('vnos.php?Izbor=txtForum&ID=<?php echo $_GET['ID'] ?>&New=<?php echo urlencode($Podatek->Ime) ?>','listscreen','scrollbars=no,status=no,menubar=no,toolbar=no,resizable=no,width=400,height=400')" TITLE="Add"><IMG SRC="pic/control.plus.gif" HEIGHT="14" WIDTH="14" BORDER=0 ALT="Add" ALIGN="absmiddle"></A><?php endif ?>
		</TD>
	</TR>
	<TR>
		<TD ALIGN="right"><B>Name:</B>&nbsp;</TD>
		<TD COLSPAN="2"><INPUT TYPE="text" NAME="Ime" MAXLENGTH="32" VALUE="<?php if ( $Podatek ) echo $Podatek->Ime ?>" STYLE="width:100%;" TABINDEX="4"></TD>
	</TR>
	<TR>
		<TD ALIGN="right">URL:&nbsp;</TD>
		<TD COLSPAN="2"><INPUT TYPE="text" NAME="URL" MAXLENGTH="128" VALUE="<?php if ( $Podatek ) echo $Podatek->URL ?>" STYLE="width:100%;" TABINDEX="5"></TD>
	</TR>
<?php if ( $Podatek && contains($ACL,"W") ) : ?>
	<TR>
		<TD ALIGN="right" STYLE="margin-top:3px;padding-top:5px;border-top:silver solid 1px;">
		<?php if ( $Podatek && $Podatek->Slika!="" ) : ?><SPAN class="red">Delete icon:</SPAN><?php endif ?>
		</TD>
		<TD STYLE="margin-top:3px;padding-top:5px;border-top:silver solid 1px;">
		<?php if ( $Podatek && $Podatek->Slika!="" ) : ?><INPUT TYPE="Checkbox" NAME="BrisiSliko" STYLE="border:solid 1px red;background-color:red;"><?php endif ?>
		</TD>
		<TD ALIGN="right" STYLE="margin-top:3px;padding-top:5px;border-top:silver solid 1px;">
		<INPUT TYPE="submit" VALUE=" Save " CLASS="but">
		</TD>
	</TR>
<?php endif ?>
	</TABLE>
	</FORM>
	</FIELDSET>
<?php if ( $Podatek && contains($ACL,"W") ) : ?>
	<FIELDSET>
		<LEGEND>Upload&nbsp;image:</LEGEND>
		<FORM NAME="Datoteka" ACTION="upload_image.php?id=<?php echo $_GET['ID'] ?>&p=besedila" METHOD="post" ENCTYPE="multipart/form-data">
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
	<TD VALIGN="top">

	<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
	<!--
	function checkLang(ID, Naziv) {
		if (confirm("Do you want to delete '"+Naziv+"'?"))
			setTimeout("loadTo('Edit','edit.php?Action=$Action->ActionID&ID=<?php echo $_GET['ID'] ?>&BrisiOpis="+ID+"');",100);
		return false;
	}
	//-->
	</SCRIPT>

	<DIV ID="tabs">
		<ul>
<?php
		$Kategorija = $db->get_var("SELECT KategorijaID FROM KategorijeBesedila WHERE BesediloID=". (int)$_GET['ID'] ." LIMIT 1");
		$Jeziki = $db->get_results("SELECT Jezik, Opis FROM Jeziki WHERE Enabled=1");

		if ( count($Jeziki) > 1 )
			echo "<li><a href=\"#all\">All</a></li>\n";

		if ( $Jeziki ) foreach ( $Jeziki as $Jezik )
			echo "<li><a href=\"#$Jezik->Jezik\">$Jezik->Jezik</a></li>\n";
?>
			<li><a href="#rubrike">Categories</a></li>
			<li><a href="#tags">Tags</a></li>
		</ul>

<?php
	if ( count($Jeziki) > 1 ) {
		$List = $db->get_results(
			"SELECT ID, Jezik, Naslov, Polozaj ".
			"FROM BesedilaOpisi ".
			"WHERE BesediloID = ".(int)$_GET['ID'].
			"	AND Jezik IS NULL ".
			"ORDER BY Jezik, Polozaj"
		);
		echo "<div id=\"all\" style=\"overflow:auto;\">\n";
		echo "<TABLE BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
		if ( contains($ACL,"W") ) {
			echo "<TR>\n";
			echo "<TD CLASS=\"novo\" STYLE=\"border-bottom:darkgray solid 1px;\">\n";
			echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','inc.php?Izbor=txtText&Jezik=&BesediloID=".$_GET['ID']."');\">New...</A>\n";
			echo "</TD>\n";
			echo "<TD ALIGN=\"right\" STYLE=\"border-bottom:darkgray solid 1px;\">\n";
			echo "<A HREF=\"". $WebURL ."/?kat=". $Kategorija ."&ID=". (int)$_GET['ID'] ."\" TARGET=\"_blank\" TITLE=\"Predogled\"><IMG SRC=\"pic/list.extern.gif\" WIDTH=11 HEIGHT=11 ALT=\"Predogled\" BORDER=\"0\" CLASS=\"icon\"></A>\n";
			echo "</TD>\n";
			echo "</TR>\n";
		}
		if ( !$List )
			echo "<TR><TD ALIGN=\"center\">No content!</TD></TR>\n";
		else {
			$CurrentRow = 1;
			$RecordCount = count($List);
			foreach ( $List as $Item ) {
				echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
				echo "<TD><A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','inc.php?Izbor=txtText&Jezik=&BesediloID=".$_GET['ID']."&ID=$Item->ID');\"><B>$Item->Naslov</B></A></TD>\n";
				echo "<TD ALIGN=\"right\" NOWRAP>\n";
				// move items up/down
				if ( contains($ACL,"W") ) {
					if ( $CurrentRow > 1 )
						echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Action=$Action->ActionID&ID=".$_GET['ID']."&Opis=$Item->ID&Smer=-1');\" TITLE=\"Gor\"><IMG SRC=\"pic/list.up.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni gor\" BORDER=\"0\" CLASS=\"icon\"></A>";
					else
						echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
					if ( $CurrentRow < $RecordCount )
						echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Action=$Action->ActionID&ID=".$_GET['ID']."&Opis=$Item->ID&Smer=1');\" TITLE=\"Dol\"><IMG SRC=\"pic/list.down.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni dol\" BORDER=\"0\" CLASS=\"icon\"></A>";
					else
						echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
					echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"javascript:checkLang('$Item->ID','$Item->Naslov');\" TITLE=\"Delete\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" CLASS=\"icon\">\n";
				}
				echo "</TD>\n";
				echo "</TR>\n";
				$CurrentRow++;
			}
		}
		echo "</TABLE>\n";
		echo "</div>\n";
	}

		if ( $Jeziki ) foreach ( $Jeziki as $Jezik ) {
			$List = $db->get_results(
				"SELECT ID, Jezik, Naslov, Polozaj ".
				"FROM BesedilaOpisi ".
				"WHERE BesediloID = ".(int)$_GET['ID'].
				"	AND Jezik = '".$Jezik->Jezik."' ".
				"ORDER BY Jezik, Polozaj"
			);
			echo "<div id=\"$Jezik->Jezik\" style=\"overflow:auto;\">\n";
			echo "<TABLE BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
			if ( contains($ACL,"W") ) {
				echo "<TR CLASS=\"novo\">\n";
				echo "<TD STYLE=\"border-bottom:darkgray solid 1px;\">\n";
				echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','inc.php?Izbor=txtText&Jezik=$Jezik->Jezik&BesediloID=".$_GET['ID']."');\">New...</A>\n";
				echo "</TD>\n";
				echo "<TD ALIGN=\"right\" STYLE=\"border-bottom:darkgray solid 1px;\">\n";
				echo "<A HREF=\"". $WebURL ."/?kat=". $Kategorija ."&ID=". (int)$_GET['ID'] ."&lng=". $Jezik->Jezik ."\" TARGET=\"_blank\" TITLE=\"Predogled\"><IMG SRC=\"pic/list.extern.gif\" WIDTH=11 HEIGHT=11 ALT=\"Predogled\" BORDER=\"0\" CLASS=\"icon\"></A>\n";
				echo "</TD>\n";
				echo "</TR>\n";
			}
			if ( !$List )
				echo "<TR><TD ALIGN=\"center\">No content!</TD></TR>\n";
			else {
				$CurrentRow = 1;
				$RecordCount = count($List);
				foreach ( $List as $Item ) {
					echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
					echo "<TD><A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','inc.php?Izbor=txtText&Jezik=$Jezik->Jezik&BesediloID=".$_GET['ID']."&ID=$Item->ID');\"><B>$Item->Naslov</B></A></TD>\n";
					echo "<TD ALIGN=\"right\" NOWRAP>\n";
					// move items up/down
					if ( contains($ACL,"W") ) {
						if ( $CurrentRow > 1 )
							echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Action=$Action->ActionID&ID=".$_GET['ID']."&Opis=$Item->ID&Smer=-1');\" TITLE=\"Gor\"><IMG SRC=\"pic/list.up.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni gor\" BORDER=\"0\" CLASS=\"icon\"></A>";
						else
							echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
						if ( $CurrentRow < $RecordCount )
							echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Action=$Action->ActionID&ID=".$_GET['ID']."&Opis=$Item->ID&Smer=1');\" TITLE=\"Dol\"><IMG SRC=\"pic/list.down.gif\" WIDTH=11 HEIGHT=11 ALT=\"Pomakni dol\" BORDER=\"0\" CLASS=\"icon\"></A>";
						else
							echo "<img src=\"pic/trans.gif\" width=11 height=11 border=\"0\" align=\"absmiddle\" class=\"icon\">";
						echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"javascript:checkLang('$Item->ID','$Item->Naslov');\" TITLE=\"Delete\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" CLASS=\"icon\">\n";
					}
					echo "</TD>\n";
					echo "</TR>\n";
					$CurrentRow++;
				}
			}
			echo "</TABLE>\n";
			echo "</div>\n";
		}

		// assigned category list
		echo "<div id=\"rubrike\" style=\"overflow:auto;\">\n";
		echo "</div>\n";

		// assigned tags
		echo "<div id=\"tags\" style=\"overflow:auto;\">\n";
		echo "</div>\n";
?>
	</DIV>

	</TD>
</TR>
</TABLE>
<?php endif ?>

<?php if ( $Podatek ) : ?>
<div id="editBox">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD VALIGN="top" WIDTH="34%">

	<FIELDSET>
		<LEGEND>
<?php if ( contains($ACL,"W") ) : ?>
		<A HREF="javascript:void(0);" ONCLICK="window.open('vnos.php?Action=<?php echo $_GET['Action'] ?>&Izbor=Images&BesediloID=<?php echo $_GET['ID'] ?>', 'editgallery', 'scrollbars=no,status=no,menubar=no,toolbar=no,resizable=no,WIDTH=800,HEIGHT=560')" TITLE="Add"><IMG SRC="pic/control.add.gif" ALIGN="absmiddle" WIDTH=14 HEIGHT=14 ALT="Add" BORDER="0" CLASS="icon"></A>&nbsp;:
<?php endif ?>
		Gallery</LEGEND>
		<DIV ID="divSlike" STYLE="overflow:auto;"><img src="pic/control.spinner.gif" alt="Loading" border="0"> Loading ...</DIV>
	</FIELDSET>

	</TD>
	<TD VALIGN="top" WIDTH="33%">

	<FIELDSET>
		<LEGEND>
<?php if ( contains($ACL,"W") ) : ?>
		<div id="findSk" class="find" style="margin:0;">
		<form name="findFrmSk" action="inc.php?Izbor=txtRelated&BesediloID=<?php echo $_GET['ID'] ?>" method="get">
		<input id="findTxtSk" type="Text" name="Find" maxlength="32" value=" Related " style="color:#aaa;" onkeypress="$('#findClrSk').show();">
		<a id="findClrSk" href="javascript:void(0);" onclick="$(this).hide();$('#findTxtSk').val('').select();"><img src="pic/list.clear.gif" border="0"></a>
		</form>
		</div>
<?php else : ?>
		Related
<?php endif ?>
		</LEGEND>
		<DIV ID="divSk" STYLE="overflow:auto;"><img src="pic/control.spinner.gif" alt="Loading" border="0"> Loading ...</DIV>
	</FIELDSET>

	</TD>
	<TD VALIGN="top" WIDTH="33%">

	<FIELDSET>
		<LEGEND>
<?php if ( contains($ACL,"W") ) : ?>
		<div id="findMe" class="find" style="margin:0;">
		<form name="findFrmMe" action="inc.php?Izbor=txtMedia&BesediloID=<?php echo $_GET['ID'] ?>" method="get">
		<input id="findTxtMe" type="Text" name="Find" maxlength="32" value=" Attachments " style="color:#aaa;" onkeypress="$('#findClrMe').show();">
		<a id="findClrMe" href="javascript:void(0);" onclick="$(this).hide();$('#findTxtMe').val('').select();"><img src="pic/list.clear.gif" border="0"></a>
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
</div>
<?php endif ?>
