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

CREATE TABLE IF NOT EXISTS `claimlog` (
	cltimestamp		TIMESTAMP (14) DEFAULT NOW(),
	cluser			INT UNSIGNED NOT NULL,
	clprocedure		INT UNSIGNED NOT NULL DEFAULT 0,
	clpayrec		INT UNSIGNED NOT NULL DEFAULT 0,
	claction		VARCHAR (50),
	clcomment		TEXT,
	clformat		VARCHAR (32) DEFAULT '',
	cltarget		VARCHAR (128) DEFAULT '',
	cltargetopt		VARCHAR (128) DEFAULT '',
	clbillkey		INT UNSIGNED NOT NULL,
	id			SERIAL,

	#	Define keys

	KEY			( clprocedure, clpayrec )
);

DROP PROCEDURE IF EXISTS claimlog_Upgrade;
DELIMITER //
CREATE PROCEDURE claimlog_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
	ALTER IGNORE TABLE claimlog CHANGE COLUMN cltarget cltarget VARCHAR (128) DEFAULT '';
	ALTER IGNORE TABLE claimlog ADD COLUMN cltargetopt VARCHAR (128) DEFAULT '' AFTER cltarget;
END
//
DELIMITER ;
CALL claimlog_Upgrade( );

