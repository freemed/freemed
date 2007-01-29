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
SOURCE data/schema/mysql/procrec.sql

CREATE TABLE IF NOT EXISTS `payrec` (
	payrecdtadd		DATE,
	payrecdtmod		DATE,
	payrecpatient		BIGINT UNSIGNED NOT NULL,
	payrecdt		DATE,
	payreccat		INT UNSIGNED,
	payrecproc		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	payrecsource		INT UNSIGNED,
	payreclink		INT UNSIGNED,
	payrectype		INT UNSIGNED,
	payrecnum		VARCHAR (100),
	payrecamt		REAL,
	payrecdescrip		TEXT,
	payreclock		ENUM ( 'unlocked', 'locked' ),
	id			SERIAL,

	#	Define keys
	KEY			( payrecpatient, payrecproc ),
	FOREIGN KEY		( payrecpatient ) REFERENCES patient ( id ) ON DELETE CASCADE,
	FOREIGN KEY		( payrecproc ) REFERENCES procrec ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS payrec_Upgrade;
DELIMITER //
CREATE PROCEDURE payrec_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER payrec_Delete;
	DROP TRIGGER payrec_Insert;
	DROP TRIGGER payrec_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL payrec_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER payrec_Delete
	AFTER DELETE ON payrec
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='payrec' AND oid=OLD.id;
	END;
//

CREATE TRIGGER payrec_Insert
	AFTER INSERT ON payrec
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, locked ) VALUES ( 'payrec', NEW.payrecpatient, NEW.id, NOW(), NEW.payrecdescrip, NEW.locked );
	END;
//

CREATE TRIGGER payrec_Update
	AFTER UPDATE ON payrec
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NOW(), patient=NEW.payrecpatient, summary=NEW.payrecdescrip, locked=NEW.locked WHERE module='payrec' AND oid=NEW.id;
	END;
//

DELIMITER ;

