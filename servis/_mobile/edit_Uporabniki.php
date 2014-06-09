<?php
/*~ edit_Uporabniki.php - Edit users. Add update user info and add/remove user from groups.
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

if ( !isset( $_GET['ID'] ) ) $_GET['ID'] = "0";

$User = $db->get_row( "SELECT * FROM SMUser WHERE UserID = " . (int)$_GET['ID'] );
?>
<script language="JavaScript" type="text/javascript">
<!-- //
$('#edit').live('pageinit', function(event){
<?php if ( (int)$_GET['ID'] == 0 ) : ?>
	// bind to the form's submit event
	$("#frmUser").submit(function(e){
		// inside event callbacks 'this' is the DOM element so we first
		// wrap it in a jQuery object
		jqObj = $(this);
		if (empty(jqObj[0].Name))		{alert("Vnesite ime in priimek uporabnika!"); jqObj[0].Name.focus(); return false;}
		if (!emailOK(jqObj[0].Email))	{alert("Nepravilen email naslov!"); jqObj[0].Email.focus(); return false;}
		if (empty(jqObj[0].Username))	{alert("Vnesite uporabniško ime!"); jqObj[0].Username.focus(); return false;}
		if (empty(jqObj[0].Password))	{alert("Vnesite geslo!"); jqObj[0].Password.focus(); return false;}
		if (jqObj[0].Password.value.length < 4 )	{alert("Geslo je prekratko!"); jqObj[0].Password.focus(); return false;}
		return true;
	});
<?php else : ?>
	// add change events
	$("input:text, input:password, input[name=Email], input[name=Phone], select").bind("change", function(event,ui){
		var options = {};
		if (this.name!='Phone' && this.value=="")	{alert("Rubrika ne sme biti prazna!"); this.focus(); return false;}
		if (this.name=='Password' && this.value.length<4)	{alert("Geslo je prekratko!"); this.focus(); return false;}
		if (this.name=='Email' && !emailOK(this))	{alert("Nepravilen email naslov!"); this.focus(); return false;}
		URL = '<?php echo dirname($_SERVER['PHP_SELF'])?>/upd.php?<?php echo $_SERVER['QUERY_STRING'] ?>';
		$.mobile.loadPage(URL, {
			pageContainer: $("#result"),
			reloadPage: true,
			type: "post",
			data: $(this).serialize() // this.name+'='+this.value
		});
		this.blur();
	});
	// add change events for ACLs
	$("input[^name=Usr-]:checkbox").bind("click", function(event,ui){
		var options = {};
		options.GroupList = this.value;
		options.Action    = (this.checked ? "Add" : "Remove");
		options.UserID    = this.name.substr(4,4);
		URL = '<?php echo dirname($_SERVER['PHP_SELF']); ?>/upd.php?<?php echo $_SERVER['QUERY_STRING'] ?>';
		$.mobile.loadPage(URL, {
			pageContainer: $("#result"),
			reloadPage: true,
			type: "post",
			data: options
		});
	});
<?php endif ?>
});
//-->
</script>

<?php
echo "<div id=\"edit\" data-role=\"page\" data-title=\"Uporabniki\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Uporabniki</h1>\n";
echo "<a href=\"list.php?Izbor=". $_GET['Izbor'] ."\" title=\"Back\" data-role=\"button\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-ajax=\"false\" data-transition=\"slide\">Back</a>\n";
echo "<a href=\"./\" title=\"Home\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Home</a>\n";
//if ( (int)$_GET['ID'] != 0 )
//	echo "<a href=\"#editGroups\" title=\"Skupine\" class=\"ui-btn-right\" data-iconpos=\"notext\" data-icon=\"gear\">Skupine</a>\n";
echo "</div>\n";
echo "<div data-role=\"content\">\n";

if ( (int)$_GET['ID'] == 0 )
	echo "<FORM ID=\"frmUser\" ACTION=\"". $_SERVER['PHP_SELF'] ."?". $_SERVER['QUERY_STRING'] ."\" METHOD=\"post\" data-ajax=\"false\">\n";

if ( isset( $Error ) ) {
	echo "<div class=\"ui-body ui-body-d ui-corner-all\" style=\"padding:1em;text-align:center;\">";
	echo "<b>Prišlo je do napake!</b><br>Podatki niso vpisani.";
	echo "</div>\n";
} else {
?>
	<fieldset class="ui-hide-label" data-role="fieldcontain"><legend>Basic&nbsp;information:</legend>
		<LABEL FOR="frmUserName"><B>Ime in priimek:</B></LABEL>
		<INPUT TYPE="text" ID="frmUserName" NAME="Name" MAXLENGTH="50" VALUE="<?php if ($User) echo $User->Name ?>" placeholder="Ime in priimek" data-theme="d"><br />
		<LABEL FOR="frmUserEmail"><B>Email:</B></LABEL>
		<INPUT TYPE="email" ID="frmUserEmail" NAME="Email" MAXLENGTH="50" VALUE="<?php if ($User) echo $User->Email ?>" placeholder="Email" data-theme="d"><br />
		<LABEL FOR="frmUserUsername"><B>Uporabniško ime:</B></LABEL>
		<INPUT TYPE="text" ID="frmUserUsername" NAME="Username" MAXLENGTH="255" VALUE="<?php if ($User) echo $User->Username ?>" placeholder="Uporabniško ime" data-theme="d"><br />
		<LABEL FOR="frmUserPassword"><B>Geslo:</B></LABEL>
		<INPUT TYPE="password" ID="frmUserPassword" NAME="Password" MAXLENGTH="255" VALUE="" placeholder="Novo geslo" data-theme="d"><br />
		<LABEL FOR="frmUserPhone">Telefon:</LABEL>
		<INPUT TYPE="tel" ID="frmUserPhone" NAME="Phone" SIZE="43" MAXLENGTH="25" VALUE="<?php if ($User) echo $User->Phone ?>" placeholder="Telefon" data-theme="d"><br />
		<LABEL FOR="frmUserTwitter">Twitter:</LABEL>
		<INPUT TYPE="tel" ID="frmUserTwitter" NAME="TwitterName" SIZE="43" MAXLENGTH="32" VALUE="<?php if ($User) echo $User->TwitterName ?>" placeholder="@username" data-theme="d"><br />
<?php /*
		<LABEL FOR="frmUserDefGrp">Privzeta skupina:</LABEL>
		<SELECT ID="frmUserDefGrp" NAME="DefGrp" SIZE="1" data-theme="d">
			<option value="">
<?php
	$Grupe = $db->get_results( "SELECT * FROM SMGroup ORDER BY Name" );
	foreach( $Grupe as $Grupa ) {
		echo "\t\t\t<option value=\"$Grupa->GroupID\"";
		echo (($User && $User->DefGrp == $Grupa->GroupID) || (!$User && $Grupa->GroupID == 1))? " SELECTED" : "";
		echo ">";
		echo $Grupa->Name . "</option>\n";
	}
?>
		</SELECT>
*/ ?>
	</fieldset>
<?php
}

// prevent disabling administrator 
if ( (int)$_GET['ID'] != 1 ) {
?>
	<fieldset data-role="fieldcontain">
		<LABEL FOR="frmUserActive">Aktiven:</LABEL>
		<select ID="frmUserActive" NAME="Active" data-role="slider" data-theme="b">
			<option value="no">No</option>
			<option value="yes" <?php if ( $User && $User->Active ) echo "SELECTED" ?>>Yes</option>
		</select>
	</fieldset>
<?php
} else {
	echo "<INPUT TYPE=\"Hidden\" NAME=\"Active\" VALUE=\"1\">\n";
}

if ( (int)$_GET['ID'] != 0 ) {
?>
	<fieldset data-role="fieldcontain">
		<LABEL FOR="frmUserLastLogon">Prijava:</LABEL>
		<INPUT TYPE="Text" ID="frmUserLastLogon" VALUE="<?php if ( $User && $User->LastLogon != "" ) echo date( "j.n.Y H:i:s", sqldate2time( $User->LastLogon ) ) ?>" DISABLED READONLY data-theme="d" />
	</fieldset>
<?php
} else {
?>
<?php if ( contains($ActionACL,"W") ) : ?>
	<INPUT TYPE="submit" VALUE="Shrani" data-iconpos="left" data-icon="check" data-theme="a">
<?php endif ?>
<?php
}

if ( (int)$_GET['ID'] == 0 )
	echo "\t</FORM>\n";

// only administrator can change groups he belongs to,
// for other users anyone with access to this script can
if ( (int)$_GET['ID'] > 1 || ($_SESSION['UserID'] == 1 && (int)$_GET['ID'] == 1) ) {
	$Members = $db->get_results(
		"SELECT
			G.GroupID,
			G.Name,
			UG.ID
		FROM
			SMGroup G
			LEFT JOIN SMUserGroups UG
				ON G.GroupID = UG.GroupID AND UG.UserID = " . (int)$_GET['ID'] . "
		WHERE
			G.GroupID > " . (strpos($ActionACL,"D")!==false ? "0" : "1") . "
		ORDER BY G.Name" );

	echo "<fieldset data-role=\"controlgroup\"><legend>Član skupin:</legend>\n";
	// disable groups 1 (everyone) for anyone and 2 (administrators) for administrator
	if ( count($Members) > 0 )
		foreach ( $Members as $Member ) {
			echo "\t\t<input type=\"checkbox\" name=\"Usr-". (int)$_GET['ID'] ."\" value=\"". $Member->GroupID ."\" id=\"cbx-". $Member->GroupID ."\"" . (($Member->GroupID==1 || ($Member->GroupID==2 && $_GET['ID']==1) || !contains($ActionACL,"W")) ? " DISABLED" : "" ) . (($Member->ID) ? " CHECKED" : "" ) . "  data-theme=\"d\" />\n";
			echo "\t\t<label for=\"cbx-$Member->GroupID\">$Member->Name</label>\n";
		}
	echo "</fieldset>\n";
}

echo "\t</div>\n";
//echo "\t<div data-role=\"footer\" data-position=\"fixed\" class=\"ui-bar\" style=\"text-align:center;\">\n";
//echo "\t</div>\n";
echo "</div>\n"; // page

/*
if ( (int)$_GET['ID'] != 0 ) {
	echo "<div id=\"editGroups\" data-role=\"page\" data-title=\"Uporabniki\">\n";
	echo "<div data-role=\"header\" data-theme=\"b\">\n";
	echo "<h1>". $User->Name ."</h1>\n";
	echo "<a href=\"list.php?Izbor=". $_GET['Izbor'] ."\" title=\"Back\" data-role=\"button\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-rel=\"back\" data-transition=\"slide\">Back</a>\n";
	echo "<a href=\"#edit\" title=\"Podatki\" class=\"ui-btn-right\" data-iconpos=\"notext\" data-icon=\"info\" data-direction=\"reverse\">Podatki</a>\n";
	echo "</div>\n";
	echo "<div data-role=\"content\">\n";

	// only administrator can change groups he belongs to,
	// for other users anyone with access to this script can
	if ( (int)$_GET['ID'] > 1 || ($_SESSION['UserID'] == 1 && (int)$_GET['ID'] == 1) ) {
		$Members = $db->get_results(
			"SELECT
				G.GroupID,
				G.Name,
				UG.ID
			FROM
				SMGroup G
				LEFT JOIN SMUserGroups UG
					ON G.GroupID = UG.GroupID AND UG.UserID = " . (int)$_GET['ID'] . "
			WHERE
				G.GroupID > " . (strpos($ActionACL,"D")!==false ? "0" : "1") . "
			ORDER BY G.Name" );

		echo "<fieldset data-role=\"controlgroup\"><legend>Član skupin:</legend>\n";
		// disable groups 1 (everyone) for anyone and 2 (administrators) for administrator
		if ( count($Members) > 0 )
			foreach ( $Members as $Member ) {
				echo "\t\t<input type=\"checkbox\" name=\"Usr-". (int)$_GET['ID'] ."\" value=\"". $Member->GroupID ."\" id=\"cbx-". $Member->GroupID ."\"" . (($Member->GroupID==1 || ($Member->GroupID==2 && $_GET['ID']==1) || !contains($ActionACL,"W")) ? " DISABLED" : "" ) . (($Member->ID) ? " CHECKED" : "" ) . "  data-theme=\"d\" />\n";
				echo "\t\t<label for=\"cbx-$Member->GroupID\">$Member->Name</label>\n";
			}
		echo "</fieldset>\n";
	}

	echo "</div>\n";
	//echo "\t<div data-role=\"footer\" data-position=\"fixed\" class=\"ui-bar\" style=\"text-align:center;\">\n";
	//echo "\t</div>\n";
	echo "</div>\n"; // page
	echo "<div id=\"result\" data-role=\"page\"></div>\n"; // page
}
*/
?>
