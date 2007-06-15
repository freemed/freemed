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

CREATE TABLE IF NOT EXISTS `medications` (
	mdrug			VARCHAR (150),
	mdosage			VARCHAR (150),
	mroute			VARCHAR (150),
	mpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	mdate			DATE,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			SERIAL,

	#	Define keys

	KEY			( mpatient, mdate ),
	FOREIGN KEY		( mpatient ) REFERENCES patient.id ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS medications_Upgrade;
DELIMITER //
CREATE PROCEDURE medications_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER medications_Delete;
	DROP TRIGGER medications_Insert;
	DROP TRIGGER medications_Update;

	#----- Upgrades
	ALTER IGNORE TABLE medications ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER mdate;
	ALTER IGNORE TABLE medications ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
END
//
DELIMITER ;
CALL medications_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER medications_Delete
	AFTER DELETE ON medications
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='medications' AND oid=OLD.id;
	END;
//

CREATE TRIGGER medications_Insert
	AFTER INSERT ON medications
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'medications', NEW.mpatient, NEW.id, NEW.mdate, CONCAT(NEW.mdrug, ' ', NEW.mdosage), NEW.user, NEW.active );
	END;
//

CREATE TRIGGER medications_Update
	AFTER UPDATE ON medications
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.mdate, patient=NEW.mpatient, summary=CONCAT(NEW.mdrug, ' ', NEW.mdosage), user=NEW.user, status=NEW.active WHERE module='medications' AND oid=NEW.id;
	END;
//

DELIMITER ;

