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
		// header( "Refresh:0; URL=../" );
		echo "<script language=\"javascript\" type=\"text/javascript\">window.close();</script>\n";
		die();
	}
}

if ( !$_SESSION['MemberID'] && isset($_COOKIE['Email']) && isset($_COOKIE['Geslo']) ) {
	header( "Refresh:0; URL=login.php?login&reload&referer=". urlencode($_SERVER['PHP_SELF']) .($_SERVER['QUERY_STRING']!="" ? "&querystring=". urlencode($_SERVER['QUERY_STRING']) : "") );
	die();
}

if ( !$_SESSION['MemberID'] && !$AllowAnonymous ) {
	// anonymous posting not allowed
	echo "<script language=\"javascript\" type=\"text/javascript\">window.close();</script>\n";
	die();
}

$Error = "";
if ( isset($_POST['What']) ) {

	switch ( $_POST['What'] ) {
		case "Vpis":
			$getMember = getmemberbyemail($_POST['Email']);
			$nPosAfna = strpos($_POST['Email'], "@");
			$nPosPika = strpos($_POST['Email'], ".", $nPosAfna+1);
			$EmailOK  = $nPosAfna < $nPosPika;
			if ( $Error == "" && $getMember )
				$Error = "Vpisan vzdevek ali epoštni naslov že obstaja! Ponovite vpis z drugimi podaki.";
			if ( $Error == "" && !$EmailOK )
				$Error = "Nepravilen epoštni naslov!";
		break;
		
		case "Spremeni":
			$getMember = getmemberbyemail($_POST['Email']);
			$nPosAfna = strpos($_POST['Email'], "@");
			$nPosPika = strpos($_POST['Email'], ".", $nPosAfna+1);
			$EmailOK  = $nPosAfna < $nPosPika;
			if ( $Error == "" && $_POST['Geslo'] != "" && ($_POST['Geslo'] != $_POST['Geslo2']))
				$Error = "Vpisani gesli se ne ujemata!";
			if ( $Error == "" && strlen(Geslo) < 4 && Geslo != "" )
				$Error = "Geslo je prekratko! Najmanjša dovoljena dolžina gesla je 4 znake.";
			if ( $Error == "" && !$EmailOK )
				$Error = "Nepravilen epoštni naslov!";
			if ( $Error == "" && ($getMember->ID > 0 && $getMember->ID != $_SESSION['MemberID']) )
				$Error = "Vpisan epoštni naslov ali vzdevek že uporablja drug član.";
			if ( $Error == "" && ($_POST['Email'] != $_SESSION['Email'] && $_POST['Geslo'] != "") )
				$Error = "Email naslova in gesla ne morete spremeniti hkrati!";
		break;
		
		default :
			$Error = "Nepravilen vstop!";
		break;
	}

	if ( $Error == "" ) {
		if( count($BanList = $db->get_results("SELECT Email FROM frmBanList WHERE Email IS NOT NULL")) ) {
			foreach ( $BanList AS $BanItem ) {
				$BannedEmail = $BanItem->Email;
				if ( left($BanItem->Email,1) == "*" )
					$BannedEmail = right($BanItem->Email, strlen($BanItem->Email)-1);
				if ( strcasecmp(right($_POST['Email'],strlen($BannedEmail)), $BannedEmail) == 0 ) {
					$Error = "Takega email naslova ne morete uporabiti.";
					break;
				}
			}
		}
	}

	if ( $Error == "" ) {

		$Nastavitve  = "Color=". $_POST['Barva'];
		$Nastavitve .= ",Rows=". max(min(99,(int)$_POST['Rows']),5);
		$Nastavitve .= ",Slika=". $_POST['Slika'];
		if ( isset($_POST['Edit']) ) {
			$Nastavitve .= ",Edit=Full";
		} else {
			$Nastavitve .= ",Edit=Plain";
		}
		if ( isset($_POST['Start']) ) {
			$Nastavitve .= ",Start=Last";
		} else {
			$Nastavitve .= ",Start=First";
		}
		$Nastavitve .= ",Sort=". $_POST['Sort'];

		switch ( $_POST['What'] ) {
		
			case "Vpis":
				$_POST['Geslo'] = chr(rand(65,92)) . rand(0,9) . chr(rand(97,123)) . chr(rand(33,47)) . chr(rand(97,123)) . rand(0,9) . chr(rand(97,123));
				$xGeslo = crypt(PWSALT . $_POST['Geslo']);
				$_POST['Signature'] = preg_replace("/<[\/]*IMG[^>]*>/i",    "", $_POST['Signature']);
				$_POST['Signature'] = preg_replace("/<[\/]*SCRIPT[^>]*>/i", "", $_POST['Signature']);
				$_POST['Signature'] = preg_replace("/<[\/]*IFRAME[^>]*>/i", "", $_POST['Signature']);
				$_POST['Signature'] = preg_replace("/<[\/]*ILAYER[^>]*>/i", "", $_POST['Signature']);
				$db->query("
					INSERT INTO frmMembers (
						Name,
						Address,
						Phone,
						Email,
						Nickname,
						Password,
						Enabled,
						MailList,
						ShowEmail,
						Settings,
						Signature,
						Posts,
						AccessLevel,
						ICQUIN,
						SignIn,
						DisplayName,
						ShowPersonalData,
						Sex,
						WebPage,
						LastIPAddress
					) VALUES (
						'". ($_POST['Ime'] != "" ? $db->escape($_POST['Ime']) : 'neimenovan') ."',
						". ($_POST['Address'] != "" ? "'". $db->escape($_POST['Address']) ."'" : NULL) .",
						". ($_POST['Phone'] != "" ? "'". $db->escape($_POST['Phone']) ."'" : NULL) .",
						". ($_POST['Email'] != "" ? "'". $db->escape($_POST['Email']) ."'" : NULL) .",
						". ($_POST['Vzdevek'] != "" ? "'". $db->escape($_POST['Vzdevek']) ."'" : NULL) .",
						'". $db->escape($xGeslo) ."',
						1,
						". (isset($_POST['Mail']) ? 1 : 0) .",
						". (isset($_POST['ShowEmail']) ? 1 : 0) .",
						'". $db->escape($Nastavitve) ."',
						". (isset($_POST['Signature']) ? "'". $db->escape(left($_POST['Signature'],500)) ."'" : NULL) .",
						0,
						1,
						". ((int)$_POST['ICQ'] ? "'". (int)$_POST['ICQ'] ."'" : NULL) .",
						'". now() ."',
						". (isset($_POST['DisplayName']) ? 1 : 0) .",
						". (isset($_POST['ShowPersonalData']) ? 1 : 0) .",
						". (isset($_POST['Sex']) && $_POST['Sex'] != "" ? "'". $db->escape($_POST['Sex']) ."'" : NULL) .",
						". ($_POST['WebPage'] != "" ? "'". $db->escape($_POST['WebPage']) ."'" : NULL) .",
						'". $_SERVER['REMOTE_ADDR'] ."'
					)");
				$id = $db->insert_id;

				setcookie("Email", $_POST['Email'], time()+31536000, $WebPath);
				
				$SMTPServer->AddAddress($_POST['Email'], $_POST['Ime']);
				$SMTPServer->Subject = AppName . " : Sprememba gesla";
				$SMTPServer->AltBody = "Pozdravljeni in dobrodošli v ". AppName ." Diskusije!\n\n".
					"Vaši osebni podatki so naslednji:\n\n".
					"Vzdevek: ". $_POST['Vzdevek'] ."\n".
					"Email: ". $_POST['Email'] ."\n".
					"Geslo: ". $_POST['Geslo'] ."\n".
					"ID: ". $id ."\n\n".
					"Ostale podatke si lahko ogledate in spremenite v nastavitvah, na naslovu:\n".
					$WebServer ."/login.php?login&referer=vpispodatkov.php&querystring=edit\n\n".
					"Svetujemo vam, da si to sporočilo shranite ali spremenite dodeljeno geslo. V primeru težav se obrnite na naslov:\n".
					$PostMaster ."\n\n".
					"Lepo pozdravljeni,\n". AppName;
				$SMTPServer->MsgHTML(
					"<p>Pozdravljeni in dobrodošli v ". AppName ." Diskusije!</p>".
					"<p>Vaši osebni podatki so naslednji:<br><br>".
					"Vzdevek: ". $_POST['Vzdevek'] ."<br>".
					"Email: ". $_POST['Email'] ."<br>".
					"Geslo: ". $_POST['Geslo'] ."<br>".
					"ID: ". $id ."</p>".
					"<p>Ostale podatke si lahko ogledate in spremenite v nastavitvah, na naslovu:<br>".
					$WebServer ."/login.php?login&referer=vpispodatkov.php&querystring=edit</p>".
					"<p>Svetujemo vam, da si to sporočilo shranite ali spremenite dodeljeno geslo. V primeru težav se obrnite na naslov:<br>".
					$PostMaster ."</p>".
					"<p>Lepo pozdravljeni,<br>". AppName ."</p>"
				);
				if ( !$SMTPServer->Send() ) {
					$Error = "Pri pošiljanju podatkov po epošti je prišlo do napake.<br>Vaše geslo se glasi: <b>". $_POST['Geslo'] ."</b>";
				} else {
				}
			break;
		
			case "Spremeni":
				if ( $_SESSION['Email'] != $_POST['Email'] )
					$_POST['Geslo'] = chr(rand(65,92)) . rand(0,9) . chr(rand(97,123)) . chr(rand(33,47)) . chr(rand(97,123)) . rand(0,9) . chr(rand(97,123));
				if ( $_POST['Geslo'] != "" )
					$xGeslo = crypt(PWSALT . $_POST['Geslo']);
				$_POST['Signature'] = preg_replace("/<[\/]*IMG[^>]*>/i",    "", $_POST['Signature']);
				$_POST['Signature'] = preg_replace("/<[\/]*SCRIPT[^>]*>/i", "", $_POST['Signature']);
				$_POST['Signature'] = preg_replace("/<[\/]*IFRAME[^>]*>/i", "", $_POST['Signature']);
				$_POST['Signature'] = preg_replace("/<[\/]*ILAYER[^>]*>/i", "", $_POST['Signature']);
				$db->query("
					UPDATE frmMembers
					SET Name      = ". ($_POST['Ime'] != "" ? "'". $db->escape($_POST['Ime']) ."'" : "'neimenovan'") .",
						Address   = ". ($_POST['Address'] != "" ? "'". $db->escape($_POST['Address']) ."'" : NULL) .",
						Phone     = ". ($_POST['Phone'] != "" ? "'". $db->escape($_POST['Phone']) ."'" : NULL) .",
						Email     = ". ($_POST['Email'] != "" ? "'". $db->escape($_POST['Email']) ."'" : NULL) .",
						Nickname  = ". ($_POST['Vzdevek'] != "" ? "'". $db->escape($_POST['Vzdevek']) ."'" : NULL) .",
						". ($_POST['Geslo'] != "" ? "Password = '". $db->escape($xGeslo) ."'," : "") ."
						Settings  = '". $db->escape($Nastavitve) ."',
						LastVisit = '". now() ."',
						MailList  = ". (isset($_POST['Mail']) ? 1 : 0) .",
						ShowEmail = ". (isset($_POST['ShowEmail']) ? 1 : 0) .",
						Signature = ". ($_POST['Signature'] == "" ? NULL : "'". $db->escape(left($_POST['Signature'],500)) ."'") .",
						ICQUIN    = ". ((int)$_POST['ICQ'] ? "'". (int)$_POST['ICQ'] ."'" : NULL) .",
						DisplayName=". (isset($_POST['DisplayName']) ? 1 : 0) .",
						ShowPersonalData=". (isset($_POST['ShowPersonalData']) ? 1 : 0) .",
						Sex       = ". (isset($_POST['Sex']) && $_POST['Sex'] != "" ? "'". $db->escape($_POST['Sex']) ."'" : NULL) .",
						WebPage   = ". ($_POST['WebPage'] != "" ? "'". $db->escape($_POST['WebPage']) ."'" : NULL) ."
					WHERE ID = ". $_SESSION['MemberID']);

				if ( $_SESSION['Email'] != $_POST['Email'] ) {
					setcookie("Email", $_POST['Email'], time()+31536000, $WebPath);
					
					$SMTPServer->AddAddress($_POST['Email'], $_POST['Ime']);
					$SMTPServer->Subject = AppName . " : Sprememba gesla";
					$SMTPServer->AltBody = "Pozdravljeni in dobrodošli v ". AppName ." Diskusije!\n\n".
						"Vaši osebni podatki so naslednji:\n\n".
						"Vzdevek: ". $_POST['Vzdevek'] ."\n".
						"Email: ". $_POST['Email'] ."\n".
						"Geslo: ". $_POST['Geslo'] ."\n".
						"ID: ". $id ."\n\n".
						"Ostale podatke si lahko ogledate in spremenite v nastavitvah, na naslovu:\n".
						$WebServer ."/login.php?login&referer=vpispodatkov.php&querystring=edit\n\n".
						"Svetujemo vam, da si to sporočilo shranite ali spremenite dodeljeno geslo. V primeru težav se obrnite na naslov:\n".
						$PostMaster ."\n\n".
						"Lepo pozdravljeni,\n". AppName;
					$SMTPServer->MsgHTML(
						"<p>Pozdravljeni in dobrodošli v ". AppName ." Diskusije!</p>".
						"<p>Vaši osebni podatki so naslednji:<br><br>".
						"Vzdevek: ". $_POST['Vzdevek'] ."<br>".
						"Email: ". $_POST['Email'] ."<br>".
						"Geslo: ". $_POST['Geslo'] ."<br>".
						"ID: ". $id ."</p>".
						"<p>Ostale podatke si lahko ogledate in spremenite v nastavitvah, na naslovu:<br>".
						$WebServer ."/login.php?login&referer=vpispodatkov.php&querystring=edit</p>".
						"<p>Svetujemo vam, da si to sporočilo shranite ali spremenite dodeljeno geslo. V primeru težav se obrnite na naslov:<br>".
						$PostMaster ."</p>".
						"<p>Lepo pozdravljeni,<br>". AppName ."</p>"
					);
					if ( !$SMTPServer->Send() ) {
						$Error = "Pri pošiljanju podatkov po epošti je prišlo do napake.<br>Vaše geslo se glasi: <b>". $_POST['Geslo'] ."</b>";
					} else {
					}
				}
			break;
			
		}

	}

}

$getMember = getmember($_SESSION['MemberID']);
if ( $getMember )
	$settings = ParseMetadata($getMember->Settings,",");

$Color = isset($settings['Color']) ? $settings['Color'] : "Red";
$Rows  = isset($settings['Rows'])  ? $settings['Rows']  : 10;
$Start = isset($settings['Start']) ? $settings['Start'] : "First";
$Slika = isset($settings['Slika']) ? $settings['Slika'] : "default";
$Edit  = isset($settings['Edit'])  ? $settings['Edit']  : "Full";
$Sort  = isset($settings['Sort'])  ? $settings['Sort']  : "Name";

if ( isset($_GET['Delete']) && (int)$_GET['Delete'] ) 
	delnotify($_GET['Delete'],$_SESSION['MemberID']);


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
function validate(fObj) {
	if (empty(fObj.Vzdevek))	{alert("Vzdevek je obvezen podatek!\n(Lahko je enak imenu.)"); fObj.Vzdevek.focus(); return false;}
	if (!emailOK(fObj.Email))	{alert("Napačen EMAIL NASLOV!"); fObj.Email.focus(); return false;}
	if (empty(fObj.Ime))		{if (confirm("Prosimo, vpišite vsaj ime ali priimek!")) {fObj.Ime.focus(); return false;}}
	if (empty(fObj.Address))	{if (confirm("Prosimo, vpišite vsaj kraj !")) {fObj.Address.focus(); return false;}}
	if (!IsNumeric(fObj.ICQ)&&!empty(fObj.ICQ))	{alert("Vpišite številčen podatek!"); fObj.ICQ.focus(); return false;}
	if (!IsNumeric(fObj.Rows))	{alert("Vpišite številčen podatek!"); fObj.Rows.focus(); return false;}
<?php if ( $_SESSION['MemberID'] ) : ?>
	if (!empty(fObj.Geslo))		{
		if (fObj.Geslo.value.length < 4)	{alert("Geslo ne sme biti krajše kot 4 znake!"); fObj.Geslo.focus(); return false;}
		if (!pwdOk(fObj.Geslo, fObj.Geslo2)){alert("Gesli se ne ujemata ali nista vpisani!"); fObj.Geslo2.focus(); return false;}
	}
<?php endif ?>
	if (!empty(fObj.OldEmail) && fObj.Email.value != fObj.OldEmail.value)	{
		return confirm("Sprememba email naslova povzroči spremembo gesla, ki bo poslano na novi naslov! Ali to res želite?");
	}
	return true;
}
function faceOpen() {
	tmpWnd = window.open('izborface.php','<?php echo AppName ?> Face','width=410,height=370,resizeable=0,scrollbars=1,toolbar=0,status=0,menubar=0,location=0,left='+(winLeft-205)+',top='+(winTop-190));
}
//-->
</SCRIPT>
<?php
echo "</HEAD>\n";
echo "<BODY style=\"background-color:". $BackgColor .";\">\n";
?>
<?php if ( contains($_SERVER['QUERY_STRING'], "New") ) : ?>

<TABLE BORDER="0" CELLPADDING="5" CELLSPACING="0" HEIGHT="100%" WIDTH="100%">
<TR>
	<TD>
	<?php
	// rules of engagement
	if ( FileExists("../_forumRules.php") )
		include_once("../_forumRules.php");
	else
		include_once("_pravila.php");
	?>
	</TD>
</TR>
<TR>
	<TD ALIGN="center">
	<INPUT TYPE="Button" VALUE="Sprejmem" CLASS="but" ONCLICK="document.location.href='vpispodatkov.php?ok'">
	<INPUT TYPE="Button" VALUE="Ne sprejmem" CLASS="but" ONCLICK="window.close();">
	</TD>
</TR>
<TR>
	<TD><BR>
	S klikom na gumb "SPREJMEM" potrjujete, da se strinjate s pogoji. Sami odgovarjate za vse informacije,
	ki prihajajo pod vašim uporabniškim imenom. Prav tako potrjujete, da ne boste pošiljali avtorsko zaščitenih
	informacij, za katere nimate avtorskih pravic ali dovoljenja za objavo. Z uporabo teh Diskusij se obvezujete,
	da ne boste pošiljali vulgarnih, nadležnih, sovražnih in grozilnih pisem ter ne boste ogrožali zasebnosti
	drugih ter kršili zakonov.<BR>
	<BR>
	Če se z navedenim strinjate, kliknite na gumb "SPREJMEM" v nasprotnem primeru kliknite na "NE SPREJMEM".
	</TD>
</TR>
</TABLE>

<?php else : ?>

<FORM ACTION="vpispodatkov.php" METHOD="post" NAME="VpisPodatkov" ONSUBMIT="return validate(this);" ENCTYPE="multipart/form-data">
<TABLE BORDER="0" CELLPADDING="3" CELLSPACING="0" HEIGHT="100%" WIDTH="100%">
	<?php if ( $Error!="" ) : ?>
<TR BGCOLOR="<?php echo $PageColor ?>">
	<TD ALIGN="center" VALIGN="top">
	<FONT COLOR="<?php echo $TxtExColor ?>"><B>Podatki niso vpisani!</B></FONT><BR>
	<SPAN CLASS="a10"><?php echo $Error ?></SPAN>
	</TD>
</TR>
	<?php endif ?>
	<?php if ( $Error=="" && isset($_POST['What']) && $_POST['What']=="Vpis" ) : ?>
<TR BGCOLOR="<?php echo $PageColor ?>">
	<TD>
	<TABLE ALIGN="center" BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="75%">
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD ALIGN="center"><B CLASS="a14"><FONT COLOR="<?php echo $TxtFrColor ?>">Potrdilo</FONT></B></TD>
	</TR>
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD>
		<TABLE ALIGN="center" BORDER="0" CELLPADDING="5" CELLSPACING="0" WIDTH="100%">
		<TR BGCOLOR="<?php echo $PageColor ?>">
			<TD VALIGN="bottom">
			<P ALIGN="center" CLASS="a14"><FONT COLOR="<?php echo $TxtExColor ?>"><B>Podatki so vpisani!</B></FONT></P>
			</TD>
		</TR>
		<TR BGCOLOR="<?php echo $BackgColor ?>">
			<TD VALIGN="top">
			<P ALIGN="left">Geslo in ostali podatki so bili poslani na naslov: <B><?php echo $_POST['Email'] ?></B><BR></P>
			<P ALIGN="left">Kmalu se bo v vašem epoštnem nabiralniku pojavilo sporočilo, ki bo vsebovalo vaše geslo.
			Če takega sporočila ne bi dobili ali pa vam vaš epoštni naslov ne deluje, se lahko
			obrnete na naslov <A HREF="mailto:<?php echo $PostMaster ?>?subject=Geslo"><?php echo $PostMaster ?></A>
			s pripisom, da želite spremeniti geslo oz. da vaš vpisani epoštni naslov ne deluje.<BR></P>
			<P ALIGN="left">Ko boste geslo prejeli, se lahko prijavite <A HREF="login.php?login&reload" TARGET="_parent"><B>tule</B></A>.</P>
			</TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	</TABLE>
	</TD>
</TR>
	<?php else : ?>
<TR>
	<TD VALIGN="top">
	<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
	<TR>
		<TD ALIGN="right" CLASS="a10" WIDTH="15%"><B>Vzdevek</B>:&nbsp;</TD>
		<TD>
		<?php if ( $getMember ) : ?>
		<INPUT NAME="ID" VALUE="<?php echo $getMember->ID ?>" TYPE="Hidden">
		<INPUT TYPE="Text" NAME="Vzdevek" VALUE="<?php echo $getMember->Nickname ?>" SIZE="45" MAXLENGTH="16" READONLY STYLE="border:<?php echo $FrameColor ?> solid 1px;">
		<?php else : ?>
		<INPUT TYPE="Text" NAME="Vzdevek" VALUE="" SIZE="45" MAXLENGTH="16" STYLE="border:<?php echo $FrameColor ?> solid 1px;">
		<?php endif ?>
		</TD>
		<TD ALIGN="center" CLASS="a10" COLSPAN="2" ROWSPAN="4" VALIGN="top">Faca:<BR>
		<INPUT NAME="Slika" TYPE="Hidden" VALUE="<?php echo $Slika ?>"><A HREF="javascript:faceOpen();"><IMG ID="Slika" SRC="px/face/<?php echo $Slika ?>.gif" BORDER="0"></A></TD>
	</TR>
	<TR>
		<TD ALIGN="right" CLASS="a10" VALIGN="baseline"><B>Email</B>:&nbsp;</TD>
		<TD CLASS="a10"><INPUT TYPE="Text" NAME="Email" VALUE="<?php echo $getMember->Email ?>" SIZE="45" MAXLENGTH="64" STYLE="border:<?php echo $FrameColor ?> solid 1px;"><INPUT NAME="OldEmail" VALUE="<?php echo $getMember->Email ?>" TYPE="Hidden"></TD>
	</TR>
	<TR>
		<TD ALIGN="right"><INPUT TYPE="Checkbox" NAME="ShowEmail" <?php if ( $getMember->ShowEmail ) : ?>CHECKED<?php endif ?>></TD>
		<TD CLASS="a10">dovolim objavo mojega email naslova</TD>
	</TR>
	<TR>
		<?php if ( $getMember ) : ?>
		<TD ALIGN="right" CLASS="a10" VALIGN="baseline"><B>Geslo</B> (2x):&nbsp;</TD>
		<TD CLASS="a10">
		<INPUT TYPE="Password" NAME="Geslo" SIZE="20" MAXLENGTH="16" STYLE="border:<?php echo $FrameColor ?> solid 1px;">
		<INPUT TYPE="Password" NAME="Geslo2" SIZE="20" MAXLENGTH="16" STYLE="border:<?php echo $FrameColor ?> solid 1px;"><br>
		<B><FONT COLOR="<?php echo $TxtExColor ?>">*</FONT></B> pustite geslo prazno, če ne želite spremembe
		</TD>
		<?php else : ?>
		<TD CLASS="a10" COLSPAN="3">
		<B>Prvo geslo vam bo dodelil sistem sam, zato je zelo pomembno, da vnesete pravilen epoštni naslov!</B>
		</TD>
		<?php endif ?>
	</TR>
	<TR>
		<TD ALIGN="right" CLASS="a10" NOWRAP>Ime priimek:&nbsp;</TD>
		<TD><INPUT TYPE="Text" NAME="Ime" VALUE="<?php echo $getMember->Name ?>" SIZE="45" MAXLENGTH="64" STYLE="border:<?php echo $FrameColor ?> solid 1px;"></TD>
		<TD ALIGN="right"><INPUT TYPE="Checkbox" NAME="DisplayName" <?php if ( $getMember->DisplayName ) : ?>CHECKED<?php endif ?>> </TD>
		<TD CLASS="a10">prikaži ime namesto vzdevka</TD>
	</TR>
	<TR>
		<TD ALIGN="right" CLASS="a10">Naslov:&nbsp;</TD>
		<TD><INPUT TYPE="Text" NAME="Address" VALUE="<?php echo $getMember->Address ?>" SIZE="45" MAXLENGTH="64" STYLE="border:<?php echo $FrameColor ?> solid 1px;"></TD>
		<TD ALIGN="right"><INPUT TYPE="Checkbox" NAME="ShowPersonalData" <?php if ( $getMember->ShowPersonalData ) : ?>CHECKED<?php endif ?>></TD>
		<TD CLASS="a10">dovolim objavo mojih osebnih podatkov</TD>
	</TR>
	<TR>
		<TD ALIGN="right" CLASS="a10">Telefon:&nbsp;</TD>
		<TD><INPUT TYPE="Text" NAME="Phone" VALUE="<?php echo $getMember->Phone ?>" SIZE="45" MAXLENGTH="20" STYLE="border:<?php echo $FrameColor ?> solid 1px;"></TD>
		<TD ALIGN="right"><INPUT TYPE="Checkbox" NAME="Mail" <?php if ( $getMember->MailList ) : ?>CHECKED<?php endif ?>></TD>
		<TD CLASS="a10" COLSPAN="2">želim prejemati epošto s propagandno vsebino</TD>
	</TR>
	<TR>
		<TD ALIGN="right" CLASS="a10">Spol:&nbsp;</TD>
		<TD><INPUT NAME="Sex" TYPE="Radio" VALUE="M" <?php if ( $getMember->Sex=="M" ) : ?>CHECKED<?php endif ?>> moški
			<INPUT NAME="Sex" TYPE="Radio" VALUE="F" <?php if ( $getMember->Sex=="F" ) : ?>CHECKED<?php endif ?>> ženski</TD>
		<TD ALIGN="right" CLASS="a10">ICQ:&nbsp;</TD>
		<TD><INPUT TYPE="Text" NAME="ICQ" VALUE="<?php echo $getMember->ICQUIN ?>" SIZE="10" MAXLENGTH="10" STYLE="border:<?php echo $FrameColor ?> solid 1px;"></TD>
	</TR>
	<TR>
		<TD ALIGN="right" CLASS="a10">Prikaži največ&nbsp;</TD>
		<TD CLASS="a10"><INPUT TYPE="Text" NAME="Rows" VALUE="<?php echo $Rows ?>" SIZE="3" MAXLENGTH="3" STYLE="border:<?php echo $FrameColor ?> solid 1px;"> sporočil pri pregledu ene teme v Diskusijah.</TD>
		<TD CLASS="a10" COLSPAN="2">Uredi&nbsp;teme:&nbsp;<SELECT NAME="Sort" SIZE="1">
			<OPTION VALUE="Name">po abecedi</OPTION>
			<OPTION VALUE="Date" <?php if ( $Sort=="Date" ) : ?>SELECTED<?php endif ?>>po datumu</OPTION>
		</SELECT></TD>
	</TR>
	<TR>
		<TD ALIGN="right" CLASS="a10" VALIGN="top">Podpis:&nbsp;<BR>
		(se doda k vsem	sporočilom v Diskusijah)</TD>
		<TD><TEXTAREA NAME="Signature" COLS="40" ROWS="4" STYLE="border:<?php echo $FrameColor ?> solid 1px;"><?php echo $getMember->Signature ?></TEXTAREA></TD>
		<TD COLSPAN="2" ROWSPAN="5" VALIGN="top">
		<?php $getNotifys = getmembernotifys($_SESSION['MemberID']) ?>
		<?php if ( count($getNotifys) ) : ?>
		<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="100%" HEIGHT="100%">
		<TR BGCOLOR="<?php echo $FrameColor ?>">
			<TD CLASS="a10"><FONT COLOR="<?php echo $TxtFrColor ?>">&nbsp;<B>Naročene teme</B></FONT></TD>
		</TR>
		<TR BGCOLOR="<?php echo $FrameColor ?>">
			<TD>
			<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" HEIGHT="100%" WIDTH="100%">
			<TR BGCOLOR="<?php echo $BackgColor ?>">
				<TD ALIGN="center"><DIV STYLE="width:100%;height:140px;overflow-y:scroll;">
				<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
			<?php $BgColor=""; foreach ( $getNotifys AS $getNotify ) : ?>
				<?php $BgCol = ($BgCol==$BckHiColor ? $BackgColor : $BckHiColor) ?>
				<TR BGCOLOR="<?php echo $BgCol ?>">
					<TD CLASS="a10"><A HREF="<?php echo $_SERVER['PHP_SELF'] ?>?Delete=<?php echo $getNotify->ID ?>"><?php echo $getNotify->TopicName ?></A></TD>
					<TD ALIGN="right" CLASS="a10"><A HREF="<?php echo$_SERVER['PHP_SELF'] ?>?Delete=<?php echo $getNotify->ID ?>"><IMG SRC="px/trash.gif" WIDTH=12 HEIGHT=12 ALT="Odstrani" BORDER="0"></A></TD>
				</TR>
			<?php endforeach ?>
				</TABLE>
				</DIV></TD>
			</TR>
			</TABLE>
			</TD>
		</TR>
		</TABLE>
		<?php endif ?>
		</TD>
	</TR>
	<TR>
		<TD ALIGN="right" CLASS="a10">Spletna stran:&nbsp;</TD>
		<TD><INPUT TYPE="Text" NAME="WebPage" VALUE="<?php echo $getMember->WebPage ?>" SIZE="45" MAXLENGTH="127" STYLE="border:<?php echo $FrameColor ?> solid 1px;"></TD>
	</TR>
	<TR>
		<TD ALIGN="right"><INPUT TYPE="Checkbox" NAME="Start" <?php if ( $Start=="Last" ) : ?>CHECKED<?php endif ?>></TD>
		<TD CLASS="a10">želim prikaz novejših sporočil ob prvem kliku na temo</TD>
	</TR>
	<TR>
		<TD ALIGN="right"><INPUT TYPE="Checkbox" NAME="Edit" <?php if ( $Edit=="Full" ) : ?>CHECKED<?php endif ?>></TD>
		<TD CLASS="a10">želim uporabljati napreden urejevalnik besedila za vpis sporočil</TD>
	</TR>
	<TR>
		<TD ALIGN="right"><INPUT TYPE="Checkbox" NAME="Auto" <?php if ( isset($_COOKIE['Geslo']) && $_COOKIE['Geslo']!="" ) : ?>CHECKED<?php endif ?> DISABLED></TD>
		<TD CLASS="a10">samodejna prijava ob vstopu v Diskusije</TD>
	</TR>
	<TR>
		<TD CLASS="a10" COLSPAN="4" VALIGN="top">Barva:<BR>
			<TABLE BGCOLOR="<?php echo $BckHiColor ?>" BORDER="0" CELLPADDING="0" CELLSPACING="1" WIDTH="100%">
			<TR>
				<TD BGCOLOR="Red"><INPUT TYPE="Radio" NAME="Barva" VALUE="Red" <?php if ( $Color=="Red" ) : ?>CHECKED<?php endif ?>></TD>
				<TD BGCOLOR="Maroon"><INPUT TYPE="Radio" NAME="Barva" VALUE="Maroon" <?php if ( $Color=="Maroon" ) : ?>CHECKED<?php endif ?>></TD>
				<TD BGCOLOR="Yellow"><INPUT TYPE="Radio" NAME="Barva" VALUE="Yellow" <?php if ( $Color=="Yellow" ) : ?>CHECKED<?php endif ?>></TD>
				<TD BGCOLOR="Olive"><INPUT TYPE="Radio" NAME="Barva" VALUE="Olive" <?php if ( $Color=="Olive" ) : ?>CHECKED<?php endif ?>></TD>
				<TD BGCOLOR="Lime"><INPUT TYPE="Radio" NAME="Barva" VALUE="Lime" <?php if ( $Color=="Lime" ) : ?>CHECKED<?php endif ?>></TD>
				<TD BGCOLOR="Green"><INPUT TYPE="Radio" NAME="Barva" VALUE="Green" <?php if ( $Color=="Green" ) : ?>CHECKED<?php endif ?>></TD>
				<TD BGCOLOR="Aqua"><INPUT TYPE="Radio" NAME="Barva" VALUE="Aqua" <?php if ( $Color=="Aqua" ) : ?>CHECKED<?php endif ?>></TD>
				<TD BGCOLOR="Teal"><INPUT TYPE="Radio" NAME="Barva" VALUE="Teal" <?php if ( $Color=="Teal" ) : ?>CHECKED<?php endif ?>></TD>
				<TD BGCOLOR="Blue"><INPUT TYPE="Radio" NAME="Barva" VALUE="Blue" <?php if ( $Color=="Blue" ) : ?>CHECKED<?php endif ?>></TD>
				<TD BGCOLOR="Navy"><INPUT TYPE="Radio" NAME="Barva" VALUE="Navy" <?php if ( $Color=="Navy" ) : ?>CHECKED<?php endif ?>></TD>
				<TD BGCOLOR="Fuchsia"><INPUT TYPE="Radio" NAME="Barva" VALUE="Fuchsia" <?php if ( $Color=="Fuchsia" ) : ?>CHECKED<?php endif ?>></TD>
				<TD BGCOLOR="Purple"><INPUT TYPE="Radio" NAME="Barva" VALUE="Purple" <?php if ( $Color=="Purple" ) : ?>CHECKED<?php endif ?>></TD>
				<TD BGCOLOR="Silver"><INPUT TYPE="Radio" NAME="Barva" VALUE="Silver" <?php if ( $Color=="Silver" ) : ?>CHECKED<?php endif ?>></TD>
				<TD BGCOLOR="Gray"><INPUT TYPE="Radio" NAME="Barva" VALUE="Gray" <?php if ( $Color=="Gray" ) : ?>CHECKED<?php endif ?>></TD>
			</TR>
			</TABLE>
		</TD>
	</TR>
	</TABLE>
	</TD>
</TR>
<TR>
	<TD ALIGN="center" COLSPAN="2">
	<INPUT TYPE="Button" NAME="" VALUE="&nbsp;&nbsp;Zapri&nbsp;&nbsp;" CLASS="but" ONCLICK="window.close();">
	&nbsp;
	<INPUT TYPE="Submit" NAME="What" VALUE="<?php if ( !$getMember->ID ) : ?>Vpis<?php else : ?>Spremeni<?php endif ?>" CLASS="but">
	</TD>
</TR>
	<?php endif ?>
</TABLE>
</FORM>
<?php endif ?>
</BODY>
</HTML>
