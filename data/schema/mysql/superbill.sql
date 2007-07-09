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

SOURCE data/schema/mysql/patient.sql

CREATE TABLE IF NOT EXISTS `superbill` (
	dateofservice		DATE,
	enteredby		INT UNSIGNED,
	patient			BIGINT UNSIGNED NOT NULL DEFAULT 0,
	provider		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	procs			BLOB,
	dx			BLOB,
	note			VARCHAR (250),
	reviewed		INT UNSIGNED,
	id			SERIAL,

	#	Define keys
	KEY			( dateofservice, reviewed ),
	FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS superbill_Upgrade;
DELIMITER //
CREATE PROCEDURE superbill_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
	ALTER TABLE superbill ADD COLUMN provider BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER patient;
END
//
DELIMITER ;
CALL superbill_Upgrade( );

