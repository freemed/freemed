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

CREATE TABLE IF NOT EXISTS `form_results` (
	fr_patient		BIGINT UNSIGNED NOT NULL,
	fr_timestamp		TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	fr_template		VARCHAR (50),
	fr_formname		VARCHAR (50),
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			SERIAL,

	#	Define keys

	KEY			( fr_patient, fr_timestamp ),
	FOREIGN KEY		( fr_patient ) REFERENCES patient ( id ) ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS form_results_Upgrade;
DELIMITER //
CREATE PROCEDURE form_results_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER form_results_Delete;
	DROP TRIGGER form_results_Insert;
	DROP TRIGGER form_results_Update;

	#----- Upgrades
	ALTER TABLE form_results ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER fr_formname;
	ALTER TABLE form_results ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
END
//
DELIMITER ;
CALL form_results_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER form_results_Delete
	AFTER DELETE ON form_results
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='form_results' AND oid=OLD.id;
	END;
//

CREATE TRIGGER form_results_Insert
	AFTER INSERT ON form_results
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'form_results', NEW.fr_patient, NEW.id, NEW.fr_timestamp, NEW.fr_template, NEW.user, NEW.active );
	END;
//

CREATE TRIGGER form_results_Update
	AFTER UPDATE ON form_results
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.fr_timestamp, patient=NEW.fr_patient, summary=NEW.fr_template, user=NEW.user, status=NEW.active WHERE module='form_results' AND oid=NEW.id;
	END;
//

DELIMITER ;

