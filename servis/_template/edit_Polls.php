<?php
/*~ edit_Ankete.php - Editing polls.
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

if ( !isset($_GET['ID']) ) $_GET['ID'] = 0;

$Podatek = $db->get_row("SELECT * FROM Ankete WHERE ID=". (int)$_GET['ID']);

// get ACL
if ( $Podatek )
	$ACL = userACL($Podatek->ACLID);
else
	$ACL = $ActionACL;
?>
<script language="JavaScript" type="text/javascript">
<!-- //
$(document).ready(function(){
	// bind to the form's submit event
	$("form[name='Vnos']").each(function(){
		$(this).submit(function(){
			$(this).ajaxSubmit({
				target: '#divEdit',
				beforeSubmit: function( formDataArr, jqObj, options ) {
					var fObj = jqObj[0];	// form object
					if (empty(fObj.O1))	{alert("Answer #1 required!"); fObj.O1.focus(); return false;}
					if (empty(fObj.O2))	{alert("Answer #2 required!"); fObj.O2.focus(); return false;}
					$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
					return true;
				} // pre-submit callback
			});
			return false;
		});
	});

	// set popup calendar
	var options = {
		dateFormat: 'd.m.yy',
		firstDay: 1,
		//changeMonth: true,
		//changeYear: true,
		//dayNamesMin: ['Ne','Po','To','Sr','Če','Pe','So'],
		//monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun','Jul','Avg','Sep','Okt','Nov','Dec'],
		//monthNames: ['Januar','Februar','Marec','April','Maj','Junij','Julij','Avgust','September','Oktober','November','December'],
		duration: ''
	};
	$("input[name='D']").datepicker(options);

	// refresh list
	listRefresh();
});
//-->
</script>

<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0">
<TR>
	<TD VALIGN="top">

	<FIELDSET ID="fldData" style="width:380px;">
	<LEGEND ID="lgdData">
<?php if ( contains( $ACL, "W" ) && $Podatek ) {
		echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=sysACL&ACL=".$Action->Action;
		echo "&AnketaID=" . $_GET['ID'] . (($Podatek->ACLID!="")? "&ID=".$Podatek->ACLID: "") . "')\" TITLE=\"Edit permissions\">";
		echo "<IMG SRC=\"pic/control.permissions.gif\" HEIGHT=\"16\" WIDTH=\"16\" BORDER=0 ALT=\"Permissions\" ALIGN=\"absmiddle\"></A>&nbsp;:";
}
?>
		Basic&nbsp;information</LEGEND>
	<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
	<TABLE BORDER=0 CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<?php if ( !$Podatek ) : ?>
	<TR>
		<TD ALIGN="right">Language:&nbsp;</TD>
		<TD><SELECT NAME="Jezik" SIZE="1">
			<OPTION VALUE="">- for all -</OPTION>
<?php
		// determine next poll date (14 days ahead)
		$Datum = $db->get_var("SELECT max(Datum) FROM Ankete WHERE (Jezik='". $_GET['Tip'] ."' OR Jezik IS NULL)");
		if ( $Datum && compareDate($Datum,now()) < 0 )
			$Datum = date("j.n.Y", sqldate2time($Datum)+24*3600*14);
		else
			$Datum = date("j.n.Y");

		$Jeziki = $db->get_results("SELECT Jezik, Opis FROM Jeziki WHERE Enabled=1");
		if ( $Jeziki ) foreach ( $Jeziki as $Jezik )
			echo "<OPTION VALUE=\"$Jezik->Jezik\"".(($Jezik->Jezik==$Podatek->Jezik || ($_GET['ID']==0 && $_GET['Tip']==$Jezik->Jezik))? " SELECTED": "").">$Jezik->Opis</OPTION>\n";
?>
		</SELECT>
		</TD>
	</TR>
<?php else : ?>
		<?php $Datum = date("j.n.Y", sqldate2time($Podatek->Datum)); ?>
		<INPUT NAME="Jezik" VALUE="<?php echo $Podatek->Jezik ?>" TYPE="Hidden">
<?php endif ?>
	<TR>
		<TD ALIGN="right">Date:&nbsp;<br><SPAN CLASS="f10 gry">(start)</SPAN>&nbsp;</TD>
		<TD><INPUT TYPE="Text" NAME="D" SIZE="10" MAXLENGTH="10" VALUE="<?php echo $Datum ?>" CLASS="txt"></TD>
		<TD ALIGN="right">Multiple:&nbsp;<INPUT TYPE="Checkbox" NAME="Multiple"<?php echo (($Podatek && $Podatek->Multiple) ? " CHECKED" : "") ?>></TD>
	</TR>
	<TR>
		<TD ALIGN="right" VALIGN="top"><B>Question:</B><BR> <SPAN CLASS="f10 gry">max 255 chars</SPAN>&nbsp;</TD>
		<TD COLSPAN="2"><TEXTAREA NAME="V" ROWS="4" STYLE="width:100%;" WRAP="virtual"><?php echo ($Podatek) ? $Podatek->Vprasanje : "" ?></TEXTAREA></TD>
	</TR>
	<TR>
		<TD ALIGN="right" VALIGN="top"><B>Comment:</B><BR> <SPAN CLASS="f10 gry">max 255 chars</SPAN>&nbsp;</TD>
		<TD COLSPAN="2"><TEXTAREA NAME="K" ROWS="4" STYLE="width:100%;" WRAP="virtual"><?php echo ($Podatek) ? $Podatek->Komentar : "" ?></TEXTAREA></TD>
	</TR>
<?php for ( $i=1; $i<=10; $i++ ) : ?>
	<TR>
		<TD ALIGN="right">Answer #<?php echo $i ?>:&nbsp;</TD>
		<TD COLSPAN="2"><INPUT TYPE="Text" NAME="O<?php echo $i ?>" MAXLENGTH="64" VALUE="<?php if ($Podatek) eval('echo $Podatek->Odg'.$i.';') ?>" STYLE="width:100%;"></TD>
	</TR>
<?php endfor ?>
<?php if ( contains($ACL,"W") ) : ?>
	<TR>
		<TD ALIGN="right" COLSPAN="3" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;">
		<INPUT TYPE="Submit" VALUE=" Save " TABINDEX="1" CLASS="but"></TD>
	</TR>
<?php endif ?>
	</TABLE>
	</FORM>
	</FIELDSET>

	</TD>
	<TD VALIGN="top">

<?php if ( (int)$_GET['ID'] ) : ?>
	<FIELDSET ID="fldData" style="width:240px;">
	<LEGEND ID="lgdData">Results</LEGEND>
	<TABLE ID="results" BORDER="0" CELLPADDING="2" CELLSPACING="1" WIDTH="100%">
	<TR>
		<TD ALIGN="left">
		<?php $VsiGlasovi = $Podatek->Rez1 + $Podatek->Rez2 + $Podatek->Rez3 + $Podatek->Rez4 + $Podatek->Rez5 + $Podatek->Rez6 + $Podatek->Rez7 + $Podatek->Rez8 + $Podatek->Rez9 + $Podatek->Rez10; ?>
		<?php $Size = 100; ?>
		<DIV STYLE="border-bottom:darkgrey solid 1px;padding-bottom:3px;margin-bottom:10px;">Skupaj <B><?php echo $VsiGlasovi ?></B> glasov</DIV>
<?php
		for ( $i=1; $i<=$Podatek->StOdg; $i++ ) {

			$Odg  = eval("return \$Podatek->Odg". $i .";");
			$Rez  = eval("return \$Podatek->Rez". $i .";");
			$Pct  = ($VsiGlasovi > 0) ? round($Rez*100 / $VsiGlasovi) : 0;
			$NPct = 100 - $Pct;
			$red  = $Size * $Pct/100;
			$wht  = $Size * $NPct/100;

			echo $Odg ."<br>\n";
			echo "&nbsp;&nbsp;";
			if ( $Pct <> 0) echo "<div style=\"display:inline-block;background-color:red;width:". $red ."px;height:10px;\"></div>";
			if ( $NPct<> 0) echo "<div style=\"display:inline-block;background-color:white;width:". $wht ."px;height:10px;\"></div>";
			echo "&nbsp;&nbsp;&nbsp;<B>";
			eval("echo $Rez;");
			echo "</B>&nbsp;vote";
			eval("echo koncnica($Rez,' ,s,s,s');"); // for slovenian word endings
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
