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

CREATE TABLE IF NOT EXISTS `patienttag` (
	tag			VARCHAR (100) NOT NULL,
	patient			BIGINT UNSIGNED NOT NULL DEFAULT 0,
	user			BIGINT UNSIGNED NOT NULL DEFAULT 0,
	datecreate		TIMESTAMP (14) DEFAULT NOW(),
	dateexpire		TIMESTAMP (14),
	id			SERIAL,

	#	Define keys
	KEY			( patient, tag )
	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

#	Define aggregation table for set operations
CREATE TABLE IF NOT EXISTS `patienttaglookup` (
	lastupdated		TIMESTAMP(14) NOT NULL,
	patient			BIGINT UNSIGNED NOT NULL DEFAULT 0,
	tags			TEXT,
	PRIMARY KEY		( patient )
);

#	Define stored procedures

-- Simple tag search procedure --
DROP PROCEDURE IF EXISTS patientTagSearchSimple;
DELIMITER //
CREATE PROCEDURE patientTagSearchSimple ( IN param VARCHAR(250) )
BEGIN
	SELECT
		p.id AS patient_record,
		p.ptid AS patient_id,
		MAX(c.caldateof) AS last_seen,
		p.ptlname AS last_name,
		p.ptfname AS first_name,
		p.ptmname AS middle_name,
		p.ptdob AS date_of_birth
	FROM
		patient p
	LEFT OUTER JOIN patienttag t ON p.id=t.patient
	LEFT OUTER JOIN scheduler c ON p.id=c.calpatient
	WHERE
		( t.dateexpire = 0 OR t.dateexpire > NOW() ) AND
		( t.tag = param )
	GROUP BY p.id;
END//
DELIMITER ;

-- Update aggregation table
DROP PROCEDURE IF EXISTS patientTagUpdateLookup;
DELIMITER //
CREATE PROCEDURE patientTagUpdateLookup ( IN pt BIGINT UNSIGNED )
BEGIN
	DECLARE cFound INT UNSIGNED;
	DECLARE tAll TEXT;

	#	Get everything
	SELECT
		GROUP_CONCAT( t.tag ) INTO tAll
	FROM patienttag t
	WHERE
		( t.dateexpire = 0 OR t.dateexpire > NOW() ) AND
		t.patient = pt
	GROUP BY t.patient;

	#	Check for update or insert
	SELECT COUNT(*) INTO cFound FROM patienttaglookup WHERE patient=pt;
	IF cFound < 1 THEN
		INSERT INTO patienttaglookup (
			lastupdated,
			patient,
			tags
		) VALUES (
			NOW(),
			pt,
			tAll
		);
	ELSE
		UPDATE patienttaglookup SET lastupdated=NOW(), tags=tAll WHERE patient=pt;
	END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS patienttag_Upgrade;
DELIMITER //
CREATE PROCEDURE patienttag_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER patienttag_Delete;
	DROP TRIGGER patienttag_Insert;
	DROP TRIGGER patienttag_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL patienttag_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER patienttag_Delete
	AFTER DELETE ON patienttag
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='patienttag' AND oid=OLD.id;
		CALL patientTagUpdateLookup( OLD.patient );
	END;
//

CREATE TRIGGER patienttag_Insert
	AFTER INSERT ON patienttag
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'patienttag', NEW.patient, NEW.id, NEW.datecreate, NEW.tag, NEW.user, 'active' );
		CALL patientTagUpdateLookup( NEW.patient );
	END;
//

CREATE TRIGGER patienttag_Update
	AFTER UPDATE ON patienttag
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='patienttag' AND oid=OLD.id;
		CALL patientTagUpdateLookup( NEW.patient );
	END;
//

DELIMITER ;

