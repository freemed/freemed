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

SOURCE data/schema/mysql/patient.sql
SOURCE data/schema/mysql/patient_emr.sql

CREATE TABLE IF NOT EXISTS `patientlocation` (
	  patient		BIGINT UNSIGNED NOT NULL DEFAULT 0
	, stamp			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
	, lat			DECIMAL ( 10, 8 ) NOT NULL DEFAULT 0.0
	, lon			DECIMAL ( 10, 8 ) NOT NULL DEFAULT 0.0
	, note			TEXT
	, geosource		INT UNSIGNED NOT NULL DEFAULT 0
	, user			INT UNSIGNED NOT NULL DEFAULT 0
	, active		ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active'
	, id			SERIAL

	# Define keys

	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS patientlocation_Upgrade;
DELIMITER //
CREATE PROCEDURE patientlocation_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER patientlocation_Delete;
	DROP TRIGGER patientlocation_Insert;
	DROP TRIGGER patientlocation_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL patientlocation_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER patientlocation_Delete
	AFTER DELETE ON patientlocation
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='patientlocation' AND oid=OLD.id;
	END;
//

CREATE TRIGGER patientlocation_Insert
	AFTER INSERT ON patientlocation
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'patientlocation', NEW.patient, NEW.id, NEW.stamp, NEW.note, NEW.user, NEW.active );
	END;
//

CREATE TRIGGER patientlocation_Update
	AFTER UPDATE ON patientlocation
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.stamp, patient=NEW.patient, summary=NEW.note, user=NEW.user, status=NEW.active WHERE module='patientlocation' AND oid=NEW.id;
	END;
//

DELIMITER ;

