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

CREATE TABLE IF NOT EXISTS `calgroupattend` (
	calgroupid		INT UNSIGNED NOT NULL,
	calid			INT UNSIGNED NOT NULL,
	patientid		INT UNSIGNED DEFAULT NULL,
	calstatus		ENUM('scheduled','confirmed','attended','cancelled','noshow','tenative') DEFAULT NULL,
	stamp			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	id			SERIAL,
	
	# define keys
	
	PRIMARY KEY  (`id`)	
	
);

DROP PROCEDURE IF EXISTS calgroupattend_Upgrade;
DELIMITER //
CREATE PROCEDURE calgroupattend_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades

	CALL FreeMED_Module_UpdateVersion( 'calgroupattend', 1 );
END
//
DELIMITER ;
CALL calgroupattend_Upgrade( );

