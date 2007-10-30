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

CREATE TABLE IF NOT EXISTS `practice` (
	pracname		VARCHAR (80) NOT NULL,
	ein			VARCHAR (16),
	addr1a			VARCHAR (50),
	addr2a			VARCHAR (50),
	citya			VARCHAR (20),
	statea			VARCHAR (20),
	zipa			CHAR (10),
	phonea			VARCHAR (16),
	countrya		VARCHAR (100),
	faxa			VARCHAR (16),
	addr1b			VARCHAR (50),
	addr2b			VARCHAR (50),
	cityb			VARCHAR (20),
	stateb			VARCHAR (20),
	zipb			CHAR (10),
	countryb		VARCHAR (100),
	phoneb			VARCHAR (16),
	faxb			VARCHAR (16),
	email			VARCHAR (50),
	cellular		VARCHAR (16),
	pager			VARCHAR (16),
	
	id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT

	#	Define keys
	, PRIMARY KEY		( id )
	, KEY			( pracname, citya, statea )
);

DROP PROCEDURE IF EXISTS practice_Upgrade;
DELIMITER //
CREATE PROCEDURE practice_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
	CALL FreeMED_Module_GetVersion( 'practice', @V );

	#IF @V < 1 THEN
	#END IF;

	CALL FreeMED_Module_UpdateVersion( 'practice', 1 );
END
//
DELIMITER ;
CALL practice_Upgrade( );

