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

SOURCE data/schema/mysql/patient.sql
SOURCE data/schema/mysql/patient_emr.sql
SOURCE data/schema/mysql/workflow_status.sql

CREATE TABLE IF NOT EXISTS `rx` (
	  rxphy			INT UNSIGNED NOT NULL
	, rxpatient		BIGINT (20) UNSIGNED NOT NULL
	, rxdtadd		TIMESTAMP (14) NOT NULL
	, rxdtmod		DATE
	, rxdtfrom		DATE
	, rxdrug		VARCHAR (150) NOT NULL
	, rxdrugmultum		CHAR (20) NOT NULL
	, rxform		VARCHAR (32)
	, rxdosage		VARCHAR (128)
	, rxquantity		REAL NOT NULL DEFAULT 0
	, rxquantityqual	INT UNSIGNED NOT NULL DEFAULT 0
	, rxsize		VARCHAR (32)
	, rxunit		VARCHAR (32)
	, rxinterval		ENUM( 'BID', 'TID', 'QID', 'Q3H', 'Q4H', 'Q5H', 'Q6H', 'Q8H', 'QD', 'HS', 'QHS', 'QAM', 'QPM', 'AC', 'PC', 'PRN', 'QSHIFT', 'QOD', 'C', 'Once' ) NOT NULL DEFAULT 'Once'
	, rxsubstitute		TINYINT UNSIGNED NOT NULL DEFAULT 0
	, rxrefills		INT UNSIGNED NOT NULL DEFAULT 0
	, rxrefillinterval	CHAR(3) NOT NULL DEFAULT 'PRN'
	, rxperrefill		INT UNSIGNED DEFAULT 0
	, rxorigrx		INT UNSIGNED DEFAULT 0
	, rxdx			VARCHAR (255)
	, rxcovstatus		CHAR (2) NOT NULL DEFAULT 'UN'
	, rxsig			TEXT
	, rxnote		TEXT
	, orderid		INT UNSIGNED NOT NULL DEFAULT 0
	, locked		INT UNSIGNED DEFAULT 0
	, user			INT UNSIGNED NOT NULL DEFAULT 0
	, active		ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active'
	, id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT

	#	Default key

	, PRIMARY KEY		( id )
	, FOREIGN KEY		( rxpatient ) REFERENCES patient.id ON DELETE CASCADE
	, KEY			( rxdrugmultum )
);

DROP PROCEDURE IF EXISTS rx_Upgrade;
DELIMITER //
CREATE PROCEDURE rx_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER rx_Delete;
	DROP TRIGGER rx_Insert;
	DROP TRIGGER rx_Update;

	#----- Upgrades
	CALL FreeMED_Module_GetVersion( 'rx', @V );

	# Version 1

	ALTER IGNORE TABLE rx ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER locked;
	ALTER IGNORE TABLE rx ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;

	# Version 2
	ALTER IGNORE TABLE rx ADD COLUMN rxdtadd DATE NOT NULL AFTER rxpatient;
	ALTER IGNORE TABLE rx ADD COLUMN rxdtmod DATE AFTER rxdtadd;

	# Version 3
	ALTER IGNORE TABLE rx CHANGE COLUMN rxsubstitute rxsubstitute TINYINT UNSIGNED NOT NULL DEFAULT 0;
	ALTER IGNORE TABLE rx ADD COLUMN rxrefillinterval CHAR(3) NOT NULL DEFAULT 'PRN' AFTER rxrefills;
	ALTER IGNORE TABLE rx ADD COLUMN rxdx VARCHAR (255) AFTER rxorigrx;
	ALTER IGNORE TABLE rx ADD COLUMN rxcovstatus CHAR (2) NOT NULL DEFAULT 'UN' AFTER rxdx;
	ALTER IGNORE TABLE rx ADD COLUMN rxdrugmultum CHAR (20) NOT NULL DEFAULT '' AFTER rxdrug;
	ALTER IGNORE TABLE rx ADD COLUMN rxquantityqual INT UNSIGNED NOT NULL DEFAULT 0 AFTER rxquantity;
	ALTER IGNORE TABLE rx ADD COLUMN rxsig TEXT AFTER rxcovstatus;
	ALTER IGNORE TABLE rx CHANGE COLUMN rxunit rxunit VARCHAR (32);

	# HL7 v2.3 normalization
	ALTER IGNORE TABLE rx CHANGE COLUMN rxinterval rxinterval ENUM( 'BID', 'TID', 'QID', 'Q3H', 'Q4H', 'Q5H', 'Q6H', 'Q8H', 'QD', 'HS', 'QHS', 'QAM', 'QPM', 'AC', 'PC', 'PRN', 'QSHIFT', 'QOD', 'C', 'Once' ) NOT NULL DEFAULT 'Once';

	ALTER IGNORE TABLE rx ADD COLUMN orderid INT UNSIGNED NOT NULL DEFAULT 0 AFTER rxnote;

	CALL FreeMED_Module_UpdateVersion( 'rx', 3 );
END
//
DELIMITER ;
CALL rx_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER rx_Delete
	AFTER DELETE ON rx
	FOR EACH ROW BEGIN
		DECLARE c INT UNSIGNED;
		DELETE FROM `patient_emr` WHERE module='rx' AND oid=OLD.id;
		SELECT COUNT(*) INTO c FROM patient_emr WHERE module='rx' AND patient=OLD.rxpatient AND DATE_FORMAT( stamp, '%Y-%m-%d' ) = OLD.rxdtadd;
		IF c < 1 THEN
			CALL patientWorkflowUpdateStatus( OLD.rxpatient, DATE_FORMAT( OLD.rxdtadd, '%Y-%m-%d' ), 'rx', FALSE, OLD.user );
		END IF;
	END;
//

CREATE TRIGGER rx_Insert
	AFTER INSERT ON rx
	FOR EACH ROW BEGIN
		DECLARE mDrug VARCHAR (150);
		IF LENGTH( NEW.rxdrug ) < 10 OR ISNULL( NEW.rxdrug ) THEN
			SELECT CONCAT( brand_description, ' (', description, ') ', form ) INTO mDrug FROM multum WHERE id=NEW.rxdrugmultum LIMIT 1;
		ELSE
			SELECT NEW.rxdrug INTO mDrug;
		END IF;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, locked, user, status ) VALUES ( 'rx', NEW.rxpatient, NEW.id, NOW(), mDrug, NEW.locked, NEW.user, NEW.active );
		CALL patientWorkflowUpdateStatus( NEW.rxpatient, NOW(), 'rx', TRUE, NEW.user );
	END;
//

CREATE TRIGGER rx_Update
	AFTER UPDATE ON rx
	FOR EACH ROW BEGIN
		DECLARE mDrug VARCHAR (150);
		IF LENGTH( NEW.rxdrug ) < 10 OR ISNULL( NEW.rxdrug ) THEN
			SELECT CONCAT( brand_description, ' (', description, ') ', form ) INTO mDrug FROM multum WHERE id=NEW.rxdrugmultum LIMIT 1;
		ELSE
			SELECT NEW.rxdrug INTO mDrug;
		END IF;
		UPDATE `patient_emr` SET stamp=NOW(), patient=NEW.rxpatient, summary=mDrug, locked=NEW.locked, user=NEW.user, status=NEW.active WHERE module='rx' AND oid=NEW.id;
	END;
//

DELIMITER ;

