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

SOURCE data/schema/mysql/systemnotification.sql

CREATE TABLE IF NOT EXISTS `clinicregistration` (
	  dateof			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
	, processed			BOOL NOT NULL DEFAULT FALSE
	, user				INT UNSIGNED NOT NULL DEFAULT 0
	, processeduser			INT UNSIGNED NOT NULL DEFAULT 0
	, facility			INT UNSIGNED NOT NULL DEFAULT 0
	, archive			INT UNSIGNED NOT NULL DEFAULT 0
	, patient			INT UNSIGNED NOT NULL DEFAULT 0

	, lastname			VARCHAR (50)
	, lastname2			VARCHAR (50)
	, firstname			VARCHAR (50)
	, dob				DATE
	, gender			ENUM ( 'm', 'f' ) DEFAULT NULL
	, age				INT UNSIGNED DEFAULT 0
	, notes				TEXT

	, id				SERIAL

	#	Define keys
	, KEY				( processed )
	, KEY				( dateof )
	, FOREIGN KEY			( user ) REFERENCES user.id
);

DROP PROCEDURE IF EXISTS clinicregistration_Upgrade;
DELIMITER //
CREATE PROCEDURE clinicregistration_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER clinicregistration_Delete;
	DROP TRIGGER clinicregistration_Insert;
	DROP TRIGGER clinicregistration_PreInsert;
	DROP TRIGGER clinicregistration_Update;

	#----- Upgrades
	ALTER IGNORE TABLE clinicregistration ADD COLUMN processeduser INT UNSIGNED NOT NULL DEFAULT 0 AFTER user;
END
//
DELIMITER ;
CALL clinicregistration_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER clinicregistration_Delete
	AFTER DELETE ON clinicregistration
	FOR EACH ROW BEGIN
	END;
//

CREATE TRIGGER clinicregistration_Update
	AFTER UPDATE ON clinicregistration
	FOR EACH ROW BEGIN
	END;
//

CREATE TRIGGER clinicregistration_PreInsert
	BEFORE INSERT ON clinicregistration
	FOR EACH ROW BEGIN
		# Determine age before full insert
		IF NEW.age = 0 THEN
			SET NEW.age = CAST( ( TO_DAYS(NOW()) - TO_DAYS( NEW.dob ) ) / 365 AS UNSIGNED );
		ELSEIF NEW.dob = NULL THEN
			SET NEW.dob = DATE_SUB(NOW(), INTERVAL NEW.age YEAR);
		END IF;
	END;
//

CREATE TRIGGER clinicregistration_Insert
	AFTER INSERT ON clinicregistration
	FOR EACH ROW BEGIN
		INSERT INTO systemnotification ( stamp, nuser, ntext, nmodule, npatient, naction ) VALUES ( NEW.dateof, 0, 'Registration', 'clinicregistration', 0, 'NEW' );
	END;
//
DELIMITER ;

DROP PROCEDURE IF EXISTS clinicregistration_MigrateToPatient;
DELIMITER //
CREATE PROCEDURE clinicregistration_MigrateToPatient ( IN userId INT UNSIGNED, IN clinicregid INT UNSIGNED, IN patientId INT UNSIGNED )
BEGIN
	UPDATE clinicregistration SET patient = patientId, processed = TRUE, processeduser = userId WHERE id = clinicregid;
END
//

DELIMITER ;

DROP PROCEDURE IF EXISTS clinicregistration_CreatePatient;
DELIMITER //
CREATE PROCEDURE clinicregistration_CreatePatient ( IN userId INT UNSIGNED, IN clinicregid INT UNSIGNED )
BEGIN
	DECLARE newPatientId INT UNSIGNED;

	INSERT INTO patient (
		  ptlname
		, ptfname
		, ptdob
		, ptsex
	) SELECT
		  CONCAT(lastname, IF(ISNULL(lastname2),'',CONCAT(' ', lastname2)))
		, firstname
		, dob
		, gender
	FROM clinicregistration WHERE id = clinicregid;

	SELECT LAST_INSERT_ID() INTO newPatientId;

	UPDATE clinicregistration SET patient = newPatientId, processed = TRUE, processeduser = userId WHERE id = clinicregid;

	# Send back new patient id
	SELECT newPatientId;
END
//

DELIMITER ;

