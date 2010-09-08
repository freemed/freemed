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

CREATE TABLE IF NOT EXISTS `insco` (
	  inscodtadd		DATE
	, inscodtmod		DATE
	, insconame		VARCHAR (50) NOT NULL
	, inscoalias		VARCHAR (30)
	, inscoaddr1		VARCHAR (45)
	, inscoaddr2		VARCHAR (45)
	, inscocity		VARCHAR (30)
	, inscostate		CHAR (3)
	, inscozip		VARCHAR (10)
	, inscophone		VARCHAR (16)
	, inscofax		VARCHAR (16)
	, inscocontact		VARCHAR (100)
	, inscoid		CHAR (10)
	, inscowebsite		VARCHAR (100)
	, inscoemail		VARCHAR (50)
	, inscogroup		INT UNSIGNED
	, inscotype		INT UNSIGNED
	, inscoassign		INT UNSIGNED
	, inscomod		TEXT
	, inscoidmap		TEXT
	, inscox12id		VARCHAR (32) NOT NULL DEFAULT ''
	, inscodefoutput	ENUM ( 'electronic', 'paper' ) DEFAULT 'electronic'
	, inscodefformat	VARCHAR (50) DEFAULT 'cms1500'
	, inscodeftarget	VARCHAR (120) DEFAULT 'org.remitt.plugin.transpo
rt.StoreFilePdf'
	, inscodeftargetopt	VARCHAR (75) DEFAULT ''
	, inscodefformate	VARCHAR (50) DEFAULT '837p'
	, inscodeftargete	VARCHAR (120) DEFAULT 'org.remitt.plugin.transport.StoreFile'
	, inscodeftargetopte	VARCHAR (50) DEFAULT ''
	, inscoarchive		INT(10) UNSIGNED DEFAULT 0
	, id			SERIAL

	# Keys
	, KEY ( insconame )
	, KEY ( inscox12id )
	, KEY ( inscoarchive )
);

DROP PROCEDURE IF EXISTS insco_Upgrade;
DELIMITER //
CREATE PROCEDURE insco_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers

	#----- Upgrades
        CALL FreeMED_Module_GetVersion( 'insco', @V );

        # Version 2
        IF @V < 2 THEN
		#	Version 0.5
		# Add target options
		ALTER IGNORE TABLE insco ADD COLUMN inscodeftargetopt VARCHAR (50) AFTER inscodeftarget;
        	ALTER IGNORE TABLE insco ADD COLUMN inscodeftargetopte VARCHAR (50) AFTER inscodeftargete;

		#	Update REMITT modules (TODO, if need be)
	END IF;
	ALTER IGNORE TABLE insco ADD COLUMN inscoarchive INT(10) UNSIGNED DEFAULT 0 AFTER inscodeftargetopte;
	ALTER IGNORE TABLE insco ADD INDEX inscoarchive ( inscoarchive );

	CALL FreeMED_Module_UpdateVersion( 'insco', 2 );
END
//
DELIMITER ;
CALL insco_Upgrade( );

