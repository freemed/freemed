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

CREATE TABLE IF NOT EXISTS `calgroup` (
	groupname		VARCHAR (50) NOT NULL,
	groupfacility		INT UNSIGNED NOT NULL,
	groupfrequency		INT UNSIGNED NOT NULL DEFAULT 7,
	grouplength		INT UNSIGNED NOT NULL DEFAULT 60,
	groupmembers		TEXT,
	id			SERIAL
);

DROP PROCEDURE IF EXISTS calgroup_Upgrade;
DELIMITER //
CREATE PROCEDURE calgroup_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER calgroupDelete;
	DROP TRIGGER calgroupInsert;
	DROP TRIGGER calgroupUpdate;

	#----- Upgrades
        CALL FreeMED_Module_GetVersion( 'calgroup', @V );

	CALL FreeMED_Module_UpdateVersion( 'scheduler', 1 );
END
//
DELIMITER ;
CALL calgroup_Upgrade( );

