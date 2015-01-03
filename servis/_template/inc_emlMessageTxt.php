<?php
/*~ inc_emlMessageTxt.php - WYSIWYG text editing
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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "0";

$Podatek = $db->get_row(
	"SELECT
		MT.emlMessageTxtID AS ID,
		MT.Naziv,
		MT.Opis,
		MT.Jezik,
		MT.emlMessageID,
		M.ACLID
	FROM emlMessagesTxt MT
		LEFT JOIN emlMessages M ON MT.emlMessageID = M.emlMessageID
	WHERE MT.emlMessageTxtID = ".(int)$_GET['ID']
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
				if (empty(fObj.Subject))	{alert("Please enter message subject!"); fObj.Subject.focus(); return false;}
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
		plugins : "inlinepopups,safari,table,advimage,advhr,contextmenu",
		auto_cleanup_word : true,
		extended_valid_elements : "a[href|target|title],img[src|border=0|alt|class|hspace|vspace|width|height|align|style],hr[size|noshade],font[face|size|color|style],div[class|align|style],span[class|style],ol[type],ul[type]",
		invalid_elements : "iframe,layer,script,link",
		file_browser_callback : "fileBrowserCallBack",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
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
	<td><div id="ToggleFrame" style="display:none;">&nbsp;<A HREF="javascript:toggleFrame()"><img src="pic/control.frame.gif" height="14" width="14" alt="Preklop celo/zmanj�ano okno" border="0" align="absmiddle" class="icon">&nbsp;List</a></div></td>
	<td id="editNote" align="right">emlMessageTxt - <B>Message entry</B>&nbsp;&nbsp;</td>
</tr>
</table>
</DIV>
<DIV ID="divContent" style="padding: 5px;">
<FORM NAME="Vnos" ACTION="edit.php?Action=<?php echo $_GET['Action'] ?>&ID=<?php echo $_GET['emlMessageID'] ?>" METHOD="post">
<?php if ( $Podatek ) : ?>
<INPUT NAME="OpisID" TYPE="Hidden" VALUE="<?php echo $Podatek->ID ?>">
<?php endif ?>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD NOWRAP><B>Subject:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="Subject" MAXLENGTH="128" VALUE="<?php echo ($Podatek? $Podatek->Naziv : "") ?>" STYLE="width:100%" TABINDEX="7"></TD>
	<TD>&nbsp;Language:
	<SELECT NAME="Jezik" SIZE="1" <?php echo (($Podatek)? "DISABLED": "") ?>>
		<OPTION VALUE="" DISABLED STYLE="background-color:whitesmoke;">Select...</OPTION>
<?php
$Jeziki = $db->get_results(
	"SELECT J.Jezik, J.Opis
	FROM Jeziki J
		LEFT JOIN emlMessagesTxt MT ON J.Jezik = MT.Jezik AND MT.emlMessageID = ". (int)$_GET['emlMessageID'] ."
	WHERE
		J.Enabled = 1". ((!$Podatek)? " AND MT.Jezik IS NULL": "")
	);
$All = $db->get_var(
	"SELECT count(*)
	FROM emlMessagesTxt
	WHERE emlMessageID = ".(int)$_GET['emlMessageID']."	AND Jezik IS NULL"
);
	if ( !$All || $Podatek )
		echo "<OPTION VALUE=\"\"".(($Podatek && $Podatek->Jezik == "")? " SELECTED": "").">- all -</OPTION>\n";
	if ( $Jeziki ) foreach ( $Jeziki as $Jezik )
		echo "<OPTION VALUE=\"$Jezik->Jezik\"".($Podatek && $Podatek->Jezik == $Jezik->Jezik? " SELECTED": "").">$Jezik->Opis</OPTION>\n";
?>
	</SELECT>
	</TD>
	<TD ALIGN="right">
	<INPUT TYPE="Button" VALUE=" Close " ONCLICK="loadTo('Edit','edit.php?Izbor=emlMessages&ID=<?php echo (int)$_GET['emlMessageID'] ?>')" CLASS="but">
	<INPUT TYPE="submit" VALUE=" Save " CLASS="but">
	</TD>
</TR>
<!--TR>
	<TD COLSPAN="4" VALIGN="top"><B>Content:</B> <SPAN CLASS="f10 gry">(Copy/Paste from Word is not recommended)</SPAN></TD>
</TR-->
<?php
	if ( $Podatek ) {
		$Opis = $Podatek->Opis;
		$Opis = preg_replace("/(src=\")(?!(?:http|data))/i", '$1../', $Opis);
		//$Opis = str_replace("&", "&amp;", $Opis);
	} else
		$Opis = "";
?>
<TR>
	<TD COLSPAN="4" VALIGN="top" HEIGHT="400"><TEXTAREA NAME="Opis" ID="HTMLeditor" STYLE="width:100%;height:100%;"><?php echo $Opis ?></TEXTAREA></TD>
</TR>
</TABLE>
</FORM>
</DIV>
