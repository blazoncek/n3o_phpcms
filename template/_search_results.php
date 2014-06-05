<?php
/* _iskanje_rezultati.php - Search template for searching all texts.
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

/*************************************
* Uses SearchString() function to build SQL search string.
* Outputs: JSON or HTML. JSON is used for AJAX requests.
**************************************/

if ( isset($_GET['format']) && $_GET['format'] == "JSON" ) {
	// search results in JSON format
	$URLsearch = "";
	$WhereClause = "1=0";

	// iskanje besedila: $_GET['term']
	if ( isset($_GET['term']) ) {
		$Plus = " OR ";
		$WhereClause .= $Plus . SearchString("BO.Naslov",   $db->escape($_GET['term']));
		$WhereClause .= $Plus . SearchString("BO.Povzetek", $db->escape($_GET['term']));
		$WhereClause .= $Plus . SearchString("BO.Opis",     $db->escape($_GET['term']));
		$URLsearch   .= "&amp;S=" . $db->escape($_GET['term']);
	}
	$Iskanje = $db->get_results(
		"SELECT
			B.BesediloID,
			B.Ime AS BesediloIme,
			KB.KategorijaID,
			K.Ime AS KategorijaIme,
			B.Datum,
			BO.Jezik,
			BO.Naslov
		FROM
			Besedila B
			LEFT JOIN BesedilaOpisi BO ON B.BesediloID = BO.BesediloID
			LEFT JOIN KategorijeBesedila KB ON B.BesediloID = KB.BesediloID
			LEFT JOIN Kategorije K ON KB.KategorijaID = K.KategorijaID
		WHERE
			B.Izpis <> 0
			AND B.Tip IN ('Besedilo','Blog')
			AND K.Izpis <> 0 AND K.Iskanje <> 0
			AND (BO.Jezik = '$lang' OR BO.Jezik IS NULL) ".
//			( isset($_GET['kat']) ? "AND KB.KategorijaID = '".$db->escape($_GET['kat'])."'" : "") .
			"AND (" . $WhereClause . ")
		ORDER BY
			KB.KategorijaID,
			B.Datum DESC,
			B.BesediloID DESC"
		);

	// maximum # of results on one page
	if ( !isset($_GET['num']) )
		$_GET['num'] = 10;
	$MaxRows = min(max((int)$_GET['num'], 10), 50);
	
	echo "[";
	if ( $Iskanje ) for ( $i=0; $i<$MaxRows && $i<count($Iskanje); $i++ ) {
		$Naslov = $Iskanje[$i]->Naslov;
		$Naslov = str_ireplace( "&lt;",    "<", $Naslov );
		$Naslov = str_ireplace( "&gt;",    ">", $Naslov );
		$Naslov = str_ireplace( "&nbsp;",  " ", $Naslov );
		$Naslov = str_ireplace( "&amp;",   "&", $Naslov );
		$Naslov = str_ireplace( "&raquo;", "»", $Naslov );
		$Naslov = str_ireplace( "&laquo;", "«", $Naslov );
		$Naslov = str_ireplace( "&rdquo;", "”", $Naslov );
		$Naslov = str_ireplace( "&ldquo;", "“", $Naslov );
		$Naslov = str_ireplace( "&quot;",  "\"", $Naslov );
		if ( left($Naslov,1) == '.' ) $Naslov = substr($Naslov,1);

		echo "{ ";
		echo "\"id\": \"". (($TextPermalinks) ? $Iskanje[$i]->BesediloIme : $Iskanje[$i]->BesediloID) ."\", ";
		echo "\"label\": \"". $Naslov ."\", ";
		echo "\"value\": \"". $Naslov ."\", ";
		echo "\"kat\": \"". (($TextPermalinks) ? $Iskanje[$i]->KategorijaIme : $Iskanje[$i]->KategorijaID) ."\" ";
		echo "}";
		if ( $i<$MaxRows-1 && $i<count($Iskanje)-1 ) echo ", ";
	}
	echo "]\n";

} else {

	// search results in HTML format
	$Kategorija = $db->get_row(
		"SELECT
			K.Ime,
			KN.Naziv,
			KN.Opis,
			KN.Povzetek
		FROM
			Kategorije K
			LEFT JOIN KategorijeNazivi KN ON K.KategorijaID = KN.KategorijaID
		WHERE
			K.KategorijaID = '". $db->escape($_GET['kat']) ."'
			AND (KN.Jezik = '$lang' OR KN.Jezik IS NULL)" ) ;
	
	echo "<div class=\"text\">\n";
//	if ( $Kategorija->Naziv != "" )
//		echo "<H1>$Kategorija->Naziv</H1>\n";
	
	if ( (isset($_GET['S']) && $_GET['S'] != "")
		|| (isset($_GET['as_q'])
			|| isset($_GET['as_epq'])
			|| isset($_GET['as_oq'])
			|| isset($_GET['as_eq'])) ) {
		// simple search or advanced search performed
	
		$URLsearch = "";
		$WhereClause = "1=0";
		// iskanje besedila: $_GET['S']
		if ( isset($_GET['S']) ) {
			$Plus = " OR ";
			$WhereClause .= $Plus . SearchString( "BO.Naslov",   $_GET['S'] );
			$WhereClause .= $Plus . SearchString( "BO.Povzetek", $_GET['S'] );
			$WhereClause .= $Plus . SearchString( "BO.Opis",     $_GET['S'] );
			$URLsearch .= "&amp;S=" . $_GET['S'];
		} else {
			// advanced search
			if ( isset($_GET['as_q']) && $_GET['as_q'] != "" ) {
				// all words
				$Plus = " OR ";
				$WhereClause .= $Plus . SearchString( "BO.Naslov",   $_GET['as_q'] );
				$WhereClause .= $Plus . SearchString( "BO.Povzetek", $_GET['as_q'] );
				$WhereClause .= $Plus . SearchString( "BO.Opis",     $_GET['as_q'] );
				$URLsearch .= "&amp;as_q=" . $_GET['as_q'];
			}
			if ( isset($_GET['as_epq']) && $_GET['as_epq'] != "" ) {
				// exact phrase
				$Plus = " OR ";
				$WhereClause = $Plus . SearchString( "BO.Naslov",   '"' . $_GET['as_epq'] . '"' );
				$WhereClause = $Plus . SearchString( "BO.Povzetek", '"' . $_GET['as_epq'] . '"' );
				$WhereClause = $Plus . SearchString( "BO.Opis",     '"' . $_GET['as_epq'] . '"' );
				$URLsearch .= "&amp;as_epq=" . $_GET['as_epq'];
			}
			if ( isset($_GET['as_oq']) && $_GET['as_oq'] != "" ) {
				// at least one word
				$Plus = " OR ";
				$WhereClause = $Plus . SearchString( "BO.Naslov",   preg_replace( '/[[:space:]]+/', ',', $_GET['as_oq'] ) );
				$WhereClause = $Plus . SearchString( "BO.Povzetek", preg_replace( '/[[:space:]]+/', ',', $_GET['as_oq'] ) );
				$WhereClause = $Plus . SearchString( "BO.Opis",     preg_replace( '/[[:space:]]+/', ',', $_GET['as_oq'] ) );
				$URLsearch .= "&amp;as_oq=" . $_GET['as_oq'];
			}
			if ( isset($_GET['as_eq']) && $_GET['as_eq'] != "" ) {
				// exclude words
				$Plus = " OR ";
				$WhereClause = $Plus ."((". SearchString( "BO.Naslov", "-".preg_replace( '/[[:space:]]+/', ' -', $_GET['as_eq'] ) ) . ")";
				$Plus = " AND ";
				$WhereClause = $Plus ."(". SearchString( "BO.Povzetek", "-".preg_replace( '/[[:space:]]+/', ' -', $_GET['as_eq'] ) ) . ")";
				$WhereClause = $Plus ."(". SearchString( "BO.Opis",     "-".preg_replace( '/[[:space:]]+/', ' -', $_GET['as_eq'] ) ) . "))";
				$URLsearch .= "&amp;as_eq=" . $_GET['as_eq'];
			}
			if ( isset($_GET['as_qdr']) ) {
				switch ( $_GET['as_qdr'] ) {
					case "m3":
						$WhereClause = "(". $WhereClause .") AND B.Datum >= ". date("'Y-m-d H:i:s'", time()-3*24*60*60);
						break;
					case "m6":
						$WhereClause = "(". $WhereClause .") AND B.Datum >= ". date("'Y-m-d H:i:s'", time()-6*24*60*60);
						break;
					case "y":
						$WhereClause = "(". $WhereClause .") AND B.Datum >= ". date("'Y-m-d H:i:s'", time()-12*24*60*60);
						break;
				}
				$URLsearch .= "&amp;as_qdr=" . $_GET['as_qdr'];
			}
		}
	
		$Iskanje = $db->get_results(
			"SELECT
				B.BesediloID,
				KB.KategorijaID,
				B.Ime,
				B.Datum,
				BO.Jezik,
				BO.Naslov,
				BO.Podnaslov,
				BO.Povzetek,
				BO.Opis
			FROM
				Besedila B
				LEFT JOIN BesedilaOpisi BO ON B.BesediloID = BO.BesediloID
				LEFT JOIN KategorijeBesedila KB ON B.BesediloID = KB.BesediloID
				LEFT JOIN Kategorije K ON KB.KategorijaID = K.KategorijaID
			WHERE
				B.Izpis <> 0
				AND K.Izpis <> 0 AND K.Iskanje <> 0
				AND (BO.Jezik = '$lang' OR BO.Jezik IS NULL)
				AND (". $WhereClause .")
			ORDER BY
				KB.KategorijaID,
				B.Datum DESC,
				B.BesediloID DESC"
			);
	
		// maximum # of results on one page
		if ( !isset($_GET['num']) )
			$_GET['num'] = 10;
		$MaxRows = min(max((int)$_GET['num'], 10), 50);
	
		// are we requested do display different page?
		if ( !isset($_GET['pg']) )
			$Page = 1;
		else
			$Page = (int)$_GET['pg'];
		
		// number of possible pages
		$NuPg = (int)((count($Iskanje)-1) / $MaxRows) + 1;
		
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
		$EndR = min(($Page * $MaxRows), count($Iskanje));
	
		if ( count($Iskanje) > 0 ) {
			$Found="Yes";
	
			// if number of pages > 1 display page selection bar
			if ( $NuPg > 1 ) {
				$kat = ($TextPermalinks) ? ($IsIIS ? 'index.php/' : ''). $KatText .'/?' : '?kat='. $_GET['kat'] .'&amp;';
				echo "<TABLE class=\"navbutton\" BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\">\n";
				echo "<TR>\n";
				echo "<TD class=\"a10\">\n";
				echo "&nbsp;". multiLang('<Page>', $lang) .":\n";
				if ( $StPg > 1 ) echo "<A HREF=\"$WebPath/$kat"."pg=$StPg$URLsearch\">&laquo;</A>\n";
				if ( $Page > 1 ) echo "<A HREF=\"$WebPath/$kat"."pg=$PrPg$URLsearch\">&lt;</A>\n";
				for ( $i = $StPg; $i <= $EdPg; $i++ ) {
					if ( $i == $Page )
						echo "<FONT COLOR=\"$TxtExColor\"><B>$i</B></FONT>\n";
					else
						echo "<A HREF=\"$WebPath/$kat"."pg=$i$URLsearch\">$i</A>\n";
				}
				if ( $Page < $EdPg ) echo "<A HREF=\"$WebPath/$kat"."pg=$NePg$URLsearch\">&gt;</A>\n";
				if ( $NuPg > $EdPg ) echo "<A HREF=\"$WebPath/$kat"."pg=$EdPg$URLsearch\">&raquo;</A>\n";
				echo "</TD>\n";
				echo "<TD ALIGN=\"right\" CLASS=\"a10\">&nbsp;</TD>\n";
				echo "</TR>\n";
				echo "</TABLE>\n";
			}
	
			// display results, start with selected page (row=max*pg)
			for ( $i = $StaR-1; $i < $EndR && $i < count($Iskanje); $i++ ) {
			
				if ( $i == $StaR-1 || $Iskanje[$i]->KategorijaID != $Iskanje[$i-1]->KategorijaID ) {
					// get category name
					$Kategorija = $db->get_row(
						"SELECT K.Ime, KN.Naziv
						FROM Kategorije K
							LEFT JOIN KategorijeNazivi KN ON K.KategorijaID = KN.KategorijaID
						WHERE K.KategorijaID = '". left($Iskanje[$i]->KategorijaID, 2) ."'
							AND (KN.Jezik = '$lang' OR KN.Jezik IS NULL)"
						);
					echo "<H1>";
					if ( $Kategorija->Naziv != "" )
						echo $Kategorija->Naziv;
					else
						echo $Kategorija->Ime;
					echo "</H1>\n";
				}
				
				$kat = ($TextPermalinks) ? ($IsIIS ? 'index.php/' : ''). $Kategorija->Ime .'/' : '?kat='. $Iskanje[$i]->KategorijaID;
				$bid = ($TextPermalinks) ? $Iskanje[$i]->Ime .'/' : '&amp;ID='. $Iskanje[$i]->BesediloID ;
				echo "<div class=\"searchresult\">\n";
				echo "<A HREF=\"$WebPath/$kat". $bid ."\">\n";
				echo "<H3>". $Iskanje[$i]->Naslov ."</H3>\n";
				echo "<div class=\"abstract\">". ReplaceSmileys($Iskanje[$i]->Povzetek, "$WebPath/pic/") ."</div>\n";
				echo "</a>\n";
				echo "</div>\n";
			}
		}
	} else {

		// advanced search form
		if ( $Kategorija->Naziv != "" )
			echo "<H1>$Kategorija->Naziv</H1>\n";

		$Found = "";
		echo "<DIV CLASS=\"comment\">\n";
	?>
		<FORM NAME="f" ACTION="<?php echo $WebPath .'/'. $WebFile; ?>" METHOD="get">
		<INPUT NAME="kat" TYPE="Hidden" VALUE="<?php echo $_GET['kat']; ?>">
		<table border="0" cellspacing="0" cellpadding="2" CLASS="a10">
		<tr>
			<td valign="top" width="15%" CLASS="a10">
			<br><br><b><?php echo multiLang('<FindPages>', $lang); ?></b>
			</td>
			<td width="85%">
			<table cellpadding="2" cellspacing="0" border="0" width="100%">
			<tr>
				<td CLASS="a10"><?php echo multiLang('<allWords>', $lang); ?></td>
				<td><input type="text" value="" name="as_q" size="25" CLASS="a10"> 
				<script type="text/javascript">
				<!--
				document.f.as_q.focus();
				// jQuery form
				//$(document).ready(function(){$("form:name=f").item('as_q').focus();});
				// -->
				</script>
				</td>
				<td valign="top" rowspan="3" CLASS="a10"><?php echo multiLang('<Show>', $lang); ?>:<br>
				<select name="num" CLASS="a10">
					<option value="10" selected><?php echo multiLang('<res10>', $lang); ?></option>
					<option value="25"><?php echo multiLang('<res25>', $lang); ?></option>
					<option value="50"><?php echo multiLang('<res50>', $lang); ?></option>
				</select>
				</td>
			</tr>
			<tr>
				<td nowrap CLASS="a10"><?php echo multiLang('<exactPhrase>', $lang); ?></td>
				<td><input type="text" size="25" value="" name="as_epq" CLASS="a10"></td>
			</tr>
			<tr>
				<td nowrap CLASS="a10"><?php echo multiLang('<atLeastOneWord>', $lang); ?></td>
				<td><input type="text" size="25" value="" name="as_oq" CLASS="a10"></td>
			</tr>
			<tr>
				<td nowrap CLASS="a10"><?php echo multiLang('<dontContain>', $lang); ?></td>
				<td><input type="text" size="25" value="" name="as_eq" CLASS="a10"></td>
				<td align="center"><input type="submit" name="btnG" value="<?php echo multiLang('<Search>', $lang); ?>" style="width:75px;"></td>
			</tr>
			</table>
			</td>
		</tr>
		</table>
		<table width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td width="15%" CLASS="a10"><b><?php echo multiLang('<Date>', $lang); ?></b></td>
			<td width="40%" nowrap CLASS="a10"><?php echo multiLang('<dateText>', $lang); ?></td>
			<td CLASS="a10">
			<select name="as_qdr" CLASS="a10">
				<option value="all"><?php echo multiLang('<resAll>', $lang); ?></option>
				<option value="m3"><?php echo multiLang('<res3m>', $lang); ?></option>
				<option value="m6"><?php echo multiLang('<res6m>', $lang); ?></option>
				<option value="y"><?php echo multiLang('<res1y>', $lang); ?></option>
			</select>
			</td>
		</tr>
		</table>
		</FORM>
	<?php
		echo "</DIV>\n";
	}
	
	if ( !isset($Found) ) {
		echo "<DIV style=\"text-align:center;height:10em;vertical-align:middle;\">\n";
		echo "<B>". multiLang('<ListEmpty>', $lang) ."</B>\n";
		echo "</DIV>\n";
	}
	
	echo "</div>\n";
}
?>