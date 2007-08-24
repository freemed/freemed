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

CREATE TABLE IF NOT EXISTS `images` (
	imagedt			DATE,
	imagepat		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	imagetype		VARCHAR (50),
	imagecat		VARCHAR (50) DEFAULT '',
	imagedesc		VARCHAR (150),
	imageeoc		TEXT,
	imagefile		VARCHAR (100),
	imageformat		CHAR (4) NOT NULL DEFAULT 'djvu',
	imagephy		INT UNSIGNED DEFAULT 0,
	imagereviewed		INT UNSIGNED DEFAULT 0,
	imagetext		TEXT,
	locked			INT UNSIGNED DEFAULT 0,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			SERIAL,

	#	Define keys

	KEY			( imagepat, imagetype, imagecat, imagedt ),
	FOREIGN KEY		( imagepat ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS images_Upgrade;
DELIMITER //
CREATE PROCEDURE images_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER images_Delete;
	DROP TRIGGER images_Insert;
	DROP TRIGGER images_Update;

	#----- Upgrades
	CALL FreeMED_Module_GetVersion( 'images', @V );

	# Version 1
	IF @V < 1 THEN
		#	Version 0.3
		ALTER IGNORE TABLE images ADD COLUMN imagecat VARCHAR(50) DEFAULT '' AFTER imagetype;

		#	Version 0.4
		ALTER IGNORE TABLE images ADD COLUMN imagephy INT UNSIGNED DEFAULT 0 AFTER imagefile;

		#	Version 0.4.1
		ALTER IGNORE TABLE images ADD COLUMN locked INT UNSIGNED NOT NULL DEFAULT 0 AFTER imagephy;

		#	Version 0.4.2
		ALTER IGNORE TABLE images ADD COLUMN imagereviewed INT UNSIGNED NOT NULL DEFAULT 0 AFTER imagephy;

		#	Version 0.4.3
		ALTER IGNORE TABLE images ADD COLUMN imageformat CHAR(4) NOT NULL DEFAULT 'djvu' AFTER imagefile;

		ALTER IGNORE TABLE images ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER locked;
		ALTER IGNORE TABLE images ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
	END IF;

	# Version 2
	IF @V < 2 THEN
		ALTER TABLE images ADD COLUMN imagetext TEXT AFTER imagereviewed;
	END IF;

	CALL FreeMED_Module_UpdateVersion( 'images', 2 );
END
//
DELIMITER ;
CALL images_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER images_Delete
	AFTER DELETE ON images
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='images' AND oid=OLD.id;
	END;
//

CREATE TRIGGER images_Insert
	AFTER INSERT ON images
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, locked, user, status ) VALUES ( 'images', NEW.imagepat, NEW.id, NEW.imagedt, NEW.imagedesc, IFNULL(NEW.locked, 0), NEW.user, NEW.active );
	END;
//

CREATE TRIGGER images_Update
	AFTER UPDATE ON images
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.imagedt, patient=NEW.imagepat, summary=NEW.imagedesc, locked=IFNULL(NEW.locked, 0), user=NEW.user, status=NEW.active WHERE module='images' AND oid=NEW.id;
	END;
//

DELIMITER ;

