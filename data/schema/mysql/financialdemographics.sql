# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2009 FreeMED Software Foundation
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

SOURCE data/schema/mysql/config.sql
SOURCE data/schema/mysql/patient.sql
SOURCE data/schema/mysql/patient_emr.sql

CREATE TABLE IF NOT EXISTS `financialdemographics` (
	fdtimestamp		TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	fdpatient		BIGINT UNSIGNED NOT NULL,
	fdincome		INT UNSIGNED,
	fdidtype		VARCHAR (50),
	fdidissuer		VARCHAR (50),
	fdidnumber		VARCHAR (50),
	fdidexpire		VARCHAR (10),
	fdhousehold		INT UNSIGNED,
	fdspouse		INT UNSIGNED,
	fdchild			INT UNSIGNED,
	fdother			INT UNSIGNED,
	fdfreetext		TEXT,
	fdentry			VARCHAR (75),
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			SERIAL,

	#	Define keys

	KEY			( fdpatient, fdtimestamp ),
	FOREIGN KEY		( fdpatient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS financialdemographics_Upgrade;
DELIMITER //
CREATE PROCEDURE financialdemographics_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER financialdemographics_Delete;
	DROP TRIGGER financialdemographics_Insert;
	DROP TRIGGER financialdemographics_Update;

	#----- Configuration options
	CALL config_Register (
		'sliding_fee',
		'0',
		'Sliding Fee Enabled',
		'Financial Demographics',
		'YesNo',
		''
	);
	CALL config_Register (
		'fed_pov_level',
		'0',
		'Federal Poverty Level',
		'Financial Demographics',
		'Number',
		''
	);
	CALL config_Register (
		'fed_pov_inc',
		'0',
		'Federal Poverty Increment',
		'Financial Demographics',
		'Number',
		''
	);

	#----- Upgrades
	ALTER TABLE financialdemographics ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER fdentry;
	ALTER TABLE financialdemographics ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
END
//
DELIMITER ;
CALL financialdemographics_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER financialdemographics_Delete
	AFTER DELETE ON financialdemographics
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='financialdemographics' AND oid=OLD.id;
	END;
//

CREATE TRIGGER financialdemographics_Insert
	AFTER INSERT ON financialdemographics
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'financialdemographics', NEW.fdpatient, NEW.id, NEW.fdtimestamp, NEW.fdentry, NEW.user, NEW.active );
	END;
//

CREATE TRIGGER financialdemographics_Update
	AFTER UPDATE ON financialdemographics
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.fdtimestamp, patient=NEW.fdpatient, summary=NEW.fdentry, user=NEW.user, status=NEW.active WHERE module='financialdemographics' AND oid=NEW.id;
	END;
//

DELIMITER ;

