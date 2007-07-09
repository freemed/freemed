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
SOURCE data/schema/mysql/patient_emr.sql

CREATE TABLE IF NOT EXISTS `referrals` (
	refpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	refprovorig		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	refprovdest		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	refstamp		TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	refdx			VARCHAR (255) NOT NULL DEFAULT '',
	refpayor		INT UNSIGNED NOT NULL DEFAULT 0,
	refcoverage		INT UNSIGNED NOT NULL DEFAULT 0,
	refreasons		TEXT,
	refstatus		INT UNSIGNED NOT NULL DEFAULT 0,
	refurgency		INT UNSIGNED NOT NULL DEFAULT 0,
	refentered		INT UNSIGNED NOT NULL DEFAULT 0,
	refapptblob		BLOB,
	refdirection		ENUM ( 'inbound', 'outbound' ) DEFAULT 'outbound',
	refpayorapproval	ENUM ( 'unknown', 'denied', 'approved' ) DEFAULT 'unknown',
	refcomorbids		VARCHAR(255),
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

	#	Define keys
	PRIMARY KEY		( id ),

	FOREIGN KEY		( refpatient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS referrals_Upgrade;
DELIMITER //
CREATE PROCEDURE referrals_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER referrals_Delete;
	DROP TRIGGER referrals_Insert;
	DROP TRIGGER referrals_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL referrals_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER referrals_Delete
	AFTER DELETE ON referrals
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='referrals' AND oid=OLD.id;
	END;
//

CREATE TRIGGER referrals_Insert
	AFTER INSERT ON referrals
	FOR EACH ROW BEGIN
		DECLARE rFrom VARCHAR(250);
		DECLARE rTo VARCHAR(250);
		SELECT CONCAT(phyfname, ' ', phylname) INTO rFrom FROM physician WHERE id=NEW.refprovorig;
		SELECT CONCAT(phyfname, ' ', phylname) INTO rTo FROM physician WHERE id=NEW.refprovdest;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user ) VALUES ( 'referrals', NEW.refpatient, NEW.id, NEW.refstamp, CONCAT(rFrom, ' > ', rTo), NEW.user );
	END;
//

CREATE TRIGGER referrals_Update
	AFTER UPDATE ON referrals
	FOR EACH ROW BEGIN
		DECLARE rFrom VARCHAR(250);
		DECLARE rTo VARCHAR(250);
		SELECT CONCAT(phyfname, ' ', phylname) INTO rFrom FROM physician WHERE id=NEW.refprovorig;
		SELECT CONCAT(phyfname, ' ', phylname) INTO rTo FROM physician WHERE id=NEW.refprovdest;
		UPDATE `patient_emr` SET stamp=NEW.refstamp, patient=NEW.refpatient, summary=CONCAT(rFrom, ' > ', rTo), user=NEW.user WHERE module='referrals' AND oid=NEW.id;
	END;
//

DELIMITER ;

