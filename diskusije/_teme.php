<?php
/* _teme.php - display thread topics
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

// izpis vseh tem v niti
$getTopics = gettopics($_GET['Nit'], "", isset($_GET['D']) ? $Datum : "", $Sort);

if ( !isset($_GET['Page']) ) $_GET['Page'] = 1;
if ( !isset($_GET['Rows']) ) $_GET['Rows'] = $TopicsPerPage;

$MxPg = 5;
$NuPg = (int)(count($getTopics) / $_GET['Rows']) + 1;

$_GET['Page'] = min(max((int)$_GET['Page'], 1), $NuPg);

$StPg = (int)min(max(1, $_GET['Page'] - ($MxPg/2)), max(1, $NuPg - $MxPg + 1));
$EdPg = (int)min($StPg + $MxPg - 1, min($_GET['Page'] + $MxPg - 1, $NuPg));

$PrPg = max(1, $_GET['Page']-1);
$NePg = min($_GET['Page']+1, $EdPg);

$StaR = min(count($getTopics),max(1,($_GET['Page']-1)*$_GET['Rows']+1));
$EndR = min(count($getTopics),max(1,$_GET['Page']*$_GET['Rows']));

$Page = (int)$_GET['Page'];

?>
<!-- teme -->
<?php if ( $NuPg > 1 ) : ?>
	<?php $query = (isset($_GET['Nit']) ? "&amp;Nit=". $_GET['Nit'] : "") . (isset($_GET['Tema']) ? "&amp;Tema=". $_GET['Tema'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") . (isset($_GET['Rows']) ? "&amp;Rows=". $_GET['Rows'] : "") ?>
<div class="frmmenu">
<TABLE CLASS="frmnav" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD CLASS="a10" WIDTH="45%" STYLE="border-top:1px solid <?php echo $FrameColor ?>;">
	Stran:
	<?php if ( $StPg > 1 ) : ?><A HREF="./?Page=<?php echo $StPg+1 . $query ?>">[&laquo;]</A><?php endif ?>
	<?php if ( $Page > 1 ) : ?><A HREF="./?Page=<?php echo $PrPg . $query ?>">[&lt;]</A><?php endif ?>
	<?php for ( $i=$StPg; $i<=$EdPg; $i++ ) { ?>
	<?php if ( $i == $Page ) : ?>[<FONT COLOR="<?php echo $TxtExColor ?>"><B><?php echo $i ?></B></FONT>]<?php else : ?><A HREF="./?Page=<?php echo $i . $query ?>">[<?php echo $i ?>]</A><?php endif ?>
	<?php } ?>
	<?php if ( $Page < $EdPg ) : ?><A HREF="./?Page=<?php echo $NePg . $query ?>">[&gt;]</A><?php endif ?>
	<?php if ( $NuPg > $EdPg ) : ?><A HREF="./?Page=<?php echo $EdPg+1 . $query ?>">[&raquo;]</A><?php endif ?>
	</TD>
	<TD ALIGN="center" CLASS="a10" WIDTH="10%" NOWRAP STYLE="border-top:1px solid <?php echo $FrameColor ?>;"></TD>
	<TD ALIGN="right" CLASS="a10" WIDTH="45%" STYLE="border-top:1px solid <?php echo $FrameColor ?>;">
	<?php $query = (isset($_GET['Nit']) ? "&amp;Nit=". $_GET['Nit'] : "") . (isset($_GET['Tema']) ? "&amp;Tema=". $_GET['Tema'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : ""); ?>
	Prikaži |
	<A HREF="./?Rows=25<?php echo $query ?>">25</A> |
	<A HREF="./?Rows=50<?php echo $query ?>">50</A> |
	<A HREF="./?Rows=75<?php echo $query ?>">75</A> |
	niti
	</TD>
</TR>
</TABLE>
</div>
<?php endif ?>

<?php if ( !$ReadOnly && !$getForums->ViewOnly && ($AllowAnonymous || $_SESSION['MemberID']) ) : ?>
<DIV CLASS="frmnew">
<IMG SRC="px/bn.gif" ALT="Začni novo diskusijo" BORDER="0" HEIGHT=12 WIDTH=12>
<A HREF="javascript:dialogOpen('oddaj.php?Act=New&amp;Nit=<?php echo $_GET['Nit'] ?>');"><FONT COLOR="<?php echo $TxtExColor ?>">Začni novo diskusijo</FONT></A>
</DIV>
<?php endif ?>

<div class="frmpost">
<TABLE CLASS="list" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR BGCOLOR="<?php echo $BckLoColor ?>">
	<TD WIDTH="20"></TD>
	<TD><A HREF="./?Nit=<?php echo $_GET['Nit'] ?>&amp;Sort=Name"><B><FONT COLOR="<?php echo $TextColor ?>">Tema</FONT></B></A></TD>
	<TD WIDTH="17%">&nbsp;<A HREF="./?Nit=<?php echo $_GET['Nit'] ?>&amp;Sort=Member"><B><FONT COLOR="<?php echo $TextColor ?>">Pričel</FONT></B></A>&nbsp;</TD>
	<TD ALIGN="right" WIDTH="5%"><B><FONT COLOR="<?php echo $TextColor ?>">Št.ogl.</FONT></B>&nbsp;</TD>
	<TD ALIGN="right" WIDTH="5%"><A HREF="./?Nit=<?php echo $_GET['Nit'] ?>&amp;Sort=Count"><B><FONT COLOR="<?php echo $TextColor ?>">Št.sp.</FONT></B></A>&nbsp;</TD>
	<TD ALIGN="right" WIDTH="20%"><A HREF="./?Nit=<?php echo $_GET['Nit'] ?>&amp;Sort=Date"><B><FONT COLOR="<?php echo $TextColor ?>">Zadnje sporočilo</FONT></B></A>&nbsp;</TD>
</TR>
<?php
if ( count($getTopics) ) {
	$Color = $BckHiColor;
	for ( $i=$StaR; $i<=$EndR; $i++ ) {
		$getTopic = $getTopics[$i-1];
		$Color    = ($Color==$BackgColor ? $BckHiColor : $BackgColor);

		// check if forum thread is private (only display to owner or moderator)
		if ( !$getForum->Private || ($IsModerator || $getTopic->StartedBy == $_SESSION['MemberID']) ) {
			$getMsgToApprove = getmsgtoapprove($_GET['Nit'],$getTopic->ID);
?>
<TR BGCOLOR="<?php echo $Color ?>">
	<TD ALIGN="center" VALIGN="baseline">
	<?php if ( isset($_GET['Tema']) && $_GET['Tema']==$getTopic->ID ) : ?>
		<IMG SRC="px/bo.gif"  BORDER="0" HEIGHT="12" WIDTH="12">
	<?php else : ?>
		<?php if ( $getTopic->Sticky ) : ?>
		<IMG SRC="px/ba.gif"  BORDER="0" HEIGHT="12" WIDTH="12">
		<?php elseif ( $getTopic->LockedBy!="" ) : ?>
		<IMG SRC="px/bl.gif" WIDTH=12 HEIGHT=12 ALT="Zaklenjena diskusija!" BORDER="0">
		<?php elseif ( $getTopic->Votes ) : ?>
		<IMG SRC="px/bp.gif"  BORDER="0" HEIGHT="12" WIDTH="12">
		<?php elseif ( isDate($getTopic->LastMessageDate) && compareDate($getTopic->LastMessageDate,$Datum) <= 0 ) : ?>
		<IMG SRC="px/bn.gif" ALT="Novo!" BORDER="0" HEIGHT="12" WIDTH="12">
		<?php else : ?>
		<IMG SRC="px/bs.gif"  BORDER="0" HEIGHT="12" WIDTH="12">
		<?php endif ?>
	<?php endif ?>
	</TD>
	<TD VALIGN="baseline"><?php if ( $getTopic->Votes ) : ?><SPAN CLASS="a10">[anketa]</SPAN><?php endif ?>
		<A HREF="./?Nit=<?php echo $_GET['Nit'] ?>&amp;Tema=<?php echo $getTopic->ID . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") ?>"><?php echo $getTopic->TopicName ?></A>
	<?php if ( isDate($getTopic->LastMessageDate) && compareDate($getTopic->LastMessageDate,$Datum) <= 0 ) : ?>
		<IMG SRC="px/new.gif" ALIGN="baseline" ALT="Novo!" BORDER="0" HEIGHT="8" WIDTH="20">
	<?php endif ?>
	<?php if ( $getMsgToApprove ) : ?>
		<?php if ( $IsModerator ) : ?><A HREF="javascript:dialogOpen('admin/approve.php?Nit=<?php echo $_GET['Nit'] ?>&Tema=<?php echo $getTopic->ID ?>');"><?php endif ?>
		<IMG SRC="px/lock.gif" ALIGN="baseline" ALT="Sporočila čakajo odobritev." BORDER="0" HEIGHT="12" WIDTH="12">
		<?php if ( $IsModerator ) : ?></A><?php endif ?>
	<?php endif ?>
	<?php if ( $getTopic->MessageCount > $MaxMsg ) : ?>
		<DIV CLASS="a10">Stran:
		<?php for ( $p=0; $p<($getTopic->MessageCount / $MaxMsg); $p++ ) : ?>
			<?php if ((int)($getTopic->MessageCount / $MaxMsg) > 8 && ($p > 3 && $p < (int)($getTopic->MessageCount / $MaxMsg)-3) ) : ?>
				<?php if ( $p==4 ) :?>...<?php endif ?>
			<?php else : ?>
			<A HREF="./?Nit=<?php echo $_GET['Nit'] ?>&amp;Tema=<?php echo $getTopic->ID ?>&amp;Page=<?php echo $p+1 ?><?php echo isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "" ?>">[<?php echo $p+1 ?>]</A>
			<?php endif ?>
		<?php endfor ?>
		</DIV>
	<?php endif ?>
	</TD>
	<?php $getMember = getmember($getTopic->StartedBy); ?>
	<TD CLASS="a10" VALIGN="baseline">&nbsp;<A HREF="clani.php?ID=<?php echo $getTopic->StartedBy ?>"><FONT COLOR="<?php echo $TextColor ?>"><?php echo (((int)$getMember->ShowPersonalData && (int)$getMember->DisplayName) ? $getMember->Name : $getMember->Nickname) ?></FONT></A></TD>
	<TD ALIGN="right" CLASS="a10" VALIGN="baseline"><?php echo $getTopic->ReadCount ?>&nbsp;&nbsp;</TD>
	<TD ALIGN="right" CLASS="a10" VALIGN="baseline"><?php echo $getTopic->MessageCount ?>&nbsp;&nbsp;</TD>
	<TD ALIGN="right" CLASS="a10" VALIGN="baseline">
		<?php if ( isDate($getTopic->LastMessageDate) && compareDate($getTopic->LastMessageDate,addDate(now(),-7)) <= 0 ) : ?><FONT COLOR="<?php echo $TxtExColor ?>"><?php endif ?>
		<?php if ( isDate($getTopic->LastMessageDate) ) echo formatDate($getTopic->LastMessageDate,"j.n.y \o\b H:i"); ?>
		<?php if ( isDate($getTopic->LastMessageDate) && compareDate($getTopic->LastMessageDate,addDate(now(),-7)) <= 0 ) : ?></FONT><?php endif ?>
	<?php if ( $getTopic->LastPostBy ) : ?>
		<?php $getMember = getmember($getTopic->LastPostBy); ?>
		<BR>oddal <B><A HREF="clani.php?ID=<?php echo $getTopic->LastPostBy ?>"><FONT COLOR="<?php echo $TextColor ?>"><?php echo (((int)$getMember->ShowPersonalData && (int)$getMember->DisplayName) ? $getMember->Name : $getMember->Nickname) ?></FONT></A></B>
	<?php endif ?>
	</TD>
</TR>
<?php
		} // end private thread
	}
} else {
?>
<TR BGCOLOR="<?php echo $BackgColor ?>"><TD ALIGN="center" COLSPAN="6" HEIGHT="70">V niti ni nobenega sporočila!</TD></TR>
<?php } ?>
</TABLE>
</DIV>

<?php if ( !$getForum->ViewOnly && !$ReadOnly && ($AllowAnonymous || $_SESSION['MemberID']) ) : ?>
<DIV CLASS="frmnew">
<IMG SRC="px/bn.gif" ALT="Začni novo diskusijo" BORDER="0" HEIGHT=12 WIDTH=12>
<A HREF="javascript:dialogOpen('oddaj.php?Act=New&amp;Nit=<?php echo $_GET['Nit'] ?>');"><FONT COLOR="<?php echo $TxtExColor ?>">Začni novo diskusijo</FONT></A>
</DIV>
<?php endif ?>

<?php if ( $NuPg > 1 ) : ?>
	<?php $query = (isset($_GET['Nit']) ? "&amp;Nit=". $_GET['Nit'] : "") . (isset($_GET['Tema']) ? "&amp;Tema=". $_GET['Tema'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") . (isset($_GET['Rows']) ? "&amp;Rows=". $_GET['Rows'] : "") ?>
<div class="frmmenu">
<TABLE CLASS="frmnav" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD CLASS="a10" WIDTH="45%" STYLE="border-bottom:1px solid <?php echo $FrameColor ?>;">
	Stran:
	<?php if ( $StPg > 1 ) : ?><A HREF="./?Page=<?php echo $StPg+1 . $query ?>">[&laquo;]</A><?php endif ?>
	<?php if ( $Page > 1 ) : ?><A HREF="./?Page=<?php echo $PrPg . $query ?>">[&lt;]</A><?php endif ?>
	<?php for ( $i=$StPg; $i<=$EdPg; $i++ ) { ?>
	<?php if ( $i == $Page ) : ?>[<FONT COLOR="<?php echo $TxtExColor ?>"><B><?php echo $i ?></B></FONT>]<?php else : ?><A HREF="./?Page=<?php echo $i . $query ?>">[<?php echo $i ?>]</A><?php endif ?>
	<?php } ?>
	<?php if ( $Page < $EdPg ) : ?><A HREF="./?Page=<?php echo $NePg . $query ?>">[&gt;]</A><?php endif ?>
	<?php if ( $NuPg > $EdPg ) : ?><A HREF="./?Page=<?php echo $EdPg+1 . $query ?>">[&raquo;]</A><?php endif ?>
	</TD>
	<TD ALIGN="center" CLASS="a10" WIDTH="10%" NOWRAP STYLE="border-bottom:1px solid <?php echo $FrameColor ?>;">
	<?php if ( $CanLock && count(getmessages($_GET['Nit'], $getTopic->ID, 0)) ) : ?>
	<IMG SRC="px/note-lock.gif" ALIGN="absmiddle" ALT="Zakleni/Odkleni" BORDER="0" HEIGHT=12 WIDTH=12 VSPACE="1">
	<A HREF="javascript:dialogOpen('admin/approve.php?Nit=<?php echo $_GET['Nit'] ?>&amp;Tema=<?php echo $getTopic->ID ?>');">Odobri čakajoča sporočila</A>
	<?php endif ?>
	</TD>
	<TD ALIGN="right" CLASS="a10" WIDTH="45%" STYLE="border-bottom:1px solid <?php echo $FrameColor ?>;">
	<?php $query = (isset($_GET['Nit']) ? "&amp;Nit=". $_GET['Nit'] : "") . (isset($_GET['Tema']) ? "&amp;Tema=". $_GET['Tema'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : ""); ?>
	Prikaži |
	<A HREF="./?Rows=25<?php echo $query ?>">25</A> |
	<A HREF="./?Rows=50<?php echo $query ?>">50</A> |
	<A HREF="./?Rows=75<?php echo $query ?>">75</A> |
	niti
	</TD>
</TR>
</TABLE>
</div>
<?php endif ?>
