<?php
/*~ edit_frmForums.php - edit forum thread data
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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "0";

$Podatek = $db->get_row( "SELECT * FROM frmForums WHERE ID = ".(int)$_GET['ID'] );
?>

<script language="JavaScript" type="text/javascript">
<!-- //
function customResize () {
	// vertically resize edit child divs
	edit = $("#divContent").height(0).height( $("#divEdit").height() + $("#divEdit").position().top - $("#divContent").position().top );
	// fix scroller problem when resizing
	if ( $("#divTopics").html() ) $("#divTopics").height(0);
	// actualy resize
	if ( $("#divModeratorji").html() ) $("#divModeratorji").height( $("#fldModeratorji").parent().innerHeight() - 26 );
	if ( $("#divTopics").html() ) $("#divTopics").height( edit.height() + edit.position().top - $("#divTopics").position().top - 16 );
}

$(document).ready(function(){
	window.customResize = customResize;

	// bind to the form's submit event
	$("form[name='Vnos']").each(function(){
		$(this).submit(function(){
			$(this).ajaxSubmit({
				target: '#divEdit',
				beforeSubmit: function( formDataArr, jqObj, options ) {
					var fObj = jqObj[0];	// form object
					if ( empty(fObj.ForumName) ) { alert("Prosim vnesite ime."); fObj.ForumName.focus(); return false; }
					$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Posodabljam" border="0" height="14" width="14" align="absmiddle">&nbsp;: Posodabljam ...</span>');
					return true;
				} // pre-submit callback
			});
			return false;
		});
	});
	// add change events
	$("input:checkbox").change(function(){
		this.value = this.checked ? "yes" : "no";
		this.blur();
	});

	// resize content div
	window.customResize();
	
	// load subdata
	if ( $("#divTopics").text() )    $("#divTopics").load('inc.php?Izbor=frmTopic&ForumID=<?php echo $_GET['ID'] ?>');
	if ( $("#divModeratorji").text() )    $("#divModeratorji").load('inc.php?Izbor=frmModeratorji&ForumID=<?php echo $_GET['ID'] ?>');

	// refresh list
	listRefresh();
});
//-->
</script>

<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD VALIGN="top">

	<FIELDSET ID="fldData">
		<LEGEND ID="lgdData">Osnovni&nbsp;podatki</LEGEND>

	<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
	<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="2" WIDTH="100%">
	<TR>
		<TD><B>Skupina:</B>&nbsp;</TD>
		<TD COLSPAN="3">
		<SELECT NAME="CategoryID" SIZE="1">
	<?php 
		$Categories = $db->get_results( "SELECT ID, CategoryName FROM frmCategories ORDER BY CategoryOrder" );
		if ( $Categories ) foreach ( $Categories as $Category )
			echo "<OPTION VALUE=\"$Category->ID\"".(($Podatek && $Podatek->CategoryID == $Category->ID)? " SELECTED STYLE=\"background-color: #99CCFF;\"": "").">$Category->CategoryName</OPTION>\n";
	?>
		</SELECT>
		</TD>
	</TR>
	<TR>
		<TD><B>Ime:</B><BR><span class="f10">Opis:</span></TD>
		<TD COLSPAN="3"><INPUT NAME="ForumName" TYPE="Text" MAXLENGTH="50" VALUE="<?php if ( $Podatek ) echo $Podatek->ForumName ?>" CLASS="txt" STYLE="width:100%;"></TD>
	</TR>
	<TR>
		<TD VALIGN="top" COLSPAN="4">
		<TEXTAREA NAME="Description" ROWS="4" CLASS="txt" STYLE="width:100%;"><?php if ( $Podatek ) echo $Podatek->Description ?></TEXTAREA></TD>
	</TR>
	<TR>
		<TD>Geslo:&nbsp;</TD>
		<TD COLSPAN="3"><INPUT NAME="Password" TYPE="Password" MAXLENGTH="16" VALUE="<?php if ( $Podatek ) echo $Podatek->Password ?>" CLASS="txt" STYLE="width:100%;"></TD>
	</TR>
	<TR>
		<TD VALIGN="baseline" ROWSPAN="2">Moderator:&nbsp;</TD>
		<TD COLSPAN="1"><SELECT NAME="Moderator" SIZE="1">
	<?php 
		$Moderators = $db->get_results( "SELECT ID, Name, Nickname FROM frmMembers WHERE AccessLevel > 1 ORDER BY AccessLevel DESC, Nickname" );
		if ( $Moderators ) foreach ( $Moderators as $Moderator )
			echo "<OPTION VALUE=\"$Moderator->ID\"".(($Podatek && $Podatek->ModeratorID == $Moderator->ID)? " SELECTED STYLE=\"background-color: #99CCFF;\"": "").">$Moderator->Name</OPTION>\n";
	?>
		</SELECT><BR>
		</TD>
		<TD ALIGN="right" VALIGN="baseline">Čiščenje:</TD>
		<TD VALIGN="baseline"><SELECT NAME="PurgeDays" SIZE="1">
			<OPTION VALUE="0">brez</OPTION>
			<OPTION VALUE="30" <?php if ( $Podatek && $Podatek->PurgeDays==30 ) echo "SELECTED" ?>>30 dni</OPTION>
			<OPTION VALUE="90" <?php if ( $Podatek && $Podatek->PurgeDays==90 ) echo "SELECTED" ?>>90 dni</OPTION>
			<OPTION VALUE="180" <?php if ( $Podatek && $Podatek->PurgeDays==180 ) echo "SELECTED" ?>>180 dni</OPTION>
			<OPTION VALUE="365" <?php if ( $Podatek && $Podatek->PurgeDays==365 ) echo "SELECTED" ?>>1 leto</OPTION>
		</SELECT></TD>
	</TR>
	<TR>
		<TD><INPUT NAME="NotifyModerator" TYPE="Checkbox" <?php if ( $Podatek && $Podatek->NotifyModerator ) echo "CHECKED VALUE=\"yes\""; else echo "VALUE=\"no\""; ?>> obveščaj</TD>
		<TD ALIGN="right">Privatna:&nbsp;</TD>
		<TD><INPUT NAME="Private" TYPE="Checkbox" <?php if ( $Podatek && $Podatek->Private ) echo "CHECKED VALUE=\"yes\""; else echo "VALUE=\"no\""; ?>></TD>
	</TR>
	<TR>
		<TD>Odobritev:&nbsp;</TD>
		<TD><INPUT NAME="ApprovalRequired" TYPE="Checkbox" <?php if ( $Podatek && $Podatek->ApprovalRequired ) echo "CHECKED VALUE=\"yes\""; else echo "VALUE=\"no\""; ?>></TD>
		<TD ALIGN="right">Skrita:&nbsp;</TD>
		<TD><INPUT NAME="Hidden" TYPE="Checkbox" <?php if ( $Podatek && $Podatek->Hidden ) echo "CHECKED VALUE=\"Yes\""; else echo "VALUE=\"no\""; ?>></TD>
	</TR>
	<TR>
		<TD>Samo prikaz:&nbsp;</TD>
		<TD><INPUT NAME="ViewOnly" TYPE="Checkbox" <?php if ( $Podatek && $Podatek->ViewOnly ) echo "CHECKED VALUE=\"yes\""; else echo "VALUE=\"no\""; ?>></TD>
		<TD ALIGN="right">Ankete&nbsp;</TD>
		<TD><INPUT NAME="PollEnabled" TYPE="Checkbox" <?php if ( $Podatek && $Podatek->PollEnabled ) echo "CHECKED VALUE=\"yes\""; else echo "VALUE=\"no\""; ?>></TD>
	</TR>
	<TR>
		<TD VALIGN="top">Upload:&nbsp;</TD>
		<TD VALIGN="top" COLSPAN="3" NOWRAP><INPUT NAME="AllowFileUploads" TYPE="Checkbox" <?php if ( $Podatek && $Podatek->AllowFileUploads ) echo "CHECKED VALUE=\"ys\""; else echo "VALUE=\"no\""; ?>>
		<FONT COLOR="Gray" CLASS="f10">(.txt,.jpg,...)</FONT> <INPUT NAME="UploadType" TYPE="Text" MAXLENGTH="64" VALUE="<?php if ( $Podatek ) echo $Podatek->UploadType ?>" CLASS="txt" STYLE="width:130px;"><!BR>
		max <INPUT NAME="MaxUploadSize" TYPE="Text" SIZE="3" MAXLENGTH="3" VALUE="<?php if ( $Podatek ) echo $Podatek->MaxUploadSize ?>" CLASS="txt"> kB&nbsp;</TD>
	</TR>
	<TR>
		<TD ALIGN="right" COLSPAN="4" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="Submit" VALUE="Zapiši" TABINDEX="1" CLASS="but"></TD>
	</TR>
	</TABLE>
	</FORM>
	</FIELDSET>
	</TD>

	<TD VALIGN="top" WIDTH="280">
	<FIELDSET ID="fldModeratorji">
		<LEGEND>Dodatni moderatorji</LEGEND>
		<DIV ID="divModeratorji" STYLE="overflow:auto;"><img src="pic/control.spinner.gif" alt="Nalagam" border="0"> Nalagam ...</DIV>
	</FIELDSET>
	</TD>
</TR>
</TABLE>
<FIELDSET ID="fldTopics">
	<LEGEND><a href="javascript:void(0);" onclick="$('#divTopics').load('inc.php?Izbor=frmTopic&ForumID=<?php echo $_GET['ID'] ?>');" title="Osveži"><img src="pic/control.refresh.gif" alt="Osveži" border="0" align="absmiddle"></a>	: Teme</LEGEND>
	<DIV ID="divTopics" STYLE="overflow:auto;"><img src="pic/control.spinner.gif" alt="Nalagam" border="0"> Nalagam ...</DIV>
</FIELDSET>
