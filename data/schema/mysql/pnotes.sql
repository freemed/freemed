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

CREATE TABLE IF NOT EXISTS `pnotes` (
	pnotesdt		DATE,
	pnotesdtadd		DATE,
	pnotesdtmod		DATE,
	pnotespat		BIGINT UNSIGNED NOT NULL,
	pnotesdescrip		VARCHAR (100),
	pnotesdoc		INT UNSIGNED NOT NULL,
	pnoteseoc		INT UNSIGNED,
	pnotes_S		TEXT,
	pnotes_O		TEXT,
	pnotes_A		TEXT,
	pnotes_P		TEXT,
	pnotes_I		TEXT,
	pnotes_E		TEXT,
	pnotes_R		TEXT,
	pnotessbp		INT UNSIGNED,
	pnotesdbp		INT UNSIGNED,
	pnotestemp		REAL,
	pnotesheartrate		INT UNSIGNED,
	pnotesresprate		INT UNSIGNED,
	pnotesweight		INT UNSIGNED,
	pnotesheight		INT UNSIGNED,
	pnotesbmi		INT UNSIGNED,
	iso			VARCHAR (15),
	locked			INT UNSIGNED NOT NULL DEFAULT 0,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			SERIAL,

	#	Define keys
	KEY			( pnotespat, pnotesdt, pnotesdoc ),
	FOREIGN KEY		( pnotespat ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS pnotes_Upgrade;
DELIMITER //
CREATE PROCEDURE pnotes_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER pnotes_Delete;
	DROP TRIGGER pnotes_Insert;
	DROP TRIGGER pnotes_Update;

	#----- Upgrades
	CALL FreeMED_Module_GetVersion( 'pnotes', @V );

	IF @V < 1 THEN
		ALTER IGNORE TABLE pnotes ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER locked;
		ALTER IGNORE TABLE pnotes ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
		ALTER IGNORE TABLE pnotes CHANGE COLUMN locked locked INT UNSIGNED NOT NULL DEFAULT 0;
	END IF;

	CALL FreeMED_Module_UpdateVersion( 'pnotes', 1 );
END
//
DELIMITER ;
CALL pnotes_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER pnotes_Delete
	AFTER DELETE ON pnotes
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='pnotes' AND oid=OLD.id;
	END;
//

CREATE TRIGGER pnotes_Insert
	AFTER INSERT ON pnotes
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, locked, provider, user, status ) VALUES ( 'pnotes', NEW.pnotespat, NEW.id, NEW.pnotesdt, NEW.pnotesdescrip, NEW.locked, NEW.pnotesdoc, NEW.user, NEW.active );
	END;
//

CREATE TRIGGER pnotes_Update
	AFTER UPDATE ON pnotes
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.pnotesdt, patient=NEW.pnotespat, summary=NEW.pnotesdescrip, provider=NEW.pnotesdoc, locked=NEW.locked, user=NEW.user, status=NEW.active WHERE module='pnotes' AND oid=NEW.id;
	END;
//

DELIMITER ;

