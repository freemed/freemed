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

CREATE TABLE IF NOT EXISTS `allergies` (
	allergy			VARCHAR (150) NOT NULL,
	severity		VARCHAR (150) NOT NULL,
	patient			BIGINT UNSIGNED NOT NULL DEFAULT 0,
	reviewed		TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			SERIAL,

	#	Define keys

	KEY			( patient, allergy ),
	FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS allergies_Upgrade;
DELIMITER //
CREATE PROCEDURE allergies_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER allergies_Delete;
	DROP TRIGGER allergies_Insert;
	DROP TRIGGER allergies_Update;

	#----- Upgrades

	#	Version 0.2.1
	ALTER IGNORE TABLE allergies ADD COLUMN reviewed TIMESTAMP (14) NOT NULL DEFAULT NOW() AFTER patient;
	ALTER IGNORE TABLE allergies ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER reviewed;
	ALTER IGNORE TABLE allergies ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
END
//
DELIMITER ;
CALL allergies_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER allergies_Delete
	AFTER DELETE ON allergies
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='allergies' AND oid=OLD.id;
	END;
//

CREATE TRIGGER allergies_Insert
	AFTER INSERT ON allergies
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'allergies', NEW.patient, NEW.id, NEW.reviewed, NEW.allergy, NEW.user, NEW.active );
	END;
//

CREATE TRIGGER allergies_Update
	AFTER UPDATE ON allergies
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.reviewed, patient=NEW.patient, summary=NEW.allergy,user=NEW.user,active=NEW.active WHERE module='allergies' AND oid=NEW.id;
	END;
//

DELIMITER ;

