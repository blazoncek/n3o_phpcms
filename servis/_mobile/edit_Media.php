<?php
/*~ edit_Media.php - Add/edit media uploads.
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

$Podatek = $db->get_row("SELECT * FROM Media WHERE MediaID = ". (int)$_GET['ID']);
// get ACL
if ( $Podatek )
	$ACL = userACL($Podatek->ACLID);
else
	$ACL = $ActionACL;

// image parameters
$x = $db->get_row(
	"SELECT
		ST.SifNaziv AS GalleryBase,
		S.SifNVal1 AS DefPicSize,
		S.SifNVal2 AS DefThumbSize,
		S.SifNVal3 AS MaxPicSize
	FROM
		Sifranti S
		LEFT JOIN SifrantiTxt ST ON S.SifrantID=ST.SifrantID
	WHERE
		S.SifrCtrl = 'BESE'
		AND S.SifrText = 'Gallery'
	ORDER BY
		ST.Jezik DESC"
);
// deafult values for image upload sisze
$GalleryBase  = "";
$DefPicSize   = 640;
$DefThumbSize = 128;
$MaxPicSize   = 1024;
if ( $x ) {
	$GalleryBase  = $x->GalleryBase;
	$DefPicSize   = (int)$x->DefPicSize;
	$DefThumbSize = (int)$x->DefThumbSize;
	$MaxPicSize   = (int)$x->MaxPicSize;
}
?>
<script language="JavaScript" type="text/javascript">
<!-- //
$('#edit').live('pageinit', function(event){
<?php if ( (int)$_GET['ID'] == 0 ) : ?>
	// bind to the form's submit event
	$("#frmMedia").submit(function(e){
		// inside event callbacks 'this' is the DOM element so we first
		// wrap it in a jQuery object
		jqObj = $(this);
		if (empty(jqObj[0].Naziv))	{alert("Please enter name!"); jqObj[0].Naziv.focus(); return false;}
		if (empty(jqObj[0].Dodaj))	{alert("Please select image!"); jqObj[0].Dodaj.focus(); return false;}
		return true;
	});
<?php else : ?>
	// handle field changes
	$("input[name!='Find']:text, textarea, select").change(function(){
		var fObj = this;	// form object
		if (fObj.name=="Naziv" && fObj.value.length==0)	{alert("Please enter title!"); fObj.focus(); return false;}
		URL = '<?php echo dirname($_SERVER['PHP_SELF'])?>/upd.php?<?php echo $_SERVER['QUERY_STRING'] ?>';
		$.mobile.loadPage(URL, {
			pageContainer: $("#result"),
			reloadPage: true,
			type: "post",
			data: $(this).serialize() // this.name+'='+this.value
		});
		this.blur();
	});
	$("input[name='Find']").change(function(e){
		e.preventDefault();
		var fObj = this;	// input object
		if (fObj.value.length==0) {return false;}
		URL = '<?php echo dirname($_SERVER['PHP_SELF']) ?>/inc.php?Izbor=medText&MediaID=<?php echo $_GET['ID'] ?>';
		$.mobile.changePage(URL, {
			reloadPage: true,
			type: "get",
			data: $(this).serialize() // this.name+'='+this.value
		});
		return false;
	});
<?php endif ?>
});

function checkFld(fld, ID, Naziv) {
	if (confirm("Do you want to remove '"+Naziv+"'?")) {
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
echo "<div id=\"edit\" data-role=\"page\" data-title=\"Attachments\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Attachments</h1>\n";
echo "<a href=\"list.php?Izbor=". $_GET['Izbor'] ."\" title=\"Back\" data-role=\"button\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-ajax=\"false\" data-transition=\"slide\">Back</a>\n";
echo "<a href=\"./\" title=\"Home\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Home</a>\n";
echo "</div>\n";
echo "<div data-role=\"content\">\n";

if ( (int)$_GET['ID'] == 0 )
	echo "<FORM ID=\"frmMedia\" ACTION=\"". $_SERVER['PHP_SELF'] ."?". $_SERVER['QUERY_STRING'] ."\" METHOD=\"post\" enctype=\"multipart/form-data\" data-ajax=\"false\">\n";

if ( isset($Error) ) {
	echo "<div class=\"ui-body ui-body-d ui-corner-all\" style=\"padding:1em;text-align:center;\">";
	echo "<b>Error!</b><br>Data not saved.";
	echo "</div>\n";
} else {
?>
	<div data-role="fieldcontain">
		<LABEL FOR="frmNaziv"><B>Title:</B></LABEL>
		<INPUT TYPE="text" ID="frmNaziv" NAME="Naziv" MAXLENGTH="32" VALUE="<?php if ( $Podatek ) echo $Podatek->Naziv; ?>" placeholder="Naziv" data-theme="d"><br />
	</div>
	<div data-role="fieldcontain">
		<LABEL FOR="frmIzpis"><b>Show:</b></LABEL>
		<select ID="frmIzpis" NAME="Izpis" data-role="slider" data-theme="b">
			<option value="no">No</option>
			<option value="yes" <?php if ( $Podatek && $Podatek->Izpis ) echo "SELECTED" ?>>Yes</option>
		</select>
	</div>
	<div data-role="fieldcontain">
<?php if ( $Podatek && $Podatek->Tip == 'PIC' ) : ?>
		<LABEL FOR="frmMeta">Metadata:</LABEL>
		<TEXTAREA ID="frmMeta" NAME="Meta" data-theme="d"><?php if ( $Podatek ) echo $Podatek->Meta ?></TEXTAREA>
<?php elseif ( (int)$_GET['ID'] == 0 ) : ?>
		<LABEL FOR="frmImage">Image:</LABEL>
		<INPUT TYPE="file" ID="frmImage" NAME="Add" data-theme="d">
<?php endif ?>
	</div>
<?php if ( (int)$_GET['ID'] == 0 ) : ?>
	<p>Size &amp; thumb (square?)</p>
	<div data-role="fieldcontain">
		<div class="ui-grid-b">
			<div class="ui-block-a">
				<INPUT TYPE="number" ID="fldR" NAME="R" VALUE="<?php echo abs((int)$DefPicSize) ?>" data-mini="true" data-theme="d">
			</div>
			<div class="ui-block-b">
				<INPUT TYPE="number" ID="fldT" NAME="T" VALUE="<?php echo abs((int)$DefThumbSize) ?>" data-mini="true" data-theme="d">
			</div>
			<div class="ui-block-c">
				<select ID="fldS" NAME="S" data-role="slider" data-mini="true" data-theme="b">
					<option value="no">No</option>
					<option value="yes" <?php echo (int)$DefThumbSize<0 ? "SELECTED" : "" ?>>Yes</option>
				</select>
			</div>
		</div>
	</div>
<?php endif ?>
<?php
}

if ( (int)$_GET['ID'] != 0 ) {

	echo "<fieldset class=\"ui-hide-label\" data-role=\"fieldcontain\" data-theme=\"a\">";
	echo "<legend>Opisi</legend>\n";
	$List = $db->get_results(
		"SELECT ID, Naslov AS Naziv, Jezik ".
		"FROM MediaOpisi ".
		"WHERE MediaID = ".(int)$_GET['ID']." ".
		"ORDER BY Jezik"
	);

	echo "<ul data-role=\"listview\" data-inset=\"true\" data-theme=\"d\" data-split-icon=\"delete\" data-split-theme=\"a\" data-count-theme=\"e\">\n";
	if ( $List ) foreach ( $List as $Naziv ) {
		echo "<li>";
		echo (contains($ACL,"W")? "<a href=\"inc.php?Izbor=medDescription&MediaID=".(int)$_GET['ID']."&ID=$Naziv->ID\">": "");
		echo "<b>". $Naziv->Naziv ."</b>";
		echo "<span class=\"ui-li-count\">".(($Naziv->Jezik=="")? "all": $Naziv->Jezik)."</span>";
		echo (contains($ACL,"W")? "</a>": "");
		if ( contains($ACL,"D") )
			echo "<a href=\"#\" onclick=\"checkFld('BrisiOpis','$Naziv->ID','$Naziv->Naziv');\" data-theme=\"c\">Delete</a>";
		echo "</li>\n";
	}
	echo "<li data-icon=\"add\"><a href=\"inc.php?Izbor=medDescription&MediaID=". $_GET['ID'] ."&ID=0\" title=\"Dodaj opis\">Dodaj opis</a>\n";
	echo "</ul>\n";
	echo "</fieldset>\n";

	// display list of assigned media
	echo "<fieldset class=\"ui-hide-label\" data-role=\"fieldcontain\" data-theme=\"a\">";
	echo "<legend>Attached to texts</legend>\n";
	$List = $db->get_results(
		"SELECT
			BM.ID,
			BM.MediaID,
			BM.BesediloID,
			BM.Polozaj,
			B.Ime,
			B.ACLID
		FROM
			BesedilaMedia BM
			LEFT JOIN Besedila B ON BM.BesediloID = B.BesediloID
		WHERE
			BM.MediaID = ". (int)$_GET['ID'] ." 

		UNION

		SELECT
			BS.ID,
			BS.MediaID,
			BS.BesediloID,
			BS.Polozaj,
			B.Ime,
			B.ACLID 
		FROM
			BesedilaSlike BS
			LEFT JOIN Besedila B ON BS.BesediloID = B.BesediloID
		WHERE
			BS.MediaID = ". (int)$_GET['ID']
		);

	echo "<ul data-role=\"listview\" data-inset=\"true\" data-theme=\"d\">\n";
	echo "<li data-theme=\"c\"><input type=\"text\" name=\"Find\" placeholder=\"Find\"></li>\n";
	if ( $List ) foreach ( $List as $Item ) {
		echo "<li -data-icon=\"delete\">";
		echo (contains($ACL,"W") ? "<a href=\"inc.php?Izbor=medText&MediaID=". (int)$_GET['ID'] ."\" data-ajax=\"false\" data-theme=\"c\">" : "");
		echo $Item->Ime;
		echo (contains($ACL,"W") ? "</a>": "");
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