# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2006 FreeMED Software Foundation
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

CREATE TABLE IF NOT EXISTS `patletter` (
	letterdt		DATE,
	lettereoc		VARCHAR (250),
	letterfrom		INT UNSIGNED NOT NULL DEFAULT 0,
	lettertext		TEXT,
	lettersent		INT UNSIGNED NOT NULL DEFAULT 0,
	letterpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	locked			INT UNSIGNED NOT NULL DEFAULT 0,
	id			SERIAL,

	#	Define keys

	KEY			( letterpatient, lettersent, letterfrom, lettereoc ),
	FOREIGN KEY		( letterpatient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS patletter_Upgrade;
DELIMITER //
CREATE PROCEDURE patletter_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER patletter_Delete;
	DROP TRIGGER patletter_Insert;
	DROP TRIGGER patletter_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL patletter_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER patletter_Delete
	AFTER DELETE ON patletter
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='patletter' AND oid=OLD.id;
	END;
//

CREATE TRIGGER patletter_Insert
	AFTER INSERT ON patletter
	FOR EACH ROW BEGIN
		DECLARE p VARCHAR(250);
		SELECT CONCAT(phyfname, ' ', phylname) INTO p FROM physician WHERE id=NEW.letterfrom;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'patletter', NEW.letterpatient, NEW.id, NEW.letterdt, p );
	END;
//

CREATE TRIGGER patletter_Update
	AFTER UPDATE ON patletter
	FOR EACH ROW BEGIN
		DECLARE p VARCHAR(250);
		SELECT CONCAT(phyfname, ' ', phylname) INTO p FROM physician WHERE id=NEW.letterfrom;
		UPDATE `patient_emr` SET stamp=NEW.letterdt, patient=NEW.letterpatient, summary=p WHERE module='patletter' AND oid=NEW.id;
	END;
//

DELIMITER ;

