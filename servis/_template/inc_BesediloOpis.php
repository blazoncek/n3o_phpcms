<?php
/* inc_BesedilaOpis.php - WYSIWYG text editing
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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "0";

if ( isset($_POST['Naslov']) && $_POST['Naslov'] != "" ) {
	// cleanup
	$_POST['Naslov']    = $db->escape(str_replace("\"", "&quot;", left($_POST['Naslov'],128)));
	$_POST['Podnaslov'] = $db->escape(str_replace("\"", "&quot;", left($_POST['Podnaslov'],128)));
	$_POST['Povzetek']  = $db->escape(left($_POST['Povzetek'],512));
	$_POST['Opis']      = str_replace("\\\"","\"",$db->escape(CleanupTinyMCE($_POST['Opis'])));

	$db->query( "START TRANSACTION" );
	if ( $_GET['ID'] != "0" ) {
		$db->query(
			"UPDATE BesedilaOpisi ".
			"SET".
			"	Naslov = ".(($_POST['Naslov']!="")? "'".$_POST['Naslov']."'": "'(neimenovan)'").",".
			"	Podnaslov = ".(($_POST['Podnaslov']!="")? "'".$_POST['Podnaslov']."'": "NULL").",".
			"	Povzetek = ".(($_POST['Povzetek']!="")? "'".$_POST['Povzetek']."'": "NULL").",".
			"	Opis = ".(($_POST['Opis']!="")? "'".$_POST['Opis']."'": "NULL")." ".
			"WHERE ID = ". (int)$_GET['ID']
		);
	} else {
		$Polozaj = $db->get_var( "SELECT max(Polozaj) FROM BesedilaOpisi WHERE BesediloID = ".(int)$_GET['BesediloID'].
			" AND Jezik ".($_POST['Jezik']!=""? "='".$_POST['Jezik']."'": "IS NULL") );
		
		$db->query(
			"INSERT INTO BesedilaOpisi (".
			"	BesediloID,".
			"	Jezik,".
			"	Polozaj,".
			"	Naslov,".
			"	Podnaslov,".
			"	Povzetek,".
			"	Opis".
			") VALUES (".
			"	".$_GET['BesediloID'].",".
			"	".(($_POST['Jezik']!="")? "'".$_POST['Jezik']."'": "NULL").",".
			"	".($Polozaj? $Polozaj+1: 1).",".
			"	".(($_POST['Naslov']!="")? "'".$_POST['Naslov']."'": "'(neimenovan)'").",".
			"	".(($_POST['Podnaslov']!="")? "'".$_POST['Podnaslov']."'": "NULL").",".
			"	".(($_POST['Povzetek']!="")? "'".$_POST['Povzetek']."'": "NULL").",".
			"	".(($_POST['Opis']!="")? "'".$_POST['Opis']."'": "NULL")." )"
		);
	}
	$db->query( "UPDATE Besedila SET DatumSpremembe = '".date('Y-m-d H:i:s')."' WHERE BesediloID = ".(int)$_GET['BesediloID'] );
	$db->query( "COMMIT" );
	
	echo "<SCRIPT LANGUAGE=JAVASCRIPT>\n";
	echo "<!--\n";
	echo "\$(document).ready(function(){loadTo('Edit','edit.php?Izbor=Besedila&ID=".(int)$_GET['BesediloID']."')});\n";
	echo "//-->\n";
	echo "</SCRIPT>\n";
	die();
}

$Podatek = $db->get_row(
	"SELECT
		BO.ID,
		BO.BesediloID,
		BO.Jezik,
		BO.Naslov,
		BO.Podnaslov,
		BO.Povzetek,
		BO.Opis
	FROM BesedilaOpisi BO
	WHERE BO.ID = ".(int)$_GET['ID']
);

$Besedilo = $db->get_row( "SELECT Tip, ACLID FROM Besedila WHERE BesediloID = ".(int)$_GET['BesediloID'] );
// get ACL
if ( $Besedilo ) {
	$ACL = userACL( $Besedilo->ACLID );
} else
	$ACL = "LRWDX";

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
		AND S.SifrText = '". $Besedilo->Tip ."'
	ORDER BY
		ST.Jezik DESC
	LIMIT 1"
);
$GalleryBase  = ($x && $x->GalleryBase!='')   ? $x->GalleryBase       : $GalleryBase;
$DefPicSize   = ($x && (int)$x->DefPicSize)   ? (int)$x->DefPicSize   : $DefPicSize;
$DefThumbSize = ($x && (int)$x->DefThumbSize) ? (int)$x->DefThumbSize : $DefThumbSize;
$MaxPicSize   = ($x && (int)$x->MaxPicSize)   ? (int)$x->MaxPicSize   : $MaxPicSize;

setcookie("img_upload","1");
setcookie("img_path", $GalleryBase);

?>
<SCRIPT language="javascript" type="text/javascript">
<!--
window.thumbSize = <?php echo $DefThumbSize ?>;
window.imageSize = <?php echo $DefPicSize ?>;
window.idDocument = <?php echo $_GET['BesediloID'] ?>;

function customResize() {
	// vertically resize edit child divs
	edit = $("#divContent").height(0).height( $("#divEdit").height() + $("#divEdit").position().top - $("#divContent").position().top );
	if ( $("#HTMLeditor").parent().html() ) {
		$("#HTMLeditor").height(0); // fix for TD resizing
		$("#HTMLeditor_ifr").height(0); // fix for TD resizing
		$("#HTMLeditor").parent().height(0).height( edit.height() + edit.position().top - $("#HTMLeditor").parent().position().top - 16 );
		$("#HTMLeditor").height( $("#HTMLeditor").parent().innerHeight() - 9 );
		$("#HTMLeditor_ifr").height( $("#HTMLeditor").height() - $("#HTMLeditor_toolbargroup").height() - $("#HTMLeditor_path_row").height() );
	}
}

$(document).ready(function(){
	window.customResize = customResize;

	// bind to the form's submit event
	$("form[name='Vnos']").submit(function(){
		this.Opis.value = $("textarea[name='Opis']").html();
		$(this).ajaxSubmit({
			target: '#divEdit',
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (empty(fObj.Naslov))	{alert("Prosim vnesite naslov!"); fObj.Naslov.focus(); return false;}
				if (fObj.Jezik.selectedIndex==0)	{alert("Izberite jezik!"); fObj.Jezik.focus(); return false;}
				return true;
			} // pre-submit callback
		});
		return false;
	});

	// enable TinyMCE
	$("textarea[name='Opis']").tinymce({
		script_url : '<?php echo $js ?>/tiny_mce/tiny_mce.js',
		mode : "exact",
		//language : "si",
		elements : "HTMLeditor",
		element_format : "html",
		theme : "advanced",
		content_css : "editor_css.php",
		plugins : "safari,table,advimage,advhr,contextmenu",
		auto_cleanup_word : true,
		extended_valid_elements : "a[href|target|title],img[src|border=0|alt|class|hspace|vspace|width|height|align|style],hr[size|noshade],font[face|size|color|style],div[class|align|style],span[class|style],ol[type],ul[type]",
		invalid_elements : "iframe,layer,script,link",
		file_browser_callback : "fileBrowserCallBack",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "none",
		theme_advanced_buttons1 : "bold,italic,underline,sub,sup,separator,bullist,numlist,outdent,indent,blockquote,separator",
		theme_advanced_buttons1_add : "justifyleft,justifycenter,justifyright,justifyfull,separator,advhr,separator,table,image,link,unlink,separator,code",
		theme_advanced_buttons2 : "styleselect,formatselect,fontselect,fontsizeselect,forecolor,backcolor,separator,removeformat",
		theme_advanced_buttons3 : "",
		theme_advanced_styles : "Koda=code;Citat=quote;Slika=imgcenter;Slika (levo)=imgleft;Slika (desno)=imgright"
	});
	
	// resize HTML editor
	window.customResize();
});
//-->
</SCRIPT>

<DIV CLASS="subtitle">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
	<td><div id="ToggleFrame" style="display:none;">&nbsp;<A HREF="javascript:toggleFrame()"><img src="pic/control.frame.gif" height="14" width="14" alt="Preklop celo/zmanjšano okno" border="0" align="absmiddle" class="icon">&nbsp;List</a></div></td>
	<td id="editNote" align="right"><B>Vnos besedila</B>&nbsp;&nbsp;</td>
</tr>
</table>
</DIV>
<DIV ID="divContent" style="padding: 5px;">
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<!-- FORM NAME="Vnos" ACTION="edit.php?Action=<?php echo $_GET['Action'] ?>&ID=<?php echo $_GET['BesediloID'] ?>" METHOD="post" -->
<?php if ( $Podatek ) : ?>
<INPUT NAME="OpisID" TYPE="Hidden" VALUE="<?php echo $Podatek->ID ?>">
<?php endif ?>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD NOWRAP><B>Naslov:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="Naslov" MAXLENGTH="128" VALUE="<?php echo ($Podatek? $Podatek->Naslov: "") ?>" STYLE="width:100%" TABINDEX="7"></TD>
	<TD>
	&nbsp;Jezik: <B class="red"><?php echo ($_GET['Jezik']==""? "vsi": $_GET['Jezik']) ?></B>
	<INPUT TYPE="Hidden" NAME="Jezik" VALUE="<?php echo $_GET['Jezik'] ?>">
	</TD>
	<TD ALIGN="right">
	<INPUT TYPE="Button" VALUE=" Zapri " ONCLICK="loadTo('Edit','edit.php?Izbor=Besedila&ID=<?php echo (int)$_GET['BesediloID'] ?>')" CLASS="but">
	<INPUT TYPE="submit" VALUE=" Save " CLASS="but">
	</TD>
</TR>
<TR>
	<TD NOWRAP><SPAN CLASS="f10">Podnaslov:&nbsp;<BR>Povzetek:</SPAN></TD>
	<TD colspan="4"><INPUT TYPE="text" NAME="Podnaslov" MAXLENGTH="128" VALUE="<?php echo ($Podatek? $Podatek->Podnaslov: "") ?>" STYLE="width:100%;" TABINDEX="8"></TD>
</TR>
<TR>
	<TD COLSPAN="4" VALIGN="top"><TEXTAREA NAME="Povzetek" ROWS="3" STYLE="width:100%;" TABINDEX="9"><?php echo ($Podatek? $Podatek->Povzetek: "") ?></TEXTAREA></TD>
</TR>
<TR>
	<TD COLSPAN="4" VALIGN="top"><B>Opis:</B> <SPAN CLASS="f10 gry">(Copy/Paste from Word is not recommended)</SPAN></TD>
</TR>
<?php
	$Opis = $Podatek ? str_replace("\\\"","\"",$Podatek->Opis) : ""; // strip escaped quotes
	$Opis = $Podatek ? str_replace('&lt;','&amp;lt;',$Opis) : ""; // TinyMCE bugfix
	$Opis = $Podatek ? str_replace('&gt;','&amp;gt;',$Opis) : ""; // TinyMCE bugfix
	$Opis = preg_replace( "/(<img\s+src=\")(?!(?:http|data|\/))/i", '$1../', $Opis ); // adjust path for images
?>
<TR>
	<TD COLSPAN="4" VALIGN="top"><TEXTAREA NAME="Opis" ID="HTMLeditor" STYLE="width:100%;height:100%;"><?php echo ($Podatek? $Opis: "") ?></TEXTAREA></TD>
</TR>	
</TABLE>
</FORM>
</DIV>
