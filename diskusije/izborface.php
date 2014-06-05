<?php
/*~ vpispodatkov.php - sign in/update profile
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

// include application variables && settings framework
require_once( "../_application.php" );

include_once( "_queries.php" );

if ( !$_SESSION['MemberID'] && isset($_COOKIE['Email']) && isset($_COOKIE['Geslo']) ) {
	header( "Refresh:0; URL=login.php?login&reload&referer=". urlencode($_SERVER['PHP_SELF']) .($_SERVER['QUERY_STRING']!="" ? "&querystring=". urlencode($_SERVER['QUERY_STRING']) : "") );
	die();
}

if ( $_SESSION['MemberID'] && (isset($_FILES['Image']) && !$_FILES['Image']['error']) ) {
	// create directories
	$uploadpath = $StoreRoot ."/diskusije/px/face/custom";
	@mkdir($uploadpath, 0777, true);

	// upload & resize image
	$photo = ImageResize(
		'Image',     // $_FILE field
		$uploadpath, // upload path
		'',          // thumbnail prefix
		'',          // original image prefix
		64,          // reduced size
		0,  	     // thumbnail size
		$jpgPct,     // JPEG quality
		''.$_SESSION['MemberID'], // new image name
		"GIF");      // change format to GIF

	//if ( $photo ) { // successful upload & resize
	//	var_dump($photo);
	//}
}

if ( contains($_SERVER['QUERY_STRING'],"brisi") && is_file($StoreRoot ."/diskusije/px/face/custom/". $_SESSION['MemberID'] .".gif") )
	@unlink($StoreRoot ."/diskusije/px/face/custom/". $_SESSION['MemberID'] .".gif");

echo "<!DOCTYPE HTML>\n";
echo "<HTML>\n";
echo "<HEAD>\n";
$TitleText = $ForumTitle ." : Vpis osebnih podatkov";
include_once( "../_htmlheader.php" );
include_once( "_forumheader.php" );
?>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
window.focus();
function setCaller(img) {
	window.opener.document.images['Slika'].src='px/face/'+img+'.gif';
	window.opener.document.VpisPodatkov.Slika.value=img;
	window.close();
}
//-->
</SCRIPT>
<?php
echo "</HEAD>\n";
echo "<BODY style=\"background-color:". $PageColor .";\">\n";

$banned  = false;
// check for blacklisted IPs
$IPBanList = $db->get_col("SELECT IP FROM frmBanList WHERE IP IS NOT NULL");
if ( count($IPBanList) ) foreach ( $IPBanList AS $IP ) {
	if ( right($IP,1)=="*" ) {
		$banIP    = left($IP, strchr("*",$IP)-1);
		$clientIP = left($_SERVER['REMOTE_ADDR'],strlen($banIP));
	} else {
		$banIP = $IP;
		$clientIP = $_SERVER['REMOTE_ADDR'];
	}
	if ( !strcmp($clientIP,$banIP) ) {
		$banned = true;
		break;
	}
}
// is IP || user blacklisted?
if ( $banned ) {

	echo "<div class=\"text\">\n";
	echo "Nimate dovoljenja za ogled teh diskusij.\n";
	echo "</div>\n";

} else {

	echo "<DIV CLASS=\"grid\">\n";
	if ( $_SESSION['MemberID'] ) {
		echo "<FORM ACTION=\"". $_SERVER['PHP_SELF'] ."\" METHOD=\"post\" ENCTYPE=\"multipart/form-data\">\n";
		echo "<TABLE BORDER=\"0\" WIDTH=\"100%\">\n";
		echo "<TR>\n";
		echo "<TD ALIGN=\"right\" CLASS=\"a10\" VALIGN=\"middle\">Slika:&nbsp;<BR>(le GIF!)&nbsp;</TD>\n";
		echo "<TD CLASS=\"a10\" VALIGN=\"middle\">";
		echo "<INPUT TYPE=\"File\" NAME=\"Image\" STYLE=\"font-size:10px;border:". $FrameColor ." solid 1px;\" ONCHANGE=\"this.form.submit();\">";
		echo "</TD>\n";
		echo "</TR>\n";
		echo "</FORM>\n";
		echo "</TABLE>\n";
	}

	echo "<UL>\n";
	if ( is_file($StoreRoot ."/diskusije/px/face/custom/". $_SESSION['MemberID'] .".gif") ) {
		echo "<LI ONMOUSEOVER=\"this.style.backgroundColor='". $BackgColor ."';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">";
		echo "<A HREF=\"javascript:setCaller('custom/". $_SESSION['MemberID'] ."');\">";
		echo "<IMG SRC=\"px/face/custom/". $_SESSION['MemberID'] .".gif\" ALT=\"\" BORDER=0 HSPACE=0 VSPACE=0></A><BR>";
		echo "<A HREF=\"". $_SERVER['PHP_SELF'] ."?brisi\" CLASS=\"a10\">Briši</A>";
		echo "</LI>\n";
	}

	$files = scandir($StoreRoot ."/diskusije/px/face/");
	$RecordCount = count($files);
	if ( $RecordCount && sort($files) ) foreach ( $files as $file ) {
		// only display image files
		if ( is_file($StoreRoot ."/diskusije/px/face/". $file) && contains(".gif,.png,.jpg",right($file,4)) ) {
			echo "<LI ONMOUSEOVER=\"this.style.backgroundColor='". $BackgColor ."';\" ONMOUSEOUT=\"this.style.backgroundColor='';\">";
			echo "<A HREF=\"javascript:setCaller('". left($file,strlen($file)-4) ."');\">";
			echo "<IMG SRC=\"px/face/". $file ."\" ALT=\"\" BORDER=0 HSPACE=0 VSPACE=0>";
			echo "</A></LI>\n";
		}
	}
	echo "</UL>\n";
	echo "</DIV>\n";

} // $banned
echo "</BODY>\n";
echo "</HTML>\n";
?>
