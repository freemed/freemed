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

CREATE TABLE IF NOT EXISTS `billkey` (
	billkeydate		TIMESTAMP (14) DEFAULT NOW(),
	billkey			BLOB,
	bkprocs			TEXT,
	id			SERIAL
);

DROP PROCEDURE IF EXISTS billkey_Upgrade;
DELIMITER //
CREATE PROCEDURE billkey_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
	CALL FreeMED_Module_GetVersion( 'billkey', @V );

	#----- Upgrades

	# Version 1
	IF @V < 1 THEN
		ALTER IGNORE TABLE billkey ADD COLUMN bkprocs TEXT AFTER billkey;
	END IF;

	CALL FreeMED_Module_UpdateVersion( 'billkey', 1 );
END
//
DELIMITER ;
CALL billkey_Upgrade( );

