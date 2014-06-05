<?php
/*~ _oddaj.php - post a message
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

if ( !$_SESSION['MemberID'] && !$AllowAnonymous && !contains("Rep,Fwd",$_GET['Act']) ) {
	// anonymous posting not allowed
	die();
}

// cleanup HTML
if ( isset($_POST['Body']) && $_POST['Body'] != "" ) {

	$_POST['Body'] =  str_replace("&scaron;",     "š",      $_POST['Body']);
	$_POST['Body'] =  str_replace("&Scaron;",     "Š",      $_POST['Body']);
	$_POST['Body'] =  str_ireplace("&nbsp;",      " ",      $_POST['Body']);
	//$_POST['Body'] = str_ireplace("</P>",         "</DIV>", $_POST['Body']);
	//$_POST['Body'] = preg_replace("/<P([^>]*)>/i",             "<DIV$1>", $_POST['Body']);
	if ( preg_match("/<[^>]*>/",left($_POST['Body'],500)) == 0 ) {
		// handle PHPBB pseudo HTML
		$_POST['Body'] = preg_replace("/\[([\/]*)B\]/i",                      "<$1B>",                    $_POST['Body']);
		$_POST['Body'] = preg_replace("/\[([\/]*)I\]/i",                      "<$1I>",                    $_POST['Body']);
		$_POST['Body'] = preg_replace("/\[([\/]*)U\]/i",                      "<$1U>",                    $_POST['Body']);
		$_POST['Body'] = preg_replace("/\[QUOTE=([^\]]*)\]/i",                "<BLOCKQUOTE CITE=\"$1\">", $_POST['Body']);
		$_POST['Body'] = preg_replace("/\[([\/]*)QUOTE[^]]*\]/i",             "<$1BLOCKQUOTE>",           $_POST['Body']);
		$_POST['Body'] = preg_replace("/\[LIST[^]]*\](.*)\[\/LIST[^]]*\]/i",  "<LI>$1</LI>",              $_POST['Body']);
		$_POST['Body'] = preg_replace("/\[IMG\](.*)\[\/IMG\]/i",              "<IMG SRC=\"$1\">",         $_POST['Body']);
		$_POST['Body'] = preg_replace("/\[URL=([^]]*)\](.*)\[\/URL[^]]*\]/i", "<A HREF=\"$1\">$2</A>",    $_POST['Body']);
		$_POST['Body'] = preg_replace("/\[URL\](.*)\[\/URL\]/i",              "<A HREF=\"$1\">$1</A>",    $_POST['Body']);
		$_POST['Body'] =  str_replace("\n",                                   "<BR>",                     $_POST['Body']);
	} else {
		$_POST['Body'] =  str_replace("\n",          "", $_POST['Body']);
		$_POST['Body'] =  str_replace("\r",          "", $_POST['Body']);
		$_POST['Body'] = str_ireplace("<o:p></o:p>", "", $_POST['Body']);
	}
	//$_POST['Body'] = preg_replace("/(&|&amp;)*CFID=[0-9]+/i",    "",        $_POST['Body']);
	//$_POST['Body'] = preg_replace("/(&|&amp;)*CFTOKEN=[0-9]+/i", "",        $_POST['Body']);
	//$_POST['Body'] = preg_replace("/<[\/]*FONT[^>]*>/i",         "",        $_POST['Body']);
	//$_POST['Body'] = preg_replace("/<[\/]*SPAN[^>]*>/i",         "",        $_POST['Body']);
	$_POST['Body'] = preg_replace("/<STYLE.*\/STYLE>/i",         "",        $_POST['Body']);
	$_POST['Body'] = preg_replace("/<[\/]*SCRIPT[^>]*>/i",       "",        $_POST['Body']);
	$_POST['Body'] = preg_replace("/<[\/]*IFRAME[^>]*>/i",       "",        $_POST['Body']);
	$_POST['Body'] = preg_replace("/<[\/]*ILAYER[^>]*>/i",       "",        $_POST['Body']);
	$_POST['Body'] = preg_replace("/ +CLASS=[^ >]*/i",           " ",       $_POST['Body']);
	//$_POST['Body'] = preg_replace("/ +STYLE=\"[^\"]*\"/i",       " ",       $_POST['Body']);
	//$_POST['Body'] = preg_replace("/ +WIDTH=[^ >]*/i",           " ",       $_POST['Body']);
	$_POST['Body'] = preg_replace("/[[:space:]]+/i",             " ",       $_POST['Body']);
	$_POST['Body'] = preg_replace("/<P> *<\/P>/i",               "<br>",    $_POST['Body']);
	$_POST['Body'] = preg_replace("/<([\/]*)EM>/i",              "<$1i>",   $_POST['Body']);
	$_POST['Body'] = preg_replace("/<([\/]*)STRONG>/i",          "<$1b>",   $_POST['Body']);
	$_POST['Body'] = preg_replace("/<([\/]*)DIV([^>]*)>/i",      "<$1p$2>", $_POST['Body']);
	$_POST['Body'] = preg_replace("/<BLOCKQUOTE +CITE=([^>]*)>/i", "<BLOCKQUOTE CITE=$1>", $_POST['Body']);
	$_POST['Body'] =  str_replace("> <",                         "><",     $_POST['Body']);
}

$IsModerator = false;
if ( $_SESSION['MemberID'] ) {
	$_POST['Od']  = $_SESSION['Email'];
	$_POST['Ime'] = $_SESSION['Nickname'];
	if ( isset($_POST['NitID']) ) {
		$getModerator = getmoderators($_POST['NitID'],$_SESSION['MemberID']);
		if ( $getModerator && $getModerator->Permissions > 0 )
			$IsModerator = true;
	}
} else {
	if ( isset($_POST['Od']) && $_POST['Od'] != "" ) {
		$getMember = getmemberbyemail($_POST['Od']);
		if ( $getMember )
			$Error = "Niste prijavljeni ali pa ste uporabili email naslov registriranega uporabnika diskusij!";
	} else {
		$_POST['Od'] = "";
	}
	if ( !isset($_POST['Ime']) || $_POST['Ime'] == "" )
		$_POST['Ime'] = "Anonimnež";
}

if ( !($_SESSION['MemberID'] || $AllowAnonymous || contains("Rep,Fwd", $_GET['Act'])) )
	$Error = "Niste prijavljeni, forum pa ne dopušča anonimnih sporočil.";

//--- error handling ---
if ( isset($_POST['Tema']) && trim($_POST['Tema']) == "" && $_GET['Act'] == "New" ) $Error = "Naziv teme ni podan!";
if ( isset($_POST['Tema']) && trim($_POST['Tema']) != "" && $_GET['Act'] == "New" )
	if ( gettopics($_POST['NitID'],$_POST['Tema']) )
		$Error = "Tema s takim naslovom že obstaja!";
if ( isset($_POST['Body']) && trim($_POST['Body']) == "" ) $Error = "Telo besedila je prazno!";
if ( isset($_POST['Za'])   && trim($_POST['Za']) == "" )   $Error = "Prejemnikov email naslov ni podan.";
if ( isset($_POST['Poll']) && (trim($_POST['Q']) == "" || trim($_POST['A1']) == "" || trim($_POST['A2']) == "") ) $Error="Izbrana je bila anketa, vendar ne vpisana.";

//--- ----------------- POŠILJANJE EMAIL SPOROČILA OZ. VPIS V BAZO ---------------- ---
if ( !isset($Error) || $Error == "" ) {

	if ( isset($_POST['NitID']) )
		$getForum = getforum($_POST['NitID']);

	switch ( $_GET['Act'] ) {
	
	//--- process new message ---
	case "New":
		if ( isset($_POST['Tema']) )
			$_POST['TemaID'] = addtopic($_POST['NitID'], $_POST['Tema'], ($_SESSION['MemberID'] ? $_SESSION['MemberID'] : 1));

		if ( isset($_POST['ID']) ) {
			$getMessage      = getmessage($_POST['ID']);
			$_POST['NitID']  = $getMessage->ForumID;
			$_POST['TemaID'] = $getMessage->TopicID;
		}

		//--- upload attached file/image ---
		if ( $getForum && $getForum->AllowFileUploads && (isset($_FILES['File']) && !$_FILES['File']['error']) ) {
			// upload file in $_FILES['File'] to ./datoteke
			$file  = strtolower(str_replace(' ','-',CleanString(basename($_FILES['File']['name']))));
			$path  = 'diskusije/datoteke';
			$name  = left($file,strlen($file)-4);
			$ext   = right($file,4);
			$uploadpath = $StoreRoot .'/'. $path;
			$uploadfile = $uploadpath .'/'. $file;

			if ( $getForum->UploadType == "" || contains($getForum->UploadType,$ext) ) {
				switch ( $ext ) {
					// upload & resize image
					case ".jpg":
					case ".png":
					case ".gif":
						$photo = ImageResize(
							'File',      // $_FILE field
							$uploadpath, // upload path
							'',          // thumbnail prefix
							'',          // original image prefix
							512,         // reduced size
							0,           // thumbnail size
							$jpgPct);    // JPEG quality
						if ( $photo )
							$UploadedFile = $photo['name'];
					break;
			
					// upload other file
					default:
						if ( fileExists($uploadfile) ) {
							// assign random name until one does not exist
							while ( fileExists($uploadpath .'/'. $name . $ext) ) { //NOTE possible infinite loop
								$name = 'rfn'. rand(100000,999999);
							}
							$uploadfile = $uploadpath .'/'. $name . $ext;
						}
						@move_uploaded_file($_FILES['File']['tmp_name'], $uploadfile);
						$size = filesize($uploadfile);
						if ( $size > $getForum->MaxUploadSize*1024 ) {
							@unlink($uploadfile);
						} else
							$UploadedFile = $name . $ext;
					break;
				}
			}
		}

		$db->query("START TRANSACTION");

		//--- insert message ---
		$db->query("INSERT INTO frmMessages (
				MessageDate,
				ForumID,
				TopicID,
				UserName,
				UserEmail,
				MessageBody,
				MemberID,
				IsApproved,
				AttachedFile,
				Icon,
				Locked,
				IPAddr
			) VALUES (
				'". now() ."',
				". (int)$_POST['NitID'] .",
				". (int)$_POST['TemaID'] .",
				'". $db->escape($_POST['Ime']) ."',
				". ($_POST['Od'] != "" ? "'". $db->escape($_POST['Od']) ."'" : "NULL") .",
				'".
					$db->escape($_POST['Body']) .
					($_SESSION['MemberID'] && isset($_POST['Sign']) && $getMember->Signature != "" ? "<DIV>---------------<BR>". $getMember->Signature ."</DIV>" : "") .
				"',
				". ($_SESSION['MemberID'] ? (int)$_SESSION['MemberID'] : "NULL") .",
				". (!$IsModerator && $getForum->ApprovalRequired ? "0" : "1") .",
				". (isset($UploadedFile) && $UploadedFile != "" ? "'". $UploadedFile ."'" : "NULL") .",
				". (trim($_POST['Icon']) == "" ? "NULL" : "'". $db->escape($_POST['Icon']) ."'") .",
				0,
				'". $_SERVER['REMOTE_ADDR'] ."'
			)"
		);

		//--- update messages counter in approval not required ---
		updtopiccount($_POST['TemaID']);
		//if ( $getForum->ApprovalRequired ) {
		//	$db->query("UPDATE frmTopics
		//		SET MessageCount = MessageCount + 1,
		//			LastMessageDate = '". now() ."',
		//			LastPostBy = ". ($_SESSION['MemberID'] ? (int)$_SESSION['MemberID'] : "NULL") ."
		//		WHERE ID=" (int)$_POST['TemaID']
		//	);

		//--- update posts counter ---
		if ( $_SESSION['MemberID'] )
			$db->query("UPDATE frmMembers SET Posts=Posts+1 WHERE ID=". (int)$_SESSION['MemberID']);
		
		//--- create poll ---
		if ( isset($_POST['Poll']) ) {
			$StOdg = 10;
			if ( trim($_POST['A10']) == "" ) $StOdg = 9;
			if ( trim($_POST['A9']) == "" )  $StOdg = 8;
			if ( trim($_POST['A8']) == "" )  $StOdg = 7;
			if ( trim($_POST['A7']) == "" )  $StOdg = 6;
			if ( trim($_POST['A6']) == "" )  $StOdg = 5;
			if ( trim($_POST['A5']) == "" )  $StOdg = 4;
			if ( trim($_POST['A4']) == "" )  $StOdg = 3;
			if ( trim($_POST['A3']) == "" )  $StOdg = 2;
			$db->query("INSERT INTO frmPoll (
					TopicID,
					Question,
					Votes,
					Answers,
					A1, A2, A3, A4, A5, A6, A7, A8, A9, A10
				) VALUES (
					". (int)$_POST['TemaID'] .",
					'". $db->escape(left(preg_replace("/<[^>]*>/","",$_POST['Q']),510)) ."',
					0,
					". $StOdg .",
					'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A1'])) ."',
					'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A2'])) ."',
					". (trim($_POST['A3'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A3'])) ."'") .",
					". (trim($_POST['A4'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A4'])) ."'") .",
					". (trim($_POST['A5'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A5'])) ."'") .",
					". (trim($_POST['A6'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A6'])) ."'") .",
					". (trim($_POST['A7'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A7'])) ."'") .",
					". (trim($_POST['A8'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A8'])) ."'") .",
					". (trim($_POST['A9'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A9'])) ."'") .",
					". (trim($_POST['A10'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A10'])) ."'") ."
				)"
			);
		}

		$db->query("COMMIT");

		//--- notify moderator(s) of the thread if requested ---
		if ( $getForum->NotifyModerator ) {
			$getModerators = getmoderators($_POST['NitID']);
			if ( count($getModerators) ) foreach ( $getModerators AS $getModerator ) {
				$SMTPServer->AddAddress($getModerator->Email, $getModerator->Name);
			}
		}

		//--- notify subscribers of a new post ---
		$getNotifys = gettopicnotifys($_POST['TemaID']);
		if ( count($getNotifys) ) foreach ( $getNotifys AS $getNotify ) {
			$SMTPServer->AddAddress($getNotify->Email, $getNotify->Name);
		}

		//--- send the message ---		
		$getTopic = gettopic($_POST['TemaID']);
		if ( $getForum->NotifyModerator || count($getNotifys) ) {
			$SMTPServer->Subject = $ForumTitle . " : Obvestilo o novem sporočilu";
			$SMTPServer->AltBody = "Pozdravljeni!\n\nNa straneh ". $ForumTitle ." Diskusij je ".
				$_POST['Ime'] ." v niti ". $getForum->ForumName ." na temo ". $getTopic->TopicName .
				" oddal novo sporočilo.\n".
				$WebURL ."/diskusije/?Nit=".$_POST['NitID']."&Tema=".$_POST['TemaID']."\n\n".
				"Lepo pozdravljeni,\n". $ForumTitle;
			$SMTPServer->MsgHTML(
				"<p>Pozdravljeni!</p><p>Na straneh ". $ForumTitle ." Diskusij je ".
				$_POST['Ime'] ." v niti ". $getForum->ForumName ." na temo ". $getTopic->TopicName .
				" oddal novo sporočilo.<br>".
				$WebURL ."/diskusije/?Nit=". $_POST['NitID'] ."&amp;Tema=". $_POST['TemaID'] ."</p>".
				"<p>Lepo pozdravljeni,<br>". $ForumTitle ."</p>"
			);
			$SMTPServer->Send();
		}
		$SMTPServer->ClearAddresses();
	break;

	//--- modify message ---
	case "Edt":
		if ( (int)$_SESSION['MemberID'] ) {
			$getForum = getforum($_POST['NitID']);
			$db->query("START TRANSACTION");
			$db->query("UPDATE frmMessages
				SET MessageBody    = '". $db->escape($_POST['Body']) ."',
					ChangeMemberID = ". (int)$_SESSION['MemberID'] .",
					ChangeDate     = '". now() ."',
					IsApproved     = ". (!$IsModerator && $getForum->ApprovalRequired ? "0" : "1") ."
				WHERE ID=". (int)$_POST['ID'] . (!$IsModerator ? " AND MemberID=". (int)$_SESSION['MemberID'] : "")
			);
/*
			if ( isset($_POST['Poll']) ) {
				$StOdg = 10;
				if ( trim($_POST['A10']) == "" ) $StOdg = 9;
				if ( trim($_POST['A9']) == "" )  $StOdg = 8;
				if ( trim($_POST['A8']) == "" )  $StOdg = 7;
				if ( trim($_POST['A7']) == "" )  $StOdg = 6;
				if ( trim($_POST['A6']) == "" )  $StOdg = 5;
				if ( trim($_POST['A5']) == "" )  $StOdg = 4;
				if ( trim($_POST['A4']) == "" )  $StOdg = 3;
				if ( trim($_POST['A3']) == "" )  $StOdg = 2;
				$db->query("UPDATE frmPoll
					SET Question = '". $db->escape(left(preg_replace("/<[^>]*>/","",$_POST['Q']),510)) ."',
						Answers = ". $StOdg .",
						A1 = '". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A1'])) ."',
						A2 = '". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A2'])) ."',
						A3 = ". (trim($_POST['A3'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A3'])) ."'") .",
						A4 = ". (trim($_POST['A4'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A4'])) ."'") .",
						A5 = ". (trim($_POST['A5'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A5'])) ."'") .",
						A6 = ". (trim($_POST['A6'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A6'])) ."'") .",
						A7 = ". (trim($_POST['A7'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A7'])) ."'") .",
						A8 = ". (trim($_POST['A8'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A8'])) ."'") .",
						A9 = ". (trim($_POST['A9'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A9'])) ."'") .",
						A10 = ". (trim($_POST['A10'])=="" ? "NULL" : "'". $db->escape(preg_replace("/<[^>]*>/","",$_POST['A10'])) ."'") .",
					WHERE TopicID=". (int)$_POST['TemaID']
				);
			}
*/
			$db->query("COMMIT");
		} else {
			$Error = "Neveljaven uporabnik!";
		}
	break;

	//--- private message ---
	case "Pvt":
		if ( (int)$_SESSION['MemberID'] ) {
			$db->query("START TRANSACTION");
			if ( isset($_POST['PvtID']) )
				updpvtmessage($_SESSION['MemberID'],$_POST['PvtID'],"reply");

			$db->query("INSERT INTO frmPvtMessages (
					FromID,
					ToID,
					TopicID,
					MessageSubject,
					MessageBody,
					MessageDate
				) VALUES (
					". (int)$_SESSION['MemberID'] .",
					". (int)$_POST['ToID'] .",
					". ($_POST['TemaID'] ? (int)$_POST['TemaID'] : "NULL") .",
					'". $db->escape($_POST['Tema']) ."',
					'". $db->escape($_POST['Body']) ."',
					'". now() ."'
				)"
			);
			$db->query("COMMIT");

			//--- notify users of a new private message ---
			$getMember = getmember($_POST['ToID']);
			if ( $getMember ) {
				$SMTPServer->AddAddress($getMember->Email, $getMember->Name);
				$SMTPServer->Subject = $ForumTitle . " : Novo zasebno sporočilo";
				$SMTPServer->AltBody = "Pozdravljeni!\n\nNa straneh ". $ForumTitle ." Diskusij ste prejeli novo zasebnos sporočilo.\n".
					$WebURL ."/diskusije/\n\n".
					"Lepo pozdravljeni,\n". $ForumTitle;
				$SMTPServer->MsgHTML(
					"<p>Pozdravljeni!</p><p>Na straneh ". $ForumTitle ." Diskusij ste prejeli novo zasebno sporočilo.<br>".
					"<a href=\"". $WebURL ."/diskusije/\">". $WebURL ."/diskusije/</a></p>".
					"<p>Lepo pozdravljeni,<br>". $ForumTitle ."</p>"
				);
				$SMTPServer->Send();
				$SMTPServer->ClearAddresses();
			}
		} else {
			$Error = "Neveljaven uporabnik!";
		}
	break;

	//--- report inappropriate message ---
	case "Rpt":
		if ( (int)$_SESSION['MemberID'] ) {
			$Body = "<p>Uporabnik je prijavil neprimerno sporočilo!</p><p>Ogledate si ga lahko na naslovu:<br>".
				    "<a href=\"". $WebURL ."/diskusije/?ID=". $_GET['ID'] ."\" TARGET=\"_blank\">".
				    $WebURL ."/diskusije/?ID=" . $_GET['ID'] ."</p>";

			$getMessage    = getmessage($_GET['ID']);
			$getModerators = getmoderators($getMessage->ForumID);
			$db->query("START TRANSACTION");
			if ( $getMessage && count($getModerators) ) foreach ( $getModerators AS $getModerator ) {
				$db->query("INSERT INTO frmPvtMessages (
						FromID,
						ToID,
						TopicID,
						MessageSubject,
						MessageBody,
						MessageDate
					) VALUES (
						". (int)$_SESSION['MemberID'] .",
						". (int)$getModerator->ID .",
						". (int)$getMessage->TopicID .",
						'Neprimerno sporočilo',
						'". $db->escape($Body) ."',
						'". now() ."'
					)"
				);
				$SMTPServer->AddAddress($getModerator->Email, $getModerator->Name);
			}
			$db->query("COMMIT");

			//--- send emails ---			
			if ( $getMessage && count($getModerators) ) {
				$SMTPServer->Subject = $ForumTitle . " : Novo zasebno sporočilo";
				$SMTPServer->AltBody = "Pozdravljeni!\n\nNa straneh ". $ForumTitle ." Diskusij ste prejeli novo zasebnos sporočilo.\n".
					$WebURL ."/diskusije/\n\n".
					"Lepo pozdravljeni,\n". $ForumTitle;
				$SMTPServer->MsgHTML(
					"<p>Pozdravljeni!</p><p>Na straneh ". $ForumTitle ." Diskusij ste prejeli novo zasebno sporočilo.<br>".
					"<a href=\"". $WebURL ."/diskusije/\">". $WebURL ."/diskusije/</a></p>".
					"<p>Lepo pozdravljeni,<br>". $ForumTitle ."</p>"
				);
				$SMTPServer->Send();
			}
			$SMTPServer->ClearAddresses();
		} else {
			$Error = "Neveljaven uporabnik!";
		}
	break;

	//--- email a message ---
	case "Rep":
	case "Fwd":
		$getMessage = getmessage($_POST['ID']);
		$SMTPServer->MsgHTML(
"<HTML>
<HEAD>
<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">
<STYLE>
A { text-decoration: none; }
A:Visited  { text-decoration: none; }
A:Active  { text-decoration: none; }
A:Hover { text-decoration: underline; }
BODY { font-family: 'Verdana', 'Arial', 'Helvetica'; font-size: 12px; }
TD { font-family: 'Verdana', 'Arial', 'Helvetica'; font-size: 12px; }
</STYLE>
</HEAD>
<BODY BGCOLOR=\"". $PageColor ."\" TEXT=\"". $TextColor ."\" LINK=\"". $LinkColor ."\" ALINK=\"". $LinkColor ."\" VLINK=\"". $LinkColor ."\">
<TABLE ALIGN=\"center\" BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"570\">
<TR><TD>Pozdravljeni!<BR><BR>
To sporočilo vam je iz ". $ForumTitle ." Diskusij poslal <B><A HREF=\"mailto:". $_POST['Od'] ."\">". $_POST['Ime'] ."</A></B>.<BR>
Če želite, si lahko originalno in vsa ostala sporočila ogledate na naslovu:<BR>
<A HREF=\"". $WebURL ."/diskusije/?Nit=". $getMessage->ForumID ."&amp;Tema=". $getMessage->TopicID ."\">". $WebURL ."/diskusije/?Nit=". $getMessage->ForumID ."&amp;Tema=". $getMessage->TopicID ."</A><BR><BR>
</TD></TR>
<TR BGCOLOR=\"". $FrameColor ."\"><TD HEIGHT=\"20\">&nbsp;<FONT COLOR=\"". $TxtFrColor ."\">Sporočilo iz ". $ForumTitle ." Diskusij</FONT></TD></TR>
<TR BGCOLOR=\"". $FrameColor ."\"><TD>
<TABLE BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"1\" WIDTH=\"100%\">
<TR><TD BGCOLOR=\"". $BckHiColor ."\">
<BLOCKQUOTE STYLE=\"border-left:black 2px solid; margin-left:5px; margin-right:0px; padding-left:5px; padding-right:0px\">
<DIV STYLE=\"background-color:". $BckLoColor ."\">Original napisal: <B>". $getMessage->UserName ."</B>, ". formatDate($getMessage->MessageDate,"j.n.y \o\b H:i") ."</DIV>
". $getMessage->MessageBody ."
</BLOCKQUOTE>
". $_POST['Body'] ."
</TD></TR>
<TR><TD ALIGN=\"center\" BGCOLOR=\"". $BackgColor ."\">
<A HREF=\"". $WebURL ."/diskusije/oddaj.php?Act=New&amp;ID=". $getMessage->ID ."\">Odgovori v diskusije</A> |
<A HREF=\"". $WebURL ."/ddiskusije/?Nit=". $getMessage->ForumID ."&amp;Tema=". $getMessage->TopicID ."\">Ogled celotne teme</A>
</TD></TR>
</TABLE>
</TD></TR>
</TABLE>
</BODY>
</HTML>");
		$SMTPServer->AltBody =
"Pozdravljeni!\n
To sporočilo vam je iz ". $ForumTitle ." Diskusij poslal ". $_POST['Ime'] ." (". $_POST['Od'] .").
Če želite, si lahko originalno in vsa ostala sporočila ogledate na naslovu:\n
". $WebURL ."/diskusije/?Nit=". $getMessage->ForumID ."&amp;Tema=". $getMessage->TopicID ."\n
> Original napisal: ". $getMessage->UserName .", ". formatDate($getMessage->MessageDate,"j.n.y \o\b H:i") ."
> -----------------------------------------------------------------------
> ". preg_replace("/<[^>]*>/", "", preg_replace("/(<\/DIV>|<BR>|<\/P>)/i", "\n> ", $getMessage->MessageBody)) ."
> -----------------------------------------------------------------------\n
". preg_replace("/<[^>]*>/", "", preg_replace("/(<\/DIV>|<BR>|<\/P>)/i", "\n", $_POST['Body']));

		$SMTPServer->AddAddress($_POST['Za']);
		$SMTPServer->AddReplyTo($_POST['Od'],$_POST['Ime']);
		$SMTPServer->Subject = $_POST['Subj'];
		$SMTPServer->Send();
		$SMTPServer->ClearAddresses();
		$SMTPServer->ClearReplyTos();
	break;
	
	default:
		$Error = "Neveljavna akcija!";
	break;

	}
}

echo "<!DOCTYPE HTML>\n";
echo "<HTML>\n";
echo "<HEAD>\n";
$TitleText = $ForumTitle ." : Oddaja sporočila";
include_once( "../_htmlheader.php" );
echo "</HEAD>\n";
echo "<BODY>\n";
?>
<TABLE ALIGN="center" BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH="100%" HEIGHT="100%">
<TR>
	<TD ALIGN="center" HEIGHT="99%" VALIGN="middle">
	<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="470">
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD ALIGN="left" HEIGHT="20">&nbsp;<B><FONT COLOR="<?php echo $TxtFrColor ?>">Obvestilo</FONT></B></TD>
	</TR>
	<TR BGCOLOR="<?php echo $FrameColor ?>">
		<TD>
		<TABLE BORDER="0" CELLPADDING="10" CELLSPACING="1" HEIGHT="270" WIDTH="100%">
		<TR>
			<TD ALIGN="center" BGCOLOR="<?php echo $BackgColor ?>" VALIGN="middle">
		<?php if ( isset($Error) && $Error != "" ) : ?><BR><BR>
			<P><FONT COLOR="<?php echo $TxtExColor ?>"><B CLASS="a14">Sporočilo ni oddano!</B></FONT></P>
			<P><B><?php echo $Error ?></B></P>
			<BR><BR><BR><BR>
			<P ALIGN="justify" CLASS="a10">
			Če ste že včlanjeni in prijavljeni, je možno, da se je časovna omejitev za oddajo iztekla.
			V takem primeru kliknite <A HREF="javascript:loginOpen('login.php?login&reload');">tule</A>
			in po prijavi klinite gumb <B>Retry</B> oz. <B>Ponovi</B>)
			</P>
		<?php else : ?>
			<P CLASS="a14"><B>Sporočilo je bilo oddano!</B></P>
			<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
			setTimeout("tmp=window.close()",1000);
			<?php if ( contains("Edt,New",$_GET['Act']) ) : ?>
			window.opener.location.assign(window.opener.location.href);
			<?php endif ?>
			window.opener.focus();
			</SCRIPT>
		<?php endif ?>
			</TD>
		</TR>
		</TABLE>
		</TD>
	</TR>
	</TABLE>
	</TD>
</TR>
</TABLE>
<?php
echo "</BODY>\n";
echo "</HTML>\n";
?>
