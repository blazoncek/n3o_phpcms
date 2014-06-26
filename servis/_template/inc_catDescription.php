<?php
/*~ inc_catDescription.php - WYSIWYG text editing
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

/* NOTE:
updating DB moved to _qry/edit_Categories.php
*/

$Podatek = $db->get_row(
	"SELECT
		KN.ID,
		KN.KategorijaID,
		KN.Jezik,
		KN.Naziv,
		KN.Povzetek,
		KN.Opis,
		KN.Slika,
		K.ACLID
	FROM KategorijeNazivi KN
		LEFT JOIN Kategorije K
			ON KN.KategorijaID = K.KategorijaID
	WHERE KN.ID = ".(int)$_GET['ID']
	);
if ( $Podatek )
	$ACL = userACL($Podatek->ACLID);
else
	$ACL = "LRWDX";

setcookie("img_upload","0");
setcookie("img_path", "");

?>
<SCRIPT language="javascript" type="text/javascript">
<!--
window.thumbSize = 128;
window.imageSize = 640;

function customResize() {
	// vertically resize edit child divs
	frame = $("#divContent").height(0).height( $("#divEdit").height() + $("#divEdit").position().top - $("#divContent").position().top );
	edit = $("#HTMLeditor").parent(); // TD element
	if ( edit.html() ) {
		edit.height( frame.height() + frame.position().top - edit.position().top - 6 );
		edit.width( frame.width() + frame.position().left - edit.position().left );
		$("#HTMLeditor").height( edit.innerHeight() - 10 );
		$("#HTMLeditor_ifr").height( $("#HTMLeditor").height() - $("#HTMLeditor_toolbargroup").height() );
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
				if (empty(fObj.Naziv))	{alert("Please enter title!"); fObj.Naziv.focus(); return false;}
				if (fObj.Jezik.selectedIndex==0)	{alert("Select language!"); fObj.Jezik.focus(); return false;}
				return true;
			} // pre-submit callback
		});
		return false;
	});
	
	// resize HTML editor
	window.customResize();

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
});
//-->
</SCRIPT>

<DIV CLASS="subtitle">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
	<td><div id="ToggleFrame" style="display:none;">&nbsp;<A HREF="javascript:toggleFrame()"><img src="pic/control.frame.gif" height="14" width="14" alt="Preklop celo/zmanjšano okno" border="0" align="absmiddle" class="icon">&nbsp;List</a></div></td>
	<td id="editNote" align="right"><B>Text entry</B>&nbsp;&nbsp;</td>
</tr>
</table>
</DIV>
<DIV ID="divContent" style="padding: 5px;">
<!-- FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post" -->
<FORM NAME="Vnos" ACTION="edit.php?Action=<?php echo $_GET['Action'] ?>&ID=<?php echo $_GET['KategorijaID'] ?>" METHOD="post">
<?php if ( $Podatek ) : ?>
<INPUT NAME="OpisID" TYPE="Hidden" VALUE="<?php echo $Podatek->ID ?>">
<?php endif ?>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD NOWRAP><B>Title:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="Naziv" MAXLENGTH="128" VALUE="<?php echo ($Podatek? $Podatek->Naziv : "") ?>" STYLE="width:100%" TABINDEX="7"></TD>
	<TD>&nbsp;Jezik: 
	<SELECT NAME="Jezik" SIZE="1" <?php echo (($Podatek)? "DISABLED": "NAME=\"Jezik\"") ?>>
		<OPTION VALUE="" DISABLED STYLE="background-color:whitesmoke;">Select ...</OPTION>
<?php
$Jeziki = $db->get_results(
	"SELECT J.Jezik, J.Opis
	FROM Jeziki J
		LEFT JOIN KategorijeNazivi KN ON J.Jezik = KN.Jezik AND KN.KategorijaID = '". $db->escape($_GET['KategorijaID']) ."'
	WHERE
		J.Enabled = 1". ((!$Podatek)? " AND KN.Jezik IS NULL": "")
	);
$All = $db->get_var(
	"SELECT count(*) ".
	"FROM KategorijeNazivi ".
	"WHERE KategorijaID = '".$db->escape($_GET['KategorijaID'])."'".
	"	AND Jezik IS NULL"
);
	if ( !$All || $Podatek )
		echo "<OPTION VALUE=\"\"".(($Podatek && $Podatek->Jezik == "")? " SELECTED": "").">- all -</OPTION>\n";
	if ( $Jeziki ) foreach ( $Jeziki as $Jezik )
		echo "<OPTION VALUE=\"$Jezik->Jezik\"".($Podatek && $Podatek->Jezik == $Jezik->Jezik? " SELECTED": "").">$Jezik->Opis</OPTION>\n";
?>
	</SELECT>
	</TD>
	<TD ALIGN="right">
	<INPUT TYPE="Button" VALUE=" Close " ONCLICK="loadTo('Edit','edit.php?Izbor=Categories&ID=<?php echo $_GET['KategorijaID'] ?>')" CLASS="but">
	<INPUT TYPE="submit" VALUE=" Save " CLASS="but">
	</TD>
</TR>
<TR>
	<TD COLSPAN="4" VALIGN="top"><SPAN CLASS="f10">Short description:</SPAN>
	<TEXTAREA NAME="Povzetek" ROWS="3" STYLE="width:100%;" TABINDEX="9"><?php echo ($Podatek? $Podatek->Povzetek: "") ?></TEXTAREA>
	</TD>
</TR>
<TR>
	<TD COLSPAN="4" VALIGN="top"><B>Description:</B> <SPAN CLASS="f10 gry">(Copy/Paste from Word is not recommended)</SPAN></TD>
</TR>
<?php
	$Opis = $Podatek ? str_replace("\\\"","\"",$Podatek->Opis) : ""; // strip escaped quotes
	$Opis = $Podatek ? str_replace('&lt;','&amp;lt;',$Opis) : ""; // TinyMCE bugfix
	$Opis = $Podatek ? str_replace('&gt;','&amp;gt;',$Opis) : ""; // TinyMCE bugfix
	$Opis = preg_replace( "/(<img\s+src=\")(?!(?:http|data|\/))/i", '$1../', $Opis ); // adjust path for images
?>
<TR>
	<TD COLSPAN="4" VALIGN="top" HEIGHT="400"><TEXTAREA NAME="Opis" ID="HTMLeditor" STYLE="width:100%;height:100%;"><?php echo $Opis ?></TEXTAREA></TD>
</TR>	
</TABLE>
</FORM>
</DIV>
