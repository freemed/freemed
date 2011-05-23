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
SOURCE data/schema/mysql/workflow_status.sql

CREATE TABLE IF NOT EXISTS `superbill` (
	dateofservice		DATE,
	enteredby		INT UNSIGNED,
	patient			BIGINT UNSIGNED NOT NULL DEFAULT 0,
	provider		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	procs			BLOB,
	dx			BLOB,
	details			BLOB,
	note			VARCHAR (250) NOT NULL DEFAULT '',
	reviewed		INT UNSIGNED DEFAULT 0,
	processed		INT UNSIGNED DEFAULT 0,
	id			SERIAL,

	#	Define keys
	KEY			( dateofservice, reviewed ),
	FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS superbill_Upgrade;
DELIMITER //
CREATE PROCEDURE superbill_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER superbill_Delete;
	DROP TRIGGER superbill_Insert;
	DROP TRIGGER superbill_Update;

	#----- Upgrades
	ALTER TABLE superbill ADD COLUMN provider BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER patient;
	ALTER TABLE superbill CHANGE COLUMN reviewed reviewed INT UNSIGNED DEFAULT 0;
	ALTER TABLE superbill ADD COLUMN processed INT UNSIGNED DEFAULT 0 AFTER reviewed;
	ALTER TABLE superbill ADD COLUMN details BLOB AFTER dx;
END
//
DELIMITER ;
CALL superbill_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER superbill_Delete
	AFTER DELETE ON superbill
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='superbill' AND oid=OLD.id;
		CALL patientWorkflowUpdateStatus( OLD.patient, OLD.dateofservice, 'superbill', FALSE, OLD.enteredby );
		DELETE FROM `systemtaskinbox` WHERE module = 'superbill' AND oid = OLD.id;
	END;
//

CREATE TRIGGER superbill_Insert
	AFTER INSERT ON superbill
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user ) VALUES ( 'superbill', NEW.patient, NEW.id, NEW.dateofservice, p, NEW.enteredby );
		CALL patientWorkflowUpdateStatus( NEW.patient, NEW.dateofservice, 'superbill', TRUE, NEW.enteredby );
		INSERT INTO `systemtaskinbox` ( user, patient, module, box, oid, summary ) VALUES ( NEW.enteredby, NEW.patient, 'superbill', 'superbill', NEW.id, NEW.note );
	END;
//

CREATE TRIGGER superbill_Update
	AFTER UPDATE ON superbill
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.dateofservice, patient=NEW.patient, summary=p, user=NEW.enteredby WHERE module='superbill' AND oid=NEW.id;
		UPDATE `systemtaskinbox` SET user = NEW.enteredby, patient = NEW.patient, oid = NEW.id, summary = NEW.note WHERE module = 'superbill' AND oid = NEW.id;
	END;
//

DELIMITER ;

