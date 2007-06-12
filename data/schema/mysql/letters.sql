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

CREATE TABLE IF NOT EXISTS `letters` (
	letterdt		DATE,
	lettereoc		VARCHAR (250) NOT NULL DEFAULT '',
	letterfrom		INT UNSIGNED NOT NULL DEFAULT 0,
	letterto		INT UNSIGNED NOT NULL DEFAULT 0,
	lettercc		BLOB,
	letterenc		BLOB,
	lettersubject		VARCHAR (250),
	lettertext		TEXT,
	lettersent		INT UNSIGNED NOT NULL DEFAULT 0,
	letterpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	lettertypist		VARCHAR (50),
	locked			INT UNSIGNED NOT NULL DEFAULT 0,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			SERIAL,

	#	Define keys

	KEY			( letterpatient, lettersent, letterfrom, lettereoc ),
	FOREIGN KEY		( letterpatient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS letters_Upgrade;
DELIMITER //
CREATE PROCEDURE letters_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER letters_Delete;
	DROP TRIGGER letters_Insert;
	DROP TRIGGER letters_Update;

	#----- Upgrades
	CALL FreeMED_Module_GetVersion( 'letters', @V );

	# Version 1
	IF @V < 1 THEN
		ALTER IGNORE TABLE letters ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER locked;
		ALTER IGNORE TABLE letters ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
		ALTER IGNORE TABLE letters ADD COLUMN lettersubject VARCHAR (250) AFTER letterenc;
	END IF;

	CALL FreeMED_Module_UpdateVersion( 'letters', 1 );
END
//
DELIMITER ;
CALL letters_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER letters_Delete
	AFTER DELETE ON letters
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='letters' AND oid=OLD.id;
	END;
//

CREATE TRIGGER letters_Insert
	AFTER INSERT ON letters
	FOR EACH ROW BEGIN
		DECLARE p VARCHAR(250);
		SELECT CONCAT(phyfname, ' ', phylname) INTO p FROM physician WHERE id=NEW.letterto;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, locked, user, status ) VALUES ( 'letters', NEW.letterpatient, NEW.id, NEW.letterdt, p, NEW.locked, NEW.user, NEW.active );
	END;
//

CREATE TRIGGER letters_Update
	AFTER UPDATE ON letters
	FOR EACH ROW BEGIN
		DECLARE p VARCHAR(250);
		SELECT CONCAT(phyfname, ' ', phylname) INTO p FROM physician WHERE id=NEW.letterto;
		UPDATE `patient_emr` SET stamp=NEW.letterdt, patient=NEW.letterpatient, summary=p, locked=NEW.locked, user=NEW.user, status=NEW.active WHERE module='letters' AND oid=NEW.id;
	END;
//

DELIMITER ;

