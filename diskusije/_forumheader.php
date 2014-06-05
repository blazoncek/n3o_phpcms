<?php
/* _forumheader.php - forum header
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
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
var winLeft=320;
var winTop =240;
//-->
</SCRIPT>
<SCRIPT LANGUAGE="JAVASCRIPT1.2">
<!--
winLeft=screen.availWidth/2;
winTop =screen.availHeight/2;
//-->
</SCRIPT>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
var tmpWnd;
function windowOpen(url,wnd,w,h,menu) {
	if (!wnd) wnd="AkvazinWindow";
	if (!w) w=640;
	if (!h) h=480;
	if (!menu) menu='yes';
	tmpWnd = window.open(url,wnd,'width='+w+',height='+h+',resizable=1,scrollbars=1,toolbar=0,status=0,menubar='+menu+',location=0,left='+(winLeft-w/2)+',top='+(winTop-h/2));
}
function dialogOpen(url) {
	tmpWnd = window.open(url,'AkvazinDialog','width=580,height=515,resizable=1,scrollbars=1,toolbar=0,status=0,menubar=0,location=0,left='+(winLeft-285)+',top='+(winTop-240));
}
function loginOpen(url) {
	tmpWnd = window.open(url,'AkvazinLogin','width=420,height=310,resizable=0,scrollbars=1,toolbar=0,status=0,menubar=0,location=0,left='+(winLeft-210)+',top='+(winTop-155));
}
function chatOpen(url) {
	tmpWnd = window.open(url,'AkvazinChat','width=640,height=480,resizable=0,scrollbars=1,toolbar=0,status=0,menubar=0,location=0,left='+(winLeft-285)+',top='+(winTop-240));
}
function testCookie() {
	document.cookie = "test=1";
	var cookieEnabled = (document.cookie.search("test=1") != -1);
	document.cookie = "test=null";
	return cookieEnabled;
}
function jumpTo(url) {
	if (url.charAt(0)!="~")
		self.location.href=url;
}
//-->
</SCRIPT>
