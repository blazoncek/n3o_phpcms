<?php
/*~ edit_Predloge.php - Editing page templates.
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

$Podatek = $db->get_row("SELECT * FROM Predloge WHERE PredlogaID = ". (int)$_GET['ID']);
// get ACL
if ( $Podatek )
	$ACL = userACL($Podatek->ACLID);
else
	$ACL = $ActionACL;
?>
<script language="JavaScript" type="text/javascript">
<!-- //
function customResize () {
	// fix scroller problem when resizing
	if ( $("#divRubrike").text() ) $("#divRubrike").height(0);
	// actualy resize
	if ( $("#divRubrike").text() ) $("#divRubrike").height( $("#fldData").innerHeight() - 18 );
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
					if (empty(fObj.Naziv))	{alert("Please enter title!"); fObj.Naziv.focus(); return false;}
					$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
					return true;
				} // pre-submit callback
			});
			return false;
		});
	});
	$("form[name='Datoteka']").submit(function(){
		$(this).ajaxSubmit({target: '#divEdit'});
		return false;
	});
	
	// resize divs
	window.customResize();

	// load subdata
	if ( $("#divRubrike").text() ) $("#divRubrike").load('inc.php?Izbor=sysTemplateCategories&PredlogaID=<?php echo $_GET['ID'] ?>');

	// refresh list
	listRefresh();
});
//-->
</script>

<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD VALIGN="top" WIDTH="50%">

<FIELDSET ID="fldData">
<LEGEND ID="lgdData">
<?php if ( contains($ACL, "W") && $Podatek ) {
		echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=sysACL&ACL=".$Action->Action;
		echo "&PredlogaID=" . $_GET['ID'] . (($Podatek->ACLID!="")? "&ID=".$Podatek->ACLID: "") . "')\" TITLE=\"Edit permissions\">";
		echo "<IMG SRC=\"pic/control.permissions.gif\" HEIGHT=\"16\" WIDTH=\"16\" BORDER=0 ALT=\"Permissions\" ALIGN=\"absmiddle\"></A>&nbsp;:";
}
?>
	Basic&nbsp;information</LEGEND>
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD ALIGN="right"><FONT COLOR="Red"><B>Show:</B></FONT>&nbsp;</TD>
	<TD NOWRAP><INPUT TYPE="Checkbox" NAME="Enabled"<?php if ( $Podatek && $Podatek->Enabled ) echo " CHECKED"; ?>></TD>
</TR>
<TR>
	<TD ALIGN="right"><B>Title:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="Naziv" MAXLENGTH="32" VALUE="<?php if ( $Podatek ) echo $Podatek->Naziv ?>" STYLE="width:100%;"></TD>
</TR>
<TR>
	<TD ALIGN="right">Language:&nbsp;</TD>
	<TD><SELECT NAME="Jezik" SIZE="1">
		<OPTION VALUE="">- all -</OPTION>
<?php
	$Jeziki = $db->get_results("SELECT Jezik, Opis FROM Jeziki WHERE Enabled=1");
	if ( $Jeziki ) foreach ( $Jeziki as $Jezik )
		echo "<OPTION VALUE=\"$Jezik->Jezik\"".(($Jezik->Jezik==$Podatek->Jezik) ? " SELECTED" : "").">$Jezik->Opis</OPTION>\n";
?>
	</SELECT>
	</TD>
</TR>
<TR>
	<TD ALIGN="right"><B>File:</B>&nbsp;</TD>
	<TD><SELECT NAME="Datoteka" SIZE="1">
	<OPTION VALUE="">--- select a template ---</OPTION>
<?php
	$files = scandir($StoreRoot ."/template/");
	foreach ( $files as $file )
		if ( is_file($StoreRoot."/template/".$file) && left($file, 1)=="_" && contains(".php,html",right($file, 4)) )
			echo "<OPTION VALUE=\"$file\"" . (($Podatek && strtolower($Podatek->Datoteka) == strtolower($file)) ? " SELECTED STYLE=\"color:red;\"" : "") . ">$file</OPTION>\n";
?>
	</SELECT></TD>
</TR>
<?php if ( isset($_GET['Ekstra']) && $_GET['Ekstra'] != "" ) : ?>
<INPUT TYPE="Hidden" NAME="Tip" VALUE="<?php echo $_GET['Ekstra'] ?>">
<?php else : ?>
<TR>
	<TD ALIGN="right" WIDTH="150"><B>Type:</B>&nbsp;</TD>
	<TD>
	<INPUT TYPE="Radio" NAME="Tip" VALUE="0"<?php echo ($Podatek && $Podatek->Tip == 0) ? " CHECKED" : "" ?>> content<br>
	<INPUT TYPE="Radio" NAME="Tip" VALUE="1"<?php echo ($Podatek && $Podatek->Tip == 1) ? " CHECKED" : "" ?>> extra<br>
	<INPUT TYPE="Radio" NAME="Tip" VALUE="2"<?php echo ($Podatek && $Podatek->Tip == 2) ? " CHECKED" : "" ?>> menu<br>
	</TD>
</TR>
<?php endif ?>
<TR>
	<TD COLSPAN="2"><B>Description:</B><BR>
	<TEXTAREA NAME="Opis" ROWS="10" COLS="80" STYLE="width:100%;height:80px;"><?php if ( $Podatek ) echo $Podatek->Opis ?></TEXTAREA>
	</TD>
</TR>
<?php if ( contains( $ACL, "W" ) ) : ?>
<TR>
	<TD ALIGN="right" COLSPAN="2" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="submit" VALUE=" Save " CLASS="but"></TD>
</TR>
<?php endif ?>
</TABLE>
</FORM>
</FIELDSET>

	<FIELDSET>
		<LEGEND>Upload:</LEGEND>
		<FORM NAME="Datoteka" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post" ENCTYPE="multipart/form-data">
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
		<TR>
			<TD><INPUT TYPE="FILE" NAME="Add" STYLE="border:none;"></TD>
			<TD ALIGN="right"><INPUT TYPE="submit" VALUE=" Add " CLASS="but"></TD>
		</TR>
		</TABLE>
		</FORM>
	</FIELDSET>

	</TD>
	<TD VALIGN="top" WIDTH="50%">

	<FIELDSET>
		<LEGEND>Used in categories</LEGEND>
		<DIV ID="divRubrike" STYLE="overflow:auto;"><img src="pic/control.spinner.gif" alt="Loading" border="0"> Loading ...</DIV>
	</FIELDSET>

	</TD>
</TR>
</TABLE>
