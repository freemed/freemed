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

CREATE TABLE IF NOT EXISTS `previous_operations` (
	odate			DATE NOT NULL,
	operation		VARCHAR (250) NOT NULL DEFAULT '',
	opatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	id			SERIAL,

	#	Define keys

	KEY			( opatient, odate ),
	FOREIGN KEY		( opatient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS previous_operations_Upgrade;
DELIMITER //
CREATE PROCEDURE previous_operations_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER previous_operations_Delete;
	DROP TRIGGER previous_operations_Insert;
	DROP TRIGGER previous_operations_Uodate;

	#----- Upgrades
END
//
DELIMITER ;
CALL previous_operations_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER previous_operations_Delete
	AFTER DELETE ON previous_operations
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='previous_operations' AND oid=OLD.id;
	END;
//

CREATE TRIGGER previous_operations_Insert
	AFTER INSERT ON previous_operations
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'previous_operations', NEW.opatient, NEW.id, NEW.odate, NEW.operation );
	END;
//

CREATE TRIGGER previous_operations_Uodate
	AFTER UPDATE ON previous_operations
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.odate, patient=NEW.opatient, summary=NEW.operation WHERE module='previous_operations' AND oid=NEW.id;
	END;
//

DELIMITER ;

