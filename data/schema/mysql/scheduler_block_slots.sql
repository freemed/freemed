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

CREATE TABLE IF NOT EXISTS `scheduler_block_slots` (
	sbshour			    INT UNSIGNED NOT NULL,
	sbsminute			INT UNSIGNED NOT NULL,
	sbsduration			INT UNSIGNED NOT NULL,
	sbdate                      DATE,
	sbsprovider		    INT UNSIGNED NOT NULL,
	sbsprovidergroup	    INT UNSIGNED NOT NULL,
	stamp				TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	user				INT UNSIGNED NOT NULL,
	id			        SERIAL,
	#keys
	PRIMARY KEY(id)
);

DROP PROCEDURE IF EXISTS scheduler_block_slots_Upgrade;
DELIMITER //
CREATE PROCEDURE scheduler_block_slots_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER scheduler_block_slotsDelete;
	DROP TRIGGER scheduler_block_slotsInsert;
	DROP TRIGGER scheduler_block_slotsUpdate;

	#----- Upgrades
        CALL FreeMED_Module_GetVersion( 'scheduler_block_slots', @V );

	CALL FreeMED_Module_UpdateVersion( 'scheduler', 1 );
END
//
DELIMITER ;
CALL scheduler_block_slots_Upgrade( );

