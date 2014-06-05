<?php
/*~ css.php - CSS generation framework
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
// include application variables and settings framework
require_once(dirname(__FILE__) ."/_application.php");

// get menu, content & extra structure
$menu  = false;
$extra = false;

// get primary sidebar (navigation) structure
if ( !$Mobile && stripos($_SERVER['QUERY_STRING'], "nomenu") === false ) {
	$Kategorija = $_GET['kat'];
	do { // loop over category hierarchy, if not defined for current category
		$TemplateMenu = $db->get_var(
			"SELECT
				count(*)
			FROM
				KategorijeVsebina KV
				LEFT JOIN Predloge P ON KV.PredlogaID = P.PredlogaID
			WHERE
				KV.KategorijaID = '". $db->escape($Kategorija) ."'
				AND KV.Ekstra = 2
				AND P.Enabled <> 0
				AND (P.Jezik='$lang' OR P.Jezik IS NULL)"
			);
		$Kategorija = left($Kategorija, strlen($Kategorija)-2);
	} while ( $TemplateMenu == 0 && strlen($Kategorija) >= 2 );
	$menu = (bool)$TemplateMenu;
}

// get secondary sidebar (extra) structure
if ( !$Mobile && stripos($_SERVER['QUERY_STRING'], "noextra") === false ) {
	$Kategorija = $_GET['kat'];
	do { // loop over category hierarchy, if not defined for current category
		$TemplateExtra = $db->get_var(
			"SELECT
				count(*)
			FROM
				KategorijeVsebina KV
				LEFT JOIN Predloge P ON KV.PredlogaID = P.PredlogaID
			WHERE
				KV.KategorijaID = '". $db->escape($Kategorija) ."'
				AND KV.Ekstra=1
				AND P.Enabled <> 0
				AND (P.Jezik='$lang' OR P.Jezik IS NULL)"
			);
		$Kategorija = left($Kategorija, strlen($Kategorija)-2);
	} while ( $TemplateExtra == 0 && strlen($Kategorija) >= 2 );
	$extra = (bool)$TemplateExtra;
}

// category title & description
$Kat = $db->get_row(
	"SELECT
		K.KategorijaID,
		K.Ime,
		KN.Naziv,
		KN.Opis,
		KN.Povzetek
	FROM
		Kategorije K
		LEFT JOIN KategorijeNazivi KN
			ON K.KategorijaID = KN.KategorijaID
	WHERE
		K.KategorijaID = '". $db->escape($_GET['kat']) ."'
		AND (KN.Jezik = '$lang' OR KN.Jezik IS NULL)
	ORDER BY
		KN.Jezik DESC
	LIMIT 1"
	);
// get kategory text for permalinks
$KatText     = $Kat->Ime;
$KatFullText = $Kat->Naziv;

// define $PageWidth (for CSS and other calculations)
if ( $KatText == "diskusije" || $KatText == "forum" ) {
	$ContentW = $ContentW + $MenuW;
	$MenuW    = $ExtraW = 0;
}
$PageWidth = $ContentW + ($menu ? $MenuW : 0) + ($extra ? $ExtraW : 0);

// set correct content-type
header('Content-type: text/css');

// EU cookies compliancy
if ( !isset($_COOKIE['accept_cookies']) && (defined('ANALYTICS_ID') || is_file(dirname(__FILE__) ."/diskusije/index.php")) ) {
?>
#cookies {
	background-color:#888;
	color:#fff;
	font-size:75%;
	text-align:center;
	padding:10px 20px;
}
#cookies .cont {
	width:<?php echo $PageWidth ?>px;
	margin:auto;
	text-align:left;
}
#cookies .text {
	float:left;
	padding:0;
	text-shadow:none;
}
#cookies .buttons {float:right;}
#cookies a {color:#333;font-weight:bold;}
#cookies .clr {clear:both;}
#cookies input {
	background:#fff;
	color:#000;
	border:0;
	font-size:11px;
	height:20px;
	line-height:20px;
	padding:0 10px;
	font-family:Helvetica, sans-serif;
}
#cookies input.no {color:#888;}
#cookies input:hover {background:#cee9ad;}
#cookies input.no:hover {background:#f00;color:#fff}
@media screen and (max-width: 479px) {
	#cookies .cont { width:320px; }
}
@media screen and (min-width: 480px) and (max-width: <?php echo $PageWidth-1; ?>px) {
	#cookies .cont { width:480px; }
}
<?php
}
?>
/*---------
/ general layout
/--------*/

HTML, * {
	margin: 0;
	padding: 0;
}

A, A:Visited, A:Active, A:Hover {
	color: <?php echo $TextColor; ?>;
	text-decoration: none;
}

BODY, TD {
	font-family: 'Trebuchet MS',Verdana,Helvetica,Arial,sans-serif;
	color: <?php echo $TextColor; ?>;
}

BODY {
	background: <?php echo $PageColor; ?> url('<?php echo $WebPath; ?>/pic/page_bg.gif') scroll top left !important;
	font-size: 11pt;
}

H1, H2, H3 {
	font-weight: bold;
	text-align: left;
	margin-bottom: .25em;
}

H1 {
	font-size: 150%;
}

H2 {
	font-size: 130%;
	margin-top: .5em;
}

H3 {
	font-size: 110%;
	margin-top: .5em;
	border-bottom: none;
}

IMG { height: auto; width: auto; }

INPUT, TEXTAREA, SELECT {
	color: #000;
}

#body {
	position: relative;
	background-color: <?php echo $BackgColor; ?>;
	box-shadow: 0px 0px 15px #000000;
	margin: <?php echo ($PageAlign)? "0 auto": "0"; ?>;
	max-width: <?php echo ($Mobile)? $ContentW: $PageWidth; ?>px;
	width: <?php echo ($Mobile)? $ContentW: $PageWidth; ?>px;
	-moz-box-shadow: 0px 0px 15px #000000;
	-webkit-box-shadow: 0px 0px 15px #000000;
}

#head { background-color: <?php echo $BckLoColor; ?>; }

#head, #foot {
	clear: both;
	position: relative;
}

#head:after, #foot:after { clear: both; content: "."; display: block; height: 0; visibility: hidden; }

#foot {
	background-color: <?php echo $BckHiColor; ?>;
	border-top: 1px solid <?php echo $FrameColor; ?>;
	padding: 10px 0 0 0;
	margin-top: 5px;
}

#content, #navigation, #extras {
	background-color: <?php echo $BackgColor; ?>;
}

#content {
	float: right;
	width: <?php echo $ContentW; ?>px;
	max-width: <?php echo $ContentW; ?>px;
	padding: 10px 0;
}

#navigation BLOCKQUOTE, #content P, #content BLOCKQUOTE {
	margin: .5em 0;
}

#navigation BLOCKQUOTE, #content BLOCKQUOTE {
	margin-left: 1em;
	text-align: left;
}

#navigation {
	float: left;
	font-size: 80%;
	padding: 10px;
	max-width: <?php echo $MenuW-20; /* width - 2*margin */?>px;
	width: <?php echo $MenuW-20; /* width - 2*margin */?>px;
}

#foot A, #navigation A, #navigation A:Visited, #navigation A:Active {
	color: <?php echo $LinkColor; ?>;
	text-decoration: none;
}

#foot A:Hover, #navigation A:Hover {
	text-decoration: underline;
}

#navigation UL, #navigation OL {
	margin-left: 0;
}

#extras {
	float: left;
	margin: 0 5px;
	max-width: <?php echo $ExtraW-10; /* width - 2*margin */?>px;
	width: <?php echo $ExtraW-10; /* width - 2*margin */?>px;
}

/*-- custom items --*/

/*---------
/ head & foot content layout
/--------*/

.warn {
	background-color: #ff3300;
	color: white;
	font-size: 85%;
	padding: 2px;
	text-align: center;
}

.warn A:Hover {
	text-decoration: underline;
}

.maintitle {
	background: url('<?php echo $WebPath; ?>/pic/title_bg.png') repeat left top;
	min-height: 80px;
	position: relative;
	text-shadow: 0px 2px 5px rgba(0,0,0,.75);
}

.maintitle H1 {
	font-size: 300%;
	letter-spacing: -1px;
	text-align: center;
	margin: 0 128px;
}

.maintitle H2 {
	font-size: 85%;
	font-style: italic;
	margin: 0 128px;
	text-align:center;
}

.maintitle UL, #foot UL {
	border: 0;
}

.maintitle LI, #foot LI {
	display: inline;
	list-style-type: none;
}

.mainmenu {
	background-color: <?php echo $BckHiColor; ?>;
	border-bottom: 1px solid <?php echo $FrameColor; ?>;
	border-top: 1px solid <?php echo $FrameColor; ?>;
	clear: both;
	font-size: 120%;
	text-align: center;
	line-height: 1.5em;
	padding: 3px 0px;
}

.mainmenu UL {
	border: 0;
}

.mainmenu LI, .footmenu LI {
	display: inline;
	list-style-type: none;
	margin: 0;
}

.mainmenu LI:before, .footmenu LI:before {
	content: "| ";
}

.mainmenu LI:first-child:before, .footmenu LI:first-child:before {
	content: "";
}

.mainmenu A, .mainmenu A:hover {
	color: <?php echo $TxtExColor; ?>;
	text-decoration: none;
}

.footmenu { margin: 0 10px; min-height: 16px; }

.copyright {
	border-top: 1px <?php echo $FrameColor; ?> dashed;
	font-size: 75%;
	text-align: left;
	padding-top: 5px;
	margin: 5px 10px 10px 10px;
	min-height: 24px;
}

.copyright A {
	color: <?php echo $LinkColor; ?>;
	clear: both;
}

#logo {
	position: absolute;
	top: 0px;
	right: -64px;
	width: 128px;
	height: 128px;
	z-index: 100;
}

#foot .lang { position:absolute; top:0; right:0; text-align:right; margin:5px 10px 0 0; }
#foot .soci { position:absolute; bottom:0; right:0; text-align:right; margin:0 10px 10px 0; }

/*-- custom head & foot items --*/

/*---------
/ blog & text layout
/--------*/

.post {
	clear: both;
	position: relative;
	padding: 0 5px 5px 5px;
	margin-bottom: 10px;
	text-shadow: 1px 1px 2px rgba(0,0,0,.5);
}
.post:first-child { border: none; padding-top: 0; margin-top: 0; }
.post:after { clear: both; content: "."; display: block; height: 0; visibility: hidden; }

.post H1, .post H2, .post H3 {
	color: <?php echo $TxtExColor; ?>;
	border-bottom: none;
	padding-left: 0;
	margin-top: 5px;
}

.post OL, .post UL, .text OL, .text UL {
	margin: .5em .5em .5em 2em;
	padding-left: 1em;
}

.post H1 { margin-top: 0px; }
.post TD { padding: 3px; }
.post IMG, .text IMG { max-width: 100%; }

.post .title, .post .body {
	padding: 0 5px;
}

.post .abstract {
	margin: 5px 0;
	min-height: 32px;
}

.post .body {
	clear: both;
	margin: 10px 0;
}

.post A, .text .body A {
	color: <?php echo $LinkColor; ?>;
}

.post .author, .post .date, .post .tags, .comment .date {
	font-size: 75%;
}

.post .date { display: none; }

.text {
	clear: both;
	position: relative;
	padding: 0 10px;
	text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

.text H1 {
	color: <?php echo $TxtExColor; ?>;
	text-align: center;
	margin-left: 15%;
	margin-right: 15%;
}

.text H2 {
	color: <?php echo $TxtExColor; ?>;
	text-align: center;
}

.text TD {
	padding: 3px;
}

.post A.postlink, .text A.postlink, .related .body A {
	background: url('<?php echo $WebPath; ?>/pic/more.png') no-repeat right center;
	display: block;
	padding: 5px 32px 5px 0px;
}

.post A.postlink, .text A.postlink {
	color: <?php echo $TextColor; ?>;
}

.related .body A {
	color: <?php echo $LinkColor; ?>;
}

.related, .comment {
	background-color: <?php echo $BckHiColor; ?>;
	border: 1px solid <?php echo $FrameColor; ?>;
	clear: both;
	padding: 0;
	margin: 10px;
	border-radius: 5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	/* IE 6-9 */
	behavior: url(PIE.php);
}

.related .head, .comment .head  {
	color: <?php echo $TextColor; ?>;
	font-weight: bold;
	padding: 10px;
}

.related .body, .comment .body {
	border-top: 1px solid <?php echo $FrameColor; ?>;
	min-height: 22px;
	padding: 5px 10px;
}

.comment FORM {
	margin: 10px;
}

.comment INPUT, .comment TEXTAREA {
	border: 1px solid #ADADAD; /*NOTE*/
	border-radius: 8px;
	padding: 3px;
	-moz-border-radius: 8px;
	-webkit-border-radius: 8px;
	/* IE 6-9 */
	behavior: url(PIE.php);
}

.comment INPUT {
	margin-right: 10px;
	width: 170px;
}

.comment TEXTAREA {
	width: 98%;
}

.calendar {
	background-color: <?php echo $BckLoColor; ?>;
	border-radius: 5px;
	font-family: helvetica, arial;
	font-size: 160%;
	line-height: 75%;
	padding-bottom: .3em;
	position: absolute;
	text-align: center;
	top: 10px;
	width: 48px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	/* IE 6-9 */
	behavior: url(PIE.php);
}

.calendar .month {
	background-color: <?php echo $BckHiColor; ?>;
	border-radius: 5px 5px 0 0;
	color: <?php echo $TextColor; ?>;
	font-size: 60%;
	font-weight: bold;
	height: 1.3em;
	-moz-border-radius: 5px 5px 0 0;
	-webkit-border-radius: 5px 5px 0 0;
	/* IE 6-9 */
	behavior: url(PIE.php);
}

.calendar .day {
	font-weight: bold;
	font-size: 85%;
	margin: 0.2em 0;
}

.calendar .year {
	font-size: 40%;
	line-height: 0.8em;
}

.calendar .bubble {
	background-color: #a40717;
	background: -webkit-gradient(linear, left top, left bottom, from(#de939e), to(#a40717), color-stop(0.5, #be4958));
	background: -moz-linear-gradient(top, #de939e, #be4958 50%, #a40717);
	background: linear-gradient(to bottom, #de939e, #be4958 50%, #a40717);
	border-style: solid;
	border-width: 2px;
	border-radius: 32px;
	color: #fff;
	display: block;
	font: bold 12px "Helvetica Neue", Helvetica, Geneva, Arial, sans-serif;
	height: 16px;
	padding: 0 5px;
	position: absolute;
	text-shadow: rgba(0,0,0,0.5) 1px 1px 2px;
	text-align: center;
	top: -8px;
	left: 32px;
	width: auto;
	-moz-border-radius: 32px;
	-webkit-background-clip: padding-box;
	-webkit-border-radius: 32px;
	z-index: 1;
	/* IE 6-9 */
	behavior: url(PIE.php);
}

.caloffset { margin-left: 56px; }

/*-- custom blog & text items --*/


/*---------
/ forum layout
/--------*/
#content.forum {
	padding: 5px 0;
}

.forum A:Hover {
	text-decoration: underline;
}

.frmmenu {
	background-color: <?php echo $BckLoColor; ?>;
	margin: 5px;
}

.frmnav {
	background-color: <?php echo $BackgColor; ?>;
}

.frmthread {
	background-color: <?php echo $FrameColor; ?>;
	color: <?php echo $TxtFrColor; ?>;
	padding: 3px;
	text-align: center;
}

.frmtopic {
	background-color: <?php echo $BckLoColor; ?>;
	color: <?php echo $TxtFrColor; ?>;
	padding: 3px;
}

.frmnew {
	background-color: <?php echo $BckHiColor; ?>;
	border: 1px solid <?php echo $FrameColor; ?>;
	margin: 10px 5px;
	padding: 2px;
	text-align: center;
}

.frmpost {
	background-color: <?php echo $BackgColor; ?>;
	border: 1px solid <?php echo $FrameColor; ?>;
	margin: 5px;
}

.frmpost .title {
	background-color: <?php echo $FrameColor; ?>;
	color: <?php echo $TxtFrColor; ?>;
}

.frmpost .list {
	border: 0px none;
	margin: 0;
}

.frmpost .list TD {
	padding: 1px 2px;
}

.frmpost .head {
	background-color: <?php echo $BckHiColor; ?>;
	padding: 5px;
}

.frmpost .foot {
	background-color: <?php echo $BckLoColor; ?>;
	padding: 3px;
	text-align: center;
	font-size: 85%;
}

.frmpost IMG {
	max-width: <?php echo $ContentW-110; ?>px;
	height: auto;
}

.frmfoot {
	background-color: <?php echo $BckLoColor; ?>;
	margin: 5px;
	font-size: 85%;
}

.frmmenu TABLE,
.frmfoot TABLE {
	border: 0px none;
	padding: 1px 3px;
}

.avatar {
	font-size: 75%;
}

.avatar IMG {
	margin: 3px 1px;
	max-width: 64px;
}

/*---------
/ grid & list layout
/--------*/

.grid { font-size: 80%; }
.grid .title H1, .grid .title H2 {
	white-space: nowrap;
	text-overflow: ellipsis;
	overflow: hidden;
	text-align: center;
}
.grid UL {
	text-align: center;
	margin:0;
}
.grid LI {
	list-style: none;
	list-style-type: none;
	display: inline-block;
	margin: 0;
	padding: 5px 2px;
	text-align: center;
	vertical-align: top;
	min-width:<?php echo (int)(($ContentW/5)-8); ?>px;
	max-width:<?php echo (int)(($ContentW/2)-8); ?>px;
}
.grid LI A { color: <?php echo $LinkColor; ?>; position: relative;}
.grid LI A .abstract, .grid LI A .subtitle { color: <?php echo $TextColor; ?>; }
.grid A .title { color: <?php echo $TxtExColor; ?>; }
.grid IMG.round {
	border-radius: 64px;
	max-width: 128px;
	max-height: 128px;
	transition-property: all;
	transition-duration: 0.5s;
	transition-timing-function: ease;
	transition-delay: .2s;
	/* Safari */
	-webkit-transition-property: all;
	-webkit-transition-duration: 0.5s;
	-webkit-transition-timing-function: ease;
	-webkit-transition-delay: .2s;
	/* IE 6-9 */
	behavior: url(PIE.php);
}
.grid IMG.round:Hover { border-radius: 0; }

.g2 { width:<?php echo (int)(($ContentW/2)-8); ?>px; }
.g3 { width:<?php echo (int)(($ContentW/3)-8); ?>px; }
.g4 { width:<?php echo (int)(($ContentW/4)-8); ?>px; }
.g5 { width:<?php echo (int)(($ContentW/5)-8); ?>px; }
.g2 IMG { max-width:<?php echo (int)(($ContentW/2)-18); ?>px; max-height:<?php echo (int)(($ContentW/2)-18); ?>px; }
.g3 IMG { max-width:<?php echo (int)(($ContentW/3)-18); ?>px; max-height:<?php echo (int)(($ContentW/3)-18); ?>px; }
.g4 IMG { max-width:<?php echo (int)(($ContentW/4)-18); ?>px; max-height:<?php echo (int)(($ContentW/4)-18); ?>px; }
.g5 IMG { max-width:<?php echo (int)(($ContentW/5)-18); ?>px; max-height:<?php echo (int)(($ContentW/5)-18); ?>px; }

.list {
	border-top: <?php echo $FrameColor; ?> dashed 1px;
	position: relative;
	padding-top: 5px;
	padding-bottom: 5px;
	margin-top: 10px;
}

.list:first-child {
	border-top: none;
	padding-top: 0;
	margin-top: 0;
}

.listitem { clear: both; }

.fence {
	border-top: <?php echo $FrameColor; ?> dashed 1px;
	border-bottom: <?php echo $FrameColor; ?> dashed 1px;
	margin: 10px 0;
	padding: 10px 0;
}

/*---------
/ gallery & image layout
/--------*/

.gallery {
	clear: both;
	text-align: center;
}
.gallery:after { clear: both; content: "."; display: block; height: 0; visibility: hidden; }

.gallery UL  {
	text-align: center;
	margin: 0;
}

.gallery LI {
	list-style: none;
	list-style-type: none;
	display: inline-block;
	margin: 0;
	text-align: center;
	vertical-align: top;
	min-width:<?php echo (int)(($ContentW/5)-8); ?>px;
	max-width:<?php echo (int)(($ContentW/3)-8); ?>px;
}
.gallery LI IMG { margin: 4px; }
.gallery LI A { color: <?php echo $LinkColor; ?>; }

.imgcenter { margin:5px 0; }
.imgleft { margin:0 7px 5px 0; }
.imgright { margin:0 0 5px 7px; }

.post IMG.imgcenter, .post IMG.imgleft, .post IMG.imgright,
.text IMG.imgcenter, .text IMG.imgleft, .text IMG.imgright {
	max-width: 96%;
	height: auto;
}

.thumb {
	border: none;
	margin: 0;
	padding: 0;
	display: inline-block;
	max-width: 128px;
	max-height: 128px;
}

.post IMG.thumb, .text IMG.thumb {
	margin: 0 0 5px 10px;
	float: right;
	max-width: 96px;
	max-height: 96px;
}

.frame, .slide, .imgcenter, .imgleft, .imgright {
	border: 1px solid <?php echo $FrameColor; ?>;
	background: <?php echo $BckHiColor; ?>;
	padding: 4px;
}

.magnifyingglass {
	border: 0;
	position: absolute;
	right: 5px;
	bottom: 14px;
	width: 32px;
	height: 32px;
}

.badge, .icon, .icon24, .smiley, .symbol {
	border: none;
	height: auto;
}
.badge { width: 26px; }
.icon24 { width: 24px; }
.icon { width: 16px; }
.smiley { width: 15px; }
.symbol { width: 13px; vertical-align: middle; }

/*---------
/ search
/--------*/

.find {
	background: <?php echo $BackgColor; ?> url('<?php echo $WebPath; ?>/pic/find.png') 3px 50% no-repeat;
	border: solid <?php echo $FrameColor; ?> 1px;
	border-radius: 15px;
	cursor: text;
	margin: 7px 10px;
	padding: 1px 25px;
	position: relative;
	-moz-border-radius: 15px;
	-webkit-border-radius: 15px;
	/* IE 6-9 */
	behavior: url(PIE.php);
}
#head .find {
	margin: 0;
	width: 4px;
	z-index: 99;
}
#foot .find {
	display: none;
}

.find INPUT {
	background-color: <?php echo $BackgColor; ?>;
	color: <?php echo $TextColor; ?>;
	border: 0;
	font-size: 17px;
	width: 100%;
}

.find A {
	z-index: 1;
	display: none;
}

.find A IMG {
	position: absolute;
	right: 3px;
	top: 3px;
	width: 16px;
}

.searchresult {
	background-color: <?php echo $BckHiColor; ?>;
	border-radius: 5px;
	border: 1px solid <?php echo $FrameColor; ?>;
	margin: 10px 0;
	padding: 10px;
	position: relative;
	text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	/* IE 6-9 */
	behavior: url(PIE.php);
}

.searchresult H3 {
	color: <?php echo $TxtExColor; ?>;
	border-bottom: none;
	padding: 0;
	margin-top: 0;
}

.searchresult A {
	background: url('<?php echo $WebPath; ?>/pic/more.png') no-repeat right center;
	color: <?php echo $TextColor; ?>;
	display: block;
	min-height: 32px;
	padding-right: 32px;
}

.searchresult .abstract {
	margin: 5px 0;
	padding: 0;
}

/*-- custom search items --*/


/*---------
/ navigation layout
/--------*/

.menu {
	margin-bottom: 10px;
}

.menu LI {
	list-style-type: none;
	border: 1px solid <?php echo $FrameColor; ?>;
	background-color: <?php echo $BackgColor; ?>;
	padding: 5px 2px;
	margin-bottom: 5px;
}

.submenu LI {
	margin-left: 15px;
}

.menu .title {
	color: <?php echo $TextColor; ?>;
	border-bottom:1px solid <?php echo $FrameColor; ?>;
	background-color: <?php echo $BckHiColor; ?>;
	margin-bottom: 4px;
	padding: 1px;
}

.menu LI, .menu .title, .menu .tags {
	text-align: center;
}

.navbutton {
	padding: 5px;
}

.navbutton A {
	background-color: <?php echo $BckLoColor; ?>;
	border: 1px solid <?php echo $FrameColor; ?>;
	border-radius: 5px;
	clear: both;
	color: <?php echo $TextColor; ?>;
	display: inline-block;
	min-width: 22px;
	padding: 5px;
	font-size: 110%;
	text-align: center;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	/* IE 6-9 */
	behavior: url(PIE.php);
}

.pages {
	margin-bottom: 10px;
}

.navbutton A:hover {
	background-color: <?php echo $BckHiColor; ?>;
}

.citat, .tweet {
	border: 1px solid <?php echo $FrameColor; ?>;
	padding: 10px 5px;
	font-family: georgia, times, serif;
	font-style: italic;
	text-align: center;
	vertical-align: middle;
}

.citat {
	background: <?php echo $BckLoColor; ?> url('<?php echo $WebPath; ?>/pic/quote.png') left top no-repeat scroll;
	min-height: 32px;
}

.tweet {
	background: <?php echo $BckLoColor; ?> url('<?php echo $WebPath; ?>/pic/twitter_bg.png') right top no-repeat scroll;
	min-height: 64px;
	padding: 0 5px;
	position: relative;
	overflow-x: scroll;
}

/*-- custom navigation items --*/
.tweet li { list-style-type: none; padding: 1px; }
.tweet li SPAN A { display: inline; }
.tweet li A { display: block; }

/*---------
/ other layout
/--------*/

.a9 { font-size: 75%; }
.a10 { font-size: 85%; }

#content .quote {
	background: <?php echo $BckLoColor; ?> url('<?php echo $WebPath; ?>/pic/quote.png') no-repeat scroll left top;
	border: 1px <?php echo $FrameColor; ?> solid;
	margin: .5em 2em;
	padding: .75em;
	color: <?php echo $TextColor; ?>;
	font-family: georgia, times, serif;
	font-style: italic;
	font-size: 100%;
	line-height: 130%;
}

#content .code {
	background: <?php echo $BckLoColor; ?>;
	border-left: 3px <?php echo $FrameColor; ?> solid;
	border-top: 1px <?php echo $FrameColor; ?> dashed;
	border-bottom: 1px <?php echo $FrameColor; ?> dashed;
	display: block;
	margin: .5em 1em;
	padding: .5em;
	color: <?php echo $TextColor; ?>;
	font-family: courier, monospace;
	overflow: auto;
}

.poll P { margin: 5px 0; }
.poll TD { padding: 0 2px; }
.poll LI { border: 1px <?php echo $FrameColor; ?> solid; background: <?php echo $BckLoColor; ?>; }

.social { padding: 5px 10px; }

.grayscale { font-size: 80%; }
.grayscale IMG {
	display: block;
	text-align: center;
	margin: 5px auto;
}

.googlemaps {
	width: 320px;
	height: 320px;
}
.googlemapswide {
	clear: both;
	width: 600px;
	height: 320px;
	margin: 5px 0;
}
.youtube {
	max-width: 560px;
}

.shadow, .slide, .related, .comment, .calendar .bubble {
	box-shadow: rgba(0,0,0,.7) 1px 1px 3px;
	-webkit-box-shadow: rgba(0,0,0,.7) 1px 1px 3px;
	-moz-box-shadow: rgba(0,0,0,.7) 1px 1px 3px;
}

/*---------
/ mobile devices
/--------*/

@media screen and (max-width: 479px) {
	body { font-size: 10pt; }
	.maintitle { min-height: 64px; }
	.maintitle H1, .maintitle H2 {
		margin: 0 5px;
		padding: 0;
		text-align: left;
	}
	.maintitle H1 { font-size: 250%; }
	.mainmenu { letter-spacing: -1px; }
	.post .date { display: inherit; }
	.post IMG.thumb, .text IMG.thumb { max-width:64px; max-height:64px; }
	.calendar { display: none; }
	.caloffset { margin-left: 0px; }
	.gallery LI { min-width: 0; }
	.gallery LI IMG {
		max-width: 56px;
		max-height: 56px;
		margin: 0;
	}
	.googlemaps {
		width: 140px;
		height: 140px;
	}
	.googlemapswide {
		width: 240px;
		height: 140px;
	}
	.youtube { max-width: 300px; }
	#navigation, #extras, #logo, #head .find, .maintitle UL, .pages, .grayscale { display: none; }
	#foot .copyright { text-align: center; }
	#foot .find { display: block; }
	#foot .lang { position:relative; text-align: center; margin:0 5px 0; }
	#foot .lang LI { margin:3px; }
	#foot .soci, #foot .footmenu, #foot .links { display: none; }
	#body, #content {
		width: 320px;
		max-width: 320px;
		min-width: 320px;
	}
}
 
@media screen and (min-width: 480px) and (max-width: <?php echo $ContentW-1; ?>px) {
	body { font-size: 10pt; }
	.maintitle { min-height: 64px; }
	.maintitle H1, .maintitle H2 {
		margin: 0 5px;
		padding: 0;
		text-align:left;
	}
	.gallery LI {
		min-width: 0;
		max-width: 224px;
	}
	.gallery LI IMG {
		max-width: 88px;
		max-height: 88px;
		margin: 0;
	}
	.googlemaps {
		width: 200px;
		height: 200px;
	}
	.googlemapswide {
		width: 400px;
		height: 200px;
	}
	.youtube { max-width: 440px; }
	#navigation, #extras, #logo, #head .find, .maintitle UL, .grayscale { display: none; }
	#foot .copyright { text-align: center; }
	#foot .find { display: block; }
	#foot .lang { position:relative; text-align: center; margin:0 5px 0; }
	#foot .lang LI { margin:3px; }
	#foot .soci, #foot .footmenu, #foot .links { display: none; }
	#body, #content {
		width: 480px;
		max-width: 480px;
		min-width: 480px;
	}
}

@media screen and (min-width: <?php echo $ContentW; ?>px) and (max-width: <?php echo $PageWidth-1; ?>px) {
	#navigation, #extras, #logo, .maintitle UL, .grayscale { display: none; }
	#body, #content {
		width: <?php echo $ContentW; ?>px;
		max-width: <?php echo $ContentW; ?>px;
		min-width: <?php echo $ContentW; ?>px;
	}
}

/* display logo only if not clipped */
@media screen and (min-width: <?php echo $PageWidth+180; ?>px) {
	#logo { display: inherit; }
}
@media screen and (max-width: <?php echo $PageWidth+128; ?>px) {
	#logo { display: none; }
}

/*@media screen and (orientation:portrait) {}*/
/*@media screen and (orientation:landscape) {}*/

/* "retina" display */
@media only screen and (-webkit-min-device-pixel-ratio: 2),
	   only screen and (-moz-min-device-pixel-ratio: 2),
	   only screen and (-o-min-device-pixel-ratio: 2/1),
	   only screen and (min-device-pixel-ratio: 2),
	   only screen and (min-resolution: 2dppx) {
	body { background-size: 128px 128px; }
	.maintitle {
		background: <?php echo $BckLoColor; ?> url('<?php echo $WebPath; ?>/pic/title_bg@2x.png') no-repeat right top;
		background-size: 128px 64px;
	}
	.find {
		background: <?php echo $BackgColor; ?> url('<?php echo $WebPath; ?>/pic/find@2x.png') 5px 50% no-repeat;
		background-size: 16px 16px;
	}
	.find IMG { width: 16px; }
	.post A.postlink, .text A.postlink, .related .body A, .searchresult A {
		background: url('<?php echo $WebPath; ?>/pic/more@2x.png') no-repeat right center;
		background-size: 22px 22px;
	}
}
