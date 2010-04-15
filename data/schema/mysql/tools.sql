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

CREATE TABLE IF NOT EXISTS `tools` (
	  tool_name			VARCHAR (100) NOT NULL
	, tool_uuid			CHAR (36) NOT NULL
	, tool_locale		CHAR (5) NOT NULL DEFAULT 'en_US'
	, tool_desc			TEXT
	, tool_sp			VARCHAR (150) NOT NULL
	, tool_param_count		TINYINT(3) NOT NULL DEFAULT 0
	, tool_param_names		TEXT
	, tool_param_types		TEXT
	, tool_param_options		TEXT
	, tool_param_optional	TEXT
	, tool_acl			VARCHAR (150)

	#	Define keys

	, PRIMARY KEY			( tool_uuid )
	, KEY				( tool_name, tool_uuid )
);

DROP PROCEDURE IF EXISTS tools_Upgrade;
DELIMITER //
CREATE PROCEDURE tools_Upgrade ( ) 
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
	#CALL FreeMED_Module_GetVersion( 'tools', @V );

	#CALL FreeMED_Module_UpdateVersion( 'tools', 1 );
END//
DELIMITER ;
CALL tools_Upgrade( );

#	Load packaged tools

SOURCE data/schema/mysql/tools/tool_MoveTag.sql
SOURCE data/schema/mysql/tools/tool_ReassignAppointments.sql

