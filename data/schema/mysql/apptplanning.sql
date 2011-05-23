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

CREATE TABLE IF NOT EXISTS `apptplanning` (
	appatient		BIGINT UNSIGNED NOT NULL,
	apdatecreated		TIMESTAMP(14) NOT NULL DEFAULT NOW(),
	apdatetarget		DATE NOT NULL,
	appriority		INT NOT NULL DEFAULT 0,
	apreason		VARCHAR (150),
	apschedulerlink		INT UNSIGNED NOT NULL DEFAULT 0,
	approvider		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	apnotifiedon		TIMESTAMP (14),
	user			INT UNSIGNED NOT NULL,
	id			SERIAL,

	#	Define keys
	KEY			( appatient, apdatetarget ),
	FOREIGN KEY		( appatient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS apptplanning_Upgrade;
DELIMITER //
CREATE PROCEDURE apptplanning_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER apptplanning_Delete;
	DROP TRIGGER apptplanning_Insert;
	DROP TRIGGER apptplanning_Update;

	#----- Upgrades
	ALTER TABLE apptplanning ADD COLUMN appriority INT NOT NULL DEFAULT 0 AFTER apdatetarget;
END
//
DELIMITER ;
CALL apptplanning_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER apptplanning_Delete
	AFTER DELETE ON apptplanning
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='apptplanning' AND oid=OLD.id;
	END;
//

CREATE TRIGGER apptplanning_Insert
	AFTER INSERT ON apptplanning
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, provider, user ) VALUES ( 'apptplanning', NEW.appatient, NEW.id, NEW.apdatecreated, NEW.apreason, NEW.approvider, NEW.user );
	END;
//

CREATE TRIGGER apptplanning_Update
	AFTER UPDATE ON apptplanning
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.apdatecreated, patient=NEW.appatient, summary=NEW.apreason, provider=NEW.approvider, user=NEW.user WHERE module='apptplanning' AND oid=NEW.id;
	END;
//

DELIMITER ;

