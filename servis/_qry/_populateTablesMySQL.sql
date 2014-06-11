--.---------------------------------------------------------------------------.
--|  Software: N3O CMS (frontend and backend)                                 |
--|   Version: 2.2.0                                                          |
--|   Contact: contact author (also http://blaz.at/home)                      |
--| ------------------------------------------------------------------------- |
--|    Author: Blaž Kristan (blaz@kristan-sp.si)                              |
--| Copyright (c) 2007-2014, Blaž Kristan. All Rights Reserved.               |
--| ------------------------------------------------------------------------- |
--|   License: Distributed under the Lesser General Public License (LGPL)     |
--|            http://www.gnu.org/copyleft/lesser.html                        |
--| ------------------------------------------------------------------------- |
--| This file is part of N3O CMS (backend).                                   |
--|                                                                           |
--| N3O CMS is free software: you can redistribute it and/or                  |
--| modify it under the terms of the GNU Lesser General Public License as     |
--| published by the Free Software Foundation, either version 3 of the        |
--| License, or (at your option) any later version.                           |
--|                                                                           |
--| N3O CMS is distributed in the hope that it will be useful,                |
--| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
--| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
--| GNU Lesser General Public License for more details.                       |
--'---------------------------------------------------------------------------'
INSERT INTO n3oParameters (ParamName,ParamValue) VALUES ('Version','2.2.1');
INSERT INTO n3oParameters (ParamName,ParamValue) VALUES ('PostMaster','root@localhost');
INSERT INTO n3oParameters (ParamName,ParamValue) VALUES ('ForumAnonymous','No');
INSERT INTO n3oParameters (ParamName,ParamValue) VALUES ('ForumTitle','Forum');
INSERT INTO n3oParameters (ParamName,ParamValue) VALUES ('ForumChat','No');
-- Groups
INSERT INTO SMGroup (GroupID,Name) VALUES (1,'Everyone');
INSERT INTO SMGroup (GroupID,Name) VALUES (2,'Administrators');
INSERT INTO SMGroup (GroupID,Name) VALUES (3,'Power Users');
INSERT INTO SMGroup (GroupID,Name) VALUES (4,'Users');
-- Users (password is Pa$$w0rD)
INSERT INTO SMUser (UserID,Username,`Password`,Active,DefGrp,Name) VALUES (1,'Admin','$1$N3O_CMS:$U31VsZSt0fHSxVuMlCEmF/',1,1,'Administrator');
-- Group membership
INSERT INTO SMUserGroups (GroupID,UserID) VALUES (1,1);
INSERT INTO SMUserGroups (GroupID,UserID) VALUES (2,1);
-- Default ACLs
INSERT INTO SMACL (ACLID,Name) VALUES (1,'Admin');
INSERT INTO SMACLr (ACLID,UserID,MemberACL) VALUES (1,1,'LRWDX');
INSERT INTO SMACLr (ACLID,GroupID,MemberACL) VALUES (1,2,'LRWDX');
-- Admin actions
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('00','System',1,NULL,NULL,1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0001','Menus',1,'sysMenus','folder',0,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0002','',1,NULL,NULL,0,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0003','Users',1,'sysUsers','user',1,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0004','Groups',1,'sysGroups','group',1,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0005','ACL',1,'sysACL','protection',1,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0006','',1,NULL,NULL,1,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0007','Parameters',1,'sysParams','toolbox',1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0008','Languages',1,'sysLang','flags',1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0009','Translations',1,'sysNLSText','flags',1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0010','',1,NULL,NULL,1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0011','Templates',1,'sysTemplates','brick',1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0012','SQL',1,'../inc.php?Izbor=SQL','process',1 ,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('01','Content',1,NULL,NULL,1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0101','Categories',1,'Categories','sitemap',0,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0102','Texts',1,'Text','edit',1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0103','Files',1,'Media','attachment',1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0104','',1,NULL,NULL,1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0105','Polls',1,'Polls','accept',1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('02','Mailing lists',1,NULL,NULL,0,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0201','Groups',1,'emlGroups','folder',0,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0202','Members',1,'emlUsers','user',0,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0203','',1,NULL,NULL,0,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0204','Messages',1,'emlMessages','mail',0,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('03','Forums',1,NULL,NULL,0,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0301','Categories',1,'frmCategories','folder',0,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0302','Threads',1,'frmForums','cloud_comment',0,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0303','Chats',0,'frmChat','sms',0,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0304','',1,NULL,NULL,0,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0305','Members',1,'frmMembers','user',0,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0306','Parameters',1,'frmSetup','toolbox',0,NULL);
-- Languages
INSERT INTO Jeziki(Jezik,Opis,Enabled,Charset,DefLang,LangCode) VALUES ('En','English',1,'utf-8',1,'en');
INSERT INTO Jeziki(Jezik,Opis,Enabled,Charset,DefLang,LangCode) VALUES ('De','Deutsch',0,'utf-8',0,'de');
INSERT INTO Jeziki(Jezik,Opis,Enabled,Charset,DefLang,LangCode) VALUES ('Fr','Francois',0,'utf-8',0,'fr');
INSERT INTO Jeziki(Jezik,Opis,Enabled,Charset,DefLang,LangCode) VALUES ('Hr','Hrvatski',0,'utf-8',0,'hr');
INSERT INTO Jeziki(Jezik,Opis,Enabled,Charset,DefLang,LangCode) VALUES ('Sl','Slovenski',1,'utf-8',0,'sl');
INSERT INTO Jeziki(Jezik,Opis,Enabled,Charset,DefLang,LangCode) VALUES ('Sr','Srpski',0,'utf-8',0,'sr');
-- Parameters
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifNVal1,ACLID,SifNVal1Desc) VALUES ('PARA',1,'ListMax',25,1,'Max row count');
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifLVal1,ACLID,SifLVal1Desc) VALUES ('PARA',2,'BESESimple',0,1,'Simple text editing');
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifNVal1,SifNVal2,SifNVal3,SifLVal1,ACLID) VALUES ('PARA',3,'PageSetup',810,160,0,1,1);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifLVal1,ACLID) VALUES ('PARA',4,'AppJs',0,1);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifNVal1,SifLVal1,ACLID,SifNVal1Desc) VALUES ('PARA',5,'AppPic',100,0,1,'Thumbnail JPEG quality');
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifLVal1,ACLID) VALUES ('PARA',6,'AppInc',0,1);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifLVal1,ACLID) VALUES ('PARA',7,'AppPMaster',0,1);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifLVal1,ACLID) VALUES ('PARA',8,'AppLDAPSvr',0,1);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifLVal1,ACLID) VALUES ('PARA',9,'AppLDAPChk',0,1);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifLVal1,ACLID) VALUES ('PARA',10,'MailSrv',0,1);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifNVal1,SifNVal2,ACLID) VALUES ('PARA',11,'BlogPosts',7,30,1);
INSERT INTO SifrantiTxt (SifrantID,Jezik,SifNaziv,SifCVal1,SifCVal2,SifCVal3)
	SELECT SifrantID,NULL,'Default colors','white;#398ec6;#ff9933','#eff7ff;#c6e7ff;#ffffff;#ffeedd','#000000;#4747d3;#ffffff;#ff6600'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='PageSetup';
INSERT INTO SifrantiTxt (SifrantID,Jezik,SifNaziv)
	SELECT SifrantID,NULL,'./js'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppJs';
INSERT INTO SifrantiTxt (SifrantID,Jezik,SifNaziv)
	SELECT SifrantID,NULL,'./pic'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppPic';
INSERT INTO SifrantiTxt (SifrantID,Jezik,SifNaziv)
	SELECT SifrantID,NULL,'./inc'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppInc';
INSERT INTO SifrantiTxt (SifrantID,Jezik,SifNaziv)
	SELECT SifrantID,NULL,'root@localhost'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppPMaster';
INSERT INTO SifrantiTxt (SifrantID,Jezik,SifNaziv)
	SELECT SifrantID,NULL,'ldapsrv.domain.com'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppLDAPSrv';
INSERT INTO SifrantiTxt (SifrantID,Jezik,SifNaziv)
	SELECT SifrantID,NULL,'DC=domain,DC=com'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppLDAPChk';
INSERT INTO SifrantiTxt (SifrantID,Jezik,SifNaziv)
	SELECT SifrantID,NULL,'localhost'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='MailSrv';
UPDATE Sifranti SET SifNVal1Desc='Content width',SifNVal2Desc='Menu width',SifNVal3Desc='Extra width',SifLVal1Desc='Centered page',SifLVal2Desc='Use textual permalinks'
	 WHERE SifrCtrl='PARA' AND SifrText LIKE 'Page%';
UPDATE SifrantiTxt SET SifNazivDesc='Color scheme',SifCVal1Desc='page;frame;fr. extra',SifCVal2Desc='bg;bg lo;bg hi;bg extra',SifCVal3Desc='text;link;txt frame;txt extra'
	WHERE SifrantID = (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText LIKE 'Page%');
UPDATE Sifranti SET SifNVal1Desc='Max blog posts per page',SifNVal2Desc='Max blog post age in days',SifLVal1Desc='Blog comments allowed'
	 WHERE SifrCtrl='PARA' AND SifrText='BlogPosts';
UPDATE SifrantiTxt SET SifNazivDesc='Common JavaScript path'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppJs');
UPDATE SifrantiTxt SET SifNazivDesc='Common image path'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppPic');
UPDATE SifrantiTxt SET SifNazivDesc='Common include path'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppInc');
UPDATE SifrantiTxt SET SifNazivDesc='Administrator email',SifCVal1Desc='Admin real name',SifCVal2Desc='Twitter''s site @username'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppPMaster');
UPDATE SifrantiTxt SET SifNazivDesc='LDAP server hostname',SifCVal1Desc='LDAP login',SifCVal2Desc='LDAP password'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppLDAPSrv');
UPDATE SifrantiTxt SET SifNazivDesc='LDAP filter'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppLDAPChk');
UPDATE Sifranti SET SifNVal1Desc='Mail server port',SifLVal1Desc='Use SSL'
	WHERE SifrCtrl='PARA' AND SifrText='MailSrv';
UPDATE SifrantiTxt SET SifNazivDesc='Mail server hostname',SifCVal1Desc='Mail login',SifCVal2Desc='Mail password',SifCVal3Desc='Administrator email address'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='MailSrv');
-- Text parameters
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifNVal1,SifNVal2,ACLID) VALUES ('BESE',1,'Text',512,96,NULL);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifNVal1,SifNVal2,ACLID) VALUES ('BESE',2,'News',256,64,NULL);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifNVal1,SifNVal2,SifLVal1,ACLID) VALUES ('BESE',3,'Blog',512,96,1,NULL);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,SifNVal1,SifNVal2,SifNval3,ACLID) VALUES ('BESE',3,'Gallery',640,128,1024,NULL);
INSERT INTO SifrantiTxt (SifrantID,SifNaziv)
	SELECT SifrantID,'gallery' FROM Sifranti WHERE SifrCtrl='BESE' AND SifrText='Text';
INSERT INTO SifrantiTxt (SifrantID,SifNaziv)
	SELECT SifrantID,'gallery' FROM Sifranti WHERE SifrCtrl='BESE' AND SifrText='News';
INSERT INTO SifrantiTxt (SifrantID,SifNaziv)
	SELECT SifrantID,'gallery' FROM Sifranti WHERE SifrCtrl='BESE' AND SifrText='Blog';
INSERT INTO SifrantiTxt (SifrantID,SifNaziv)
	SELECT SifrantID,'gallery' FROM Sifranti WHERE SifrCtrl='BESE' AND SifrText='Gallery';
UPDATE Sifranti SET SifNVal1Desc='Max reduced image size',SifNVal2Desc='Max thumbnail image size',SifNVal3Desc='Max original image size',SifLVal1Desc='Create forum topic for comments'
	WHERE SifrCtrl='BESE';
UPDATE SifrantiTxt SET SifNazivDesc='Default image folder'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='BESE');
-- social media
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,ACLID) VALUES ('SOCI',1,'RSS',NULL);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,ACLID) VALUES ('SOCI',2,'Twitter',NULL);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,ACLID) VALUES ('SOCI',3,'Facebook',NULL);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,ACLID) VALUES ('SOCI',4,'YouTube',NULL);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,ACLID) VALUES ('SOCI',5,'LinkedIn',NULL);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,ACLID) VALUES ('SOCI',6,'GooglePlus',NULL);
INSERT INTO Sifranti (SifrCtrl,SifrZapo,SifrText,ACLID) VALUES ('SOCI',7,'Flickr',NULL);
INSERT INTO SifrantiTxt (SifrantID,SifNaziv,SifNazivDesc)
	SELECT SifrantID,'./RSS.php','URL' FROM Sifranti WHERE SifrCtrl='SOCI' AND SifrText='RSS';
-- Forums
INSERT INTO frmMembers (Nickname,Password,Email,AccessLevel,Enabled,ShowEmail,MailList,Settings,SignIn) VALUES ('Admin','$1$N3O_CMS:$U31VsZSt0fHSxVuMlCEmF/','root@localhost',5,0,0,0,'Rows=10,Slika=default,Color=red',now());
-- OBSOLETE!!! just for compatibility (will be removed)
INSERT INTO frmParameters (ParamName,ParamValue,ParamText) VALUES ('AllowAnonymous','No','Allow anonymous users to post messages.');
INSERT INTO frmParameters (ParamName,ParamValue,ParamText) VALUES ('ForumTitle','Forum','Window title.');
INSERT INTO frmParameters (ParamName,ParamValue,ParamText) VALUES ('ForumChat','No','Allow chat application.');
-- Page templates
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'FrontPage',NULL,'_frontpage.php',0,'',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'Blog',NULL,'_blog.php',0,'',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'Gallery',NULL,'_galleries.php',0,'',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'Texts (list)',NULL,'_texts_list.php',0,'',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'Texts (grid)',NULL,'_texts_grid.php',0,'Pogled besedil v mreži (grid) s slikami.',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'Texts (all)',NULL,'_texts_all.php',0,'',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'Last5Images',NULL,'_gallery_last4.php',0,'',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'SearchResults',NULL,'_search_results.php',0,'',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'Image',NULL,'_image.php',0,'',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'PageMenu',NULL,'_categories.php',2,'Navigacijski seznam rubrik (levi menu).',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'Attachments',NULL,'_attachments.php',2,'',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'LatestImages',NULL,'_gallery_latest.php',0,'',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'RandomImage',NULL,'_random_image.php',2,'',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'Archive',NULL,'_arhiv_left.php',2,'',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'Tags',NULL,'_tags.php',2,'Izpis vseh oznak (tag) z vsaj enim besedilom.',NULL,1);
INSERT INTO Predloge (Jezik,Naziv,Slika,Datoteka,Tip,Opis,ACLID,Enabled) VALUES (NULL,'Tweet',NULL,'_latest_tweet.php',2,'Prikaz zadnjega vpisa na omrežju Twitter.',NULL,1);
-- Page structure
INSERT INTO Kategorije (KategorijaID,Izpis,Iskanje,Ime,Slika,ACLID) VALUES ('00',0,0,'home',NULL,NULL);
INSERT INTO Kategorije (KategorijaID,Izpis,Iskanje,Ime,Slika,ACLID) VALUES ('01',1,1,'blog',NULL,NULL);
INSERT INTO Kategorije (KategorijaID,Izpis,Iskanje,Ime,Slika,ACLID) VALUES ('02',1,1,'texts',NULL,NULL);
INSERT INTO Kategorije (KategorijaID,Izpis,Iskanje,Ime,Slika,ACLID) VALUES ('03',1,1,'photography',NULL,NULL);
INSERT INTO Kategorije (KategorijaID,Izpis,Iskanje,Ime,Slika,ACLID) VALUES ('0301',1,0,'latest',NULL,NULL);
INSERT INTO Kategorije (KategorijaID,Izpis,Iskanje,Ime,Slika,ACLID) VALUES ('0302',0,1,'nature',NULL,NULL);
INSERT INTO Kategorije (KategorijaID,Izpis,Iskanje,Ime,Slika,ACLID) VALUES ('04',1,0,'search',NULL,NULL);
INSERT INTO Kategorije (KategorijaID,Izpis,Iskanje,Ime,Slika,ACLID) VALUES ('05',1,1,'about',NULL,NULL);
INSERT INTO Kategorije (KategorijaID,Izpis,Iskanje,Ime,Slika,ACLID) VALUES ('0501',0,0,'cookies',NULL,NULL);
-- Page layout
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('00',1,1,0);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('00',10,1,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('00',13,2,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('00',11,3,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('00',16,4,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('01',7,1,0);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('01',2,2,0);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('01',10,1,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('01',13,2,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('01',14,3,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('01',15,4,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('01',16,5,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('02',4,1,0);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('02',5,2,0);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('02',10,1,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('02',13,2,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('02',11,3,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('02',16,4,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('03',3,1,0);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('03',10,1,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('03',13,2,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('03',11,3,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('03',16,4,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('0301',12,1,0);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('04',8,1,0);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('04',10,1,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('04',13,2,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('05',6,1,0);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('05',10,1,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('05',13,2,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('05',11,3,2);
INSERT INTO KategorijeVsebina (KategorijaID,PredlogaID,Polozaj,Ekstra) VALUES ('05',16,4,2);
-- Populate NLS texts (Si and En)
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Admin','Admin',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','AdvancedSearch','Advanced search',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Agree','I agree',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','allWords','with <B>all</B> of the words',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Archive','Archive',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','atLeastOneWord','with <B>at least one</B> of the words',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Attachments','Attachments',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Back','Back',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Browser',NULL,'<b>Your browser is <em>ancient!</em></b> <a href=\"http://browsehappy.com/\">Upgrade to a different browser</a> to fully experience this site.');
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Catalogue','Catalogue',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Categories','Categories',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','ClickLarge','Click for larger image',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Close','Close',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','CommApproval','* Comments need to be approved by admin.',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Comments','Comments',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Content','Content',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Cookies',NULL,'This site uses cookies for better user experience and usage statistics.<br />\nBy continuing to access this site or clicking \"<strong>I agree</strong>\",you agree to our use of cookies in your browser.');
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','CopyRight',NULL,'Copyright © 2007-[year] <b>Web Owner</b>. All rights reserved.<BR>No images may be used without <A HREF=\"mailto:[PostMaster]?Subject=Permission\">written</A> permission.');
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Date','Date',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','dateText','Return pages updated in the',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','dontContain','without the words',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Email','Email',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Error','Error!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','exactPhrase','with the <B>exact phrase</B>',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Files','Files',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','FindPages','Find results',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Follow','Follow',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','FollowMe','Follow me ...',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','ForumLink','Discuss in forum',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Gallery','Images',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','HearALot','You can hear a lot on Twitter.',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','image','image',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','img_ending','&nbsp;,s,s,s',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','JavaScript','Please enable JavaScript in your browser for richer experience of this site.',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','ListEmpty','No data!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Login','Log in',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','LoginGeneralError','General error!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','LoginNoConnection','No server connection!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','LoginNoUser','No such user!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','LoginRedirect',NULL,'Click <A HREF=\"[Referer]\">here</A> if you are not redirected automatically.');
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','LoginSuccess','Successful login!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','LoginWrongPW','Wrong password!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','MessageSent','Message sent!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Name','Name',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Newer','Newer',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Next','Next',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','NextPage','Next page',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Occurrences','Occurrences',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','occurrenceText','Return results where my terms occur',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Older','Older',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Page','Page',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Password','Password:',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Poll','Poll',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','PollAllVotes','All votes',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','PostedBy','Posted by',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Prev','Previous',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','PrevPage','Previous page',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Reject','I don\'t agree',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','res10','10 results',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','res1y','past year',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','res25','25 results',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','res3m','past 3m',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','res50','50 results',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','res6m','past 6m',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','resAll','anytime',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','resAny','anywhere in the page',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','resBody','in the text of the page',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','resTitle','in the title of the page',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','rows','rows',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Search','Search',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','SeeAlso','See also ...',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Send','Send',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Show','Show',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','SlikaDneva','Picture of the day',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','StartSlideshow','Start slide-show',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','StopSlideshow','Stop slide-show',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Subscribe','Subscribe',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','SubTitle','Web Subtitle.',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Tags','Tags',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Title','Web Title',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','ToGallery','to&nbsp;gallery',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','Username','Username:',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('En','VisitAlso','Visit also ...',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Admin','Urejanje',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','AdvancedSearch','Napredno iskanje',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Agree','Se strinjam',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','allWords','ki vsebujejo <b>vse</b> besede',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Archive','Arhiv',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','atLeastOneWord','ki vsebujejo <b>vsaj eno</b> izmed besed',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Attachments','Priloge',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Back','Nazaj',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Browser',NULL,'<b>Tvoj brskalnik je <em>zastarel!</em></b> <a href=\"http://browsehappy.com/\">Nadgradi ali zamenjaj ga</a>,da boš v popolnosti doživel(a) to stran.');
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Catalogue','Katalog',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Categories','Rubrike',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','ClickLarge','Klikni za večjo sliko',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Close','Zapri',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','CommApproval','* Komentarje mora odobriti admin.',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Comments','Komentarji',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Content','Vsebina',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Cookies',NULL,'Spletna stran uporablja piškotke za boljšo uporabniško izkušnjo in spremljanje statistike obiskov.<br />\nZ nadaljno uporabo spletne strani ali klikom na \"<strong>Strinjam se</strong>\",se strinjate z uporabo piškotkov.');
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','CopyRight',NULL,'Copyright © 2007-[year] <b>Web Lastnik</b>. Vse pravice pridržane.<br>\nSlike lahko uporabite samo po predhodnem <A HREF=\"mailto:[PostMaster]?Subject=Dovoljenje\">pisnem</A> dovoljenju.');
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Date','Datum',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','dateText','Najdi strani,ki so bile spremenjene',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','dontContain','ki <b>ne vsebujejo</b> besed',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Email','e-Naslov',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Error','Napaka!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','exactPhrase','ki vsebujejo <b>točno to besedno zvezo</b>',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Files','Datoteke',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','FindPages','Najdi strani,',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Follow','Sledi',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','FollowMe','Sledi mi ...',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','ForumLink','Sodelujte v diskusijah na to temo',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Gallery','Slike',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','HearALot','Na Twitterju se sliši marsikaj!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','image','slik',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','img_ending','a,i,e,',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','JavaScript','Prosim,vklopite JavaScript za popoln užitek ob ogledu te spletne strani.',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','ListEmpty','Ni podatkov!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Login','Prijava',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','LoginGeneralError','Splošna napaka!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','LoginNoConnection','Ni povezave s strežnikom!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','LoginNoUser','Ni uporabnika!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','LoginRedirect',NULL,'Kliknite <A HREF=\"[Referer]\">tule</A>,če niste samodejno preusmerjeni na naslednjo stran.');
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','LoginSuccess','Prijava uspešna!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','LoginWrongPW','Napačno geslo!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','MessageSent','Sporočilo odposlano!',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Name','Ime',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Newer','Novejše',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Next','Naslednji',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','NextPage','Naslednja stran',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Occurrences','Pojavljanje',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','occurrenceText','Vrni strani,kjer se iskani izrazi pojavijo',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Older','Starejše',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Page','Stran',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Password','Geslo:',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Poll','Anketa',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','PollAllVotes','Vseh glasov',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','PostedBy','Objavil',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Prev','Predhodni',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','PrevPage','Predhodna stran',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Reject','Se ne strinjam',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','res10','10 rezultatov',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','res1y','v preteklem letu',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','res25','25 rezultatov',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','res3m','v preteklih 3m',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','res50','50 rezultatov',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','res6m','v preteklih 6m',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','resAll','kadarkoli',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','resAny','kjerkoli na strani',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','resBody','v besedilu strani',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','resTitle','v naslovu strani',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','rows','vrstic',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Search','Išči',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','SeeAlso','Preberite tudi ...',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Send','Oddaj',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Show','Prikaži',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','SlikaDneva','Slika dneva',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','StartSlideshow','Predvajaj slike',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','StopSlideshow','Prekini predvajanje',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Subscribe','Naroči se',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','SubTitle','Web Subtitle',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Tags','Oznake',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Title','Web Title',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','ToGallery','v&nbsp;galerijo',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','Username','Uporabniško ime:',NULL);
INSERT INTO NLSText (Jezik,NLSToken,NLSShort,NLSLong) VALUES ('Sl','VisitAlso','Oglejte si tudi ...',NULL);
-- Sample text
INSERT INTO Besedila (Izpis,Datum,DatumObjave,Ime,Slika,Center,URL,Tip,Avtor,ACLID,ForumTopicID) VALUES (1,now(),now(),'sample-text',NULL,0,NULL,'Text',1,NULL,NULL);
INSERT INTO BesedilaOpisi (BesediloID,Jezik,Polozaj,Naslov,Podnaslov,Povzetek,Opis) VALUES (1,'En',1,'Sample text','Subtitle','Abstract','<p align=\"justify\">Lorem ipsum dolor sit amet, risus molestie ac, quam sagittis, mauris nulla, taciti faucibus erat aenean sapien, eu etiam sollicitudin neque. Cras sed quis nullam elit, id venenatis odio tempus eu. Nunc urna non ac nibh arcu faucibus. Id in, nec ac, proin nibh purus, lectus commodo netus rutrum morbi. Enim nibh viverra vel et dui aliquet, augue laoreet odio urna primis wisi dolor, mus aut facilis semper. Tempor molestiae magna lectus, ante eu aliquet odio mauris, amet mauris suscipit vestibulum ante est hac, ligula arcu. Risus pede non nunc aenean pede ac. Magnis vestibulum magna sed porttitor ante massa. Nec rutrum laoreet vivamus, mattis aliquam ante ac risus sit arcu. Dictum laoreet lorem ut nec, mus varius nam nam suspendisse mattis ut. Wisi enim ullamcorper lectus consequat enim est, aut morbi non, sed lacinia velit cras penatibus, non nunc eu urna est non, erat libero.</p>\r\n<p align=\"justify\">Scelerisque eu. Velit eleifend eget a gravida nulla posuere. Erat hendrerit tellus vivamus, nulla iaculis ac massa, sed sit odio suspendisse ac pellentesque, urna pellentesque in vulputate dolor, etiam et feugiat faucibus rutrum feugiat. Congue sed odio vitae volutpat luctus et, nam itaque lectus sapien feugiat. Mattis tincidunt. Platea consequat pulvinar dolore et volutpat, bibendum posuere suspendisse, mattis bibendum, eget ligula porttitor amet, montes vivamus sed. Mattis sodales, elit quis ultricies, elit magnis magnis dictum morbi interdum. Consectetuer erat sollicitudin metus, mus nulla iaculis sit metus. Fringilla suscipit vel, quis neque ultricies netus eget turpis augue, lectus in odio urna adipiscing vulputate cras.</p>');
INSERT INTO BesedilaOpisi (BesediloID,Jezik,Polozaj,Naslov,Podnaslov,Povzetek,Opis) VALUES (1,'Sl',1,'Vzorčno besedilo','Podnaslov','Povzetek','<p align=\"justify\">Lorem ipsum dolor sit amet, risus molestie ac, quam sagittis, mauris nulla, taciti faucibus erat aenean sapien, eu etiam sollicitudin neque. Cras sed quis nullam elit, id venenatis odio tempus eu. Nunc urna non ac nibh arcu faucibus. Id in, nec ac, proin nibh purus, lectus commodo netus rutrum morbi. Enim nibh viverra vel et dui aliquet, augue laoreet odio urna primis wisi dolor, mus aut facilis semper. Tempor molestiae magna lectus, ante eu aliquet odio mauris, amet mauris suscipit vestibulum ante est hac, ligula arcu. Risus pede non nunc aenean pede ac. Magnis vestibulum magna sed porttitor ante massa. Nec rutrum laoreet vivamus, mattis aliquam ante ac risus sit arcu. Dictum laoreet lorem ut nec, mus varius nam nam suspendisse mattis ut. Wisi enim ullamcorper lectus consequat enim est, aut morbi non, sed lacinia velit cras penatibus, non nunc eu urna est non, erat libero.</p>\n<p align=\"justify\">Scelerisque eu. Velit eleifend eget a gravida nulla posuere. Erat hendrerit tellus vivamus, nulla iaculis ac massa, sed sit odio suspendisse ac pellentesque, urna pellentesque in vulputate dolor, etiam et feugiat faucibus rutrum feugiat. Congue sed odio vitae volutpat luctus et, nam itaque lectus sapien feugiat. Mattis tincidunt. Platea consequat pulvinar dolore et volutpat, bibendum posuere suspendisse, mattis bibendum, eget ligula porttitor amet, montes vivamus sed. Mattis sodales, elit quis ultricies, elit magnis magnis dictum morbi interdum. Consectetuer erat sollicitudin metus, mus nulla iaculis sit metus. Fringilla suscipit vel, quis neque ultricies netus eget turpis augue, lectus in odio urna adipiscing vulputate cras.</p>');
INSERT INTO KategorijeBesedila (KategorijaID,BesediloID,Polozaj) VALUES ('00',1,1);
INSERT INTO KategorijeBesedila (KategorijaID,BesediloID,Polozaj) VALUES ('01',1,1);
INSERT INTO KategorijeBesedila (KategorijaID,BesediloID,Polozaj) VALUES ('02',1,1);
INSERT INTO KategorijeBesedila (KategorijaID,BesediloID,Polozaj) VALUES ('03',1,1);
INSERT INTO KategorijeBesedila (KategorijaID,BesediloID,Polozaj) VALUES ('05',1,1);
