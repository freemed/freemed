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

CREATE TABLE IF NOT EXISTS `workflow_status_type` (
	status_name		VARCHAR (250) NOT NULL,
	status_order		INT NOT NULL DEFAULT 0,
	status_module		VARCHAR (250) NOT NULL,
	active			BOOL NOT NULL DEFAULT TRUE,
	id			SERIAL
);

DROP PROCEDURE IF EXISTS workflow_status_type_Upgrade;
DELIMITER //
CREATE PROCEDURE workflow_status_type_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
END
//
DELIMITER ;
CALL workflow_status_type_Upgrade( );

