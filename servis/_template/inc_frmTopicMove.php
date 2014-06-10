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
?>
<script language="JavaScript" type="text/javascript">
<!-- //
$(document).ready(function(){
	// bind to the form's submit event
	$("form[name='Forum']").each(function(){
		$(this).submit(function(){
			$(this).ajaxSubmit({
				target: '#divTopics',
				beforeSubmit: function( formDataArr, jqObj, options ) {
					var fObj = jqObj[0];	// form object
					return confirm('Ste prepričani?');
				} // pre-submit callback
			});
			return false;
		});
	});
});
//-->
</script>

<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" CLASS="title" WIDTH="100%">
<TR>
	<TD ALIGN="center"><B>Move topic</B></TD>
</TR>
</TABLE>
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR><TD COLSPAN="2" HEIGHT="10"></TD></TR>
<TR>
	<TD WIDTH="10"></TD>
	<TD ALIGN="center">
	<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="1" WIDTH="100%">
	<TR>
		<TD VALIGN="middle">
		<FORM NAME="Forum" ACTION="inc.php?Izbor=frmTopic&ForumID=<?php echo $_GET['ForumID'] ?>" METHOD="post">
		<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
		<TR>
			<TD ALIGN="right" NOWRAP><B>Into thread:</B></TD>
			<TD>
			<SELECT NAME="NitID" SIZE="1" STYLE="width:240px;">
<?php
		$getForums = $db->get_results(
			"SELECT f.ID, f.ForumName, c.CategoryName
			FROM frmForums f
				LEFT JOIN frmCategories c ON f.CategoryID = c.ID
			ORDER BY c.CategoryOrder, f.ForumOrder, f.ForumName"
		);
		if ( $getForums ) foreach ( $getForums as $getForum )
			echo "<OPTION VALUE=\"$getForum->ID\"".(($_GET['ForumID'] == $getForum->ID)? " SELECTED STYLE=\"background-color: #99CCFF;\"": "").">$getForum->CategoryName : $getForum->ForumName</OPTION>\n";
?>
			</SELECT>
			</TD>
		</TR>
		<TR>
			<TD ALIGN="right" COLSPAN="2"><INPUT TYPE="Hidden" NAME="TemaID" VALUE="<?php echo $_GET['ID'] ?>">
			<INPUT VALUE="Move" TYPE="Submit" CLASS="but">
			</TD>
		</TR>
		</TABLE>
		</FORM>

		</TD>
	</TR>
	</TABLE>
	</TD>
	<TD WIDTH="10"></TD>
</TR>
</TABLE>
