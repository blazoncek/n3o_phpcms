<?php
/* _pravila.php - custom forum rules (include file) change at will
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
?>
<style>
ul {margin-left:25px; list-style:disc outside;}
li {padding-top:3px;padding-bottom:3px;}
li.bg {background-color:<?php echo $BackgColor ?>; list-style-type:circle;}
</style>
<script>
function doSearch() {
<?php if ( contains("confirm",$_SERVER['QUERY_STRING']) ) : ?>
	window.opener.document.location.href='iskanje.php';
<?php else : ?>
	window.location.href='iskanje.php';
<?php endif ?>
}
</script>
<p>Kot verjetno že veste so Diskusije prostor, kjer se izmenjujejo izkušnje in nasveti,
se zastavljajo in se odgovarja na vprašanja, ipd. Ker pri tem sodeluje veliko različnih
ljudi, je potrebno imeti pravila, ki se jih morajo držati sogovorniki, da ohranijo
kulturno raven pogovora.<BR>
Prosimo, da upoštevate tudi morebitna navodila in opozorila moderatorjev. Le
tako bomo ohranili preglednost Diskusij in komuniciranje na primerni ravni.</p>
<h1>Pravila in pogoji uporabe Diskusij</h1>
<ul type="disc">
<li class=bg>Če želite aktivno sodelovati v Diskusijah, se morate včlaniti, kar pomeni,
    da v sistem vpišete vsaj izbrani vzdevek in vaš <B>veljaven</B> elektronski naslov.
    Računalnik vam dodeli geslo in ga pošlje na vpisan elektronski naslov. Geslo
    lahko kadarkoli spremenite v nastavitvah na straneh Diskusij.</li>
<li>Včlanitev je BREZPLAČNA. Vse, kar od vas zahtevamo je, da nam zaupate svoj vzdevek
    in elektronski naslov, vsi ostali podatki so neobvezni. Vaše podatke si bodo lahko
    ogledali tudi ostali uporabniki Diskusij (izjema je telefonska številka). Če ne želite,
    da ostali uporabniki vidijo vaš naslov (tudi elektronski), lahko to ob vpisu ali tudi
    kasneje izberete v Nastavitvah.</li>
<li class=bg>Diskusije so javne, kar pomeni, da bo vse, kar napišete, takoj postalo
    dostopno javnosti. Če najdete vsebine, ki so dvomljive narave, nemudoma obvestite
    administratorja ali moderatorja po elektronski pošti.</li>
<li>Lastnik ali upravljalec spletnega strežnika, moderatorji in administratorji teh diskusij v
    nobenem primeru ne odgovarjajo za vsebino vpisanih sporočil.  Administratorji in
    moderatorji so pooblaščeni za odstranitev ali spreminjanje vsebine sporočil, če kršijo
    pogoje ali pravila uporabe. Prav tako so pooblaščeni za popravljanje (slovničnih ali drugih)
    napak v sporočilih.</li>
<li class=bg>Administrator ali moderator ima pravico ukrepati, če meni, da je bilo kakšno pravilo
    resneje kršeno; če uporabnik opozoril ne upošteva, se ga lahko izključi iz diskusij.</li>
<li>Oddajanje žaljivih, neresničnih, moralno in etično nesprejemljivih sporočil ni
    dovoljeno. Prav tako ni dovoljeno oddajanje sporočil, ki bi napeljevala k rasni, verski,
    politični ali kaki drugi nestrpnosti. Pridržujemo si pravico, da vsakogar, ki odda tako
    sporočilo, takoj izključimo iz Diskusij.</li>
<li class=bg>Vsa vpisana sporočila so javna last in nihče si ne more lastiti avtorskih
    pravic, pravic kopiranja ali razmnoževanja (copyright) sporočil. Če nameravate
    uporabiti katerokoli sporočilo izven teh Diskusij, to sporočite avtorju ter obvezno
    navedite vir (spletno stran - <a href="<?php echo $WebServer ?>"><?php echo $WebServer ?></a>).</li>
<li>Nekatere diskusijske niti omogočajo pripenjanje datotek, če uporabljate
    Microsoft Internet Explorer v4+ ali Netscape Navigator v4+. Nedovoljeno je pripenjanje
    avtorsko zaščitenih datotek ali programov brez pisnega dovoljenja avtorjev oz. lastnika
    avtorskih pravic. Pripete datoteke ali programe uporabniki uporabljajo na lastno odgovornost.
    Lastnik ali upravljalec spletnega strežnika, administratorji ter moderatorji ne jamčijo
    za vsebino in v nobenem primeru ne morejo biti odgovorni za morebitne posledice,
    ki bi nastale z uporabo takih datotek ali programov.</li>
<li class=bg>Reklamiranje podjetij in/ali izdelkov ni zaželeno, razen v za to namenjenih nitih.
    (Seveda je dovoljeno običajno diskutiranje in priporočanje določenega izdelka zaradi
    njegove kvalitete, cene, ...)</li>
<li>Podvajanje tem ali ponavljanje podobnih vprašanj ni zaželeno in jih lahko
    administratorji ali moderatorji izbrišejo. Prav tako lahko administratorji ali
    moderatorji premikajo teme ali posamezna sporočila med nitmi ali drugimi temami,
    če se jim to zdi primerno. O svojih dejanjih niso dolžni obvestiti nikogar,
    čeprav se to spodobi.</li>
<li class=bg>Za nemoteno delovanje spletne aplikacije Diskusij morajo v vašem spletnem
    brskalniku biti vključeni piškotki (cookies). Vanje se shranjujejo podatki o vašem
    zadnjem obisku Diskusij ter vaš elektronski naslov za hitrejšo prijavo.</li>
</ul>
<p>Diskusije lahko vsebujejo povezave na druge spletne strani in/ali datoteke nad
katerimi nimamo vpliva in ne moremo zagotoviti, da njihova vsebina ne bo pravno,
moralno, etično ali ideološko vprašljiva. Vsekakor pa bomo odstranili povezave na
strani, katerih vsebina se nam bo zdela neprimerna, takoj, ko bomo zanje izvedeli.
Zaželeno je, da o takih povezavah obvestite administratorje ali moderatorje po
elektronski pošti tudi obiskovalci Diskusij.</p>

<hr color=silver size=1 noshade>

<p><B>Nasveti</B> za boljšo in lažjo uporabo Diskusij:</p>
<ul type="disc">
<li class=bg>Preden v Diskusijah zastavite vprašanje, uporabite
    <A HREF="javascript:doSearch();">Iskanje</A> in preverite,
    če je bilo na podobno vprašanje že odgovorjeno; tako se bomo izognili
    nepotrebnemu ponavljanju vprašanj hkrati pa bo tudi preglednost Diskusij
    boljša.</li>
<li>Ko zastavljate vprašanje, dobro opišite problem; naštejte čim več podatkov
    (imena in število rib (zaželeno latinsko ime), parametri vode, ...);
    le tako se bo dalo hitro in pravilno odgovoriti. (Tako se bomo izognili
    odvečnim vprašanjem.)</li>
<li class=bg>Vaše vprašanje naj se nanaša na nit in temo v niti v katero pišete,</li>
<li>Zavedajte se, da v Diskusijah sodeluje mnogo ljudi, ki imajo na enako stvar
    različne poglede; ne spuščajte se v osebne dvoboje (če že morate, za to
    uporabljajte elektronsko pošto), raje argumentirano in konstruktivno
    povejte svoje mnenje.</li>
<li class=bg>Preden pošljete tekst v diskusije, ga še enkrat preberite in popravite
    morebitne napake, da ne bo težav z razumevanjem, pa tudi boljši vtis boste
    naredili na sogovornike.</li>
<li>Podvojeno sporočilo lahko v roku ene ure od oddaje sami izbrišete s klikom na
    ustrezno ikono oz. povezavo (Briši).</li>
<li class=bg>Svoje sporočilo lahko po oddaji tudi popravljate s klikom na ustrezno
    ikono oz. povezavo (Uredi).</li>
<li>Odgovori naj bodo kratki in jedrnati, ne dolgovezite, kjer to ni potrebno.</li>
<li class=bg>Če imate pripravljeno daljše besedilo, ki bi bilo morda zanimivo širšemu
    krogu obiskovalcev, ga pošljite
    <A HREF="mailto:<?php echo $Application.PostMaster ?>">administratorju</A>, da ga vpiše v
    posebno rubriko.</li>
<li>Zasebnih sporočil (med vami in kakšnim od diskutantov) ne oddajajte v
    diskusije, razen če se tudi vaš sogovornik s tem strinja in je sporočilo seveda
    v zvezi z diskusijsko temo,</li>
<li class=bg>Ne žalite sogovornikov, ne uporabljajte vulgarnih oz. neprimernih izrazov.
    (Neprimerni so med drugim tudi vsi tisti izrazi, ki jih ne bi izrekli svoji mami.)</li>
<li>Pri sodelovanju v diskusijah ravnajte tako, kot bi želeli, da drugi ravnajo
    z vami - <i>zapomnite si, da so ti pogovori javni in so namenjeni izmenjavi
    mnenj ter izkušenj</i>.</li>
<li class=bg>Ne bodite nestrpni in ne pričakujte odgovora na vaše vprašanje takoj;
    še posebej ne odpirajte novih niti z enakimi vprašanji, da bi vzbudili
    pozornost! (Vsakemu, ki se prijavi v diskusije, se pojavi znak 
    <IMG SRC="px/new.gif" WIDTH=20 HEIGHT=8 ALT="Novo!" BORDER="0">
    poleg niti, kjer je kaj novega od njegovega zadnjega obiska. Tako ne
    bodo zgrešili vašega vprašanja.)</li>
<li>Zaželeno je, da v osebne podatke vpišete pravo ime in/ali priimek; mnogi
    udeleženci diskusij ne marajo »anonimnežev«, na email pa boste dobili
    obvestila moderatorjev, če bo kakšno vaše vprašanje prestavljeno v drugo
    nit.</li>
<li class=bg>Zaželeno je tudi, da na koncu besedila napišete vir (knjiga, revija, stran
    na internetu, ...), od koder ste črpali informacije, da si bodo
    interesenti lahko prebrali še kaj več.</li>
<li>Priporočamo, da se po končanem ogledu Diskusij odjavite iz sistema s
    klikom na ustrezno povezavo; s tem preprečite morebitno zlorabo vaše prijave v
    Diskusijah.</li>
<li class=bg>V primeru, da pozabite svoje geslo, vam ga lahko sistem pošlje na vpisan
    elektronski naslov. Po neuspelem poskusu prijave kliknite na ustrezno povezavo
    na strani za prijavo.</li>
<li>Če spremenite svoj elektronski naslov, ga nemudoma popravite v svojih podatkih,
    saj boste le tako lahko nemoteno prejemali sistemska sporočila oz. sporočila
    administratorjev ali moderatorjev ter ostalih obiskovalcev Diskusij.</li>
<li class=bg>V Nastavitvah si lahko nastavite število prikazanih sporočil na zaslonu.
    Privzeta vrednost je 10 (če ni vpisa), največja možna vrednost je 99.</li>
<li>V primeru težav se obrnite na <a href="mailto:<?php echo $PostMaster ?>">administratorja</a> sistema.</li>
</ul>
