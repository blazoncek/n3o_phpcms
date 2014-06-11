<?php
/*~ edit_Servis.php - Edit administration menu structure. Add/remove admin units.
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

$Podatek = $db->get_row("SELECT * FROM SMActions WHERE ActionID = '". $_GET['ID'] ."'");	// URL param ID defined in qry/ script
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
	$("form[name='Vnos']").submit(function(){
		$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
		$(this).ajaxSubmit({target: '#divEdit'}); // inside event callbacks 'this' is the DOM element
		return false; // always return false to prevent standard browser submit
	});
});
//-->
</script>

<FIELDSET ID="fldData" style="width:300px;">
<LEGEND ID="lgdData">
<?php if ( contains($ACL, "W") ) {
		echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=sysACL&ACL=".$Action->Action;
		echo "&ActionID=" . $_GET['ID'] . (($Podatek->ACLID!="")? "&ACLID=".$Podatek->ACLID: "") . "')\" TITLE=\"Edit permissions\">";
		echo "<IMG SRC=\"pic/control.permissions.gif\" HEIGHT=\"16\" WIDTH=\"16\" BORDER=0 ALT=\"Permissions\" ALIGN=\"absmiddle\"></A>&nbsp;:";
}
?>
	Basic&nbsp;information</LEGEND>
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<TABLE BORDER=0 CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD ALIGN="right"><B CLASS="red">Show:</B>&nbsp;</TD>
	<TD><INPUT TYPE="Checkbox" NAME="Show"<?php if ( $Podatek && $Podatek->Enabled ) echo " CHECKED"; ?> VALUE="yes">
	Mobile: <INPUT TYPE="Checkbox" NAME="Mobile"<?php if ( $Podatek && $Podatek->MobileCapable ) echo " CHECKED"; ?> VALUE="yes"></TD>
	<TD ALIGN="right"><B>Title:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="Name" MAXLENGTH="64" STYLE="width:100%;" VALUE="<?php if ( $Podatek ) echo $Podatek->Name; ?>"></TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="top"><B>Action:</B>&nbsp;</TD>
	<TD COLSPAN="2"><SELECT SIZE="1" ONCHANGE="this.form.Action.value=this.options[this.selectedIndex].value">
		<OPTION VALUE="">--- no action ---</OPTION>
<?php
	sort($files = scandir($StoreRoot ."/servis/_template/"));
	foreach ( $files as $file ) {
		$name = substr($file, 5, strlen($file)-9);
		if ( is_file($StoreRoot."/servis/_template/".$file) && left($file, 5) == "edit_" && right($file, 4) == ".php" )
			echo "<OPTION VALUE=\"$name\"" . (( $Podatek && $Podatek->Action == $name )? " SELECTED": "") . ">$name</OPTION>\n";
	}
	sort($files = scandir($StoreRoot ."/servis/template/"));
	foreach ( $files as $file ) {
		$name = substr($file, 5, strlen($file)-9);
		if ( is_file($StoreRoot."/servis/template/".$file) && left($file, 5) == "edit_" && right($file, 4) == ".php" )
			echo "<OPTION VALUE=\"$name\"" . (( $Podatek && $Podatek->Action == $name )? " SELECTED": "") . ">$name</OPTION>\n";
	}
?>
		</SELECT>
	</TD>
	<TD><INPUT TYPE="Text" NAME="Action" MAXLENGTH="64" STYLE="width:100%;" VALUE="<?php if ( $Podatek ) echo $Podatek->Action; ?>"></TD>
</TR>
<TR>
	<TD ALIGN="right"><B>Icon:</B>&nbsp;</TD>
	<TD COLSPAN="2"><SELECT NAME="Icon" SIZE="1" ONCHANGE="document.images['Ikona'].src=(this.selectedIndex==0?'./pic/trans.gif':'./pic/icon.'+this[selectedIndex].value+'.png');">
		<OPTION VALUE="">--- no icon ---</OPTION>
<?php
	$iconsf = $StoreRoot ."/servis/pic";
	sort($files = scandir($iconsf));
	foreach ( $files as $file ) {
		$name = explode(".", $file);
		if ( is_file($iconsf."/".$file) && left($file, 5) == "icon." && right($file, 4) == ".png" )
			echo "<OPTION VALUE=\"".$name[1]."\"" . (($Podatek && $Podatek->Icon == $name[1]) ? " SELECTED" : "") . ">".$name[1]."</OPTION>\n";
	}
?>
		</SELECT>
	</TD>
	<TD>
		<IMG NAME="Ikona" SRC="./pic/<?php echo (($Podatek && $Podatek->Icon != '')? "icon.".$Podatek->Icon: "trans") ?>.png" ALIGN="absmiddle" ALT="" BORDER="0" HEIGHT="16" WIDTH="16" HSPACE="0" VSPACE="0">
	</TD>
</TR>
<?php if ( contains($ACL, "W") ) : ?>
<TR>
	<TD ALIGN="right" COLSPAN="4" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="submit" VALUE=" Save " CLASS="but"></TD>
</TR>
<?php endif ?>
</TABLE>
</FORM>
</FIELDSET>
