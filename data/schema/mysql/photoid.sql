# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2009 FreeMED Software Foundation
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

CREATE TABLE IF NOT EXISTS `photoid` (
	p_stamp			TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	p_patient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	p_description		VARCHAR (250),
	p_filename		VARCHAR (250),
	p_user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			SERIAL

	#	Define keys

	, FOREIGN KEY		( p_patient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS photoid_Upgrade;
DELIMITER //
CREATE PROCEDURE photoid_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER photoid_Delete;
	DROP TRIGGER photoid_Insert;
	DROP TRIGGER photoid_Update;

	#----- Upgrades
	CALL FreeMED_Module_GetVersion( 'photoid', @V );

	ALTER IGNORE TABLE photoid ADD COLUMN p_filename VARCHAR(250) AFTER p_description;

	CALL FreeMED_Module_UpdateVersion( 'photoid', 1 );
END
//
DELIMITER ;
CALL photoid_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER photoid_Delete
	AFTER DELETE ON photoid
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='photoid' AND oid=OLD.id;
	END;
//

CREATE TRIGGER photoid_Insert
	AFTER INSERT ON photoid
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'photoid', NEW.p_patient, NEW.id, NEW.p_stamp, IF(ISNULL(NEW.p_description), NEW.p_stamp, NEW.p_description), NEW.p_user, NEW.active );
	END;
//

CREATE TRIGGER photoid_Update
	AFTER UPDATE ON photoid
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.p_stamp, patient=NEW.p_patient, summary=NEW.p_description, user=NEW.p_user, status=NEW.active WHERE module='photoid' AND oid=NEW.id;
	END;
//

DELIMITER ;

DROP PROCEDURE IF EXISTS photoid_GetLatest;
DELIMITER //

CREATE PROCEDURE photoid_GetLatest ( IN patient BIGINT(20) UNSIGNED )
BEGIN
	SELECT p_filename FROM photoid WHERE p_patient = patient ORDER BY p_stamp DESC LIMIT 1;
END;
//

DELIMITER ;
