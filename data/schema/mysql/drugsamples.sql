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
SOURCE data/schema/mysql/drugsampleinv.sql

CREATE TABLE IF NOT EXISTS `drugsamples` (
	  drugsampleid		INT UNSIGNED NOT NULL
	, patientid		INT UNSIGNED NOT NULL
	, prescriber		INT UNSIGNED NOT NULL
	, deliveryform		VARCHAR (50)
	, amount		INT UNSIGNED NOT NULL DEFAULT 0
	, instructions		TEXT
	, stamp			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
	, locked		INT UNSIGNED NOT NULL DEFAULT 0
	, user			INT UNSIGNED NOT NULL DEFAULT 0
	, active		ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active'
	, id			SERIAL

	#	Define keys
	, FOREIGN KEY		( drugsampleid ) REFERENCES drugsampleinv ( id ) ON DELETE CASCADE
	, FOREIGN KEY		( patientid ) REFERENCES patient ( id )
);

DROP PROCEDURE IF EXISTS drugsamples_Upgrade;
DELIMITER //
CREATE PROCEDURE drugsamples_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER drugsamples_Delete;
	DROP TRIGGER drugsamples_PreInsert;
	DROP TRIGGER drugsamples_Insert;
	DROP TRIGGER drugsamples_Update;

	#----- Upgrades
	ALTER IGNORE TABLE drugsamples ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER locked;
	ALTER IGNORE TABLE drugsamples ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
END
//
DELIMITER ;
CALL drugsamples_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER drugsamples_Delete
	AFTER DELETE ON drugsamples
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='drugsamples' AND oid=OLD.id;
	END;
//

CREATE TRIGGER drugsamples_PreInsert
	BEFORE INSERT ON drugsamples
	FOR EACH ROW BEGIN
		# Update sample count in drug inventory
		UPDATE drugsampleinv SET amount = amount - NEW.amount
			, samplecountremain = samplecountremain - NEW.amount
			WHERE id = NEW.drugsampleid;
	END;
//

CREATE TRIGGER drugsamples_Insert
	AFTER INSERT ON drugsamples
	FOR EACH ROW BEGIN
		DECLARE d VARCHAR (250);
		SELECT description INTO d FROM drugsampleinv WHERE id=NEW.drugsampleid;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, locked, user, status, provider ) VALUES ( 'drugsamples', NEW.patientid, NEW.id, NEW.stamp, d, NEW.locked, NEW.user, NEW.active, NEW.prescriber );
	END;
//

CREATE TRIGGER drugsamples_Update
	AFTER UPDATE ON drugsamples
	FOR EACH ROW BEGIN
		DECLARE d VARCHAR (250);
		SELECT description INTO d FROM drugsampleinv WHERE id=NEW.drugsampleid;
		UPDATE `patient_emr` SET stamp=NEW.stamp, patient=NEW.patientid, summary=d, locked=NEW.locked, user=NEW.user, status=NEW.active, provider=NEW.prescriber WHERE module='drugsamples' AND oid=NEW.id;
	END;
//

DELIMITER ;

