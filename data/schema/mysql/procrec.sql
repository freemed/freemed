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

SOURCE data/schema/mysql/patient.sql
SOURCE data/schema/mysql/patient_emr.sql
SOURCE data/schema/mysql/physician.sql
SOURCE data/schema/mysql/cpt.sql
SOURCE data/schema/mysql/dxhistory.sql

CREATE TABLE IF NOT EXISTS `procrec` (
	procpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	proceoc			TEXT,
	proccpt			BIGINT UNSIGNED NOT NULL DEFAULT 0,
	proccptmod		INT UNSIGNED DEFAULT 0,
	proccptmod2		INT UNSIGNED DEFAULT 0,
	proccptmod3		INT UNSIGNED DEFAULT 0,
	procdiag1		INT UNSIGNED DEFAULT 0,
	procdiag2		INT UNSIGNED DEFAULT 0,
	procdiag3		INT UNSIGNED DEFAULT 0,
	procdiag4		INT UNSIGNED DEFAULT 0,
	proccharges		REAL DEFAULT 0.0,
	procunits		REAL DEFAULT 1.0,
	procvoucher		VARCHAR (25),
	procphysician		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	procdt			DATE,
	procdtend		DATE,
	procpos			INT UNSIGNED DEFAULT 0,
	proccomment		TEXT,
	procbalorig		REAL,
	procbalcurrent		REAL,
	procamtpaid		REAL,
	procbilled		INT UNSIGNED DEFAULT 0,
	procbillable		INT UNSIGNED DEFAULT 0,
	procauth		INT UNSIGNED DEFAULT 0,
	procrefdoc		INT UNSIGNED DEFAULT 0,
	procrefdt		DATE,
	procamtallowed		REAL,
	procdtbilled		TEXT,
	proccurcovid		INT UNSIGNED DEFAULT 0,
	proccurcovtp		INT UNSIGNED DEFAULT 0,
	proccov1		INT UNSIGNED DEFAULT 0,
	proccov2		INT UNSIGNED DEFAULT 0,
	proccov3		INT UNSIGNED DEFAULT 0,
	proccov4		INT UNSIGNED DEFAULT 0,
	procclmtp		INT UNSIGNED DEFAULT 0,
	procmedicaidref		VARCHAR (20),
	procmedicaidresub	VARCHAR (20),
	proclabcharges		REAL DEFAULT 0.0,
	procstatus		VARCHAR (50),
	procslidingscale	CHAR (1),
	proctosoverride		INT UNSIGNED DEFAULT 0,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY		( id ),

	#	Define keys

	FOREIGN KEY		( procpatient ) REFERENCES patient.id ON DELETE CASCADE,
	FOREIGN KEY		( proccpt ) REFERENCES cpt.id,
	FOREIGN KEY		( procphysician ) REFERENCES physician.id
);

DROP PROCEDURE IF EXISTS procrec_Upgrade;
DELIMITER //
CREATE PROCEDURE procrec_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER procrec_Delete;
	DROP TRIGGER procrec_Insert;
	DROP TRIGGER procrec_Update;

	#----- Upgrades
	CALL FreeMED_Module_GetVersion( 'procrec', @V );

	# Version 1
	IF @V < 1 THEN
		ALTER IGNORE TABLE procrec ADD COLUMN procslidingscale CHAR (1) AFTER procstatus;
		ALTER IGNORE TABLE procrec ADD COLUMN proctosoverride INT UNSIGNED DEFAULT 0 AFTER procslidingscale;
		ALTER IGNORE TABLE procrec ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER proctosoverride;
	END IF;

	# Version 2
	IF @V < 2 THEN
		ALTER IGNORE TABLE procrec ADD COLUMN procdtend DATE AFTER procdt;
	END IF;

	CALL FreeMED_Module_UpdateVersion( 'procrec', 2 );
END
//
DELIMITER ;
CALL procrec_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER procrec_Delete
	AFTER DELETE ON procrec
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='procrec' AND oid=OLD.id;
		DELETE FROM `dxhistory` WHERE procrec=OLD.id;
	END;
//

CREATE TRIGGER procrec_Insert
	AFTER INSERT ON procrec
	FOR EACH ROW BEGIN
		DECLARE c VARCHAR(250);
		SELECT CONCAT(cptcode, ' - ', cptdescrip) INTO c FROM cpt WHERE id=NEW.proccpt;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user ) VALUES ( 'procrec', NEW.procpatient, NEW.id, NEW.procdt, c, NEW.user );

		#	Diagnosis 1
		IF NEW.procdiag1 > 0 THEN
			INSERT INTO `dxhistory` ( patient, procrec, dx, stamp ) VALUES ( NEW.procpatient, NEW.id, NEW.procdiag1, NEW.procdt );
		END IF;

		#	Diagnosis 2
		IF NEW.procdiag2 > 0 THEN
			INSERT INTO `dxhistory` ( patient, procrec, dx, stamp ) VALUES ( NEW.procpatient, NEW.id, NEW.procdiag2, NEW.procdt );
		END IF;

		#	Diagnosis 3
		IF NEW.procdiag3 > 0 THEN
			INSERT INTO `dxhistory` ( patient, procrec, dx, stamp ) VALUES ( NEW.procpatient, NEW.id, NEW.procdiag3, NEW.procdt );
		END IF;

		#	Diagnosis 4
		IF NEW.procdiag4 > 0 THEN
			INSERT INTO `dxhistory` ( patient, procrec, dx, stamp ) VALUES ( NEW.procpatient, NEW.id, NEW.procdiag4, NEW.procdt );
		END IF;

	END;
//

CREATE TRIGGER procrec_Update
	AFTER UPDATE ON procrec
	FOR EACH ROW BEGIN
		DECLARE c VARCHAR(250);
		SELECT CONCAT(cptcode, ' - ', cptdescrip) INTO c FROM cpt WHERE id=NEW.proccpt;
		UPDATE `patient_emr` SET stamp=NEW.procdt, patient=NEW.procpatient, summary=c, user=NEW.user WHERE module='procrec' AND oid=NEW.id;

		#	Diagnosis 1
		DELETE FROM `dxhistory` WHERE procrec=OLD.id AND dx=OLD.procdiag1;
		IF NEW.procdiag1 > 0 THEN
			INSERT INTO `dxhistory` ( patient, procrec, dx, stamp ) VALUES ( NEW.procpatient, NEW.id, NEW.procdiag1, NEW.procdt );
		END IF;

		#	Diagnosis 2
		DELETE FROM `dxhistory` WHERE procrec=OLD.id AND dx=OLD.procdiag2;
		IF NEW.procdiag2 > 0 THEN
			INSERT INTO `dxhistory` ( patient, procrec, dx, stamp ) VALUES ( NEW.procpatient, NEW.id, NEW.procdiag2, NEW.procdt );
		END IF;

		#	Diagnosis 3
		DELETE FROM `dxhistory` WHERE procrec=OLD.id AND dx=OLD.procdiag3;
		IF NEW.procdiag3 > 0 THEN
			INSERT INTO `dxhistory` ( patient, procrec, dx, stamp ) VALUES ( NEW.procpatient, NEW.id, NEW.procdiag3, NEW.procdt );
		END IF;

		#	Diagnosis 4
		DELETE FROM `dxhistory` WHERE procrec=OLD.id AND dx=OLD.procdiag4;
		IF NEW.procdiag4 > 0 THEN
			INSERT INTO `dxhistory` ( patient, procrec, dx, stamp ) VALUES ( NEW.procpatient, NEW.id, NEW.procdiag4, NEW.procdt );
		END IF;

	END;
//

DELIMITER ;

