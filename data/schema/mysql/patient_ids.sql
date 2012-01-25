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

CREATE TABLE IF NOT EXISTS `patient_ids` (
	  patient		BIGINT UNSIGNED NOT NULL DEFAULT 0
	, foreign_id		VARCHAR (50) NOT NULL
	, facility		INT UNSIGNED
	, practice		INT UNSIGNED
	, stamp			TIMESTAMP NOT NULL DEFAULT NOW()
	, user			INT UNSIGNED NOT NULL DEFAULT 0
	, active		ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active'
	, id			SERIAL

	# Define keys

	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS patient_ids_Upgrade;
DELIMITER //
CREATE PROCEDURE patient_ids_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER patient_ids_Delete;
	DROP TRIGGER patient_ids_Insert;
	DROP TRIGGER patient_ids_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL patient_ids_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER patient_ids_Delete
	AFTER DELETE ON patient_ids
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='patient_ids' AND oid=OLD.id;
	END;
//

CREATE TRIGGER patient_ids_Insert
	AFTER INSERT ON patient_ids
	FOR EACH ROW BEGIN
		DECLARE fT VARCHAR( 100 );
		DECLARE pT VARCHAR( 100 );
		DECLARE t VARCHAR( 100 );

		SELECT psrname INTO fT FROM facility WHERE id=NEW.facility;
		SELECT pracname INTO pT FROM practice WHERE id=NEW.practice;

		SELECT IFNULL(fT, pT) INTO t;

		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'patient_ids', NEW.patient, NEW.id, NEW.stamp, CONCAT(t, ': ', NEW.foreign_id ), NEW.user, NEW.active );
	END;
//

CREATE TRIGGER patient_ids_Update
	AFTER UPDATE ON patient_ids
	FOR EACH ROW BEGIN
		DECLARE fT VARCHAR( 100 );
		DECLARE pT VARCHAR( 100 );
		DECLARE t VARCHAR( 100 );

		SELECT psrname INTO fT FROM facility WHERE id=NEW.facility;
		SELECT pracname INTO pT FROM practice WHERE id=NEW.practice;

		SELECT IFNULL(fT, pT) INTO t;

		UPDATE `patient_emr` SET stamp=NEW.stamp, patient=NEW.patient, summary=CONCAT(t, ': ', NEW.foreign_id ), user=NEW.user, status=NEW.active WHERE module='patient_ids' AND oid=NEW.id;
	END;
//

DELIMITER ;

