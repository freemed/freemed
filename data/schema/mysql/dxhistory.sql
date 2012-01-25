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
SOURCE data/schema/mysql/physician.sql
SOURCE data/schema/mysql/icd9.sql

CREATE TABLE IF NOT EXISTS `dxhistory` (
	  patient		BIGINT UNSIGNED NOT NULL DEFAULT 0
	, provider		BIGINT UNSIGNED NOT NULL DEFAULT 0
	, procrec		BIGINT UNSIGNED NOT NULL DEFAULT 0
	, stamp			TIMESTAMP (14) DEFAULT NOW()
	, dx			BIGINT UNSIGNED NOT NULL DEFAULT 0
	, dxset			ENUM ( '9', '10' ) NOT NULL DEFAULT '9'
	, id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
	, PRIMARY KEY		( id )

	#	Define keys

	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
	, FOREIGN KEY		( procrec ) REFERENCES procrec.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS dxhistory_Upgrade;
DELIMITER //
CREATE PROCEDURE dxhistory_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER dxhistory_Delete;
	DROP TRIGGER dxhistory_Insert;
	DROP TRIGGER dxhistory_Update;

	#----- Upgrades
	ALTER IGNORE TABLE dxhistory ADD COLUMN dxset ENUM ( '9', '10' ) NOT NULL DEFAULT '9' AFTER dx;
END
//
DELIMITER ;
CALL dxhistory_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER dxhistory_Delete
	AFTER DELETE ON dxhistory
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='dxhistory' AND oid=OLD.id;
	END;
//

CREATE TRIGGER dxhistory_Insert
	AFTER INSERT ON dxhistory
	FOR EACH ROW BEGIN
		DECLARE c VARCHAR(250);
		SELECT CONCAT( icd9code, ' (', icd9descrip, ')' ) INTO c FROM icd9 WHERE id=NEW.dx;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'dxhistory', NEW.patient, NEW.id, NEW.stamp, c );
	END;
//

CREATE TRIGGER dxhistory_Update
	AFTER UPDATE ON dxhistory
	FOR EACH ROW BEGIN
		DECLARE c VARCHAR(250);
		SELECT CONCAT( icd9code, ' (', icd9descrip, ')' ) INTO c FROM icd9 WHERE id=NEW.dx;
		UPDATE `patient_emr` SET stamp=NEW.stamp, patient=NEW.patient, summary=c WHERE module='dxhistory' AND oid=NEW.id;
	END;
//

DELIMITER ;

