<?php
/*~ oddaj.php - post a message
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
| This file is part of N3O CMS (frontend).                                  |
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

// include application variables && settings framework
require_once( "../_application.php" );

include_once( "_queries.php" );

// check for blacklisted IPs
$IPBanList = $db->get_col("SELECT IP FROM frmBanList WHERE IP IS NOT NULL");
if ( count($IPBanList) ) foreach ( $IPBanList AS $IP ) {
	if ( right($IP,1)=="*" ) {
		$banIP    = left($IP, strchr("*",$IP)-1);
		$clientIP = left($_SERVER['REMOTE_ADDR'],strlen($banIP));
	} else {
		$banIP = $IP;
		$clientIP = $_SERVER['REMOTE_ADDR'];
	}
	if ( !strcmp($clientIP,$banIP) ) {
		// IP address is blacklisted
		// header( "Refresh:0; URL=../" );
		echo "<script language=\"javascript\" type=\"text/javascript\">window.close();</script>\n";
		die();
	}
}

if ( !$_SESSION['MemberID'] && isset($_COOKIE['Email']) && isset($_COOKIE['Geslo']) ) {
	header( "Refresh:0; URL=login.php?login&reload&referer=". urlencode($_SERVER['PHP_SELF']) .($_SERVER['QUERY_STRING']!="" ? "&querystring=". urlencode($_SERVER['QUERY_STRING']) : "") );
	die();
}

/*
if ( !$_SESSION['MemberID'] && !$AllowAnonymous ) {
	// anonymous posting not allowed
	echo "<script language=\"javascript\" type=\"text/javascript\">window.close();</script>\n";
	die();
}
*/

if ( !isset($_GET['Act']) ) $_GET['Act']="New";

$AccessLevel = 0;
$Edit        = "Full";
if ( $_SESSION['MemberID'] ) {
	updmemberlastvisit($_SESSION['MemberID']);
	$settings    = ParseMetadata($_SESSION['Settings'],',');
	$AccessLevel = $_SESSION['AccessLevel'];
	if ( isset($settings['Edit']) ) $Edit = $settings['Edit'];
	$getUser = getmember($_SESSION['MemberID']);
}

if ( isset($_GET['ID']) ) {
	$getMessage   = getmessage($_GET['ID']);
	$_GET['Nit']  = $getMessage->ForumID;
	$_GET['Tema'] = $getMessage->TopicID;
	$getMember    = getmember($getMessage->MemberID);
}

if ( isset($_GET['PvtID']) ) {
	$getPvtMessage = getpvtmessage($_GET['PvtID']);
	$_GET['Tema']  = $getPvtMessage->TopicID;
	$_GET['Nit']   = $getPvtMessage->ForumID;
	$getMember     = getmember($getPvtMessage->FromID);
}

if ( isset($_GET['Nit']) )  $getForum = getforum($_GET['Nit']);
if ( isset($_GET['Tema']) ) $getTopic = gettopic($_GET['Tema']);

$IsModerator = false;
if ( $_SESSION['MemberID'] && $getForum ) {
	$getModerator = getmoderators($getForum->ID,$_SESSION['MemberID']);
	if ( ($getModerator && $getModerator->Permissions > 0) )
		$IsModerator = true;
}

echo "<!DOCTYPE HTML>\n";
echo "<HTML>\n";
echo "<HEAD>\n";
$TitleText = $ForumTitle ." : Vpis sporočila";
include_once( "../_htmlheader.php" );
if ( fileExists("../_forumStyle.css") )
	echo "<LINK REL=\"stylesheet\" TYPE=\"text/css\" HREF=\"../_forumStyle.css\">\n";
else
	echo "<LINK REL=\"stylesheet\" TYPE=\"text/css\" HREF=\"style.css\">\n";
echo "<SCRIPT LANGUAGE=\"JavaScript\" TYPE=\"text/javascript\">window.focus();</SCRIPT>\n";

?>

<?php if ( $Edit == "Full" ) : ?>
<!-- tinyMCE -->
<script language="javascript" type="text/javascript" src="<?php echo $js ?>/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		mode : "exact",
		//language : "si",
		elements : "HTMLeditor",
		theme : "advanced",
		plugins : "table,preview,emoticons",
		content_css : "editor.css",
		extended_valid_elements : "a[href|target|title],img[src|border=0|alt|hspace|vspace|width|height|align],hr[size|noshade],font[face|size|color|style],div[class|align|style],span[class|style],blockquote[style|cite]",
		auto_cleanup_word : true,
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_buttons1 : "bold,italic,underline,sub,sup,separator,bullist,numlist,outdent,indent,separator",
		theme_advanced_buttons1_add : "justifyleft,justifycenter,justifyright,justifyfull,separator,hr,separator,table,link,unlink,separator,code,removeformat,separator,emoticons",
		theme_advanced_buttons2 : "", // "fontselect,fontsizeselect,forecolor,backcolor,separator",
		theme_advanced_buttons3 : "",
		theme_advanced_styles : "" // Theme specific setting CSS classes
	});
</script>
<!-- /tinyMCE -->
<?php else : ?>
<script language="javascript" type="text/javascript" src="<?php echo $js ?>/bbtextedit.js"></script>
<script language="javascript" type="text/javascript">
	bbeditFieldID = "HTMLeditor";
</script>
<?php endif ?>

<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
function validate(fObj) {
<?php if ( $Edit == "Full" ) : ?>
	// trigger executes after validate funcion is called so force it here
	tinyMCE.triggerSave();
<?php endif ?>
//	if (fObj.Ime  && empty(fObj.Ime)) {alert("Prosim, vpišite svoje ime!");fObj.UserName.focus();return false;}
//	if (fObj.Od   && !emailOK(fObj.Od)) {alert("Napačen email naslov!");fObj.Od.focus();return false;}
//	if (fObj.Nit  && empty(fObj.Nit)) {alert("Prosim, vpišite nit!");fObj.Nit.focus();return false;}
	if (fObj.Tema && empty(fObj.Tema)) {alert("Prosim, vpišite temo!");fObj.Tema.focus();return false;}
	if (fObj.Za   && !emailOK(fObj.Za)) {alert("Napačen email naslov!");fObj.Za.focus();return false;}
	if (fObj.Body && empty(fObj.Body)) {alert("Prosim, vpišite besedilo!");return false;}
	if (fObj.Poll && fObj.Poll.checked && empty(fObj.Q)) {alert("Prosim vpišite vprašanje ankete!");fObj.Q.focus();return false;}
	if (fObj.Poll && fObj.Poll.checked && empty(fObj.A1)) {alert("Anketa mora vsebovati vsaj odgovor 1 in 2!");fObj.A1.focus();return false;}
	if (fObj.Poll && fObj.Poll.checked && empty(fObj.A2)) {alert("Anketa mora vsebovati vsaj odgovor 1 in 2!");fObj.A2.focus();return false;}
<?php if ( $_GET['Act'] == "New" && $getForum && $getForum->AllowFileUploads ) : ?>
	var fileName = fObj.File.value;
	// does it contain any invalid characters?
	for (i=fileName.lastIndexOf("\\")+1; i<fileName.length; i++) {
		if (!((fileName.charAt(i) >= "@" && fileName.charAt(i) <= "~") ||
			  (fileName.charAt(i) >= "0" && fileName.charAt(i) <= "9") ||
			  fileName.charAt(i) == "." || fileName.charAt(i) == "-" || fileName.charAt(i) == "(" || fileName.charAt(i) == ")") ||
			fileName.charAt(i) == "\\" || fileName.charAt(i) == "|") {
			alert("Ime datoteke vsebuje neveljavne znake!");
			return false;
		}
	}
<?php endif ?>
	return true;
}
</SCRIPT>
</HEAD>

<BODY style="background-color:<?php echo $BackgColor ?>;">
<TABLE ALIGN="center" BORDER=0 CELLPADDING=2 CELLSPACING=0 WIDTH="100%" HEIGHT="100%">
<?php if ( !($_SESSION['MemberID'] || $AllowAnonymous || contains("Rep,Fwd", $_GET['Act'])) ) : ?>
<TR>
	<TD ALIGN="center" HEIGHT="99%" VALIGN="middle">
	Če želite oddati sporočilo v <?php echo $AppName ?> Diskusije, se morate najprej prijaviti!<BR>
	To lahko storite tudi <A HREF="javascript:loginOpen('login.cfm?login&reload');"><B>tule</B></A>.
	</TD>
</TR>
<?php elseif ( !isset($_GET['Nit']) ) : ?>
<TR>
	<TD ALIGN="center" HEIGHT="99%" VALIGN="middle">
	Napaka pri vstopu v oddajo sporočila!
	</TD>
</TR>
<?php elseif ( isDate($getUser->SignIn) && compareDate($getUser->SignIn, now()) < 4 ) : ?>
<TR>
	<TD ALIGN="center" HEIGHT="99%" VALIGN="middle">
	<B>Prvo sporočilo lahko oddate šele 3 dni po včlanitvi!</B><BR><BR>
	Prosimo, spoznajte se z diskusijami, jih prebrskajte ali poiščite morebitna stara sporočila z vsebino, ki vas zanima.
	</TD>
</TR>
<?php else : ?>
<FORM NAME="Vnos" ACTION="_oddaj.php?Act=<?php echo $_GET['Act'] ?>" METHOD="post" ENCTYPE="multipart/form-data" ONSUBMIT="return validate(this);">
<INPUT NAME="NitID" TYPE="Hidden" VALUE="<?php echo $_GET['Nit'] ?>">
<TR>
	<TD ALIGN="center" HEIGHT="99%" VALIGN="top">
	<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">

 	<?php if ( contains("New,Rep,Fwd",$_GET['Act']) && !$_SESSION['MemberID'] ) : ?>
	<TR>
		<TD WIDTH="20%"><B>Pošilja</B> (ime)<B>:</B></TD>
		<TD><INPUT TYPE="Text" NAME="Ime" SIZE="20" MAXLENGTH="50" VALUE="" STYLE="width:99%;border:silver solid 1px;"></TD>
	</TR>
	<TR>
		<TD>(email)</TD>
		<TD><INPUT TYPE="Text" NAME="Od" SIZE="20" MAXLENGTH="64" VALUE="" STYLE="width:99%;border:silver solid 1px;"></TD>
	</TR>
	<?php endif ?>

 	<?php if ( $_GET['Act'] == "New" ) : ?>

		<?php if ( !isset($_GET['Tema']) ) : ?>
			<?php $height=400; ?>
	<TR>
		<TD><B>Tema:</B></TD>
		<TD COLSPAN="2"><INPUT TYPE="Text" NAME="Tema" SIZE="50" MAXLENGTH="50" VALUE="" STYLE="border:silver solid 1px;"></TD>
<!-- ANKETA -->
			<?php if ( $getForum->PollEnabled || $IsModerator ) : ?>
		<TD ALIGN="right" VALIGN="top" NOWRAP>Anketa:
		<INPUT NAME="Poll" TYPE="Checkbox" ONMOUSEUP="this.checked?window.moveBy(0,125):window.moveBy(0,-125),this.checked?window.resizeBy(0,-250):window.resizeBy(0,250),this.checked?idPoll.style.display='none':idPoll.style.display='',void 0;">&nbsp;
		</TD>
			<?php endif ?>
<!-- ANKETA -->
	</TR>

<!-- ANKETA -->
			<?php if ( $getForum->PollEnabled || $IsModerator ) : ?>
	<TR>
		<TD COLSPAN="3">
		<TABLE ID="idPoll" CELLPADDING="0" CELLSPACING="1" WIDTH="100%" STYLE="display:none;">
		<TR>
			<TD ALIGN="right" CLASS="a10" VALIGN="top"><B>Vprašanje:</B>&nbsp;<BR><FONT COLOR="darkgray" CLASS="a10">(max 510&nbsp; znakov)</FONT>&nbsp;</TD>
			<TD><TEXTAREA NAME="Q" COLS="70" ROWS="4" WRAP="virtual" STYLE="border:silver solid 1px;font-size:9px;"></TEXTAREA></TD>
		</TR>
		<?php for ( $i=1; $i<=10; $i++ ) : ?>
		<TR>
			<TD ALIGN="right" CLASS="a10"><?php if ( $i<3 ) : ?><B><?php endif ?>Odg. <?php echo $i ?>:<?php if ( $i<3 ) : ?></B><?php endif ?>&nbsp;</TD>
			<TD><INPUT TYPE="Text" NAME="A<?php echo $i ?>" SIZE="70" MAXLENGTH="127" VALUE="" STYLE="border:silver solid 1px;font-size:9px;"></TD>
		</TR>
		<?php endfor ?>
		<TR><TD COLSPAN="2"><HR SIZE="1" NOSHADE></TD></TR>
		</TABLE>
		</TD>
	</TR>
			<?php endif ?>
<!-- ANKETA -->

		<?php else : ?>
			<?php $height=425; ?>
	<INPUT NAME="TemaID" TYPE="Hidden" VALUE="<?php echo $_GET['Tema'] ?>">
		<?php endif ?>

	<TR>
		<TD VALIGN="top" CLASS="a10" NOWRAP>Tip:<SELECT NAME="Icon" SIZE="1" CLASS="a10">
		<OPTION VALUE=""<?php if ( isset($_GET['ID']) && $getMessage->Icon == "" ) : ?> SELECTED<?php endif ?>>Brez posebnosti
		<OPTION VALUE="question"<?php if ( isset($_GET['ID']) && $getMessage->Icon == "question" ) : ?> SELECTED<?php endif ?>>Vprašanje
		<OPTION VALUE="lightbulb"<?php if ( isset($_GET['ID']) && $getMessage->Icon == "lightbulb" ) : ?> SELECTED<?php endif ?>>Nasvet
		<OPTION VALUE="note"<?php if ( isset($_GET['ID']) && $getMessage->Icon == "note" ) : ?> SELECTED<?php endif ?>>Zabeležka
		<OPTION VALUE="statement"<?php if ( isset($_GET['ID']) && $getMessage->Icon == "statement" ) : ?> SELECTED<?php endif ?>>Opozorilo
		<OPTION VALUE="thumbsdown"<?php if ( isset($_GET['ID']) && $getMessage->Icon == "thumbsdown" ) : ?> SELECTED<?php endif ?>>Razočaranje
		<OPTION VALUE="thumbsup"<?php if ( isset($_GET['ID']) && $getMessage->Icon == "thumbsup" ) : ?> SELECTED<?php endif ?>>Vzpodbuda
		<OPTION VALUE="flag"<?php if ( isset($_GET['ID']) && $getMessage->Icon == "flag" ) : ?> SELECTED<?php endif ?>>Zastavica
		<OPTION VALUE="tools"<?php if ( isset($_GET['ID']) && $getMessage->Icon == "tools" ) : ?> SELECTED<?php endif ?>>Drži kot pribito
		</SELECT></TD>
		<TD VALIGN="top" CLASS="a10">
		<?php if ( $getForum->AllowFileUploads ) : ?>
		<B>Datoteka:</B>&nbsp;
		<INPUT NAME="File" TYPE="File" STYLE="font-size:10px;border:silver solid 1px;"><br>
		(<?php if ( $getForum->MaxUploadSize > 0 ) : ?>maks. <B><?php echo $getForum->MaxUploadSize ?></B>kB<?php endif ?><?php if ( $getForum->MaxUploadSize > 0 && $getForum->UploadType != "" ) : ?> <?php endif ?><?php if ( $getForum->UploadType != "" ) : ?>tip: <B><?php echo $getForum->UploadType ?></B><?php endif ?>)
		<?php endif ?>
		</TD>
		<TD ALIGN="right"><INPUT TYPE="Submit" VALUE=" &nbsp;&nbsp;Oddaj&nbsp;&nbsp; " CLASS="but"></TD>
	</TR>
		<?php $Bes=""; ?>
		<?php if ( isset($_GET['ID']) ) : ?>
	 		<?php $Bes = preg_replace("/<P[^>]*>|<DIV[^>]*>/i", "", $getMessage->MessageBody); ?>
			<?php $Bes = "<BLOCKQUOTE CITE=\"". $getMember->Nickname ."\" STYLE=\"font-size:10px;border: ". $FrameColor ." 1px solid;background-color:". $BackgColor .";margin:5px 15px;padding:5px;\">". $Bes ."</BLOCKQUOTE><P>&nbsp;</P>"; ?>
		<?php endif ?>
	<TR>
		<TD COLSPAN="4">
		<TEXTAREA NAME="Body" ID="HTMLeditor" COLS="40" ROWS="8" STYLE="width:100%;height:<?php echo $height ?>px;"><?php echo $Bes ?></TEXTAREA>
		</TD>
	</TR>
	<TR>
		<TD ALIGN="right" COLSPAN="3" VALIGN="baseline" CLASS="a10" NOWRAP>
		<?php if ( $_SESSION['MemberID'] ) : ?>
		Podpis:	<INPUT TYPE="CheckBox" NAME="Sign" CHECKED>
		<?php endif ?>
		</TD>
	</TR>
		<?php if ( $getForum->ApprovalRequired ) : ?>
	<TR>
		<TD ALIGN="right" CLASS="a10" VALIGN="top"><B>Opozorilo:</B>&nbsp;</TD>
		<TD COLSPAN="3" CLASS="a10">
		Vsa sporočila, oddana v tej niti, zahtevajo odobritev urednika ali moderatorja, preden se objavijo!
		</TD>
	</TR>
		<?php endif ?>

 	<?php elseif ( $_GET['Act'] == "Edt" ) : ?>

	<INPUT TYPE="Hidden" NAME="ID" VALUE="<?php echo $getMessage->ID ?>">
	<INPUT TYPE="Hidden" NAME="NitID" VALUE="<?php echo $getMessage->ForumID ?>">
	<INPUT TYPE="Hidden" NAME="TemaID" VALUE="<?php echo $getMessage->TopicID ?>">
<?php /*
<!-- ANKETA -->
	<?php if ( $getTopic->Votes != "" ) : /-* need to detect if it is a first message *-/ ?>
		<?php $getPoll = getpoll($getMessage->TopicID); ?>
	<TR>
		<TD COLSPAN="4">
		<INPUT NAME="Poll" TYPE="Hidden" VALUE="">
		<TABLE ID="idPoll" ALIGN="center" CELLPADDING="0" CELLSPACING="0" WIDTH="200">
		<TR>
			<TD ALIGN="right" VALIGN="top"><B>Vprašanje:</B>&nbsp;<BR><FONT COLOR="darkgray" CLASS="a10">(max 510&nbsp; znakov)</FONT>&nbsp;</TD>
			<TD><TEXTAREA NAME="Q" COLS="70" ROWS="4" WRAP="virtual" STYLE="border:silver solid 1px;font-size:9px;"><?php echo $getPoll->Question ?></TEXTAREA></TD>
		</TR>
		<?php for ( $i=1; $i<=10; $i++ ) : ?>
		<TR><TD COLSPAN="2" HEIGHT="5"></TD></TR>
		<TR>
			<TD ALIGN="right"><?php if ( $i<3 ) : ?><B><?php endif ?>Odg. <?php echo $i ?>:<?php if ( $i<3 ) : ?></B><?php endif ?>&nbsp;</TD>
			<TD><INPUT TYPE="Text" NAME="A<?php echo $i ?>" SIZE="70" MAXLENGTH="127" VALUE="<?php eval('echo $getPoll->A'. $i .';') ?>" STYLE="border:silver solid 1px;font-size:9px;"></TD>
		</TR>
		<?php endfor ?>
		</TABLE>
		</TD>
	</TR>
	<?php endif ?>
<!-- ANKETA -->
*/ ?>
	<TR>
		<TD ALIGN="right"><INPUT TYPE="Submit" VALUE=" &nbsp;&nbsp;Oddaj&nbsp;&nbsp; " CLASS="but"></TD>
	</TR>
	<TR>
		<TD>
		<?php $Bes = preg_replace("/<BLOCKQUOTE([^>]*)>/i", "<BLOCKQUOTE STYLE=\"font-size:10px;border:". $FrameColor ." 1px solid;background-color:". $BackgColor .";margin:5px 15px;padding:5px;\">", $getMessage->MessageBody); ?>
		<TEXTAREA NAME="Body" ID="HTMLeditor" COLS="40" ROWS="8" STYLE="width:100%;height:440px;"><?php echo $Bes ?></TEXTAREA>
		</TD>
	</TR>

 	<?php elseif ( $_GET['Act'] == "Pvt" ) : ?>

	<INPUT TYPE="Hidden" NAME="NitID" VALUE="<?php echo $_GET['Nit'] ?>">
	<INPUT TYPE="Hidden" NAME="TemaID" VALUE="<?php echo $_GET['Tema'] ?>">
		<?php if ( isset($_GET['PvtID']) && (int)$_GET['PvtID'] ) : ?>
	<INPUT TYPE="Hidden" NAME="PvtID" VALUE="<?php echo $_GET['PvtID'] ?>">
	<INPUT TYPE="Hidden" NAME="ToID" VALUE="<?php echo $getPvtMessage->FromID ?>">
			<?php if ( left($getPvtMessage->MessageSubject,3) == "RE:" ) : ?>
				<?php $Bes = $getPvtMessage->MessageSubject ?>
			<?php else : ?>
				<?php $Bes = left("RE: ". $getPvtMessage->MessageSubject,64) ?>
			<?php endif ?>
		<?php elseif ( isset($_GET['ToID']) && (int)$_GET['ToID'] ) : ?>
	<INPUT TYPE="Hidden" NAME="ToID" VALUE="<?php echo $_GET['ToID'] ?>">
			<?php $Bes = "" ?>
		<?php else : ?>
	<INPUT TYPE="Hidden" NAME="ToID" VALUE="<?php echo $getMessage->MemberID ?>">
			<?php $Bes = left($getTopic->TopicName,64) ?>
		<?php endif ?>
	<TR>
		<TD WIDTH="14%"><B>Zadeva:</B></TD>
		<TD COLSPAN="2"><INPUT TYPE="Text" NAME="Tema" SIZE="30" MAXLENGTH="64" VALUE="<?php echo $Bes ?>" STYLE="border:silver solid 1px;width:100%;"></TD>
		<TD ALIGN="right"><INPUT TYPE="Submit" VALUE=" &nbsp;&nbsp;Oddaj&nbsp;&nbsp; " CLASS="but"></TD>
	</TR>
	<TR>
		<TD COLSPAN="4">
		<?php if ( isset($_GET['ID']) && (int)$_GET['ID'] ) : ?>
			<?php $Bes="<BLOCKQUOTE CITE=\"". $getMember->Nickname. "\" STYLE=\"font-size:10px;border:". $FrameColor ." 1px solid;background-color:". $BackgColor .";margin:5px 15px;padding:5px;\">". $getMessage->MessageBody ."</BLOCKQUOTE><P>&nbsp;</P>" ?>
		<?php elseif ( isset($_GET['PvtID']) && (int)$_GET['PvtID'] ) : ?>
			<?php $Bes="<BLOCKQUOTE CITE=\"". $getMember->Nickname ."\" STYLE=\"font-size:10px;border:". $FrameColor ." 1px solid;background-color:". $BackgColor .";margin:5px 15px;padding:5px;\">". $getPvtMessage->MessageBody ."</BLOCKQUOTE><P>&nbsp;</P>" ?>
		<?php else : ?>
			<?php $Bes = "" ?>
		<?php endif ?>
		<TEXTAREA NAME="Body" ID="HTMLeditor" COLS="40" ROWS="8" STYLE="width:100%;height:440px;"><?php echo $Bes ?></TEXTAREA>
		</TD>
	</TR>

 	<?php elseif ( $_GET['Act'] == "Rep" ) : ?>

	<INPUT TYPE="Hidden" NAME="ID" VALUE="<?php echo $getMessage->ID ?>">
	<INPUT TYPE="Hidden" NAME="Za" VALUE="<?php echo $getMessage->UserEmail ?>">
	<TR>
		<TD WIDTH="20%">Zadeva:</TD>
		<TD><INPUT TYPE="Text" NAME="Subj" VALUE="RE: [<?php echo $ForumTitle ?>] <?php echo $getTopic->TopicName ?>" STYLE="border:silver solid 1px;width:99%;"></TD>
		<TD ALIGN="right" WIDTH="20%"><INPUT TYPE="Submit" VALUE=" &nbsp;&nbsp;Oddaj&nbsp;&nbsp; " CLASS="but"></TD>
	</TR>
		<?php
		if ( preg_match("/<[^>]*>/i", left($getMessage->MessageBody, 300)) != 0 ) {
			$Bes = str_replace("\n", " ", $getMessage->MessageBody);
			$Bes = preg_replace("/<\/P[^>]*>|<BR>|<\/DIV[^>]*>/i", "\n", $Bes);
			$Bes = preg_replace("/<[^>]*>/", "", $Bes);
		} else {
			$Bes = preg_replace("/<[^>]*>/", "", str_replace("<BR>", "\n", $getMessage->MessageBody));
		}
		?>
	<TR>
		<TD BGCOLOR="<?php echo $BckHiColor ?>" COLSPAN="3">
		<SPAN CLASS="a10">Napisal: <B><?php echo $getMember->Nickname ?></B>, <?php echo formatDate($getMessage->MessageDate,"j.n.y \o\b H:i"); ?></SPAN>
		<TEXTAREA COLS="45" ROWS="5" READONLY CLASS="a10" STYLE="width:99%;"><?php echo $Bes ?></TEXTAREA>
		</TD>
	</TR>
	<TR>
		<TD COLSPAN="3">
		<TEXTAREA NAME="Body" ID="HTMLeditor" COLS="40" ROWS="8" STYLE="width:100%;height:300px;"></TEXTAREA>
		<SPAN CLASS="a10">Originalno sporočilo bo priloženo.</SPAN>
		</TD>
	</TR>

 	<?php elseif ( $_GET['Act'] == "Fwd" ) : ?>

	<INPUT TYPE="Hidden" NAME="ID" VALUE="<?php echo $getMessage->ID ?>">
	<TR>
		<TD WIDTH="20%"><B>Prejemnik:</B></TD>
		<TD><INPUT TYPE="Text" NAME="Za" VALUE="" STYLE="border:silver solid 1px;width:99%;"></TD>
		<TD ALIGN="right" WIDTH="20%"><INPUT TYPE="Submit" VALUE=" &nbsp;&nbsp;Oddaj&nbsp;&nbsp; " CLASS="but"></TD>
	</TR>
	<TR>
		<TD>Zadeva:</TD>
		<TD COLSPAN="2"><INPUT TYPE="Text" NAME="Subj" VALUE="FW: [<?php echo $ForumTitle ?>] <?php echo $getTopic->TopicName ?>" READONLY STYLE="border:silver solid 1px;width:99%;"></TD>
	</TR>
		<?php
		if ( preg_match("/<[^>]*>/i", left($getMessage->MessageBody, 300)) != 0 ) {
			$Bes = str_replace("\n", " ", $getMessage->MessageBody);
			$Bes = preg_replace("/<\/P[^>]*>|<BR>|<\/DIV[^>]*>/i", "\n", $Bes);
			$Bes = preg_replace("/<[^>]*>/", "", $Bes);
		} else {
			$Bes = preg_replace("/<[^>]*>/", "", str_replace("<BR>", "\n", $getMessage->MessageBody));
		}
		?>
	<TR>
		<TD COLSPAN="3">
		<SPAN CLASS="a10">Napisal: <B><?php echo $getMember->Nickname ?></B>, <?php echo formatDate($getMessage->MessageDate,"j.n.y \o\b H:i"); ?></SPAN>
		<TEXTAREA COLS="45" ROWS="5" READONLY CLASS="a10" STYLE="width:99%;border:silver solid 1px;"><?php echo $Bes ?></TEXTAREA>
		</TD>
	</TR>
	<TR>
		<TD COLSPAN="3">
		<TEXTAREA NAME="Body" ID="HTMLeditor" COLS="40" ROWS="8" STYLE="width:100%;height:320px;"></TEXTAREA>
		<SPAN CLASS="a10">Originalno sporočilo bo priloženo.</SPAN>
		</TD>
	</TR>

	<?php endif ?>

	</TABLE>
	</TD>
</TR>
</FORM>
<?php endif ?>
</TABLE>
</BODY>
</HTML>
