<?php
/*~ upload_text_image.php - Uploading images for TinyMCE WYSIWYG editor
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

// include application variables and settings framework
require_once("../_application.php");

if ( !isset($_COOKIE['img_path']) )
	die("Wrong parameters.");

// Hard Code path here
$UPLOADpath = $StoreRoot ."/media";
$URLpath    = "../media";
if ( !isset($_GET['base']) ) $_GET['base'] = $_COOKIE['img_path'];
if ( $_GET['base'] != "" ) {
	$UPLOADpath .= '/'.$_GET['base'];
	$URLpath    .= '/'.$_GET['base'];
}
$URLpath .= '/';

// delete image
if ( isset($_GET['delete']) && $_GET['delete'] != "" ) {
	$e = strrchr($_GET['delete'], '.');
	$b = left($_GET['delete'], strlen($_GET['delete'])-strlen($e));
	$Slika = $b . $e;
	@unlink($UPLOADpath ."/". $Slika);
	@unlink($UPLOADpath ."/". $b .'@2x'. $e);
	@unlink($UPLOADpath ."/thumbs/". $Slika);
	@unlink($UPLOADpath ."/thumbs/". $b .'@2x'. $e);
	@unlink($UPLOADpath ."/large/". $Slika);

	$_SERVER['QUERY_STRING'] = preg_replace( "/\&delete=[a-zA-Z0-9\.\-\_]+/", "", $_SERVER['QUERY_STRING'] );
	$message = "<div class=\"red\">Image deleted!</div>\n";
}

if ( !isset($_GET['sort']) ) $_GET['sort'] = "date";

// build search links
$FindURL = dirname($_SERVER['PHP_SELF']) ."/". basename($_SERVER['PHP_SELF']) ."?";
foreach ( explode("&", $_SERVER['QUERY_STRING']) as $Param ) {
	// prevent empty parameters (double &)
	if ( $Param == "" ) continue;
	// split parameter to name and value: x=[name,value]
	$x = explode("=", $Param);
	// check if preprocessing changed parameter
	if ( $_GET[$x[0]] != $x[1] )
		$Param = $x[0] ."=". $_GET[$x[0]];
	else
		$Param = $x[0] ."=". $x[1];
	// remove certain parameters
	if ( $x[0]!="delete" && $x[0]!="sort" && $x[0]!="find" )
		$FindURL .= $Param ."&";
}
if ( substr($FindURL,-1)=="&" )
	$FindURL = substr($FindURL,0,strlen($FindURL)-1);
//$FindURL = str_replace('&','&amp;',$FindURL);

?>
<!DOCTYPE HTML>
<HTML>
<head>
<meta name="Author" content="Blaž Kristan (blaz@kristan-sp.si)">
<link rel="stylesheet" type="text/css" href="style.css">
<style>
INPUT.text { border:silver solid 1px; font-size: 10px; }
INPUT.check { border: none; padding:0px; margin:0px; }
</style>
<script language="javascript" type="text/javascript" src="<?php echo $js ?>/jquery/jquery.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $js ?>/jquery/jquery.ui.widget.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $js ?>/jquery/jquery.iframe-transport.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $js ?>/jquery/jquery.fileupload.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $js ?>/tiny_mce/tiny_mce_popup.js"></script>
<script type="text/javascript">
<!-- //
// fix internal height
function fixSize() {
	var list  = $("#divListFU").width(0).height(0);
	list.width( $(window).width() ).height( $(window).height() - list.position().top );
}
// prevent default drop behaviour on drop (outside fileupload area)
$(document).bind('drop dragover', function (e) {
    e.preventDefault();
});

$(document).ready(function(){
	fixSize();
	// drag&drop image uploading
    $('#fileupload').fileupload({
        dataType: 'json',
		dropZone: $('#divListFU'),
		pasteZone: null,
		//fileInput: null, // prevent automatic upload
		add: function(e, data) {
			$('#loading').remove();
			data.context = $('#divListFU').prepend('<div id="loading" class="gry center"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</div>');
			data.url = 'upload_image.php?p='+$('input[name=base]').val()
				+'&bid='+$('input[name=bid]').val()
				+'&t='+$('input[name=thumbnail]').val()
				+'&s='+$('input[name=maxsize]').val()
				+($('input[name=square]:checked').val() ? '&sq=on' : '');
			data.submit();
		},
        done: function (e, data) {
			$('#loading').remove();
			if ( data.result.files['name'] ) {
				file = '../'+data.result.files['path']+'/'+data.result.files['name'];
				row  = "<TR ONMOUSEOVER=\"this.style.backgroundColor='#edf3fe';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">";
				row += "<TD VALIGN=\"middle\">&nbsp;<a href=\"javascript:insertURL('"+file+"')\" class=\"red\">"+data.result.files['name']+"</a></TD>";
				row += "<TD ALIGN=\"right\" class=\"red\">now&nbsp;</TD>\n";
				row += "<TD ALIGN=\"right\"><a href=\"javascript:deleteimg('"+data.result.files['name']+"');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Delete\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a></TD>";
				row += "</TR>";
				$('#tblList').prepend(row);
			} else if ( data.result.files['error'] ) {
				$('#tblList').prepend("<tr><td align=\"center\" class=\"red\" colspan=\"3\">"+data.result.files['error']+"</td></tr>");
			}
        }
    });
});
$(window).resize(fixSize);

function checkImg(file) {
	document.images["test"].style.display="none";
}

function insertURL(url) {
	var refWin = tinyMCEPopup.getWindowArg("window");
	var refFld = refWin.document.getElementById(tinyMCEPopup.getWindowArg("input"));
	// insert information now
	refFld.value = url; // parent.window.opener.tinyMCE.editors.HTMLeditor.convertURL(url, null, true);
	// Try to fire the onchange event
	try {
		refFld.onchange();
	} catch (e) {} // Skip it
	// close popup window
	tinyMCEPopup.close();
//	window.close();
}

function deleteimg(img_name) {
	if (confirm("Delete '"+img_name+"'\n\nThis can affect other texts/galleries!\nAre you sure?"))
		document.location.href="<?php echo $FindURL; ?>&delete="+img_name;
}

function loading(o) {
	$('#divListFU').html('<div class="gry" style="text-align:center;"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
	o.action = 'upload_image.php?p='+$('input[name=base]').val()
		+'&bid='+$('input[name=bid]').val()
		+'&t='+$('input[name=thumbnail]').val()
		+'&s='+$('input[name=maxsize]').val()
		+($('input[name=square]:checked').val() ? '&sq=on' : '');
	o.submit();
	setTimeout('location.replace("<?php echo $FindURL ?>");', 500);
	return false;
}

var FileBrowserDialogue = {
	init : function () {
		// Here goes your code for setting your custom things onLoad.
		var res = tinyMCEPopup.getWindowArg("resizable");
		var inline = tinyMCEPopup.getWindowArg("inline");
	},
	mySubmit : function () {
		// Here goes your code to insert the retrieved URL value into the original dialogue window.
		var URL = document.my_form.my_field.value;
		var win = tinyMCEPopup.getWindowArg("window");
		var input = tinyMCEPopup.getWindowArg("input");

		// insert information now
		win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

	}
}
tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);
//-->
</script>
</head>
<body style="background-color:lightgrey;">
<?php if ( isset($message) ) echo $message; ?>
<?php if ( isset($_COOKIE['img_upload']) && $_COOKIE['img_upload'] != "0" ) : ?>
<div id="dropzone" style="margin:5px;">
<form name="frm_upload" action="upload_image.php" onsubmit="return loading(this);" enctype="multipart/form-data" method="post">
<table border="0" cellspacing="0" cellpadding="1" width="100%">
<tr>
	<td colspan="2" class="f10">Set thumbnail size to
	<input type="Text" name="thumbnail" size="3" maxlength="3" value="<?php echo isset($_GET['T'])? abs((int)$_GET['T']): "64" ?>" class="text">px
	(0=none);
	<input name="square" type="checkbox" <?php echo (isset($_GET['T']) && (int)$_GET['T'] < 0) ? "checked" : "" ?> style="border:none;padding:0px;margin:0px;">
	use square thumbnail;
	<input name="large" type="checkbox" checked class="check">
	retain image &gt; 
	<input name="maxsize" type="text" size="4" maxlength="4" value="<?php echo isset($_GET['S'])? $_GET['S']: "512" ?>" class="text" onchange="checkImg(this.form.file.value);">px
	and then select image:</td>
</tr>
<tr>
	<td class="f10"><input type="Hidden" name="bid" value="<?php echo isset($_GET['ID'])? (int)$_GET['ID']: "0" ?>">
	<input id="fileupload" type="file" name="file" style="border:none;" onchange="checkImg(this.value);"></td>
	<td align="right" class="f10" width="30%"><input type="submit" value=" Add &raquo; " class="but" style="font-weight:bold;">&nbsp;</td>
</tr>
<tr>
	<td colspan="2" class="f10">(If the image is not uploaded automatically use the Add button.)</td>
</tr>
</table>
</form>
<img name="test" style="visibility:hidden;display:none;">
</div>
<?php endif ?>
<div class="find" style="margin-top:5px;border-top:1px solid black;">
<form name="ListFind" action="<?php echo $FindURL ?>" method="get">
<input name="T" type="Hidden" value="<?php echo $_GET['T'] ?>">
<input name="S" type="Hidden" value="<?php echo $_GET['S'] ?>">
<input name="base" type="Hidden" value="<?php echo $_GET['base'] ?>">
<input type="Text" name="find" id="inpFind" maxlength="32" value="<?php echo isset($_GET['find']) ? $_GET['find'] : ''; ?>" onkeypress="$('#clrFind').show();" onfocus="if ($('#inpFind').val()!='') $('#clrFind').show();">
<a id="clrFind" href="javascript:void(0);" onclick="$(this).hide();$('#inpFind').val('').select();"><img src="pic/list.clear.gif" border="0"></a>
</form>
</div>
<DIV ID="divSort" style="text-align: center; background-color:whitesmoke;margin-top:5px;border-top: silver 1px solid; border-bottom: silver 1px solid;">Sort: 
<A HREF="<?php echo $FindURL ?>&amp;sort=name<?php echo isset($_GET['find']) ? '&amp;find='.$_GET['find'] : '' ?>">ime</A> | 
<A HREF="<?php echo $FindURL ?>&amp;sort=date<?php echo isset($_GET['find']) ? '&amp;find='.$_GET['find'] : '' ?>">datum</A> |
<A HREF="<?php echo $FindURL ?>&amp;sort=size<?php echo isset($_GET['find']) ? '&amp;find='.$_GET['find'] : '' ?>">velikost</A>
</DIV>
<?php
	$folder = scandir($UPLOADpath);

	//custom function for sorting files
	function compare( $aa, $bb )
	{
		switch ( $_GET['sort'] ) {
			case "name": $a = $aa[0]; $b = $bb[0]; $order = 1; break;
			case "size": $a = $aa[1]; $b = $bb[1]; $order = 1; break;
			case "date": $a = $aa[2]; $b = $bb[2]; $order = -1; break;
		}
	    if ($a == $b) return 0;
	    return ($a < $b) ? -$order : $order;
	}

	// create arrays with dirs & files w/ info (name, size, date)
	$dirs  = array();
	$files = array();
	foreach ( $folder as $item ) {
		// skip reserved files
		if ( (left($item,1) == "." || left($item,1) == "_" || left($item,1) == "@") ) continue;

		// mark directories
		if ( is_dir($UPLOADpath."/".$item) ) $dirs[] = $item;

		// select files (use filter)
		if ( is_file($UPLOADpath."/".$item) ) {
			// ignore files not in filter
			if ( isset($_GET['find']) && $_GET['find']!='' && !contains(strtolower($item),strtolower($_GET['find'])) ) continue;
			// ignore non-image files & retina images
			if ( left(strtolower($item),3) == 'sm_'
			  || !contains('gif,jpg,png', strtolower(right($item,3)))
			  || contains(strtolower($item), '@2x') )
				continue;
			$stat  = stat($UPLOADpath."/".$item);
			$files = array_merge($files, array(array($item, (int)$stat['size']/1024, $stat['mtime'])));
		}
	}
	unset($folder);
	
	// sort files
	usort($files, "compare");

	$RecordCount = count($files);

	// determine maximum number of rows to display
	$MaxRows = $db->get_var("SELECT SifNVal1 FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='ListMax'");
	if ( !$MaxRows ) $MaxRows = 25; // default value

	// are we requested do display different page?
	$Page = !isset($_GET['pg']) ? 1 : (int) $_GET['pg'];
	
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

	$link = $FindURL .'&amp;sort='.$_GET['sort'] . ((isset($_GET['find']) && $_GET['find']!='') ? '&amp;find='.$_GET['find'] : '');
	if ( $NuPg > 1 ) {
		echo "<DIV CLASS=\"pg\" style=\"border-bottom: darkgrey solid 1px; border-top: darkgrey solid 1px; padding: 5px 0px; text-align: center;\">\n";
		if ( $StPg > 1 )
			echo "<A HREF=\"". $link ."&amp;pg=".($StPg-1)."\">&laquo;</A>\n";
		if ( $Page > 1 )
			echo "<A HREF=\"". $link ."&amp;pg=". $PrPg ."\">&lt;</A>\n";
		for ( $i = $StPg; $i <= $EdPg; $i++ ) {
			if ( $i == $Page )
				echo "<FONT COLOR=\"red\"><B>". $i ."</B></FONT>\n";
			else
				echo "<A HREF=\"". $link ."&amp;pg=". $i ."\">". $i ."</A>\n";
		}
		if ( $Page < $EdPg )
			echo "<A HREF=\"". $link ."&amp;pg=". $NePg ."\">&gt;</A>\n";
		if ( $NuPg > $EdPg )
			echo "<A HREF=\"". $link ."&amp;pg=".($EdPg<$NuPg? $EdPg+1: $EdPg)."\">&raquo;</A>\n";
		echo "</DIV>\n";
	}

	echo "<DIV id=\"divListFU\" style=\"overflow-y:auto;\">\n";
	echo "<TABLE ID=\"tblList\" BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	if ( $_GET['base']!='') {
		echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='#edf3fe';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
		echo "<TD VALIGN=\"middle\">&nbsp;<A href=\"". $_SERVER['PHP_SELF'] ."?T=". $_GET['T'] ."&amp;S=". $_GET['S'] ."&amp;base=". left($_GET['base'], strrpos($_GET['base'],'/')) ."\">..</A></TD>\n";
		echo "<TD ALIGN=\"right\"><I>dir</I>&nbsp;</TD>\n";
		echo "<TD ALIGN=\"right\">&nbsp;</TD>\n";
		echo "</TR>\n";
	}
	if ( sort($dirs) ) foreach ( $dirs as $dir ) {
		echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='#edf3fe';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
		echo "<TD VALIGN=\"middle\">&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?T=".$_GET['T']."&amp;S=".$_GET['S']."&amp;base=".($_GET['base']!='' ? $_GET['base'].'/' : '').$dir."\"><I>".left($dir,33).(strlen($dir)>33? "...": "")."</I></A></TD>\n";
		echo "<TD ALIGN=\"right\"><I>dir</I>&nbsp;</TD>\n";
		echo "<TD ALIGN=\"right\">&nbsp;</TD>\n";
		echo "</TR>\n";
	}
	// display files
	
	for ( $i = $StaR-1; $i < $EndR; $i++ ) {
		$file = $files[$i]; // get list item
		//$file[0] = strtolower($file[0]);
		echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='#edf3fe';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
		echo "<TD VALIGN=\"middle\"". ((isset($_POST['photo']) && $file[0]==$photo) ? " class=\"red\">" : ">");
		echo "&nbsp;<a href=\"javascript:insertURL('". $URLpath . $file[0] ."')\">";
		echo left($file[0],33) . (strlen($file[0])>33 ? "..." : "") ."</a></TD>\n";
		echo "<TD ALIGN=\"right\"".((isset($_POST['photo']) && $file[0]==$photo)? " class=\"red\">": "").">". date("j.n.y",$file[2]) ."&nbsp;</TD>\n";
		echo "<TD ALIGN=\"right\"><a href=\"javascript:deleteimg('$file[0]');\"><img src=\"pic/list.delete.gif\" width=11 height=11 alt=\"Delete\" border=\"0\" align=\"absmiddle\" class=\"icon\"></a></td>\n";
		echo "</TR>\n";
	}
	echo "</TABLE>\n";
	echo "</DIV>\n";
?>
</body>
</HTML>