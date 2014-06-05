<?php
/*~ edit_Besedila.php - Add/edit texts.
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

$Podatek = $db->get_row( "SELECT * FROM Besedila WHERE BesediloID = " . (int)$_GET['ID'] );
// get ACL
if ( $Podatek )
	$ACL = userACL( $Podatek->ACLID );
else
	$ACL = $ActionACL;
?>
<script language="JavaScript" type="text/javascript">
<!-- //
$('#edit').live('pageinit', function(event){
<?php if ( (int)$_GET['ID'] == 0 ) : ?>
	// bind to the form's submit event
	$("#frmBesedilo").submit(function(e){
		// inside event callbacks 'this' is the DOM element so we first
		// wrap it in a jQuery object
		jqObj = $(this);
		if (empty(jqObj[0].Ime))	{alert("Vnesite ime!"); jqObj[0].Ime.focus(); return false;}
		return true;
	});
<?php else : ?>
	// handle field changes
	$("input[name!='newtag']:text, input[type=date], textarea, select").change(function(e){
		e.preventDefault();
		var fObj = this;	// input object
		if (fObj.name=="Ime" && fObj.value.length==0) {alert("Prosim vnesite ime!"); fObj.focus(); return false;}
		URL = '<?php echo dirname($_SERVER['PHP_SELF'])?>/upd.php?<?php echo $_SERVER['QUERY_STRING'] ?>';
		$.mobile.loadPage(URL, {
			pageContainer: $("#result"),
			reloadPage: true,
			type: "post",
			data: $(this).serialize() // this.name+'='+this.value
		});
		this.blur();
	});
	$("input[name='newtag']").change(function(e){
		e.preventDefault();
		var fObj = this;	// input object
		if (fObj.value.length==0) {return false;}
		URL = '<?php echo dirname($_SERVER['PHP_SELF']) ?>/inc.php?Izbor=BesediloTags&BesediloID=<?php echo $_GET['ID'] ?>';
		$.mobile.changePage(URL, {
			reloadPage: true,
			type: "get",
			data: 'Find='+this.value // $(this).serialize()
		});
		return false;
	});
	$("input[name='Find']").change(function(e){
		e.preventDefault();
		var fObj = this;	// input object
		if (fObj.value.length==0) {return false;}
		URL = '<?php echo dirname($_SERVER['PHP_SELF']) ?>/inc.php?Izbor=BesediloSkupina&BesediloID=<?php echo $_GET['ID'] ?>';
		$.mobile.changePage(URL, {
			reloadPage: true,
			type: "get",
			data: $(this).serialize()
		});
		return false;
	});
<?php endif ?>
});

function checkFld(fld, ID, Naziv) {
	if (confirm("Ali res želite odstraniti '"+Naziv+"'?")) {
		URL = "<?php echo $_SERVER['PHP_SELF'] ?>?Izbor=<?php echo $_GET['Izbor'] ?>&ID=<?php echo $_GET['ID'] ?>";
		$.mobile.changePage(URL, {
			reloadPage: true,
			type: "get",
			data: fld+"="+ID
		});
	}
	return false;
}
//-->
</script>
<?php
echo "<div id=\"edit\" data-role=\"page\" data-title=\"Besedila\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Besedila</h1>\n";
echo "<a href=\"list.php?Izbor=". $_GET['Izbor'] ."\" title=\"Nazaj\" data-role=\"button\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-ajax=\"false\" data-transition=\"slide\">Nazaj</a>\n";
echo "<a href=\"./\" title=\"Domov\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Domov</a>\n";
echo "</div>\n";
echo "<div data-role=\"content\">\n";

if ( (int)$_GET['ID'] == 0 )
	echo "<FORM ID=\"frmBesedilo\" ACTION=\"". $_SERVER['PHP_SELF'] ."?". $_SERVER['QUERY_STRING'] ."\" METHOD=\"post\" data-ajax=\"false\">\n";

if ( isset($Error) ) {
	echo "<div class=\"ui-body ui-body-d ui-corner-all\" style=\"padding:1em;text-align:center;\">";
	echo "<b>Prišlo je do napake!</b><br>Podatki niso vpisani.";
	echo "</div>\n";
} else {
?>
	<div data-role="fieldcontain">
		<LABEL FOR="fldIme"><B>Ime:</B></LABEL>
		<INPUT TYPE="text" ID="fldIme" NAME="Ime" MAXLENGTH="127" VALUE="<?php echo $Podatek ? $Podatek->Ime : ''; ?>" placeholder="Ime" data-theme="d"><br />
	</div>
	<div data-role="fieldcontain">
		<LABEL FOR="fldIzpis"><b>Izpis:</b></LABEL>
		<select ID="fldIzpis" NAME="Izpis" data-role="slider" data-theme="b">
			<option value="no">No</option>
			<option value="yes" <?php if ( $Podatek && $Podatek->Izpis ) echo "SELECTED" ?>>Yes</option>
		</select><br />
	</div>
	<div data-role="fieldcontain">
		<?php $Datum = date("d.n.Y", $Podatek ? sqldate2time($Podatek->Datum) : time()); ?>
		<LABEL FOR="fldDatum"><B>Datum:</B></LABEL>
		<INPUT TYPE="date" ID="fldDatum" NAME="Datum" MAXLENGTH="10" VALUE="<?php echo $Datum; ?>" CLASS="datepicker" placeholder="Datum" data-theme="d" data-role="datebox" data-options='{"mode": "calbox"}'><br />
		<LABEL FOR="fldURL"><B>URL:</B></LABEL>
		<INPUT TYPE="text" ID="fldURL" NAME="URL" MAXLENGTH="128" VALUE="<?php echo $Podatek ? $Podatek->URL : ''; ?>" placeholder="URL" data-theme="d"><br />
	</div>
<?php
}

if ( (int)$_GET['ID'] != 0 ) {

	echo "<fieldset class=\"ui-hide-label\" data-role=\"fieldcontain\" data-theme=\"a\">";
	echo "<legend>Vsebina</legend>\n";
	$List = $db->get_results(
		"SELECT ID, Naslov AS Naziv, Jezik, Polozaj ".
		"FROM BesedilaOpisi ".
		"WHERE BesediloID = ".(int)$_GET['ID']." ".
		"ORDER BY Jezik, Polozaj"
	);

	echo "<ul data-role=\"listview\" data-inset=\"true\" data-theme=\"d\" data-split-icon=\"delete\" data-split-theme=\"a\" data-count-theme=\"e\">\n";
	if ( $List ) foreach ( $List as $Naziv ) {
		echo "<li>";
		echo (contains($ACL,"W")? "<a href=\"inc.php?Izbor=BesediloOpis&BesediloID=".(int)$_GET['ID']."&ID=$Naziv->ID\">": "");
		echo "<b>". $Naziv->Naziv ."</b>";
		echo "<span class=\"ui-li-count\">".(($Naziv->Jezik=="")? "vsi": $Naziv->Jezik)."</span>";
		echo (contains($ACL,"W")? "</a>": "");
		if ( contains($ACL,"D") )
			echo "<a href=\"#\" onclick=\"checkFld('BrisiOpis','$Naziv->ID','$Naziv->Naziv');\" data-theme=\"c\">Briši</a>";
		echo "</li>\n";
	}
	echo "<li data-icon=\"add\"><a href=\"inc.php?Izbor=BesediloOpis&BesediloID=". (int)$_GET['ID'] ."&ID=0\" title=\"Dodaj vsebino\" class=\"ui-btn-right\" data-iconpos=\"left\" data-icon=\"add\">Dodaj vsebino</a></li>\n";
	echo "</ul>\n";
	echo "</fieldset>\n";

	// display tags
	echo "<fieldset class=\"ui-hide-label\" data-role=\"fieldcontain\" data-theme=\"a\">";
	echo "<legend>Oznake</legend>\n";
	$List = $db->get_results(
		"SELECT
			BT.ID,
			T.TagName
		FROM
			BesedilaTags BT
			LEFT JOIN Tags T ON BT.TagID = T.TagID
		WHERE
			BT.BesediloID = ".(int)$_GET['ID']."
		ORDER BY
			T.TagName"
	);

	echo "<ul data-role=\"listview\" data-inset=\"true\" data-theme=\"d\">\n";
	echo "<li data-theme=\"c\"><input type=\"text\" name=\"newtag\" placeholder=\"Add new tag\"></li>\n";
	if ( $List) foreach ( $List as $Item ) {
		echo "<li>";
		echo (contains($ACL,"W") ? "<a href=\"inc.php?Izbor=BesediloTags&BesediloID=". (int)$_GET['ID'] ."\" data-ajax=\"false\" data-theme=\"c\">" : "");
		echo $Item->TagName;
		echo (contains($ACL,"W") ? "</a>" : "");
		echo "</li>\n";
	}
	echo "</ul>\n";
	echo "</fieldset>\n";

	// display related
	echo "<fieldset class=\"ui-hide-label\" data-role=\"fieldcontain\" data-theme=\"a\">";
	echo "<legend>Povezana besedila</legend>\n";
	$List = $db->get_results(
		"SELECT BS.ID, BS.DodatniID, BS.Polozaj, B.Ime, B.ACLID ".
		"FROM BesedilaSkupine BS ".
		"	LEFT JOIN Besedila B ON BS.DodatniID = B.BesediloID ".
		"WHERE BS.BesediloID = ".(int)$_GET['ID']." ".
		"ORDER BY BS.BesediloID, BS.Polozaj"
		);

	echo "<ul data-role=\"listview\" data-inset=\"true\" data-theme=\"d\">\n";
	echo "<li data-theme=\"c\"><input type=\"text\" name=\"Find\" placeholder=\"Find\"></li>\n";
	if ( $List) foreach ( $List as $Item ) {
		echo "<li>";
		echo (contains($ACL,"W") ? "<a href=\"inc.php?Izbor=BesediloSkupina&BesediloID=". (int)$_GET['ID'] ."\" data-ajax=\"false\" data-theme=\"c\">" : "");
		echo $Item->Ime;
		echo (contains($ACL,"W") ? "</a>" : "");
		echo "</li>\n";
	}
	echo "</ul>\n";
	echo "</fieldset>\n";

} else {

	if ( contains($ActionACL,"W") )
		echo "<INPUT TYPE=\"submit\" VALUE=\"Shrani\" data-iconpos=\"left\" data-icon=\"check\" data-theme=\"a\">\n";
	echo "</FORM>\n";

}

echo "</div>\n";
//echo "\t<div data-role=\"footer\" data-position=\"fixed\" class=\"ui-bar\" style=\"text-align:center;\">\n";
//echo "\t</div>\n";
echo "</div>\n"; // page

if ( (int)$_GET['ID'] != 0 ) {
	echo "<div id=\"result\" data-role=\"page\"></div>\n"; // page
}
?>