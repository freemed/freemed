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

CREATE TABLE IF NOT EXISTS `pharmacy` (
	phname			VARCHAR (50) NOT NULL,
	phaddr1			VARCHAR (150) NOT NULL,
	phaddr2			VARCHAR (150),
	phcity			VARCHAR (150) NOT NULL,
	phstate			CHAR (3) NOT NULL,
	phzip			VARCHAR (10) NOT NULL,
	phfax			CHAR (16),
	phemail			VARCHAR (100),
	phmethod		VARCHAR (150) NOT NULL,
	id			SERIAL,

	# Define keys

	KEY			( phname, phcity, phstate )
);

DROP PROCEDURE IF EXISTS pharmacy_Upgrade;
DELIMITER //
CREATE PROCEDURE pharmacy_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
	CALL FreeMED_Module_GetVersion( 'pharmacy', @V );

	# Version 1
	IF @V < 1 THEN
		ALTER IGNORE TABLE pharmacy ADD COLUMN phfax CHAR (16) AFTER phzip;
		ALTER IGNORE TABLE pharmacy ADD COLUMN phemail VARCHAR (100) AFTER phfax;
	END IF;

	CALL FreeMED_Module_UpdateVersion( 'pharmacy', 1 );
END
//
DELIMITER ;
CALL pharmacy_Upgrade( );

