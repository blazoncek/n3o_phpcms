<?php
/* ~edit_frmPoll.php - Editing forum polls.
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

if ( !isset($_GET['TopicID']) ) $_GET['TopicID'] = "0";

$Podatek = $db->get_row("SELECT * FROM frmPoll WHERE TopicID = ". (int)$_GET['TopicID']);
?>
<script language="JavaScript" type="text/javascript">
<!-- //
function customResize() {
	// vertically resize edit child divs
	edit = $("#divContent").height(0).height( $("#divEdit").height() + $("#divEdit").position().top - $("#divContent").position().top - 15 );
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
					if (empty(fObj.A1))	{alert("Anketa mora imeti vsaj 2 odgovora!"); fObj.A1.focus(); return false;}
					if (empty(fObj.A2))	{alert("Anketa mora imeti vsaj 2 odgovora!"); fObj.A2.focus(); return false;}
					return true;
				} // pre-submit callback
			});
			return false;
		});
	});

	// resize editing divs
	window.customResize();
});
//-->
</script>
<DIV CLASS="subtitle">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
	<td><div id="ToggleFrame" style="display:none;">&nbsp;<A HREF="javascript:toggleFrame()"><img src="pic/control.frame.gif" height="14" width="14" alt="Preklop celo/zmanj�ano okno" border="0" align="absmiddle" class="icon">&nbsp;List</a></div></td>
	<td id="editNote" align="right"><B>Ankete</B>&nbsp;&nbsp;</td>
</tr>
</table>
</DIV>

<DIV STYLE="padding:5px;">
<TABLE ALIGN="center" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD VALIGN="top">

	<FIELDSET ID="fldData">
	<LEGEND ID="lgdData">Anketa</LEGEND>
	<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
	<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
	<TR>
		<TD ALIGN="right">Zaklenjeno:&nbsp;</TD>
		<TD><INPUT TYPE="Checkbox" NAME="Locked"<?php echo (($Podatek && $Podatek->Locked)? " CHECKED": "") ?>></TD>
		<TD ALIGN="right"></TD>
	</TR>
	<TR>
		<TD ALIGN="right" VALIGN="top"><B>Vprašanje:</B>&nbsp;<BR> <SPAN CLASS="f10 gry">max 512 znakov</SPAN>&nbsp;</TD>
		<TD COLSPAN="2"><TEXTAREA NAME="Q" ROWS="4" STYLE="width:100%;" WRAP="virtual"><?php echo ($Podatek)? $Podatek->Question: "" ?></TEXTAREA></TD>
	</TR>
<?php for ( $i=1; $i<=10; $i++ ) { ?>
	<TR>
		<TD ALIGN="right">Odg. <?php echo $i ?>:&nbsp;</TD>
		<TD COLSPAN="2"><INPUT TYPE="Text" NAME="A<?php echo $i ?>" MAXLENGTH="64" VALUE="<?php if ($Podatek) eval('echo $Podatek->A'.$i.';') ?>" STYLE="width:100%;"></TD>
	</TR>
<?php } ?>
<!--
	<TR>
		<TD ALIGN="right" COLSPAN="3" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="Submit" VALUE="Zapiši" TABINDEX="1" CLASS="but"></TD>
	</TR>
-->
	</TABLE>
	</FORM>
	</FIELDSET>

	</TD>
	<TD VALIGN="top">

<?php if ( $Podatek ) : ?>
	<!-- rezultati -->
	<FIELDSET ID="fldResults">
	<LEGEND ID="lgdResults">Rezultati</LEGEND>
	<TABLE ID="results" BORDER="0" CELLPADDING="2" CELLSPACING="1" WIDTH="100%">
	<TR>
		<TD ALIGN="left">
		<?php $Size = 100; ?>
		<!--DIV><?php echo $Podatek->Question ?></DIV-->
		<DIV CLASS="f10 gry" STYLE="border-bottom:darkgrey solid 1px;padding-bottom:3px;margin-bottom:10px;">Skupaj <B><?php echo $Podatek->Votes ?></B> glas<?php eval("echo koncnica($Podatek->Votes,' ,ova,ovi,ov');"); ?></DIV>
<?php
		for ( $i=1; $i<=$Podatek->Answers; $i++ ) {
			$Odg = '$Podatek->A' . $i;
			$Rez = '$Podatek->R' . $i;
			if ( $Podatek->Votes == 0 )
				$Pct=0;
			else
				eval("\$Pct = round($Rez*100 / \$Podatek->Votes);");
			$red = $Size * $Pct/100;
			$wht = $Size * (100-$Pct)/100;
			eval("echo \"<div class='f10'>\" . $Odg . \"</div>\n\";");
			echo "&nbsp;&nbsp;";
			echo (($red!=0)? "<div style=\"display:inline-block;background-color:red;width:" . $red . "px;height:10px;\"></div>": "");
			echo (($wht!=0)? "<div style=\"display:inline-block;background-color:white;width:" . $wht . "px;height:10px;\"></div>": "");
			echo "&nbsp;&nbsp;&nbsp;<B>";
			eval("echo $Rez;");
			echo "</B>&nbsp;glas";
			eval("echo koncnica($Rez,' ,ova,ovi,ov');");
			echo "&nbsp;($Pct%)<BR>\n";
		}
?>
		</TD>
	</TR>
	</TABLE>
	</FIELDSET>
<?php endif ?>

	</TD>
</TR>
</TABLE>

<FIELDSET ID="fldVoters">
	<LEGEND>Glasovalci</LEGEND>
<div id="divContent" style="overflow: auto;">
<?php if ( $Podatek ) : ?>
	<?php
		$List = $db->get_results(
			"SELECT M.ID, M.NickName, M.Name, M.Email, PV.VoteDate, PV.Answer
			FROM frmPollVotes PV
				LEFT JOIN frmMembers M ON PV.MemberID = M.ID
			WHERE TopicID = ".(int)$_GET['TopicID']."
			ORDER BY PV.VoteDate"
		);
	?>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="100%">
	<?php if ( !$List ) : ?>
<TR BGCOLOR="white">
	<TD ALIGN="center" VALIGN="middle">
	<BR><BR><B>Ni podatkov o glasovanju!</B><BR><BR><BR>
	</TD>
</TR>
	<?php else : ?>
	<?php
	$CurrentRow = 1;
	$RecordCount = count($List);
	foreach ( $List as $Item ) {
		echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
		echo "<TD>&nbsp;";
		echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=frmMembers&ID=$Item->ID')\">";
		echo "$Item->NickName ($Item->Name)";
		echo "</A>";
		echo "</TD>\n";
		echo "<TD>&nbsp;$Item->Email</TD>\n";
		echo "<TD ALIGN=\"right\">O:&nbsp;$Item->Answer&nbsp;</TD>\n";
		echo "<TD ALIGN=\"right\" CLASS=\"a10\" VALIGN=\"top\">";
		echo date("j.n.y \o\b H:i",sqldate2time($Item->VoteDate)) . "&nbsp;";
		echo "</TD>\n";
		echo "</TR>\n";
		$CurrentRow++;
	}
	?>
	<?php endif ?>
</TABLE>
<?php endif ?>
</div>
</FIELDSET>
</DIV>
