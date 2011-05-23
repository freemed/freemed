# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2011 FreeMED Software Foundation
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
SOURCE data/schema/mysql/systemnotification.sql

CREATE TABLE IF NOT EXISTS `patient_emr` (
	  patient		BIGINT(20) UNSIGNED NOT NULL DEFAULT 0
	, module		VARCHAR (150) NOT NULL
	, oid			INT UNSIGNED NOT NULL
	, stamp			TIMESTAMP (16) NOT NULL DEFAULT NOW()
	, summary		VARCHAR (250) DEFAULT ''
	, locked		BOOL NOT NULL DEFAULT FALSE
	, annotation		TEXT
	, user			INT UNSIGNED NOT NULL DEFAULT 0
	, provider		INT UNSIGNED NOT NULL DEFAULT 0
	, language		CHAR( 5 ) DEFAULT ''
	, status		ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active'
	, id			SERIAL

	#	Define keys

	, KEY			( patient, module, oid )
	, KEY			( module, oid )
	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS patient_emr_Upgrade;
DELIMITER //
CREATE PROCEDURE patient_emr_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER patient_emr_PreInsert;
	DROP TRIGGER patient_emr_Insert;

	ALTER IGNORE TABLE patient_emr ADD COLUMN language CHAR( 5 ) DEFAULT '' AFTER provider;
	#ALTER IGNORE TABLE patient_emr CHANGE COLUMN summary summary VARCHAR (250) DEFAULT '';
END;
//

CREATE TRIGGER patient_emr_PreInsert
	BEFORE INSERT ON patient_emr
	FOR EACH ROW BEGIN
		DECLARE prov INT UNSIGNED;

		#	Handle resolving providers from user table
		IF NEW.provider = 0 THEN
			SELECT userrealphy INTO prov FROM user WHERE id=NEW.user;
			IF prov > 0 THEN
				SET NEW.provider = prov;
			END IF;
		END IF;
	END;
//

CREATE TRIGGER patient_emr_Insert
	AFTER INSERT ON patient_emr
	FOR EACH ROW BEGIN
		#	Fire off a notification
		INSERT INTO systemnotification ( stamp, nuser, ntext, nmodule, npatient, naction ) VALUES ( NEW.stamp, 0, NEW.module, 'patient_emr', NEW.patient, 'NEW' );
	END;
//

DELIMITER ;

CALL patient_emr_Upgrade ( );

