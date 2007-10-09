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

#	Import external functions and procedures
SOURCE data/schema/mysql/_functions.sql

CREATE TABLE IF NOT EXISTS `userpreferences` (
	u_option		CHAR (64) UNIQUE NOT NULL,
	u_defaultvalue		VARCHAR (100),
	u_title			VARCHAR (100),
	u_section		VARCHAR (100),
	u_type			VARCHAR (100) NOT NULL,
	u_options		TEXT,
	id			SERIAL
);

DROP PROCEDURE IF EXISTS userpreferences_Upgrade;
DELIMITER //
CREATE PROCEDURE userpreferences_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
END
//
DELIMITER ;
CALL userpreferences_Upgrade( );

DROP PROCEDURE IF EXISTS userpreferences_Register;
DELIMITER //

# Function: userpreferences_Register
#
#	Register userpreferencesuration entries.
#
# Parameters:
#
#	IN name - Option name. CHAR (64)
#
#	IN defaultValue - Default value for this option. VARCHAR (100).
#
#	IN title - Textual name for this option. VARCHAR (100).
#
#	IN section - Optional section name for this option. VARCHAR (100).
#
#	IN type - Type of configuration widget. VARCHAR (100).
#
#	IN options - Options, if needed by widget. TEXT.
#
CREATE PROCEDURE userpreferences_Register ( IN name CHAR(64), IN defaultValue VARCHAR(100), IN title VARCHAR(100), IN section VARCHAR(100), IN type VARCHAR(100), IN options TEXT )
BEGIN
	DECLARE found BOOL;
	SELECT ( COUNT(*) > 0 ) INTO found FROM userpreferences WHERE u_option=name;

	IF found THEN
		UPDATE userpreferences SET u_title=title, u_defaultvalue=defaultValue, u_section=section, u_type=type, u_options=options WHERE u_option=name;
	ELSE
		INSERT INTO userpreferences ( u_option, u_defaultvalue, u_title, u_section, u_type, u_options ) VALUES ( name, defaultValue, title, section, type, options );
	END IF;
END
//
DELIMITER ;

#----- Define basic user preferences values

CALL userpreferences_Register (
	'workflow_status_age',
	'1',
	'Workflow Status Maximum Age',
	'Workflow',
	'Select',
	'1,2,3,4,5,6,7,8,9,10,11,12,13,14,-1/Never Expires'
);

