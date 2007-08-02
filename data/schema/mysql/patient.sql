# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2007 FreeMED Software Foundation
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

CREATE TABLE IF NOT EXISTS `patient` (
	ptdtadd			DATE,
	ptdtmod			DATE,
	ptbal			REAL,
	ptbalfwd		REAL,
	ptunapp			REAL,
	ptdoc			VARCHAR (150),
	ptrefdoc		VARCHAR (150),
	ptpcp			VARCHAR (150),
	ptphy1			VARCHAR (150),
	ptphy2			VARCHAR (150),
	ptphy3			VARCHAR (150),
	ptphy4			VARCHAR (150),
	ptbilltype		ENUM( 'sta', 'mon', 'chg' ) NOT NULL,
	ptbudg			REAL,
	ptsalut			VARCHAR (8),
	ptlname			VARCHAR (50) NOT NULL,
	ptmaidenname		VARCHAR (50),
	ptfname			VARCHAR (50) NOT NULL,
	ptmname			VARCHAR (50),
	ptsuffix		VARCHAR (10),
	ptaddr1			VARCHAR (45),
	ptaddr2			VARCHAR (45),
	ptcity			VARCHAR (45),
	ptstate			VARCHAR (20),
	ptzip			CHAR (10),
	ptcountry		VARCHAR (50),
	ptprefcontact		VARCHAR (10) NOT NULL DEFAULT 'home',
	pthphone		VARCHAR (16),
	ptwphone		VARCHAR (16),
	ptmphone		VARCHAR (16),
	ptfax			VARCHAR (16),
	ptemail			VARCHAR (80),
	ptsex			ENUM( 'm', 'f', 't' ) NOT NULL,
	ptdob			DATE,
	ptssn			VARCHAR (9),
	ptdmv			VARCHAR (15),
	ptdtlpay		DATE,
	ptamtlpay		REAL,
	ptpaytype		INT UNSIGNED,
	ptdtbill		DATE,
	ptamtbill		REAL,
	ptstatus		INT UNSIGNED,
	ptytdchg		REAL,
	ptar			REAL,
	ptextinf		TEXT,
	ptdisc			REAL,
	ptdol			DATE,
	ptdiag1			INT UNSIGNED,
	ptdiag2			INT UNSIGNED,
	ptdiag3			INT UNSIGNED,
	ptdiag4			INT UNSIGNED,
	ptid			VARCHAR (10),
	pthistbal		REAL,
	ptmarital		ENUM ( 'single', 'married', 'divorced', 'separated', 'widowed', 'unknown' ),
	ptempl			ENUM ( 'y', 'n' ),
	ptemp1			INT UNSIGNED,
	ptemp2			INT UNSIGNED,
	ptnextofkin		TEXT,
	ptblood			CHAR (3),
	ptdead			INT UNSIGNED NOT NULL DEFAULT 0,
	ptdeaddt		DATE,
	pttimestamp		TIMESTAMP (16) NOT NULL DEFAULT NOW(),
	ptemritimestamp		TIMESTAMP (16),
	ptemriversion		BLOB,
	ptpharmacy		INT UNSIGNED,
	ptrace			INT UNSIGNED,
	ptreligion		INT UNSIGNED,
	ptarchive		INT UNSIGNED,
	iso			VARCHAR (15),
	id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

	#	Define keys
	KEY			( ptlname, ptfname, ptmname, ptid, ptdob ),
	PRIMARY KEY		( id )
);

DROP PROCEDURE IF EXISTS patient_Upgrade;
DELIMITER //
CREATE PROCEDURE patient_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER patient_Update;

	#----- Upgrades

	#	Version 0.9.0
	ALTER IGNORE TABLE patient ADD COLUMN ptsuffix VARCHAR (10) AFTER ptmname;
	ALTER IGNORE TABLE patient ADD COLUMN ptmphone CHAR(16) AFTER ptwphone;
	ALTER IGNORE TABLE patient ADD COLUMN ptprefcontact VARCHAR (10) NOT NULL DEFAULT 'home' AFTER ptcountry;
END
//
DELIMITER ;
CALL patient_Upgrade( );

#----- Triggers

DELIMITER //
CREATE TRIGGER patient_Update
	AFTER UPDATE ON patient
	FOR EACH ROW BEGIN
		IF
				OLD.ptlname<>NEW.ptlname OR
				OLD.ptfname<>NEW.ptfname OR
				OLD.ptmname<>NEW.ptmname OR
				OLD.ptmaidenname<>NEW.ptmaidenname OR
				OLD.ptsuffix<>NEW.ptsuffix OR
				OLD.ptaddr1<>NEW.ptaddr1 OR
				OLD.ptaddr2<>NEW.ptaddr2 OR
				OLD.ptcity<>NEW.ptcity OR
				OLD.ptstate<>NEW.ptstate OR
				OLD.ptzip<>NEW.ptzip OR
				OLD.ptcountry<>NEW.ptcountry OR
				OLD.ptprefcontact<>NEW.ptprefcontact OR
				OLD.pthphone<>NEW.pthphone OR
				OLD.ptwphone<>NEW.ptwphone OR
				OLD.ptmphone<>NEW.ptmphone OR
				OLD.ptfax<>NEW.ptfax OR
				OLD.ptemail<>NEW.ptemail OR
				OLD.ptmarital<>NEW.ptmarital THEN
			INSERT INTO `patient_prior` (
					patient,
					ptlname,
					ptfname,
					ptmname,
					ptmaidenname,
					ptsuffix,
					ptaddr1,
					ptaddr2,
					ptcity,
					ptstate,
					ptzip,
					ptcountry,
					ptprefcontact,
					pthphone,
					ptwphone,
					ptmphone,
					ptfax,
					ptemail,
					ptmarital
				) VALUES (
					NEW.id,
					OLD.ptlname,
					OLD.ptfname,
					OLD.ptmname,
					OLD.ptmaidenname,
					OLD.ptsuffix,
					OLD.ptaddr1,
					OLD.ptaddr2,
					OLD.ptcity,
					OLD.ptstate,
					OLD.ptzip,
					OLD.ptcountry,
					OLD.ptprefcontact,
					OLD.pthphone,
					OLD.ptwphone,
					OLD.ptmphone,
					OLD.ptfax,
					OLD.ptemail,
					OLD.ptmarital
				);
		END IF;
		IF
				OLD.ptpcp<>NEW.ptpcp OR
				OLD.ptdoc<>NEW.ptdoc OR
				OLD.ptphy1<>NEW.ptphy1 OR
				OLD.ptphy2<>NEW.ptphy2 OR
				OLD.ptphy3<>NEW.ptphy3 OR
				OLD.ptphy4<>NEW.ptphy4 OR
				OLD.ptrefdoc<>NEW.ptrefdoc THEN
			INSERT INTO `patient_prior_provider` (
					patient,
					ptdoc,
					ptrefdoc,
					ptpcp,
					ptphy1,
					ptphy2,
					ptphy3,
					ptphy4
				) VALUES (
					NEW.id,
					OLD.ptdoc,
					OLD.ptrefdoc,
					OLD.ptpcp,
					OLD.ptphy1,
					OLD.ptphy2,
					OLD.ptphy3,
					OLD.ptphy4
				);
		END IF;
	END;
//
DELIMITER ;

#----- Address table

CREATE TABLE IF NOT EXISTS `patient_address` (
	patient			BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
	stamp			TIMESTAMP (16) NOT NULL DEFAULT NOW(),
	type			CHAR (2) NOT NULL DEFAULT 'H',
	active			BOOL NOT NULL DEFAULT FALSE,
	relate			CHAR (2) NOT NULL DEFAULT 'S',
	line1			VARCHAR (100),
	line2			VARCHAR (100),
	city			VARCHAR (100),
	stpr			VARCHAR (100),
	postal			CHAR (10) NOT NULL,
	country			CHAR (60) NOT NULL,
	id			SERIAL

	, KEY ( patient, stamp )
);

#----- Prior demographics holding table

CREATE TABLE IF NOT EXISTS `patient_prior` (
	patient			BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
	stamp			TIMESTAMP (16) NOT NULL DEFAULT NOW(),

	ptlname			VARCHAR (50) NOT NULL,
	ptmaidenname		VARCHAR (50),
	ptfname			VARCHAR (50) NOT NULL,
	ptmname			VARCHAR (50),
	ptsuffix		VARCHAR (10),
	ptaddr1			VARCHAR (45),
	ptaddr2			VARCHAR (45),
	ptcity			VARCHAR (45),
	ptstate			VARCHAR (20),
	ptzip			CHAR (10),
	ptcountry		VARCHAR (50),
	ptprefcontact		VARCHAR (10) NOT NULL DEFAULT 'home',
	pthphone		VARCHAR (16),
	ptwphone		VARCHAR (16),
	ptmphone		VARCHAR (16),
	ptfax			VARCHAR (16),
	ptemail			VARCHAR (80),
	ptmarital		ENUM ( 'single', 'married', 'divorced', 'separated', 'widowed', 'unknown' ),
	id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

	#	Define keys

	PRIMARY KEY		( id )
	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `patient_prior_provider` (
	patient			BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
	stamp			TIMESTAMP (16) NOT NULL DEFAULT NOW(),

	ptdoc			VARCHAR (150),
	ptrefdoc		VARCHAR (150),
	ptpcp			VARCHAR (150),
	ptphy1			VARCHAR (150),
	ptphy2			VARCHAR (150),
	ptphy3			VARCHAR (150),
	ptphy4			VARCHAR (150),
	id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

	#	Define keys

	PRIMARY KEY		( id )
	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);
