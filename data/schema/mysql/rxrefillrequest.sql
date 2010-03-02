# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2010 FreeMED Software Foundation
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
SOURCE data/schema/mysql/rx.sql
SOURCE data/schema/mysql/physician.sql
SOURCE data/schema/mysql/systemnotification.sql
SOURCE data/schema/mysql/workflow_status.sql

CREATE TABLE IF NOT EXISTS `rxrefillrequest` (
	stamp			TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	patient			BIGINT UNSIGNED NOT NULL DEFAULT 0,
	provider		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	rxorig			TEXT,
	note			VARCHAR (250) NOT NULL DEFAULT '',
	approved		TIMESTAMP (14),
	locked			INT UNSIGNED NOT NULL DEFAULT 0,
	id			SERIAL

	#	Define keys
	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
	, FOREIGN KEY		( provider ) REFERENCES physician.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS rxrefillrequest_Upgrade;
DELIMITER //
CREATE PROCEDURE rxrefillrequest_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER rxrefillrequest_Delete;
	DROP TRIGGER rxrefillrequest_Insert;
	DROP TRIGGER rxrefillrequest_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL rxrefillrequest_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER rxrefillrequest_Delete
	AFTER DELETE ON rxrefillrequest
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='rxrefillrequest' AND oid=OLD.id;
		DELETE FROM `systemtaskinbox` WHERE module = 'rxrefillrequest' AND oid = OLD.id;
	END;
//

CREATE TRIGGER rxrefillrequest_Insert
	AFTER INSERT ON rxrefillrequest
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, locked, user, provider ) VALUES ( 'rxrefillrequest', NEW.patient, NEW.id, NEW.stamp, NEW.note, NEW.locked, NEW.user, NEW.provider );
		INSERT INTO `systemnotification` ( stamp, nuser, ntext, nmodule, npatient ) VALUES ( NEW.stamp, NEW.user, NEW.note, 'rxrefillrequest', NEW.patient );
		INSERT INTO `systemtaskinbox` ( user, patient, module, box, oid, summary ) VALUES ( NEW.user, NEW.patient, 'rxrefillrequest', 'rxrefillrequest', NEW.id, NEW.note );
	END;
//

CREATE TRIGGER rxrefillrequest_Update
	AFTER UPDATE ON rxrefillrequest
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.stamp, patient=NEW.patient, summary=NEW.note, locked=NEW.locked, user=NEW.user, provider=NEW.provider WHERE module='rxrefillrequest' AND oid=NEW.id;
		UPDATE `systemtaskinbox` SET user = NEW.user, patient = NEW.patient, oid = NEW.id, summary = NEW.note WHERE module = 'rxrefillrequest' AND oid = NEW.id;
	END;
//

DELIMITER ;

