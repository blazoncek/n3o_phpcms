<?php
/*~ edit_emlSporocila.php - Edit mailing messages.
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

$Podatek = $db->get_row(
	"SELECT Naziv, Datum, ACLID
	FROM emlMessages
	WHERE emlMessageID = ". (int)$_GET['ID']
);
// get ACL
if ( $Podatek ) {
	$ACL = userACL( $Podatek->ACLID );
} else
	$ACL = $ActionACL;

?>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
function checkLang(ID, Naziv) {
	if (confirm("Ali res želite brisati zapis '"+Naziv+"'?"))
		loadTo('Edit','edit.php?Action=<?php echo $Action->ActionID ?>&ID=<?php echo $_GET['ID'] ?>&BrisiOpis='+ID);
	return false;
}

function setAction(form_obj, action_str) {
	form_obj.Action.value = action_str;
}

function setList(list_obj, select_obj) {
	var count = 0;

	list_obj.value = "";			
    for (i=0; i < select_obj.length; i++) {
		if (select_obj.options[i].selected && select_obj.options[i].value != "") {
			var startPosition = 0;
			var indexPosition = 0;
			var selectString;

			if (count > 0 ) { list_obj.value += ","; }

			selectString = select_obj.options[i].value;
			indexPosition = selectString.indexOf(",");

			for (; indexPosition > 0; indexPosition = selectString.indexOf(",", startPosition)) {
				list_obj.value += selectString.substring(startPosition, indexPosition);
				list_obj.value += "~";	
				startPosition = indexPosition + 1;
			}					

			list_obj.value += selectString.substring(startPosition, selectString.length);
			count++;
		}
	}
}

function customResize() {
	// vertically resize edit child divs
	frame = $("#divContent").height(0).height( $("#divEdit").height() + $("#divEdit").position().top - $("#divContent").position().top );
}

$(document).ready(function(){
	window.customResize = customResize;

	// bind to the form's submit event
	$("form[name='Vnos']").submit(function(){
		// inside event callbacks 'this' is the DOM element so we first
		// wrap it in a jQuery object and then invoke ajaxSubmit
		$(this).ajaxSubmit({
			target: '#divEdit', // target element(s) to be updated with server response
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (empty(fObj.Naziv))	{alert("Prosim vnesite ime sporočila!"); fObj.Naziv.focus(); return false;}
				$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Posodabljam" border="0" height="14" width="14" align="absmiddle">&nbsp;: Posodabljam ...</span>');
				return true;
			} // pre-submit callback
		});
		return false;
	});
	$("form[name='Grupe']").submit(function(){
		$(this).ajaxSubmit({target: '#divEdit'});
		return false;
	});
	$("#Add").click(function(){
		MemberList = document.getElementsByName("MemberList");
		NonGroup   = document.getElementsByName("NonGroup");
		setList(MemberList[0],NonGroup[0]);
		$("form[name='Grupe'] :hidden[name='Action']").val('Add');
		$("form[name='Grupe']").ajaxSubmit({target: '#divEdit'});
	});
	$("#Remove").click(function(){
		MemberList = document.getElementsByName("MemberList");
		Group      = document.getElementsByName("Group");
		setList(MemberList[0],Group[0]);
		$("form[name='Grupe'] :hidden[name='Action']").val('Remove');
		$("form[name='Grupe']").ajaxSubmit({target: '#divEdit'});
	});
	// refresh list
	listRefresh();
});
//-->
</SCRIPT>
<!-- VSEBINA -->
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD VALIGN="top" WIDTH="50%">
	<FIELDSET ID="fldData">
		<LEGEND ID="lgdData">
<?php if ( contains($ACL,"W") && $Podatek ) {
		echo "\t\t<A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','edit.php?Izbor=ACL&ACL=".$Action->Action;
		echo "&emlMessageID=". $_GET['ID'].(($Podatek->ACLID!="")? "&ID=".$Podatek->ACLID: "")."')\" TITLE=\"Uredi pravice\">";
		echo "<IMG SRC=\"pic/control.permissions.gif\" HEIGHT=\"16\" WIDTH=\"16\" BORDER=0 ALT=\"Dovoljenja\" ALIGN=\"absmiddle\"></A>&nbsp;:";
} ?>
			Osnovni&nbsp;podatki
		</LEGEND>
		
		<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
		<TABLE BORDER=0 CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
		<TR>
			<TD ALIGN="right"><B>Oznaka:</B>&nbsp;</TD>
			<TD><INPUT TYPE="text" NAME="Naziv" SIZE="25" MAXLENGTH="100" VALUE="<?php echo $Podatek ? $Podatek->Naziv : "" ?>"></TD>
<?php if ( contains($ACL,"W") ) : ?>
			<TD ALIGN="right"><INPUT TYPE="submit" VALUE=" Zapiši " CLASS="but"></TD>
<?php endif ?>
		</TR>
<?php if ( $Podatek && $Podatek->Datum!="" ) : ?>
		<TR>
			<TD ALIGN="right">Odposlano:&nbsp;</TD>
			<TD><?php echo date('j.n.Y @ H:m',sqldate2time($Podatek->Datum)); ?></TD>
		</TR>
<?php endif ?>
		</TABLE>
		</FORM>
	</FIELDSET>

<?php if ( $Podatek ) : ?>
	<FIELDSET ID="fldContent" style="min-height:8.5em;">
		<LEGEND ID="lgdContent">
<?php if ( contains($ACL,"W") ) : ?>
		<A HREF="javascript:void(0);" ONCLICK="loadTo('Edit','inc.php?Izbor=emlSporocilaTxt&Action=<?php echo $Action->ActionID ?>&emlMessageID=<?php echo $_GET['ID'] ?>')" TITLE="Dodaj"><IMG SRC="pic/control.add_document.gif" ALIGN="absmiddle" WIDTH=14 HEIGHT=14 ALT="Dodaj" BORDER="0" CLASS="icon"></A>&nbsp;:
<?php endif ?>
		Vsebina</LEGEND>
<?php
		$List = $db->get_results(
			"SELECT emlMessageTxtID AS ID, Naziv, Jezik
			FROM emlMessagesTxt
			WHERE emlMessageID=". $_GET['ID'] ."
			ORDER BY Jezik"
		);
		echo "<TABLE BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
		if ( !$List ) 
			echo "<TR><TD ALIGN=\"center\">Ni vsebin!</TD></TR>\n";
		else {
			$CurrentRow = 1;
			$RecordCount = count( $List );
			foreach ( $List as $Item ) {
				echo "<TR ONMOUSEOVER=\"this.style.backgroundColor='whitesmoke';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">\n";
				echo "<TD width=\"8%\">[<b class=\"red\">".($Item->Jezik? $Item->Jezik: "vsi")."</b>]</TD>\n";
				echo "<TD><A HREF=\"javascript:void(0);\" ONCLICK=\"loadTo('Edit','inc.php?Izbor=emlSporocilaTxt&Action=".$Action->ActionID."&emlMessageID=".$_GET['ID']."&ID=".$Item->ID."');\"><B>$Item->Naziv</B></A></TD>\n";
				echo "<TD ALIGN=\"right\" NOWRAP>\n";
				echo "<A HREF=\"../viewmsg.php?id=".$Item->ID."\" TARGET=\"preview\" TITLE=\"Predogled\"><IMG SRC=\"pic/list.extern.gif\" WIDTH=11 HEIGHT=11 ALT=\"Predogled\" BORDER=\"0\" CLASS=\"icon\">\n";
				if ( contains($ACL,"W") ) {
					echo "<A HREF=\"javascript:void(0);\" ONCLICK=\"javascript:checkLang('$Item->ID','$Item->Naziv');\" TITLE=\"Briši\"><IMG SRC=\"pic/list.delete.gif\" WIDTH=11 HEIGHT=11 ALT=\"Briši\" BORDER=\"0\" CLASS=\"icon\">\n";
				}
				echo "</TD>\n";
				echo "</TR>\n";
				$CurrentRow++;
			}
		}
		echo "</TABLE>\n";
?>
	</FIELDSET>
<?php endif ?>
	</TD>

	<!--- SKUPINE SPOROČILA --->
	<TD VALIGN="top" WIDTH="50%">
<?php
if ( (int)$_GET['ID'] > 0 ) {
	// users
	$Members = $db->get_results(
		"SELECT EG.emlGroupID AS ID, EG.Naziv
		FROM emlGroups EG
			LEFT JOIN emlMessagesGrp EMG
				ON EG.emlGroupID = EMG.emlGroupID AND EMG.emlMessageID = ". (int)$_GET['ID'] ."
		WHERE EMG.emlGroupID IS NOT NULL
		ORDER BY EG.Naziv"
	);

	$NonMembers = $db->get_results(
		"SELECT EG.emlGroupID AS ID, EG.Naziv
		FROM emlGroups EG
			LEFT JOIN emlMessagesGrp EMG
				ON EG.emlGroupID = EMG.emlGroupID AND EMG.emlMessageID = ". (int)$_GET['ID'] ."
		WHERE EMG.emlGroupID IS NULL
		ORDER BY EG.Naziv"
	);
?>
	<FIELDSET ID="fldGroup">
	<LEGEND ID="lgdGroup">Skupine</LEGEND>
	<FORM NAME="Grupe" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
	<TABLE ID="results" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
	<TR>
		<TD ALIGN="right" WIDTH="45%">Ne pošlji:</TD>
		<TD ALIGN="center" WIDTH="10%"></TD>
		<TD ALIGN="right" WIDTH="45%">Pošlji:</TD>
	</TR>
	<TR>
		<TD ALIGN="left">
		<INPUT Name="MessageID" Type="HIDDEN" VALUE="<?php echo (int)$_GET['ID'] ?>">
		<INPUT Name="MemberList" Type="HIDDEN" VALUE="">
		<INPUT Name="Action" Type="HIDDEN" VALUE="">
		<SELECT NAME="NonGroup" MULTIPLE SIZE="10" STYLE="width:100%;">
<?php
	if ( count( $NonMembers ) > 0 )
		foreach ( $NonMembers as $NonMember )
			echo "\t\t<OPTION VALUE=\"$NonMember->ID\">$NonMember->Naziv</OPTION>\n";
?>
		</SELECT>
		</TD>
		<TD ALIGN="center">
		<IMG ID="Add" SRC="pic/icon.arrow_right.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon"><BR><BR>
		<IMG ID="Remove" SRC="pic/icon.arrow_left.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon">
<!--
		<INPUT TYPE="Submit" NAME="Add" VALUE="&nbsp;---&gt;&nbsp;" ONCLICK="setList(UserList,NonGroup),setAction(this.form,'Add');" CLASS="but"><BR><BR>
		<INPUT TYPE="Submit" NAME="Remove" VALUE="&nbsp;&lt;---&nbsp;" ONCLICK="setList(UserList,Group),setAction(this.form,'Remove');" CLASS="but">
-->
		</TD>
		<TD ALIGN="right">
		<SELECT NAME="Group" MULTIPLE SIZE="10" STYLE="width:100%;">
<?php
	if ( count( $Members ) > 0 )
		foreach ( $Members as $Member )
			echo "\t\t<OPTION VALUE=\"$Member->ID\">$Member->Naziv</OPTION>\n";
?>
		</SELECT>
		</TD>
	</TR>
	</TABLE>
	</FORM>
	</FIELDSET>
	</TD>
</TR>

<?php if ( false && $Podatek ) : ?>
<TR>
	<TD>
<?php /*
	<!-- PRIPONKE -->
	<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%" STYLE="border:inset 1px;">
	<TR>
		<TD>
		<TABLE ALIGN="center" BORDER="0" CELLPADDING="0" CELLSPACING="0" CLASS="subtitle" WIDTH="100%">
		<TR>
			<TD WIDTH="18"></TD>
			<TD ALIGN="center"><B>Priponke</B></TD>
			<TD WIDTH="18"><A HREF="javascript:results1.style.display==''?collapse.src='#application.pic#/menu/control.plus.gif':collapse.src='#application.pic#/menu/control.minus.gif',results1.style.display==''?results1.style.display='none':results1.style.display='',void 0;"><IMG ID="collapse" SRC="#application.pic#/menu/control.minus.gif" HEIGHT="14" WIDTH="14" ALT="Prika�i/Skrij" BORDER="0"></A></TD>
		</TR>
		</TABLE>
		<TABLE ID="results1" BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
		<CFIF ACL CONTAINS "W">
		<FORM NAME="Vnos" ACTION="#cgi.script_name#?#cgi.query_string#" METHOD="post" ENCTYPE="multipart/form-data">
		<TR CLASS="novo">
			<TD COLSPAN="2" STYLE="border-bottom:darkgray solid 1px;">
			<INPUT TYPE="File" NAME="Datoteka" STYLE="width:100%;border:none;">
			</TD>
			<TD ALIGN="right" WIDTH="10%" STYLE="border-bottom:darkgray solid 1px;">
			<INPUT TYPE="submit" VALUE=" Dodaj " CLASS="find">
			<!--- &nbsp;<A HREF="javascript:void(0);" ONCLICK="window.open('vnos.cfm?Izbor=emlSporocilaDoc&ID=#URL.ID#&DocID=0', 'mainscreen', 'scrollbars=Yes,status=no,menubar=no,toolbar=no,resizable=no,WIDTH=600,HEIGHT=480')">Nova priponka...</A> --->
			</TD>
		</TR>
		</FORM>
		</CFIF>
		<CFQUERY NAME="List" DATASOURCE="#DSN#">
			SELECT emlMessageDocID, Datoteka
			FROM emlMessagesDoc
			WHERE emlMessageID = #val(URL.ID)#
		</CFQUERY>
		<CFIF List.RecordCount EQ 0>
		<TR><TD ALIGN="center" COLSPAN="3">Ni priponk!</TD></TR>
		<CFELSE>
		<CFLOOP QUERY="List">
		<TR ONMOUSEOVER="this.style.backgroundColor='whitesmoke';" ONMOUSEOUT="this.style.backgroundColor='';">
			<TD WIDTH="8%">&nbsp;[<FONT COLOR="Red"><B>#List.CurrentRow#</B></FONT>]</TD>
			<TD><B>#left(List.Datoteka,65)#<CFIF len(List.Datoteka) GT 65>...</CFIF></B></TD>
			<TD ALIGN="right" WIDTH="8%"><CFIF ACL CONTAINS "W"><A HREF="javascript:void(0);" ONCLICK="javascript:check2('#List.emlMessageDocID#','#replace(replace(List.Datoteka,"'","`","ALL"),chr(34),"`","ALL")#');"><IMG SRC="#application.pic#/menu/list.delete.gif" WIDTH=11 HEIGHT=11 ALT="Bri�i" BORDER="0" CLASS="icon"></CFIF></TD>
		</TR>
		</CFLOOP>
		</CFIF>
		</TABLE>
		</TD>
	</TR>
	</TABLE>
*/ ?>
	</TD>
</TR>
<?php endif ?>
</TABLE>
<?php
}
?>
<?php if ( $Podatek ) : ?>
<table border="0" cellpadding="0" cellspacing="0" width="400">
<tr>
	<td>
	<A HREF="javascript:void(0);" ONCLICK="loadTo('Edit','inc.php?Action=<?php echo $_GET['Action'] ?>&Izbor=emlPoslji&ID=<?php echo $_GET['ID'] ?>');">Odpošlji sporočilo</A>
	</td>
	<td align="right">
	</td>
</tr>
</table>
<?php endif ?>