<?php
/* _userACL.php - Access Control List check (permissions check).
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend and backend)                                 |
|   Version: 2.2.0                                                          |
|   Contact: contact author (also http://blaz.at/home)                      |
| ------------------------------------------------------------------------- |
|    Author: Blaû Kristan (blaz@kristan-sp.si)                              |
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

/******************************************************************************
* ugotovimo kateri ACLji so za objekt (na userja in njegove grupe)
* v queryu so najprej grupni nato userjevi ACL (Ëe kateri obstajajo)
* tako imajo userjevi prioriteto nad grupnimi, znotraj grupnih ACL
* velja pravilo "least restrictive apply", kar pomeni, da velja najmanj
* omejujoË ACL
******************************************************************************/
function userACL($ACLID=1)
{
	global $db;

	// get ACLs for user and groups he/she belongs
	$SmACL = $db->get_results(
		"SELECT
			ACLID,
			MemberACL
		FROM
			SMACLr
		WHERE
			ACLID = ". (int) $ACLID ."
			AND
			(UserID = ". $_SESSION['UserID'] ." OR GroupID IN (". $_SESSION['Groups'] ."))
		ORDER BY
			UserID,
			MemberACL"
		);

	// if ACL ID is defined, defaults to no rights else all
	if ( $SmACL && $ACLID ) {
		$ACL = "     ";
		// nastavimo ACL
		foreach ( $SmACL as $mACL ) {
			if ( contains($mACL->MemberACL,"L") )
				$ACL = "L    ";
			if ( contains($mACL->MemberACL,"R") )
				$ACL = "LR   ";
			if ( contains($mACL->MemberACL,"W") )
				$ACL = "LRW  ";
			if ( contains($mACL->MemberACL,"D") )
				$ACL = "LRWD ";
			if ( contains($mACL->MemberACL,"X") )
				$ACL = left($ACL,4) . "X";
		}
	} else if ( $ACLID )
		$ACL = "     ";
	else
		$ACL = "LRXWD";
	return $ACL;
}
?>
