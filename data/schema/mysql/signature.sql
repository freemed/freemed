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
SOURCE data/schema/mysql/_functions.sql

CREATE TABLE IF NOT EXISTS `signature` (
	  stamp			TIMESTAMP (14) NOT NULL DEFAULT CURRENT_TIMESTAMP
	, patient		BIGINT UNSIGNED NOT NULL DEFAULT 0
	, module		VARCHAR (150) NOT NULL
	, module_field		VARCHAR (150) NOT NULL
	, oid			INT UNSIGNED NOT NULL
	, data			LONGBLOB
	, format		ENUM ( 'UNKNOWN', 'JPG', 'PNG', 'TOPAZ' ) NOT NULL DEFAULT 'UNKNOWN'
	, collector_location	VARCHAR (100)
	, collector_model	VARCHAR (100)
	, collector_jobid	VARCHAR (100)
	, collector_finished	BOOL NOT NULL DEFAULT FALSE
	, user			INT UNSIGNED NOT NULL
	, id			SERIAL

	#	Define keys

	, KEY			( patient, module, oid,module_field )
	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS signature_Upgrade;
DELIMITER //
CREATE PROCEDURE signature_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER signature_Delete;
	DROP TRIGGER signature_Insert;
	DROP TRIGGER signature_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL signature_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER signature_Delete
	AFTER DELETE ON signature
	FOR EACH ROW BEGIN
	END;
//

CREATE TRIGGER signature_Insert
	AFTER INSERT ON signature
	FOR EACH ROW BEGIN
	END;
//

DELIMITER ;

