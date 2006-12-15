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

CREATE TABLE IF NOT EXISTS `annotations` (
	atimestamp		TIMESTAMP (14) DEFAULT NOW(),
	apatient		INT UNSIGNED NOT NULL DEFAULT 0,
	amodule			VARCHAR (150) NOT NULL,
	aid			INT UNSIGNED NOT NULL,
	auser			INT UNSIGNED NOT NULL,
	annotation		TEXT,
	id			SERIAL,

	#	Define keys

	KEY			( apatient, amodule, aid ),
	FOREIGN KEY		( apatient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS annotations_Upgrade;
DELIMITER //
CREATE PROCEDURE annotations_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER annotations_Delete;
	DROP TRIGGER annotations_Insert;
	DROP TRIGGER annotations_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL annotations_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER annotations_Delete
	AFTER DELETE ON annotations
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='annotations' AND oid=OLD.id;
	END;
//

CREATE TRIGGER annotations_Insert
	AFTER INSERT ON annotations
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'annotations', NEW.apatient, NEW.id, NEW.atimestamp, NEW.annotation );
	END;
//

DELIMITER ;

