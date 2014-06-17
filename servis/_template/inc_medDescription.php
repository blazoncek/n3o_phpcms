<?php
/*~ inc_medDescription.php - Editing of media text descriptions.
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

if ( !isset($_GET['Jezik']) ) $_GET['Jezik'] = "Novo";

// VPISOVANJE PODATKOV O JEZIKOVNIH VARIANTAH
if ( isset($_POST['Naslov']) ) {
	// cleanup
	$_POST['Naslov'] = $db->escape(str_replace( "\"", "&quot;", $_POST['Naslov'] ));
	$_POST['Opis']   = str_replace("\\\"","\"",$db->escape(CleanupTinyMCE($_POST['Opis'])));

	$db->query("START TRANSACTION");
	if ( $_GET['ID'] != "0" ) {
		$db->query(
			"UPDATE MediaOpisi ".
			"SET Naslov = ".(($_POST['Naslov']!="")? "'".$_POST['Naslov']."'": "NULL").",".
			"	Opis = ".(($_POST['Opis']!="")? "'".$_POST['Opis']."'": "NULL")." ".
			"WHERE ID = " . (int)$_GET['ID']
		);
	} else {
		$db->query(
			"INSERT INTO MediaOpisi (".
			"	Jezik,".
			"	MediaID,".
			"	Naslov,".
			"	Opis".
			") VALUES (".
			"	".(($_POST['Jezik']!="")? "'".$_POST['Jezik']."'": "NULL").",".
			"	".(int)$_GET['MediaID'].",".
			"	".(($_POST['Naslov']!="")? "'".$_POST['Naslov']."'": "'(unnamed)'").",".
			"	".(($_POST['Opis']!="")? "'".$_POST['Opis']."'": "NULL")." )"
		);
		//$_GET['ID'] = $db->insert_id;
		// update URI
		//$_SERVER['QUERY_STRING'] = preg_replace( "/\&ID=[0-9]+/", "", $_SERVER['QUERY_STRING'] ) . "&ID=" . $_GET['ID'];
	}
	$db->query("COMMIT");

	unset($_GET['ID']);
}

if ( isset( $_GET['BrisiOpis'] ) && $_GET['BrisiOpis'] != "" ) {
	$db->query( "DELETE FROM MediaOpisi WHERE ID = " . (int) $_GET['BrisiOpis'] );
}

if ( isset($_GET['ID']) ) {

	$Podatek = $db->get_row(
		"SELECT MO.ID, MO.Naslov, MO.Opis, MO.Jezik, MO.MediaID, M.ACLID ".
		"FROM MediaOpisi MO ".
		"	LEFT JOIN Media M ON MO.MediaID = M.MediaID ".
		"WHERE MO.ID = ".(int)$_GET['ID']
	);
	if ( $Podatek ) {
		$ACL = userACL( $Podatek->ACLID );
		$_GET['Jezik'] = $Podatek->Jezik;
	} else
		$ACL = "LRWDX";

	setcookie("img_upload","0");
	setcookie("img_path", "");

?>
<script language="JavaScript" type="text/javascript">
<!-- //
window.thumbSize = 128;
window.imageSize = 640;

$(document).ready(function(){
	// bind to the form's submit event
	$("form[name='medDescription']").submit(function(){
		if ( $("textarea[name='Opis']:tinymce").html() )
			this.Opis.value = $("textarea[name='Opis']:tinymce").html();
		$(this).ajaxSubmit({
			target: '#divNames',
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (fObj.Jezik.selectedIndex==0)	{alert("Select language!"); fObj.Jezik.focus(); return false;}
				if (empty(fObj.Naslov))	{alert("Please enter a title!"); fObj.Naslov.focus(); return false;}
				return true;
			} // pre-submit callback
		});
		return false;
	});

	// resizing done in parent file
	window.customResize();

	// enable TinyMCE
	$("#HTMLeditor").tinymce({
		script_url : '<?php echo $js ?>/tiny_mce/tiny_mce.js',
		mode : "exact",
		//language : "si",
		elements : "HTMLeditor",
		element_format : "html",
		theme : "advanced",
		content_css : "editor_css.php",
		plugins : "safari,table,advimage,advhr,contextmenu",
		auto_cleanup_word : true,
		extended_valid_elements : "a[href|target|title],hr[size|noshade],font[face|size|color|style],div[class|align|style],span[class|style],ol[type],ul[type]",
		invalid_elements : "iframe,layer,script,link",
		file_browser_callback : "fileBrowserCallBack",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "none",
		theme_advanced_buttons1 : "bold,italic,underline,sub,sup,separator,undo,redo,separator,bullist,numlist,separator,link,unlink,separator,cleanup,removeformat",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_styles : "Koda=code;Citat=quote;Slika=imgcenter;Slika (levo)=imgleft;Slika (desno)=imgright"
	});
});
//-->
</script>

<FORM NAME="medDescription" ACTION="inc.php?Izbor=<?php echo $_GET['Izbor'] ?>&MediaID=<?php echo $_GET['MediaID'] ?>&ID=<?php echo $_GET['ID'] ?>" METHOD="post" onsubmit="return void(0);">
<?php if ( $Podatek ) : ?>
<INPUT NAME="OpisID" TYPE="Hidden" VALUE="<?php echo $Podatek->ID ?>">
<?php endif ?>
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD><B>Title:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="Naslov" MAXLENGTH="128" VALUE="<?php echo ($Podatek)? $Podatek->Naslov: "" ?>" TABINDEX="2" STYLE="width:100%;"></TD>
	<TD nowrap>&nbsp;Language:
<?php if ( $Podatek ) : ?>
	<INPUT TYPE="Hidden" NAME="Jezik" VALUE="<?php echo $Podatek->Jezik ?>"><b><?php echo $Podatek->Jezik=='' ? 'all' : $Podatek->Jezik ?></b>
<?php else : ?>
	<SELECT NAME="Jezik" SIZE="1">
		<OPTION VALUE="" DISABLED STYLE="background-color:whitesmoke;">Select...</OPTION>
<?php
$Jeziki = $db->get_results(
	"SELECT J.Jezik, J.Opis
	FROM Jeziki J
		LEFT JOIN MediaOpisi MO ON J.Jezik = MO.Jezik AND MO.MediaID = '". $db->escape($_GET['MediaID']) ."'
	WHERE
		J.Enabled=1". ((!$Podatek)? " AND MO.Jezik IS NULL": "")
	);
$All = $db->get_var(
	"SELECT count(*) ".
	"FROM MediaOpisi ".
	"WHERE MediaID = '".$_GET['MediaID']."'".
	"	AND Jezik IS NULL"
);
	if ( !($All) )
		echo "<OPTION VALUE=\"\"".(($Podatek->Jezik == "")? " SELECTED": "").">- all -</OPTION>\n";
	if ( $Jeziki ) foreach ( $Jeziki as $Jezik )
		echo "<OPTION VALUE=\"$Jezik->Jezik\"".($Podatek->Jezik == $Jezik->Jezik? " SELECTED": "").">$Jezik->Opis</OPTION>\n";
?>
	</SELECT>
<?php endif ?>
	</TD>
<?php if ( contains($ACL,"W") ) : ?>
	<TD ALIGN="right" valign="middle" NOWRAP>
	<a href="#" onclick="$('#divNames').load('inc.php?Izbor=medDescription&MediaID=<?php echo (int)$_GET['MediaID'] ?>');" title=" Zapri "><img src="pic/icon.remove.png"></a>
	<a href="#" onclick="$('form[name=medDescription]').submit();" title=" Save "><img src="pic/icon.accept.png"></a>
	&nbsp;
	</TD>
<?php endif ?>
</TR>
<TR>
	<TD COLSPAN="4"><B>Description:</B> <SPAN CLASS="f10 gry">(Copy/Paste from Word is not recommended)</SPAN></TD>
</TR>
<?php
	$Opis = $Podatek ? str_replace("\\\"","\"",$Podatek->Opis) : ""; // strip escaped quotes
	$Opis = $Podatek ? str_replace('&lt;','&amp;lt;',$Opis) : ""; // TinyMCE bugfix
	$Opis = $Podatek ? str_replace('&gt;','&amp;gt;',$Opis) : ""; // TinyMCE bugfix
	$Opis = preg_replace( "/(<img\s+src=\")(?!(?:http|data|\/))/i", '$1../', $Opis ); // adjust path for images
?>
<TR>
	<TD COLSPAN="4" VALIGN="top"><TEXTAREA NAME="Opis" ID="HTMLeditor" STYLE="height:140px;"><?php echo ($Podatek)? $Podatek->Opis: "" ?></TEXTAREA></TD>
</TR>
</TABLE>
</FORM>
<?php
} else {
	$Podatek = $db->get_row( "SELECT * FROM Media WHERE MediaID = " . (int)$_GET['MediaID'] );
	// get ACL
	if ( $Podatek )
		$ACL = userACL( $Podatek->ACLID );
	else
		$ACL = $ActionACL;

	echo "<SCRIPT LANGUAGE=\"JavaScript\" TYPE=\"text/javascript\">\n";
	echo "<!--\n";
	echo "function checkTxt(ID, Naziv) {\n";
	echo "\tif (confirm(\"Do you want to delete '\"+Naziv+\"'?\"))\n";
	echo "\t\tloadTo('Names','".$_SERVER['PHP_SELF']."?Izbor=".$_GET['Izbor']."&MediaID=".$_GET['MediaID']."&BrisiOpis='+ID);\n";
	echo "\treturn false;\n";
	echo "}\n";
	echo "//-->\n";
	echo "</SCRIPT>\n";

	echo "<TABLE BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	$Nazivi = $db->get_results(
		"SELECT ID, Naslov AS Naziv, Jezik ".
		"FROM MediaOpisi ".
		"WHERE MediaID = ".(int)$_GET['MediaID']." ".
		"ORDER BY Jezik"
	);

	if ( !$Nazivi ) {
		echo "<TR><TD ALIGN=\"center\">Ni tekstov!</TD></TR>\n";
	} else {
		foreach ( $Nazivi as $Naziv ) {
			echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
			echo "<TD WIDTH=\"8%\">[<FONT COLOR=\"Red\"><B>".(($Naziv->Jezik=="")? "all": $Naziv->Jezik)."</B></FONT>]</TD>\n";
			echo "<TD>".(contains($ACL,"W")? "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Names','inc.php?Izbor=medDescription&MediaID=".(int)$_GET['MediaID']."&ID=$Naziv->ID')\">": "");
			echo "<B>".left($Naziv->Naziv,45).((strlen($Naziv->Naziv)>45)? "...": "")."</B>";
			echo (contains($ACL,"W")? "</A>": "") . "</TD>\n";
			echo "<TD ALIGN=\"right\" WIDTH=\"16\">".(contains($ACL,"W")? "<A HREF=\"javascript:void(0);\" ONCLICK=\"javascript:checkTxt('$Naziv->ID','$Naziv->Naziv');\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" CLASS=\"icon\"></A>": "")."</TD>\n";
			echo "</TR>\n";
		}
	}
	echo "</TABLE>\n";
}
?>