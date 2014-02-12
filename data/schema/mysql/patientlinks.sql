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

CREATE TABLE IF NOT EXISTS `patientlinks` (
	  stamp			TIMESTAMP (14) NOT NULL DEFAULT CURRENT_TIMESTAMP
	, srcpatient		BIGINT (20) NOT NULL DEFAULT 0
	, destpatient		BIGINT (20) NOT NULL DEFAULT 0
	, linktype		VARCHAR (50) NOT NULL DEFAULT ''
	, linkdetails		TEXT
	, user			INT UNSIGNED NOT NULL DEFAULT 0
	, active		ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active'
	, id			SERIAL

	#	Define keys

	, KEY			( srcpatient, linktype )
	, KEY			( destpatient, linktype )
	, FOREIGN KEY		( srcpatient ) REFERENCES patient.id ON DELETE CASCADE
	, FOREIGN KEY		( destpatient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS patientlinks_Upgrade;
DELIMITER //
CREATE PROCEDURE patientlinks_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER patientlinks_Delete;
	DROP TRIGGER patientlinks_Insert;
	DROP TRIGGER patientlinks_Update;

	#----- Upgrades
	CALL FreeMED_Module_GetVersion( 'patientlinks', @V );

	CALL FreeMED_Module_UpdateVersion( 'patientlinks', 1 );
END
//
DELIMITER ;
CALL patientlinks_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER patientlinks_Delete
	AFTER DELETE ON patientlinks
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='patientlinks' AND oid=OLD.id;
	END;
//

CREATE TRIGGER patientlinks_Insert
	AFTER INSERT ON patientlinks
	FOR EACH ROW BEGIN
		DECLARE dP VARCHAR(250);
		SELECT CONCAT( ptlname, ', ', ptfname, ' ', ptmname, ' (', ptid, ')' ) INTO dP FROM patient WHERE id=NEW.destpatient;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'patientlinks', NEW.srcpatient, NEW.id, NEW.stamp, CONCAT( NEW.linktype, ' ', dP ), NEW.user, NEW.active );
	END;
//

CREATE TRIGGER patientlinks_Update
	AFTER UPDATE ON patientlinks
	FOR EACH ROW BEGIN
		DECLARE dP VARCHAR(250);
		SELECT CONCAT( ptlname, ', ', ptfname, ' ', ptmname, ' (', ptid, ')' ) INTO dP FROM patient WHERE id=NEW.destpatient;
		UPDATE `patient_emr` SET stamp=NEW.stamp, patient=NEW.srcpatient, summary=CONCAT( NEW.linktype, ' ', dP), user=NEW.user, status=NEW.active WHERE module='patientlinks' AND oid=NEW.id;
	END;
//

DELIMITER ;

