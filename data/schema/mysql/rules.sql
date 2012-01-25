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

CREATE TABLE IF NOT EXISTS `rules` (
	  rule_created			TIMESTAMP (16) NOT NULL DEFAULT NOW()
	, rule_descrip			VARCHAR (150)
	, rule_prio			INT UNSIGNED
	, rule_type			VARCHAR (150) NOT NULL DEFAULT 'BILLING'
	, rule_clause_if_facility_eq	ENUM ( 'OFF', 'EQ', 'NE' ) NOT NULL DEFAULT 'OFF'
	, rule_clause_if_facility	TEXT DEFAULT NULL
	, rule_clause_if_cpt_eq		ENUM ( 'OFF', 'EQ', 'NE' ) NOT NULL DEFAULT 'OFF'
	, rule_clause_if_cpt		TEXT DEFAULT NULL
	, rule_clause_if_cptmod_eq	ENUM ( 'OFF', 'EQ', 'NE' ) NOT NULL DEFAULT 'OFF'
	, rule_clause_if_cptmod		TEXT DEFAULT NULL
	, rule_clause_then_charges	REAL DEFAULT NULL
	, rule_clause_then_tos		INT UNSIGNED DEFAULT NULL
	, id				SERIAL

	#	Define keys

	, KEY				( rule_type, rule_prio )
);

DROP PROCEDURE IF EXISTS rules_Upgrade;
DELIMITER //
CREATE PROCEDURE rules_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER rules_PreInsert;
	DROP TRIGGER rules_PreUpdate;

	#----- Upgrades
	CALL FreeMED_Module_GetVersion( 'rules', @V );

	CALL FreeMED_Module_UpdateVersion( 'rules', 1 );
END
//
DELIMITER ;
CALL rules_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER rules_PreInsert
	BEFORE INSERT ON rules
	FOR EACH ROW BEGIN

	END;
//

CREATE TRIGGER rules_PreUpdate
	BEFORE UPDATE ON rules
	FOR EACH ROW BEGIN

	END;
//

DELIMITER ;

