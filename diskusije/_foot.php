<?php
/* _foot.php - forum footer
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
<!-- footer -->
<div class="frmfoot">
<?php if ( isset($_GET['Tema']) && (int)$_GET['Tema'] && !isset($_GET['ID']) && ($getForum->Password=="" || !strcmp($_SESSION['frmPassword'], $getForum->Password)) ) : ?>
<?php if ( count($getMessages) ) : ?>
	<?php if ( $IsModerator ) : ?>
		<?php if ( $CanLock ) : ?>
<IMG SRC="px/lock.gif" ALIGN="baseline" ALT="Zakleni/Odkleni" BORDER="0" HEIGHT=12 WIDTH=12>
<A HREF="javascript:loginOpen('admin/lock.php?Act=Top&amp;Nit=<?php echo $_GET['Nit'] ?>&amp;Tema=<?php echo $_GET['Tema'] ?>');"><?php if ( $getTopic->LockedBy == "" ) : ?>Zakleni<?php else : ?>Odkleni<?php endif ?> temo</A>
|
<IMG SRC="px/ba.gif" ALIGN="baseline" ALT="Prilepi/Odlepi" BORDER="0" HEIGHT=12 WIDTH=12>
<A HREF="javascript:loginOpen('admin/glue.php?Act=Top&amp;Nit=<?php echo $_GET['Nit'] ?>&amp;Tema=<?php echo $_GET['Tema'] ?>');"><?php if ( $getTopic->Sticky ) : ?>Odlepi<?php else : ?>Prilepi<?php endif ?> temo</A>
|
		<?php endif ?>
		<?php if ( $CanRename ) : ?>
<IMG SRC="px/br.gif" ALIGN="baseline" ALT="Preimenuj" BORDER="0" HEIGHT=12 WIDTH=12>
<A HREF="javascript:loginOpen('admin/rename.php?Nit=<?php echo $_GET['Nit'] ?>&amp;Tema=<?php echo $_GET['Tema'] ?>');">Preimenuj</A>
|
		<?php endif ?>
		<?php if ( $CanMove ) : ?>
<IMG SRC="px/bm.gif" ALIGN="baseline" ALT="Premakni" BORDER="0" HEIGHT=12 WIDTH=12>
<A HREF="javascript:dialogOpen('admin/move.php?Nit=<?php echo $_GET['Nit'] ?>&amp;Tema=<?php echo $_GET['Tema'] ?>');">Premakni</A>
|
		<?php endif ?>
		<?php if ( $CanDelete ) : ?>
<IMG SRC="px/bd.gif" ALIGN="baseline" ALT="Briši temo" BORDER="0" HEIGHT=12 WIDTH=12>
<A HREF="javascript:delTopic(<?php echo $_GET['Nit'] ?>, <?php echo $_GET['Tema'] ?>);">Briši temo</A>
|
		<?php endif ?>
	<?php endif ?>
<IMG SRC="px/icoprinter.gif" ALIGN="baseline" WIDTH=12 HEIGHT=12 ALT="Oblika primerna za tiskanje" BORDER="0">
<A HREF="javascript:windowOpen('print.php?What=Diskusije&amp;Nit=<?php echo $_GET['Nit'] ?>&amp;Tema=<?php echo $_GET['Tema'] ?>')">Natisni temo</A>
	<?php if ( $_SESSION['MemberID'] ) : ?>
|
<IMG SRC="px/env.gif" ALIGN="baseline" WIDTH=12 HEIGHT=12 ALT="Naroči se na obvestila!" BORDER="0">
<A HREF="javascript:loginOpen('narocila.php?Add=<?php echo $getTopic->ID ?>');">Spremljaj temo</A>
	<?php endif ?>
<?php endif ?>
<?php endif ?>

<TABLE CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<?php if ( isset($_GET['Nit']) && (int)$_GET['Nit'] && !isset($_GET['ID']) ) : ?>
<TR>
	<TD>
	<?php if ( !isset($_GET['Tema']) ) : ?>
	<IMG SRC="px/ba.gif" ALIGN="baseline" ALT="Pripeto" BORDER="0" HEIGHT="12" WIDTH="12"> Pripeta tema |
	<IMG SRC="px/bl.gif" ALIGN="baseline" ALT="Zaklenjeno" BORDER="0" HEIGHT="12" WIDTH="12"> Zaklenjena tema |
	<IMG SRC="px/bp.gif" ALIGN="baseline" ALT="Anketa" BORDER="0" HEIGHT="12" WIDTH="12"> Tema z anketo
	<?php else : ?>
	<B><FONT COLOR="<?php echo $TxtExColor ?>">*</FONT></B> - nevčlanjen avtor
	<?php endif ?>
	</TD>
	<TD ALIGN="right">
	Hitri skok:
	<SELECT NAME="Forum" SIZE="1" CLASS="a9" ONCHANGE="jumpTo(this.options[this.selectedIndex].value);">
		<OPTION VALUE="./" DISABLED STYLE="background:<?php echo $FrameColor ?>;color:white;">==============&nbsp;&nbsp;&nbsp;&nbsp;Niti&nbsp;&nbsp;&nbsp;&nbsp;==============</OPTION>
<?php $getCategories = getcategories(); ?>
<?php foreach ( $getCategories AS $getCategory ) { ?>
		<OPTION VALUE="~Category" DISABLED STYLE="background:<?php echo $BackgColor ?>"><?php echo $getCategory->CategoryName ?></OPTION>
	<?php
	$getForums = getforums($getCategory->ID,(int)$AccessLevel>1);
	if ( count($getForums) ) foreach ( $getForums AS $getForum ) {
		$getLastPost = getlastpost($getForum->ID);
	?>
		<OPTION VALUE="./?Nit=<?php echo $getForum->ID . (isset($_GET['Sort']) ? "&sort=". $_GET['Sort'] :"") ?>" <?php echo isset($_GET['Nit']) && $_GET['Nit']==$getForum->ID ? "SELECTED" : "" ?>>&nbsp;&nbsp; <?php echo $getForum->ForumName ?>
		<?php if ( isDate($getLastPost->LastMessageDate) && compareDate($getLastPost->LastMessageDate,$Datum)<0 ) : ?>(Novo!)<?php endif ?>
		</OPTION>
	<?php } ?>
<?php } ?>
		<OPTION VALUE="~Title" DISABLED STYLE="background:<?php echo $FrameColor ?>;color:white;">==============&nbsp;&nbsp;&nbsp;&nbsp;Ostalo&nbsp;&nbsp;&nbsp;&nbsp;==============</OPTION>
		<OPTION VALUE="./">&nbsp;&nbsp; Diskusije</OPTION>
		<OPTION VALUE="clani.php">&nbsp;&nbsp; Člani</OPTION>
		<OPTION VALUE="faq.php">&nbsp;&nbsp; FAQ</OPTION>
		<OPTION VALUE="pravila.php">&nbsp;&nbsp; Pravila</OPTION>
	</SELECT>
	</TD>
</TR>
<?php elseif ( strtolower(basename($_SERVER['PHP_SELF'])) == 'index.php' ) : ?>
<TR>
	<TD COLSPAN="2" VALIGN="top">
	<IMG SRC="px/bc.gif" ALT="Vpogled" BORDER="0" HEIGHT="12" WIDTH="12" VSPACE="1">&nbsp;Samo vpogled |
	<IMG SRC="px/bl.gif" ALT="Zaščiteno" BORDER="0" HEIGHT="12" WIDTH="12" VSPACE="1">&nbsp;Zaščitena nit |
	<IMG SRC="px/bn.gif" ALT="Novo" BORDER="0" HEIGHT="12" WIDTH="12" VSPACE="1">&nbsp;Nova sporočila
	</TD>
	<TD ALIGN="right" VALIGN="top">
	<IMG SRC="px/new.gif" ALT="Novo!" BORDER="0" HEIGHT="8" WIDTH="20" VSPACE="1">
	Sporočila vpisana po: <?php echo formatDate($Datum,"j.n.Y H:i") ?>
	</TD>
</TR>
<?php endif ?>
</TABLE>

<TABLE CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD VALIGN="baseline" WIDTH="20"><IMG SRC="px/stats.gif" ALT="Statistika" BORDER="0" HEIGHT="12" WIDTH="12"></TD>
	<TD VALIGN="baseline">
<?php
$Visitors = (int)$db->get_var("SELECT count(*) FROM frmVisitors");
$Users    = (int)$db->get_var("SELECT count(*) FROM frmVisitors WHERE MemberID IS NOT NULL");
$Guests   = $Visitors - $Users;
if ( $Users > $Visitors ) {
	$Visitors = $Users;
	$Guests   = 0;
}

echo "Diskusije <a href=\"clani.php?online\">pregleduje</a> <B>". $Visitors ."</B> obiskoval". koncnica($Visitors,"ec,ca,ci,cev") ."&nbsp;";
echo "(". $Users ." član". koncnica($Users," ,a,i,ov") .", ". $Guests ." gost". koncnica($Guests," ,a,i,ov") .") ";
echo "[<FONT COLOR=\"". $TxtExColor ."\"><i>administrator</i></FONT>,&nbsp;<FONT COLOR=\"". $TxtExColor ."\">moderator</FONT>]<BR>\n";

if ( $Users ) {
	$ForumUsers = $db->get_results("
		SELECT V.MemberID
		,      M.Nickname
		,      M.AccessLevel
		,      M.Patron
		FROM   frmVisitors V
		LEFT JOIN frmMembers M ON M.ID = V.MemberID
		WHERE V.MemberID IS NOT NULL
	");
	$i = 0;
	if ( count($ForumUsers) ) foreach ( $ForumUsers AS $ForumUser ) {
		echo "<A HREF=\"clani.php?ID=". $ForumUser->MemberID ."\">";
		if ( $ForumUser->AccessLevel >= 5 ) echo "<i>";
		if ( $ForumUser->AccessLevel >= 3 ) echo "<FONT COLOR=\"". $TxtExColor ."\">";
		echo $ForumUser->Nickname;
		if ( $ForumUser->AccessLevel >= 3 ) echo "</FONT>";
		if ( $ForumUser->AccessLevel >= 5 ) echo "</i>";
		echo "</A>";
		if ( ++$i < count($ForumUsers) ) echo ", ";
	}
	echo "<BR>\n";
}

$ChatCount = (int)$db->get_var("SELECT count(*) FROM frmVisitors WHERE InChat<>0");
if ( $ForumChat && $ChatCount ) {
	echo "V Klepetalnici klepeta". koncnica($ChatCount," ,ta,jo, ") ." ". $ChatCount ." uporabnik". koncnica($ChatCount,".,a.,i.,ov.") ."<BR>\n";
}
?>
	</TD>
	<TD ALIGN="right" VALIGN="top">
	Sporočil: <B><?php echo $db->get_var("SELECT count(*) FROM frmMessages") ?></B><br>
	<A HREF="clani.php">Članov: <B><?php echo $db->get_var("SELECT count(*) FROM frmMembers WHERE Enabled=1 AND LastVisit IS NOT NULL") ?></B></A>
	</TD>
</TR>
</TABLE>
</div>
