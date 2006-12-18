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

CREATE TABLE IF NOT EXISTS `translation` (
	ttimestamp		TIMESTAMP (14) DEFAULT NOW(),
	tpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	tmodule			VARCHAR (150) NOT NULL,
	tid			INT UNSIGNED NOT NULL,
	tuser			INT UNSIGNED NOT NULL,
	tlanguage		CHAR(10) NOT NULL,
	tcomment		TEXT,
	id			SERIAL,

	#	Define keys

	KEY			( tpatient, tmodule, tid, tlanguage ),
	FOREIGN KEY		( tpatient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS translation_Upgrade;
DELIMITER //
CREATE PROCEDURE translation_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER translation_Delete;
	DROP TRIGGER translation_Insert;
	DROP TRIGGER translation_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL translation_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER translation_Delete
	AFTER DELETE ON translation
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='translation' AND oid=OLD.id;
	END;
//

CREATE TRIGGER translation_Insert
	AFTER INSERT ON translation
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'translation', NEW.tpatient, NEW.id, NEW.ttimestamp, NEW.tlanguage );
	END;
//

CREATE TRIGGER translation_Update
	AFTER UPDATE ON translation
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.ttimestamp, patient=NEW.tpatient, summary=NEW.tlanguage WHERE module='translation' AND oid=NEW.id;
	END;
//

DELIMITER ;

