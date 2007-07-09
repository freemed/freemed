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

CREATE TABLE IF NOT EXISTS `scheduler_status` (
	csstamp			TIMESTAMP (14) DEFAULT NOW(),
	cspatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	csappt			INT UNSIGNED NOT NULL DEFAULT 0,
	csnote			VARCHAR (250),
	csstatus		INT UNSIGNED NOT NULL DEFAULT 0,
	csuser			INT UNSIGNED NOT NULL,
	id			SERIAL,

	#	Define keys

	KEY			( cspatient, csstatus, csstamp )
	, FOREIGN KEY		( cspatient ) REFERENCES patient.id ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `scheduler_status_delta` (
	patient			BIGINT UNSIGNED NOT NULL,
	appointment		INT UNSIGNED NOT NULL,
	st_start		INT UNSIGNED,
	st_end			INT UNSIGNED,
	stamp_start		TIMESTAMP (14),
	stamp_end		TIMESTAMP (14),
	duration		INT UNSIGNED,
	id_start		INT UNSIGNED,
	id_end			INT UNSIGNED,
	id			SERIAL

	#	Define keys
	
	, FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS scheduler_status_Upgrade;
DELIMITER //
CREATE PROCEDURE scheduler_status_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER scheduler_status_Delete;
	DROP TRIGGER scheduler_status_Insert;
	DROP TRIGGER scheduler_status_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL scheduler_status_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER scheduler_status_Delete
	AFTER DELETE ON scheduler_status
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='scheduler_status' AND oid=OLD.id;
	END;
//

CREATE TRIGGER scheduler_status_Insert
	AFTER INSERT ON scheduler_status
	FOR EACH ROW BEGIN
		DECLARE f INT UNSIGNED;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user ) VALUES ( 'scheduler_status', NEW.cspatient, NEW.id, NEW.csstamp, NEW.csnote, NEW.csuser );
		#----- Update deltas if they exist
		SELECT scheduler_status_last( NEW.csappt, NEW.id ) INTO f;
		IF f > 0 THEN
			CALL scheduler_status_record_delta( f, NEW.id );
		END IF;
	END;
//

CREATE TRIGGER scheduler_status_Update
	AFTER UPDATE ON scheduler_status
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.csstamp, patient=NEW.cspatient, summary=NEW.csnote, user=NEW.csuser WHERE module='scheduler_status' AND oid=NEW.id;
	END;
//

DELIMITER ;

DROP FUNCTION IF EXISTS scheduler_status_last;

DELIMITER //

CREATE FUNCTION scheduler_status_last ( appt INT UNSIGNED, tid INT UNSIGNED ) RETURNS INT UNSIGNED
	READS SQL DATA
BEGIN
	DECLARE found INT UNSIGNED DEFAULT 0;

	SELECT s.id INTO found FROM scheduler_status s WHERE s.csappt = appt AND s.id <> tid ORDER BY s.id DESC LIMIT 1;
	RETURN found;
END//

DELIMITER ;

DROP PROCEDURE IF EXISTS scheduler_status_record_delta;

DELIMITER //

CREATE PROCEDURE scheduler_status_record_delta ( IN beginId INT UNSIGNED, IN endId INT UNSIGNED )
	CONTAINS SQL
BEGIN
	DECLARE b_status INT UNSIGNED;
	DECLARE e_status INT UNSIGNED;
	DECLARE b_stamp  TIMESTAMP (14);
	DECLARE e_stamp  TIMESTAMP (14);
	DECLARE pt       BIGINT UNSIGNED;
	DECLARE appt     BIGINT UNSIGNED;

	SELECT csstatus INTO b_status FROM scheduler_status WHERE id=beginId;
	SELECT csstamp INTO b_stamp FROM scheduler_status WHERE id=beginId;
	SELECT cspatient INTO pt  FROM scheduler_status WHERE id=beginId;
	SELECT csappt INTO appt FROM scheduler_status WHERE id=beginId;
	SELECT csstamp INTO e_stamp FROM scheduler_status WHERE id=endId;
	SELECT csstatus INTO e_status FROM scheduler_status WHERE id=endId;

	IF beginId > 0 THEN
		INSERT INTO `scheduler_status_delta` ( patient, appointment, st_start, st_end, stamp_start, stamp_end, duration, id_start, id_end ) VALUES ( pt, appt, b_status, e_status, b_stamp, e_stamp, e_stamp - b_stamp, beginId, endId );
	END IF;
END//

DELIMITER ;

