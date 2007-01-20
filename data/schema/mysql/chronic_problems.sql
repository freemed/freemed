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

CREATE TABLE IF NOT EXISTS `chronic_problems` (
	pdate			DATE NOT NULL,
	problem			VARCHAR (250) NOT NULL DEFAULT '',
	ppatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	id			SERIAL,

	#	Define keys

	KEY			( ppatient, pdate ),
	FOREIGN KEY		( ppatient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS chronic_problems_Upgrade;
DELIMITER //
CREATE PROCEDURE chronic_problems_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER chronic_problems_Delete;
	DROP TRIGGER chronic_problems_Insert;
	DROP TRIGGER chronic_problems_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL chronic_problems_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER chronic_problems_Delete
	AFTER DELETE ON chronic_problems
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='chronic_problems' AND oid=OLD.id;
	END;
//

CREATE TRIGGER chronic_problems_Insert
	AFTER INSERT ON chronic_problems
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'chronic_problems', NEW.ppatient, NEW.id, NEW.pdate, NEW.problem );
	END;
//

CREATE TRIGGER chronic_problems_Update
	AFTER UPDATE ON chronic_problems
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.pdate, patient=NEW.ppatient, summary=NEW.problem WHERE module='chronic_problems' AND oid=NEW.id;
	END;
//

DELIMITER ;

