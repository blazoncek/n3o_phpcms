<?php
/*~ edit_frmForums.php - edit forum thread data
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

$Podatek = $db->get_row("SELECT * FROM frmForums WHERE ID = ".(int)$_GET['ID']);
?>

<script language="JavaScript" type="text/javascript">
<!-- //
$('#edit').live('pageinit', function(event){
	// add change events
	$("input:text, input:password, input:checkbox, textarea, select").change(function(){
//		var arr_unchecked_values = "".concat($('input[type=checkbox]:not(:checked)').map(function(){return {"name": this.name, "value": this.value}}).get());
//		var arr_unchecked_values = "".concat($('input[type=checkbox]:not(:checked)').map(function(){return this.name+'='+(this.checked?"yes":"no");}).get());
//		alert(arr_unchecked_values);
		if ( this.type == "checkbox" ) this.value = this.checked ? "yes" : "no";
		if ( this.name=="ForumName" && this.value.length==0 )	{alert("Prosim vnesite naziv!"); fObj.focus(); return false;}
		URL = '<?php echo dirname($_SERVER['PHP_SELF'])?>/upd.php?<?php echo $_SERVER['QUERY_STRING'] ?>';
//return false;
		$.mobile.loadPage(URL, {
			pageContainer: $("#result"),
			reloadPage: true,
			type: "post",
			data: $(this).serialize() // this.name+'='+this.value
		});
		this.blur();
	});
//	$.mobile.loadPage('inc.php?Izbor=frmTopic&ForumID=<?php echo $_GET['ID'] ?>', {
//		reloadPage: true
//	});
});
//-->
</script>
<?php
echo "<div id=\"edit\" data-role=\"page\" data-title=\"Forum\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Forum</h1>\n";
echo "<a href=\"list.php?Izbor=". $_GET['Izbor'] ."\" title=\"Nazaj\" data-role=\"button\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-ajax=\"false\" data-transition=\"slide\">Nazaj</a>\n";
echo "<a href=\"./\" title=\"Domov\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Domov</a>\n";
echo "</div>\n";
echo "<div data-role=\"content\">\n";
?>
	<fieldset class="ui-hide-label" data-role="fieldcontain">
		<LABEL FOR="frmCategory"><B>Skupina:</B></LABEL>
		<SELECT NAME="CategoryID" ID="frmCategory" SIZE="1" data-theme="a">
	<?php 
		$Categories = $db->get_results( "SELECT ID, CategoryName FROM frmCategories ORDER BY CategoryOrder" );
		if ( $Categories ) foreach ( $Categories as $Category )
			echo "<OPTION VALUE=\"$Category->ID\"".(($Podatek && $Podatek->CategoryID == $Category->ID)? " SELECTED": "").">$Category->CategoryName</OPTION>\n";
	?>
		</SELECT>

		<LABEL FOR="frmNaziv"><B>Naziv</B></LABEL>
		<INPUT NAME="ForumName" ID="frmNaziv" TYPE="Text" MAXLENGTH="50" VALUE="<?php if ( $Podatek ) echo $Podatek->ForumName ?>" placeholder="Naziv" data-theme="d"><br />

		<LABEL FOR="frmOpis">Opis</LABEL>
		<TEXTAREA ID="frmOpis" NAME="Description" data-theme="d"><?php if ( $Podatek ) echo $Podatek->Description ?></TEXTAREA>
<!--
		<LABEL FOR="frmPassword">Geslo</LABEL>
		<INPUT NAME="Password" ID="frmPassword" TYPE="Password" MAXLENGTH="16" VALUE="<?php if ( $Podatek ) echo $Podatek->Password ?>" placeholder="Geslo" data-theme="d"><br />
-->
	</fieldset>
<!--
	<fieldset data-role="fieldcontain">
		<LABEL FOR="frmModerator">Moderator</LABEL>
		<SELECT NAME="Moderator" ID="frmModerator" SIZE="1" data-theme="d">
	<?php 
		$Moderators = $db->get_results( "SELECT ID, Name, Nickname FROM frmMembers WHERE AccessLevel > 1 ORDER BY AccessLevel DESC, Nickname" );
		if ( $Moderators ) foreach ( $Moderators as $Moderator )
			echo "<OPTION VALUE=\"$Moderator->ID\"".(($Podatek && $Podatek->ModeratorID == $Moderator->ID)? " SELECTED": "").">$Moderator->Name</OPTION>\n";
	?>
		</SELECT><br />
	</fieldset>

	<fieldset data-role="fieldcontain">
		<LABEL FOR="frmNotifyModerator">Obveščaj</LABEL>
		<select ID="frmNotifyModerator" NAME="NotifyModerator" data-role="slider" data-theme="b">
			<option value="no">No</option>
			<option value="yes" <?php if ( $Podatek && $Podatek->NotifyModerator ) echo "SELECTED" ?>>Yes</option>
		</select><br />
	</fieldset>

	<fieldset data-role="fieldcontain">
		<LABEL FOR="frmPurge">Čiščenje</LABEL>
		<SELECT NAME="PurgeDays" ID="frmPurge" SIZE="1" data-theme="d">
			<OPTION VALUE="0">brez čiščenja</OPTION>
			<OPTION VALUE="30" <?php if ( $Podatek && $Podatek->PurgeDays==30 ) echo "SELECTED" ?>>30 dni</OPTION>
			<OPTION VALUE="90" <?php if ( $Podatek && $Podatek->PurgeDays==90 ) echo "SELECTED" ?>>90 dni</OPTION>
			<OPTION VALUE="180" <?php if ( $Podatek && $Podatek->PurgeDays==180 ) echo "SELECTED" ?>>180 dni</OPTION>
			<OPTION VALUE="365" <?php if ( $Podatek && $Podatek->PurgeDays==365 ) echo "SELECTED" ?>>1 leto</OPTION>
		</SELECT><br />
	</fieldset>

	<fieldset data-role="fieldcontain">
		<LABEL for="frmPrivate">Privatna</LABEL>
		<select ID="frmPrivate" NAME="Private" data-role="slider" data-theme="b">
			<option value="no">No</option>
			<option value="yes" <?php if ( $Podatek && $Podatek->Private ) echo "SELECTED" ?>>Yes</option>
		</select><br />

		<LABEL for="frmApprovalRequired">Odobritev</LABEL>
		<select ID="frmApprovalRequired" NAME="ApprovalRequired" data-role="slider" data-theme="b">
			<option value="no">No</option>
			<option value="yes" <?php if ( $Podatek && $Podatek->ApprovalRequired ) echo "SELECTED" ?>>Yes</option>
		</select><br />

		<LABEL for="frmHidden">Skrita</LABEL>
		<select ID="frmHidden" NAME="Hidden" data-role="slider" data-theme="b">
			<option value="no">No</option>
			<option value="yes" <?php if ( $Podatek && $Podatek->Hidden ) echo "SELECTED" ?>>Yes</option>
		</select><br />

		<LABEL for="frmViewOnly">Samo prikaz</LABEL>
		<select ID="frmViewOnly" NAME="ViewOnly" data-role="slider" data-theme="b">
			<option value="no">No</option>
			<option value="yes" <?php if ( $Podatek && $Podatek->ViewOnly ) echo "SELECTED" ?>>Yes</option>
		</select><br />

		<LABEL for="frmPollEnabled">Ankete</LABEL>
		<select ID="frmPollEnabled" NAME="PollEnabled" data-role="slider" data-theme="b">
			<option value="no">No</option>
			<option value="yes" <?php if ( $Podatek && $Podatek->PollEnabled ) echo "SELECTED" ?>>Yes</option>
		</select><br />
	</fieldset>

	<fieldset data-role="fieldcontain">
		<LABEL for="frmAllowFileUploads">Nalaganje</LABEL>
		<select ID="frmAllowFileUploads" NAME="AllowFileUploads" data-role="slider" data-theme="b">
			<option value="no">No</option>
			<option value="yes" <?php if ( $Podatek && $Podatek->AllowFileUploads ) echo "SELECTED" ?>>Yes</option>
		</select><br />

		<INPUT NAME="UploadType" TYPE="Text" MAXLENGTH="64" VALUE="<?php if ( $Podatek ) echo $Podatek->UploadType ?>" placeholder="(.txt,.jpg,...)"><br />
		<INPUT NAME="MaxUploadSize" ID="frmMaxUploadSize" TYPE="range" VALUE="<?php if ( $Podatek ) echo $Podatek->MaxUploadSize ?>" min="0" max="1024" step="16" placeholder="kB">
		<label for="frmMaxUploadSize">kB</label>
	</fieldset>
-->
<?php
$List = $db->get_results(
	"SELECT
		T.ID,
		T.TopicName,
		T.LastMessageDate,
		T.MessageCount,
		(SELECT count(*) FROM frmMessages M WHERE M.ForumID = T.ForumID AND M.TopicID = T.ID) AS TotalMessageCount,
		T.Sticky,
		P.Votes
	FROM
		frmTopics T
		LEFT JOIN frmPoll P ON T.ID = P.TopicID
	WHERE
		ForumID = ".(int)$_GET['ID']."
	HAVING
		TotalMessageCount > 0
	ORDER BY
		T.Sticky DESC,
		T.LastMessageDate DESC,
		TotalMessageCount DESC,
		T.TopicName"
);

echo "<fieldset class=\"ui-hide-label\" data-role=\"fieldcontain\" data-theme=\"a\">";
echo "<legend>Teme z vsebino</legend>\n";

if ( count($List) ) {
	echo "<ul data-role=\"listview\" data-inset=\"true\" data-theme=\"d\" data-count-theme=\"e\">\n";
	foreach ( $List as $Item ) {
		$Title = $Item->TopicName;
		if ( $Title=="" ) $Title = "(brez naziva)";
		echo "<li>";
		echo "<A HREF=\"inc.php?Izbor=frmMessages&Action=". $_GET['Action'] ."&ID=". $_GET['ID'] ."&TopicID=". $Item->ID ."\">";
		echo "<h3>". $Title ."</h3>";
		if ( sqldate2time($Item->LastMessageDate) ) {
			echo "<p>";
			echo date("j.n.y \@ H:i",sqldate2time($Item->LastMessageDate));
			echo "</p>";
		}
		if ( $Item->TotalMessageCount > $Item->MessageCount ) {
			echo "<span class=\"ui-li-count\">";
			echo (int)$Item->TotalMessageCount - (int)$Item->MessageCount;
			echo "</span>";
		}
		echo "</A>";
		echo "</li>\n";
	}
	echo "</ul>\n";
} else {
	echo "<div class=\"ui-body ui-body-d ui-corner-all\" style=\"color:red;padding:1em;text-align:center;\">\n";
	echo "<B>Ni podatkov!</B>\n";
	echo "</div>\n";
}
echo "</fieldset>\n";

echo "\t</div>\n";
echo "</div>\n"; // page

echo "<div id=\"result\" data-role=\"page\"></div>\n"; // page
?>
