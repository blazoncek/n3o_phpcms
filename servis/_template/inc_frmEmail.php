<?php
/*
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

setcookie("img_upload",'0');
setcookie("img_path", "");

if ( isset($_POST['Who']) ) {
	switch ( $_POST['Who'] ) {
		case "all":        $filter = 'Enabled <> 0';                       break;
		case "new":        $filter = 'Enabled <> 0 AND LastVisit IS NULL'; break;
		case "subscribed": $filter = 'Enabled <> 0 AND MailList <> 0';     break;
		case "moderators": $filter = 'Enabled <> 0 AND AccessLeel > 2';    break;
		default:           $filter = 'ID = '.(int)$_POST['ID'];            break;
	}
	$MailList = $db->get_results(
		"SELECT Name, Nickname, Email
		FROM frmMembers
		WHERE $filter"
		);

	$Body = $_POST['Body'];
	$Body = str_replace("&nbsp;", " ", $Body);
	$Body = str_replace("&scaron;", "š", $Body);
	$Body = str_replace("&Scaron;", "Š", $Body);

	$AltBody = preg_replace( "/<([\/]*)DIV([^>]*)>/i", "<\1p>", $Body );
	$AltBody = str_ireplace( '<li>', "* ", $AltBody );
	$AltBody = preg_replace( "/<([\/]*)([^>]*)>/i", "", $AltBody );

	$Body = "<style>" . file_get_contents('./mail.css') . "</style>\n" . $Body;

	if ( $MailList ) foreach ( $MailList as $User ) {
		$SMTPServer->AddAddress( $User->Email, $User->Name );
		$SMTPServer->Subject = AppName . " : " . $_POST['Subj'];
		$SMTPServer->AltBody = $AltBody;
		$SMTPServer->MsgHTML( $Body );
		if ( !$SMTPServer->Send() )
			echo "<!-- mail send error (".$User->Email.") -->\n";
		$SMTPServer->ClearAddresses();
	}
}

?>
<SCRIPT language="javascript" type="text/javascript">
<!--
function customResize() {
	// vertically resize edit child divs
	frame = $("#divContent").height(0).height( $("#divEdit").height() + $("#divEdit").position().top - $("#divContent").position().top );
	edit = $("#HTMLeditor").parent(); // TD element
	if ( edit.html() ) {
		edit.height( frame.height() + frame.position().top - edit.position().top - 16 );
		edit.width( frame.width() + frame.position().left - edit.position().left );
		$("#HTMLeditor").height( edit.innerHeight() - 10 );
		$("#HTMLeditor_ifr").height( $("#HTMLeditor").height() - $("#HTMLeditor_toolbargroup").height() );
	}
}

$(document).ready(function(){
	window.customResize = customResize;

	// bind to the form's submit event
	$("form[name='Vnos']").each(function(){
		$(this).submit(function(){
			this.Body.value = $("textarea[name='Body']").html();
			$(this).ajaxSubmit({
				target: '#divEdit',
				beforeSubmit: function( formDataArr, jqObj, options ) {
					var fObj = jqObj[0];	// form object
					if (empty(fObj.Subj))	{alert("Please enter subject!"); fObj.Subj.focus(); return false;}
					return true;
				} // pre-submit callback
			});
			return false;
		});
	});

	// resize content div
	window.customResize();

	// enable TinyMCE
	$("textarea[name='Body']").tinymce({
		script_url : '<?php echo $js ?>/tiny_mce/tiny_mce.js',
		mode : "exact",
		//language : "si",
		elements : "HTMLeditor",
		element_format : "html",
		theme : "advanced",
		content_css : "editor_css.php",
		plugins : "inlinepopups,safari,contextmenu",
		auto_cleanup_word : true,
		extended_valid_elements : "a[href|target|title],hr[size|noshade],font[face|size|color|style],div[class|align|style],span[class|style],ol[type],ul[type]",
		invalid_elements : "iframe,layer,script,link",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_buttons1 : "bold,italic,underline,sub,sup,separator,bullist,numlist,outdent,indent,blockquote,separator",
		theme_advanced_buttons1_add : "justifyleft,justifycenter,justifyright,justifyfull,separator,link,unlink",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_styles : ""
	});
});
//-->
</SCRIPT>
<DIV CLASS="subtitle">
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<td><div id="ToggleFrame" style="display:none;">&nbsp;<A HREF="javascript:toggleFrame()"><img src="pic/control.frame.gif" height="14" width="14" alt="Preklop celo/zmanjۡno okno" border="0" align="absmiddle" class="icon">&nbsp;List</a></div></td>
<?php if ( isset($_POST['Who']) ) : ?>
	<TD id="editNote"><B CLASS="red">Message sent!</B></TD>
<?php else : ?>
	<TD id="editNote" ALIGN="right"><?php echo $_GET['Izbor'] ?> - Send message&nbsp;</TD>
<?php endif ?>
</TR>
</TABLE>
</DIV>
<DIV ID="divContent" style="margin: 5px;">
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
	<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<?php if ( isset($_GET['ID']) ) : ?>
	<INPUT TYPE="Hidden" NAME="ID" VALUE="<?php echo $_GET['ID'] ?>">
	<INPUT TYPE="Hidden" NAME="Who" VALUE="single">
<?php else : ?>
	<TR>
		<TD ALIGN="right"><b>Send to</b>:&nbsp;</TD>
		<TD CLASS="a10" VALIGN="top">
		<INPUT TYPE="Radio" NAME="Who" CHECKED VALUE="subscribed">&nbsp;subscribed&nbsp;&nbsp;
		<INPUT TYPE="Radio" NAME="Who" value="moderators">&nbsp;moderators&nbsp;&nbsp;
		<INPUT TYPE="Radio" NAME="Who" VALUE="all">&nbsp;all&nbsp;&nbsp;
		<INPUT TYPE="Radio" NAME="Who" VALUE="new">&nbsp;new members&nbsp;&nbsp;
		</TD>
		<TD ALIGN="right"><INPUT TYPE="Submit" NAME="what" VALUE=" Send " CLASS="but"></TD>
	</TR>
<?php endif ?>
	<TR>
		<TD ALIGN="right"><b>Subject</b>:&nbsp;</TD>
		<TD><INPUT NAME="Subj" CLASS="Txt" MAXLENGTH="64" STYLE="width:100%;"></TD>
	</TR>
	<TR>
		<TD ALIGN="center" COLSPAN="3">
		<TEXTAREA NAME="Body" ID="HTMLeditor" STYLE="width:100%;height:100%;"></TEXTAREA>
		</TD>
	</TR>
	</TABLE>
</FORM>
<DIV>
