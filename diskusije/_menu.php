<?php
/* _menu.php - forum menus
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
?>
<!-- menu -->
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
function validatePWD(fObj) {
  if (!emailOK(fObj.Email)) {alert("Nepravilen email naslov!"); return false;}
  if (fObj.Geslo.value.length<=0) {alert("Vpišite geslo!"); return false;}
  return true;
}
function reportSPAM(ID) {
	if (confirm("Prijavljate sporočilo z neprimerno vsebino!\nAli to res želite?")) {
		windowOpen("_oddaj.php?Act=Rpt&ID=" + ID);
	}
}
<?php if ( $IsModerator ) : ?>
function delTopic(Nit, Tema) {
	if (confirm("Ste tik pred tem, da izbrišete vsa sporočila na to temo!\nAli to res želite?")) {
		loginOpen("admin/delete.php?Act=Top&Nit=" + Nit + "&Tema=" + Tema);
	}
}
function delMessage(ID, Title) {
	if (confirm("Ste tik pred tem, da izbrišete sporočilo!\nAli to res želite?")) {
		loginOpen("admin/delete.php?Act=Msg&ID=" + ID);
	}
}
<?php endif ?>
//-->
</SCRIPT>
<div class="frmmenu">
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD CLASS="a10" VALIGN="baseline">
<?php
if ( $_SESSION['MemberID'] ) {
	$getPvtMessages = getpvtunread($_SESSION['MemberID']);
	if ( $getPvtMessages ) {
		echo "<IMG SRC=\"px/eml-new.gif\" ALIGN=\"baseline\" ALT=\"Nova zasebna sporočila!\" BORDER=\"0\" HEIGHT=12 WIDTH=12 VSPACE=\"1\">\n";
		echo "V <A HREF=\"javascript:dialogOpen('zasebno.php');\">nabiralniku</A> imate <B>". $getPvtMessages ." neprebran". koncnica($getPvtMessages,"o,i,a,ih") ."</B>\n";
		echo "sporočil". koncnica($getPvtMessages,"o!,i!,a!,!") ."<BR>\n";
	}
} else {
	$Email="epoštni naslov";
	if ( isset($_COOKIE['Email']) )	$Email=$_COOKIE['Email'];
?>
	<FORM ACTION="login.php?login&amp;reload&amp;referer=<?php echo urlencode($_SERVER['PHP_SELF']) ?>&amp;querystring=<?php echo urlencode($_SERVER['QUERY_STRING']) ?>" METHOD="post" ONSUBMIT="return validatePWD(this);">
	<INPUT TYPE="Text" NAME="Email" SIZE="8" VALUE="<?php echo $Email ?>" MAXLENGTH="64" ONFOCUS="this.select();" TITLE="Vpišite email naslov." STYLE="border:<?php echo $FrameColor ?> solid 1px;width:125px;font-size:10px;">
	<INPUT TYPE="Password" NAME="Geslo" SIZE="8" VALUE="" MAXLENGTH="64" TITLE="Vpišite geslo." STYLE="border:<?php echo $FrameColor ?> solid 1px;width:75px;font-size:10px;">
	samodejna? <INPUT TYPE="Checkbox" NAME="Auto"> <INPUT TYPE="image" VALUE="Prijava" SRC="px/login.gif" CLASS="but">
	<div class="a9"><A HREF="login.php?reset">Ste pozabili geslo?</A></div>
	</FORM>
<?php
}
?>
	</TD>
	<TD ALIGN="right" CLASS="a10" VALIGN="baseline">Sporočila:
	<SELECT NAME="Forum" SIZE="1" CLASS="a10" ONCHANGE="jumpTo(this.options[this.selectedIndex].value);">
	<OPTION VALUE="~Title" DISABLED STYLE="background:<?php echo $BckLoColor ?>;color:<?php echo $TextColor ?>;" SELECTED>--=[&nbsp;&nbsp;&nbsp;&nbsp;Izberi&nbsp;&nbsp;&nbsp;&nbsp;]=--</option>
	<OPTION VALUE="./<?php echo (isset($_GET['Nit']) ? "?Nit=". $_GET['Nit'] : "") ?>">vsa</OPTION>
	<OPTION VALUE="./?D=<?php echo formatDate(now(),'d.m.Y') . (isset($_GET['Nit']) ? "&amp;Nit=". $_GET['Nit'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") ?>">današnja</OPTION>
	<OPTION VALUE="./?D=<?php echo formatDate(addDate(now(),-1),'d.m.Y') . (isset($_GET['Nit']) ? "&amp;Nit=". $_GET['Nit'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") ?>">od včeraj</OPTION>
	<OPTION VALUE="./?D=<?php echo formatDate(addDate(now(),-7),'d.m.Y') . (isset($_GET['Nit']) ? "&amp;Nit=". $_GET['Nit'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") ?>">v 7 dneh</OPTION>
	<OPTION VALUE="./?D=LastVisit<?php echo (isset($_GET['Nit']) ? "&amp;Nit=". $_GET['Nit'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") ?>">od zadnjega obiska</OPTION>
	</SELECT>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" CLASS="a10" COLSPAN="2" VALIGN="baseline">
	<A HREF="faq.php"><IMG SRC="px/help.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;FAQ</A>
	| <A HREF="pravila.php"><IMG SRC="px/note-check.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Pravila</A>
	| <A HREF="ankete.php"><IMG SRC="px/poll.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Ankete</A>
	| <A HREF="clani.php"><IMG SRC="px/chat.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Člani</A>
<?php if ( !$_SESSION['MemberID'] ) : ?>
	<?php if ( !$ReadOnly ) : ?>
	| <A HREF="javascript:dialogOpen('vpispodatkov.php?new');"><IMG SRC="px/flag.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Včlanitev</A>
	<?php endif ?>
<?php else : ?>
	<?php if ( $ForumChat ) : ?>
	| <A HREF="javascript:chatOpen('klepet/')"><IMG SRC="px/chat.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Klepetalnice</A>
	<?php endif ?>
	| <A HREF="javascript:dialogOpen('zasebno.php');"><IMG SRC="px/eml.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Nabiralnik</A>
	| <A HREF="javascript:dialogOpen('vpispodatkov.php?edit');"><IMG SRC="px/options.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Nastavitve</A>
	| <A HREF="login.php?logout&amp;exit"><IMG SRC="px/lock.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Odjava</A>
<?php endif ?>
	</TD>
</TR>
</TABLE>
</div>
<div class="frmmenu">
<TABLE CLASS="frmnav" WIDTH="100%">
<TR>
	<TD CLASS="a10" VALIGN="middle">
	<A HREF="./"><IMG SRC="px/home.png" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Diskusije</A>
	<?php if ( $WebFile=="faq.php" ) : ?>
	&raquo; <A HREF="<?php echo $WebFile ?>"><IMG SRC="px/help.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;FAQ</A>
	<?php elseif ( $WebFile=="clani.php" ) : ?>
	&raquo; <A HREF="<?php echo $WebFile ?>"><IMG SRC="px/chat.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Člani</A>
	<?php elseif ( $WebFile=="pravila.php" ) : ?>
	&raquo; <A HREF="<?php echo $WebFile ?>"><IMG SRC="px/note-check.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Pravila</A>
	<?php elseif ( $WebFile=="ankete.php" ) : ?>
	&raquo; <A HREF="<?php echo $WebFile ?>"><IMG SRC="px/poll.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Ankete</A>
	<?php elseif ( $WebFile=="zasebno.php" ) : ?>
	&raquo; <A HREF="<?php echo $WebFile ?>"><IMG SRC="px/eml.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Nabiralnik</A>
	<?php elseif ( $WebFile=="vpispodatkov.php" ) : ?>
	&raquo; <A HREF="<?php echo $WebFile ?>"><IMG SRC="px/options.gif" WIDTH=12 HEIGHT=12 ALIGN="baseline" ALT="" BORDER="0">&nbsp;Nastavitve</A>
	<?php else : ?>
		<?php
		if ( isset($_GET['Nit']) && (int)$_GET['Nit'] ) {
			echo "&raquo; <IMG SRC=\"px/bo.gif\" WIDTH=12 HEIGHT=12 ALIGN=\"baseline\" ALT=\"\" BORDER=\"0\">&nbsp;<A HREF=\"./?Nit=". $_GET['Nit'] ."\">";
			echo $getForum->ForumName . "</A>\n";
			if ( isset($_GET['Tema']) && (int)$_GET['Tema'] ) {
				echo "&raquo; <IMG SRC=\"px/bo.gif\" WIDTH=12 HEIGHT=12 ALIGN=\"baseline\" ALT=\"\" BORDER=\"0\">&nbsp;<A HREF=\"./?Nit=". $_GET['Nit'] ."&amp;Tema=". $_GET['Tema'] ."\">";
				echo $getTopic->TopicName . "</A>\n";
			}
		}
		?>
	<?php endif ?>
	</TD>
	<TD ALIGN="right" CLASS="a10" VALIGN="middle" NOWRAP>
	<FORM ACTION="<?php echo ($WebFile=="faq.php" || $WebFile=="pravila.php" ? "./" : $_SERVER['PHP_SELF']) ?>" METHOD="get" ONSUBMIT="return (this.Find.value.length>2);">
	Najdi:<?php if ( isset($_GET['Nit']) ) : ?><INPUT NAME="Nit" TYPE="Hidden" VALUE="<?php echo $_GET['Nit'] ?>"><?php endif ?>
	<INPUT NAME="Find" TYPE="Text" SIZE="12" VALUE="<?php if ( isset($_GET['Find']) ) echo $_GET['Find']; ?>" STYLE="width:118px; font-size:10px; border:<?php echo $FrameColor ?> solid 1px;">
	<INPUT TYPE="image" SRC="px/fnd-red.gif" CLASS="img">
	</FORM>
	</TD>
</TR>
</TABLE>
</div>
<div class="frmmenu">
<?php
if ( isset($_GET['Nit']) && (int)$_GET['Nit'] ) {
	$getMsgToApprove = getmsgtoapprove($_GET['Nit']);
	echo "<TABLE CLASS=\"frmthread\" WIDTH=\"100%\">\n";
	echo "<TR>\n";
	echo "<TD CLASS=\"a10\" WIDTH=\"15%\">";
	echo "</TD>\n";
	echo "<TD ALIGN=\"center\">\n";
	echo "<A HREF=\"./?Nit=". $_GET['Nit'] . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") ."\"><B>". $getForum->ForumName ."</B></A>\n";
	if ( $getForum->ApprovalRequired )
		echo "<DIV CLASS=\"a9\"><B>(Sporočila odobri moderator ali administrator!)</B></DIV>\n";
	echo "</TD>\n";
	echo "<TD ALIGN=\"right\" CLASS=\"a10\" WIDTH=\"15%\">";
	if ( $getMsgToApprove && $IsModerator ) {
		echo "<A HREF=\"javascript:dialogOpen('admin/approve.php?Nit=". $_GET['Nit'] ."');\">";
		echo "<IMG SRC=\"px/note-lock.gif\" ALIGN=\"baseline\" ALT=\"Odobritev sporočil\" BORDER=\"0\" HEIGHT=12 WIDTH=12>&nbsp;";
		echo "Čakajoča sp.</A>";
	}
	echo "</TD>\n";
	echo "</TR>\n";
	echo "</TABLE>\n";
}

if ( isset($_GET['Tema']) && (int)$_GET['Tema'] && ($getForum->Password=="" || !strcmp($_SESSION['frmPassword'], $getForum->Password)) ) {
	// user has selected a topic (and optional password matches supplied)
	echo "<TABLE CLASS=\"frmtopic\" WIDTH=\"100%\">\n";
	echo "<TR>\n";
	echo "<TD CLASS=\"a10\" WIDTH=\"25%\">\n";

	if ( $IsModerator && $getTopic->LockedBy=="" && !isset($_GET['ID']) ) {
		if ( $CanMove ) { // move
			echo "<IMG SRC=\"px/bm.gif\" ALIGN=\"baseline\" ALT=\"Premakni\" BORDER=\"0\" HEIGHT=12 WIDTH=12>\n";
			echo "<A HREF=\"javascript:loginOpen('admin/move.php?Nit=". $_GET['Nit'] ."&amp;Tema=". $_GET['Tema'] ."');\">Premakni</A>\n";
		}

		if ( $CanMove && $CanDelete )
			echo " | ";

		if ( $CanDelete ) { // delete
			echo "<IMG SRC=\"px/bd.gif\" ALIGN=\"baseline\" ALT=\"Briši temo\" BORDER=\"0\" HEIGHT=12 WIDTH=12>\n";
			echo "<A HREF=\"javascript:delTopic(".$_GET['Nit'] .",". $_GET['Tema'] .");\">Briši</A>\n";
		}
	} else if ( $IsModerator && $getTopic->LockedBy!="" && !isset($_GET['ID']) ) {
		if ( $CanLock ) { // lock
			echo "<IMG SRC=\"px/ba.gif\" ALIGN=\"baseline\" ALT=\"Premakni\" BORDER=\"0\" HEIGHT=12 WIDTH=12>\n";
			echo "<A HREF=\"javascript:loginOpen('admin/glue.php?Nit=". $_GET['Nit'] ."&amp;Tema=". $_GET['Tema'] ."');\">". ($getTopic->Sticky ? "Odlepi" : "Prilepi") ."</A>\n";
		}
	} else if ( $_SESSION['MemberID'] ) {
		echo "<IMG SRC=\"px/env.gif\" ALIGN=\"baseline\" WIDTH=12 HEIGHT=12 ALT=\"Naroči se na obvestila!\" BORDER=\"0\">\n";
		echo "<A HREF=\"javascript:loginOpen('narocila.php?Add=". $getTopic->ID ."');\">Spremljaj</A>\n";
	}

	echo "</TD>\n";
	echo "<TD ALIGN=\"center\">\n";

	echo "<A HREF=\"./?Nit=". $_GET['Nit'] ."&amp;Tema=". $_GET['Tema'] . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") ."\"><FONT COLOR=\"". $TextColor ."\"><B>". $getTopic->TopicName ."</B></FONT></A>\n";
	if ( $getTopic->LockedBy != "" ) {
		echo "<DIV CLASS=\"a10\"><B>(Zaklenjena diskusija!)</B></DIV>\n";
	}

	echo "</TD>\n";
	echo "<TD ALIGN=\"right\" CLASS=\"a10\" WIDTH=\"25%\">\n";

	if ( $IsModerator && $getTopic->LockedBy=="" && !isset($_GET['ID']) ) {
		if ( $CanLock ) { // lock
			echo "<IMG SRC=\"px/bl.gif\" ALIGN=\"baseline\" ALT=\"Zakleni/Odkleni\" BORDER=\"0\" HEIGHT=12 WIDTH=12>\n";
			echo "<A HREF=\"javascript:loginOpen('admin/lock.php?Act=Top&amp;Nit=". $_GET['Nit'] ."&amp;Tema=". $_GET['Tema'] ."');\">". ($getTopic->LockedBy=="" ? "Zakleni" : "Odkleni") ."</A>\n";
		}
		if ( $CanLock && $CanRename )
			echo " | ";

		if ( $CanRename ) { // rename
			echo "<IMG SRC=\"px/br.gif\" ALIGN=\"baseline\" ALT=\"Preimenuj\" BORDER=\"0\" HEIGHT=12 WIDTH=12>\n";
			echo "<A HREF=\"javascript:loginOpen('admin/rename.php?Nit=". $_GET['Nit'] ."&amp;Tema=". $_GET['Tema'] ."');\">Preimenuj</A>\n";
		}
	} else if ( $IsModerator && $getTopic->LockedBy!="" && !isset($_GET['ID']) ) {
		if ( $CanLock ) {
			echo "<IMG SRC=\"px/bl.gif\" ALIGN=\"baseline\" ALT=\"Zakleni/Odkleni\" BORDER=\"0\" HEIGHT=12 WIDTH=12>\n";
			echo "<A HREF=\"javascript:loginOpen('admin/lock.php?Act=Top&amp;Nit=". $_GET['Nit'] ."&amp;Tema=". $_GET['Tema'] ."');\">". ($getTopic->LockedBy=="" ? "Zakleni" : "Odkleni") ."</A>\n";
		}
	} else {
		echo "<IMG SRC=\"px/icoprinter.gif\" ALIGN=\"baseline\" WIDTH=12 HEIGHT=12 ALT=\"Oblika primerna za tiskanje\" BORDER=\"0\">\n";
		echo "<A HREF=\"javascript:windowOpen('print.php?What=Diskusije&amp;Nit=". $_GET['Nit'] ."&amp;Tema=". $_GET['Tema'] ."')\">Natisni</A>\n";
	}

	echo "</TD>\n";
	echo "</TR>\n";
	echo "</TABLE>\n";
}
?>
</div>