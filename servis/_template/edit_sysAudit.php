<?php
/*~ edit_Jeziki.php - Edit available language customizations.
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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "";

$Podatek = $db->get_row(
	"SELECT
		A.*,
		U.Name,
		U.UserID
	FROM SMAudit A
		LEFT JOIN SMUser U ON A.UserID=U.UserID
	WHERE A.ID=". (int)$_GET['ID']
	);
?>
<script language="JavaScript" type="text/javascript">
<!-- //
$(document).ready(function(){
	// refresh list
	listRefresh();
});
//-->
</script>

<FIELDSET ID="fldData" style="width:370px;">
<LEGEND ID="lgdData">Basic&nbsp;information</LEGEND>
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD ALIGN="right" WIDTH="25%">Timestamp:&nbsp;</TD>
	<TD NOWRAP><?php echo date("j.n.Y \@ H:i:s", sqldate2time($Podatek->DateOfEntry)) ?></TD>
</TR>
<TR>
	<TD ALIGN="right"><B>User:</B>&nbsp;</TD>
	<TD><A HREF="javascript:void(0);" ONCLICK="loadTo('Edit','edit.php?Izbor=sysUsers&ID=<?php echo $Podatek->UserID ?>');"><?php echo $Podatek->Name ?></A></TD>
</TR>
<TR>
	<TD ALIGN="right"><B>Object:</B>&nbsp;</TD>
	<TD><?php echo $Podatek->ObjectType ?></TD>
</TR>
<TR>
	<TD ALIGN="right"><B>Action:</B>&nbsp;</TD>
	<TD><?php echo $Podatek->Action ?></TD>
</TR>
<TR>
	<TD ALIGN="right" VALIGN="top"><B>Description:</B>&nbsp;</TD>
	<TD><?php echo str_replace(',','<br>',$Podatek->Description) ?></TD>
</TR>
</TABLE>
</FIELDSET>
