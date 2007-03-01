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

CREATE TABLE IF NOT EXISTS `immunization` (
	dateof			TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	patient			BIGINT UNSIGNED NOT NULL,
	provider		BIGINT UNSIGNED NOT NULL,
	eoc			INT UNSIGNED,
	immunization		INT UNSIGNED NOT NULL DEFAULT 0,
	notes			TEXT,
	locked			INT UNSIGNED,
	id			SERIAL,

	#	Define keys
	KEY			( patient, dateof, provider ),
	FOREIGN KEY		( patient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS immunization_Upgrade;
DELIMITER //
CREATE PROCEDURE immunization_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER immunization_Delete;
	DROP TRIGGER immunization_Insert;
	DROP TRIGGER immunization_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL immunization_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER immunization_Delete
	AFTER DELETE ON immunization
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='immunization' AND oid=OLD.id;
	END;
//

CREATE TRIGGER immunization_Insert
	AFTER INSERT ON immunization
	FOR EACH ROW BEGIN
		DECLARE i VARCHAR (250);
		SELECT description INTO i FROM bccdc WHERE id=NEW.immunization;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, locked ) VALUES ( 'immunization', NEW.patient, NEW.id, NEW.dateof, i, NEW.locked );
	END;
//

CREATE TRIGGER immunization_Update
	AFTER UPDATE ON immunization
	FOR EACH ROW BEGIN
		DECLARE i VARCHAR (250);
		SELECT description INTO i FROM bccdc WHERE id=NEW.immunization;
		UPDATE `patient_emr` SET stamp=NEW.dateof, patient=NEW.patient, summary=i, locked=NEW.locked WHERE module='immunization' AND oid=NEW.id;
	END;
//

DELIMITER ;

