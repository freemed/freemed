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

CREATE TABLE IF NOT EXISTS `systemnotification` (
	stamp			TIMESTAMP (16) NOT NULL DEFAULT NOW(),
	nuser			INT UNSIGNED NOT NULL DEFAULT 0,
	ntext			VARCHAR (250) NOT NULL DEFAULT '',
	nmodule			VARCHAR (250) NOT NULL DEFAULT '',
	npatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

	#	Default key

	PRIMARY KEY		( id ),
	KEY			( stamp, nuser ),
	FOREIGN KEY		( nuser ) REFERENCES user.id ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS systemnotification_Upgrade;
DELIMITER //
CREATE PROCEDURE systemnotification_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers

	#----- Upgrades
END
//
DELIMITER ;
CALL systemnotification_Upgrade( );

