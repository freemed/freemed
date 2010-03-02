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

CREATE TABLE IF NOT EXISTS `holiday` (
	hol_date		DATE DEFAULT NULL,
	hol_descrip		VARCHAR (200) DEFAULT NULL,
	id			SERIAL
);

DROP PROCEDURE IF EXISTS holiday_Upgrade;
DELIMITER //
CREATE PROCEDURE holiday_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
	CALL FreeMED_Module_GetVersion( 'holiday', @V );

	CALL FreeMED_Module_UpdateVersion( 'holiday', 1 );
END
//
DELIMITER ;
CALL holiday_Upgrade( );

