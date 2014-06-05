<?php
/*~ inc_Password.php - Change password form
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

if ( isset( $_POST['NewPWD'] ) && $_POST['NewPWD'] != "" ) {
	$User = $db->get_row( "SELECT Password
		FROM SMUser
		WHERE UserID = " . (int)$_SESSION['UserID'] );
	if ( $User->Password == MD5(PWSALT.$_POST['OldPWD']) && $_POST['NewPWD'] == $_POST['ConfPWD'] && strlen(trim($_POST['NewPWD'])) >= 4 ) {
		$db->query( "UPDATE SMUser
			SET Password = '" . MD5(PWSALT.$_POST['NewPWD']) . "'
			WHERE UserID = " . (int)$_SESSION['UserID'] );
	} else {
		if ( $User->Password != MD5(PWSALT.$_POST['OldPWD']) )
			$Error = "Staro geslo ni pravilno!";
		elseif ( $_POST['NewPWD'] != $_POST['ConfPWD'] )
			$Error = "Novo geslo in potrditev gesla se ne ujemata!";
		elseif ( strlen(trim($_POST['NewPWD'])) < 4 )
			$Error = "Novo geslo je prekratko!";
		else
			$Error = "Napaka v geslu!";
	}
}
?>
<script language="JavaScript" type="text/javascript">
<!-- //
$('#edit').live('pageinit', function(event){
	// bind to the form's submit event
	$("#frmPassword").submit(function(e){
		jqObj = $(this);
		if (empty(jqObj[0].OldPWD))	{alert("Vnesite staro geslo!"); jqObj[0].OldPWD.focus(); return false;}
		if (empty(jqObj[0].NewPWD))	{alert("Vnesite novo geslo!"); jqObj[0].NewPWD.focus(); return false;}
		if (empty(jqObj[0].ConfPWD))	{alert("Vnesite potrditev gesla!"); jqObj[0].ConfPWD.focus(); return false;}
		if (jqObj[0].NewPWD.value.length < 4)	{alert("Novo geslo je prekratko!"); jqObj[0].NewPWD.focus(); return false;}
		if (jqObj[0].ConfPWD.value != jqObj[0].NewPWD.value)	{alert("Geslo in potrditev gesla se ne ujemata!"); jqObj[0].ConfPWD.focus(); return false;}
		return true;
	});
});
//-->
</script>
<?php
echo "<div id=\"edit\" data-role=\"page\">\n";
echo "\t<div data-role=\"header\" data-theme=\"b\">\n";
echo "\t\t<h1>Menjava gesla</h1>\n";
echo "\t\t<a href=\"./\" title=\"Home\" class=\"ui-btn-left\" data-direction=\"reverse\" data-iconpos=\"notext\" data-icon=\"home\" data-ajax=\"false\">Home</a>\n";
echo "\t</div>\n";
echo "\t<div data-role=\"content\">\n";

if ( isset( $_POST["NewPWD"] ) && $_POST["NewPWD"] != "" ) {
	echo "<div class=\"ui-body ui-body-d ui-corner-all\" style=\"padding:1em;text-align:center;\">\n";
	if ( isset( $Error ) ) {
		echo "<B class=\"warn\">Geslo ni zamenjano!</B><br>" . $Error . "\n";
	} else {
		echo "<B>Geslo uspešno zamenjano!</B>\n";
	}
	echo "</div>\n";
	echo "<div><a href=\"".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\" data-role=\"button\" data-direction=\"reverse\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-theme=\"c\" data-ajax=\"false\">Nazaj</a></div>\n";
} else {
?>
<FORM ID="frmPassword" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $_SERVER['QUERY_STRING']; ?>" METHOD="post">
<fieldset class="ui-hide-label" data-role="fieldcontain">
	<LABEL FOR="OldPWD">Staro geslo:</LABEL>
	<INPUT NAME="OldPWD" ID="OldPWD" TYPE="Password" SIZE="20" MAXLENGTH="16" placeholder="Staro geslo" data-theme="e"><br>
</fieldset>
<fieldset class="ui-hide-label" data-role="fieldcontain">
	<LABEL FOR="NewPWD"><B>Novo geslo:</B></LABEL>
	<INPUT NAME="NewPWD" ID="NewPWD" TYPE="Password" SIZE="20" MAXLENGTH="16" placeholder="Novo geslo" data-theme="d"><br>
	<LABEL FOR="ConfPWD"><B>Potrditev gesla:</B></LABEL>
	<INPUT NAME="ConfPWD" ID="ConfPWD" TYPE="Password" SIZE="20" MAXLENGTH="16" placeholder="Novo geslo" data-theme="d"><br>
</fieldset>
<fieldset class="ui-grid-a">
	<div class="ui-block-a"><a href="./" data-role="button" data-iconpos="left" data-icon="arrow-l" data-theme="c" data-ajax="false">Nazaj</a></div>
	<div class="ui-block-b"><INPUT TYPE="submit" VALUE="Menjaj" data-iconpos="left" data-icon="check" data-theme="a"></div>
</fieldset>
</FORM>
<?php
}

echo "\t</div>\n";
//echo "\t<div data-role=\"footer\" data-position=\"fixed\" class=\"ui-bar\" style=\"text-align:center;\">\n";
//echo "\t</div>\n";
echo "</div>\n"; // page
?>