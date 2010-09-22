# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2010 FreeMED Software Foundation
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

CREATE TABLE IF NOT EXISTS `clinicregistration` (
	  dateof			TIMESTAMP (14) NOT NULL DEFAULT NOW()
	, processed			BOOL NOT NULL DEFAULT FALSE
	, user				INT UNSIGNED NOT NULL DEFAULT 0
	, facility			INT UNSIGNED NOT NULL DEFAULT 0
	, archive			INT UNSIGNED NOT NULL DEFAULT 0
	, patient			INT UNSIGNED NOT NULL DEFAULT 0

	, lastname			VARCHAR (50)
	, lastname2			VARCHAR (50)
	, firstname			VARCHAR (50)
	, dob				DATE
	, gender			ENUM ( 'm', 'f' ) DEFAULT NULL
	, age				INT UNSIGNED DEFAULT 0
	, notes				TEXT

	, id				SERIAL

	#	Define keys
	, KEY				( processed )
	, KEY				( dateof )
	, FOREIGN KEY			( user ) REFERENCES user.id
);

DROP PROCEDURE IF EXISTS clinicregistration_Upgrade;
DELIMITER //
CREATE PROCEDURE clinicregistration_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER clinicregistration_Delete;
	DROP TRIGGER clinicregistration_PreInsert;
	DROP TRIGGER clinicregistration_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL clinicregistration_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER clinicregistration_Delete
	AFTER DELETE ON clinicregistration
	FOR EACH ROW BEGIN
	END;
//

CREATE TRIGGER clinicregistration_Update
	AFTER UPDATE ON clinicregistration
	FOR EACH ROW BEGIN
	END;
//

CREATE TRIGGER clinicregistration_PreInsert
	BEFORE INSERT ON clinicregistration
	FOR EACH ROW BEGIN
		# Determine age before full insert
		IF NEW.age = 0 THEN
			SET NEW.age = CAST( ( TO_DAYS(NOW()) - TO_DAYS( NEW.dob ) ) / 365 AS UNSIGNED );
		END IF;
	END;
//

DELIMITER ;

