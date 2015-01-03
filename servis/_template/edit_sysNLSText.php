<?php
/*~ edit_NLSText.php - NLS text customizations.
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend and backend)                                 |
|   Version: 2.2.2                                                          |
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

$Podatki = $db->get_results("SELECT * FROM NLSText WHERE NLSToken = '". $db->escape($_GET['ID']) ."'");
?>
<script language="JavaScript" type="text/javascript">
<!-- //
$(document).ready(function(){
<?php if ( count($_POST) > 0 ) : ?>
	$('#fldData').css('background-color','#FFCCCC');
	setTimeout("$('#fldData').css('background-color','')",750);
<?php endif ?>

	// bind to the form's submit event
	$("form[name='Vnos']").each(function(){
		$(this).submit(function(){
			$(this).ajaxSubmit({
				target: '#divEdit',
				beforeSubmit: function( formDataArr, jqObj, options ) {
					var fObj = jqObj[0];	// form object
					if (fObj.NLSToken && empty(fObj.NLSToken))	{alert("Please enter token!"); fObj.NLSToken.focus(); return false;}
					return true;
				} // pre-submit callback
			});
			return false;
		});
	});
	// refresh list
	listRefresh();
});
//-->
</script>

<?php if ( !$Podatki ) : ?>
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="430">
<TR><TD COLSPAN="2" HEIGHT="10"></TD></TR>
	<TR>
		<TD ALIGN="right"><B>Token:</B>&nbsp;</TD>
		<TD><INPUT TYPE="text" NAME="NLSToken" MAXLENGTH="32" VALUE="" STYLE="width:400px;"></TD>
	</TR>
	<TR>
		<TD ALIGN="right">Language:&nbsp;</TD>
		<TD><SELECT NAME="Jezik" SIZE="1">
			<!--OPTION VALUE="">- for all -</OPTION-->
<?php
	$Jeziki = $db->get_results("SELECT Jezik, Opis FROM Jeziki WHERE Enabled=1");
	if ( $Jeziki ) foreach ( $Jeziki as $Jezik )
		echo "<OPTION VALUE=\"$Jezik->Jezik\"". ($Jezik->Jezik==$Podatek->Jezik ? " SELECTED" : "") .">$Jezik->Opis</OPTION>\n";
?>
		</SELECT>
		</TD>
	</TR>
	<TR>
		<TD ALIGN="right">NLSShort:&nbsp;</TD>
		<TD><INPUT TYPE="text" NAME="NLSShort" MAXLENGTH="255" VALUE="" STYLE="width:400px;"></TD>
	</TR>
	<TR>
		<TD COLSPAN="2">NLSLong:<BR>
		<TEXTAREA NAME="NLSLong" ROWS="5" COLS="80" STYLE="width:540px;height:50px;"></TEXTAREA>
		</TD>
	</TR>
<?php if ( contains($ActionACL,"W") ) : ?>
	<TR>
		<TD ALIGN="right" COLSPAN="2" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="submit" VALUE=" Save " CLASS="but"></TD>
	</TR>
<?php endif ?>
	</FORM>
</TABLE>
<?php else : ?>
<?php
foreach ( $Podatki as $Podatek ) {
?>
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="430">
<INPUT TYPE="Hidden" NAME="Jezik" VALUE="<?php echo $Podatek->Jezik ?>">
<TR>
	<TD ALIGN="right">[<FONT COLOR="Red"><?php echo $Podatek->Jezik ?></FONT>] <B><?php echo $Podatek->NLSToken ?></B> =</TD>
	<TD><INPUT TYPE="text" NAME="NLSShort" MAXLENGTH="255" VALUE="<?php echo $Podatek->NLSShort ?>" STYLE="width:400px;"></TD>
</TR>
<TR>
	<TD COLSPAN="2">
	<TEXTAREA NAME="NLSLong" ROWS="5" COLS="80" STYLE="width:540px;height:50px;"><?php echo $Podatek->NLSLong ?></TEXTAREA>
	</TD>
</TR>
<?php if ( contains($ActionACL,"W") ) : ?>
<TR>
	<TD ALIGN="right" COLSPAN="2" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="submit" VALUE=" Save " CLASS="but"></TD>
</TR>
<?php endif ?>
</FORM>
</TABLE>
<?php } ?>

<?php
$Jeziki = $db->get_results(
	"SELECT J.Jezik, J.Opis
	FROM Jeziki J
		LEFT JOIN NLSText T ON J.Jezik = T.Jezik AND T.NLSToken = '". $db->escape($_GET['ID']) ."'
	WHERE
		J.Enabled = 1
		AND T.Jezik IS NULL"
	);
if ( $Jeziki ) {
?>
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="430">
<INPUT TYPE="Hidden" NAME="NLSToken" VALUE="<?php echo $Podatek->NLSToken ?>">
<TR>
	<TD ALIGN="right">Language:&nbsp;</TD>
	<TD><SELECT NAME="Jezik" SIZE="1">
<?php
		foreach ( $Jeziki as $Jezik )
			echo "<OPTION VALUE=\"$Jezik->Jezik\"".(($Jezik->Jezik==$Podatek->Jezik)? " SELECTED": "").">$Jezik->Opis</OPTION>\n";
?>
	</SELECT>
	</TD>
</TR>
<TR>
	<TD ALIGN="right">NLSShort:&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="NLSShort" MAXLENGTH="255" VALUE="" STYLE="width:400px;"></TD>
</TR>
<TR>
	<TD COLSPAN="2">NLSLong:<BR>
	<TEXTAREA NAME="NLSLong" ROWS="5" COLS="80" STYLE="width:540px;height:50px;"></TEXTAREA>
	</TD>
</TR>
<?php if ( contains($ActionACL,"W") ) : ?>
<TR>
	<TD ALIGN="right" COLSPAN="2"><INPUT TYPE="submit" VALUE=" Add " CLASS="but"></TD>
</TR>
<?php endif ?>
</TABLE>
<?php } ?>
<?php endif ?>
