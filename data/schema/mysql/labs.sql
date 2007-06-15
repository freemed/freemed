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

SOURCE data/schema/mysql/patient.sql
SOURCE data/schema/mysql/patient_emr.sql

CREATE TABLE IF NOT EXISTS `labs` (
	labpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	labfiller		TEXT,
	labstatus		CHAR (2),
	labprovider		INT UNSIGNED NOT NULL DEFAULT 0,
	labordercode		VARCHAR (16),
	laborderdescrip		VARCHAR (250),
	labcomponentcode	VARCHAR (16),
	labcomponentdescrip	VARCHAR (250),
	labfillernum		VARCHAR (16),
	labplacernum		VARCHAR (16),
	labtimestamp		TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	labresultstatus		CHAR (1),
	labnotes		TEXT,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			SERIAL,

	#	Define keys
	KEY			( labpatient, labprovider, labtimestamp ),
	FOREIGN KEY		( labpatient ) REFERENCES patient.id ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `labresults` (
	labid			BIGINT UNSIGNED NOT NULL,
	labpatient		BIGINT UNSIGNED NOT NULL,
	labobsnote		TEXT,
	labobscode		VARCHAR (150),
	labobsdescrip		VARCHAR (250),
	labobsvalue		TEXT,
	labobsunit		VARCHAR (150),
	labobsranges		VARCHAR (50),
	labobsabnormal		CHAR (5),
	labobsstatus		CHAR (1),
	labobsreported		TIMESTAMP (14),
	labobsfiller		VARCHAR (60),
	id			SERIAL,

	#	Define keys

	KEY			( labpatient, labid ),
	FOREIGN KEY		( labpatient ) REFERENCES patient.id ON DELETE CASCADE,
	FOREIGN KEY		( labid ) REFERENCES labs.id ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS labs_Upgrade;
DELIMITER //
CREATE PROCEDURE labs_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER labs_Delete;
	DROP TRIGGER labs_Insert;
	DROP TRIGGER labs_Update;

	#----- Upgrades
	ALTER IGNORE TABLE labs ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER labnotes;
	ALTER IGNORE TABLE labs ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
END
//
DELIMITER ;
CALL labs_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER labs_Delete
	AFTER DELETE ON labs
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='labs' AND oid=OLD.id;
	END;
//

CREATE TRIGGER labs_Insert
	AFTER INSERT ON labs
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'labs', NEW.labpatient, NEW.id, NEW.labtimestamp, CONCAT( NEW.labordercode, ' - ', NEW.laborderdescrip ), NEW.user, NEW.active );
	END;
//

CREATE TRIGGER labs_Update
	AFTER UPDATE ON labs
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.labtimestamp, patient=NEW.labpatient, summary=CONCAT( NEW.labordercode, ' - ', NEW.laborderdescrip ), user=NEW.user, status=NEW.active WHERE module='labs' AND oid=NEW.id;
	END;
//

DELIMITER ;

