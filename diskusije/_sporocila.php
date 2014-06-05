<?php
/* _sporocila.php - display forum messages
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

if ( isset($_GET['ID']) ) {

	$getMessages = array();
	$getMessages[0] = getmessage($_GET['ID']);

} else if ( isset($_GET['Find']) && $_GET['Find'] != "" ) {

	// $Find paramters M.=message,U.=member,T.=topic,F.=forum
	if ( isset($_GET['What']) && $_GET['What'] != "" ) {
		switch ($_GET['What']) {
			case "Nickname" : $Find = SearchString("U.Nickname", $_GET['Find']); break;
			case "Topic"    : $Find = SearchString("T.TopicName", $_GET['Find']); break;
			case "Name"     : $Find = SearchString("U.Name", $_GET['Find']); break;
			case "ID"       : $Find = "M.MemberID=". (int)$_GET['Find']; break;
			default         : $Find = SearchString("M.MessageBody", $_GET['Find']); break;
		}
	} else {
		$Find  = SearchString("M.MessageBody", $_GET['Find']);
		$Find .= " OR ". SearchString("U.Name", $_GET['Find']);
		$Find .= " OR ". SearchString("U.Nickname", $_GET['Find']);
		$Find .= " OR ". SearchString("T.TopicName", $_GET['Find']);
	}
	$Find  = isset($_GET['Nit']) ? "(". $Find .") AND F.ID=". (int)$_GET['Nit'] : $Find;
	$getMessages = findmessages($Find, 1, isset($_GET['D']) ? $Datum : "", $AccessLevel>2);

} else if ( isset($_GET['D']) ) {

	$getMessages = getmessages((isset($_GET['Nit']) ? $_GET['Nit'] : 0), 0, 1, $Datum);

} else
	$getMessages = getmessages($_GET['Nit'], $_GET['Tema'], 1);

if ( !isset($_GET['Rows']) ) $_GET['Rows'] = $MaxMsg;
$_GET['Rows'] = max(5,min(100,(int)$_GET['Rows']));

// calculate page numbers, start & end rows
$MxPg = 5;
$NuPg = (int)(count($getMessages) / $_GET['Rows']) + 1;

if ( !isset($_GET['Page']) && isset($_GET['Tema']) ) {
	$_GET['Page'] = strtolower($StartMsg) == "last" ? $NuPg : 1;
	// update read count but only if no page selected
	$db->query("UPDATE frmTopics SET ReadCount=ReadCount+1 WHERE ID=". (int)$_GET['Tema']);
}

$_GET['Page'] = min(max((int)$_GET['Page'], 1), $NuPg);

$StPg = (int)min(max(1, $_GET['Page'] - ($MxPg/2)), max(1, $NuPg - $MxPg + 1));
$EdPg = (int)min($StPg + $MxPg - 1, min($_GET['Page'] + $MxPg - 1, $NuPg));

$PrPg = max(1, $_GET['Page']-1);
$NePg = min($_GET['Page']+1, $EdPg);

$StaR = min(count($getMessages),max(1,($_GET['Page']-1)*$_GET['Rows']+1));
$EndR = min(count($getMessages),max(1,$_GET['Page']*$_GET['Rows']));

$Page = (int)$_GET['Page'];
?>

<!-- izpis besedil -->
<?php if ( $NuPg > 1 ) : ?>
<div class="frmmenu">
<TABLE CLASS="frmnav" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<?php $query = (isset($_GET['Nit']) ? "&amp;Nit=". $_GET['Nit'] : "") . (isset($_GET['Tema']) ? "&amp;Tema=". $_GET['Tema'] : "") . (isset($_GET['What']) ? "&amp;What=". $_GET['What'] : "") . (isset($_GET['Find']) ? "&amp;Find=". $_GET['Find'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") . (isset($_GET['Rows']) ? "&amp;Rows=". $_GET['Rows'] : "") ?>
	<TD CLASS="a10" WIDTH="50%" STYLE="border-top:1px solid <?php echo $FrameColor ?>;">
	Stran:
	<?php if ( $StPg > 1 ) : ?><A HREF="./?Page=<?php echo $StPg+1 . $query ?>">[&laquo;]</A><?php endif ?>
	<?php if ( $Page > 1 ) : ?><A HREF="./?Page=<?php echo $PrPg . $query ?>">[&lt;]</A><?php endif ?>
	<?php for ( $i=$StPg; $i<=$EdPg; $i++ ) { ?>
	<?php if ( $i == $Page ) : ?>[<FONT COLOR="<?php echo $TxtExColor ?>"><B><?php echo $i ?></B></FONT>]<?php else : ?><A HREF="./?Page=<?php echo $i . $query ?>">[<?php echo $i ?>]</A><?php endif ?>
	<?php } ?>
	<?php if ( $Page < $EdPg ) : ?><A HREF="./?Page=<?php echo $NePg . $query ?>">[&gt;]</A><?php endif ?>
	<?php if ( $NuPg > $EdPg ) : ?><A HREF="./?Page=<?php echo $EdPg+1 . $query ?>">[&raquo;]</A><?php endif ?>
	</TD>
	<?php $query = (isset($_GET['Nit']) ? "&amp;Nit=". $_GET['Nit'] : "") . (isset($_GET['Tema']) ? "&amp;Tema=". $_GET['Tema'] : "") . (isset($_GET['What']) ? "&amp;What=". $_GET['What'] : "") . (isset($_GET['Find']) ? "&amp;Find=". $_GET['Find'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") ?>
	<TD ALIGN="right" CLASS="a10" WIDTH="50%" STYLE="border-top:1px solid <?php echo $FrameColor ?>;">
	Prikaži |
	<A HREF="./?Rows=5<?php echo $query ?>">5</A> |
	<A HREF="./?Rows=10<?php echo $query ?>">10</A> |
	<A HREF="./?Rows=20<?php echo $query ?>">20</A> |
	<A HREF="./?Rows=50<?php echo $query ?>">50</A> |
	besedil
	</TD>
</TR>
</TABLE>
</div>
<?php endif ?>

<?php if ( $getTopic->Votes != "" ) : ?>
	<?php
	if ( isset($_POST['Vote']) && $_POST['Vote'] != "" ) {
		$getPoll = getPoll($_GET['Tema'],$_SESSION['MemberID']);
		if ( $getPoll->VoteDate == "" )
			updPoll($_GET['Tema'],$_POST['Vote'],$_SESSION['MemberID']);
	}
	$getPoll = getPoll($_GET['Tema'],$_SESSION['MemberID']);
	$Txt = ReplaceSmileys($getPoll->Question,"../pic/");
	?>
<!-- anketa -->
<DIV CLASS="frmpost">
<DIV CLASS="frmthread"><B>ANKETA</B></DIV>
<DIV ALIGN="center">
	<P><B><?php echo $Txt ?></B></P>
	<?php if ( $ReadOnly || $getPoll->VoteDate != ""
		|| contains($_SERVER['QUERY_STRING'],"viewpoll")
		|| $_SESSION['MemberID'] == $getTopic->StartedBy
		|| $_SESSION['MemberID'] == 0
		|| $getTopic->LockedBy != "" ) : ?>
	<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0">
		<?php
		for ( $i=1; $i<=$getPoll->Answers; $i++ ) {
			$Odg = eval('return $getPoll->A'. $i .';');
			$Rez = eval('return $getPoll->R'. $i .';');
			$Pct = 0;
			if ( $getPoll->Votes > 0 )
				$Pct = round($Rez*100 / $getPoll->Votes);
			$NPct= 100 - $Pct;
		?>
	<TR>
		<TD CLASS="a10"><?php echo $Odg ?>&nbsp;</TD>
		<TD ALIGN="center" WIDTH="110"><?php if ( $Pct != 0 ) : ?><IMG SRC="px/red.gif" WIDTH="<?php echo $Pct ?>" HEIGHT="10"><?php endif ?><?php if ( $NPct != 0 ) : ?><IMG SRC="px/wht.gif" WIDTH=<?php echo $NPct ?> HEIGHT=10><?php endif ?></TD>
		<TD ALIGN="right" CLASS="a10" WIDTH="35"><?php echo $Pct ?>%</TD>
		<TD ALIGN="right" CLASS="a10" WIDTH="35">[<?php echo $Rez ?>]</TD>
	</TR>
		<?php } ?>
	</TABLE>
	<P CLASS="a10">Vseh glasov: <B><?php echo $getPoll->Votes ?></B></P>
	<?php else : ?>
	<FORM ACTION="./?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
	<TABLE ALIGN="center" BORDER=0 CELLPADDING=4 CELLSPACING=0 WIDTH="400">
		<?php
		for ( $i=1; $i<$getPoll->Answers; $i++ ) {
			$Odg = eval('return $getPoll->A'. $i .';');
		?>
	<TR>
		<TD CLASS="a10" VALIGN="middle" WIDTH="5%"><INPUT TYPE="Radio" NAME="Vote" VALUE="<?php echo $i ?>" <?php if ( $i == 1 ) : ?>CHECKED<?php endif ?>></TD>
		<TD CLASS="a10" VALIGN="middle" WIDTH="95%"><?php echo $Odg ?></TD>
	</TR>
		<?php } ?>
	<TR>
		<TD ALIGN="center" CLASS="a10" COLSPAN="2">
		<INPUT TYPE="Submit" VALUE="Glasuj" CLASS="but" STYLE="margin-top:7px;margin-bottom:7px;"><BR>
		<A HREF="./?<?php echo $_SERVER['QUERY_STRING'] ?>&viewpoll">Rezultati</A>
		</TD>
	</TR>
	</TABLE>
	</FORM>
	<?php endif ?>
</DIV>
</DIV>
<?php endif ?>

<?php if ( $getTopic->Sticky ) : ?>
	<?php if ( $StaR==1 ) $StaR = 2; ?>
<!--- lepljiva tema  --->
<A NAME="MSG<?php echo $getMessages[0]->ID ?>"></A>
<DIV CLASS="frmpost">
<DIV CLASS="frmthread"><B>Originalno sporočilo: <B><?php echo $getTopic->TopicName ?></B></DIV>
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="1" WIDTH="100%">
<TR>
	<TD BGCOLOR="<?php echo $BckHiColor ?>"><?php echo ReplaceSmileys(CleanHTML($getMessages[0]->MessageBody), "../pic/") ?></TD>
</TR>
<TR BGCOLOR="<?php echo $BckLoColor ?>">
	<TD ALIGN="center">
	<IMG SRC="px/env-fwd.gif" ALIGN="baseline" WIDTH=12 HEIGHT=12 ALT="Posreduj" BORDER="0">
	<A HREF="javascript:dialogOpen('oddaj.php?Act=Fwd&amp;ID=<?php echo $getMessages[0]->ID ?>');">Posreduj</A> |
	<IMG SRC="px/icoprinter.gif" ALIGN="baseline" WIDTH=12 HEIGHT=12 ALT="Oblika primerna za tiskanje" BORDER="0">
	<A HREF="javascript:windowOpen('print.php?What=Diskusije&amp;ID=<?php echo $getMessages[0]->ID ?>')">Natisni</A>
	</TD>
</TR>
</TABLE>
</DIV>
<?php endif ?>

<?php if ( !$ReadOnly && !$getForum->ViewOnly && $getTopic->LockedBy == "" && ($AllowAnonymous || $_SESSION['MemberID'] != 0) ) : ?>
<DIV CLASS="frmnew">
<IMG SRC="px/note-new.gif" ALIGN="baseline" ALT="Uredi" BORDER="0" HEIGHT=12 WIDTH=12>
<A HREF="javascript:dialogOpen('oddaj.php?Act=New&amp;Nit=<?php echo $_GET['Nit'] ?>&amp;Tema=<?php echo $_GET['Tema'] ?>');"><FONT COLOR="<?php echo $TxtExColor ?>">Novo sporočilo</FONT></A>
</DIV>
<?php endif ?>

<?php if ( count($getMessages) <= (int)$getTopic->Sticky ) : ?>
<DIV CLASS="frmpost">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $BackgColor ?>">
	<TD ALIGN="center" HEIGHT="60" VALIGN="middle">
	<B>Ni <?php if ( $getForum->ApprovalRequired ) : ?>odobrenih<?php endif ?> sporočil.</B>
	</TD>
</TR>
</TABLE>
</DIV>
<?php else : ?>

<?php
// izpis zadnjih nekaj sporočil
for ( $i=$StaR; $i<=$EndR; $i++ ) {
	$getMessage = $getMessages[$i-1];
	$Bes = CleanHTML($getMessage->MessageBody);
	$Bes = ReplaceSmileys($Bes, "../pic/");

	if ( (isset($_GET['Find']) && $_GET['Find'] != "") || isset($_GET['D']) ) {
		$getForum  = getforum($getMessage->ForumID);
		$getTopic  = gettopic($getMessage->TopicID);
	}
	$getMember = getmember($getMessage->MemberID);
	$settings  = ParseMetadata($getMember->Settings,',');
?>
<DIV CLASS="frmpost">
	<?php if ( !($i == 0 && $getTopic->Sticky) ) : ?><A NAME="MSG<?php echo $getMessage->ID ?>"></A>
	<DIV CLASS="head">
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
		<?php if ( (isset($_GET['Find']) && $_GET['Find'] != "") || isset($_GET['D']) ) : ?>
		<TR>
			<TD ALIGN="center" WIDTH="20"><IMG SRC="px/bs.gif" ALIGN="baseline" ALT="" BORDER="0" HEIGHT="12" WIDTH="12"></TD>
			<TD COLSPAN="3">&nbsp;&nbsp;
			<A HREF="./?Nit=<?php echo $getMessage->ForumID ?>"><B><?php echo $getForum->ForumName ?></B></A> :
			<A HREF="./?Nit=<?php echo $getMessage->ForumID ?>&Tema=<?php echo $getMessage->TopicID ?>"><B><?php echo $getTopic->TopicName ?></B></A>
			</TD>
		</TR>
		<?php endif ?>
		<TR>
			<TD ALIGN="center" VALIGN="baseline" WIDTH="20">
			<?php switch ( $getMessage->Icon ) {
				case "question": $icon=$getMessage->Icon; $alt="Vprašanje" ; break;
				case "note": $icon=$getMessage->Icon; $alt="Zabeležka" ; break;
				case "lightbulb": $icon=$getMessage->Icon; $alt="Nasvet" ; break;
				case "statement": $icon=$getMessage->Icon; $alt="POZOR!" ; break;
				case "thumbsdown": $icon=$getMessage->Icon; $alt="Buuuuu" ; break;
				case "thumbsup": $icon=$getMessage->Icon; $alt="Bravo!" ; break;
				case "flag": $icon=$getMessage->Icon; $alt="Zastavica" ; break;
				case "tools": $icon=$getMessage->Icon; $alt="Drži kot pribito!" ; break;
				default : $icon='trans'; $alt=''; break;
			} ?><IMG SRC="px/<?php echo $icon ?>.gif" ALIGN="baseline" ALT="<?php echo $alt ?>" BORDER="0" WIDTH="12" HEIGHT="12">
			</TD>
			<TD ALIGN="right" CLASS="a10" VALIGN="baseline" WIDTH="70">Napisal:&nbsp;</TD>
			<TD VALIGN="baseline">
			<?php if ( $getMessage->MemberID ) : ?>
				<A HREF="clani.php?ID=<?php echo $getMessage->MemberID ?>"><B><?php echo ($getMember->ShowPersonalData && $getMember->DisplayName) ? $getMember->Name : $getMember->Nickname ?></B></A>,
			<?php else : ?>
				<B><?php echo $getMessage->UserName ?><FONT COLOR="<?php echo $TxtExColor ?>">*</FONT></B>,
			<?php endif ?>
			<SPAN CLASS="a10"><?php echo formatDate($getMessage->MessageDate,"j.n.y \o\b H:i"); ?></SPAN>
			<?php if ( compareDate($getMessage->MessageDate,$Datum) <= 0 ) : ?>
				<IMG SRC="px/new.gif" ALIGN="baseline" ALT="Novo!" BORDER="0" HEIGHT="8" WIDTH="20">
			<?php endif ?>
			<?php if ( $getMessage->Locked ) : ?>
				<IMG SRC="px/note-lock.gif" ALIGN="baseline" ALT="Trajno sporočilo!" BORDER="0" HEIGHT=12 WIDTH=12>
			<?php endif ?>
			</TD>
			<TD ALIGN="right" CLASS="a10" VALIGN="baseline" WIDTH="45%">
			<?php if ( $_SESSION['MemberID'] && !$IsModerator && !$getMessage->Locked && $getTopic->LockedBy == "" && !$ReadOnly ) : ?>
				<A HREF="javascript:reportSPAM(<?php echo $getMessage->ID ?>);"><IMG SRC="px/bell.gif" ALIGN="baseline" ALT="" WIDTH="12" HEIGHT="12" BORDER="0"> Prijavi neprimerno sporočilo</A> |
			<?php endif ?>
		<?php if ( $IsModerator && $getTopic->LockedBy=="" ) : ?>
			<?php if ( !$getMessage->Locked ) : ?>
				<?php if ( $CanRename && compareDate($getMessage->MessageDate, now()) < 30 ) : ?>
				<IMG SRC="px/note-write.gif" ALIGN="baseline" ALT="Uredi" BORDER="0" HEIGHT=12 WIDTH=12>
				<A HREF="javascript:dialogOpen('oddaj.php?Act=Edt&amp;ID=<?php echo $getMessage->ID ?>');">Uredi</A> |
				<?php endif ?>
				<?php if ( $CanDelete ) : ?>
				<IMG SRC="px/note-del.gif" ALIGN="baseline" ALT="Briši sporočilo" BORDER="0" HEIGHT=12 WIDTH=12>
				<A HREF="javascript:delMessage(<?php echo $getMessage->ID ?>);">Briši</A> |
				<?php endif ?>
				<?php if ( $CanMove ) : ?>
				<IMG SRC="px/move.gif" ALIGN="baseline" ALT="Premakni" BORDER="0" HEIGHT=12 WIDTH=12>
				<A HREF="javascript:loginOpen('admin/move.php?ID=<?php echo $getMessage->ID ?>');">Premakni</A> |
				<?php endif ?>
			<?php endif ?>
			<?php if ( $CanLock ) : ?>
				<IMG SRC="px/lock.gif" ALIGN="baseline" ALT="Zakleni/Odkleni" BORDER="0" HEIGHT=12 WIDTH=12>
				<A HREF="javascript:loginOpen('admin/lock.php?Act=Msg&amp;ID=<?php echo $getMessage->ID ?>');"><?php if ( $getMessage->Locked ) : ?>Odkleni<?php else : ?>Zakleni<?php endif ?></A> |
			<?php endif ?>
		<?php else : ?>
			<?php if ( $_SESSION['MemberID'] == $getMessage->MemberID && compareDate($getMessage->MessageDate,now()) == 0 ) : ?>
				<IMG SRC="px/note-del.gif" ALIGN="baseline" ALT="Briši sporočilo" BORDER="0" HEIGHT=12 WIDTH=12>
				<A HREF="javascript:delMessage(<?php echo $getMessage->ID ?>);">Briši</A> |
			<?php endif ?>
		<?php endif ?>
				<IMG SRC="px/icoprinter.gif" ALIGN="baseline" WIDTH=12 HEIGHT=12 ALT="Oblika primerna za tiskanje" BORDER="0">
				<A HREF="javascript:windowOpen('print.php?What=Diskusije&amp;ID=<?php echo $getMessage->ID ?>')">Natisni</A>&nbsp;
			</TD>
		</TR>
		</TABLE>
	</DIV>
	<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
	<TR>
		<TD ALIGN="center" CLASS="avatar" VALIGN="top" WIDTH="90">
		<?php if ( is_numeric($getMessage->MemberID) ) : ?>
			<?php
			$Slika = 'default';
			if ( isset($settings['Slika']) && fileExists('px/face/'.$settings['Slika'].'.gif')) $Slika = $settings['Slika'];
			switch ( $getMember->AccessLevel ) {
				case 5: $user="<b><i>administrator foruma</i></b>"; break;
				case 4: $user="<b>administrator skupine</b>"; break;
				case 3: $user="<i>moderator</i>"; break;
				case 2: $user="<i>moderator pripravnik</i>"; break;
				default : $user="uporabnik"; break;
			}
			?>
			<IMG SRC="px/face/<?php echo $Slika ?>.gif" BORDER="0"><BR>
			<?php echo $user ?><BR>
			<B><?php echo (int)$getMember->Posts ?></B> sporočil<?php echo koncnica((int)$getMember->Posts,"o,i,a,") ?>
			<p>
			<?php if ( $getMember->WebPage != "" ) : ?><A HREF="<?php echo $getMember->WebPage ?>" TARGET="_blank"><img src="px/home.gif" HEIGHT="16" WIDTH="16" border=0 alt="Domača stran" ALIGN="absmiddle"></A><?php endif ?>
			<?php if ( (int)$getMember->ICQUIN ) : ?><A HREF="http://wwp.icq.com/<?php echo $getMember->ICQUIN ?>" TARGET="_blank"><img src="http://online.mirabilis.com/scripts/online.dll?icq=<?php echo $getMember->ICQUIN ?>&amp;img=5" border=0 ALIGN="absmiddle" alt="<?php echo $getMember->ICQUIN ?>"></A><?php endif ?>
			<?php if ( (int)$getMember->Patron ) : ?><IMG SRC="px/patron.png" BORDER=0 ALIGN="absmiddle" ALT="Donator!" WIDTH="16" HEIGHT="16"><?php endif ?>
			</p>
		<?php endif ?>
		</TD>
		<TD VALIGN="top" CLASS="post">
		<?php if ( $getMessage->AttachedFile != "" ) : ?>
		<TABLE ALIGN="right" BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="130">
		<TR BGCOLOR="<?php echo $FrameColor ?>">
			<TD>
			<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
			<TR BGCOLOR="<?php echo $BckLoColor ?>">
				<TD ALIGN="center" CLASS="a10">Pripeta datoteka:</TD>
			</TR>
			<TR BGCOLOR="<?php echo $BackgColor ?>">
				<?php if ( contains('.jpg,.gif,.png',right($getMessage->AttachedFile,4)) ) : ?>
				<TD CLASS="a10"><A HREF="datoteke/<?php echo $getMessage->AttachedFile ?>" REL="lightbox"><IMG SRC="px/attachedfile.gif" WIDTH=12 HEIGHT=12 ALIGN="absmiddle" ALT="Pripeta slika" BORDER="0">&nbsp;<?php echo $getMessage->AttachedFile ?></A></TD>
				<?php else : ?>
				<TD CLASS="a10"><A HREF="datoteke/<?php echo urlencode($getMessage->AttachedFile) ?>"><IMG SRC="px/attachedfile.gif" WIDTH=12 HEIGHT=12 ALIGN="absmiddle" ALT="Pripeta datoteka" BORDER="0">&nbsp;<?php echo $getMessage->AttachedFile ?></A></TD>
				<?php endif ?>
			</TR>
			</TABLE>
			</TD>
		</TR>
		</TABLE>
		<?php endif ?>

		<?php echo $Bes; ?>

		<?php if ( $getMessage->ChangeDate != "" ) : ?>
		<?php $getChangeMember = getmember($getMessage->ChangeMemberID); ?>
		<DIV CLASS="a10" STYLE="border-top:silver solid 1px;margin-top:5px;padding-top:3px;">
		Spremenil:
		<B><A HREF="clani.php?ID=<?php echo $getMessage->ChangeMemberID ?>"><FONT COLOR="<?php echo $TextColor ?>"><?php echo ($getChangeMember->ShowPersonalData && $getChangeMember->DisplayName) ? $getChangeMember->Name : $getChangeMember->Nickname ?></FONT></A></B>,
		<?php echo formatDate($getMessage->ChangeDate,"j.n.y \o\b H:i") ?>
		</DIV>
		<?php endif ?>
		</TD>
	</TR>
	</TABLE>
	<DIV CLASS="foot">
		<?php if ( $getMessage->MemberID == $_SESSION['MemberID'] ) : ?>
			<?php if ( !$ReadOnly && !$getForum->ViewOnly && $getTopic->LockedBy == "" && !$getMessage->Locked && ($AllowAnonymous || $_SESSION['MemberID'] != 0)
			&& compareDate($getMessage->MessageDate, now()) == 0 ) : ?>
		<IMG SRC="px/note-write.gif" ALIGN="baseline" ALT="Uredi" BORDER="0" HEIGHT=12 WIDTH=12>
		<A HREF="javascript:dialogOpen('oddaj.php?Act=Edt&amp;ID=<?php echo $getMessage->ID ?>');">Uredi</A> |
			<?php endif ?>
		<?php else : ?>
			<?php if ( !$ReadOnly && !$getForum->ViewOnly && $getTopic->LockedBy == "" && ($AllowAnonymous || $_SESSION['MemberID'] != 0) ) : ?> 
		<IMG SRC="px/note-write.gif" ALIGN="baseline" ALT="Odgovori" BORDER="0" HEIGHT=12 WIDTH=12>
		<A HREF="javascript:dialogOpen('oddaj.php?Act=New&amp;ID=<?php echo $getMessage->ID ?>');">Citiraj</A> |
			<?php endif ?>
			<?php if ( $getMessage->MemberID && $getMember->Enabled && $_SESSION['MemberID'] ) : ?>
		<IMG SRC="px/reply.gif" ALIGN="baseline" ALT="Odgovori zasebno" BORDER="0" WIDTH=12 HEIGHT=12>
		<A HREF="javascript:dialogOpen('oddaj.php?Act=Pvt&amp;ID=<?php echo $getMessage->ID ?>');">Odgovori zasebno</A> |
			<?php elseif ( $getMessage->UserEmail != "" && ($AllowAnonymous || $_SESSION['MemberID']) ) : ?>
		<IMG SRC="px/env-rep.gif" ALIGN="baseline" ALT="Odgovori po epošti" BORDER="0" WIDTH=12 HEIGHT=12>
		<A HREF="javascript:dialogOpen('oddaj.php?Act=Rep&amp;ID=<?php echo $getMessage->ID ?>');">Odgovori po e-pošti</A> |
			<?php endif ?>
		<?php endif ?>
		<?php if ( $AllowAnonymous || $_SESSION['MemberID'] ) : ?>
		<IMG SRC="px/env-fwd.gif" ALIGN="baseline" ALT="Posreduj" BORDER="0" WIDTH=12 HEIGHT=12>
		<A HREF="javascript:dialogOpen('oddaj.php?Act=Fwd&amp;ID=<?php echo $getMessage->ID ?>');">Posreduj</A>
		<?php endif ?>
	</DIV>
	<?php endif ?>
</DIV>
<?php } ?>
<?php endif ?>

<?php if ( !$ReadOnly && !$getForum->ViewOnly && $getTopic->LockedBy == "" && ($AllowAnonymous || $_SESSION['MemberID'] != 0) ) : ?>
<DIV CLASS="frmnew">
<IMG SRC="px/note-new.gif" ALIGN="baseline" ALT="Novo" BORDER="0" HEIGHT=12 WIDTH=12>
<A HREF="javascript:dialogOpen('oddaj.php?Act=New&amp;Nit=<?php echo $_GET['Nit'] ?>&amp;Tema=<?php echo $_GET['Tema'] ?>');"><FONT COLOR="<?php echo $TxtExColor ?>">Novo sporočilo</FONT></A>
</DIV>
<?php endif ?>

<?php if ( $NuPg > 1 ) : ?>
<div class="frmmenu">
<TABLE CLASS="frmnav" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<?php $query = (isset($_GET['Nit']) ? "&amp;Nit=". $_GET['Nit'] : "") . (isset($_GET['Tema']) ? "&amp;Tema=". $_GET['Tema'] : "") . (isset($_GET['What']) ? "&amp;What=". $_GET['What'] : "") . (isset($_GET['Find']) ? "&amp;Find=". $_GET['Find'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") . (isset($_GET['Rows']) ? "&amp;Rows=". $_GET['Rows'] : "") ?>
	<TD CLASS="a10" WIDTH="50%" STYLE="border-bottom:1px solid <?php echo $FrameColor ?>;">
	Stran:
	<?php if ( $StPg > 1 ) : ?><A HREF="./?Page=<?php echo $StPg+1 . $query ?>">[&laquo;]</A><?php endif ?>
	<?php if ( $Page > 1 ) : ?><A HREF="./?Page=<?php echo $PrPg . $query ?>">[&lt;]</A><?php endif ?>
	<?php for ( $i=$StPg; $i<=$EdPg; $i++ ) { ?>
	<?php if ( $i == $Page ) : ?>[<FONT COLOR="<?php echo $TxtExColor ?>"><B><?php echo $i ?></B></FONT>]<?php else : ?><A HREF="./?Page=<?php echo $i . $query ?>">[<?php echo $i ?>]</A><?php endif ?>
	<?php } ?>
	<?php if ( $Page < $EdPg ) : ?><A HREF="./?Page=<?php echo $NePg . $query ?>">[&gt;]</A><?php endif ?>
	<?php if ( $NuPg > $EdPg ) : ?><A HREF="./?Page=<?php echo $EdPg+1 . $query ?>">[&raquo;]</A><?php endif ?>
	</TD>
	<?php $query = (isset($_GET['Nit']) ? "&amp;Nit=". $_GET['Nit'] : "") . (isset($_GET['Tema']) ? "&amp;Tema=". $_GET['Tema'] : "") . (isset($_GET['What']) ? "&amp;What=". $_GET['What'] : "") . (isset($_GET['Find']) ? "&amp;Find=". $_GET['Find'] : "") . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") ?>
	<TD ALIGN="right" CLASS="a10" WIDTH="50%" STYLE="border-bottom:1px solid <?php echo $FrameColor ?>;">
	Prikaži |
	<A HREF="./?Rows=5<?php echo $query ?>">5</A> |
	<A HREF="./?Rows=10<?php echo $query ?>">10</A> |
	<A HREF="./?Rows=20<?php echo $query ?>">20</A> |
	<A HREF="./?Rows=50<?php echo $query ?>">50</A> |
	besedil
	</TD>
</TR>
</TABLE>
</div>
<?php endif ?>
