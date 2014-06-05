<?php
/*~ vnos_SifrantTxt.php - Editing of parameter texts.
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
if ( !isset( $_GET['Jezik'] ) ) $_GET['Jezik'] = "Novo";

/* NOTE: moved to edit_Sifranti
// insert/update text value
if ( isset($_POST['TxtID']) && $_POST['TxtID'] != "" ) {
	
	$db->query( "START TRANSACTION" );
	$ID = $db->get_var(
		"SELECT ID ".
		"FROM SifrantiTxt ".
		"WHERE SifrantID = ".$_POST['TxtID']." AND ".
		"	Jezik ".(($_POST['Jezik']!="")? "='".$_POST['Jezik']."'": "IS NULL")
	);
	if ( !$ID )
		$db->query(
			"INSERT INTO SifrantiTxt (".
			"	SifrantID,".
			"	Jezik,".
			"	SifNaziv,".
			"	SifCVal1,".
			"	SifCVal2,".
			"	SifCVal3".
			") VALUES (".
			"	".$_POST['TxtID'].",".
			"	".(($_POST['Jezik']!="")? "'".$_POST['Jezik']."'": "NULL").",".
			"	".(($_POST['Naziv']!="")? "'".$_POST['Naziv']."'": "'(prazno)'").",".
			"	".(($_POST['CVal1']!="")? "'".$_POST['CVal1']."'": "NULL").",".
			"	".(($_POST['CVal2']!="")? "'".$_POST['CVal2']."'": "NULL").",".
			"	".(($_POST['CVal3']!="")? "'".$_POST['CVal3']."'": "NULL").
			")"
		);
	else
		$db->query(
			"UPDATE SifrantiTxt ".
			"SET SifNaziv = ".(($_POST['Naziv']!="")? "'".$_POST['Naziv']."'": "'(prazno)'").", ".
			"	SifCVal1 = ".(($_POST['CVal1']!="")? "'".$_POST['CVal1']."'": "NULL").", ".
			"	SifCVal2 = ".(($_POST['CVal2']!="")? "'".$_POST['CVal2']."'": "NULL").", ".
			"	SifCVal3 = ".(($_POST['CVal3']!="")? "'".$_POST['CVal3']."'": "NULL")." ".
			"WHERE ID= " . $ID
		);
	$db->query( "COMMIT" );
}
*/

$Podatek = $db->get_row(
	"SELECT ST.*, S.ACLID " .
	"FROM SifrantiTxt ST " .
	"	LEFT JOIN Sifranti S ON ST.SifrantID = S.SifrantID " .
	"WHERE ST.SifrantID = " . (int)$_GET['ID'] .
	"	AND ST.Jezik ".((isset($_GET['Jezik']) && $_GET['Jezik']!="")? "='".$_GET['Jezik']."'": "IS NULL")
);
if ( $Podatek )
	$ACL = userACL( $Podatek->ACLID );
else
	$ACL = "LRWDX";
?>
<script language="JavaScript" type="text/javascript">
<!-- //
$(document).ready(function(){
	// bind to the form's submit event
	$("form[name='Text']").each(function(){
		$(this).submit(function(){
			$(this).ajaxSubmit({
				target: '#divEdit',
				beforeSubmit: function( formDataArr, jqObj, options ) {
					var fObj = jqObj[0];	// form object
					if (fObj.Jezik.selectedIndex==0)	{alert("Izberite jezik!"); fObj.Jezik.focus(); return false;}
					if (empty(fObj.Naziv))	{alert("Prosim vnesite naziv!"); fObj.Naziv.focus(); return false;}
					return true;
				} // pre-submit callback
			});
			return false;
		});
	});
});
//-->
</script>

<FIELDSET style="width:340px;">
<LEGEND>Vnos tekstov</LEGEND>
<FORM NAME="Text" ACTION="edit.php?Action=<?php echo $_GET['Action'] ?>&ID=<?php echo $_GET['ID'] ?>" METHOD="post">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<INPUT TYPE="hidden" NAME="TxtID" VALUE="<?php echo $_GET['ID'] ?>">
<TR>
	<TD ALIGN="right"><B>Jezik:</B>&nbsp;</TD>
	<TD><SELECT <?php echo (($_GET['Jezik']!="Novo")? "DISABLED": "NAME=\"Jezik\"") ?> SIZE="1" TABINDEX="1">
		<OPTION VALUE="" DISABLED STYLE="background-color:whitesmoke;">Izberi...</OPTION>
<?php
$Jeziki = $db->get_results(
	"SELECT J.Jezik, J.Opis ".
	"FROM Jeziki J ".
	"	LEFT JOIN SifrantiTxt ST ON J.Jezik = ST.Jezik AND ST.SifrantID = ".(int)$_GET['ID'].
	(($_GET['Jezik']=="Novo")? " WHERE ST.Jezik IS NULL": "")
);
$All = $db->get_var(
	"SELECT count(*) ".
	"FROM SifrantiTxt ST ".
	"WHERE ST.SifrantID = ".(int)$_GET['ID'].
	"	AND ST.Jezik IS NULL"
);
if ( !($All && $_GET['Jezik'] == "Novo") )
	echo "<OPTION VALUE=\"\"".(($_GET['Jezik']=="")? " SELECTED": "").">- vsi jeziki -</OPTION>\n";
if ( $Jeziki )
	foreach ( $Jeziki as $Jezik )
		echo "<OPTION VALUE=\"$Jezik->Jezik\"".(($Jezik->Jezik==$_GET['Jezik'])? " SELECTED": "").">$Jezik->Opis</OPTION>\n";
?>
	</SELECT>
	<?php if ($_GET['Jezik']!="Novo") : ?><INPUT NAME="Jezik" TYPE="Hidden" VALUE="<?php echo $_GET['Jezik'] ?>"><?php endif ?>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="baseline"><B>Naziv:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="Naziv" MAXLENGTH="64" VALUE="<?php echo ($Podatek)? $Podatek->SifNaziv: "" ?>" STYLE="width:100%">
		<?php echo ($Podatek) ?
			"<div class=\"f10 gry\">". $Podatek->SifNazivDesc ."</div>" :
			"<div><INPUT TYPE=\"text\" NAME=\"NazivDesc\" CLASS=\"f10\" style=\"color:#aaa;border:solid 1px #999;width:100%;\" VALUE=\" field description\" onfocus=\"this.value==' field description' ? this.value='' : i=0;\"></div>" ?>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="baseline"><B>CVal1:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="CVal1" MAXLENGTH="128" VALUE="<?php echo ($Podatek)? $Podatek->SifCVal1: "" ?>" STYLE="width:100%">
		<?php echo ($Podatek) ?
			"<div class=\"f10 gry\">". $Podatek->SifCVal1Desc ."</div>" :
			"<div><INPUT TYPE=\"text\" NAME=\"CVal1Desc\" CLASS=\"f10\" style=\"color:#aaa;border:solid 1px #999;width:100%;\" VALUE=\" field description\" onfocus=\"this.value==' field description' ? this.value='' : i=0;\"></div>" ?>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="baseline"><B>CVal2:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="CVal2" MAXLENGTH="128" VALUE="<?php echo ($Podatek)? $Podatek->SifCVal2: "" ?>" STYLE="width:100%">
		<?php echo ($Podatek) ?
			"<div class=\"f10 gry\">". $Podatek->SifCVal2Desc ."</div>" :
			"<div><INPUT TYPE=\"text\" NAME=\"CVal2Desc\" CLASS=\"f10\" style=\"color:#aaa;border:solid 1px #999;width:100%;\" VALUE=\" field description\" onfocus=\"this.value==' field description' ? this.value='' : i=0;\"></div>" ?>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="baseline"><B>CVal3:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="CVal3" MAXLENGTH="128" VALUE="<?php echo ($Podatek)? $Podatek->SifCVal3: "" ?>" STYLE="width:100%">
		<?php echo ($Podatek) ?
			"<div class=\"f10 gry\">". $Podatek->SifCVal3Desc ."</div>" :
			"<div><INPUT TYPE=\"text\" NAME=\"CVal3Desc\" CLASS=\"f10\" style=\"color:#aaa;border:solid 1px #999;width:100%;\" VALUE=\" field description\" onfocus=\"this.value==' field description' ? this.value='' : i=0;\"></div>" ?>
	</TD>
</TR>
<?php if ( contains($ACL,"W") ) : ?>
<TR><TD COLSPAN="2"><HR SIZE="2"></TD></TR>
<TR>
	<TD ALIGN="right" COLSPAN="4"><INPUT TYPE="submit" VALUE=" Zapiši " CLASS="but"></TD>
</TR>
<?php endif ?>
</TABLE>
</FORM>
</FIELDSET>
