# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2009 FreeMED Software Foundation
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

CREATE TABLE IF NOT EXISTS `utilities` (
	  utility_name			VARCHAR (100) NOT NULL
	, utility_uuid			CHAR (36) NOT NULL
	, utility_locale		CHAR (5) NOT NULL DEFAULT 'en_US'
	, utility_desc			TEXT
	, utility_sp			VARCHAR (150) NOT NULL
	, utility_param_count		TINYINT(3) NOT NULL DEFAULT 0
	, utility_param_names		TEXT
	, utility_param_types		TEXT
	, utility_param_options		TEXT
	, utility_param_optional	TEXT
	, utility_acl			VARCHAR (150)

	#	Define keys

	, PRIMARY KEY			( utility_uuid )
	, KEY				( utility_name, utility_uuid )
);

DROP PROCEDURE IF EXISTS utilities_Upgrade;
DELIMITER //
CREATE PROCEDURE utilities_Upgrade ( ) 
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
	#CALL FreeMED_Module_GetVersion( 'utilities', @V );

	#CALL FreeMED_Module_UpdateVersion( 'utilities', 1 );
END//
DELIMITER ;
CALL utilities_Upgrade( );

#	Load packaged utilities

SOURCE data/schema/mysql/utilities/utility_ReassignAppointments.sql

