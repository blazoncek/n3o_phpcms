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
	ParamValue nvarchar(128) NOT NULL
);

CREATE TABLE SMGroup (
	GroupID int IDENTITY NOT NULL PRIMARY KEY,
	Name nvarchar(64) NULL
);

CREATE TABLE SMUser (
	UserID int IDENTITY NOT NULL PRIMARY KEY,
	Username varchar(16) NOT NULL UNIQUE,
	Password varchar(128) NOT NULL,
	Name nvarchar(64) NULL,
	Email varchar(64) NULL,
	Phone varchar(32) NULL,
	DefGrp int NULL,
	Active bit NOT NULL DEFAULT 0,
	LastLogon datetime NULL,
	TwitterName varchar(32) NULL
);
ALTER TABLE SMUser ADD CONSTRAINT USR_FK_GRP FOREIGN KEY (DefGrp) REFERENCES SMGroup (GroupID);
CREATE INDEX USR_NI1 ON SMUser (Username);
CREATE INDEX USR_NI2 ON SMUser (Email);

CREATE TABLE SMUserGroups (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	GroupID int NOT NULL,
	UserID int NOT NULL
);
ALTER TABLE SMUserGroups ADD CONSTRAINT USG_FK_GRP FOREIGN KEY (GroupID) REFERENCES SMGroup (GroupID);
ALTER TABLE SMUserGroups ADD CONSTRAINT USG_FK_USR FOREIGN KEY (UserID) REFERENCES SMUser(UserID);
CREATE UNIQUE INDEX USG_I1 ON SMUserGroups (GroupID,UserID);

CREATE TABLE SMACL (
	ACLID int IDENTITY NOT NULL PRIMARY KEY,
	Name varchar(64) NULL UNIQUE
);

CREATE TABLE SMACLr (
	ACLID int NOT NULL,
	UserID int NULL,
	GroupID int NULL,
	MemberACL char(5) NULL
);
ALTER TABLE SMACLr ADD CONSTRAINT ACR_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);
ALTER TABLE SMACLr ADD CONSTRAINT ACR_FK_USR FOREIGN KEY (UserID) REFERENCES SMUser (UserID);
ALTER TABLE SMACLr ADD CONSTRAINT ACR_FK_GRP FOREIGN KEY (GroupID) REFERENCES SMGroup (GroupID);
CREATE UNIQUE INDEX ACR_I1 ON SMACLr (ACLID,GroupID);
CREATE UNIQUE INDEX ACR_I2 ON SMACLr (ACLID,UserID);

CREATE TABLE SMActions (
	ActionID varchar(10) NOT NULL PRIMARY KEY,
	Name nvarchar(64) NULL,
	Enabled bit NOT NULL DEFAULT 0,
	Action nvarchar(128) NULL,
	Icon nvarchar(128) NULL,
	MobileCapable bit NULL DEFAULT 0,
	ACLID int NULL
);
ALTER TABLE SMActions ADD CONSTRAINT ACT_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);

CREATE TABLE SMAudit (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	DateOfEntry smalldatetime NOT NULL DEFAULT getdate(),
	UserID int NOT NULL,
	ObjectID int NULL,
	ObjectType varchar(16) NULL,
	Action nvarchar(64) NULL,
	Description nvarchar(512) NULL
);
ALTER TABLE SMAudit ADD CONSTRAINT AUD_FK_USR FOREIGN KEY (UserID) REFERENCES SMUser(UserID);

CREATE TABLE Jeziki (
	Jezik char(2) NOT NULL PRIMARY KEY,
	Opis nvarchar(64) NULL,
	Ikona nvarchar(64) NULL,
	Enabled bit NULL DEFAULT 0,
	CharSet varchar(64) NULL,
	DefLang bit NULL DEFAULT 0,
	LangCode varchar(6) NULL
);

CREATE TABLE Sifranti (
	SifrantID int IDENTITY NOT NULL PRIMARY KEY,
	SifrCtrl char(4) NOT NULL,
	SifrZapo smallint NOT NULL,
	SifrText varchar(10) NULL,
	SifNVal1 decimal(15,4) NULL,
	SifNVal2 decimal(15,4) NULL,
	SifNVal3 decimal(15,4) NULL,
	SifDVal1 datetime NULL,
	SifDVal2 datetime NULL,
	SifDVal3 datetime NULL,
	SifLVal1 bit NOT NULL DEFAULT 0,
	SifLVal2 bit NOT NULL DEFAULT 0,
	ACLID int NULL,
	SifNVal1Desc nvarchar(128) NULL,
	SifNVal2Desc nvarchar(128) NULL,
	SifNVal3Desc nvarchar(128) NULL,
	SifDVal1Desc nvarchar(128) NULL,
	SifDVal2Desc nvarchar(128) NULL,
	SifDVal3Desc nvarchar(128) NULL,
	SifLVal1Desc nvarchar(128) NULL,
	SifLVal2Desc nvarchar(128) NULL
);
ALTER TABLE Sifranti ADD CONSTRAINT SIF_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);
CREATE INDEX SIF_CI ON Sifranti (SifrCtrl, SifrZapo);

CREATE TABLE SifrantiTxt (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	SifrantID int NOT NULL,
	Jezik char(2) NULL,
	SifNaziv nvarchar(64) NULL,
	SifCVal1 nvarchar(128) NULL,
	SifCVal2 nvarchar(128) NULL,
	SifCVal3 nvarchar(128) NULL,
	SifNazivDesc nvarchar(128) NULL,
	SifCVal1Desc nvarchar(128) NULL,
	SifCVal2Desc nvarchar(128) NULL,
	SifCVal3Desc nvarchar(128) NULL
);
ALTER TABLE SifrantiTxt ADD CONSTRAINT SIT_FK_SIF FOREIGN KEY (SifrantID) REFERENCES Sifranti (SifrantID);
ALTER TABLE SifrantiTxt ADD CONSTRAINT SIT_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);

CREATE TABLE Predloge (
	PredlogaID int IDENTITY NOT NULL PRIMARY KEY,
	Jezik char(2) NULL,
	Naziv nvarchar(32) NULL,
	Slika nvarchar(64) NULL,
	Datoteka nvarchar(64) NULL,
	Tip tinyint,
	Opis text NULL,
	ACLID int NULL,
	Enabled bit NOT NULL DEFAULT 0
);
ALTER TABLE Predloge ADD CONSTRAINT PRE_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);
ALTER TABLE Predloge ADD CONSTRAINT PRE_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);

CREATE TABLE NLSText (
	StringID int IDENTITY NOT NULL PRIMARY KEY,
	Jezik char(2) NULL,
	NLSToken varchar(32) NOT NULL,
	NLSShort nvarchar(512) NULL,
	NLSLong ntext NULL
);
ALTER TABLE NLSText ADD CONSTRAINT NLT_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);
CREATE INDEX NLT_CI ON NLSText (NLSToken, Jezik);

CREATE TABLE Media (
	MediaID int IDENTITY NOT NULL PRIMARY KEY,
	Izpis bit NOT NULL DEFAULT 0,
	Datum datetime NULL,
	Naziv nvarchar(255) NULL,
	Datoteka nvarchar(128) NULL,
	Velikost int NULL,
	Tip varchar(5) NULL,
	Slika nvarchar(64) NULL,
	Meta nvarchar(255) NULL,
	ACLID int NULL
);
ALTER TABLE Media ADD CONSTRAINT MED_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);
CREATE INDEX MED_I1 ON Media (Tip);

CREATE TABLE MediaOpisi (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	MediaID int NOT NULL,
	Jezik char(2) NULL,
	Naslov nvarchar(255) NULL,
	Opis ntext NULL
);
ALTER TABLE MediaOpisi ADD CONSTRAINT MOP_FK_MED FOREIGN KEY (MediaID) REFERENCES Media (MediaID);
ALTER TABLE MediaOpisi ADD CONSTRAINT MOP_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);

CREATE TABLE Kategorije (
	KategorijaID varchar(10) NOT NULL PRIMARY KEY,
	Izpis bit NOT NULL DEFAULT 0,
	Iskanje bit NOT NULL DEFAULT 0,
	Ime varchar(32) NULL,
	Slika nvarchar(64) NULL,
	ACLID int NULL
);
ALTER TABLE Kategorije ADD CONSTRAINT KAT_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);

CREATE TABLE KategorijeNazivi (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	KategorijaID varchar(10) NOT NULL,
	Jezik char(2) NULL,
	Naziv nvarchar(128) NULL,
	Povzetek nvarchar(255) NULL,
	Opis ntext NULL,
	Slika nvarchar(64) NULL
);
ALTER TABLE KategorijeNazivi ADD CONSTRAINT KTN_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID);
ALTER TABLE KategorijeNazivi ADD CONSTRAINT KTN_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);
CREATE INDEX KTN_CI ON KategorijeNazivi (KategorijaID, Jezik);

CREATE TABLE KategorijeVsebina (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	KategorijaID varchar(10) NOT NULL,
	PredlogaID int NOT NULL,
	Polozaj smallint NOT NULL,
	Ekstra tinyint
);
ALTER TABLE KategorijeVsebina ADD CONSTRAINT KTV_CK_POL CHECK (Polozaj BETWEEN 0 AND 9999);
ALTER TABLE KategorijeVsebina ADD CONSTRAINT KTV_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID);
ALTER TABLE KategorijeVsebina ADD CONSTRAINT KTV_FK_PRE FOREIGN KEY (PredlogaID) REFERENCES Predloge (PredlogaID);
CREATE INDEX KTV_CI ON KategorijeVsebina (KategorijaID, Polozaj);

CREATE TABLE KategorijeMedia (
	ID int IDENTITY (1,1) NOT NULL PRIMARY KEY,
	KategorijaID varchar(10) NOT NULL ,
	MediaID int NOT NULL ,
	Polozaj smallint NOT NULL 
);
ALTER TABLE KategorijeMedia ADD CONSTRAINT KTM_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID);
ALTER TABLE KategorijeMedia ADD CONSTRAINT KTM_FK_MED FOREIGN KEY (MediaID) REFERENCES Media (MediaID);
ALTER TABLE KategorijeMedia ADD CONSTRAINT KTM_CK_POL CHECK (Polozaj BETWEEN 0 AND 9999);
CREATE INDEX KTM_CI ON KategorijeMedia (KategorijaID, Polozaj);
CREATE INDEX KTM_I2 ON KategorijeMedia (MediaID);

CREATE TABLE Tags (
	TagID int IDENTITY NOT NULL PRIMARY KEY,
	TagName varchar(64) NOT NULL
);
CREATE UNIQUE INDEX TAG_I1 ON Tags (TagName);

CREATE TABLE Besedila (
	BesediloID int IDENTITY NOT NULL PRIMARY KEY,
	Izpis bit NOT NULL DEFAULT 0,
	Datum datetime NULL,
	DatumObjave datetime NULL,
	DatumSpremembe datetime NULL,
	Ime varchar(128) NOT NULL UNIQUE,
	Slika nvarchar(128) NULL,
	Center bit NOT NULL DEFAULT 0,
	URL varchar(128) NULL,
	Tip nvarchar(10) NULL,
	Avtor int NOT NULL,
	ACLID int NULL
);
ALTER TABLE Besedila ADD CONSTRAINT BES_FK_USR FOREIGN KEY (Avtor) REFERENCES SMUser (UserID);
ALTER TABLE Besedila ADD CONSTRAINT BES_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);
CREATE INDEX BES_CI ON Besedila (Datum);
CREATE INDEX BES_I1 ON Besedila (Tip);
CREATE INDEX BES_I2 ON Besedila (Ime);

CREATE TABLE BesedilaOpisi (
	ID int IDENTITY NOT NULL CONSTRAINT PK_BesedilaOpisi PRIMARY KEY, -- used for full-text index
	BesediloID int NOT NULL,
	Jezik char(2) NULL,
	Polozaj smallint NOT NULL,
	Naslov nvarchar(128) NULL,
	Podnaslov nvarchar(128) NULL,
	Povzetek nvarchar(512) NULL,
	Opis ntext NULL
);
ALTER TABLE BesedilaOpisi ADD CONSTRAINT BOP_FK_BES FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
ALTER TABLE BesedilaOpisi ADD CONSTRAINT BOP_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);
ALTER TABLE BesedilaOpisi ADD CONSTRAINT BOP_CK_POL CHECK (Polozaj BETWEEN 0 AND 9999);
CREATE INDEX BOP_CI ON BesedilaOpisi (BesediloID, Jezik);

CREATE TABLE BesedilaSlike (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	BesediloID int NOT NULL,
	MediaID int NOT NULL,
	Polozaj smallint NOT NULL
);
ALTER TABLE BesedilaSlike ADD CONSTRAINT BSL_CK_POL CHECK (Polozaj BETWEEN 0 AND 9999);
ALTER TABLE BesedilaSlike ADD CONSTRAINT BSL_FK_BES FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
ALTER TABLE BesedilaSlike ADD CONSTRAINT BSL_FK_MED FOREIGN KEY (MediaID) REFERENCES Media (MediaID);
CREATE INDEX BSL_CI ON BesedilaSlike (BesediloID, Polozaj);

CREATE TABLE BesedilaSkupine (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	BesediloID int NOT NULL,
	DodatniID int NOT NULL,
	Polozaj smallint NOT NULL
);
ALTER TABLE BesedilaSkupine ADD CONSTRAINT BSK_CK_POL CHECK (Polozaj BETWEEN 0 AND 9999);
ALTER TABLE BesedilaSkupine ADD CONSTRAINT BSK_FK_BES1 FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
ALTER TABLE BesedilaSkupine ADD CONSTRAINT BSK_FK_BES2 FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
CREATE INDEX BSK_CI ON BesedilaSkupine (BesediloID, Polozaj);

CREATE TABLE BesedilaTags (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	BesediloID int NOT NULL,
	TagID int NOT NULL
);
ALTER TABLE BesedilaTags ADD CONSTRAINT BST_FK_TAG FOREIGN KEY (TagID) REFERENCES Tags (TagID);
ALTER TABLE BesedilaTags ADD CONSTRAINT BST_FK_BES FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
CREATE UNIQUE INDEX BST_I1 ON BesedilaTags (BesediloID, TagID);
CREATE INDEX BST_I2 ON BesedilaTags (TagID);

CREATE TABLE KategorijeBesedila (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	KategorijaID varchar(10) NOT NULL,
	BesediloID int NOT NULL,
	Polozaj smallint NOT NULL
);
ALTER TABLE KategorijeBesedila ADD CONSTRAINT KTB_CK_POL CHECK (Polozaj BETWEEN 0 AND 9999);
ALTER TABLE KategorijeBesedila ADD CONSTRAINT KTB_FK_KAT FOREIGN KEY (KategorijaID) REFERENCES Kategorije (KategorijaID);
ALTER TABLE KategorijeBesedila ADD CONSTRAINT KTB_FK_BES FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
CREATE INDEX KTB_CI ON KategorijeBesedila (KategorijaID, Polozaj);

CREATE TABLE BesedilaMedia (
	ID int IDENTITY (1,1) NOT NULL PRIMARY KEY,
	BesediloID int NOT NULL ,
	MediaID int NOT NULL ,
	Polozaj smallint NOT NULL 
);
ALTER TABLE BesedilaMedia ADD CONSTRAINT BEM_FK_BES FOREIGN KEY (BesediloID) REFERENCES Besedila (BesediloID);
ALTER TABLE BesedilaMedia ADD CONSTRAINT BEM_FK_MED FOREIGN KEY (MediaID) REFERENCES Media (MediaID);
ALTER TABLE BesedilaMedia ADD CONSTRAINT BEM_CK_POL CHECK (Polozaj BETWEEN 0 AND 9999);
CREATE INDEX BSM_I2 ON BesedilaMedia (BesediloID, Polozaj);
CREATE INDEX BSM_I3 ON BesedilaMedia (MediaID);

CREATE TABLE emlGroups (
	emlGroupID int IDENTITY NOT NULL PRIMARY KEY,
	KtgID varchar (10) NULL,
	Naziv nvarchar (50) NULL
);

CREATE TABLE emlMembers (
	emlMemberID int IDENTITY NOT NULL PRIMARY KEY,
	Naziv nvarchar (50) NULL,
	Podjetje nvarchar (50) NULL,
	Naslov nvarchar (50) NULL,
	Posta nvarchar (50) NULL,
	Telefon varchar (20) NULL,
	Fax varchar (20) NULL,
	GSM varchar (20) NULL,
	Email varchar (128) NULL,
	Jezik char (2) NULL,
	Aktiven bit NOT NULL DEFAULT 1,
	Datum datetime NULL 
);
ALTER TABLE emlMembers ADD CONSTRAINT EML_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);

CREATE TABLE emlMembersGrp (
	emlMemberGrpID int IDENTITY NOT NULL PRIMARY KEY,
	emlMemberID int NULL,
	emlGroupID int NULL
);
ALTER TABLE emlMembersGrp ADD CONSTRAINT EML_FK_MEM FOREIGN KEY (emlMemberID) REFERENCES emlMembers (emlMemberID);
ALTER TABLE emlMembersGrp ADD CONSTRAINT EML_FK_GRP FOREIGN KEY (emlGroupID) REFERENCES emlGroups (emlGroupID);

CREATE TABLE emlMessages (
	emlMessageID int IDENTITY NOT NULL PRIMARY KEY,
	Naziv nvarchar (50) NULL,
	Datum datetime NULL,
	ACLID int NULL
);
ALTER TABLE emlMessages ADD CONSTRAINT EMS_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);

CREATE TABLE emlMessagesDoc (
	emlMessageDocID int IDENTITY NOT NULL PRIMARY KEY,
	emlMessageID int NULL,
	Naziv nvarchar (50) NULL,
	Datoteka nvarchar (128) NULL 
);
ALTER TABLE emlMessagesDoc ADD CONSTRAINT EMD_FK_MES FOREIGN KEY (emlMessageID) REFERENCES emlMessages (emlMessageID);

CREATE TABLE emlMessagesGrp (
	emlMessageGrpID int IDENTITY NOT NULL PRIMARY KEY,
	emlMessageID int NULL,
	emlGroupID int NULL
);
ALTER TABLE emlMessagesGrp ADD CONSTRAINT EMG_FK_MES FOREIGN KEY (emlMessageID) REFERENCES emlMessages (emlMessageID);
ALTER TABLE emlMessagesGrp ADD CONSTRAINT EMG_FK_MEM FOREIGN KEY (emlGroupID) REFERENCES emlGroups (emlGroupID);

CREATE TABLE emlMessagesTxt (
	emlMessageTxtID int IDENTITY NOT NULL PRIMARY KEY,
	emlMessageID int NULL,
	Naziv nvarchar (50) NULL,
	Opis ntext NULL,
	Jezik char(2) NULL 
);
ALTER TABLE emlMessagesTxt ADD CONSTRAINT EMT_FK_MES FOREIGN KEY (emlMessageID) REFERENCES emlMessages (emlMessageID);
ALTER TABLE emlMessagesTxt ADD CONSTRAINT EMT_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki (Jezik);

CREATE TABLE frmParameters (
	ParamName varchar(16) NOT NULL PRIMARY KEY,
	ParamValue varchar(128) NOT NULL,
	ParamText text NULL
);

CREATE TABLE frmBanList (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	IP varchar(15) NULL,
	Email varchar(64) NULL
);

CREATE TABLE frmMembers (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	Nickname nvarchar(32) NOT NULL,
	Password varchar(32) NOT NULL,
	Email varchar(64) NOT NULL,
	ShowEmail bit NOT NULL DEFAULT 1,
	Name nvarchar(64) NULL,
	Address nvarchar(64) NULL,
	Phone varchar(24) NULL,
	Enabled bit NOT NULL DEFAULT 1,
	AccessLevel tinyint DEFAULT 1,
	MailList bit NOT NULL DEFAULT 1,
	Posts smallint NULL,
	LastVisit datetime NULL,
	SignIn datetime NULL,
	Signature nvarchar(255) NULL,
	ICQUIN varchar(11) NULL,
	Settings text NULL,
	DisplayName bit NOT NULL DEFAULT 0,
	ShowPersonalData bit NOT NULL DEFAULT 0,
	Sex char(1) NULL,
	WebPage varchar(128) NULL,
	Patron bit NOT NULL DEFAULT 0,
	LastIPAddress varchar(15) NULL
);
CREATE INDEX FRMMEM_CI1 ON frmMembers(LastVisit);
CREATE INDEX FRMMEM_NI1 ON frmMembers(Email);

CREATE TABLE chtRooms (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	Room nvarchar(32) NULL
);

CREATE TABLE frmCategories (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	CategoryName nvarchar(64) NULL,
	CategoryOrder smallint NULL,
	Administrator int NULL;
);
ALTER TABLE frmCategories ADD CONSTRAINT FRMCAT_FK_MEM FOREIGN KEY (Administrator) REFERENCES frmMembers (ID);
ALTER TABLE frmCategories ADD CONSTRAINT FRMCAT_CK_POL CHECK (CategoryOrder IS NULL OR CategoryOrder BETWEEN 0 AND 9999);
CREATE INDEX FRMCAT_I1 ON frmCategories (CategoryOrder);

CREATE TABLE frmForums (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	CategoryID int NULL,
	ForumName nvarchar(64) NULL,
	Description nvarchar(255) NULL,
	Moderator int NULL,
	NotifyModerator bit NOT NULL DEFAULT 0,
	Password varchar(16) NULL,
	ApprovalRequired bit NOT NULL DEFAULT 0,
	AllowFileUploads bit NOT NULL DEFAULT 0,
	MaxUploadSize smallint NULL,
	UploadType varchar(64) NULL,
	ViewOnly bit NOT NULL DEFAULT 0,
	Hidden bit NOT NULL DEFAULT 0,
	PollEnabled bit NOT NULL DEFAULT 0,
	ForumOrder smallint NULL,
	PurgeDays smallint NULL,
	Private bit NOT NULL DEFAULT 0
);
ALTER TABLE frmForums ADD CONSTRAINT FRMFRM_FK_CAT FOREIGN KEY (CategoryID) REFERENCES frmCategories (ID);
ALTER TABLE frmForums ADD CONSTRAINT FRMFRM_FK_MEM FOREIGN KEY (Moderator) REFERENCES frmMembers (ID);
ALTER TABLE frmForums ADD CONSTRAINT FRMFRM_CK_POL CHECK (ForumOrder IS NULL OR ForumOrder BETWEEN 0 AND 9999);
CREATE INDEX FRMFOR_I1 ON frmForums (CategoryID, ForumOrder);

CREATE TABLE frmModerators (
	ForumID int NOT NULL,
	MemberID int NOT NULL,
	Permissions tinyint DEFAULT 15 -- 1-approve, 2-move, 4-edit, 8-delete, 16-reserved
);
ALTER TABLE frmModerators ADD CONSTRAINT FRMMOD_PK PRIMARY KEY (ForumID, MemberID);
ALTER TABLE frmModerators ADD CONSTRAINT FRMMOD_FK_FRM FOREIGN KEY (ForumID) REFERENCES frmForums (ID);
ALTER TABLE frmModerators ADD CONSTRAINT FRMMOD_FK_MEM FOREIGN KEY (MemberID) REFERENCES frmMembers (ID);

CREATE TABLE frmTopics (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	ForumID int NOT NULL,
	TopicName nvarchar(64) NOT NULL,
	MessageCount smallint NULL,
	LastMessageDate datetime NULL,
	NotifyEmail varchar(64) NULL,
	LockedBy int NULL,
	StartedBy int NULL,
	LastPostBy int NULL,
	ReadCount int DEFAULT 0,
	Sticky bit NOT NULL DEFAULT 0
);
ALTER TABLE frmTopics ADD CONSTRAINT FRMTOP_FK_FRM FOREIGN KEY (ForumID) REFERENCES frmForums (ID);
ALTER TABLE frmTopics ADD CONSTRAINT FRMTOP_FK_MEM1 FOREIGN KEY (LockedBy) REFERENCES frmMembers (ID);
ALTER TABLE frmTopics ADD CONSTRAINT FRMTOP_FK_MEM2 FOREIGN KEY (StartedBy) REFERENCES frmMembers (ID);
ALTER TABLE frmTopics ADD CONSTRAINT FRMTOP_FK_MEM3 FOREIGN KEY (LastPostBy) REFERENCES frmMembers (ID);
CREATE INDEX FRMTOP_CI ON frmTopics (ForumID, LastMessageDate);
CREATE INDEX FRMTOP_I1 ON frmTopics (LastMessageDate);

ALTER TABLE Besedila ADD
	ForumTopicID int NULL,
	CONSTRAINT BES_FK_FRMTOP FOREIGN KEY (ForumTopicID) REFERENCES frmTopics (ID);

CREATE TABLE frmMessages (
	ID int IDENTITY NOT NULL CONSTRAINT PK_frmMessages PRIMARY KEY, -- used for full-text index
	ForumID int NOT NULL,
	TopicID int NOT NULL,
	MemberID int NULL,
	UserName nvarchar(16) NULL,
	UserEmail varchar(64) NULL,
	MessageDate datetime NOT NULL,
	MessageBody ntext NOT NULL,
	ChangeMemberID int NULL,
	ChangeDate datetime NULL,
	AttachedFile nvarchar(128) NULL,
	Icon nvarchar(32) NULL,
	IsApproved bit NOT NULL DEFAULT 0,
	ApprovedBy int NULL,
	Locked bit NOT NULL DEFAULT 0,
	IPaddr varchar (15) NULL
);
ALTER TABLE frmMessages ADD CONSTRAINT FRMMSG_FK_FRM FOREIGN KEY (ForumID) REFERENCES frmForums (ID);
ALTER TABLE frmMessages ADD CONSTRAINT FRMMSG_FK_TOP FOREIGN KEY (TopicID) REFERENCES frmTopics (ID);
ALTER TABLE frmMessages ADD CONSTRAINT FRMMSG_FK_MEM1 FOREIGN KEY (MemberID) REFERENCES frmMembers (ID);
ALTER TABLE frmMessages ADD CONSTRAINT FRMMSG_FK_MEM2 FOREIGN KEY (ChangeMemberID) REFERENCES frmMembers (ID);
ALTER TABLE frmMessages ADD CONSTRAINT FRMMSG_FK_MEM3 FOREIGN KEY (ApprovedBy) REFERENCES frmMembers (ID);
CREATE INDEX FRMMSG_CI ON frmMessages (ForumID, TopicID);
CREATE INDEX FRMMSG_I1 ON frmMessages (TopicID, MessageDate);
CREATE INDEX FRMMSG_I2 ON frmMessages (MemberID, MessageDate);

CREATE TABLE frmNotify (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	MemberID int NOT NULL,
	TopicID int NOT NULL
);
ALTER TABLE frmNotify ADD CONSTRAINT FRMNTF_FK_TOP FOREIGN KEY (TopicID) REFERENCES frmTopics (ID);
ALTER TABLE frmNotify ADD CONSTRAINT FRMNTF_FK_MEM FOREIGN KEY (MemberID) REFERENCES frmMembers (ID);
CREATE INDEX FRMNOT_I1 ON frmNotify (MemberID);
CREATE INDEX FRMNOT_I2 ON frmNotify (TopicID);

CREATE TABLE frmPvtMessages (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	FromID int NULL,
	ToID int NOT NULL,
	TopicID int NULL,
	IsRead bit NOT NULL DEFAULT 0,
	IsReply bit NOT NULL DEFAULT 0,
	IsDeleted bit NOT NULL DEFAULT 0,
	MessageDate datetime NULL,
	MessageSubject nvarchar(64) NULL,
	MessageBody ntext NULL
);
ALTER TABLE frmPvtMessages ADD CONSTRAINT FRMPMS_FK_TOP FOREIGN KEY (TopicID) REFERENCES frmTopics (ID);
ALTER TABLE frmPvtMessages ADD CONSTRAINT FRMPMS_FK_MEM1 FOREIGN KEY (FromID) REFERENCES frmMembers (ID);
ALTER TABLE frmPvtMessages ADD CONSTRAINT FRMPMS_FK_MEM2 FOREIGN KEY (ToID) REFERENCES frmMembers (ID);
CREATE INDEX FRMPVT_I1 ON frmPvtMessages (ToID);
CREATE INDEX FRMPVT_I2 ON frmPvtMessages (FromID);

CREATE TABLE frmPoll (
	TopicID int NOT NULL PRIMARY KEY,
	Question nvarchar(512) NOT NULL,
	Locked bit NOT NULL DEFAULT 0,
	Votes int DEFAULT 0,
	Answers tinyint NOT NULL,
	A1 nvarchar(128) NOT NULL,
	A2 nvarchar(128) NOT NULL,
	A3 nvarchar(128) NULL,
	A4 nvarchar(128) NULL,
	A5 nvarchar(128) NULL,
	A6 nvarchar(128) NULL,
	A7 nvarchar(128) NULL,
	A8 nvarchar(128) NULL,
	A9 nvarchar(128) NULL,
	A10 nvarchar(128) NULL,
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
);
ALTER TABLE frmPoll ADD CONSTRAINT FRMPOL_FK_TOP FOREIGN KEY (TopicID) REFERENCES frmTopics (ID);
ALTER TABLE frmPoll ADD CONSTRAINT FRMPOL_CK_ANS CHECK (Answers BETWEEN 2 AND 10);

CREATE TABLE frmPollVotes (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	TopicID int NOT NULL,
	MemberID int NOT NULL,
	Answer tinyint NULL,
	VoteDate datetime NULL DEFAULT getdate()
);
ALTER TABLE frmPollVotes ADD CONSTRAINT FRMPLV_FK_TOP FOREIGN KEY (TopicID) REFERENCES frmTopics (ID);
ALTER TABLE frmPollVotes ADD CONSTRAINT FRMPLV_FK_MEM FOREIGN KEY (MemberID) REFERENCES frmMembers (ID);
ALTER TABLE frmPollVotes ADD CONSTRAINT FRMPLV_CK_ANS CHECK (Answer IS NULL OR Answer BETWEEN 1 AND 10);
CREATE INDEX FRMPLV_I1 ON frmPollVotes (TopicID, VoteDate);

CREATE TABLE frmVisitors (
	SessionID varchar(32) NOT NULL PRIMARY KEY,
	MemberID int NULL UNIQUE,
	LastVisit datetime NOT NULL DEFAULT now(),
	InChat bit NULL DEFAULT 0
);
ALTER TABLE frmVisitors ADD CONSTRAINT FRMVIS_FK_MEM FOREIGN KEY (MemberID) REFERENCES frmMembers (ID);

CREATE TABLE Ankete (
	ID int IDENTITY NOT NULL PRIMARY KEY,
	Jezik char(2) NULL,
	Datum datetime NULL,
	Vprasanje nvarchar(255) NULL,
	Komentar nvarchar(255) NULL,
	Multiple bit NOT NULL DEFAULT 0,
	StGlasov int NULL,
	StOdg tinyint,
	Odg1 nvarchar(128) NULL,
	Odg2 nvarchar(128) NULL,
	Odg3 nvarchar(128) NULL,
	Odg4 nvarchar(128) NULL,
	Odg5 nvarchar(128) NULL,
	Odg6 nvarchar(128) NULL,
	Odg7 nvarchar(128) NULL,
	Odg8 nvarchar(128) NULL,
	Odg9 nvarchar(128) NULL,
	Odg10 nvarchar(128) NULL,
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
);
ALTER TABLE Ankete ADD CONSTRAINT ANK_CK_STO CHECK (StOdg BETWEEN 2 AND 10);
ALTER TABLE Ankete ADD CONSTRAINT ANK_FK_JEZ FOREIGN KEY (Jezik) REFERENCES Jeziki(Jezik);
ALTER TABLE Ankete ADD CONSTRAINT ANK_FK_ACL FOREIGN KEY (ACLID) REFERENCES SMACL (ACLID);
CREATE INDEX ANK_CI ON Ankete (Datum);
