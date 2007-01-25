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

CREATE TABLE IF NOT EXISTS `physician` (
	phylname		VARCHAR (52) NOT NULL,
	phyfname		VARCHAR (50) NOT NULL,
	phymname		VARCHAR (50) NOT NULL,
	phytitle		VARCHAR (10),
	phypracname		VARCHAR (80),
	phypracein		VARCHAR (16),
	phyaddr1a		VARCHAR (30),
	phyaddr2a		VARCHAR (30),
	phycitya		VARCHAR (20),
	phystatea		VARCHAR (20),
	phyzipa			CHAR (9),
	phyphonea		VARCHAR (16),
	phyfaxa			VARCHAR (16),
	phyaddr1b		VARCHAR (30),
	phyaddr2b		VARCHAR (30),
	phycityb		VARCHAR (20),
	phystateb		VARCHAR (20),
	phyzipb			CHAR (9),
	phyphoneb		VARCHAR (16),
	phyfaxb			VARCHAR (16),
	phyemail		VARCHAR (50),
	phycellular		VARCHAR (16),
	phypager		VARCHAR (16),
	phyupin			VARCHAR (15),
	physsn			CHAR (9),
	phydeg1			INT UNSIGNED,
	phydeg2			INT UNSIGNED,
	phydeg3			INT UNSIGNED,
	physpe1			INT UNSIGNED,
	physpe2			INT UNSIGNED,
	physpe3			INT UNSIGNED,
	phyid1			CHAR (10),
	phystatus		INT UNSIGNED,
	phyref			ENUM ( 'yes', 'no' ) NOT NULL DEFAULT 'no',
	phyrefcount		INT UNSIGNED,
	phyrefamt		REAL DEFAULT 0.00,
	phyrefcoll		REAL DEFAULT 0.00,
	phychargemap		TEXT,
	phyidmap		TEXT,
	phygrpprac		INT UNSIGNED,
	phyanesth		INT UNSIGNED NOT NULL DEFAULT 0,
	phyhl7id		VARCHAR (16) DEFAULT '',
	phydea			VARCHAR (16) NOT NULL DEFAULT '',
	phyclia			VARCHAR (32) NOT NULL DEFAULT '',
	phynpi			VARCHAR (32) NOT NULL DEFAULT '',
	
	id			BIGINT NOT NULL AUTO_INCREMENT,

	#	Define keys
	PRIMARY KEY		( id ),
	KEY			( phylname, phyfname, phymname )
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS physician_Upgrade;
DELIMITER //
CREATE PROCEDURE physician_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades

	# Version 0.3.6
	ALTER IGNORE TABLE physician ADD COLUMN phynpi VARCHAR(32) NOT NULL DEFAULT '' AFTER phyclia;
END
//
DELIMITER ;
CALL physician_Upgrade( );

