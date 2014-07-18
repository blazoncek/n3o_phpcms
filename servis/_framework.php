<?php
/*~ _framework.php - HTML framework for administration
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

// include application variables and settings framework
require_once("../_application.php");
require_once("../inc/thumb/PhpThumb.inc.php");

function CheckUploads($folder) {
	// get uploaded files
	$Slike = @scandir($folder);
	if ( $Slike ) foreach ( $Slike As $Slika ) {
		if ( substr($Slika,1,1) == "." || !contains("jpg,png,gif", strtolower(right($Slika,3))) ) continue;
		return true;
	}
	return false;
}

// Check email server for messages
// Messagess need to be in certain format and from currently logged user
function CheckEmails($mailServer, $mailUser, $mailPass, $mailSSL=false) {
	global $db;
	
	// safety check
	if ( !isset($mailServer) ) return false;

	// get logged-in user details
	if ( $_SESSION['Authenticated'] ) {
		$UserEmail = $db->get_var("SELECT Email FROM SMUser WHERE UserID = ". (int)$_SESSION['UserID']);
		if ( $UserEmail == "" )
			return false;
	} else
		return false;

	//  Connect
	$conn = new POP3;
	if ( @$conn->Connect($mailUser, $mailPass, $mailServer, 110, $mailSSL) ) {
		$count = 0;

		// get list of messages
		$list = $conn->GetMessageList();
	
		// process  each message
		if ( $list ) foreach ( $list as $mail ) {
			// extract senders email address
			preg_match("/<([^>]+)>/", $mail['from'], $email);
			$email = substr($email[0], 1, strlen($email[0])-2);
			// find user messages
			if ( $email == $UserEmail && substr($mail['subject'], 0, 3) == 'ID:' ) {
				$count++;
			}
		}

		//  We need to disconnect
		$conn->Disconnect();
	
		return $count;
	}
	return false;
}
?>
<!DOCTYPE HTML>
<html>
<head>
<title>[Servis] <?php echo AppName ?></title>
<meta name="Author" content="Blaž Kristan (blaz@kristan-sp.si)" />
<link rel="icon" type="image/png" href="pic/servis-icon-128.png" />
<link rel="stylesheet" type="text/css" href="<?php echo $WebPath ?>/js/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="style.css" />
<link rel="stylesheet" type="text/css" href="xmenu.css" />
<script language="javascript" type="text/javascript" src="<?php echo $js ?>/funcs.js"></script>
<script language="javascript" type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $WebPath ?>/js/jquery/jquery.form.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $WebPath ?>/js/jquery/jquery.ui.widget.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $WebPath ?>/js/jquery/jquery.iframe-transport.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $WebPath ?>/js/jquery/jquery.fileupload.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $WebPath ?>/js/fancybox/jquery.easing-1.3.pack.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $WebPath ?>/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script language="javascript" type="text/javascript" src="xmenu.js"></script>
<!-- tinyMCE 3.5.6 -->
<script language="javascript" type="text/javascript" src="<?php echo $WebPath ?>/js/tiny_mce/jquery.tinymce.js"></script>
<script language="javascript" type="text/javascript">
// support variables
window.thumbSize  = 64;
window.imageSize  = 640;
window.idDocument = 0;

// custom file browser function
function fileBrowserCallBack(field_name, url, type, win)
{
	// example: win.document.forms[0].elements[field_name].value = "somevalue";
	var cmsURL = window.location.toString();    // script URL - use an absolute path!
/*
	if (cmsURL.indexOf("?") < 0) {
		//add the type as the only query parameter
		cmsURL = cmsURL + "?type=" + type;
	}
	else {
		//add the type as an additional query parameter
		// (PHP session ID is now included if there is one at all)
		cmsURL = cmsURL + "&type=" + type;
	}
*/

	switch (type) {
		case "image":
			par = "?";
			if (idDocument) par = par + "ID=" + idDocument;
			if (idDocument && (thumbSize || imageSize)) par = par + "&";
			if (thumbSize) par = par + "T=" + thumbSize;
			if (thumbSize && imageSize) par = par + "&";
			if (imageSize) par = par + "S=" + imageSize;
			cmsURL = "<?php echo dirname($_SERVER['PHP_SELF']) ?>/upload_text_image.php"+par;
			break;
		case "file":
			cmsURL = "<?php echo dirname($_SERVER['PHP_SELF']) ?>/select_text.php";
			break;
		default:
			cmsURL = null;
			break;
	}

	tinyMCE.activeEditor.windowManager.open({
		file : cmsURL,
		title : 'File Browser',
		width : 320,
		height : 480,
		resizable : "no",
		inline : "no",  // This parameter only has an effect if you use the inlinepopups plugin!
		close_previous : "yes",
		popup_css : false
	}, {
		window : win,
		input : field_name
	});
	return false;
}
// -->
</script>
<script language="javascript" type="text/javascript">
<!-- //
var lineHeight;

function fixSize(e)
{
	if ( !lineHeight ) lineHeight = $('#divList').height() + 4;

	var frame = $("#divFrame").width(0).height(0); // fix scroller problem
	var list  = $("#divList").width(0).height(0);
	var edit  = $("#divEdit").width(0).height(0);

	frame.width( $(window).innerWidth() - 2 ).height( $(window).innerHeight() - frame.position().top - 2 );
	list.height( frame.innerHeight()-1 ).width( 320 );
	edit.height( frame.innerHeight()-1 ).width( frame.innerWidth() - (list.css("display")=="block" ? 320 : 0) );

	// vertically resize list child's last div
	var seznam = $("#divList > div:last").height(0);
	if ( seznam.html() ) {
		seznam.height( list.innerHeight() + list.position().top - seznam.position().top - 10 );
		// set maximum lines for list
		setCookie('listmax',Math.floor((list.innerHeight() - seznam.position().top - 12)/lineHeight - 1));
		// refresh list
		if ( e && window.tReload ) clearTimeout( window.tReload );
		if ( e && window.listRefresh ) window.tReload = setTimeout(listRefresh,500);
	} else {
		// set default maximum lines for list
		setCookie('listmax',Math.floor((list.innerHeight() - 90)/lineHeight - 1));
	}

	// call user defined function
	if ( window.customResize ) customResize();
}

function loadTo(ID, page, options)
{
	if ( ID == "List" ) {
		toggleFrame(1); // display list view
		$("#divEdit").text(""); // clear edit view
		window.check        = null; // clear custom function
		window.customResize = null; // clear custom function
		window.listRefresh  = null; // clear custom function
		if ( window.tReload ) clearTimeout( window.tReload );
	}
	$("#imgClose").hide(); // in list view
	$("#imgSpinner").show(); // in list view
	if ( options != null )
		$("#div"+ID).load(page,options,function(){$("#imgSpinner").hide();$("#imgClose").show();});
	else
		$("#div"+ID).load(page,null,function(){$("#imgSpinner").hide();$("#imgClose").show();});
}

function toggleFrame(mode)
{
	if ( mode == null ) {
		$("#divList").toggle("fast",fixSize);
		$("#ToggleFrame").toggle();
	} else if ( mode == 1 ) {
		$("#divList").show("fast",fixSize);
		$("#ToggleFrame").hide();
	} else if ( mode == 0 ) {
		$("#divList").hide("fast",fixSize);
		$("#ToggleFrame").show();
	}
}

$(document).ready(function(){
	fixSize();
/*
    $('#fileupload').fileupload({
        dataType: 'json',
		dropZone: $('#fileupload'),
		add: function(e, data) {
			data.context = $('#fileupload').text('Uploading...');
			data.submit();
		},
        done: function (e, data) {
            $.each(data.result.files, function (index, value) {
				if ( index=='name' ) {
					data.context.text('Uploaded.');
					$('<p/>').text(index+'='+value).appendTo(document.body);
				}
            });
        }
    });
*/
});
$(window).resize(fixSize);
//-->
</script>
</head>
<body>
<div id="divMenu">
<?php include("_xmenu.php"); ?>
</div>
<div id="divFrame">
<div id="divList">&nbsp;</div>
<div id="divEdit">&nbsp;</div>
</div>
</body>
</html>
