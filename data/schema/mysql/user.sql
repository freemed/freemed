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

CREATE TABLE IF NOT EXISTS `user` (
	  username			VARCHAR (16) NOT NULL UNIQUE
	, userpassword			CHAR (32) NOT NULL
	, userdescrip			VARCHAR (50) NOT NULL DEFAULT ''
	, userlevel			BLOB
	, usertype			ENUM ( 'phy', 'misc', 'super' )
	, userfac			BLOB
	, userphy			BLOB
	, userphygrp			BLOB
	, userrealphy			INT UNSIGNED NOT NULL DEFAULT 0
	, usermanageopt			BLOB
	, useremail			VARCHAR (250)
	, usersms			VARCHAR (25)
	, usersmsprovider		INT UNSIGNED NOT NULL DEFAULT 0
	, userfname 			VARCHAR(50) NOT NULL DEFAULT ''
	, userlname 			VARCHAR(50) NOT NULL DEFAULT ''
	, usermname 			VARCHAR(50) NOT NULL DEFAULT ''
	, usertitle 			VARCHAR(50) NOT NULL DEFAULT ''
	, id				SERIAL

	#	Define keys
	, PRIMARY KEY 			( id )
);

DROP PROCEDURE IF EXISTS user_Upgrade;
DELIMITER //
CREATE PROCEDURE user_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	ALTER IGNORE TABLE user ADD COLUMN useremail VARCHAR (250) AFTER usermanageopt;
	ALTER IGNORE TABLE user ADD COLUMN usersms VARCHAR (25) AFTER useremail;
	ALTER IGNORE TABLE user ADD COLUMN usersmsprovider INT UNSIGNED NOT NULL DEFAULT 0 AFTER usersms;
	ALTER IGNORE TABLE user ADD COLUMN userfname VARCHAR(50) NOT NULL DEFAULT '' AFTER userdescrip;
	ALTER IGNORE TABLE user ADD COLUMN userlname VARCHAR(50) NOT NULL DEFAULT '' AFTER userfname;
	ALTER IGNORE TABLE user ADD COLUMN usermname VARCHAR(50) NOT NULL DEFAULT '' AFTER userlname;
	ALTER IGNORE TABLE user ADD COLUMN usertitle VARCHAR(50) NOT NULL DEFAULT '' AFTER usermname;
	ALTER IGNORE TABLE user CHANGE COLUMN username username	VARCHAR (16) NOT NULL UNIQUE;
END
//
DELIMITER ;
CALL user_Upgrade( );

