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

SOURCE data/schema/mysql/patient.sql
SOURCE data/schema/mysql/patient_emr.sql
SOURCE data/schema/mysql/systemnotification.sql

CREATE TABLE IF NOT EXISTS `messages` (
	  msgby		INT UNSIGNED
	, msgtime	TIMESTAMP (14) DEFAULT NOW()
	, msgfor	INT UNSIGNED
	, msgrecip	TEXT
	, msgpatient	BIGINT UNSIGNED
	, msgperson	VARCHAR (50)
	, msgurgency	INT UNSIGNED DEFAULT 3
	, msgsubject	VARCHAR (75)
	, msgtext	TEXT
	, msgread	INT UNSIGNED DEFAULT 0
	, msgunique	VARCHAR(32)
	, msgtag	VARCHAR(32)
	, active	ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active'
	, id		SERIAL

	#	Define keys

	, KEY 		( msgfor )
	, FOREIGN KEY	( msgpatient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS messages_Upgrade;
DELIMITER //
CREATE PROCEDURE messages_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER messages_Delete;
	DROP TRIGGER messages_Insert;
	DROP TRIGGER messages_Update;

	#----- Upgrades
	ALTER IGNORE TABLE messages ADD COLUMN active ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' AFTER msgtag;
END
//
DELIMITER ;
CALL messages_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER messages_Delete
	AFTER DELETE ON messages
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='messages' AND oid=OLD.id;
	END;
//

CREATE TRIGGER messages_Insert
	AFTER INSERT ON messages
	FOR EACH ROW BEGIN
		IF NEW.msgpatient > 0 THEN
			INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'messages', NEW.msgpatient, NEW.id, NEW.msgtime, NEW.msgsubject, NEW.msgby, NEW.active );
			SELECT CONCAT( ptlname, ', ', ptfname, ' ', ptmname, ' (', ptid, ')' ) INTO @re FROM patient WHERE id = NEW.msgpatient;
		ELSE
			SET @re = NEW.msgsubject;
		END IF;
		INSERT INTO systemnotification ( stamp, nuser, ntext, nmodule, npatient, naction ) VALUES ( NEW.msgtime, NEW.msgfor, CONCAT( 'Msg RE: ', @re ), 'messages', NEW.msgpatient, 'NEW' );
	END;
//

CREATE TRIGGER messages_Update
	AFTER UPDATE ON messages
	FOR EACH ROW BEGIN
		IF NEW.msgpatient > 0 THEN
		UPDATE `patient_emr` SET stamp=NEW.msgtime, patient=NEW.msgpatient, summary=NEW.msgsubject, user=NEW.msgby, status=NEW.active WHERE module='messages' AND oid=NEW.id;
		END IF;
	END;
//

DELIMITER ;

