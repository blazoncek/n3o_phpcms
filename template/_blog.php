<?php
/* _blog.php - Diary page, last records displayed first.
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

//-----------------------
// BLOG parameters
$BlogMaxPosts = 7;
$BlogMaxDays = 30;
$CommentsAllowed = false;
$x = $db->get_row(
	"SELECT
		S.SifNVal1 AS MaxPosts,
		S.SifNVal2 AS MaxDays,
		S.SifLVal1 AS CommentsAllowed
	FROM
		Sifranti S
		LEFT JOIN SifrantiTxt ST ON S.SifrantID=ST.SifrantID
	WHERE
		S.SifrCtrl = 'PARA'
		AND
		S.SifrText = 'BlogPosts'
		AND
		(ST.Jezik='$lang' OR ST.Jezik IS NULL)
	LIMIT 1"
	);
if ( $x->MaxPosts != "" ) $BlogMaxPosts = $x->MaxPosts;
if ( $x->MaxDays == "" )  $BlogMaxDays = $x->MaxDays;
if ( $x->CommentsAllowed ) $CommentsAllowed = (bool) $x->CommentsAllowed;

//-----------------------
// detect comment post and insert one
$CaptchaOK = $db->get_var("SELECT count(*) FROM frmParameters WHERE ParamName LIKE 'Captcha%'") == 0;
if ( isset($_POST['CaptchaName']) && isset($_POST['CaptchaVal']) ) {
	$CaptchaOK = $db->get_var("SELECT ParamValue FROM frmParameters WHERE ParamName='". $_POST['CaptchaName'] ."'") === $_POST['CaptchaVal'];
}

if ( isset($_POST['MessageBody']) && $_POST['MessageBody'] != ""  && $CaptchaOK ) {
	// cleanup HTML
	$_POST['MessageBody'] = str_replace("< ", "&lt; ",  $_POST['MessageBody']);
	$_POST['MessageBody'] = str_replace(" >", " &gt;",  $_POST['MessageBody']);
	$_POST['MessageBody'] = preg_replace("/<([^>]*)>/i", '', $_POST['MessageBody']); // remove all HTML tags
	$_POST['MessageBody'] = substr($_POST['MessageBody'], 0, 512); // shorten the text
	$_POST['MessageBody'] = str_replace("'",  "&#39;",  $_POST['MessageBody']);
	$_POST['MessageBody'] = str_replace("\"", "&quot;", $_POST['MessageBody']);
	$_POST['MessageBody'] = str_ireplace("[b]",  "<b>",  $_POST['MessageBody']);
	$_POST['MessageBody'] = str_ireplace("[/b]", "</b>", $_POST['MessageBody']);
	$_POST['MessageBody'] = str_ireplace("[i]",  "<i>",  $_POST['MessageBody']);
	$_POST['MessageBody'] = str_ireplace("[/i]", "</i>", $_POST['MessageBody']);
	$_POST['MessageBody'] = str_ireplace("\n",   "<br>", $_POST['MessageBody']);
	$_POST['MessageBody'] = preg_replace("/[[:space:]]+/", " ", $_POST['MessageBody']);

	// get forum details (comments are stored in forum data)
	$getTopics = $db->get_row("SELECT T.ForumID, T.TopicName FROM frmTopics T WHERE T.ID=". (int)$_POST['ID']);

	if ( $getTopics ) {
		// insert comment into database
		$db->query("
			INSERT INTO frmMessages (
				ForumID,
				TopicID,
				UserName,
				UserEmail,
				MessageDate,
				MessageBody,
				IPAddr
			) VALUES (
				$getTopics->ForumID," .
				(int)$_POST['ID'] . "," .
				"'". (($_POST['UserName'] != "") ? $db->escape($_POST['UserName']) : "Anonymous") ."',".
				(($_POST['UserEmail'] != "") ? "'". $db->escape($_POST['UserEmail']) . "'" : "NULL") .",".
				"'". date("Y-m-d H:i:s") ."',".
				"'". $db->escape($_POST['MessageBody']) ."'," .
				"'". $db->escape($_SERVER['REMOTE_ADDR']) ."'
			)"
			);

		// notify moderator of the thread if requested
		$Moderators = $db->get_results(
			"SELECT
				M.Email,
				M.Name
			FROM
				frmModerators MO
					LEFT JOIN frmMembers M ON MO.MemberID = M.ID
			WHERE
				MO.ForumID = " .(int)$getTopics->ForumID
			);
		if ( $Moderators ) foreach ( $Moderators as $Moderator ) {
			$SMTPServer->AddAddress($Moderator->Email, $Moderator->Name);
			$SMTPServer->Subject = AppName ." : Nov komentar v blogu";
			$SMTPServer->AltBody = "Pozdravljeni!\n\nV Blog je ".
				(($_POST['UserName'] != "") ? $_POST['UserName'] : "Anonymous").
				" na sporočilo ". $getTopics->TopicName ." oddal nov komentar.\n\n".
				"Komentar se glasi:\n". $_POST['MessageBody'];
			$SMTPServer->MsgHTML( "<p>Pozdravljeni!</p><p>V Blog je <b>".
				(($_POST['UserName'] != "") ? $_POST['UserName'] : "Anonymous").
				"</b> na sporočilo <i>". $getTopics->TopicName ."</i> oddal nov komentar.</p>".
				"<p>Komentar se glasi:<br>". $_POST['MessageBody'] ."</p>" );
			if ( !$SMTPServer->Send() )
				echo "<!-- mail send error (". $Moderator->Email .") -->\n";
			$SMTPServer->ClearAddresses();
		}
	}
}

/* not needed for Blog
// category description
include("__category.php");
*/

if ( isset($_GET['ID']) && (int)$_GET['ID'] != 0 ) {
//-----------------------
// display a single post
//-----------------------
	
	echo "<div class=\"post\" id=\"entry-". (int)$_GET['ID'] ."\">\n";

	// display post image
	if ( $Teksti && $Teksti[0]->Slika != "" ) {
		try { // to get image size
			$thumb = PhpThumbFactory::create($StoreRoot ."/media/besedila/". $Teksti[0]->Slika);
			$size = $thumb->getCurrentDimensions();
			echo "\t<img src=\"$WebPath/media/besedila/". $Teksti[0]->Slika ."\" alt=\"\" border=0 class=\"frame\" style=\"width:".$size['width']."px;height:".$size['height']."px;\">\n";
		} catch (Exception $e) {
			echo "\t<!-- Error getting image size! -->\n";
			echo "\t<img src=\"$WebPath/media/besedila/". $Teksti[0]->Slika ."\" alt=\"\" border=0 class=\"frame\" retina=\"no\">\n";
		}
	}

	$j = 0;
	// display a single post (comprised of multiple texts)
	if ( $Teksti ) foreach( $Teksti as $Tekst ) {
		
		echo "\t<div class=\"title\">\n";
		// display post title			
		if ( left($Tekst->Naslov,1) != '.' ) {
			echo "\t".($j==0 ? "<h1>" : "<h2>")."";
			echo $Tekst->Naslov;
			echo ($j==0 ? "</h1>" : "</h2>")."\n";
		}
		// display post data
		if ( $j == 0 ) {
			// set Tweet text
			$TweetText = (left($Tekst->Naslov,1)!='.' ? $Tekst->Naslov : "Tweet text placeholder");
			if ( $Tekst->TwitterName != '' ) $TwitterName = $Tekst->TwitterName;

			// display text date
			echo "\t<div class=\"author\">";
			echo multiLang('<PostedBy>', $lang) ." ";
			if ( $Tekst->TwitterName != "" )
				echo "<a href=\"https://twitter.com/". $Tekst->TwitterName ."\" target=\"_blank\">";
			echo $Tekst->Name;
			if ( $Tekst->TwitterName != "" )
				echo "</a>";
			echo " ". date("j.n.Y \@ H:i", sqldate2time($Tekst->DatumObjave));
			echo "</div>\n";

			// display assigned tags
			if ( $Tags ) {
				$kat = $TextPermalinks ? ($IsIIS ? "$WebFile/" : ''). "$KatText/" : '?kat='. $_GET['kat'];
				echo "\t<div class=\"tags\">";
				echo "<img src=\"$WebPath/pic/tags.png\" alt=\"\" border=\"0\" class=\"symbol\"> ";
				echo multiLang('<Tags>', $lang) .": ";
				for ( $i=0; $i<count($Tags); $i++ ) {
					$tag = ($TextPermalinks) ? 'TAG'. $Tags[$i]->TagID .'/' : "&amp;tag=". $Tags[$i]->TagID;
					echo "<a href=\"$WebPath/$kat". $tag ."\">";
					echo $Tags[$i]->TagName;
					echo "</a>";
					if ( $i < count($Tags)-1 ) echo ", ";
				}
				echo "</div>\n";
			}
		}
		
		// display subtitle
		if ( !empty($Tekst->Podnaslov) && left($Tekst->Podnaslov,1) != '.' ) {
			echo "\t<h3>". $Tekst->Podnaslov ."</h3>\n";
		}
		// display text abstract
		if ( $Tekst->Povzetek != "" ) {
			$abstract = str_replace("\n","<br>",$Tekst->Povzetek);
			$abstract = str_replace("&quot;", "\"", $abstract);
			$abstract = ReplaceSmileys(PrependImagePath($abstract, "$WebPath/"), "$WebPath/pic/");
			echo "\t<div class=\"abstract\"><b>". $abstract ."</b></div>\n";
		}
		echo "\t</div>\n";

		// display text content
		echo "\t<div class=\"body\">\n";
		$Bes = $Tekst->Opis;
		// correct escaped quotes (some PHP/MySQL combos)
		$Bes = str_replace("\\\"","\"",$Bes);
		// implement Google Maps
		if ( preg_match_all("/\[googlemaps\]([^[]+)\[\/googlemaps\]/i", $Bes, $locations) ) {
			// $locations = {{'[googlemaps]location1[/googlemaps]',...},{'location1',...}}
			$gmaps = '<iframe class="googlemaps" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=###LOCATION###&amp;ie=UTF8&amp;t=m&amp;z=15&amp;output=embed"></iframe><br><small><a href="https://maps.google.com/maps?q=###LOCATION###&amp;ie=UTF8&amp;t=m&amp;z=15&amp;source=embed">Prikaži večji zemljevid</a></small>';
			if ( count($locations[1]) > 0 ) foreach ( $locations[1] as $location ) {
				$Bes = str_ireplace('[googlemaps]'. $location .'[/googlemaps]', str_ireplace('###LOCATION###', $location, $gmaps), $Bes);
			}
		}
		// fix images for permalinks
		$Bes = PrependImagePath($Bes, "$WebPath/");
		// add <A HREF=...> to images with larger version (in ./large folder)
		$Bes = AddLightboxLink($Bes, $Tekst->ID);
		// replace text smilies with images
		$Bes = ReplaceSmileys($Bes, "$WebPath/pic/");
		echo "$Bes\n";
		echo "\t</div>\n";
		
		$j++;
	}
	echo "\t</div>\n"; // post

	// display gallery
	if ( count($Galerija) > 0 ) {
		echo "<div class=\"gallery fence\">\n";
		echo "<ul id=\"Gallery\">\n";
		$i = 0;
		foreach ( $Galerija as $Slika ) {

			// determine file (sPath) and URL (rPath) path
			$sFile = $Slika->Datoteka;
			$rPath = $WebPath   ."/". dirname($sFile);
			$sPath = $StoreRoot ."/". dirname($sFile);
			$sFile = basename($sFile);
			// file title exists?
			$sName = $Slika->Naslov != "" ? $Slika->Naslov : $sFile;
	
			echo "\t<li>";
			// add link tag
			echo "<A HREF=\"$rPath/". (fileExists("$sPath/large/".$sFile)?"large/":"") ."$sFile\" ". ($Mobile?" REL=\"external\"":"CLASS=\"fancybox\" REL=\"lightbox_gal\"") ." TITLE=\"$sName\">";

			// display image thumbnail
			if ( fileExists($sPath ."/thumbs/". $sFile) ) {
				// get metadata
				$Meta = ParseMetadata($Slika->Meta);
				$IMG_WIDTH  = (int)$Meta['tw'];	// thumbnail width
				$IMG_HEIGHT = (int)$Meta['th']; // thumbnail height
				unset($Meta);
				// display existing thumbnail
				echo "<IMG SRC=\"$rPath/thumbs/$sFile\" alt=\"\" BORDER=0 retina=\"no\">";
			} else {
				// try to create thumbnail on the fly
				try {
					$thumb = PhpThumbFactory::create($sPath."/".((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? DecodeUTF8($sFile) : $sFile));
					$thumb->adaptiveResize($DefThumbSize, $DefThumbSize);
					$imageAsString = $thumb->getImageAsString(); 
					echo "<IMG SRC=\"data:image/". strtolower($thumb->getFormat()) .";base64,". base64_encode($imageAsString) ."\" ALT=\"$sName\" BORDER=\"0\" class=\"thumb\" retina=\"no\">";
				} catch (Exception $e) {
					// display missing thumbnail image
					echo "<IMG SRC=\"$WebPath/pic/nislike_112.png\" ALT=\"\" BORDER=\"0\" class=\"thumb\">";
				}
			}
	
			// add closing link tag
			echo "</A>";
			echo "</li>\n";
		}
		echo "</ul>";
		echo "</div>\n";
	}

	// display attachments for non-mobile clients
	if ( !$Mobile ) {
		if ( count($Media) > 0 ){
			echo "<div class=\"related\">\n";
			echo "\t<div class=\"head\">". multiLang("<Attachments>", $lang) ."</div>\n";
			echo "\t<div class=\"body\">\n";
			foreach ( $Media as $File ) {
				if ( $File->Tip == 'GPX' ) {
					echo "\t<div id=\"map_". $File->Ime ."\" class=\"googlemapswide\"></div>\n";
					echo "<script>";
					echo "initialize_map('map_". $File->Ime ."','". $WebURL .'/media/'. $File->Datoteka ."');";
					echo "</script>\n";
				} else {
					echo "\t<div class=\"listitem\">";
					echo "<A HREF=\"$WebPath/media/$File->Datoteka\" TITLE=\"". sprintf("%4.1f", ((float)$File->Velikost)/1024) ."kB\" TARGET=\"_blank\">";
					if ( $File->Slika != "" )
						echo "<IMG SRC=\"$WebPath/media/media/". (fileExists($StoreRoot.'/media/media/thumbs/'.$File->Slika) ? 'thumbs/' : '') .$File->Slika ."\" ALIGN=\"right\" ALT=\"\" CLASS=\"thumb\">";
					if ( $File->Naslov != "" && left($File->Naslov,1) != "." )
						echo "<B>$File->Naslov</B>";
					elseif ( $File->Ime != "" )
						echo "<B>$File->Ime</B>";
					echo ($File->Opis!='' ? '<div class="a9">'. strip_tags($File->Opis) .'</div>' : '');
					echo "</A>";
					echo "</div>\n";
				}
			}
			echo "\t</div>\n";
			echo "</div>\n";
		}
	}

	// display additional content (just for 1st text in post)
	if ( $Dodatni && count($Dodatni) > 0 ) {
		echo "<div class=\"related\">\n";
		echo "\t<div class=\"head\">". multiLang("<SeeAlso>", $lang) ."</div>\n";
		echo "\t<div class=\"body\">";
		foreach ( $Dodatni as $Dodatno ) {
			// find first category ID of text
			$Kat = $db->get_row(
				"SELECT
					KB.KategorijaID,
					K.Ime,
					B.Tip
				FROM
					KategorijeBesedila KB
					LEFT JOIN Kategorije K
						ON KB.KategorijaID = K.KategorijaID
					LEFT JOIN Besedila B
						ON KB.BesediloID = B.BesediloID
				WHERE
					KB.BesediloID = ". (int)$Dodatno->ID ."
				ORDER BY
					KB.ID
				LIMIT 1"
				);

			$kat = $TextPermalinks ? ($IsIIS ? "$WebFile/" : ''). $Kat->Ime .'/' : '?kat='. $Kat->KategorijaID;
			$bid = $TextPermalinks ? $Dodatno->Ime .'/' : '&amp;ID='. $Dodatno->ID;
			echo "<a href=\"$WebPath/$kat". $bid ."\">". $Dodatno->Naslov ."</a>";
		}
		echo "</div>\n";
		echo "</div>\n";
	}

	// display comments
	if ( $Teksti && count($Teksti) > 0 && $Teksti[0]->ForumTopicID != 0 ) {
		$Comments = $db->get_results(
			"SELECT
				UserName,
				UserEmail,
				MessageBody,
				MessageDate
			FROM
				frmMessages
			WHERE
				TopicID = ". (int)$Teksti[0]->ForumTopicID ."
				AND IsApproved = 1
			ORDER BY
				MessageDate"
			);
		
		if ( $CommentsAllowed || count($Comments) > 0 ) {
			echo "<DIV CLASS=\"comment\">\n";
			echo "<div class=\"head\">". multiLang('<Comments>', $lang) ."</div>\n";
			// display confirmation about message post
			if ( isset($_POST['MessageBody']) && $_POST['MessageBody'] != "" ) 
				echo "<DIV CLASS=\"body\">". multiLang('<MessageSent>', $lang) ."</DIV>\n";
			// display actual comments
			if ( count($Comments) > 0 ) {
				echo "<DIV CLASS=\"body\">\n";
				foreach ( $Comments as $Comment ) {
					//if ( $Comment->UserEmail != "" )
					//	echo "<A HREF=\"mailto:$Comment->UserEmail\">";
					echo "<B>$Comment->UserName</B>";
					//if ( $Comment->UserEmail != "" )
					//	echo "</A>";
					echo "<BR>\n";
					echo "<DIV CLASS=\"a9\"><I>". date('j.n.Y \@ G:i', sqldate2time($Comment->MessageDate)) ."</I></DIV>\n";
					echo "<p>$Comment->MessageBody</p>\n";
				}
				echo "</DIV>\n";
			}
			// comment entry fields
			if ( $CommentsAllowed ) {
				$kat = ($TextPermalinks) ? ($IsIIS ? "$WebFile/" : ''). $KatText .'/' : '?kat='. $_GET['kat'];
				$bid = ($TextPermalinks) ? $Teksti[0]->Ime .'/' : '&ID='. (int)$_GET['ID'];
				echo "<DIV CLASS=\"body\">\n";
				echo "<FORM ACTION=\"$WebPath/$kat". $bid ."\" METHOD=\"post\">\n";
				echo "<input name=\"ID\" value=\"". $Teksti[0]->ForumTopicID ."\" type=\"Hidden\">\n";
				echo "<p><input name=\"UserName\" type=\"Text\" size=\"22\">";
				echo "<label for=\"UserName\">". multiLang('<Name>', $lang) ."</label></p>\n";
				echo "<p><input name=\"UserEmail\" type=\"Text\" size=\"22\">";
				echo "<label for=\"UserEmail\">". multiLang('<Email>', $lang) ."</label></p>\n";
				echo "<p><textarea name=\"MessageBody\" cols=\"40\" rows=\"4\"></textarea></p>\n";
				$Captchas = $db->get_col("SELECT ParamName FROM frmParameters WHERE ParamName LIKE 'Captcha%'");
				if ( count($Captchas) > 0 ) {
					srand(time()); // seed RNG
					$i = rand(1, count($Captchas)); // get a random row
					echo "<p><input name=\"CaptchaVal\" type=\"Text\" value=\"\">";
					echo "<input name=\"CaptchaName\" type=\"Hidden\" value=\"". $Captchas[$i-1] ."\">";
					echo "<label for=\"CaptchaVal\">". multiLang('<'.$Captchas[$i-1].'>', $lang) ."</label></p>\n";
				}
				echo "<p class=\"a9\">". multiLang('<CommApproval>', $lang) ."</p>\n";
				echo "<p><input value=\"". multiLang('<Send>', $lang) ."\" type=\"Submit\"></p>\n";
				echo "</FORM>\n";
				echo "</DIV>\n";
			}
			echo "</DIV>\n";
		}
		unset($Comments);
	}

	// permalinks
	$kat = ($TextPermalinks) ? ($IsIIS ? "$WebFile/" : ''). $KatText .'/' : '?kat='. $_GET['kat'];
	$bid = ($TextPermalinks) ? $Tekst->Ime .'/' : '&amp;ID='. $_GET['ID'];
	$URL = $WebURL .'/'. $kat . $bid;
	
	// add social buttons
	echo "<DIV CLASS=\"social\">\n";
	// twitter
	echo "<a href=\"https://twitter.com/share\" class=\"twitter-share-button\" data-url=\"". $URL ."\"";
	if ( isset($TweetText) && $TweetText != "" )
		echo " data-text=\"". $TweetText ."\"";
	if ( isset($TwitterName) )
		echo " data-via=\"". $TwitterName ."\"";
	echo ">Tweet</a>\n";
	// facebook
	echo "<iframe src=\"http://www.facebook.com/plugins/like.php?href=". urlencode($URL) ."&amp;layout=button_count&amp;show_faces=false&amp;action=like&amp;colorscheme=light\"";
	echo " scrolling=\"no\" frameborder=\"0\" style=\"border:none;overflow:hidden;width:120px;height:20px;\" allowTransparency=\"true\"></iframe>\n";
	// google+
	echo "<div class=\"g-plusone\" data-size=\"medium\"></div>\n";
	echo "<script type=\"text/javascript\">";
	echo "(function(){";
	echo "var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;";
	echo "po.src = 'https://apis.google.com/js/plusone.js';";
	echo "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);";
	echo "})();";
	echo "</script>\n";
	echo "</DIV>\n";

	// navigation buttons
	echo "<TABLE class=\"navbutton list\" BORDER=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
	echo "<TR>\n";
	echo "\t<TD ALIGN=\"left\">\n";
	if ( $NextPost )
		echo "\t<A HREF=\"$WebPath/$kat". ($TextPermalinks ? '?':'&amp;') ."ID=". $NextPost ."\">&laquo;&nbsp;". multiLang('<Prev>', $lang) ."</A>\n";
	echo "\t</TD>\n";
	echo "\t<TD ALIGN=\"right\">\n";
	if ( $PrevPost )
		echo "\t<A HREF=\"$WebPath/$kat". ($TextPermalinks ? '?':'&amp;') ."ID=". $PrevPost ."\">". multiLang('<Next>', $lang) ."&nbsp;&raquo;</A>\n";
	echo "\t</TD>\n";
	echo "</TR>\n";
	echo "</TABLE>\n";

} else {
//-----------------------
// all posts in category
//-----------------------

	// are we requested do display different page?
	$Page = isset($_GET['pg']) ? (int)$_GET['pg'] : 1; // #evaluate('(Galerija.RecordCount-1) \ MaxRows + 1')#
	
	if ( count($Besedila) > 0 ) {
		// get maximum number of posts displayed
		$MaxRows = $BlogMaxPosts;
		
		// all available rows
		$AllRows = count($Besedila);
		
		// number of possible pages
		$NuPg = (int)(($AllRows-1) / $MaxRows) + 1;
		
		// fix page number if out of limits
		$Page = max($Page, 1);
		$Page = min($Page, $NuPg);
		
		// start & end page
		$StPg = min(max(1, $Page - 5), max(1, $NuPg - 10));
		$EdPg = min($StPg + 10, min($Page + 10, $NuPg));
		
		// previous and next page numbers
		$PrPg = $Page - 1;
		$NePg = $Page + 1;
		
		// start and end row from recordset
		$StaR = ($Page - 1) * $MaxRows + 1;
		$EndR = min(($Page * $MaxRows), $AllRows);
	
		// loop until all texts for current page are displayed
		for( $i=$StaR; $i<=$EndR; $i++ ) {
			// get the record
			$Besedilo = $Besedila[$AllRows-$i];
			
			// get text pages (for mobile client just get first page of text)
			$Tekst = $db->get_row(
				"SELECT
					BO.Naslov,
					BO.Podnaslov,
					BO.Povzetek,
					BO.Opis,
					B.DatumObjave,
					U.Name
				FROM
					BesedilaOpisi BO
					LEFT JOIN Besedila B ON B.BesediloID = BO.BesediloID
					LEFT JOIN SMUser U ON U.UserID = B.Avtor
				WHERE
					BO.BesediloID = ". (int)$Besedilo->ID ."
					AND (BO.Jezik='$lang' OR BO.Jezik IS NULL)
				ORDER BY
					BO.Jezik,
					BO.Polozaj
				LIMIT 1"
				);

			// get 1st gallery photo
			$Galerija = $db->get_row(
				"SELECT
					M.Datoteka
				FROM
					BesedilaSlike BS
					LEFT JOIN Media M ON BS.MediaID = M.MediaID
				WHERE
					BS.BesediloID = ". (int)$Besedilo->ID ."
				ORDER BY
					BS.Polozaj
				LIMIT 1"
				);
			
			// display a single post (comprised of multiple texts)
			if ( $Tekst ) {
				echo "<div class=\"post list\" id=\"entry-". $Besedilo->ID ."\">\n";
				// set Tweet text
				$TweetText = ( left($Tekst->Naslov,1)!='.' ? $Tekst->Naslov : "Tweet text placeholder" );
				
				// display link in list
				$kat = ($TextPermalinks) ? ($IsIIS ? "$WebFile/" : ''). $KatText .'/' : '?kat='. $_GET['kat'];
				$bid = ($TextPermalinks) ? $Besedilo->Ime .'/' : '&amp;ID='. $Besedilo->ID;
				echo "\t<a href=\"$WebPath/$kat". $bid ."\" class=\"postlink\">\n";
	
				// display image if not mobile site
				//if ( !$Mobile ) {
					$pic = ""; //default: no image

					if ( $Besedilo->Slika != "" && fileExists($StoreRoot ."/media/besedila/". $Besedilo->Slika) ) {

						// if text has an image and it exists display it
						$pic = $WebPath ."/media/besedila/". $Besedilo->Slika;
						// try to generate thumbnail
						try {
							// image thumbnail parameters
							$x = $db->get_row(
								"SELECT
									ST.SifNaziv AS GalleryBase,
									S.SifNVal1 AS DefPicSize,
									S.SifNVal2 AS DefThumbSize,
									S.SifNVal3 AS MaxPicSize
								FROM
									Sifranti S
									LEFT JOIN SifrantiTxt ST ON S.SifrantID=ST.SifrantID
								WHERE
									S.SifrCtrl = 'BESE'
									AND S.SifrText = '". $Besedilo->Tip ."'
								ORDER BY
									ST.Jezik DESC
								LIMIT 1"
								);
							if ( $x ) {
								$GalleryBase  = $x->GalleryBase;
								$DefPicSize   = (int)$x->DefPicSize;
								$DefThumbSize = (int)$x->DefThumbSize;
								$MaxPicSize   = (int)$x->MaxPicSize;
							}
							unset($x);

							$thumb = PhpThumbFactory::create($StoreRoot ."/media/besedila/". $Besedilo->Slika, array('jpegQuality' => $jpgPct,'resizeUp' => false));
							$size = $thumb->getCurrentDimensions();
							// if size is largerer than thumbnail use thumbnail
							if ( $size['width'] > abs($DefThumbSize) || $size['height'] > abs($DefThumbSize) ) {
								if ( $DefThumbSize < 0 )
									$thumb->adaptiveResize(abs($DefThumbSize), abs($DefThumbSize));
								else
									$thumb->resize($DefThumbSize, $DefThumbSize);
								$imageAsString = $thumb->getImageAsString(); 
								$pic = "data:image/". strtolower($thumb->getFormat()) .";base64,". base64_encode($imageAsString);
							}
						} catch (Exception $e) {
						}

					} else if ( $Galerija ) {

						// 1st image in gallery: determine file (sPath) and URL (rPath) path
						$sFile = $Galerija->Datoteka;
						$rPath = $WebPath   ."/". dirname($sFile);
						$sPath = $StoreRoot ."/". dirname($sFile);
						$sFile = basename($sFile);
				
						if ( fileExists($sPath."/thumbs/".$sFile) ) {
							$pic = $rPath ."/thumbs/". $sFile; // existing thumbnail
						}

					} else {

						// find 1st embeded image
						if ( preg_match("/<img[^>]*>/i", str_replace("\\\"","\"",$Tekst->Opis), $src) ) {
							if ( preg_match("/src=\"(?!http)([^\"]*)\"/i", $src[0], $pic) ) { // find SRC= content
								$sPath = dirname($StoreRoot ."/". $pic[1]); // filesystem path
								$rPath = dirname($WebPath ."/". $pic[1]); // web relative path
								$sName = basename($WebPath ."/". $pic[1]); // filename
						
								// check if thumbnail exists
								if ( fileExists($sPath .'/thumbs/'. $sName) ) {
									$pic = "$rPath/thumbs/". $sName;
								} else {
									$pic = "";
								}
							}
						}
					}
					if ( $pic != "" )
						echo "\t<IMG SRC=\"". $pic ."\" alt=\"\" BORDER=\"0\" CLASS=\"thumb frame\" retina=\"no\">\n";
				//}

				echo "\t<div class=\"caloffset\">\n";

				// display text title (hide title if starting with .)
				if ( left($Tekst->Naslov,1) != '.' ) {
					echo "\t<h1>";
					echo $Tekst->Naslov;
					echo "</h1>\n";
				}

				// display text abstract
				if ( $Tekst->Povzetek != "" ) {
					echo "\t<div class=\"abstract\">";
					echo (!$Mobile ? "<b>": "");
					echo ReplaceSmileys($Tekst->Povzetek, "$WebPath/pic/");
					echo (!$Mobile ? "</b>": "");
					echo "</div>\n";
				}

				// display author & text date
				echo "\t<div class=\"date\">";
				echo multiLang('<PostedBy>', $lang);
				echo " ". $Tekst->Name;
				echo " ". date("j.n.Y \@ H:i", sqldate2time($Tekst->DatumObjave));
				echo "</div>\n";

				echo "\t</div>\n"; // title

				echo "\t<div class=\"calendar\">";
				// display bubble for # comments
				if ( $Besedilo->ForumTopicID != 0 ) {
					$Comments = $db->get_var(
						"SELECT
							count(*)
						FROM
							frmMessages
						WHERE
							TopicID = ". (int)$Besedilo->ForumTopicID ."
							AND IsApproved = 1"
						);
				
					if ( $Comments > 0 )
						echo "<div class=\"bubble\">". (int)$Comments ."</div>\n";
				}
				// display text date
				echo "<div class=\"month\">". date("M",sqldate2time($Besedilo->Datum)) ."</div>";
				echo "<div class=\"day\">". date("j",sqldate2time($Besedilo->Datum)) ."</div>";
				echo "<div class=\"year\">". date("Y",sqldate2time($Besedilo->Datum)) ."</div>";
				echo "</div>\n";

				echo "\t</a>\n";
				
				echo "</div>\n"; // post
			}
			unset($Tekst);
		}

		// show navigation buttons
		$kat = ($TextPermalinks) ? ($IsIIS ? "$WebFile/" : ''). "$KatText/" : '?kat='. $_GET['kat'];
		$ar = '';
		if ( isset($_GET['ar']) ) $ar = ($TextPermalinks) ? 'AR'. $_GET['ar'] .'/' : '&amp;ar='. $_GET['ar'];
		if ( isset($_GET['tag']) ) $ar = ($TextPermalinks) ? 'TAG'. $_GET['tag'] .'/' : "&amp;tag=". $_GET['tag'];

		echo "<TABLE class=\"navbutton list\" BORDER=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
		echo "<TR>\n";
		echo "\t<TD ALIGN=\"left\">\n";
		if ( $Page > 1 )
			echo "\t<A HREF=\"$WebPath/$kat". $ar . ($TextPermalinks ? '?':'&amp;') ."pg=". $PrPg ."\">&laquo;&nbsp;". multiLang('<PrevPage>', $lang) ."</A>\n";
		echo "\t</TD>\n";
		echo "\t<TD ALIGN=\"right\">\n";
		if ( $Page < $EdPg )
			echo "\t<A HREF=\"$WebPath/$kat". $ar . ($TextPermalinks ? '?':'&amp;') ."pg=". $NePg ."\">". multiLang('<NextPage>', $lang) ."&nbsp;&raquo;</A>\n";
		echo "\t</TD>\n";
		echo "</TR>\n";
		echo "</TABLE>\n";
	}
}
