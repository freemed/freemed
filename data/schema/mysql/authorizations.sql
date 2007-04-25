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

CREATE TABLE IF NOT EXISTS `authorizations` (
	authdtadd		DATE NOT NULL,
	authdtmod		DATE NOT NULL,
	authpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	authdtbegin		DATE,
	authdtend		DATE,
	authnum			VARCHAR (25),
	authtype		INT UNSIGNED,
	authprov		INT UNSIGNED,
	authprovid		VARCHAR (20),
	authinsco		INT UNSIGNED,
	authvisits		INT UNSIGNED,
	authvisitsused		INT UNSIGNED,
	authvisitsremain	INT UNSIGNED,
	authcomment		VARCHAR (100),
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			SERIAL,

	# Define keys

	KEY			( authpatient, authdtbegin, authdtend ),
	FOREIGN KEY		( authpatient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS authorizations_Upgrade;
DELIMITER //
CREATE PROCEDURE authorizations_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER authorizations_Delete;
	DROP TRIGGER authorizations_Insert;
	DROP TRIGGER authorizations_Update;

	#----- Upgrades
	ALTER TABLE authorizations ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER authcomment;
	ALTER TABLE authorizations ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER user;
END
//
DELIMITER ;
CALL authorizations_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER authorizations_Delete
	AFTER DELETE ON authorizations
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='authorizations' AND oid=OLD.id;
	END;
//

CREATE TRIGGER authorizations_Insert
	AFTER INSERT ON authorizations
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'authorizations', NEW.authpatient, NEW.id, NEW.authdtadd, CONCAT(NEW.authdtbegin,' - ',NEW.authdtend,' (',NEW.authnum,')'), NEW.user, NEW.active );
	END;
//

CREATE TRIGGER authorizations_Update
	AFTER UPDATE ON authorizations
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.authdtmod, patient=NEW.authpatient, summary=CONCAT(NEW.authdtbegin,' - ',NEW.authdtend,' (',NEW.authnum,')'), user=NEW.user, status=NEW.active WHERE module='authorizations' AND oid=NEW.id;
	END;
//

DELIMITER ;

