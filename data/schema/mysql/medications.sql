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

CREATE TABLE IF NOT EXISTS `medications` (
	mpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	mdate			DATE,
	mdrugs			VARCHAR (250),
	locked			INT UNSIGNED NOT NULL DEFAULT 0,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			BIGINT NOT NULL AUTO_INCREMENT

	#	Define keys

	, PRIMARY KEY		( id )
	, KEY			( mpatient, mdate )
	, FOREIGN KEY		( mpatient ) REFERENCES patient.id ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `medications_atomic` (
	mid			INT UNSIGNED NOT NULL,
	mdrug			VARCHAR (150),
	mdosage			VARCHAR (150),
	mroute			VARCHAR (150),
	mpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	mdate			DATE,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			BIGINT NOT NULL AUTO_INCREMENT

	#	Define keys

	, PRIMARY KEY		( id )
	, KEY			( mpatient, mdate )
	, FOREIGN KEY		( mpatient ) REFERENCES patient.id ON DELETE CASCADE
	, FOREIGN KEY		( mid ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS medications_Upgrade;
DELIMITER //
CREATE PROCEDURE medications_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER medications_Delete;
	DROP TRIGGER medications_atomic_Delete;
	DROP TRIGGER medications_Insert;
	DROP TRIGGER medications_atomic_Insert;
	DROP TRIGGER medications_Update;
	DROP TRIGGER medications_atomic_Update;

	#----- Upgrades
	ALTER TABLE medications ADD COLUMN mdrugs VARCHAR (250) AFTER mdate;
	ALTER TABLE medications ADD COLUMN locked INT UNSIGNED NOT NULL DEFAULT 0 AFTER mdrugs;
	ALTER TABLE medications ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER locked;
	ALTER TABLE medications ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
END
//
DELIMITER ;
CALL medications_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER medications_Delete
	AFTER DELETE ON medications
	FOR EACH ROW BEGIN
		DELETE FROM `medications_atomic` WHERE mid = OLD.id;
		DELETE FROM `patient_emr` WHERE module = 'medications' AND oid = OLD.id;
	END;
//

CREATE TRIGGER medications_atomic_Delete
	AFTER DELETE ON medications_atomic
	FOR EACH ROW BEGIN
		CALL medicationsReindex ( OLD.mid );
	END;
//

CREATE TRIGGER medications_Insert
	AFTER INSERT ON medications
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'medications', NEW.mpatient, NEW.id, NEW.mdate, NEW.mdrugs, NEW.user, NEW.active );
	END;
//

CREATE TRIGGER medications_atomic_Insert
	AFTER INSERT ON medications_atomic
	FOR EACH ROW BEGIN
		CALL medicationsReindex ( NEW.mid );
	END;
//

CREATE TRIGGER medications_Update
	AFTER UPDATE ON medications
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.mdate, patient=NEW.mpatient, summary=IFNULL(NEW.mdrugs,''), user=NEW.user, status=NEW.active WHERE module='medications' AND oid=NEW.id;
	END;
//

CREATE TRIGGER medications_atomic_Update
	AFTER UPDATE ON medications_atomic
	FOR EACH ROW BEGIN
		CALL medicationsReindex ( NEW.mid );
	END;
//

DELIMITER ;

DROP PROCEDURE IF EXISTS medicationsReindex;

DELIMITER //

CREATE PROCEDURE medicationsReindex ( IN thisId BIGINT UNSIGNED )
BEGIN
	DECLARE m VARCHAR (250);
	SELECT GROUP_CONCAT( mdrug ) INTO m FROM medications_atomic WHERE mid = thisId GROUP BY mid;
	UPDATE medications SET mdrugs = m WHERE id = thisId;
END
//

DELIMITER ;

