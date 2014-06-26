<?php
/*
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

if ( isset($_GET['New']) && $_GET['New'] != "" ) {
	// get text type (defines comments forum name)
	$Tip = $db->get_var("SELECT Tip FROM Besedila WHERE BesediloID = ". (int)$_GET['ID']);
	// get forum ID
	$id = $db->get_var("SELECT ID FROM frmForums WHERE ForumName = '". $Tip ."'");
	if ( $id ) {
		$db->query(
			"INSERT INTO frmTopics (".
			"	ForumID,".
			"	TopicName,".
			"	MessageCount".
			") VALUES (".
			"	$id,".
			"	'".left($_GET['New'],63)."',".
			"	0".
			")"
		);
		$id = $db->insert_id;
		$db->query(
			"UPDATE Besedila ".
			"SET ForumTopicID = $id ".
			"WHERE BesediloID = ".(int)$_GET['ID']
		);
	}

	echo "<SCRIPT type=\"text/javascript\">\n";
	echo "<!--\n";
	echo "window.opener.$('#divEdit').load('edit.php?Izbor=Text&ID=".$_GET['ID']."');\n";
	echo "window.close();\n";
	echo "//-->\n";
	echo "</SCRIPT>\n";
	die();
}

// get list of forums
if ( isset($_GET['Find']) && $_GET['Find'] != "" )
	$List = $db->get_results(
		"SELECT T.ID, T.TopicName, F.ForumName ".
		"FROM frmTopics T ".
		"	LEFT JOIN frmForums F ON T.ForumID = F.ID ".
		"WHERE ".( is_numeric($_GET['Find'])?
			"T.ID = ".(int)$_GET['Find']:
			"(T.TopicName LIKE '%".$db->escape($_GET['Find'])."%' OR F.ForumName LIKE '%".$db->escape($_GET['Find'])."%')" )." ".
		"ORDER BY F.ForumName, T.TopicName"
	);

// build search links
$FindURL = $_SERVER['PHP_SELF'] ."?";
foreach ( explode( "&", $_SERVER['QUERY_STRING'] ) as $Param ) {
	// prevent empty parameters (double &)
	if ( $Param == "") continue;
	// split parameter to name and value: x=[name,value]
	$x = explode( "=", $Param );
	// check if preprocessing changed parameter
	if ( $_GET[$x[0]] != $x[1] )
		$Param = $x[0] . "=" . $_GET[$x[0]];
	else
		$Param = $x[0] . "=" . $x[1];
	// remove certain parameters
	if ( $x[0] != "Find" && $x[0] != "New" && $x[0] != "ID" )
		$FindURL .= $Param . "&";
}
if ( substr($FindURL,-1) == "&" )
	$FindURL = substr($FindURL,0,strlen($FindURL)-1);
?>
<SCRIPT type="text/javascript">
<!--
function setParentField(id) {
	parent.window.opener.document.forms["Vnos"].ForumTopicID.value = id;
	window.close()
}

function fixSize() {
	$("#divList").height( $(window).height() - $("#divList").position().top - 10 ).width( $(window).width() - 10 );
}

$(document).ready(fixSize);
$(window).resize(fixSize);
//-->
</SCRIPT>

<DIV ALIGN="center" CLASS="subtitle"><B>Select topic</B></DIV>
<div id="divFind" class="find">
<form name="ListFind" action="<?php echo $FindURL ?>" method="get">
<input type="Hidden" name="ID" value="<?php echo $_GET['ID']; ?>">
<input type="Hidden" name="Izbor" value="<?php echo $_GET['Izbor']; ?>">
<input type="Text" name="Find" id="inpFind" maxlength="32" value="<?php echo (isset($_GET['Find'])? $_GET['Find']: "") ?>" onkeypress="$('#clrFind').show();">
<a id="clrFind" href="javascript:void(0);" onclick="$(this).hide();$('#inpFind').val('').select();"><img src="pic/list.clear.gif" border="0"></a>
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
		if ( $BgCol == "white")
			$BgCol = "lightgrey";
		else
			$BgCol = "white";
		$Title = "$Item->ForumName : $Item->TopicName";
		echo "<TR BGCOLOR=\"$BgCol\">\n";
		echo "<TD><A HREF=\"#\" ONCLICK=\"setParentField('$Item->ID')\">".left($Title,65).(strlen($Title)>65? "...": "")."</A></TD>\n";
		echo "</TR>\n";
	}
	?>
<?php endif ?>
	</TABLE>
</DIV>
