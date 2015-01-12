<?php
/*~ edit_Predloge.php - Editing page templates.
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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "0";

$Podatek = $db->get_row("SELECT * FROM Tags WHERE TagID = ". (int)$_GET['ID']);

$ACL = $ActionACL;
?>
<script language="JavaScript" type="text/javascript">
<!-- //
function customResize () {
	// fix scroller problem when resizing
	if ( $("#divBesedila").text() ) $("#divBesedila").height(0);
	// actualy resize
	if ( $("#divBesedila").text() ) $("#divBesedila").height( $("#fldOpt").innerHeight() - 18 );
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
					if (empty(fObj.Naziv))	{alert("Please enter tag name!"); fObj.Naziv.focus(); return false;}
					$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
					return true;
				} // pre-submit callback
			});
			return false;
		});
	});
	
	// resize divs
	window.customResize();

	// load subdata
	if ( $("#divBesedila").text() ) $("#divBesedila").load('inc.php?Izbor=textTagsAssign&TagID=<?php echo $_GET['ID'] ?>');

	// refresh list
	listRefresh();
});
//-->
</script>

<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
<?php if ( $_GET['ID'] == 0 ) : ?>
	<TD CLASS="red">
	<B>Not possible!</B>
	</TD>
<?php else : ?>
	<TD VALIGN="top" WIDTH="50%">

<FIELDSET ID="fldData">
<LEGEND ID="lgdData">
	Tag</LEGEND>
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD ALIGN="right"><B>Name:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="Naziv" MAXLENGTH="64" VALUE="<?php if ( $Podatek ) echo $Podatek->TagName ?>" STYLE="width:100%;"></TD>
</TR>
<?php if ( contains($ACL, "W") ) : ?>
<TR>
	<TD ALIGN="right" COLSPAN="2" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="submit" VALUE=" Save " CLASS="but"></TD>
</TR>
<?php endif ?>
</TABLE>
</FORM>
</FIELDSET>

<FIELDSET ID="fldOpt" STYLE="min-height:250px;">
	<LEGEND>Used in texts</LEGEND>
	<DIV ID="divBesedila" STYLE="overflow:auto;"><img src="pic/control.spinner.gif" alt="Loading" border="0"> Loading ...</DIV>
</FIELDSET>

	</TD>
	<TD VALIGN="top" WIDTH="50%">

	</TD>
<?php endif ?>
</TR>
</TABLE>
