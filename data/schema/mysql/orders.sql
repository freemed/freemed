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
SOURCE data/schema/mysql/systemnotification.sql

CREATE TABLE IF NOT EXISTS `orders` (
	  dateof		TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
	, patient		BIGINT UNSIGNED NOT NULL
	, provider		BIGINT UNSIGNED NOT NULL
	, eoc			INT UNSIGNED
	, ordertype		ENUM ( 'consult', 'radiology', 'lab', 'immunization', 'procedure', 'rx' ) NOT NULL
	, orderstatus 		ENUM ( 'CA', 'CM', 'DC', 'ER', 'HD', 'IP', 'RP', 'SC' ) NOT NULL DEFAULT 'IP'
	, orderresponseflag	ENUM ( 'D', 'E', 'F', 'N', 'R' ) DEFAULT 'R'
	, orderingprovider	INT UNSIGNED NOT NULL
	, delinquentdate	DATE
	, orderpriority		ENUM ( 'R', 'S' ) DEFAULT 'R' COMMENT 'Routine or Stat'
	, problems		TEXT COMMENT 'CSV list of dx'
	, summary		VARCHAR (250) NOT NULL DEFAULT ''
				COMMENT 'Textual description of the order'
	, notes			TEXT

	, consultingprovider	INT UNSIGNED DEFAULT 0

	, radiologycode		INT UNSIGNED DEFAULT 0

	, labpanelcodeset	INT UNSIGNED DEFAULT 0
	, labpanelcode		INT UNSIGNED DEFAULT 0
	, labspecimenactioncode	ENUM ( 'A', 'G', 'L', 'O', 'P', 'R', 'S' ) DEFAULT 'S'

	, immunizationcode	INT UNSIGNED DEFAULT 0
	, immunizationgivendate	DATE DEFAULT NULL
	, immunizationunits	REAL DEFAULT 0.0

	, procedurecode		INT UNSIGNED DEFAULT 0

	, locked		INT UNSIGNED NOT NULL DEFAULT 0
	, user			INT UNSIGNED NOT NULL DEFAULT 0
	, active		ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active'
	, id			SERIAL

	#	Define keys
	, KEY			( patient, dateof, provider )
	, FOREIGN KEY		( patient ) REFERENCES patient ( id ) ON DELETE CASCADE
	, FOREIGN KEY		( provider ) REFERENCES physician ( id )
);

DROP PROCEDURE IF EXISTS orders_Upgrade;
DELIMITER //
CREATE PROCEDURE orders_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER orders_Delete;
	DROP TRIGGER orders_Insert;
	DROP TRIGGER orders_Update;

	#----- Upgrades
	ALTER IGNORE TABLE orders ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER locked;
	ALTER IGNORE TABLE orders ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
END
//
DELIMITER ;
CALL orders_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER orders_Delete
	AFTER DELETE ON orders
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='orders' AND oid=OLD.id;
	END;
//

CREATE TRIGGER orders_Insert
	AFTER INSERT ON orders
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, locked, user, status, provider ) VALUES ( 'orders', NEW.patient, NEW.id, NEW.dateof, NEW.summary, NEW.locked, NEW.user, NEW.active, NEW.provider );
		INSERT INTO systemnotification ( stamp, nuser, ntext, nmodule, npatient, naction ) VALUES ( NEW.dateof, 0, NEW.summary, 'orders', NEW.patient, 'NEW' );
	END;
//

CREATE TRIGGER orders_Update
	AFTER UPDATE ON orders
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.dateof, patient=NEW.patient, summary=NEW.summary, locked=NEW.locked, user=NEW.user, status=NEW.active, provider=NEW.provider WHERE module='orders' AND oid=NEW.id;
		INSERT INTO systemnotification ( stamp, nuser, ntext, nmodule, npatient, naction ) VALUES ( NEW.dateof, 0, NEW.summary, 'orders', NEW.patient, 'UPDATE' );
	END;
//

DELIMITER ;

