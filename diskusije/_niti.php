<?php
/* _niti.php - forum threads
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
<!-- niti -->
<div class="frmpost">
<TABLE CLASS="title" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD WIDTH="20"></TD>
	<TD><B>Niti</B></TD>
	<TD WIDTH="17%">&nbsp;<B>Moderira</B></TD>
	<TD ALIGN="right" WIDTH="5%"><B>Št.sp.</B>&nbsp;</TD>
	<TD ALIGN="right" WIDTH="20%"><B>Zadnje sporočilo</B>&nbsp;</TD>
</TR>
</TABLE>
<TABLE CLASS="list" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<?php
$getForums = getforums(0, $AccessLevel>1 ? 1 : 0, isset($_GET['Ord']) ? $_GET['Ord'] : "");

$Category = $i = 0;
$Color = $BckHiColor;
if ( count($getForums) ) foreach ( $getForums AS $getForum ) {

	$Color= ($Color==$BackgColor ? $BckHiColor : $BackgColor);
	$getLastPost = getlastpost($getForum->ID);

	if ( $Category != $getForum->CategoryID ) {
		$Category = $getForum->CategoryID;
		echo "<TR BGCOLOR=\"". $BckLoColor ."\">\n";
		echo "<TD ALIGN=\"center\" COLSPAN=\"6\" HEIGHT=\"18\"><B>". $getForum->CategoryName ."</B></TD>\n";
		echo "</TR>\n";
	}
	
	echo "<TR BGCOLOR=\"". $Color ."\">\n";
	echo "<TD ALIGN=\"center\" VALIGN=\"baseline\" WIDTH=\"20\">\n";

	if ( isset($_GET['Nit']) && $_GET['Nit']==$getForum->ID ) {
		echo "<IMG SRC=\"px/bo.gif\" BORDER=\"0\" HEIGHT=\"12\" WIDTH=\"12\">\n";
	} else {
		if ( $getForum->Password!="" ) {
			echo "<IMG SRC=\"px/bl.gif\" BORDER=\"0\" HEIGHT=\"12\" WIDTH=\"12\">\n";
		} else if ( isDate($getLastPost->LastMessageDate) && compareDate($getLastPost->LastMessageDate,$Datum) <= 0 ) {
			echo "<IMG SRC=\"px/bn.gif\" ALT=\"Novo!\" BORDER=\"0\" HEIGHT=\"12\" WIDTH=\"12\">\n";
		} else if ( $getForum->ViewOnly ) {
			echo "<IMG SRC=\"px/bc.gif\" BORDER=\"0\" HEIGHT=\"12\" WIDTH=\"12\">\n";
		} else {
			echo "<IMG SRC=\"px/bs.gif\" BORDER=\"0\" HEIGHT=\"12\" WIDTH=\"12\">\n";
		}
	}

	echo "</TD>\n";
	echo "<TD VALIGN=\"baseline\">\n";

	echo "<A HREF=\"./?Nit=". $getForum->ID . (isset($_GET['Sort']) ? "&amp;Sort=". $_GET['Sort'] : "") ."\"><B>". $getForum->ForumName ."</B></A>\n";

	if ( isDate($getLastPost->LastMessageDate) && compareDate($getLastPost->LastMessageDate,$Datum) <= 0 )
		echo "<IMG SRC=\"px/new.gif\" ALIGN=\"baseline\" ALT=\"Novo!\" BORDER=\"0\" HEIGHT=\"8\" WIDTH=\"20\">\n";

	if ( $getForum->Description != "" )
		echo "<DIV CLASS=\"a10\">". $getForum->Description ."</DIV>\n";

	echo "</TD>\n";
	echo "<TD CLASS=\"a10\" VALIGN=\"baseline\" WIDTH=\"17%\">\n";

	// display moderators
	$getModerators = getmoderators($getForum->ID);
	$i = count($getModerators);
	if ( $i ) foreach ( $getModerators AS $getModerator ) {
		echo "<A HREF=\"clani.php?ID=". $getModerator->ID. "\">";
		echo ($getModerator->ShowPersonalData && $getModerator->DisplayName ? $getModerator->Name : $getModerator->NickName);
		echo "</A>";
		if ( --$i ) echo ", ";
	}

	$getModerator = getmoderators($getForum->ID,$_SESSION['MemberID']);
	$getMsgToApprove = getmsgtoapprove($getForum->ID);
	if ( $getMsgToApprove && $getModerator->ID ) {
		echo "<DIV CLASS=\"a10\">";
		echo "<IMG SRC=\"px/note-lock.gif\" ALIGN=\"baseline\" ALT=\"Odobritev sporočil\" BORDER=\"0\" HEIGHT=12 WIDTH=12>";
		echo "<A HREF=\"javascript:dialogOpen('admin/approve.php?Nit=". $getForum->ID ."');\"> Čakajoča sp.</A>";
		echo "</DIV>\n";
	}

	echo "</TD>\n";
	echo "<TD ALIGN=\"right\" CLASS=\"a10\" VALIGN=\"baseline\" WIDTH=\"5%\">". getmsgcount($getForum->ID) ."&nbsp;</TD>\n";
	echo "<TD ALIGN=\"right\" CLASS=\"a10\" VALIGN=\"baseline\" WIDTH=\"20%\">\n";
	
	if ( isDate($getLastPost->LastMessageDate) && compareDate($getLastPost->LastMessageDate, addDate(now(),-7)) <= 0 )
		echo "<FONT COLOR=\"". $TxtExColor ."\">";
	echo formatDate($getLastPost->LastMessageDate,"j.n.y \o\b H:i");
	if ( isDate($getLastPost->LastMessageDate) && compareDate($getLastPost->LastMessageDate, addDate(now(),-7)) <= 0 ) 
		echo "</FONT>";

	if ( isDate($getLastPost->LastMessageDate) ) {
		if ( (int)$getLastPost->LastPostBy ) {
			$getMember = getmember((int)$getLastPost->LastPostBy);
			echo "<BR>oddal <B><A HREF=\"clani.php?ID=". $getMember->ID ."\"><FONT COLOR=\"". $TextColor ."\">";
			echo ((int)$getMember->ShowPersonalData && (int)$getMember->DisplayName ? $getMember->Name : $getMember->Nickname);
			echo "</FONT></A></B>\n";
		}
	}

	echo "</TD>\n";
	echo "</TR>\n";

} else {
	echo "<TR BGCOLOR=\"". $BackgColor ."\"><TD ALIGN=\"center\">V diskusijah ni nobenega sporočila!</TD></TR>\n";
}
?>
</TABLE>
</div>