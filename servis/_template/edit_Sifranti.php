<?php
/*~ edit_Sifranti.php - Editing parameters.
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

$Podatek = $db->get_row("SELECT * FROM Sifranti WHERE SifrantID = ". (int)$_GET['ID']);
if ( $Podatek )
	$ACL = userACL($Podatek->ACLID);
else
	$ACL = $ActionACL;
?>
<script language="JavaScript" type="text/javascript">
<!-- //
$(document).ready(function(){
	$("form[name='Vnos']").each(function(){
		$(this).submit(function(){
			$(this).ajaxSubmit({
				target: '#divEdit',
				beforeSubmit: function( formDataArr, jqObj, options ) {
					var fObj = jqObj[0];	// form object
					if (empty(fObj.Ctrl))	{alert("Prosim, vnesi tip!"); fObj.Ctrl.focus(); return false;}
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
		dayNamesMin: ['Ne','Po','To','Sr','Če','Pe','So'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun','Jul','Avg','Sep','Okt','Nov','Dec'],
		monthNames: ['Januar','Februar','Marec','April','Maj','Junij','Julij','Avgust','September','Oktober','November','December'],
		duration: ''
	};
	$("input[name='DVal1']").datepicker(options);
	$("input[name='DVal2']").datepicker(options);
	$("input[name='DVal3']").datepicker(options);

	// refresh list
	listRefresh();
});
//-->
</script>

<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0">
<TR>
	<TD VALIGN="top">

<FIELDSET ID="fldData" style="width:260px;">
<LEGEND ID="lgdData">
<?php if ( contains( $ACL, "W" ) && $Podatek ) {
		echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=ACL&ACL=".$Action->Action;
		echo "&SifrantID=" . $_GET['ID'] . (($Podatek->ACLID!="")? "&ACLID=".$Podatek->ACLID: "") . "')\" TITLE=\"Edit permissions\">";
		echo "<IMG SRC=\"pic/control.permissions.gif\" HEIGHT=\"16\" WIDTH=\"16\" BORDER=0 ALT=\"Permissions\" ALIGN=\"absmiddle\"></A>&nbsp;:";
}
?>
	Basic&nbsp;information</LEGEND>
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD ALIGN="right"><B>Type:</B>&nbsp;</TD>
	<TD>
<?php if ( isset($_GET['Tip']) ) : ?>
	<INPUT NAME="Ctrl" TYPE="Hidden" VALUE="<?php echo $_GET['Tip'] ?>"><FONT COLOR="red"><B><?php echo $_GET['Tip'] ?></B></FONT>
<?php elseif ( $Podatek ) : ?>
	<INPUT NAME="Ctrl" TYPE="Hidden" VALUE="<?php echo $Podatek->SifrCtrl ?>"><FONT COLOR="red"><B><?php echo $Podatek->SifrCtrl ?></B></FONT>
<?php else : ?>
	<INPUT TYPE="text" NAME="Ctrl" MAXLENGTH="4" SIZE="4" VALUE="">
<?php endif ?>
	</TD>
</TR>
<TR>
	<TD ALIGN="right"><B>Ident:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="Text" MAXLENGTH="10" SIZE="10" VALUE="<?php echo ($Podatek)? $Podatek->SifrText: "" ?>"></TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="baseline"><B># value 1:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="NVal1" VALUE="<?php echo ($Podatek)? $Podatek->SifNVal1: "" ?>">
		<?php echo ($Podatek) ?
			"<div class=\"f10 gry\">". $Podatek->SifNVal1Desc ."</div>" :
			"<div><INPUT TYPE=\"text\" NAME=\"NVal1Desc\" CLASS=\"f10\" style=\"color:#aaa;border:solid 1px #999;\" VALUE=\" field description\" onfocus=\"this.value==' field description' ? this.value='' : i=0;\"></div>" ?>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="baseline"><B># value 2:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="NVal2" VALUE="<?php echo ($Podatek)? $Podatek->SifNVal2: "" ?>">
		<?php echo ($Podatek) ?
			"<div class=\"f10 gry\">". $Podatek->SifNVal2Desc ."</div>" :
			"<div><INPUT TYPE=\"text\" NAME=\"NVal2Desc\" CLASS=\"f10\" style=\"color:#aaa;border:solid 1px #999;\" VALUE=\" field description\" onfocus=\"this.value==' field description' ? this.value='' : i=0;\"></div>" ?>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="baseline"><B># value 3:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="NVal3" VALUE="<?php echo ($Podatek)? $Podatek->SifNVal3: "" ?>">
		<?php echo ($Podatek) ?
			"<div class=\"f10 gry\">". $Podatek->SifNVal3Desc ."</div>" :
			"<div><INPUT TYPE=\"text\" NAME=\"NVal3Desc\" CLASS=\"f10\" style=\"color:#aaa;border:solid 1px #999;\" VALUE=\" field description\" onfocus=\"this.value==' field description' ? this.value='' : i=0;\"></div>" ?>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="baseline"><B>D value 1:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="DVal1" MAXLENGTH="10" SIZE="10" VALUE="<?php echo ($Podatek && $Podatek->SifDVal1!="")? date("j.n.Y",sqldate2time($Podatek->SifDVal1)): "" ?>">
		<?php echo ($Podatek) ?
			"<div class=\"f10 gry\">". $Podatek->SifDVal1Desc ."</div>" :
			"<div><INPUT TYPE=\"text\" NAME=\"DVal1Desc\" CLASS=\"f10\" style=\"color:#aaa;border:solid 1px #999;\" VALUE=\" field description\" onfocus=\"this.value==' field description' ? this.value='' : i=0;\"></div>" ?>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="baseline"><B>D value 2:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="DVal2" MAXLENGTH="10" SIZE="10" VALUE="<?php echo ($Podatek && $Podatek->SifDVal2!="")? date("j.n.Y",sqldate2time($Podatek->SifDVal2)): "" ?>">
		<?php echo ($Podatek) ?
			"<div class=\"f10 gry\">". $Podatek->SifDVal2Desc ."</div>" :
			"<div><INPUT TYPE=\"text\" NAME=\"DVal2Desc\" CLASS=\"f10\" style=\"color:#aaa;border:solid 1px #999;\" VALUE=\" field description\" onfocus=\"this.value==' field description' ? this.value='' : i=0;\"></div>" ?>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="baseline"><B>D value 3:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="DVal3" MAXLENGTH="10" SIZE="10" VALUE="<?php echo ($Podatek && $Podatek->SifDVal3!="")? date("j.n.Y",sqldate2time($Podatek->SifDVal3)): "" ?>">
		<?php echo ($Podatek) ?
			"<div class=\"f10 gry\">". $Podatek->SifDVal3Desc ."</div>" :
			"<div><INPUT TYPE=\"text\" NAME=\"DVal3Desc\" CLASS=\"f10\" style=\"color:#aaa;border:solid 1px #999;\" VALUE=\" field description\" onfocus=\"this.value==' field description' ? this.value='' : i=0;\"></div>" ?>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="baseline"><B>L value 1:</B>&nbsp;</TD>
	<TD><INPUT TYPE="CheckBox" NAME="LVal1"<?php echo ($Podatek && $Podatek->SifLVal1)? " CHECKED": "" ?>>
		<?php echo ($Podatek) ?
			"<span class=\"f10 gry\">". $Podatek->SifLVal1Desc ."</span>" :
			"<INPUT TYPE=\"text\" NAME=\"LVal1Desc\" CLASS=\"f10\" style=\"color:#aaa;border:solid 1px #999;\" VALUE=\" field description\" onfocus=\"this.value==' field description' ? this.value='' : i=0;\">" ?>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="baseline"><B>L value 2:</B>&nbsp;</TD>
	<TD><INPUT TYPE="CheckBox" NAME="LVal2"<?php echo ($Podatek && $Podatek->SifLVal2)? " CHECKED": "" ?>>
		<?php echo ($Podatek) ?
			"<span class=\"f10 gry\">". $Podatek->SifLVal2Desc ."</span>" :
			"<INPUT TYPE=\"text\" NAME=\"LVal2Desc\" CLASS=\"f10\" style=\"color:#aaa;border:solid 1px #999;\" VALUE=\" field description\" onfocus=\"this.value==' field description' ? this.value='' : i=0;\">" ?>
	</TD>
</TR>
<?php if ( contains($ACL, "W") ) : ?>
<TR>
	<TD ALIGN="right" COLSPAN="2" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="submit" VALUE=" Save " CLASS="but"></TD>
</TR>
<?php endif ?>
</TABLE>
</FORM>
</FIELDSET>

	</TD>
	<TD VALIGN="top">

<?php if ( $Podatek ) : ?>
	<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
	<!--
	function checkTxt(ID, Naziv) {
		if (confirm("Do yo want to delete '"+Naziv+"'?"))
			loadTo('Edit',"<?php echo $_SERVER['PHP_SELF']?>?Action=<?php echo $_GET['Action'] ?>&Izbor=<?php echo $_GET['Izbor'] ?>&ID=<?php echo $_GET['ID'] ?>&BrisiTxt="+ID);
		return false;
	}
	//-->
	</SCRIPT>
	<FIELDSET ID="fldText" style="width:340px;">
	<LEGEND ID="lgdText">
<?php if ( contains($ACL,"W") ) : ?>
		<A HREF="javascript:void(0);" ONCLICK="$('#editTekst').load('inc.php?Action=<?php echo $_GET['Action'] ?>&Izbor=SifrantiTxt&Jezik=Novo&ID=<?php echo $_GET['ID'] ?>')" TITLE="Add"><IMG SRC="pic/control.add_document.gif" WIDTH=14 HEIGHT=14 ALT="Add" BORDER="0" CLASS="icon"></A>&nbsp;:
<?php endif ?>
		Text values</LEGEND>
	<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<?php
	$Nazivi = $db->get_results(
		"SELECT ID, SifNaziv AS Naziv, Jezik ".
		"FROM SifrantiTxt ".
		"WHERE SifrantID= ".(int)$_GET['ID']." ".
		"ORDER BY Jezik"
	);

	if ( !$Nazivi ) {
		echo "<TR><TD ALIGN=\"center\">No text values!</TD></TR>\n";
	} else {
		foreach ( $Nazivi as $Naziv ) {
			echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
			echo "<TD WIDTH=\"8%\">&nbsp;[<FONT COLOR=\"Red\"><B>".(($Naziv->Jezik=="")? "vsi": $Naziv->Jezik)."</B></FONT>]</TD>\n";
			echo "<TD><A HREF=\"javascript:void(0);\" ONCLICK=\"$('#editTekst').load('inc.php?Action=".$_GET['Action']."&Izbor=SifrantiTxt&Jezik=$Naziv->Jezik&ID=".$_GET['ID']."')\"><B>".left($Naziv->Naziv,45).((strlen($Naziv->Naziv)>45)? "...": "")."</B></A></TD>\n";
			echo "<TD ALIGN=\"right\" WIDTH=\"8%\"><A HREF=\"javascript:void(0);\" ONCLICK=\"javascript:checkTxt('$Naziv->ID','$Naziv->Naziv');\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Delete\" BORDER=\"0\" CLASS=\"icon\"></A></TD>\n";
			echo "</TR>\n";
		}
	}
?>
	</TABLE>
	</FIELDSET>
	<DIV ID="editTekst"></DIV>
<?php endif ?>

	</TD>
</TR>
</TABLE>
