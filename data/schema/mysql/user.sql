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

CREATE TABLE IF NOT EXISTS `user` (
	username			VARCHAR (16) NOT NULL,
	userpassword			CHAR (32) NOT NULL,
	userdescrip			VARCHAR (50) NOT NULL DEFAULT '',
	userlevel			BLOB,
	usertype			ENUM ( 'phy', 'misc', 'super' ),
	userfac				BLOB,
	userphy				BLOB,
	userphygrp			BLOB,
	userrealphy			INT UNSIGNED NOT NULL DEFAULT 0,
	usermanageopt			BLOB,
	useremail			VARCHAR (250),
	usersms				VARCHAR (25),
	usersmsprovider			INT UNSIGNED NOT NULL DEFAULT 0,
	id				SERIAL,

	#	Define keys
	PRIMARY KEY 			( id )
);

DROP PROCEDURE IF EXISTS user_Upgrade;
DELIMITER //
CREATE PROCEDURE user_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	ALTER IGNORE TABLE user ADD COLUMN useremail VARCHAR (250) AFTER usermanageopt;
	ALTER IGNORE TABLE user ADD COLUMN usersms VARCHAR (25) AFTER useremail;
	ALTER IGNORE TABLE user ADD COLUMN usersmsprovider INT UNSIGNED NOT NULL DEFAULT 0 AFTER usersms;
END
//
DELIMITER ;
CALL user_Upgrade( );

