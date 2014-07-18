<?php
/*
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
?>
<script language="JavaScript" type="text/javascript">
<!-- //
function setFields( f, l, u, p, m, t )
{
	$("input[name='Name']").val(f+' '+l);
	$("input[name='Username']").val(u);
	$("input[name='Password']").val(p);
	$("input[name='Email']").val(m);
	$("input[name='Phone']").val(t);
}
//-->
</script>

<FIELDSET style="width:270px;height:230px;">
<LEGEND>User lookup</LEGEND>
<DIV STYLE="overflow-y: auto; width:270px;height:215px;">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<?php
if ( $_POST['Find'] != "" )
	$Filter = "(&(objectClass=User)(objectCategory=person)(|(sAMAccountName=".$_POST['Find']."*)(sn=".$_POST['Find']."*)(givenName=".$_POST['Find']."*)(mail=".$_POST['Find']."*)))";
else
	$Filter = "(&(objectClass=User)(objectCategory=person))";

$ldap = ldap_connect( $LDAPServer )
	or die ("<TR><TD CLASS=\"warn\">LDAP Connect error.</TD></TR>");
@ldap_bind( $ldap, $_SESSION["Username"].$AuthDomain, $_SESSION['Password'] )
	or die ("<TR><TD CLASS=\"warn\">LDAP Connect error.</TD></TR>");
$result = @ldap_search( $ldap, $LDAPCheck, $Filter, array("dn","cn","sn","givenname","mail","mobile","sAMAccountName","objectClass","objectCategory") )
	or die ("<TR><TD CLASS=\"warn\">Error in query.</TD></TR>");
$data = ldap_get_entries( $ldap, $result );
$BgCol="#edf3fe";
$i = 0;
foreach ($data as $d ) {
	if ( !is_array($d) )
		continue;
	if ( $i > 25 )
		break;
	if ( $BgCol == "white" )
		$BgCol="#edf3fe";
	else
		$BgCol = "white";
	$name = EncodeUTF8( $d['sn'][0].", ".$d['givenname'][0] );
	echo "<TR BGCOLOR=\"$BgCol\">\n";
	echo "<TD NOWRAP><A HREF=\"javascript:setFields(";
	echo "'" . EncodeUTF8( $d['givenname'][0] ) . "',";
	echo "'" . EncodeUTF8( $d['sn'][0] ) . "',";
	echo "'" . $d['samaccountname'][0] . "',";
	echo "'$AuthDomain',";
	echo "'" . (isset($d['mail'])? EncodeUTF8( $d['mail'][0] ): "") . "',";
	echo "'" . (isset($d['mobile'])? EncodeUTF8( $d['mobile'][0] ): "") . "'";
	echo ");\">".$name."</A>\n";
	echo (isset($d['mail'])? "<div class=\"f10\">".$d['mail'][0]."</div>\n": "\n");
	echo "</TD>\n";
	echo "<TD>".$d['samaccountname'][0]."</TD>\n";
	echo "</TR>\n";
}
?>
</TABLE>
</DIV>
</FIELDSET>