# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2011 FreeMED Software Foundation
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

SOURCE data/schema/mysql/patient.sql
SOURCE data/schema/mysql/patient_emr.sql

CREATE TABLE IF NOT EXISTS `eoc` (
	eocpatient		BIGINT UNSIGNED NOT NULL,
	eocdescrip		VARCHAR (100),
	eocstartdate		DATE,
	eocdtlastsimilar	DATE,
	eocreferrer		INT UNSIGNED,
	eocfacility		INT UNSIGNED,
	eocdiagfamily		TEXT,
	eocrelpreg		ENUM ( 'no', 'yes' ),
	eocrelemp		ENUM ( 'no', 'yes' ),
	eocrelauto		ENUM ( 'no', 'yes' ),
	eocrelother		ENUM ( 'no', 'yes' ),
	eocrelstpr		VARCHAR (10),
	eoctype			ENUM ( 'acute', 'chronic', 'chronic recurrent', 'historical' ),
	eochospital		INT UNSIGNED,
	eocrelautoname		VARCHAR (100),
	eocrelautoaddr1		VARCHAR (100),
	eocrelautoaddr2		VARCHAR (100),
	eocrelautocity		VARCHAR (50),
	eocrelautostpr		VARCHAR (30),
	eocrelautozip		VARCHAR (16),
	eocrelautocountry	VARCHAR (100),
	eocrelautocase		VARCHAR (30),
	eocrelautorcname	VARCHAR (100),
	eocrelautorcphone	VARCHAR (16),
	eocrelempname		VARCHAR (100),
	eocrelempaddr1		VARCHAR (100),
	eocrelempaddr2		VARCHAR (100),
	eocrelempcity		VARCHAR (50),
	eocrelempstpr		VARCHAR (30),
	eocrelempzip		VARCHAR (10),
	eocrelempcountry	VARCHAR (100),
	eocrelempfile		VARCHAR (30),
	eocrelemprcname		VARCHAR (100),
	eocrelemprcphone	VARCHAR (16),
	eocrelemprcemail	VARCHAR (100),
	eocrelpregcycle		INT UNSIGNED,
	eocrelpreggravida	INT UNSIGNED,
	eocrelpregpara		INT UNSIGNED,
	eocrelpregmiscarry	INT UNSIGNED,
	eocrelpregabort		INT UNSIGNED,
	eocrelpreglastper	DATE,
	eocrelpregconfine	DATE,
	eocrelothercomment	VARCHAR (100),
	eocdistype		INT UNSIGNED,
	eocdisfromdt		DATE,
	eocdistodt		DATE,
	eocdisworkdt		DATE,
	eochosadmdt		DATE,
	eochosdischrgdt		DATE,
	eocrelautotime		CHAR (8),
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			SERIAL,

	#	Define keys

	KEY			( eocpatient, eocstartdate, eocdtlastsimilar ),
	FOREIGN KEY		( eocpatient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS eoc_Upgrade;
DELIMITER //
CREATE PROCEDURE eoc_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER eoc_Delete;
	DROP TRIGGER eoc_Insert;
	DROP TRIGGER eoc_Update;

	#----- Upgrades
	ALTER TABLE eoc ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER eocrelautotime;
	ALTER TABLE eoc ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
END
//
DELIMITER ;
CALL eoc_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER eoc_Delete
	AFTER DELETE ON eoc
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='eoc' AND oid=OLD.id;
	END;
//

CREATE TRIGGER eoc_Insert
	AFTER INSERT ON eoc
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'eoc', NEW.eocpatient, NEW.id, NOW(), NEW.eocdescrip, NEW.user, NEW.active );
	END;
//

CREATE TRIGGER eoc_Update
	AFTER UPDATE ON eoc
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NOW(), patient=NEW.eocpatient, summary=NEW.eocdescrip, user=NEW.user, status=NEW.active WHERE module='eoc' AND oid=NEW.id;
	END;
//

DELIMITER ;

