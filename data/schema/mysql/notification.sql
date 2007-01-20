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

CREATE TABLE IF NOT EXISTS `notification` (
	noriginal		DATE,
	ntarget			DATE,
	ndescrip		TEXT,
	nuser			INT UNSIGNED NOT NULL DEFAULT 0,
	nfor			INT UNSIGNED NOT NULL DEFAULT 0,
	npatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	id			SERIAL,

	#	Default key

	KEY			( nuser, npatient, nfor, ntarget ),
	FOREIGN KEY		( npatient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS notification_Upgrade;
DELIMITER //
CREATE PROCEDURE notification_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER notification_Delete;
	DROP TRIGGER notification_Insert;
	DROP TRIGGER notification_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL notification_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER notification_Delete
	AFTER DELETE ON notification
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='notification' AND oid=OLD.id;
	END;
//

CREATE TRIGGER notification_Insert
	AFTER INSERT ON notification
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'notification', NEW.npatient, NEW.id, NOW(), NEW.ndescrip );
	END;
//

CREATE TRIGGER notification_Update
	AFTER UPDATE ON notification
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NOW(), patient=NEW.npatient, summary=NEW.ndescrip WHERE module='notification' AND oid=NEW.id;
	END;
//

DELIMITER ;

