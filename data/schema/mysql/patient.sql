# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2012 FreeMED Software Foundation
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

SOURCE data/schema/mysql/_functions.sql

CREATE TABLE IF NOT EXISTS `patient` (
	  ptdtadd		DATE
	, ptdtmod		DATE
	, ptbal			REAL
	, ptbalfwd		REAL
	, ptunapp		REAL
	, ptdoc			VARCHAR (150)
	, ptrefdoc		VARCHAR (150)
	, ptpcp			VARCHAR (150)
	, ptphy1		VARCHAR (150)
	, ptphy2		VARCHAR (150)
	, ptphy3		VARCHAR (150)
	, ptphy4		VARCHAR (150)
	, ptbilltype		ENUM( 'sta', 'mon', 'chg' ) NOT NULL
	, ptbudg		REAL
	, ptsalut		VARCHAR (8)
	, ptlname		VARCHAR (50) NOT NULL
	, ptmaidenname		VARCHAR (50)
	, ptfname		VARCHAR (50) NOT NULL
	, ptmname		VARCHAR (50)
	, ptsuffix		VARCHAR (10)
	, ptaddr1		VARCHAR (45)
	, ptaddr2		VARCHAR (45)
	, ptcity		VARCHAR (45)
	, ptstate		VARCHAR (20)
	, ptzip			CHAR (10)
	, ptcountry		VARCHAR (50)
	, ptprefcontact		VARCHAR (10) NOT NULL DEFAULT 'home'
	, pthphone		VARCHAR (16)
	, ptwphone		VARCHAR (16)
	, ptmphone		VARCHAR (16)
	, ptfax			VARCHAR (16)
	, ptemail		VARCHAR (80)
	, ptsex			ENUM( 'm', 'f', 't' ) NOT NULL
	, ptdob			DATE
	, ptssn			VARCHAR (9)
	, ptdmv			VARCHAR (15)
	, ptdtlpay		DATE
	, ptamtlpay		REAL
	, ptpaytype		INT UNSIGNED
	, ptdtbill		DATE
	, ptamtbill		REAL
	, ptstatus		INT UNSIGNED
	, ptytdchg		REAL
	, ptar			REAL
	, ptextinf		TEXT
	, ptdisc		REAL
	, ptdol			DATE
	, ptdiag1		INT UNSIGNED
	, ptdiag2		INT UNSIGNED
	, ptdiag3		INT UNSIGNED
	, ptdiag4		INT UNSIGNED
	, ptdiagset		ENUM ( '9', '10' ) NOT NULL DEFAULT '9'
	, ptid			VARCHAR (10)
	, pthistbal		REAL
	, ptmarital		ENUM ( 'single', 'married', 'divorced', 'separated', 'widowed', 'unknown' )
	, ptempl		ENUM ( 'y', 'n', 'r', 'p', 's', 'm', 'u' )
	, ptemp1		INT UNSIGNED
	, ptemp2		INT UNSIGNED
	, ptnextofkin		TEXT
	, ptblood		CHAR (3)
	, ptdead		INT UNSIGNED NOT NULL DEFAULT 0
	, ptdeaddt		DATE
	, pttimestamp		TIMESTAMP (16) NOT NULL DEFAULT NOW()
	, ptemritimestamp	TIMESTAMP (16)
	, ptemriversion		BLOB
	, ptpharmacy		INT UNSIGNED
	, ptrace		INT UNSIGNED
	, ptreligion		INT UNSIGNED
	, ptarchive		INT UNSIGNED DEFAULT 0
	, ptprimaryfacility	INT UNSIGNED DEFAULT 0
	, ptprimarylanguage	CHAR (5) NOT NULL DEFAULT 'en'
	, iso			VARCHAR (15)
	, id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT

	#	Define keys
	, KEY			( ptlname, ptfname, ptmname, ptid, ptdob )
	, PRIMARY KEY		( id )
);

DROP PROCEDURE IF EXISTS patient_trigger_Remove;
DELIMITER //
CREATE PROCEDURE patient_trigger_Remove ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER patient_Insert;
	DROP TRIGGER patient_Update;
	DROP TRIGGER patient_address_Insert;
	DROP TRIGGER patient_address_Update;
END//
DELIMITER ;

CALL patient_trigger_Remove();

DROP PROCEDURE IF EXISTS patient_Upgrade;
DELIMITER //
CREATE PROCEDURE patient_Upgrade ( )
BEGIN
	DECLARE patient_Count INT UNSIGNED DEFAULT 0;
	DECLARE patient_keypad_Count INT UNSIGNED DEFAULT 0;
	DECLARE patient_phone_Count INT UNSIGNED DEFAULT 0;

	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades

	#	Version 0.9.0
	ALTER IGNORE TABLE patient ADD COLUMN ptsuffix VARCHAR (10) AFTER ptmname;
	ALTER IGNORE TABLE patient ADD COLUMN ptmphone CHAR(16) AFTER ptwphone;
	ALTER IGNORE TABLE patient ADD COLUMN ptprefcontact VARCHAR (10) NOT NULL DEFAULT 'home' AFTER ptcountry;
	ALTER IGNORE TABLE patient ADD COLUMN ptprimaryfacility INT UNSIGNED DEFAULT 0 AFTER ptarchive;

	# If we have nothing in patient_keypad_lookup, populate.
	SELECT COUNT(*) INTO patient_Count FROM patient;
	SELECT COUNT(*) INTO patient_keypad_Count FROM patient_keypad_lookup;
	SELECT COUNT(*) INTO patient_phone_Count FROM patient_phone_lookup;
	IF patient_keypad_Count = 0 AND patient_Count > 0 THEN
		INSERT INTO `patient_keypad_lookup` (
				patient,
				last_name,
				first_name,
				year_of_birth,
				ssn,
				archive
			) SELECT
				id,
				STRING_TO_PHONE( ptlname ),
				STRING_TO_PHONE( ptfname ),
				YEAR( ptdob ),
				SUBSTRING( ptssn FROM -4 FOR 4 ),
				ptarchive
			FROM patient;
	END IF;

	#---- Populate phone number lookup table if needed
	IF patient_phone_Count = 0 AND patient_Count > 0 THEN
		INSERT INTO `patient_phone_lookup` (
				  patient, type, phone_number
			) SELECT
				  id, 'home', pthphone
			FROM patient
			WHERE ptarchive = 0 AND LENGTH( pthphone ) > 0;
		INSERT INTO `patient_phone_lookup` (
				  patient, type, phone_number
			) SELECT
				  id, 'work', ptwphone
			FROM patient
			WHERE ptarchive = 0 AND LENGTH( ptwphone ) > 0;
		INSERT INTO `patient_phone_lookup` (
				  patient, type, phone_number
			) SELECT
				  id, 'mobile', ptmphone
			FROM patient
			WHERE ptarchive = 0 AND LENGTH( ptmphone ) > 0;
	END IF;

	ALTER IGNORE TABLE patient ADD COLUMN ptdiagset ENUM ( '9', '10' ) NOT NULL DEFAULT '9' AFTER ptdiag4;

	ALTER IGNORE TABLE patient ADD COLUMN ptprimarylanguage CHAR (5) NOT NULL DEFAULT 'en' AFTER ptprimaryfacility;
END
//
DELIMITER ;

#----- Triggers

DELIMITER //
CREATE TRIGGER patient_Insert
	AFTER INSERT ON patient
	FOR EACH ROW BEGIN
		#-----	Add new phone lookup
		INSERT INTO `patient_keypad_lookup` (
				patient,
				last_name,
				first_name,
				year_of_birth,
				ssn,
				archive
			) VALUES (
				NEW.id,
				STRING_TO_PHONE( NEW.ptlname ),
				STRING_TO_PHONE( NEW.ptfname ),
				YEAR( NEW.ptdob ),
				SUBSTRING( NEW.ptssn FROM -4 FOR 4 ),
				NEW.ptarchive
			);
		#----- If the phone numbers aren't null, insert
		IF LENGTH(NEW.pthphone) > 0 THEN
			INSERT INTO `patient_phone_lookup` (
				  patient
				, type
				, phone_number
			) VALUES (
				  NEW.id
				, 'home'
				, NEW.pthphone
			);
		END IF;
		IF LENGTH(NEW.ptwphone) > 0 THEN
			INSERT INTO `patient_phone_lookup` (
				  patient
				, type
				, phone_number
			) VALUES (
				  NEW.id
				, 'work'
				, NEW.ptwphone
			);
		END IF;
		IF LENGTH(NEW.ptmphone) > 0 THEN
			INSERT INTO `patient_phone_lookup` (
				  patient
				, type
				, phone_number
			) VALUES (
				  NEW.id
				, 'mobile'
				, NEW.ptmphone
			);
		END IF;
	END;
//

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
		#-----	Update phone lookup
		IF
				OLD.ptlname<>NEW.ptlname OR
				OLD.ptfname<>NEW.ptfname OR
				OLD.ptdob<>NEW.ptdob OR
				OLD.ptarchive<>NEW.ptarchive OR
				OLD.ptssn<>NEW.ptssn THEN
			UPDATE `patient_keypad_lookup` SET
				last_name = STRING_TO_PHONE( NEW.ptlname ),
				first_name = STRING_TO_PHONE( NEW.ptfname ),
				year_of_birth = YEAR( NEW.ptdob ),
				ssn = SUBSTRING( NEW.ptssn FROM -4 FOR 4 ),
				archive = NEW.ptarchive
			WHERE patient = NEW.id;
		END IF;
		#----- Handle all phone lookups
		IF OLD.pthphone <> NEW.pthphone THEN
			IF LENGTH(OLD.pthphone) > 0 THEN
				UPDATE `patient_phone_lookup` SET
					phone_number = NEW.pthphone
				WHERE patient = NEW.id AND type = 'home' AND
					phone_number = OLD.pthphone;
			ELSE
				INSERT INTO `patient_phone_lookup` (
					  patient
					, type
					, phone_number
				) VALUES (
					  NEW.id
					, 'home'
					, NEW.pthphone
				);
			END IF;
		END IF;
		IF OLD.ptwphone <> NEW.ptwphone THEN
			IF LENGTH(OLD.ptwphone) > 0 THEN
				UPDATE `patient_phone_lookup` SET
					phone_number = NEW.ptwphone
				WHERE patient = NEW.id AND type = 'work' AND
					phone_number = OLD.ptwphone;
			ELSE
				INSERT INTO `patient_phone_lookup` (
					  patient
					, type
					, phone_number
				) VALUES (
					  NEW.id
					, 'work'
					, NEW.ptwphone
				);
			END IF;
		END IF;
		IF OLD.ptmphone <> NEW.ptmphone THEN
			IF LENGTH(OLD.ptmphone) > 0 THEN
				UPDATE `patient_phone_lookup` SET
					phone_number = NEW.ptmphone
				WHERE patient = NEW.id AND type = 'mobile' AND
					phone_number = OLD.ptmphone;
			ELSE
				INSERT INTO `patient_phone_lookup` (
					  patient
					, type
					, phone_number
				) VALUES (
					  NEW.id
					, 'mobile'
					, NEW.ptmphone
				);
			END IF;
		END IF;
	END;
//
DELIMITER ;

#----- Address table

CREATE TABLE IF NOT EXISTS `patient_address` (
	  patient		BIGINT(20) UNSIGNED NOT NULL DEFAULT 0
	, stamp			TIMESTAMP (16) NOT NULL DEFAULT NOW()
	, type			CHAR (2) NOT NULL DEFAULT 'H'
	, active		BOOL NOT NULL DEFAULT FALSE
	, relate		CHAR (2) NOT NULL DEFAULT 'S'
	, line1			VARCHAR (100)
	, line2			VARCHAR (100)
	, city			VARCHAR (100)
	, stpr			VARCHAR (100)
	, postal		CHAR (10) NOT NULL
	, country		CHAR (60) NOT NULL
	, id			SERIAL

	, KEY ( patient, stamp )
);

DELIMITER //
CREATE TRIGGER patient_address_Update
	AFTER UPDATE ON patient_address
	FOR EACH ROW BEGIN
		IF NEW.active AND NOT OLD.active THEN
			UPDATE patient SET ptaddr1 = NEW.line1, ptaddr2 = NEW.line2, ptcity = NEW.city, ptstate = NEW.stpr, ptzip = NEW.postal, ptcountry = NEW.country WHERE id = NEW.patient;
		END IF;
	END;
//
DELIMITER ;

DELIMITER //
CREATE TRIGGER patient_address_Insert
	AFTER INSERT ON patient_address
	FOR EACH ROW BEGIN
		IF NEW.active THEN
			UPDATE patient SET ptaddr1 = NEW.line1, ptaddr2 = NEW.line2, ptcity = NEW.city, ptstate = NEW.stpr, ptzip = NEW.postal, ptcountry = NEW.country WHERE id = NEW.patient;
		END IF;
	END;
//
DELIMITER ;

#----- Prior demographics holding table

CREATE TABLE IF NOT EXISTS `patient_prior` (
	  patient		BIGINT(20) UNSIGNED NOT NULL DEFAULT 0
	, stamp			TIMESTAMP (16) NOT NULL DEFAULT NOW()

	, ptlname		VARCHAR (50) NOT NULL
	, ptmaidenname		VARCHAR (50)
	, ptfname		VARCHAR (50) NOT NULL
	, ptmname		VARCHAR (50)
	, ptsuffix		VARCHAR (10)
	, ptaddr1		VARCHAR (45)
	, ptaddr2		VARCHAR (45)
	, ptcity		VARCHAR (45)
	, ptstate		VARCHAR (20)
	, ptzip			CHAR (10)
	, ptcountry		VARCHAR (50)
	, ptprefcontact		VARCHAR (10) NOT NULL DEFAULT 'home'
	, pthphone		VARCHAR (16)
	, ptwphone		VARCHAR (16)
	, ptmphone		VARCHAR (16)
	, ptfax			VARCHAR (16)
	, ptemail		VARCHAR (80)
	, ptmarital		ENUM ( 'single', 'married', 'divorced', 'separated', 'widowed', 'unknown' )
	, id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT

	#	Define keys

	, PRIMARY KEY		( id )
	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `patient_prior_provider` (
	  patient		BIGINT(20) UNSIGNED NOT NULL DEFAULT 0
	, stamp			TIMESTAMP (16) NOT NULL DEFAULT NOW()

	, ptdoc			VARCHAR (150)
	, ptrefdoc		VARCHAR (150)
	, ptpcp			VARCHAR (150)
	, ptphy1		VARCHAR (150)
	, ptphy2		VARCHAR (150)
	, ptphy3		VARCHAR (150)
	, ptphy4		VARCHAR (150)
	, id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT

	#	Define keys

	, PRIMARY KEY		( id )
	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `patient_keypad_lookup` (
	  patient		BIGINT(20) UNSIGNED NOT NULL DEFAULT 0
	, stamp			TIMESTAMP (16) NOT NULL DEFAULT NOW()

	, last_name		VARCHAR (30)
	, first_name		VARCHAR (30)
	, year_of_birth		CHAR (4)
	, ssn			CHAR (4)
	, pin			VARCHAR (150)	# future use
	, archive		INT UNSIGNED DEFAULT 0
	, id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT

	#	Define keys

	, PRIMARY KEY		( id )
	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `patient_phone_lookup` (
	  patient		BIGINT(20) UNSIGNED NOT NULL DEFAULT 0
	, stamp			TIMESTAMP (16) NOT NULL DEFAULT NOW()

	, type			ENUM ( 'home', 'work', 'mobile', 'other' ) NOT NULL DEFAULT 'home'
	, phone_number		VARCHAR (20) NOT NULL
	, id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT

	#	Define keys

	, PRIMARY KEY		( id )
	, KEY			( phone_number )
	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `patient_view_history` (
	  user			INT UNSIGNED NOT NULL
	, patient		BIGINT UNSIGNED NOT NULL
	, stamp			TIMESTAMP (16) NOT NULL DEFAULT NOW()
	, viewed		VARCHAR (100) NOT NULL DEFAULT 'EMR'
	, id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT

	#	Define keys

	, PRIMARY KEY		( id )
	, KEY			( user )
	, KEY			( patient, viewed )
);

#----- Call upgrade at end of DTD so that all tables are created properly

CALL patient_Upgrade( );

#----- Make sure patient data store definition is loaded

SOURCE data/schema/mysql/pds.sql

