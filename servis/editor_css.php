<?php
/*~ editor_css.php - CSS generation framework
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

// include application variables and settings framework
require_once("../_application.php");

// set correct content-type
header('Content-type: text/css');

?>
body {background-color:<?php echo $BackgColor; ?>;}
body, td, pre {
	color:<?php echo $TextColor; ?>;
	font-family:Verdana,Arial,Helvetica,sans-serif;
	font-size:11pt;
}
a {color:<?php echo $LinkColor; ?>;}
pre {font-family:courier,monospace;font-size:12pt;}
blockquote {margin:.5em 2em;padding:0;}
p {margin:.5em 0;padding:0;}
ul {margin:.5em 0 .5em 2em;padding:0;}
H1, H2, H3 {font-weight:bold;text-align:left;margin-bottom: 0.25em;}
H1 {font-size:1.5em;}
H2 {font-size: 1.3em;margin-top: 0.5em;}
H3 {font-size: 1.1em;margin-top: 0.5em;}
.imgleft, .imgcenter, .imgright {
	background:<?php echo $BckHiColor; ?>;
	border:solid 1px <?php echo $FrameColor; ?>;
	padding:9px;
}
.imgcenter {margin:5px 0;}
.imgleft {margin:0 7px 5px 0;}
.imgright {margin:0 0 5px 7px;}
.smiley {border:none;padding:0;margin:0;}
.quote {
	background:<?php echo $BckLoColor; ?> url('../pic/quote.png') no-repeat scroll left top !important;
	border:1px <?php echo $FrameColor; ?> solid;
	color: <?php echo $TxtFrColor; ?>;
	font-family:georgia,times,serif;
	font-style:italic;
	padding:.75em;
	margin:.5em 2em;
	min-height:1.5em;
	text-align:center;
}
.code {
	background:<?php echo $BckLoColor; ?>;
	border-left:3px <?php echo $FrameColor; ?> solid;
	border-top:1px <?php echo $FrameColor; ?> dashed;
	border-bottom:1px <?php echo $FrameColor; ?> dashed;
	color: <?php echo $TxtFrColor; ?>;
	display:block;
	font-family:courier,monospace;
	margin:.5em 2em;
	padding:.5em;
	overflow: auto;
}
