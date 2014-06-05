<?php
/*
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend)                                             |
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
| N3O CMS (frontend) is free software: you can redistribute it and/or       |
| modify it under the terms of the GNU Lesser General Public License as     |
| published by the Free Software Foundation, either version 3 of the        |
| License, or (at your option) any later version.                           |
|                                                                           |
| N3O CMS (frontend) is distributed in the hope that it will be useful,     |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU Lesser General Public License for more details.                       |
'---------------------------------------------------------------------------'
*/

// get ID of special category for cookies disclaimer
$ktg = $db->get_row(
	"SELECT
		K.KategorijaID,
		KO.Naziv
	FROM
		Kategorije K
		LEFT JOIN KategorijeNazivi KO ON K.KategorijaID = KO.KategorijaID
	WHERE
		K.Ime = 'Cookies'
		AND (KO.Jezik IS NULL OR KO.Jezik='$lang')
	LIMIT 1"
);
?>
<div id="cookies">
<div class="cont">
<div class="text">
<?php echo multiLang('<Cookies>', $lang); ?><br>
<a href="?kat=<?php echo $ktg->KategorijaID ?>"><?php echo $ktg->Naziv; ?></a>
</div>
<div class="buttons">
<input type="button" value="<?php echo multiLang('<Agree>', $lang); ?>" onclick="setCookie('accept_cookies','yes',365);$('#cookies').hide();" />
<input class="no" type="button" value="<?php echo multiLang('<Reject>', $lang); ?>" onclick="location.href='http://www.google.com?q=http+cookie';" />
</div>
<div class="clr"></div>
</div>
</div>