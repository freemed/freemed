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

CREATE TABLE IF NOT EXISTS `scheduler_status` (
	csstamp			TIMESTAMP (14) DEFAULT NOW(),
	cspatient		INT UNSIGNED NOT NULL DEFAULT 0,
	csappt			INT UNSIGNED NOT NULL DEFAULT 0,
	csnote			VARCHAR (250),
	csstatus		INT UNSIGNED NOT NULL DEFAULT 0,
	csuser			INT UNSIGNED NOT NULL,
	id			SERIAL,

	#	Define keys

	KEY			( cspatient, csstatus, csstamp ),
	FOREIGN KEY		( cspatient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS scheduler_status_Upgrade;
DELIMITER //
CREATE PROCEDURE scheduler_status_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER scheduler_status_Delete;
	DROP TRIGGER scheduler_status_Insert;
	DROP TRIGGER scheduler_status_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL scheduler_status_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER scheduler_status_Delete
	AFTER DELETE ON scheduler_status
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='scheduler_status' AND oid=OLD.id;
	END;
//

CREATE TRIGGER scheduler_status_Insert
	AFTER INSERT ON scheduler_status
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'scheduler_status', NEW.cspatient, NEW.id, NEW.csstamp, NEW.csnote );
	END;
//

CREATE TRIGGER scheduler_status_Update
	AFTER UPDATE ON scheduler_status
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.csstamp, patient=NEW.cspatient, summary=NEW.csnote WHERE module='scheduler_status' AND oid=NEW.id;
	END;
//

DELIMITER ;

