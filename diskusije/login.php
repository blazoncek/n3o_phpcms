<?php
/*~ index.php - main page of application framework
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

if ( contains($_SERVER['QUERY_STRING'],"exit") )
	header("Refresh:2; URL=". $WebURL);

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
		// IP address is blacklisted
		header( "Refresh:0; URL=../" );
		die();
	}
}

if ( !isset($_GET['querystring']) ) $_GET['querystring'] = "";
if ( isset($_GET['referer']) ) {
	$_POST['referer']     = $_GET['referer'];
	$_POST['querystring'] = $_GET['querystring'];
}

$Email="";
if ( isset($_COOKIE['Email']) ) $Email = $_COOKIE['Email'];
if ( isset($_GET['Email']) )    $Email = $_GET['Email'];
if ( isset($_POST['Email']) )   $Email = $_POST['Email'];	

echo "<!DOCTYPE HTML>\n";
echo "<HTML>\n";
echo "<HEAD>\n";
include_once( "../_htmlheader.php" );
include_once( "_forumheader.php" );
echo "<SCRIPT LANGUAGE=\"JavaScript\" TYPE=\"text/javascript\">\n";
echo "<!--\n";
echo "window.focus();\n";
echo "if (!testCookie()) alert(\"Piškotki niso vklopljeni!\nDelovanje diskusij bo zelo omejeno ali nepravilno!\");\n";
echo "//-->\n";
echo "</SCRIPT>\n";
echo "</HEAD>\n";

echo "<BODY>\n";
// EU cookie compliance (Google Analytics & forum support)
if ( !isset($_COOKIE['accept_cookies']) && strncasecmp($WebURL, $_SERVER['HTTP_REFERER'], strlen($WebURL)) == 0 ) {
 	// get ID of special category for cookies disclaimer/description
	$ktg = $db->get_var("SELECT KategorijaID FROM Kategorije WHERE Ime = 'Cookies'");
	if ( $_GET['kat'] != $ktg ) {
		// continuing browsing -> implicit consent
		setcookie('accept_cookies', 'yes', time()+31536000, $WebPath);
		$_COOKIE['accept_cookies'] = 'yes';
	}
	unset($ktg);
}
if ( !isset($_COOKIE['accept_cookies']) && (defined('ANALYTICS_ID') || is_file(dirname(__FILE__) ."/diskusije/index.php")) ) {
	// display cookie warning
	include_once(dirname(__FILE__) ."/_cookies.php");
} else if ( isset($_COOKIE['accept_cookies']) && $_COOKIES['accept_cookies'] == "no" ) {
	// redirect to Google if not accepting
	header( "Refresh:1; URL=http://www.google.com?q=http+cookie" );
	die();
}
echo "<div id=\"body\">\n";

echo "<div id=\"head\">\n";
include_once( "../_glava.php" );
echo "</div>\n";

echo "<div id=\"content\">\n";
?>
<?php if ( contains($_SERVER['QUERY_STRING'],"reset") ) : ?>

<DIV ALIGN="center"><TABLE ALIGN="center" BGCOLOR="<?php echo $FrameColor ?>" BORDER="0" CELLPADDING="3" CELLSPACING="1" WIDTH="320">
<TR>
	<TD VALIGN="middle">&nbsp;<B><FONT COLOR="<?php echo $TxtFrColor ?>">Resetiranje gesla</FONT></B></TD>
</TR>
<TR>
	<TD ALIGN="center" BGCOLOR="<?php echo $BackgColor ?>">
<?php
	if ( isset($_GET['Confirm']) && isset($_GET['Email']) ) {
	
		$getMember = getmemberbyemail($Email);
		if ( $getMember->Password == base64_decode($_GET['Confirm'] .'==') ) {

			$xGeslo = chr(rand(65,92)) . rand(0,9) . chr(rand(97,123)) . chr(rand(33,47)) . chr(rand(97,123)) . rand(0,9) . chr(rand(97,123));

			$SMTPServer->AddAddress($getMember->Email, $getMember->Name);
			$SMTPServer->Subject = $ForumTitle . " : Sprememba gesla";
			$SMTPServer->AltBody = "Pozdravljeni!\n\nNa straneh ". $ForumTitle ." Diskusij ste zahtevali reset gesla,\nki se po novem glasi:\n\n". $xGeslo .
				"\n\nPriporočamo, da si geslo takoj spremenite v Nastavitvah znotraj ". $ForumTitle ." Diskusij.\n\n" .
				"V kolikor reset gesla niste zahtevali sami, obstaja verjetnost,\n".
				"da je nekdo poskušal zlorabiti vaše geslo za vstop v ". $ForumTitle ."\n".
				"Diskusije v vašem imenu. Če menite, da je to res, nas o tem\n".
				"nemudom obvestite na naslov:\n". $Postmaster ."\n".
				($getMember->Enabled ? "\n(trenutno je ta račun zaklenjen in neuporaben)\n\n" : "") .
				"Lepo pozdravljeni,\n". $ForumTitle;
			$SMTPServer->MsgHTML(
				"<p>Pozdravljeni!</p><p>Na straneh ". $ForumTitle ." Diskusij ste zahtevali reset gesla,\nki se po novem glasi:<br><br>". $xGeslo .
				"<br><br>Priporočamo, da si geslo takoj spremenite v Nastavitvah znotraj " . $ForumTitle . " Diskusij.</p>" .
				"<p>V kolikor reset gesla niste zahtevali sami, obstaja verjetnost, ".
				"da je nekdo poskušal zlorabiti vaše geslo za vstop v ". $ForumTitle ." ".
				"Diskusije v vašem imenu. Če menite, da je to res, nas o tem ".
				"nemudom obvestite na naslov:<br>" . $PostMaster . "</p>".
				(!$getMember->Enabled ? "<p>(trenutno je ta račun zaklenjen in neuporaben)</p>" : "") .
				"<p>Lepo pozdravljeni,<br>". $ForumTitle ."</p>"
			);
			if ( !$SMTPServer->Send() ) {
				echo "Pri pošiljanju novega gesla je prišlo do napake.<br><b>Geslo ni resetirano!</b>";
			} else {
				$db->query("UPDATE frmMembers
					SET Password = '". $db->escape(crypt(PWSALT . $xGeslo))."'
					WHERE ID = ".(int)$getMember->ID
				);
?>
			Geslo je bilo poslano na naslov:<BR><B><?php echo $getMember->Email ?></B><BR><BR>
			<P ALIGN="justify">Kmalu se bo v vašem e-poštnem nabiralniku pojavilo sporočilo, ki bo vsebovalo vaše geslo.
			Če takega sporočila ne bi dobili ali pa ste zamenjali vaš e-poštni naslov, se lahko
			obrnete na <A HREF="mailto:<?php echo $PostMaster ?>?subject=Geslo za klub"><?php echo $PostMaster ?></A>
			s pripisom, da želite spremeniti geslo oz. da vaš vpisani e-poštni naslov ne deluje več.</P>
			<?php if ( !$getMember->Enabled ) : ?>
			<P><FONT COLOR="<?php echo $TxtExColor ?>"><B>Trenutno je ta račun zaklenjen in neuporaben!</B></FONT></P>
			<?php endif ?>
<?php
			}
			$SMTPServer->ClearAddresses();
		} else {
			echo "Neveljavna potrditvena koda!";
		}

	} else if ( isset($_GET['Email']) || isset($_POST['Email']) ) {

		$getMember = getmemberbyemail($Email);
		if ( $getMember && $getMember->Email != "" ) {

			$link = $WebURL . "/diskusije/login.php?reset&Email=". urlencode($getMember->Email) ."&Confirm=". base64_encode($getMember->Password);

			$SMTPServer->AddAddress($getMember->Email, $getMember->Name);
			$SMTPServer->Subject = $ForumTitle . " : Sprememba gesla";
			$SMTPServer->AltBody = "Pozdravljeni!\n\nNa straneh ". $ForumTitle ." Diskusij ste zahtevali reset gesla.\n".
				"S klikom na spodnjo povezavo boste geslo resetirali.\n\n".
				$link.
				"\n\nV kolikor reset gesla niste zahtevali sami, obstaja verjetnost,\n".
				"da je nekdo poskušal zlorabiti vaše geslo za vstop v ". $ForumTitle ." Diskusije\n".
				"v vašem imenu. Če menite, da je to res, nas o tem nemudom obvestite na naslov:\n".
				$Postmaster ."\n".
				($getMember->Enabled ? "\n(trenutno je ta račun zaklenjen in neuporaben)\n\n" : "") .
				"Lepo pozdravljeni,\n". $ForumTitle;
			$SMTPServer->MsgHTML(
				"<p>Pozdravljeni!</p><p>Na straneh ". $ForumTitle ." Diskusij ste zahtevali reset gesla.<br>".
				"S klikom na spodnjo povezavo boste geslo resetirali.<br><br>".
				"<a href=\"". $link ."\">". $link ."</a></p>" .
				"<p>V kolikor reset gesla niste zahtevali sami, obstaja verjetnost, ".
				"da je nekdo poskušal zlorabiti vaše geslo za vstop v ". $ForumTitle ." ".
				"Diskusije v vašem imenu. Če menite, da je to res, nas o tem ".
				"nemudom obvestite na naslov:<br>".
				$PostMaster . "</p>".
				(!$getMember->Enabled ? "<p>(trenutno je ta račun zaklenjen in neuporaben)</p>" : "") .
				"<p>Lepo pozdravljeni,<br>". $ForumTitle ."</p>"
			);
			if ( !$SMTPServer->Send() ) {
				echo "Pri pošiljanju potrditvenega sporočila je prišlo do napake!";
			} else {
?>
			Potrditveno sporočilo je bilo poslano na naslov:<BR><B><?php echo $getMember->Email ?></B><BR><BR>
			<P ALIGN="justify">Kmalu se bo v vašem e-poštnem nabiralniku pojavilo sporočilo, ki bo vsebovalo potrditveno povezavo.
			Geslo boste resetirali šele po kliku na to povezavo.<br>
			Če takega sporočila ne bi dobili ali pa ste zamenjali vaš e-poštni naslov, se lahko
			obrnete na <A HREF="mailto:<?php echo $PostMaster ?>?subject=Geslo za klub"><?php echo $PostMaster ?></A>
			s pripisom, da želite spremeniti geslo oz. da vaš vpisani e-poštni naslov ne deluje več.</P>
			<?php if ( !$getMember->Enabled ) : ?>
			<P><FONT COLOR="<?php echo $TxtExColor ?>"><B>Trenutno je ta račun zaklenjen in neuporaben!</B></FONT></P>
			<?php endif ?>
<?php
			}
			$SMTPServer->ClearAddresses();
		} else {
?>
			<FONT COLOR="<?php echo $TxtExColor ?>"><B>V Diskusijah ni aktivnega člana s takim <nobr>e-poštnim</nobr> naslovom!</B></FONT><BR><BR>
			<P ALIGN="justify">Če ste zamenjali epoštni naslov in vaš stari ne deluje več, se lahko
			obrnete na <A HREF="mailto:<?php echo $PostMaster ?>?subject=Geslo za klub"><?php echo $PostMaster ?></A>
			s pripisom, da želite spremeniti geslo oz. da vaš vpisani e-poštni naslov ne deluje več.
<?php
		}
	} else {
?>
			Vpišite svoj e-poštni naslov in sistem vam bo nanj poslal vaše novo geslo.<BR><BR>
			<FORM NAME="sendfrm" ACTION="login.php?reset&exit" METHOD="post">
			<DIV ALIGN="left" STYLE="padding:10px;">Email:<BR><INPUT TYPE="Text" NAME="Email" SIZE="8" VALUE="<?php echo $Email ?>" ONFOCUS="this.select();" TITLE="Vpišite email naslov." STYLE="width:90%;border:<?php echo $FrameColor ?> solid 1px;"></DIV>
			<INPUT TYPE="Submit" VALUE="Pošlji" CLASS="but">
			</FORM>
<?php
	}
?>
	</TD>
</TR>
</TABLE>
<?php endif ?>

<?php if ( contains($_SERVER['QUERY_STRING'],"logout") ) : ?>
	<?php
	if ( $_SESSION['MemberID'] ) {
		updmemberlastvisit($_SESSION['MemberID']);
		$db->query("DELETE FROM frmVisitors WHERE SessionID='". session_id() ."'");
	}
	$_SESSION['MemberID']      = 0;
	$_SESSION['Nickname']    = "";
	$_SESSION['Name']        = "";
	$_SESSION['Email']       = "";
	$_SESSION['Settings']    = "";
	$_SESSION['AccessLevel'] = 0;
	$_SESSION['frmPassword'] = "";
	unset( $_SESSION['MemberID'] );
	unset( $_SESSION['Nickname'] );
	unset( $_SESSION['Name'] );
	unset( $_SESSION['Email'] );
	unset( $_SESSION['Settings'] );
	unset( $_SESSION['AccessLevel'] );
	unset( $_SESSION['frmPassword'] );
	?>
	<DIV ALIGN="center" style="margin:10px 0;"><TABLE ALIGN="center" BGCOLOR="<?php echo $FrameColor ?>" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="320">
	<TR>
		<TD HEIGHT="20" VALIGN="middle">&nbsp;<B><FONT COLOR="<?php echo $TxtFrColor ?>">Odjava</FONT></B></TD>
	</TR>
	<TR>
		<TD HEIGHT="160">
		<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="1" HEIGHT="100%" WIDTH="100%">
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD ALIGN="center">
			<B CLASS="a14">Odjavljeni ste iz sistema!</B><BR>
			<BR>
			<SPAN CLASS="a10">(Za vrnitev na osnovno stran kliknite <A HREF="<?php echo $WebURL ?>">tule</A>.)</SPAN>
			</TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	</TABLE></DIV>
<?php endif ?>

<?php if ( contains($_SERVER['QUERY_STRING'],"login") ) : ?>
	<?php
	$_SESSION['MemberID']    = 0;
	$_SESSION['Nickname']    = "";
	$_SESSION['Name']        = "";
	$_SESSION['Email']       = "";
	$_SESSION['Settings']    = "";
	$_SESSION['AccessLevel'] = 0;
	$_SESSION['frmPassword'] = "";
	
	if ( isset($_POST['Email']) && isset($_POST['Geslo']) ) {

		$getMember = getmemberbyemail($_POST['Email']);
		if ( $getMember && $getMember->Enabled ) {

			// authenticate user from DB
			if ( crypt(PWSALT. $_POST['Geslo'], $getMember->Password) == $getMember->Password ) {

				if ( isset($_POST['referer']) && contains($_SERVER['QUERY_STRING'],"reload") )
					header("Refresh:1; URL=". $_POST['referer'] . ($_POST['querystring']=="" ? "" : "?". $_POST['querystring']));
				else if ( contains($_SERVER['QUERY_STRING'],"reload") )
					header("Refresh:1; URL=./");

				$_SESSION['MemberID']    = $getMember->ID;
				$_SESSION['Nickname']    = $getMember->Nickname;
				$_SESSION['Name']        = $getMember->Name;
				$_SESSION['Email']       = $getMember->Email;
				$_SESSION['Settings']    = $getMember->Settings;
				$_SESSION['AccessLevel'] = $getMember->AccessLevel;
				
				setcookie("Email", $_POST['Email'], time()+31536000, $WebPath);
				if ( isset($_POST['Auto']) ) {
					setcookie("Geslo", $getMember->Password, time()+31536000, $WebPath);
				} else {
					setcookie("Geslo", '', time(), $WebPath);
				}
				updmemberlastvisit($getMember->ID);

			} else {
				$Error = "Neveljavno geslo.";
			}
		} else {
			$Error = "Uporabnik s takim e-naslovom ne obstaja.";
		}
	} else if ( (isset($_COOKIE['Email']) && $_COOKIE['Email'] != "")
		&& (isset($_COOKIE['Geslo']) && $_COOKIE['Geslo'] != "") ) {

		$getMember = getmemberbyemail($_COOKIE['Email']);
		if ( $getMember && $getMember->Enabled ) {

			// authenticate user from DB
			if ( $_COOKIE['Geslo'] == $getMember->Password ) {

				if ( isset($_POST['referer']) && contains($_SERVER['QUERY_STRING'],"reload") )
					header("Refresh:1; URL=". $_POST['referer'] . ($_POST['querystring']=="" ? "" : "?". $_POST['querystring']));
				else if ( contains($_SERVER['QUERY_STRING'],"reload") )
					header("Refresh:1; URL=./");

				$_SESSION['MemberID']    = $getMember->ID;
				$_SESSION['Nickname']    = $getMember->Nickname;
				$_SESSION['Name']        = $getMember->Name;
				$_SESSION['Email']       = $getMember->Email;
				$_SESSION['Settings']    = $getMember->Settings;
				$_SESSION['AccessLevel'] = $getMember->AccessLevel;
				
				updmemberlastvisit($getMember->ID);

			} else {
				$Error = "Neveljavna samodejna prijava.";
				setcookie("Geslo", '', time(), $WebPath); // expire cookie immediately
			}
		} else {
			$Error = "Uporabnik s takim e-naslovom ne obstaja.";
			setcookie("Email", '', time(), $WebPath); // expire cookie immediately
		}

	} else {
		setcookie("Geslo", '', time(), $WebPath); // expire cookie immediately
	}
?>
	<?php if ( !$_SESSION['MemberID'] ) : ?>
	<?php if ( isset($_POST['Geslo']) && $_POST['Geslo'] != "" ) : ?>
	<P ALIGN="center" style="margin:10px 0;"><FONT COLOR="<?php echo $TxtExColor ?>"><B CLASS="a14">Neuspešna prijava!</B></FONT></P>
	<?php endif ?>
	<?php if ( isset($Error) && $Error != "" ) : ?><P ALIGN="center"><?php echo $Error ?></P><?php endif ?>
	<DIV ALIGN="center"><TABLE ALIGN="center" BGCOLOR="<?php echo $FrameColor ?>" BORDER="0" CELLPADDING="0" CELLSPACING="1" HEIGHT="180" WIDTH="320">
	<TR>
		<TD HEIGHT="20" VALIGN="middle">&nbsp;<B><FONT COLOR="<?php echo $TxtFrColor ?>">Prijava</FONT></B></TD>
	</TR>
	<TR>
		<TD BGCOLOR="<?php echo $BackgColor ?>" CLASS="a10">
		<FORM NAME="loginfrm" ACTION="login.php?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
		<TABLE BORDER="0" CELLPADDING="10" CELLSPACING="1" HEIGHT="100%" WIDTH="100%">
		<TR>
			<TD><?php if ( isset($_POST['referer']) ) : ?><INPUT TYPE="Hidden" NAME="referer" VALUE="<?php echo $_POST['referer'] ?>"><INPUT TYPE="Hidden" NAME="querystring" VALUE="<?php echo $_POST['querystring'] ?>"><?php endif ?>
			Email:<BR>
			<INPUT TYPE="Text" NAME="Email" SIZE="8" VALUE="<?php echo $Email ?>" MAXLENGTH="64" ONFOCUS="this.select();" TITLE="Vpišite email naslov." STYLE="width:100%;border:<?php echo $FrameColor ?> solid 1px;"><BR>
			Geslo:<BR>
			<INPUT TYPE="Password" NAME="Geslo" SIZE="8" VALUE="" MAXLENGTH="32" ONFOCUS="this.select();" TITLE="Vpišite geslo." STYLE="width:100%;border:<?php echo $FrameColor ?> solid 1px;"><BR>
			Samodejna prijava? <INPUT TYPE="Checkbox" NAME="Auto"><BR><BR>
			<DIV ALIGN="center"><INPUT TYPE="Submit" VALUE="Prijava" CLASS="but">&nbsp;</DIV>
			</TD>
		</TR>
		</TABLE>
		</FORM>
		</TD>
	</TR>
	</TABLE></DIV>
	<P ALIGN="center">Vsi, ki še niste včlanjeni, se lahko včlanite <A HREF="javascript:dialogOpen('vpispodatkov.php?new');window.close();"><B>tule</B></A>!<BR>
	Če ste pozabili geslo, kliknite <A HREF="login.php?reset"><B>tule</B></A>.</P>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
if (document.loginfrm.Email.value=="") {
	document.loginfrm.Email.focus();
} else {
	document.loginfrm.Geslo.focus();
	document.loginfrm.Geslo.select();
}
//-->
</SCRIPT>

	<?php else : ?>

	<DIV ALIGN="center" style="margin:10px 0;"><TABLE ALIGN="center" BGCOLOR="<?php echo $FrameColor ?>" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="320">
	<TR>
		<TD HEIGHT="20" VALIGN="middle">&nbsp;<B><FONT COLOR="<?php echo $TxtFrColor ?>">Prijava</FONT></B></TD>
	</TR>
	<TR>
		<TD HEIGHT="160">
		<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="1" HEIGHT="100%" WIDTH="100%">
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD ALIGN="center">
			<B CLASS="a14">Prijava uspešno zaključena!</B><BR>
			<?php if ( isset($_POST['referer']) && $_POST['referer'] != "" ) : ?>
			<BR>
			<SPAN CLASS="a10">(Za vrnitev v diskusije kliknite <A HREF="<?php echo $_POST['referer'] ?>?<?php echo $_POST['querystring'] ?>">tule</A>.)</SPAN>
			<?php endif ?>
			</TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	</TABLE></DIV>

	<?php endif ?>
<?php endif ?>
<?php
echo "</div>\n";

echo "<div id=\"foot\">\n";
include_once( "../_noga.php" );
echo "</div>\n";
echo "</div>\n";
echo "</BODY>\n";
echo "</HTML>\n";
?>