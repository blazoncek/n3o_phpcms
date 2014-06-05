<?php
/*~ zasebno.php - private messges
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

if ( !$_SESSION['MemberID'] ) {
	echo "<script language=\"javascript\" type=\"text/javascript\">window.close();</script>\n";
	die();
}

updmemberlastvisit($_SESSION['MemberID']);
$getMember = getmember($_SESSION['MemberID']);
$settings = ParseMetadata($getMember->Settings);
$Edit  = isset($settings['Edit'])  ? $settings['Edit']  : "Full";

if ( isset($_GET['Delete']) && (int)$_GET['Delete'] )     updpvtmessages($_SESSION['MemberID'],$_GET['Delete'],"delete");
if ( isset($_GET['UnDelete']) && (int)$_GET['UnDelete'] ) updpvtmessages($_SESSION['MemberID'],$_GET['UnDelete'],"undelete");
if ( contains($_SERVER['QUERY_STRING'], "Purge") )        delpvtmessages($_SESSION['MemberID']);

if ( !isset($_GET['Sort']) )    $_GET['Sort'] = "Date";
if ( !isset($_GET['SortDir']) ) $_GET['SortDir'] = ($_GET['Sort']=="Date" ? "Down" : "Up");

echo "<!DOCTYPE HTML>\n";
echo "<HTML>\n";
echo "<HEAD>\n";
$TitleText = $ForumTitle ." : Zasebna sporočila";
include_once( "../_htmlheader.php" );
include_once( "_forumheader.php" );
echo "<SCRIPT LANGUAGE=\"JavaScript\" TYPE=\"text/javascript\">\n";
echo "<!--\n";
echo "window.focus();\n";
echo "//-->\n";
echo "</SCRIPT>\n";
?>
<?php if ( contains($_SERVER['QUERY_STRING'], "Reply") ) : ?>
	<?php if ( $Edit=="Full" ) : ?>
<!-- tinyMCE -->
<script language="javascript" type="text/javascript" src="<?php echo $js ?>/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		mode : "exact",
		//language : "si",
		elements : "HTMLeditor",
		theme : "advanced",
		plugins : "table,preview,emoticons",
		content_css : "editor_content.css",
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
<?php endif ?>
<?php
echo "</HEAD>\n";
?>

<?php if ( contains($_SERVER['QUERY_STRING'],"Reply") ) : ?>
	<?php
	$getPvtMessage = getpvtmessage($_SESSION['MemberID'],$_GET['ID']);
	$_GET['Tema']  = $getPvtMessage->TopicID>
	$_GET['Nit']   = $getPvtMessage->ForumID>
	$getFromMember = getmember($getPvtMessage->FromID);
	?>
<BODY>
<FORM NAME="Vnos" ACTION="_oddaj.php?Act=Pvt" METHOD="post" ENCTYPE="multipart/form-data">
<TABLE BGCOLOR="<?php echo $BackgColor ?>" BORDER="0" CELLPADDING="1" CELLSPACING="0" HEIGHT="100%" WIDTH="100%">
<TR>
	<TD ALIGN="center" VALIGN="top">
	<INPUT TYPE="Hidden" NAME="NitID" VALUE="<?php echo $_GET['Nit'] ?>">
	<INPUT TYPE="Hidden" NAME="TemaID" VALUE="<?php echo $_GET['Tema'] ?>">
	<INPUT TYPE="Hidden" NAME="FromID" VALUE="<?php echo $_SESSION['MemberID'] ?>">
	<INPUT TYPE="Hidden" NAME="PvtID" VALUE="<?php echo $_GET['ID'] ?>">
	<INPUT TYPE="Hidden" NAME="ToID" VALUE="<?php echo $getPvtMessage->FromID ?>">
	<?php if ( left($getPvtMessage->MessageSubject,3) == "RE:" ) : ?>
		<?php $Bes = $getPvtMessage->MessageSubject; ?>
	<?php else : ?>
		<?php $Bes = left("RE: ". $getPvtMessage->MessageSubject,64); ?>
	<?php endif ?>
	<INPUT TYPE="Hidden" NAME="Tema" VALUE="<?php echo $Bes ?>">
	<?php if ( isset($_GET['ID']) && (int)$_GET['ID'] ) : ?>
		<?php $Bes = $getPvtMessage->MessageBody; ?>
 		<?php $Bes = preg_replace("/<P[^>]*>|<DIV[^>]*>/i","",$Bes); ?>
		<?php $Bes = "<BLOCKQUOTE CITE=\"". $getFromMember->Nickname ."\" STYLE=\"font-size:10px;border:1px solid ". $FrameColor. ";background-color:". $BackgColor .";margin:5px 15px;padding:5px;'>" .$Bes ."</BLOCKQUOTE><P></P>"; ?>
	<?php else : ?>
		<?php $Bes = ""; ?>
	<?php endif ?>
	<TEXTAREA NAME="Body" ID="HTMLeditor" COLS="40" ROWS="8" STYLE="width:100%;height:370px;"><?php echo $Bes ?></TEXTAREA>
	</TD>
</TR>
<TR>
	<TD ALIGN="center">
	<INPUT TYPE="Hidden" NAME="ID" VALUE="<?php echo $_GET['ID'] ?>">
	<INPUT TYPE="Submit" VALUE=" &nbsp;&nbsp;Oddaj&nbsp;&nbsp; " CLASS="but">
	</TD>
</TR>
</TABLE>
</FORM>
</BODY>

<?php elseif ( isset($_GET['ID']) /*&& (int)$_GET['ID']*/ ) : ?>

	<?php updpvtmessages($_SESSION['MemberID'],$_GET['ID'],"read"); ?>
	<?php $getPvtMessage = getpvtmessage($_SESSION['MemberID'],$_GET['ID']); ?>

<BODY>
<SCRIPT LANGUAGE="JavaScript">
<!--
window.top.frames.emailList.document.location.href='<?php echo $_SERVER['PHP_SELF'] ?>?Sort=<?php echo $_GET['Sort'] ?>&SortDir=<?php echo $_GET['SortDir'] ?>';
// -->
</SCRIPT>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" HEIGHT="100%" WIDTH="100%">
<TR BGCOLOR="<?php echo $BackgColor ?>">
	<TD ALIGN="center">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" HEIGHT="100%" WIDTH="100%">
	<TR BGCOLOR="<?php echo $BckLoColor ?>">
		<TD CLASS="a10">&nbsp;Od: <B><?php if ( (int)$getPvtMessage->FromID ) : ?><A HREF="javascript:window.top.opener.document.location.href='clani.php?ID=<?php echo $getPvtMessage->FromID ?>', void (0);"><FONT COLOR="<?php echo $TextColor ?>"><?php echo $getPvtMessage->FromNickName ?></FONT></A><?php endif ?></B></TD>
		<TD ALIGN="right" CLASS="a10"><?php if ( (int)$getPvtMessage->TopicID ) : ?>&nbsp;Tema: <B><A HREF="javascript:window.top.opener.document.location.href='./?Nit=<?php echo $getPvtMessage->ForumID ?>&Tema=<?php echo $getPvtMessage->TopicID ?>', void (0);"><FONT COLOR="<?php echo $TextColor ?>"><?php echo $getPvtMessage->TopicName ?></FONT></A></B>&nbsp;<?php endif ?></TD>
	</TR>
	<TR BGCOLOR="<?php echo $BckLoColor ?>">
		<TD CLASS="a10" COLSPAN="2">&nbsp;Zadeva: <B><?php echo $getPvtMessage->MessageSubject ?></B></TD>
	</TR>
	<?php
	if ( preg_match("/<P[^>]*>|<DIV/i",left($getPvtMessage->MessageBody,100)) ) {
 		$Bes = preg_replace("/<P([^>]*)>/i", '<DIV$1>', $getPvtMessage->MessageBody);
		$Bes = str_replace("</P>","</DIV>", $Bes);
	} else {
		$Bes = str_replace("<BR>", chr(13) . chr(10), $getPvtMessage->MessageBody);
		$Bes = str_replace(chr(13) . chr(10), "<BR>" . chr(13) . chr(10), $Bes);
	}
	$Bes = ReplaceSmileys($Bes, "../pic/");
	$Bes = preg_replace("/ +STYLE=\"[^\"]*\"/i", "", $Bes);
	$Bes = preg_replace("/<BLOCKQUOTE +CITE=\"([^\"]*)\"([^>]*)>/i", "<P STYLE=\"margin-left:25px;\"><B>".'$1'."</B> je napisal(a):</P><BLOCKQUOTE>", $Bes);
	$Bes = preg_replace("/<([\/]*)BLOCKQUOTE([^>]*)>/i", "<".'$1'."BLOCKQUOTE>", $Bes);
	$Bes = str_replace("<P STYLE=\"margin-left:25px;\"><B></B> je napisal(a):</P>", "", $Bes);
	$Bes = preg_replace("/<A HREF=\"([^\"]*)\"([^>]*)>/i","<A HREF=\"".'$1'."\" TARGET=\"_blank\"".'$2'.">", $Bes);
	?>
	<TR BGCOLOR="<?php echo $BckHiColor ?>">
		<TD COLSPAN="2" VALIGN="top">
		<DIV STYLE="padding:5px;overflow-y:scroll;height:340px;"><?php echo $Bes ?></DIV>
		</TD>
	</TR>
	<TR BGCOLOR="<?php echo $BckLoColor ?>">
		<TD ALIGN="center" COLSPAN="2" HEIGHT="12">
		<?php if ( (int)$_GET['ID']>0 ) : ?>
		<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Reply&ID=<?php echo $getPvtMessage->ID ?>"><IMG SRC="px/reply.GIF" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0"> Odgovori</A> |
		<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Sort=<?php echo $_GET['Sort'] ?>&SortDir=<?php echo $_GET['SortDir'] ?>&<?php if ( $getPvtMessage->IsDeleted ) : ?>Un<?php endif ?>Delete=<?php echo $_GET['ID'] ?>" TARGET="emailList"><IMG SRC="px/note-del.gif" WIDTH=12 HEIGHT=12 ALIGN="absmiddle" ALT="" BORDER="0"> <?php if ( $getPvtMessage->IsDeleted ) : ?>Vrni izbrisano<?php else : ?>Izbriši<?php endif ?></A> |
		<?php endif ?>
		<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Sort=<?php echo $_GET['Sort'] ?>&SortDir=<?php echo $_GET['SortDir'] ?>&Purge" TARGET="emailList"><IMG SRC="px/trash.gif" WIDTH=12 HEIGHT=12 ALIGN="absmiddle" ALT="" BORDER="0"> Počisti nabiralnik</A>
		</TD>
	</TR>
	</TABLE>
	</TD>
</TR>
</TABLE>
</BODY>

<?php elseif ( contains($_SERVER['QUERY_STRING'],"Top") || contains($_SERVER['QUERY_STRING'],"Sort") ) : ?>

	<?php $getPvtMessages = getpvtmessages($_SESSION['MemberID'],$_GET['Sort'],$_GET['SortDir']); ?>

<BODY>
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR BGCOLOR="<?php echo $BackgColor ?>">
	<TD ALIGN="center">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="100%" HEIGHT="100%">
	<?php if ( count($getPvtMessages) ) : ?>
	<TR BGCOLOR="<?php echo $BckLoColor ?>">
		<TD></TD>
		<TD>&nbsp;<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Sort=From<?php if ( $_GET['Sort']=='From' && $_GET['SortDir']!='Down' ) : ?>&SortDir=Down<?php endif ?>"><FONT COLOR="<?php echo $TextColor ?>"><B>Od</B></FONT></A></TD>
		<TD>&nbsp;<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Sort=Subj<?php if ( $_GET['Sort']=='Subj' && $_GET['SortDir']!='Down' ) : ?>&SortDir=Down<?php endif ?>"><FONT COLOR="<?php echo $TextColor ?>"><B>Zadeva</B></FONT></A></TD>
		<TD ALIGN="Right"><A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Sort=Date<?php if ( $_GET['Sort']=='Date' && $_GET['SortDir']!='Up' ) : ?>&SortDir=Up<?php endif ?>"><FONT COLOR="<?php echo $TextColor ?>"><B>Datum</B></FONT></A>&nbsp;</TD>
	</TR>
	<?php
	$Color="";
	foreach ( $getPvtMessages AS $getPvtMessage ) {
		$Color = ($Color==$BckHiColor ? $BackgColor : $BckHiColor)
	?>
	<TR BGCOLOR="<?php echo $Color ?>" ONCLICK="javascript:window.parent.frames.emailView.document.location.href='<?php echo $_SERVER['PHP_SELF'] ?>?Sort=<?php echo $_GET['Sort'] ?>&SortDir=<?php echo $_GET['SortDir'] ?>&ID=<?php echo $getPvtMessage->ID ?>';">
		<TD ALIGN="center" VALIGN="top">&nbsp;<A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Sort=<?php echo $_GET['Sort'] ?>&SortDir=<?php echo $_GET['SortDir'] ?>&ID=<?php echo $getPvtMessage->ID ?>" TARGET="emailView"><?php if ( $getPvtMessage->IsDeleted ) : ?><IMG SRC="px/env-del.gif" WIDTH=12 HEIGHT=12 ALT="" BORDER="0"><?php elseif ( $getPvtMessage->IsReply ) : ?><IMG SRC="px/env-rep.gif" WIDTH=12 HEIGHT=12 ALT="" BORDER="0"><?php elseif ( $getPvtMessage->IsRead ) : ?><IMG SRC="px/env-opn.gif" WIDTH=12 HEIGHT=12 ALT="" BORDER="0"><?php else : ?><IMG SRC="px/eml.gif" WIDTH=12 HEIGHT=12 ALT="" BORDER="0"><?php endif ?></A></TD>
		<TD CLASS="a10" VALIGN="top">&nbsp;<?php if ( !$getPvtMessage->IsRead ) : ?><B><?php endif ?><?php if ( (int)$getPvtMessage->FromID ) : ?><A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Sort=<?php echo $_GET['Sort'] ?>&SortDir=<?php echo $_GET['SortDir'] ?>&ID=<?php echo $getPvtMessage->ID ?>" TARGET="emailView"><?php if ( $getPvtMessage->IsDeleted ) : ?><FONT COLOR="gray" STYLE="text-decoration:line-through;"><?php else : ?><FONT COLOR="<?php echo $TextColor ?>"><?php endif ?><?php echo $getPvtMessage->FromNickName ?></FONT></A><?php endif ?><?php if ( !$getPvtMessage->IsRead ) : ?></B><?php endif ?></TD>
		<TD CLASS="a10" VALIGN="top" WIDTH="50%">&nbsp;<?php if ( !$getPvtMessage->IsRead ) : ?><B><?php endif ?><A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Sort=<?php echo $_GET['Sort'] ?>&SortDir=<?php echo $_GET['SortDir'] ?>&ID=<?php echo $getPvtMessage->ID ?>" TARGET="emailView"><?php if ( $getPvtMessage->IsDeleted ) : ?><FONT COLOR="gray" STYLE="text-decoration:line-through;"><?php else : ?><FONT COLOR="<?php echo $TextColor ?>"><?php endif ?><?php if ( $getPvtMessage->MessageSubject=='' ) : ?><I>(brez)</I><?php else : ?><?php echo left($getPvtMessage->MessageSubject,45) ?><?php if ( strlen($getPvtMessage->MessageSubject)>45 ) : ?>...<?php endif ?><?php endif ?></FONT></A><?php if ( !$getPvtMessage->IsRead ) : ?></B><?php endif ?></TD>
		<TD ALIGN="right" CLASS="a10" VALIGN="top"><?php if ( !$getPvtMessage->IsRead ) : ?><B><?php endif ?><?php if ( $getPvtMessage->IsDeleted ) : ?><FONT COLOR="gray" STYLE="text-decoration:line-through;"><?php endif ?><?php echo formatDate($getPvtMessage->MessageDate,"d.m.Y H:i"); ?></FONT><?php if ( !$getPvtMessage->IsRead ) : ?></B><?php endif ?>&nbsp;</TD>
	</TR>
	<?php } ?>
	<?php else : ?>
	<TR BGCOLOR="<?php echo $BckHiColor ?>">
		<TD ALIGN="center" COLSPAN="4" HEIGHT="90"><B>Nimate zasebnih sporočil!</B></TD>
	</TR>
	<?php endif ?>
	</TABLE>
	</TD>
</TR>
</TABLE>
</BODY>

<?php elseif ( !contains($_SERVER['HTTP_REFERER'],"zasebno.php") ) : ?>

	<?php delpvtmessages(); ?>

<!-- frames -->
<FRAMESET  ROWS="115,*" FRAMESPACING=1 FRAMEBORDER=0 BORDER=0>
    <FRAME NAME="emailList" SRC="<?php echo $_SERVER['PHP_SELF'] ?>?Top" MARGINWIDTH="0" MARGINHEIGHT="0" SCROLLING="auto" FRAMEBORDER="0">
    <FRAME NAME="emailView" SRC="<?php echo $_SERVER['PHP_SELF'] ?>?ID=0" MARGINWIDTH="0" MARGINHEIGHT="0" SCROLLING="no" FRAMEBORDER="0">
</FRAMESET>
<NOFRAMES>
<BODY>
<P ALIGN="center">Nabiralnik uporablja okvirje, ki jih vaš internetni brskalnik, žal, ne podpira!</P>
</BODY>
</NOFRAMES>

<?php endif ?>
</HTML>