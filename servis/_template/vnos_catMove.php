<?php
/* move category into another category
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

if ( isset($_GET['Cilj']) && $_GET['Cilj'] != "" ) {
	$db->query("START TRANSACTION");
	// fing new vacant ID
	$List = $db->get_row(
		"SELECT KategorijaID FROM Kategorije
		WHERE KategorijaID LIKE '". $db->escape($_GET['Cilj']) ."__'
		ORDER BY KategorijaID DESC"
		);
	if ( count($List) == 0 )
		$CiljID = $_GET['Cilj'] . "01";
	else {
		$CiljID = (int)$List->KategorijaID + 1;
		$CiljID = right("0000000000". $CiljID, strlen($List->KategorijaID));
	}
	$Start = strlen($_GET['KategorijaID']) + 1;

	// popravimo podatek v kategoriji, ki jo premikamo 
	if ( SQLType == "MySQL" ) {
		$db->query( "ALTER TABLE KategorijeNazivi   DROP FOREIGN KEY KTN_FK_KAT" );
		$db->query( "ALTER TABLE KategorijeVsebina  DROP FOREIGN KEY KTV_FK_KAT" );
		$db->query( "ALTER TABLE KategorijeBesedila DROP FOREIGN KEY KTB_FK_KAT" );
		$db->query( "ALTER TABLE KategorijeMedia    DROP FOREIGN KEY KTM_FK_KAT" );
		// NOTE: update ACL names (not mandatory)
		$db->query( "UPDATE SMACL SET Name=ConCat('KTG-". $CiljID ."',substring(Name,". ($Start+4) .",99)) WHERE Name LIKE 'KTG-". $_GET['KategorijaID'] ."%'" );
		// NOTE: end
		$db->query( "UPDATE KategorijeMedia    SET KategorijaID=ConCat('$CiljID',substring(KategorijaID,$Start,99)) WHERE KategorijaID LIKE '". $_GET['KategorijaID'] ."%'" );
		$db->query( "UPDATE KategorijeBesedila SET KategorijaID=ConCat('$CiljID',substring(KategorijaID,$Start,99)) WHERE KategorijaID LIKE '". $_GET['KategorijaID'] ."%'" );
		$db->query( "UPDATE KategorijeVsebina  SET KategorijaID=ConCat('$CiljID',substring(KategorijaID,$Start,99)) WHERE KategorijaID LIKE '". $_GET['KategorijaID'] ."%'" );
		$db->query( "UPDATE KategorijeNazivi   SET KategorijaID=ConCat('$CiljID',substring(KategorijaID,$Start,99)) WHERE KategorijaID LIKE '". $_GET['KategorijaID'] ."%'" );
		$db->query( "UPDATE Kategorije         SET KategorijaID=ConCat('$CiljID',substring(KategorijaID,$Start,99)) WHERE KategorijaID LIKE '". $_GET['KategorijaID'] ."%'" );
		$db->query( "ALTER TABLE KategorijeNazivi   ADD CONSTRAINT KTN_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
		$db->query( "ALTER TABLE KategorijeVsebina  ADD CONSTRAINT KTV_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
		$db->query( "ALTER TABLE KategorijeBesedila ADD CONSTRAINT KTB_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
		$db->query( "ALTER TABLE KategorijeMedia    ADD CONSTRAINT KTM_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
	} else if ( SQLType == "MsSQL" ) {
		$db->query( "ALTER TABLE KategorijeNazivi   DROP CONSTRAINT KTN_FK_KAT" );
		$db->query( "ALTER TABLE KategorijeVsebina  DROP CONSTRAINT KTV_FK_KAT" );
		$db->query( "ALTER TABLE KategorijeBesedila DROP CONSTRAINT KTB_FK_KAT" );
		$db->query( "ALTER TABLE KategorijeMedia    DROP CONSTRAINT KTM_FK_KAT" );
		// NOTE: update ACL names (not mandatory)
		$db->query( "UPDATE SMACL SET Name='KTG-". $CiljID ."' + substring(Name,". ($Start+4) .",99)     WHERE Name LIKE 'KTG-". $_GET['KategorijaID'] ."%'" );
		// NOTE: end
		$db->query( "UPDATE KategorijeMedia    SET KategorijaID='$CiljID' + substring(KategorijaID,$Start,99) WHERE KategorijaID LIKE '". $_GET['KategorijaID'] ."%'" );
		$db->query( "UPDATE KategorijeBesedila SET KategorijaID='$CiljID' + substring(KategorijaID,$Start,99) WHERE KategorijaID LIKE '". $_GET['KategorijaID'] ."%'" );
		$db->query( "UPDATE KategorijeVsebina  SET KategorijaID='$CiljID' + substring(KategorijaID,$Start,99) WHERE KategorijaID LIKE '". $_GET['KategorijaID'] ."%'" );
		$db->query( "UPDATE KategorijeNazivi   SET KategorijaID='$CiljID' + substring(KategorijaID,$Start,99) WHERE KategorijaID LIKE '". $_GET['KategorijaID'] ."%'" );
		$db->query( "UPDATE Kategorije         SET KategorijaID='$CiljID' + substring(KategorijaID,$Start,99) WHERE KategorijaID LIKE '". $_GET['KategorijaID'] ."%'" );
		$db->query( "ALTER TABLE KategorijeNazivi   ADD CONSTRAINT KTN_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
		$db->query( "ALTER TABLE KategorijeVsebina  ADD CONSTRAINT KTV_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
		$db->query( "ALTER TABLE KategorijeBesedila ADD CONSTRAINT KTB_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
		$db->query( "ALTER TABLE KategorijeMedia    ADD CONSTRAINT KTM_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID)" );
	}
	$db->query("COMMIT");

	echo "<SCRIPT type=\"text/javascript\">\n";
	echo "<!--\n";
	echo "window.opener.$('#divEdit').load('edit.php?Izbor=Categories&ID=". $CiljID ."');\n";
	echo "window.close();\n";
	echo "//-->\n";
	echo "</SCRIPT>\n";

	die();
}

// convert passed parameter
if ( isset($_GET['Find']) && !isset($_POST['Find']) ) $_POST['Find'] = $_GET['Find'];

$List = $db->get_results(
	"SELECT DISTINCT
		K.KategorijaID,
		K.Ime,
		K.ACLID
	FROM
		Kategorije K ".
		(isset($_POST['Find']) && $_POST['Find'] != "" ?
			"LEFT JOIN KategorijeNazivi KN ON K.KategorijaID = KN.KategorijaID
			WHERE
			(K.Ime LIKE '%". $_POST['Find'] ."%' OR KN.Naziv LIKE '%". $_POST['Find'] ."%') " :
			" " ).
	"ORDER BY
		K.KategorijaID"
);
?>
<SCRIPT type="text/javascript">
<!--
window.focus();

function fixSize() {
	$("#divList").height( $(window).height() - $("#divList").position().top - 10 ).width( $(window).width() - 10 );
}

$(document).ready(function() {
	// search fields
	$('input[id^=findTxt]').click(function(){
		$('#findClr'+$(this).attr('id').substr(7,2)).show();
		if ( $(this).val().substr(0,1) == " " && $(this).val().substr($(this).val().length-1,1) == " " ) {
			$(this).css('color','#000').val('');
			$('#findClr'+$(this).attr('id').substr(7,2)).hide();
		}
	}).keypress(function(e){
		if ( e.keyCode == 13 ) {
			//e.preventDefault();
		}
		$('#findClr'+$(this).attr('id').substr(7,2)).show();
	}).keyup(function(e){
		if ( e.keyCode == 13 ) {
			//e.preventDefault();
		}
		if ( $('#findTxt'+$(this).attr('id').substr(7,2)).val() == "" )
			$('#findClr'+$(this).attr('id').substr(7,2)).hide();
	});
	// add clear action
	$('a[id^=findClr]').click(function(){
		$(this).hide();
		$('#findTxt'+$(this).attr('id').substr(7,2)).val('').select();
	});

	fixSize();
});
$(window).resize(fixSize);
//-->
</SCRIPT>

<DIV ALIGN="center" CLASS="subtitle"><B>Select category</B></DIV>
<div id="findRu" class="find" style="margin:5px 0;">
<form name="findFrmRu" action="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" method="post">
<input id="findTxtRu" type="Text" name="Find" maxlength="32" value="<?php echo (isset($_POST['Find'])? $_POST['Find']: " Search ") ?>" style="color:#aaa;">
<a id="findClrRu" href="javascript:void(0);"><img src="pic/list.clear.gif" border="0"></a>
</form>
</div>

<DIV ID="divList" STYLE="overflow: auto; background: White; padding: 5px; margin-top: 2px; width: 100%;">
	<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%" CLASS="frame">
<?php if ( !isset($List) ) : ?>
	<TR BGCOLOR="white">
		<TD ALIGN="center" VALIGN="middle">
		<BR><BR><B>No data!</B><BR><BR><BR>
		</TD>
	</TR>
<?php else : ?>
	<?php
	$BgCol = "lightgrey";
	foreach ( $List as $Item ) {
		// get ACL
		$ACL = userACL( $Item->ACLID );
		if ( contains($ACL,"L") ) {
			$BgCol = $BgCol=="white" ? "lightgrey" : "white";
			$Title = $Item->Ime=="" ? "(brez)" : $Item->Ime;
			echo "<TR BGCOLOR=\"$BgCol\">\n";
			echo "<TD>". str_repeat("&nbsp;",strlen($Item->KategorijaID));
			if ( $_GET['KategorijaID'] != left($Item->KategorijaID,strlen($_GET['KategorijaID'])) )
				echo "<A HREF=\"". $_SERVER['PHP_SELF'] ."?". $_SERVER['QUERY_STRING'] ."&Cilj=". $Item->KategorijaID ."\">";
			echo left($Title,65).(strlen($Title)>65? "...": "");
			if ( $_GET['KategorijaID'] != left($Item->KategorijaID,strlen($_GET['KategorijaID'])) )
				echo "</A>";
			echo "</TD>\n";
			echo "</TR>\n";
		}
	}
	?>
<?php endif ?>
	</TABLE>
<BR>
</DIV>
