<?php
/* _login.php - Login procedure & HTML.
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

function md5bin($target)
{
    $md5 = md5($target);
    $ret = '';

    for ( $i=0; $i<32; $i+=2 ) {
        $ret .= chr(hexdec($md5{ $i + 1 }) + hexdec($md5{ $i }) * 16);
    }
    return $ret;
}

if ( isset($_GET["login"]) && isset($_POST["Usr"]) ) {

	$_SESSION['Authenticated'] = false;
	$Error = "";

	$User = $db->get_row(
		"SELECT
			UserID,
			Email,
			Name,
			Password,
			Active
		FROM
			SMUser
		WHERE
			Username = '". $_POST["Usr"] ."'"
		);

	if ( $User && $User->Active == 1 ) {
		// authenticate user from LDAP (higher priority)
		if ( $User->Password == $AuthDomain ) {
			$ldap = ldap_connect($LDAPServer);
			if ( $ldap ) {
				if ( @ldap_bind($ldap, $_POST["Usr"].$AuthDomain, $_POST['Pwd']) ) {
					$_SESSION['Authenticated'] = true;
				} else {
					$Error = "Password";
				}
				ldap_close($ldap);
			} else {
				$Error = "Connect";
			}
		} else
		// authenticate user from DB
		//echo "<!-- ". crypt(PWSALT . $_POST['Pwd'], $User->Password) ." -->\n"; // for debugging password issues
		if ( crypt(PWSALT . $_POST['Pwd'], $User->Password) == $User->Password ) {
			$_SESSION['Authenticated'] = true;
		} else {
			$Error = "Password";
		}

		// if user authenticated create session structure
		if ( $_SESSION['Authenticated'] ) {
			$_SESSION['UserID']   = $User->UserID;
			$_SESSION['Email']    = $User->Email;
			$_SESSION['Username'] = $_POST['Usr'];
			$_SESSION['Password'] = $User->Password;
			$_SESSION['Name']     = $User->Name;
			setcookie("User", $_POST['Usr'], time()+60*60*24*365, $WebPath); // should never expire
			setcookie("User", $_POST['Usr'], time()+60*60*24*365, dirname($WebPath)); // should never expire
			
			// build user groups list (string with comma separated IDs)
			if ( $UserGroups = $db->get_col(
				"SELECT
					GroupID
				FROM
					SMUserGroups
				WHERE
					UserID = ". (int)$_SESSION['UserID'] ."
				ORDER BY
					GroupID" ) ) {
				// everyone group not found
				if ( !in_array("0", $UserGroups) )
					$_SESSION['Groups'] = "0,". implode(",", $UserGroups);
				else
					$_SESSION['Groups'] = implode(",", $UserGroups);
			} else {
				$_SESSION['Groups'] = "0";
			}
			// update login timestamp
			$db->query("START TRANSACTION");
			// purge old audit events (>3 months)
			$db->query("DELETE FROM SMAudit WHERE DateOfEntry < subdate(now(),90)");
			$db->query(
				"INSERT INTO SMAudit (
					UserID,
					Action,
					Description
				) VALUES (
					". (int)$_SESSION['UserID'] .",
					'Login',
					'". $_SERVER['REMOTE_ADDR'] ."'
				)"
				);
			$db->query(
				"UPDATE
					SMUser
				SET
					LastLogon = '". date("Y-m-d H:i:s") ."'
				WHERE
					UserID = ". (int)$_SESSION['UserID']
				);
			$db->query("COMMIT");
		}
	} else {
		$Error = "NoUser";
	}
}
?>
<!DOCTYPE HTML>
<html>
<head>
<title>[Servis] <?php echo AppName ?></title>
<meta name="Author" content="Blaž Kristan (blaz@kristan-sp.si)">
<meta name="viewport" content="initial-scale=1, maximum-scale=1.0, minimum-scale=1, user-scalable=no, width=device-width">
<meta name="apple-mobile-web-app-capable" content="yes" />
<link rel="apple-touch-icon" href="pic/servis-icon-precomposed-57.png" />
<link rel="apple-touch-icon" sizes="57x57" href="pic/servis-icon-precomposed-57.png" />
<link rel="apple-touch-icon" sizes="72x72" href="pic/servis-icon-precomposed-72.png" />
<link rel="apple-touch-icon" sizes="114x114" href="pic/servis-icon-precomposed-114.png" />
<link rel="apple-touch-icon" sizes="144x144" href="pic/servis-icon-precomposed-144.png" />
<link rel="icon" type="image/png" href="pic/servis-icon-128.png" />
<script language="javascript" type="text/javascript" src="<?php echo $js ?>/funcs.js"></script>
<script language="javascript" type="text/javascript">
<!-- // do not allow to run in a frame
if (self.parent.frames.length != 0)
	self.parent.location=document.location;

function testCookie() {
	document.cookie = "test=1";
	var cookieEnabled = (document.cookie.search("test=1") != -1);
	document.cookie = "test=null";
	return cookieEnabled;
}

if (!testCookie()) {
	alert("Cookies are not enabled!\nSystem management not possible!");
	document.location.href="../";
}
//-->
</script>
<style>
HTML, BODY {
	margin:0; padding:0;
	height:100%;
}
BODY, TD, INPUT {
	color: black;
	font-family: Verdana,Arial,Helvetica;
	font-size: 11px;
}
BODY {
	background: url( pic/fabric128.png ) top left;
	background-color: #ECEBE7;
	padding: 0 25px;
}
A { color: #6699CC;text-decoration: none; }
A:Hover { text-decoration: underline; }
INPUT { border: inset 1px; }
INPUT.but {
	color:white;
	background-color: #6699CC;
	border-top: solid #99CCFF 1px;
	border-left: solid #99CCFF 1px; 
	border-bottom: solid #003366 1px;
	border-right: solid #003366 1px;
	padding: 3px 5px;
	cursor: hand;
}
#nojs {
	padding:5px;
	background-color:#ff0000;
	font-size:16px;
	color:white;
	text-align:center;
}
#float {
	height: 50%;
	margin-bottom: <?php echo ($Mobile ? "-128px" : "-200px") ?>;
}
#loginframe {
	background: white url( pic/ozadje.jpg ) bottom right no-repeat;
	border: #174A7D solid 1px;
	border-radius: 5px;
	clear: left;
	width: <?php echo (!$Mobile ? "412px" : "100%") ?>;
	height: <?php echo (!$Mobile ? "325px" : "256px") ?>;
	margin: 0 auto;
	overflow: hidden;
	position: relative;
	-webkit-border-radius: 5px;
	-webkit-box-shadow: rgba(0,0,0, .5) 3px 3px 5px;
	-moz-border-radius: 5px;
	-moz-box-shadow: rgba(0,0,0, .5) 3px 3px 5px;
}
#loginframe FORM {
	border: #6699CC solid 1px;
	background: WhiteSmoke;
	margin-left: auto;
	margin-right: auto;
	width: 70%;
	padding: 10px;
}
#loginframe INPUT.txt {
	width: 90%;
}
#img {
	background: transparent url( pic/servis.gif ) top left no-repeat;
	height: 160px;
	margin-bottom: <?php echo ($Mobile ? "-80px" : "0") ?>;
	border-top-right-radius: 3px;
	border-top-left-radius: 3px;
	-webkit-border-top-right-radius: 3px;
	-webkit-border-top-left-radius: 3px;
	-moz-border-top-right-radius: 3px;
	-moz-border-top-left-radius: 3px;
}
#copy {
	position: absolute;
	bottom: 0px;
	background-color: #174A7D;
	color: white;
	width: 100%;
	padding: 3px 0;
	text-align: center;
	border-bottom-right-radius: 3px;
	border-bottom-left-radius: 3px;
	-webkit-border-bottom-right-radius: 3px;
	-webkit-border-bottom-left-radius: 3px;
	-moz-border-bottom-right-radius: 3px;
	-moz-border-bottom-left-radius: 3px;
}
#copy A { color: white; }
#msg {
	color: red;
	text-align: center;
	margin: 20px 0;
	font-size: 14px;
}
@media only screen and (max-width: 480px) {
	#float {
		margin-bottom: -85px;
	}
	#loginframe {
		width: 400px;
		height: 170px;
	}
	#img {
		margin-bottom: -133px;
		background-size: 194px auto;
	}
	#msg {
		margin-top: 40px;
	}
}
@media only screen and (max-width: 450px) {
	#float {
		margin-bottom: -100px;
	}
	#loginframe {
		width: 270px;
		height: 200px;
	}
	#img {
		margin-bottom: -110px;
		background-size: 194px auto;
	}
	#msg {
		margin-top: 40px;
	}
}
@media only screen and (-webkit-min-device-pixel-ratio: 1.5),
       only screen and (min--moz-device-pixel-ratio: 1.5),
       only screen and (min-resolution: 240dpi) {
	BODY {
		background-size: 128px auto;
	}
	#loginframe {
		background-size: 206px auto;
	}
}
</style>
</head>
<body>
<noscript><div id="nojs"><b>Please enable JavaScript for this site to function properly.</b></div></noscript>
<div id="float"></div>
<div id="loginframe"><div id="img"></div>
<?php
if ( isset( $_SESSION['Authenticated'] ) && $_SESSION['Authenticated'] ) {
	header("Refresh:1; URL=./");
	echo "<div id=\"msg\">\n";
	echo "<B>Login successful!</B>\n";
	echo "</div>\n";
} else {
	if ( isset($_POST['Usr']) && isset($_POST['Pwd']) ) {
		switch ( $Error ){
			case "NoUser"   : echo "<SPAN STYLE=\"color: red;\"><B>No such user!</B></SPAN>\n"; break;
			case "Password" : echo "<SPAN STYLE=\"color: red;\"><B>Incorrect username/password!</B></SPAN><!-- ". crypt(PWSALT . $_POST['Pwd']) ." -->\n"; break;
			case "Connect"  : echo "<SPAN STYLE=\"color: red;\"><B>No connection to server!</B></SPAN>\n"; break;
			default         : echo "<SPAN STYLE=\"color: red;\"><B>Unspecified error!</B></SPAN>\n"; break;
		}
	}
?>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
function validate(fObj) {
	if (fObj.Usr.value.length==0) {fObj.Usr.focus(); return false;}
	if (fObj.Pwd.value.length==0) {fObj.Pwd.focus(); return false;}
	return true;
}

function selectEdit() {
	if (document.forms.loginform) {
		if (document.forms.loginform.Usr.value=="") {
			document.forms.loginform.Usr.focus();
			document.forms.loginform.Usr.select();
		} else {
			document.forms.loginform.Pwd.focus();
			document.forms.loginform.Pwd.select();
		}
	}
}

onload="selectEdit()";
//-->
</SCRIPT>
<?php if ( !$Mobile ) : ?>
	<div style="float:left;margin:0 10px;width:160px;">
	Please login with your username and password<br><br>
	Access to administration pages requires use of cookies and enabled JavaScript.
	</div>
	<div style="float:left;margin:0 10px;width:200px;">
<?php else : ?>
	<div style="margin:0 10px;">
<?php endif ?>
	<FORM ACTION="<?php echo $_SERVER['PHP_SELF']?>?login" METHOD="post" NAME="loginform" ONSUBMIT="return validate(this);">
	Username:<BR>
	<INPUT Name="Usr" Type="TEXT" Size="20" MAXLENGTH="50" class="txt" TABINDEX=1 VALUE="<?php if ( isset($_COOKIE['User']) ) echo $_COOKIE['User']; ?>"><BR>
	Password:<BR>
	<INPUT Name="Pwd" Type="PASSWORD" Size="20" MAXLENGTH="50" class="txt" TABINDEX=2><BR>
	<DIV ALIGN="right"><INPUT TYPE="submit" VALUE="Enter" CLASS="but" TABINDEX="3"></DIV>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!-- //
selectEdit();
//-->
</SCRIPT>			
	</FORM>
	</div>
<?php
}
?>
<div id="copy">Copyright &copy; 2007-<?php echo date('Y') ?>, <a href="http://blaz.at/home/" target="_top">Blaž Kristan</a></div>
</div>
</body>
</html>
