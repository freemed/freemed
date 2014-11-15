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

SOURCE data/schema/mysql/facility.sql

CREATE TABLE IF NOT EXISTS `shimstation` (
	  name				VARCHAR (50) NOT NULL
	, location			VARCHAR (150)
	, facility			BIGINT UNSIGNED NOT NULL
	, username			VARCHAR (50)
	, password			VARCHAR (50)
	, service_url			VARCHAR (150)
	, ip				VARCHAR (50)

	### Capabilities ###
	, dosing_enabled		TINYINT NOT NULL DEFAULT 0 
	, label_enabled			TINYINT NOT NULL DEFAULT 0
	, signature_enabled		TINYINT NOT NULL DEFAULT 0
	, vitals_enabled		TINYINT NOT NULL DEFAULT 0

	### Dosing specific fields ###
	, dosing_last_close		DATE
	, dosing_open			ENUM( 'open', 'closed' ) NOT NULL DEFAULT 'closed'
	, dosing_bottle			INT UNSIGNED
	, dosing_bottle_quantity	INT(10) UNSIGNED NOT NULL DEFAULT 0
	, dosing_lot			INT UNSIGNED

	, id				SERIAL

	#	Define keys
	, FOREIGN KEY			( facility ) REFERENCES facility ( id ) ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS shimstation_Upgrade;
DELIMITER //
CREATE PROCEDURE shimstation_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
END//
DELIMITER ;
CALL shimstation_Upgrade( );

#----- Define specific configuration options -----

CALL config_Register (
	'check_pump_status',
	'1',
	'Check Pump Status While Dispensing',
	'Dispensing',
	'YesNo',
	''
);

CALL config_Register (
	'bypass_comm_err',
	'0',
	'Bypass Communication Errors When Opening and Closing Dosing Station',
	'Dispensing',
	'YesNo',
	''
);

