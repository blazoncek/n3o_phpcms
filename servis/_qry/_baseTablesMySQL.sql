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

CREATE TABLE n3oParameters (
	ParamName varchar(16) NOT NULL PRIMARY KEY,
	ParamValue varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE SMGroup (
	GroupID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	Name varchar(64) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE SMUser (
	UserID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	Username varchar(16) NOT NULL UNIQUE,
	Password varchar(128) NOT NULL,
	Name varchar(64) NULL,
	Email varchar(64) NULL,
	Phone varchar(32) NULL,
	DefGrp int NULL,
	Active boolean NOT NULL DEFAULT 0,
	LastLogon datetime NULL,
	TwitterName varchar(32) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE SMUser ADD CONSTRAINT USR_FK_GRP FOREIGN KEY (DefGrp) REFERENCES SMGroup (GroupID);
CREATE INDEX USR_NI1 ON SMUser (Username);
CREATE INDEX USR_NI2 ON SMUser (Email);

CREATE TABLE SMUserGroups (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	GroupID int NOT NULL,
	UserID int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE SMUserGroups ADD CONSTRAINT USG_FK_GRP FOREIGN KEY (GroupID) REFERENCES SMGroup (GroupID);
ALTER TABLE SMUserGroups ADD CONSTRAINT USG_FK_USR FOREIGN KEY (UserID) REFERENCES SMUser(UserID);
CREATE UNIQUE INDEX USG_I1 ON SMUserGroups (GroupID,UserID);

CREATE TABLE SMACL (
	ACLID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	Name varchar(64) NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE SMACLr (
	ACLID int NOT NULL,
	UserID int NULL,
	GroupID int NULL,
	MemberACL char(5) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE SMACLr ADD CONSTRAINT ACR_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);
ALTER TABLE SMACLr ADD CONSTRAINT ACR_FK_USR FOREIGN KEY (UserID) REFERENCES SMUser (UserID);
ALTER TABLE SMACLr ADD CONSTRAINT ACR_FK_GRP FOREIGN KEY (GroupID) REFERENCES SMGroup (GroupID);
CREATE UNIQUE INDEX ACR_I1 ON SMACLr (ACLID,GroupID);
CREATE UNIQUE INDEX ACR_I2 ON SMACLr (ACLID,UserID);

CREATE TABLE SMActions (
	ActionID varchar(10) NOT NULL PRIMARY KEY,
	Name varchar(64) NULL,
	Enabled boolean NOT NULL DEFAULT 0,
	Action varchar(128) NULL,
	Icon varchar(64) NULL,
	MobileCapable boolean NULL DEFAULT 0,
	ACLID int NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE SMActions ADD CONSTRAINT ACT_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);

CREATE TABLE SMAudit (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	DateOfEntry timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	UserID int NOT NULL,
	ObjectID int NULL,
	ObjectType varchar(16) NULL,
	Action varchar(64) NULL,
	Description varchar(512) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE SMAudit ADD CONSTRAINT AUD_FK_USR FOREIGN KEY (UserID) REFERENCES SMUser(UserID);

CREATE TABLE Jeziki (
	Jezik char(2) NOT NULL PRIMARY KEY,
	Opis varchar(64) NULL,
	Ikona varchar(64) NULL,
	Enabled boolean NULL DEFAULT 0,
	CharSet varchar(64) NULL,
	DefLang boolean NULL DEFAULT 0,
	LangCode varchar(6) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE Sifranti (
	SifrantID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	SifrCtrl char(4) NOT NULL,
	SifrZapo smallint NOT NULL,
	SifrText varchar(10) NULL,
	SifNVal1 decimal(15,4) NULL,
	SifNVal2 decimal(15,4) NULL,
	SifNVal3 decimal(15,4) NULL,
	SifDVal1 datetime NULL,
	SifDVal2 datetime NULL,
	SifDVal3 datetime NULL,
	SifLVal1 boolean NOT NULL DEFAULT 0,
	SifLVal2 boolean NOT NULL DEFAULT 0,
	ACLID int NULL,
	SifNVal1Desc varchar(128) NULL,
	SifNVal2Desc varchar(128) NULL,
	SifNVal3Desc varchar(128) NULL,
	SifDVal1Desc varchar(128) NULL,
	SifDVal2Desc varchar(128) NULL,
	SifDVal3Desc varchar(128) NULL,
	SifLVal1Desc varchar(128) NULL,
	SifLVal2Desc varchar(128) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE Sifranti ADD CONSTRAINT SIF_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);
CREATE INDEX SIF_CI ON Sifranti (SifrCtrl, SifrZapo);

CREATE TABLE SifrantiTxt (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	SifrantID int NOT NULL,
	Jezik char(2) NULL,
	SifNaziv varchar(64) NULL,
	SifCVal1 varchar(128) NULL,
	SifCVal2 varchar(128) NULL,
	SifCVal3 varchar(128) NULL,
	SifNazivDesc varchar(128) NULL,
	SifCVal1Desc varchar(128) NULL,
	SifCVal2Desc varchar(128) NULL,
	SifCVal3Desc varchar(128) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE SifrantiTxt ADD CONSTRAINT SIT_FK_SIF FOREIGN KEY (SifrantID) REFERENCES Sifranti (SifrantID);
ALTER TABLE SifrantiTxt ADD CONSTRAINT SIT_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);

CREATE TABLE Predloge (
	PredlogaID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	Jezik char(2) NULL,
	Naziv varchar(32) NULL,
	Slika varchar(64) NULL,
	Datoteka varchar(64) NULL,
	Tip tinyint,
	Opis text NULL,
	ACLID int NULL,
	Enabled boolean NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE Predloge ADD CONSTRAINT PRE_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);
ALTER TABLE Predloge ADD CONSTRAINT PRE_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);

CREATE TABLE NLSText (
	StringID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	Jezik char(2) NULL,
	NLSToken varchar(32) NOT NULL,
	NLSShort varchar(512) NULL,
	NLSLong text NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE NLSText ADD CONSTRAINT NLT_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);
CREATE INDEX NLT_CI ON NLSText (NLSToken, Jezik);

CREATE TABLE Media (
	MediaID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	Izpis boolean NOT NULL DEFAULT 0,
	Datum datetime NULL,
	Naziv varchar(255) NULL,
	Datoteka varchar(128) NULL,
	Velikost int NULL,
	Tip varchar(5) NULL,
	Slika varchar(64) NULL,
	Meta varchar(255) NULL,
	ACLID int NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE Media ADD CONSTRAINT MED_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);
CREATE INDEX MED_I1 ON Media (Tip);

CREATE TABLE MediaOpisi (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	MediaID int NOT NULL,
	Jezik char(2) NULL,
	Naslov varchar(255) NULL,
	Opis text NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE MediaOpisi ADD CONSTRAINT MOP_FK_MED FOREIGN KEY (MediaID) REFERENCES Media (MediaID);
ALTER TABLE MediaOpisi ADD CONSTRAINT MOP_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);

CREATE TABLE Kategorije (
	KategorijaID varchar(10) NOT NULL PRIMARY KEY,
	Izpis boolean NOT NULL DEFAULT 0,
	Iskanje boolean NOT NULL DEFAULT 0,
	Ime varchar(128) NULL,
	Slika varchar(64) NULL,
	ACLID int NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE Kategorije ADD CONSTRAINT KAT_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);

CREATE TABLE KategorijeNazivi (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	KategorijaID varchar(10) NOT NULL,
	Jezik char(2) NULL,
	Naziv varchar(128) NULL,
	Povzetek varchar(255) NULL,
	Opis text NULL,
	Slika varchar(64) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE KategorijeNazivi ADD CONSTRAINT KTN_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID);
ALTER TABLE KategorijeNazivi ADD CONSTRAINT KTN_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);
CREATE INDEX KTN_CI ON KategorijeNazivi (KategorijaID, Jezik);

CREATE TABLE KategorijeVsebina (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	KategorijaID varchar(10) NOT NULL,
	PredlogaID int NOT NULL,
	Polozaj smallint NOT NULL,
	Ekstra tinyint
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE KategorijeVsebina ADD CONSTRAINT KTV_CK_POL CHECK (Polozaj BETWEEN 0 AND 9999);
ALTER TABLE KategorijeVsebina ADD CONSTRAINT KTV_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID);
ALTER TABLE KategorijeVsebina ADD CONSTRAINT KTV_FK_PRE FOREIGN KEY (PredlogaID) REFERENCES Predloge (PredlogaID);
CREATE INDEX KTV_CI ON KategorijeVsebina (KategorijaID, Polozaj);

CREATE TABLE KategorijeMedia (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	KategorijaID varchar(10) NOT NULL ,
	MediaID int NOT NULL ,
	Polozaj smallint NOT NULL 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE KategorijeMedia ADD CONSTRAINT KTM_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID);
ALTER TABLE KategorijeMedia ADD CONSTRAINT KTM_FK_MED FOREIGN KEY (MediaID) REFERENCES Media (MediaID);
ALTER TABLE KategorijeMedia ADD CONSTRAINT KTM_CK_POL CHECK (Polozaj BETWEEN 0 AND 9999);
CREATE INDEX KTM_CI ON KategorijeMedia (KategorijaID, Polozaj);
CREATE INDEX KTM_I2 ON KategorijeMedia (MediaID);

CREATE TABLE Tags (
	TagID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	TagName varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
CREATE UNIQUE INDEX TAG_I1 ON Tags (TagName);

CREATE TABLE Besedila (
	BesediloID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	Izpis boolean NOT NULL DEFAULT 0,
	Datum datetime NULL,
	DatumObjave datetime NULL,
	DatumSpremembe datetime NULL,
	Ime varchar(128) NOT NULL UNIQUE,
	Slika varchar(128) NULL,
	Center boolean NOT NULL DEFAULT 0,
	URL varchar(128) NULL,
	Tip varchar(10) NULL,
	Avtor int NOT NULL,
	ACLID int NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE Besedila ADD CONSTRAINT BES_FK_USR FOREIGN KEY (Avtor) REFERENCES SMUser (UserID);
ALTER TABLE Besedila ADD CONSTRAINT BES_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);
CREATE INDEX BES_CI ON Besedila (Datum);
CREATE INDEX BES_I1 ON Besedila (Tip);
CREATE INDEX BES_I2 ON Besedila (Ime);

CREATE TABLE BesedilaOpisi (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	BesediloID int NOT NULL,
	Jezik char(2) NULL,
	Polozaj smallint NOT NULL,
	Naslov varchar(128) NULL,
	Podnaslov varchar(128) NULL,
	Povzetek varchar(512) NULL,
	Opis text NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE BesedilaOpisi ADD CONSTRAINT BOP_FK_BES FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
ALTER TABLE BesedilaOpisi ADD CONSTRAINT BOP_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);
ALTER TABLE BesedilaOpisi ADD CONSTRAINT BOP_CK_POL CHECK (Polozaj BETWEEN 0 AND 9999);
CREATE INDEX BOP_CI ON BesedilaOpisi (BesediloID, Jezik);

CREATE TABLE BesedilaSlike (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	BesediloID int NOT NULL,
	MediaID int NOT NULL,
	Polozaj smallint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE BesedilaSlike ADD CONSTRAINT BSL_CK_POL CHECK (Polozaj BETWEEN 0 AND 9999);
ALTER TABLE BesedilaSlike ADD CONSTRAINT BSL_FK_BES FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
ALTER TABLE BesedilaSlike ADD CONSTRAINT BSL_FK_MED FOREIGN KEY (MediaID) REFERENCES Media (MediaID);
CREATE INDEX BSL_CI ON BesedilaSlike (BesediloID, Polozaj);

CREATE TABLE BesedilaSkupine (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	BesediloID int NOT NULL,
	DodatniID int NOT NULL,
	Polozaj smallint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE BesedilaSkupine ADD CONSTRAINT BSK_CK_POL CHECK (Polozaj BETWEEN 0 AND 9999);
ALTER TABLE BesedilaSkupine ADD CONSTRAINT BSK_FK_BES1 FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
ALTER TABLE BesedilaSkupine ADD CONSTRAINT BSK_FK_BES2 FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
CREATE INDEX BSK_CI ON BesedilaSkupine (BesediloID, Polozaj);

CREATE TABLE BesedilaTags (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	BesediloID int NOT NULL,
	TagID int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE BesedilaTags ADD CONSTRAINT BST_FK_TAG FOREIGN KEY (TagID) REFERENCES Tags (TagID);
ALTER TABLE BesedilaTags ADD CONSTRAINT BST_FK_BES FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
CREATE UNIQUE INDEX BST_I1 ON BesedilaTags (BesediloID, TagID);
CREATE INDEX BST_I2 ON BesedilaTags (TagID);

CREATE TABLE KategorijeBesedila (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	KategorijaID varchar(10) NOT NULL,
	BesediloID int NOT NULL,
	Polozaj smallint NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE KategorijeBesedila ADD CONSTRAINT KTB_CK_POL CHECK (Polozaj IS NULL OR Polozaj BETWEEN 0 AND 9999);
ALTER TABLE KategorijeBesedila ADD CONSTRAINT KTB_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID);
ALTER TABLE KategorijeBesedila ADD CONSTRAINT KTB_FK_BES FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
CREATE INDEX KTB_CI ON KategorijeBesedila (KategorijaID, Polozaj);

CREATE TABLE BesedilaMedia (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	BesediloID int NOT NULL,
	MediaID int NOT NULL,
	Polozaj smallint NOT NULL 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE BesedilaMedia ADD CONSTRAINT BEM_FK_BES FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
ALTER TABLE BesedilaMedia ADD CONSTRAINT BEM_FK_MED FOREIGN KEY (MediaID) REFERENCES Media (MediaID);
ALTER TABLE BesedilaMedia ADD CONSTRAINT BEM_CK_POL CHECK (Polozaj BETWEEN 0 AND 9999);
CREATE INDEX BSM_I2 ON BesedilaMedia (BesediloID, Polozaj);
CREATE INDEX BSM_I3 ON BesedilaMedia (MediaID);

CREATE TABLE emlGroups (
	emlGroupID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	KtgID varchar (10) NULL,
	Naziv varchar (50) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE emlMembers (
	emlMemberID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	Naziv varchar (50) NULL,
	Podjetje varchar (50) NULL,
	Naslov varchar (50) NULL,
	Posta varchar (50) NULL,
	Telefon varchar (20) NULL,
	Fax varchar (20) NULL,
	GSM varchar (20) NULL,
	Email varchar (128) NULL,
	Jezik char (2) NULL,
	Aktiven boolean NOT NULL DEFAULT 1,
	Datum datetime NULL 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE emlMembers ADD CONSTRAINT EML_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);

CREATE TABLE emlMembersGrp (
	emlMemberGrpID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	emlMemberID int NULL,
	emlGroupID int NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE emlMembersGrp ADD CONSTRAINT EML_FK_MEM FOREIGN KEY (emlMemberID) REFERENCES emlMembers (emlMemberID);
ALTER TABLE emlMembersGrp ADD CONSTRAINT EML_FK_GRP FOREIGN KEY (emlGroupID) REFERENCES emlGroups (emlGroupID);

CREATE TABLE emlMessages (
	emlMessageID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	Naziv varchar (50) NULL,
	Datum datetime NULL,
	ACLID int NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE emlMessages ADD CONSTRAINT EMS_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);

CREATE TABLE emlMessagesDoc (
	emlMessageDocID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	emlMessageID int NULL,
	Naziv varchar (50) NULL,
	Datoteka varchar (128) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE emlMessagesDoc ADD CONSTRAINT EMD_FK_MES FOREIGN KEY (emlMessageID) REFERENCES emlMessages (emlMessageID);

CREATE TABLE emlMessagesGrp (
	emlMessageGrpID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	emlMessageID int NULL,
	emlGroupID int NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE emlMessagesGrp ADD CONSTRAINT EMG_FK_MES FOREIGN KEY (emlMessageID) REFERENCES emlMessages (emlMessageID);
ALTER TABLE emlMessagesGrp ADD CONSTRAINT EMG_FK_MEM FOREIGN KEY (emlGroupID) REFERENCES emlGroups (emlGroupID);

CREATE TABLE emlMessagesTxt (
	emlMessageTxtID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	emlMessageID int NULL,
	Naziv varchar (50) NULL,
	Opis text NULL,
	Jezik char(2) NULL 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE emlMessagesTxt ADD CONSTRAINT EMT_FK_MES FOREIGN KEY (emlMessageID) REFERENCES emlMessages (emlMessageID);
ALTER TABLE emlMessagesTxt ADD CONSTRAINT EMT_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);

CREATE TABLE frmParameters (
	ParamName varchar(16) NOT NULL PRIMARY KEY,
	ParamValue varchar(128) NOT NULL,
	ParamText text NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE frmBanList (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	IP varchar(15) NULL,
	Email varchar(64) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE frmMembers (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	Nickname varchar(32) NOT NULL,
	Password varchar(128) NOT NULL,
	Email varchar(64) NOT NULL,
	ShowEmail boolean NOT NULL DEFAULT 1,
	Name varchar(64) NULL,
	Address varchar(64) NULL,
	Phone varchar(24) NULL,
	Enabled boolean NOT NULL DEFAULT 1,
	AccessLevel tinyint DEFAULT 1,
	MailList boolean NOT NULL DEFAULT 1,
	Posts smallint NULL,
	LastVisit datetime NULL,
	SignIn datetime NULL,
	Signature varchar(255) NULL,
	ICQUIN varchar(11) NULL,
	Settings text NULL,
	DisplayName boolean NOT NULL DEFAULT 0,
	ShowPersonalData boolean NOT NULL DEFAULT 0,
	Sex char(1) NULL,
	WebPage varchar(128) NULL,
	Patron boolean NOT NULL DEFAULT 0,
	LastIPAddress varchar(15) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
CREATE INDEX FRMMEM_CI1 ON frmMembers(LastVisit);
CREATE INDEX FRMMEM_NI1 ON frmMembers(Email);

CREATE TABLE chtRooms (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	Room varchar(32) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE frmCategories (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	CategoryName varchar(64) NULL,
	CategoryOrder smallint NULL,
	Administrator int NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE frmCategories ADD CONSTRAINT FRMCAT_FK_MEM FOREIGN KEY (Administrator) REFERENCES frmMembers (ID);
ALTER TABLE frmCategories ADD CONSTRAINT FRMCAT_CK_POL CHECK (CategoryOrder IS NULL OR CategoryOrder BETWEEN 0 AND 9999);
CREATE INDEX FRMCAT_I1 ON frmCategories (CategoryOrder);

CREATE TABLE frmForums (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	CategoryID int NULL,
	ForumName varchar(64) NULL,
	Description varchar(255) NULL,
	Moderator int NULL,
	NotifyModerator boolean NOT NULL DEFAULT 0,
	Password varchar(16) NULL,
	ApprovalRequired boolean NOT NULL DEFAULT 0,
	AllowFileUploads boolean NOT NULL DEFAULT 0,
	MaxUploadSize smallint NULL,
	UploadType varchar(64) NULL,
	ViewOnly boolean NOT NULL DEFAULT 0,
	Hidden boolean NOT NULL DEFAULT 0,
	PollEnabled boolean NOT NULL DEFAULT 0,
	ForumOrder smallint NULL,
	PurgeDays smallint NULL,
	Private boolean NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE frmForums ADD CONSTRAINT FRMFRM_FK_CAT FOREIGN KEY (CategoryID) REFERENCES frmCategories (ID);
ALTER TABLE frmForums ADD CONSTRAINT FRMFRM_FK_MEM FOREIGN KEY (Moderator) REFERENCES frmMembers (ID);
ALTER TABLE frmForums ADD CONSTRAINT FRMFRM_CK_POL CHECK (ForumOrder IS NULL OR ForumOrder BETWEEN 0 AND 9999);
CREATE INDEX FRMFOR_I1 ON frmForums (CategoryID, ForumOrder);

CREATE TABLE frmModerators (
	ForumID int NOT NULL,
	MemberID int NOT NULL,
	Permissions tinyint DEFAULT 15 -- 1-approve, 2-move, 4-edit, 8-delete, 16-reserved
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE frmModerators ADD CONSTRAINT FRMMOD_PK PRIMARY KEY (ForumID, MemberID);
ALTER TABLE frmModerators ADD CONSTRAINT FRMMOD_FK_FRM FOREIGN KEY (ForumID) REFERENCES frmForums (ID);
ALTER TABLE frmModerators ADD CONSTRAINT FRMMOD_FK_MEM FOREIGN KEY (MemberID) REFERENCES frmMembers (ID);

CREATE TABLE frmTopics (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	ForumID int NOT NULL,
	TopicName varchar(64) NOT NULL,
	MessageCount smallint NULL,
	LastMessageDate datetime NULL,
	NotifyEmail varchar(64) NULL,
	LockedBy int NULL,
	StartedBy int NULL,
	LastPostBy int NULL,
	ReadCount int DEFAULT 0,
	Sticky boolean NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE frmTopics ADD CONSTRAINT FRMTOP_FK_FRM FOREIGN KEY (ForumID) REFERENCES frmForums (ID);
ALTER TABLE frmTopics ADD CONSTRAINT FRMTOP_FK_MEM1 FOREIGN KEY (LockedBy) REFERENCES frmMembers (ID);
ALTER TABLE frmTopics ADD CONSTRAINT FRMTOP_FK_MEM2 FOREIGN KEY (StartedBy) REFERENCES frmMembers (ID);
ALTER TABLE frmTopics ADD CONSTRAINT FRMTOP_FK_MEM3 FOREIGN KEY (LastPostBy) REFERENCES frmMembers (ID);
CREATE INDEX FRMTOP_CI ON frmTopics (ForumID, LastMessageDate);
CREATE INDEX FRMTOP_I1 ON frmTopics (LastMessageDate);

ALTER TABLE Besedila ADD
	ForumTopicID int NULL;
ALTER TABLE Besedila ADD
	CONSTRAINT BES_FK_FRMTOP FOREIGN KEY (ForumTopicID) REFERENCES frmTopics (ID);

CREATE TABLE frmMessages (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	ForumID int NOT NULL,
	TopicID int NOT NULL,
	MemberID int NULL,
	UserName varchar(16) NULL,
	UserEmail varchar(64) NULL,
	MessageDate datetime NOT NULL,
	MessageBody text NOT NULL,
	ChangeMemberID int NULL,
	ChangeDate datetime NULL,
	AttachedFile varchar(128) NULL,
	Icon varchar(32) NULL,
	IsApproved boolean NOT NULL DEFAULT 0,
	ApprovedBy int NULL,
	Locked boolean NOT NULL DEFAULT 0,
	IPaddr varchar (15) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE frmMessages ADD CONSTRAINT FRMMSG_FK_FRM FOREIGN KEY (ForumID) REFERENCES frmForums (ID);
ALTER TABLE frmMessages ADD CONSTRAINT FRMMSG_FK_TOP FOREIGN KEY (TopicID) REFERENCES frmTopics (ID);
ALTER TABLE frmMessages ADD CONSTRAINT FRMMSG_FK_MEM1 FOREIGN KEY (MemberID) REFERENCES frmMembers (ID);
ALTER TABLE frmMessages ADD CONSTRAINT FRMMSG_FK_MEM2 FOREIGN KEY (ChangeMemberID) REFERENCES frmMembers (ID);
ALTER TABLE frmMessages ADD CONSTRAINT FRMMSG_FK_MEM3 FOREIGN KEY (ApprovedBy) REFERENCES frmMembers (ID);
CREATE INDEX FRMMSG_CI ON frmMessages (ForumID, TopicID);
CREATE INDEX FRMMSG_I1 ON frmMessages (TopicID, MessageDate);
CREATE INDEX FRMMSG_I2 ON frmMessages (MemberID, MessageDate);

CREATE TABLE frmNotify (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	MemberID int NOT NULL,
	TopicID int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE frmNotify ADD CONSTRAINT FRMNTF_FK_TOP FOREIGN KEY (TopicID) REFERENCES frmTopics (ID);
ALTER TABLE frmNotify ADD CONSTRAINT FRMNTF_FK_MEM FOREIGN KEY (MemberID) REFERENCES frmMembers (ID);
CREATE INDEX FRMNOT_I1 ON frmNotify (MemberID);
CREATE INDEX FRMNOT_I2 ON frmNotify (TopicID);

CREATE TABLE frmPvtMessages (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	FromID int NULL,
	ToID int NOT NULL,
	TopicID int NULL,
	IsRead boolean NOT NULL DEFAULT 0,
	IsReply boolean NOT NULL DEFAULT 0,
	IsDeleted boolean NOT NULL DEFAULT 0,
	MessageDate datetime NULL,
	MessageSubject varchar(64) NULL,
	MessageBody text NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE frmPvtMessages ADD CONSTRAINT FRMPMS_FK_TOP FOREIGN KEY (TopicID) REFERENCES frmTopics (ID);
ALTER TABLE frmPvtMessages ADD CONSTRAINT FRMPMS_FK_MEM1 FOREIGN KEY (FromID) REFERENCES frmMembers (ID);
ALTER TABLE frmPvtMessages ADD CONSTRAINT FRMPMS_FK_MEM2 FOREIGN KEY (ToID) REFERENCES frmMembers (ID);
CREATE INDEX FRMPVT_I1 ON frmPvtMessages (ToID);
CREATE INDEX FRMPVT_I2 ON frmPvtMessages (FromID);

CREATE TABLE frmPoll (
	TopicID int NOT NULL PRIMARY KEY,
	Question varchar(512) NOT NULL,
	Locked boolean NOT NULL DEFAULT 0,
	Votes int DEFAULT 0,
	Answers tinyint NOT NULL,
	A1 varchar(128) NOT NULL,
	A2 varchar(128) NOT NULL,
	A3 varchar(128) NULL,
	A4 varchar(128) NULL,
	A5 varchar(128) NULL,
	A6 varchar(128) NULL,
	A7 varchar(128) NULL,
	A8 varchar(128) NULL,
	A9 varchar(128) NULL,
	A10 varchar(128) NULL,
	R1 int DEFAULT 0,
	R2 int DEFAULT 0,
	R3 int DEFAULT 0,
	R4 int DEFAULT 0,
	R5 int DEFAULT 0,
	R6 int DEFAULT 0,
	R7 int DEFAULT 0,
	R8 int DEFAULT 0,
	R9 int DEFAULT 0,
	R10 int DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE frmPoll ADD CONSTRAINT FRMPOL_FK_TOP FOREIGN KEY (TopicID) REFERENCES frmTopics (ID);
ALTER TABLE frmPoll ADD CONSTRAINT FRMPOL_CK_ANS CHECK (Answers BETWEEN 2 AND 10);

CREATE TABLE frmPollVotes (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	TopicID int NOT NULL,
	MemberID int NOT NULL,
	Answer tinyint NULL,
	VoteDate timestamp DEFAULT current_timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE frmPollVotes ADD CONSTRAINT FRMPLV_FK_TOP FOREIGN KEY (TopicID) REFERENCES frmTopics (ID);
ALTER TABLE frmPollVotes ADD CONSTRAINT FRMPLV_FK_MEM FOREIGN KEY (MemberID) REFERENCES frmMembers (ID);
ALTER TABLE frmPollVotes ADD CONSTRAINT FRMPLV_CK_ANS CHECK (Answer IS NULL OR Answer BETWEEN 1 AND 10);
CREATE INDEX FRMPLV_I1 ON frmPollVotes (TopicID, VoteDate);

CREATE TABLE frmVisitors (
	SessionID varchar(32) NOT NULL PRIMARY KEY,
	MemberID int NULL UNIQUE,
	LastVisit timestamp NOT NULL DEFAULT now(),
	InChat tinyint NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE frmVisitors ADD CONSTRAINT FRMVIS_FK_MEM FOREIGN KEY (MemberID) REFERENCES frmMembers (ID);

CREATE TABLE Ankete (
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	Jezik char(2) NULL,
	Datum datetime NULL,
	Vprasanje varchar(255) NULL,
	Komentar varchar(255) NULL,
	Multiple boolean NOT NULL DEFAULT 0,
	StGlasov int NULL,
	StOdg tinyint,
	Odg1 varchar(128) NULL,
	Odg2 varchar(128) NULL,
	Odg3 varchar(128) NULL,
	Odg4 varchar(128) NULL,
	Odg5 varchar(128) NULL,
	Odg6 varchar(128) NULL,
	Odg7 varchar(128) NULL,
	Odg8 varchar(128) NULL,
	Odg9 varchar(128) NULL,
	Odg10 varchar(128) NULL,
	Rez1 int DEFAULT 0,
	Rez2 int DEFAULT 0,
	Rez3 int DEFAULT 0,
	Rez4 int DEFAULT 0,
	Rez5 int DEFAULT 0,
	Rez6 int DEFAULT 0,
	Rez7 int DEFAULT 0,
	Rez8 int DEFAULT 0,
	Rez9 int DEFAULT 0,
	Rez10 int DEFAULT 0,
	ACLID int NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
ALTER TABLE Ankete ADD CONSTRAINT ANK_CK_STO CHECK (StOdg BETWEEN 2 AND 10);
ALTER TABLE Ankete ADD CONSTRAINT ANK_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki(Jezik);
ALTER TABLE Ankete ADD CONSTRAINT ANK_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);
CREATE INDEX ANK_CI ON Ankete (Datum);
