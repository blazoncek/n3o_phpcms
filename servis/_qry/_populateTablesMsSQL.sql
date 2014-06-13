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
SET IDENTITY_INSERT SMGroup ON;
INSERT INTO SMGroup (GroupID, Name) VALUES (1, 'Everyone');
INSERT INTO SMGroup (GroupID, Name) VALUES (2, 'Administrators');
INSERT INTO SMGroup (GroupID, Name) VALUES (3, 'Power Users');
INSERT INTO SMGroup (GroupID, Name) VALUES (4, 'Users');
SET IDENTITY_INSERT SMGroup OFF;
-- Users (password is Pa$$w0rD)
SET IDENTITY_INSERT SMUser ON;
INSERT INTO SMUser (UserID, Username, Password, Active, DefGrp, Name) VALUES (1, 'Admin', '$1$N3O_CMS:$U31VsZSt0fHSxVuMlCEmF/', 1, 1, 'Administrator'); -- Pa$$w0rD
SET IDENTITY_INSERT SMUser OFF;

INSERT INTO SMUserGroups (GroupID, UserID) VALUES (0, 1);
INSERT INTO SMUserGroups (GroupID, UserID) VALUES (1, 1);

SET IDENTITY_INSERT SMACL ON;
INSERT INTO SMACL (ACLID, Name) VALUES (1, 'Admin');
SET IDENTITY_INSERT SMACL OFF;

INSERT INTO SMACLr (ACLID, UserID, MemberACL) VALUES (1, 1, 'LRWDX');
INSERT INTO SMACLr (ACLID, GroupID, MemberACL) VALUES (1, 2, 'LRWDX');
-- Admin actions
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('00','System',1,NULL,NULL,1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0001','Menus',1,'sysMenus','folder',0,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0002','',1,NULL,NULL,0,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0003','Users',1,'sysUsers','user',1,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0004','Groups',1,'sysGroups','group',1,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0005','ACL',1,'sysACL','protection',1,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0006','',1,NULL,NULL,1,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0007','Audit',1,'sysAudit','bell',1,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0008','',1,NULL,NULL,1,1);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0009','Parameters',1,'sysParams','toolbox',1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0010','Languages',1,'sysLang','flags',1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0011','Translations',1,'sysNLSText','flags',1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0012','',1,NULL,NULL,1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0013','Templates',1,'sysTemplates','brick',1,NULL);
INSERT INTO SMActions (ActionID,Name,Enabled,Action,Icon,MobileCapable,ACLID) VALUES ('0014','SQL',1,'../inc.php?Izbor=SQL','database',1 ,1);
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

INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifNVal1, ACLID) VALUES ('PARA', 1, 'ListMax', 25, 1);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifLVal1, ACLID) VALUES ('PARA', 2, 'BESESimple', 1, 1);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifNVal1, SifNVal2, SifLVal1, ACLID) VALUES ('PARA', 3, 'PageColors', 810, 0, 1, 1);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifLVal1, ACLID) VALUES ('PARA', 4, 'AppJs', 0, 1);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifNVal1, SifLVal1, ACLID) VALUES ('PARA', 5, 'AppPic', 100, 0, 1);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifLVal1, ACLID) VALUES ('PARA', 6, 'AppInc', 0, 1);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifLVal1, ACLID) VALUES ('PARA', 7, 'AppPMaster', 0, 1);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifLVal1, ACLID) VALUES ('PARA', 8, 'AppLDAPSvr', 0, 1);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifLVal1, ACLID) VALUES ('PARA', 9, 'AppLDAPChk', 0, 1);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifLVal1, ACLID) VALUES ('PARA', 10, 'MailSrv', 0, 1);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifNVal1, SifNVal2, ACLID) VALUES ('PARA', 11, 'BlogPosts', 7, 30, 1);

INSERT INTO SifrantiTxt (SifrantID, Jezik, SifNaziv, SifCVal1, SifCVal2, SifCVal3)
	SELECT SifrantID, NULL, 'Modra', 'white;#398ec6;#ff9933', '#eff7ff;#c6e7ff;#ffffff;#ffeedd', '#000000;#4747d3;#ffffff;#ff6600'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='PageColors';
INSERT INTO SifrantiTxt (SifrantID, Jezik, SifNaziv)
	SELECT SifrantID, NULL, '/_js'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppJs';
INSERT INTO SifrantiTxt (SifrantID, Jezik, SifNaziv)
	SELECT SifrantID, NULL, '/_pic'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppPic';
INSERT INTO SifrantiTxt (SifrantID, Jezik, SifNaziv)
	SELECT SifrantID, NULL, '/_inc'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppInc';
INSERT INTO SifrantiTxt (SifrantID, Jezik, SifNaziv)
	SELECT SifrantID, NULL, 'root@localhost'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppPMaster';
INSERT INTO SifrantiTxt (SifrantID, Jezik, SifNaziv)
	SELECT SifrantID, NULL, 'ldapsrv.domain.com'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppLDAPSrv';
INSERT INTO SifrantiTxt (SifrantID, Jezik, SifNaziv)
	SELECT SifrantID, NULL, 'DC=domain,DC=com'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppLDAPChk';
INSERT INTO SifrantiTxt (SifrantID, Jezik, SifNaziv)
	SELECT SifrantID, NULL, 'localhost'
	FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='MailSrv';

UPDATE Sifranti SET SifNVal1Desc='Max row count'
	WHERE SifrCtrl='PARA' AND SifrText='ListMax';
UPDATE Sifranti SET SifLVal1Desc='Simple text editing'
	WHERE SifrCtrl='PARA' AND SifrText='BESESimple';
UPDATE Sifranti SET SifNVal1Desc='Content width', SifNVal2Desc='Menu width', SifNVal3Desc='Extra width', SifLVal1Desc='Centered page', SifLVal2Desc='Use textual permalinks'
	 WHERE SifrCtrl='PARA' AND SifrText LIKE 'Page%';
UPDATE SifrantiTxt SET SifNazivDesc='Color scheme', SifCVal1Desc='page;frame;fr. extra', SifCVal2Desc='bg;bg lo;bg hi;bg extra', SifCVal3Desc='text;link;txt frame;txt extra'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText LIKE 'Page%');
UPDATE Sifranti SET SifNVal1Desc='Max thumbnail size'
	 WHERE SifrCtrl='PARA' AND SifrText='AppPic';
UPDATE Sifranti SET SifNVal1Desc='Max blog posts per page', SifNVal2Desc='Max blog post age in days', SifLVal1Desc='Blog comments allowed'
	 WHERE SifrCtrl='PARA' AND SifrText='BlogPosts';
UPDATE SifrantiTxt SET SifNazivDesc='Common JavaScript path'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppJs');
UPDATE SifrantiTxt SET SifNazivDesc='Common image path'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppPic');
UPDATE SifrantiTxt SET SifNazivDesc='Common include path'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppInc');
UPDATE SifrantiTxt SET SifNazivDesc='Administrator email', SifCVal1Desc='Admin real name', SifCVal2Desc='Twitter''s site @username'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppPMaster');
UPDATE SifrantiTxt SET SifNazivDesc='LDAP server hostname', SifCVal1Desc='LDAP login', SifCVal2Desc='LDAP password'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppLDAPSrv');
UPDATE SifrantiTxt SET SifNazivDesc='LDAP filter'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='AppLDAPChk');
UPDATE Sifranti SET SifNVal1Desc='Mail server port', SifLVal1Desc='Use SSL'
	WHERE SifrCtrl='PARA' AND SifrText='MailSrv';
UPDATE SifrantiTxt SET SifNazivDesc='Mail server hostname', SifCVal1Desc='Mail login', SifCVal2Desc='Mail password', SifCVal3Desc='Administrator email address'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='PARA' AND SifrText='MailSrv');

INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifNVal1, SifNVal2, ACLID) VALUES ('BESE', 1, 'Besedilo', 540, 60, NULL);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifNVal1, SifNVal2, ACLID) VALUES ('BESE', 2, 'Novica', 240, 60, NULL);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifNVal1, SifNVal2, ACLID) VALUES ('BESE', 3, 'Blog', 540, 90, NULL);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, SifNVal1, SifNVal2, SifLVal1, ACLID) VALUES ('BESE', 3, 'Galerija', 540, 90, 1, NULL);

INSERT INTO SifrantiTxt (SifrantID, SifNaziv)
	SELECT SifrantID, 'galerija'
	FROM Sifranti WHERE SifrCtrl='BESE' AND SifrText='Besedilo';
INSERT INTO SifrantiTxt (SifrantID, SifNaziv)
	SELECT SifrantID, 'galerija'
	FROM Sifranti WHERE SifrCtrl='BESE' AND SifrText='Novica';
INSERT INTO SifrantiTxt (SifrantID, SifNaziv)
	SELECT SifrantID, 'galerija'
	FROM Sifranti WHERE SifrCtrl='BESE' AND SifrText='Blog';
INSERT INTO SifrantiTxt (SifrantID, SifNaziv)
	SELECT SifrantID, 'galerija'
	FROM Sifranti WHERE SifrCtrl='BESE' AND SifrText='Galerija';

UPDATE Sifranti SET SifNVal1Desc='Max reduced image size', SifNVal2Desc='Max thumbnail image size', SifLVal1Desc='Create forum topic for comments'
	WHERE SifrCtrl='BESE';
UPDATE SifrantiTxt SET SifNazivDesc='Default image folder'
	WHERE SifrantID IN (SELECT SifrantID FROM Sifranti WHERE SifrCtrl='BESE');

INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, ACLID) VALUES ('SOCI', 1, 'RSS', NULL);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, ACLID) VALUES ('SOCI', 2, 'Twitter', NULL);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, ACLID) VALUES ('SOCI', 3, 'Facebook', NULL);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, ACLID) VALUES ('SOCI', 4, 'YouTube', NULL);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, ACLID) VALUES ('SOCI', 5, 'LinkedIn', NULL);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, ACLID) VALUES ('SOCI', 6, 'GooglePlus', NULL);
INSERT INTO Sifranti (SifrCtrl, SifrZapo, SifrText, ACLID) VALUES ('SOCI', 7, 'Flickr', NULL);

INSERT INTO SifrantiTxt (SifrantID, SifNaziv, SifNazivDesc)
	SELECT SifrantID, './RSS.php', 'URL'
	FROM Sifranti WHERE SifrCtrl='SOCI' AND SifrText='RSS';

INSERT INTO Kategorije (KategorijaID, Izpis, Ime) VALUES ('00', 1, 'Home');

SET identity_insert frmMembers ON;
INSERT INTO frmMembers (ID, Nickname, Password, Email, AccessLevel, Enabled, ShowEmail, MailList, Settings, SignIn)
	VALUES (1, 'Admin', 'nimda', 'admin@admin.com', 5, 1, 0, 0, 'Rows=10,Slika=default,Color=red', getdate());
SET identity_insert frmMembers off;

INSERT INTO n3oParameters (ParamName, ParamValue) VALUES ('ForumAnonymous', 'No');
INSERT INTO n3oParameters (ParamName, ParamValue) VALUES ('ForumTitle', 'Forum');
INSERT INTO n3oParameters (ParamName, ParamValue) VALUES ('ForumChat', 'No');

-- OBSOLETE!!! just for compatibility (will be removed)
INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('AllowAnonymous', 'No', 'Allow anonymous users to post messages.');
INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('ForumTitle', 'Forum', 'Window title.');
INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('ForumChat', 'No', 'Allow chat application.');

-- Should be defined elswhere since forum is part of N3O application
-- INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('ForumAlign', 'Center', 'Forum tables alignment.');
-- INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('ForumWidth', '760', 'Forum tables width.');
-- INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('PageColor', 'White', 'Page background color.');
-- INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('TextColor', '#000000', 'Default text color.');
-- INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('TextColorHi', '#ffffff', 'Hilited text color (used for table headers).');
-- INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('TextColorEx', '#ff6600', 'Extra text color (errors, warnings).');
-- INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('LinkColor', '#4747d3', 'Link color.');
-- INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('FrameColor', '#398ec6', 'Table frame color.');
-- INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('BackgColor', '#eff7ff', 'Default table background color.');
-- INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('BackgColorHi', '#ffffff', 'Hilited table background color.');
-- INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('BackgColorLo', '#c6e7ff', 'Table subtitle background color.');
-- INSERT INTO frmParameters (ParamName, ParamValue, ParamText) VALUES ('ButtonColor', 'LightGrey', 'Form button face color.');
