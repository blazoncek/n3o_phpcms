<?php
/* _geslo.php - password request dialog
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
| This file is part of N3O CMS (frontend).                                  |
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
<!-- geslo -->
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR BGCOLOR="<?php echo $FrameColor ?>">
	<TD>
	<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="1" WIDTH="100%">
	<TR>
		<TD ALIGN="center" BGCOLOR="<?php echo $BackgColor ?>">
		<BR>
		<FORM ACTION="./?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post" ONSUBMIT="return this.Geslo.value!='';">
<?php if ( isset($_POST['Geslo']) ) : ?>
		<FONT COLOR="<?php echo $TxtExColor ?>"><B>Geslo ni pravilno!</B></FONT><BR>
<?php else : ?>
		Za ogled teh diskusij morate vpisati geslo!<BR>
<?php endif ?>
		<B>Geslo:</B>&nbsp;<INPUT NAME="Geslo" TYPE="Password" MAXLENGTH="16" SIZE="25" STYLE="border:<?php echo $FrameColor ?> solid 1px;">
		<INPUT TYPE="Submit" VALUE="Nadaljuj &gt;&gt;" CLASS="but">
		</FORM>
		<BR>
		</TD>
	</TR>
	</TABLE>
	</TD>
</TR>
</TABLE>
<BR>
