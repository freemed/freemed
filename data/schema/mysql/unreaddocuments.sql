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

CREATE TABLE IF NOT EXISTS `unreaddocuments` (
	urfstamp		TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	urfdate			DATE NOT NULL,
	urffilename		VARCHAR (150) NOT NULL,
	urftype			VARCHAR (50),
	urfpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	urfphysician		INT UNSIGNED NOT NULL DEFAULT 0,
	urfnote			TEXT,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	id			SERIAL,

	#	Define keys

	KEY			( urfphysician, urfpatient ),
	FOREIGN KEY		( urfpatient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS unreaddocuments_Upgrade;
DELIMITER //
CREATE PROCEDURE unreaddocuments_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER unreaddocuments_Delete;
	DROP TRIGGER unreaddocuments_Insert;
	DROP TRIGGER unreaddocuments_Update;

	#----- Upgrades
	ALTER IGNORE TABLE unreaddocuments ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER urfnote;
END
//
DELIMITER ;
CALL unreaddocuments_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER unreaddocuments_Delete
	AFTER DELETE ON unreaddocuments
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='unreaddocuments' AND oid=OLD.id;
		DELETE FROM `systemtaskinbox` WHERE module = 'unreaddocuments' AND oid = OLD.id;
	END;
//

CREATE TRIGGER unreaddocuments_Insert
	AFTER INSERT ON unreaddocuments
	FOR EACH ROW BEGIN
		DECLARE p INT UNSIGNED;
		SELECT id INTO p FROM user WHERE userrealphy = NEW.urfphysician;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user ) VALUES ( 'unreaddocuments', NEW.urfpatient, NEW.id, NEW.urfdate, NEW.urfnote, NEW.user );
		INSERT INTO `systemtaskinbox` ( user, patient, module, box, oid, summary ) VALUES ( p, NEW.urfpatient, 'unreaddocuments', 'unreaddocuments', NEW.id, NEW.urfnote );
	END;
//

CREATE TRIGGER unreaddocuments_Update
	AFTER UPDATE ON unreaddocuments
	FOR EACH ROW BEGIN
		DECLARE p INT UNSIGNED;
		SELECT id INTO p FROM user WHERE userrealphy = NEW.urfphysician;
		UPDATE `patient_emr` SET stamp=NEW.urfdate, patient=NEW.urfpatient, summary=NEW.urfnote, user=NEW.user WHERE module='unreaddocuments' AND oid=NEW.id;
		UPDATE `systemtaskinbox` SET user = p, patient = NEW.urfpatient, oid = NEW.id, summary = NEW.urfnote WHERE module = 'unreaddocuments' AND oid = NEW.id;
	END;
//

DELIMITER ;

