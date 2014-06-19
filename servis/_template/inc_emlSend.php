<?php
/*~ inc_emlSend.php - send message to mailing list
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

$Texts = $db->get_results("SELECT * FROM emlMessagesTxt WHERE emlMessageID=". (int)$_GET['ID']);

if ( $Texts ) foreach ( $Texts as $Text ) {
	$MailList = $db->get_results(
		"SELECT DISTINCT
			EM.Naziv AS Name,
			EM.Email
		FROM
			emlMembers EM
			LEFT JOIN emlMembersGrp EMG
				ON EM.emlMemberID = EMG.emlMemberID
			LEFT JOIN emlMessagesGrp EMSG
				ON EMG.emlGroupID = EMSG.emlGroupID
		WHERE
			EM.Aktiven = 1
			AND EMG.emlGroupID IS NOT NULL
			AND EMSG.emlMessageGrpID IS NOT NULL
			AND EMSG.emlMessageID =". (int)$_GET['ID'] ."
			AND (EM.Jezik IS NULL OR EM.Jezik = '". $Text->Jezik ."')"
	);
	$Lang = $Text->Jezik=='' ? $db->get_var('SELECT Jezik FROM Jeziki WHERE DefLang=1') : $Text->Jezik;

	$Subject = $Text->Naziv;
	$Body    = $Text->Opis;
	// format plaintext body
	$AltBody = preg_replace("/<([\/]*)DIV([^>]*)>/i", "<\1p>", $Body);
	$AltBody = str_ireplace('<li>', '* ', $AltBody);
	$AltBody = str_ireplace('&nbsp;', ' ', $AltBody);
	$AltBody = str_ireplace('&lt;', '<', $AltBody);
	$AltBody = str_ireplace('&gt;', '>', $AltBody);
	$AltBody = preg_replace('/<br.*>/i', '\n', $AltBody);
	$AltBody = preg_replace('/\&(ra|la)quo\;/i', '\"', $AltBody);
	$AltBody = preg_replace('/\&[a-zA-Z]+\;/i', '', $AltBody);
	$AltBody = preg_replace('/<([\/]*)([^>]*)>/i', '', $AltBody); // remove all tags
	// make absolute URLs
	$Body    = preg_replace("/(src=\")/i", '$1'.$WebURL.'/', $Body);
	// convert text sileys into images
	$Body    = ReplaceSmileys($Body, $WebURL ."/pic/");
/*
 * TODO: embeding an image into $Body using the following
	try {
		$thumb = PhpThumbFactory::create($file['data'], array('jpegQuality' => $jpgPct,'resizeUp' => false), true);
		$imageAsString = $thumb->getImageAsString(); 
	} catch (Exception $e) {
	}
	echo $imageAsString!="" ? "<img src=\"data:image/". strtolower($thumb->getFormat()) .";base64,". base64_encode($imageAsString) ."\">" : "";
*/
	// add styling
	if ( file_exists('../template/_mailTemplate.html') ) {
		$Message = file_get_contents('../template/_mailTemplate.html');
	} else {
		$Message = file_get_contents('./_mailTemplate.html');
	}
	$Message = str_replace("#TextColor#", $TextColor, $Message);
	$Message = str_replace("#LinkColor#", $LinkColor, $Message);
	$Message = str_replace("#PageColor#", $PageColor, $Message);
	$Message = str_replace("#TxtFrColor#",$TxtFrColor,$Message);
	$Message = str_replace("#TxtExColor#",$TxtExColor,$Message);
	$Message = str_replace("#FrameColor#",$FrameColor,$Message);
	$Message = str_replace("#FrmExColor#",$FrmExColor,$Message);
	$Message = str_replace("#BackgColor#",$BackgColor,$Message);
	$Message = str_replace("#BckLoColor#",$BckLoColor,$Message);
	$Message = str_replace("#BckHiColor#",$BckHiColor,$Message);
	$Message = str_replace("#PageWidth#", $ContentW,  $Message);
	$Message = str_replace("#ContentW#",  $ContentW,  $Message);
	$Message = str_replace("#WebURL#",    $WebURL,    $Message);
	$Message = str_replace("#MsgBody#",   $Body,      $Message);
	$Message = str_replace("#ID#",        $Text->emlMessageTxtID,        $Message);
	$Message = str_replace("#Title#",     multiLang('<Title>',$Lang),    $Message);
	$Message = str_replace("#SubTitle#",  multiLang('<SubTitle>',$Lang), $Message);
	$Message = str_replace("#CopyRight#", multiLang('<CopyRight>',$Lang),$Message);
		
	// send messages
	if ( $MailList ) {
		$SMTPServer->SetFrom($_SESSION['Email'], $_SESSION['Name']);
		foreach ( $MailList as $User )
			$SMTPServer->AddBCC($User->Email, $User->Name);
		$SMTPServer->Subject = $Subject;
		$SMTPServer->AltBody = $AltBody;
		$SMTPServer->MsgHTML($Message);
		if ( $error = !$SMTPServer->Send() ) {
			$db->query(
				"UPDATE emlMessages
				SET Datum = '".date('Y-n-j H:m:s')."'
				WHERE emlMessageID = ". (int)$_GET['ID']
				);
			// audit action
			$db->query(
				"INSERT INTO SMAudit (
					UserID,
					ObjectID,
					ObjectType,
					Action,
					Description
				) VALUES (
					". $_SESSION['UserID'] .",
					". (int)$_GET['ID'] .",
					'Mailing list',
					'Send mesage',
					'". $db->get_var("SELECT Naziv FROM emlMessages WHERE emlMessageID=". (int)$_GET['ID']) ."'
				)"
				);
		}
//		file_put_contents('message.html',$Message);
		$SMTPServer->ClearAddresses();
	}
}
?>
<DIV CLASS="subtitle">
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<td><div id="ToggleFrame" style="display:none;">&nbsp;<A HREF="javascript:toggleFrame()"><img src="pic/control.frame.gif" height="14" width="14" alt="Preklop celo/zmanjۡno okno" border="0" align="absmiddle" class="icon">&nbsp;List</a></div></td>
	<TD align="right" id="editNote">emlSend&nbsp;</TD>
</TR>
</TABLE>
</DIV>
<DIV ID="divContent" style="padding: 5px;">
<div class="frame" style="display: table;margin: 0 auto;height: 100px;width: 320px;">
<div style="background-color: white;display: table-cell;text-align: center;vertical-align: middle;">
<?php if ( isset($error) && !$error ) : ?>
	<B>Message sent!</B>
<?php else : ?>
	<B CLASS="red">Message NOT sent!</B>
<?php endif ?>
</div>
</div>
</DIV>
