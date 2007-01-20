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

CREATE TABLE IF NOT EXISTS `rx` (
	rxdtadd			DATE NOT NULL,
	rxdtmod			DATE,
	rxphy			INT UNSIGNED NOT NULL,
	rxpatient		BIGINT UNSIGNED NOT NULL,
	rxdtfrom		DATE,
	rxdrug			VARCHAR (150) NOT NULL,
	rxform			VARCHAR (32),
	rxdosage		VARCHAR (128),
	rxquantity		REAL NOT NULL DEFAULT 0,
	rxsize			VARCHAR (32),
	rxunit			VARCHAR (32),
	rxinterval		ENUM( 'b.i.d.', 't.i.d.', 'q.i.d.', 'q. 3h', 'q. 4h', 'q. 5h', 'q. 6h', 'q. 8h', 'q.d.', 'h.s.', 'q.h.s.', 'q.A.M.', 'q.P.M.', 'a.c.', 'p.c.', 'p.r.n.' ),
	rxsubstitute		ENUM( 'may substitute', 'may not substitute' ),
	rxrefills		INT UNSIGNED NOT NULL DEFAULT 0,
	rxperrefill		INT UNSIGNED,
	rxorigrx		INT UNSIGNED,
	rxnote			TEXT,
	locked			INT UNSIGNED DEFAULT 0,
	id			SERIAL,

	#	Default key

	FOREIGN KEY		( rxpatient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS rx_Upgrade;
DELIMITER //
CREATE PROCEDURE rx_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER rx_Delete;
	DROP TRIGGER rx_Insert;
	DROP TRIGGER rx_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL rx_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER rx_Delete
	AFTER DELETE ON rx
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='rx' AND oid=OLD.id;
	END;
//

CREATE TRIGGER rx_Insert
	AFTER INSERT ON rx
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'rx', NEW.rxpatient, NEW.id, NOW(), NEW.rxdrug );
	END;
//

CREATE TRIGGER rx_Update
	AFTER UPDATE ON rx
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NOW(), patient=NEW.rxpatient, summary=NEW.rxdrug WHERE module='rx' AND oid=NEW.id;
	END;
//

DELIMITER ;

