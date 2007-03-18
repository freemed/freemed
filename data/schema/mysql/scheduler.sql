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

CREATE TABLE IF NOT EXISTS `scheduler` (
	caldateof		DATE,
	calcreated		TIMESTAMP (16),
	calmodified		TIMESTAMP (16),
	caltype			ENUM( 'temp', 'pat', 'block' ) NOT NULL DEFAULT 'pat',
	calhour			INT UNSIGNED,
	calminute		INT UNSIGNED,
	calduration		INT UNSIGNED,
	calfacility		INT UNSIGNED,
	calroom			INT UNSIGNED,
	calphysician		INT UNSIGNED,
	calpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	calcptcode		INT UNSIGNED,
	calstatus		ENUM ( 'scheduled', 'confirmed', 'attended', 'cancelled', 'noshow', 'tenative' ) NOT NULL DEFAULT 'scheduled',
	calprenote		VARCHAR (250),
	calpostnote		TEXT,
	calmark			INT UNSIGNED NOT NULL DEFAULT 0,
	calgroupid		INT UNSIGNED NOT NULL DEFAULT 0,
	calrecurnote		VARCHAR (100),
	calrecurid		INT UNSIGNED NOT NULL DEFAULT 0,
	calappttemplate		INT UNSIGNED NOT NULL DEFAULT 0,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	id			SERIAL,

	# Define keys

	KEY			( caldateof, calhour, calminute )
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS scheduler_Upgrade;
DELIMITER //
CREATE PROCEDURE scheduler_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER scheduler_Delete;
	DROP TRIGGER scheduler_Insert;
	DROP TRIGGER scheduler_Update;

	#----- Upgrades

	#	Version 0.6.3
	ALTER IGNORE TABLE scheduler ADD COLUMN calgroupid INT UNSIGNED NOT NULL DEFAULT 0 AFTER calmark;
	ALTER IGNORE TABLE scheduler ADD COLUMN calrecurnote VARCHAR (100) AFTER calgroupid;
	ALTER IGNORE TABLE scheduler ADD COLUMN calrecurid INT UNSIGNED NOT NULL DEFAULT 0 AFTER calrecurnote;
	ALTER IGNORE TABLE scheduler CHANGE COLUMN caltype caltype ENUM ( 'temp', 'pat', 'block' );
	ALTER IGNORE TABLE scheduler CHANGE COLUMN calstatus caltype ENUM ( 'scheduled', 'confirmed', 'attended', 'cancelled', 'noshow', 'tenative' ) NOT NULL DEFAULT 'scheduled';

	#	Version 0.6.3.1
	ALTER IGNORE TABLE scheduler CHANGE COLUMN calprenote calprenote VARCHAR (250);

	#	Version 0.6.5
	ALTER IGNORE TABLE scheduler ADD COLUMN calcreated TIMESTAMP (16) AFTER caldateof;
	ALTER IGNORE TABLE scheduler ADD COLUMN calmodified TIMESTAMP (16) AFTER calcreated;

	#	Version 0.6.6
	ALTER IGNORE TABLE scheduler ADD COLUMN calappttemplate INT UNSIGNED NOT NULL DEFAULT 0 AFTER calrecurid;

	ALTER IGNORE TABLE scheduler ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER calappttemplate;
END
//
DELIMITER ;
CALL scheduler_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER scheduler_Delete
	AFTER DELETE ON scheduler
	FOR EACH ROW BEGIN
		IF OLD.caltype = 'pat' THEN
			DELETE FROM `patient_emr` WHERE module='scheduler' AND oid=OLD.id;
		END IF;
	END;
//

CREATE TRIGGER scheduler_Insert
	AFTER INSERT ON scheduler
	FOR EACH ROW BEGIN
		IF NEW.caltype = 'pat' THEN
			INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user ) VALUES ( 'scheduler', NEW.calpatient, NEW.id, NEW.caldateof, NEW.calprenote, NEW.user );
		END IF;
	END;
//

CREATE TRIGGER scheduler_Update
	AFTER UPDATE ON scheduler
	FOR EACH ROW BEGIN
		IF NEW.caltype = 'pat' THEN
			UPDATE `patient_emr` SET stamp=NEW.caldateof, patient=NEW.calpatient, summary=NEW.calprenote, user=NEW.user WHERE module='scheduler' AND oid=NEW.id;
		END IF;
	END;
//

DELIMITER ;

