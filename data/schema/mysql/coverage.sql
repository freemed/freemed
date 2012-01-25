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

CREATE TABLE IF NOT EXISTS `coverage` (
	covdtadd		DATE,
	covdtmod		DATE,
	covpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	coveffdt		TEXT,
	covinsco		INT UNSIGNED,
	covpatinsno		VARCHAR (50) NOT NULL,
	covpatgrpno		VARCHAR (50),
	covtype			INT UNSIGNED,
	covstatus		INT UNSIGNED DEFAULT 0,
	covrel			CHAR (2) NOT NULL DEFAULT 'S',
	covlname		VARCHAR (50),
	covfname		VARCHAR (50),
	covmname		CHAR (1),
	covaddr1		VARCHAR (25),
	covaddr2		VARCHAR (25),
	covcity			VARCHAR (25),
	covstate		CHAR (3),
	covzip			VARCHAR (10),
	covdob			DATE,
	covsex			ENUM ( 'm', 'f', 't' ),
	covssn			CHAR (9),
	covinstp		INT UNSIGNED,
	covprovasgn		INT UNSIGNED,
	covbenasgn		INT UNSIGNED,
	covrelinfo		INT UNSIGNED,
	covrelinfodt		DATE,
	covplanname		VARCHAR (33),
	covisassigning		INT UNSIGNED NOT NULL DEFAULT 1,
	covschool		VARCHAR (50),
	covemployer		VARCHAR (50),
	covcopay		REAL,
	covdeduct		REAL,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	id			SERIAL

	#	Define keys

	, KEY			( covpatient, covinsco, covrel )
	, FOREIGN KEY		( covpatient ) REFERENCES patient.id ON DELETE CASCADE
	, KEY			( covpatinsno )
);

DROP PROCEDURE IF EXISTS coverage_Upgrade;
DELIMITER //
CREATE PROCEDURE coverage_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER coverage_Delete;
	DROP TRIGGER coverage_Insert;
	DROP TRIGGER coverage_Update;

	#----- Upgrades
	ALTER IGNORE TABLE coverage ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER covdeduct;
	ALTER IGNORE TABLE coverage CHANGE COLUMN covpatinsno covpatinsno VARCHAR (50) NOT NULL;
	ALTER IGNORE TABLE coverage ADD KEY ( covpatinsno );
END//
DELIMITER ;
CALL coverage_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER coverage_Delete
	AFTER DELETE ON coverage
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='coverage' AND oid=OLD.id;
	END;
//

CREATE TRIGGER coverage_Insert
	AFTER INSERT ON coverage
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary,user, status ) VALUES ( 'coverage', NEW.covpatient, NEW.id, NEW.covdtadd, CONCAT( NEW.covplanname, '[', NEW.covrel, ']' ), NEW.user, IF( NEW.covstatus = 1, 'inactive', 'active' ) );
	END;
//

CREATE TRIGGER coverage_Update
	AFTER UPDATE ON coverage
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.covdtmod, patient=NEW.covpatient, summary=CONCAT( NEW.covplanname, '[', NEW.covrel, ']' ), user=NEW.user, status=IF( NEW.covstatus=1, 'inactive', 'active' ) WHERE module='coverage' AND oid=NEW.id;
	END;
//

DELIMITER ;

