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

CREATE TABLE IF NOT EXISTS `codes` (
	  codedictionary	VARCHAR(50) NOT NULL
	, codevalue		VARCHAR(50) NOT NULL
	, codedescripinternal	VARCHAR(100) DEFAULT NULL
	, codedescripexternal	VARCHAR(100) DEFAULT NULL
	, codelimitgender	ENUM('n','m','f') DEFAULT NULL
	, id 			SERIAL

	#	Define keys
	, PRIMARY KEY 			(id)
	, KEY codedictionary		(codedictionary)
	, KEY codedescripinternal	(codedescripinternal)
	, KEY codevalue			(codevalue)
	, KEY codelimitgender		(codelimitgender)
);

DROP PROCEDURE IF EXISTS codes_Upgrade;
DELIMITER //
CREATE PROCEDURE codes_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER codesDelete;
	DROP TRIGGER codesInsert;
	DROP TRIGGER codesUpdate;

	#----- Upgrades
	#CALL FreeMED_Module_GetVersion( 'codes', @V );

	#CALL FreeMED_Module_UpdateVersion( 'codes', 1 );
END
//
DELIMITER ;
CALL codes_Upgrade( );

