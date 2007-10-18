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

CREATE TABLE IF NOT EXISTS `zipcodes` (
	zip			CHAR (5) NOT NULL,
	city			CHAR (25) NOT NULL,
	state			CHAR (3) NOT NULL,
	latitude		REAL,
	longitude		REAL,
	timezone		INT,
	dst			INT UNSIGNED NOT NULL DEFAULT 0,
	country			CHAR (100) NOT NULL DEFAULT 'United States',
	id			SERIAL,

	# Define keys

	KEY			( city, state, zip, country )
);

DROP PROCEDURE IF EXISTS zipcodes_Upgrade;
DELIMITER //
CREATE PROCEDURE zipcodes_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
	CALL FreeMED_Module_GetVersion( 'zipcodes', @V );

	# Version 1
	IF @V < 1 THEN
		ALTER IGNORE TABLE zipcodes ADD COLUMN country CHAR (100) NOT NULL DEFAULT 'United States' AFTER dst;
	END IF;

	CALL FreeMED_Module_UpdateVersion( 'zipcodes', 1 );
END
//
DELIMITER ;
CALL zipcodes_Upgrade( );

