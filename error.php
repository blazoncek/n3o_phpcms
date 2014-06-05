<?php
/*~ error.php - error page
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
| This file is part of N3O CMS (frontend and backend).                      |
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
// base configuration, no dependencies
require_once(dirname(__FILE__) .'/_config.php');
?>
<!DOCTYPE html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<title>Internal error! | <?php echo $_SERVER['SERVER_NAME'] ?></title>
<style type="text/css" media="all">
HTML, BODY {
	margin:0; padding:0;
	height:100%;
}
BODY, TABLE, TD {
	font-family:'Verdana','Arial','Helvetica';
	font-size:16px;
	color: #ffde00;
	background: #000;
}
H1 {
	color: #FFde00;
	font-style: italic;
	font-size: 300%;
	text-shadow: 0px 2px 10px rgba(255,255,0,0.5);
	margin-bottom: 2em;
	margin-top: 0.2em;
}
IMG {
	border: 0;
}
A {
	color: #FFde00;
	text-decoration: none;
}
A:visited, A:hover {
	color: #FFF;
}
#top {
	position:absolute;
	top:0; left:0;
}
#bottom {
	position:absolute;
	bottom:0; right:0;
}
#float {
	/* float: left;*/
	height: 50%;
	margin-bottom: -180px;
	/* position: relative;*/
}
#center {
	background: #333 url('./pic/work.png') no-repeat scroll 0 33% !important;
	border: 1px solid #ffde00;
	border-radius: 10px;
	clear: left;
	height: 360px;
	margin: 0 auto;
	overflow: hidden;
	position: relative;
	text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
	width: 640px;
	-webkit-box-shadow: rgba(0,0,0, .5) 3px 3px 5px;
	-moz-border-radius: 10px;
	-moz-box-shadow: rgba(0,0,0, .5) 3px 3px 5px;
}
#content {
	padding: 10px;
	text-align: center;
	overflow:auto;
}
</style>
</head>

<body>
<div id="float"></div>
<div id="center">
<div id="content">
<h1>Napaka/Error</h1>
<p>Prišlo je do napake. Opravičujemo se za nevšečnost.</p>
<p>Internal server error. We apologize for the inconvenience.</p>
<p style="font-size:12px;margin-top:6em;"><a href="mailto:<?php echo PostMaster ?>">Webmaster</a></p>
</div>
</div>
</div>
<div id="bottom"></div>
<?php
if ( defined('ANALYTICS_ID') ) {
	// google analytics
	echo "<script type=\"text/javascript\">\n";
	echo "var gaJsHost = ((\"https:\" == document.location.protocol) ? \"https://ssl.\" : \"http://www.\");\n";
	echo "document.write(unescape(\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));\n";
	echo "</script>\n";
	echo "<script type=\"text/javascript\">\n";
	echo "try {\n";
	echo "var pageTracker = _gat._getTracker(\"". ANALYTICS_ID ."\");\n";
	echo "pageTracker._trackPageview();\n";
	echo "} catch(err) {}</script>\n";
}
?>
</body>
</html>