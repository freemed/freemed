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

CREATE TABLE IF NOT EXISTS `certifications` (
	certpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	certtype		INT UNSIGNED NOT NULL,
	certformnum		INT UNSIGNED,
	certdesc		VARCHAR (20),
	certformdata		TEXT,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			SERIAL,

	#	Define keys

	KEY			( certpatient, certtype ),
	FOREIGN KEY		( certpatient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS certifications_Upgrade;
DELIMITER //
CREATE PROCEDURE certifications_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER certifications_Delete;
	DROP TRIGGER certifications_Insert;
	DROP TRIGGER certifications_Update;

	#----- Upgrades
	ALTER TABLE certifications ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER certformdata;
	ALTER TABLE certifications ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
END
//
DELIMITER ;
CALL certifications_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER certifications_Delete
	AFTER DELETE ON certifications
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='certifications' AND oid=OLD.id;
	END;
//

CREATE TRIGGER certifications_Insert
	AFTER INSERT ON certifications
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'certifications', NEW.certpatient, NEW.id, NOW(), NEW.certdesc, NEW.user, NEW.active );
	END;
//

CREATE TRIGGER certifications_Update
	AFTER UPDATE ON certifications
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NOW(), patient=NEW.certpatient, summary=NEW.certdesc, user=NEW.user, status=NEW.active WHERE module='certifications' AND oid=NEW.id;
	END;
//

DELIMITER ;

