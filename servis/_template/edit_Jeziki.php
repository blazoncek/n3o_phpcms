<?php
/*~ edit_Jeziki.php - Edit available language customizations.
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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "";

$Podatek = $db->get_row("SELECT * FROM Jeziki WHERE Jezik='".$_GET['ID']."'");
?>
<script language="JavaScript" type="text/javascript">
<!-- //
$(document).ready(function(){
	$("form[name='Vnos']").submit(function(){
		$(this).ajaxSubmit({
			target: '#divEdit',
			iframe: false, // fix for listRefresh
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (empty(fObj.Jezik))	{alert("Prosim vnesite kratico!"); fObj.Jezik.focus(); return false;}
				if (empty(fObj.Opis))	{alert("Prosim vnesite opis!"); fObj.Opis.focus(); return false;}
				$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
				return true;
			} // pre-submit callback
		});
		return false;
	});
	// refresh list
	listRefresh();
});
//-->
</script>

<FIELDSET ID="fldData" style="width:370px;">
<LEGEND ID="lgdData">Basic&nbsp;information</LEGEND>
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD ALIGN="right"><FONT COLOR="Red"><B>Show:</B></FONT>&nbsp;</TD>
	<TD NOWRAP><INPUT TYPE="Checkbox" NAME="Izpis"<?php echo (($Podatek && $Podatek->Enabled)? " CHECKED": "") ?>></TD>
</TR>
<TR>
	<TD ALIGN="right">Default:&nbsp;</TD>
	<TD NOWRAP><INPUT TYPE="Checkbox" NAME="DefLang"<?php echo (($Podatek && $Podatek->DefLang) ? " CHECKED" : "") ?>></TD>
</TR>
<TR>
	<TD ALIGN="right"><B>Short:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="Jezik" SIZE="2" MAXLENGTH="2" VALUE="<?php echo (($Podatek) ? $Podatek->Jezik : "") ?>"<?php echo (($Podatek)? " READONLY": "") ?>></TD>
</TR>
<TR>
	<TD ALIGN="right"><B>Long:</B>&nbsp;</TD>
	<TD><INPUT TYPE="Text" NAME="Opis" SIZE="20" MAXLENGTH="20" VALUE="<?php echo (($Podatek) ? $Podatek->Opis : "") ?>"></TD>
</TR>
<TR>
	<TD ALIGN="right"><B>Charset:</B>&nbsp;</TD>
	<TD><INPUT TYPE="Text" NAME="CharSet" SIZE="20" MAXLENGTH="64" VALUE="<?php echo (($Podatek) ? $Podatek->CharSet : "") ?>"></TD>
</TR>
<TR>
	<TD ALIGN="right"><B>Code:</B>&nbsp;</TD>
	<TD><INPUT TYPE="Text" NAME="LangCode" SIZE="20" MAXLENGTH="5" VALUE="<?php echo (($Podatek) ? $Podatek->LangCode : "") ?>"></TD>
</TR>
<TR>
	<TD ALIGN="right"><B>Image:</B>&nbsp;</TD>
	<TD VALIGN="top">
		<INPUT TYPE="FILE" NAME="Slika" STYLE="width:100%;border:none;" MAXLENGTH="128">
	</TD>
</TR>
<TR>
	<TD ALIGN="right">Current image:&nbsp;</TD>
	<TD>
<?php
if ( ($Podatek && $Podatek->Ikona!="") ){
	echo "<IMG SRC=\"../pic/$Podatek->Ikona\" BORDER=\"0\">&nbsp;\n";
	echo "<INPUT TYPE=\"Hidden\" NAME=\"S1\" VALUE=\"$Podatek->Ikona\">\n";
} else
	echo "<IMG SRC=\"../pic/lng/$Podatek->LangCode.png\" BORDER=\"0\">&nbsp;\n";
	echo "<INPUT TYPE=\"Hidden\" NAME=\"S1\" VALUE=\"\">\n";
?>
	</TD>
</TR>	
<?php if ( contains($ActionACL,"W") ) : ?>
<TR>
	<TD ALIGN="right" COLSPAN="2" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="submit" VALUE=" Save " CLASS="but"></TD>
</TR>
<?php endif ?>
</TABLE>
</FORM>
</FIELDSET>
