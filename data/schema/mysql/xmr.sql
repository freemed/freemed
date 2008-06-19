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
SOURCE data/schema/mysql/xmr_definition.sql

CREATE TABLE IF NOT EXISTS `xmr` (
	patient			BIGINT UNSIGNED NOT NULL,
	form_id			INT UNSIGNED NOT NULL,
	stamp			TIMESTAMP NOT NULL DEFAULT NOW(),
	provider		INT UNSIGNED NOT NULL,
	user			INT UNSIGNED NOT NULL,
	locked			INT UNSIGNED NOT NULL,
	id			SERIAL

	#	Define keys
	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
	, FOREIGN KEY		( form_id ) REFERENCES xmr_definition.id ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `xmr_element` (
	patient			BIGINT UNSIGNED NOT NULL,
	patient_form_id		INT UNSIGNED NOT NULL,
	atom_id			INT UNSIGNED NOT NULL,
	stamp			TIMESTAMP NOT NULL DEFAULT NOW(),
	user			INT UNSIGNED NOT NULL,
	value			TEXT,
	id			SERIAL

	#	Define keys
	, KEY			( patient )
	, KEY			( atom_id )
	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
	, FOREIGN KEY		( patient_form_id ) REFERENCES xmr.id ON DELETE CASCADE
	, FOREIGN KEY		( atom_id ) REFERENCES xmr_definition_element.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS xmr_Upgrade;
DELIMITER //
CREATE PROCEDURE xmr_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER xmr_Delete;
	DROP TRIGGER xmr_Insert;
	DROP TRIGGER xmr_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL xmr_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER xmr_Delete
	AFTER DELETE ON xmr
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='xmr' AND oid=OLD.id;
	END;
//

CREATE TRIGGER xmr_Insert
	AFTER INSERT ON xmr
	FOR EACH ROW BEGIN
		DECLARE d VARCHAR (250);
		SELECT form_name INTO d FROM xmr_definition WHERE id = NEW.form_id;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, provider, locked ) VALUES ( 'xmr', NEW.patient, NEW.id, NEW.stamp, d, NEW.user, NEW.provider, NEW.locked );
	END;
//

CREATE TRIGGER xmr_Update
	AFTER UPDATE ON xmr
	FOR EACH ROW BEGIN
		DECLARE d VARCHAR (250);
		SELECT form_name INTO d FROM xmr_definition WHERE id = NEW.form_id;
		UPDATE `patient_emr` SET stamp=NEW.stamp, patient=NEW.patient, summary=d, user=NEW.user, provider=NEW.provider, locked=NEW.locked WHERE module='xmr' AND oid=NEW.id;
	END;
//

DELIMITER ;

