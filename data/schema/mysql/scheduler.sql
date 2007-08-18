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
SOURCE data/schema/mysql/workflow_status.sql

CREATE TABLE IF NOT EXISTS `scheduler` (
	caldateof		DATE,
	calcreated		TIMESTAMP (16),
	calmodified		TIMESTAMP (16),
	caltype			ENUM( 'temp', 'pat', 'block' ) NOT NULL DEFAULT 'pat',
	calhour			INT UNSIGNED,
	calminute		INT UNSIGNED,
	calduration		INT UNSIGNED,
	calfacility		INT UNSIGNED,
	calroom			INT UNSIGNED,
	calphysician		INT UNSIGNED,
	calpatient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	calcptcode		INT UNSIGNED,
	calstatus		ENUM ( 'scheduled', 'confirmed', 'attended', 'cancelled', 'noshow', 'tenative' ) NOT NULL DEFAULT 'scheduled',
	calprenote		VARCHAR (250),
	calpostnote		TEXT,
	calmark			INT UNSIGNED NOT NULL DEFAULT 0,
	calgroupid		INT UNSIGNED NOT NULL DEFAULT 0,
	calrecurnote		VARCHAR (100),
	calrecurid		INT UNSIGNED NOT NULL DEFAULT 0,
	calappttemplate		INT UNSIGNED NOT NULL DEFAULT 0,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	id			SERIAL,

	# Define keys

	KEY			( caldateof, calhour, calminute )
	, KEY			( calpatient )
);

DROP PROCEDURE IF EXISTS scheduler_Upgrade;
DELIMITER //
CREATE PROCEDURE scheduler_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER scheduler_Delete;
	DROP TRIGGER scheduler_Insert;
	DROP TRIGGER scheduler_Update;

	#----- Upgrades
        CALL FreeMED_Module_GetVersion( 'scheduler', @V );

        # Version 1
        IF @V < 1 THEN
		#	Version 0.6.3
		ALTER IGNORE TABLE scheduler ADD COLUMN calgroupid INT UNSIGNED NOT NULL DEFAULT 0 AFTER calmark;
		ALTER IGNORE TABLE scheduler ADD COLUMN calrecurnote VARCHAR (100) AFTER calgroupid;
		ALTER IGNORE TABLE scheduler ADD COLUMN calrecurid INT UNSIGNED NOT NULL DEFAULT 0 AFTER calrecurnote;
		ALTER IGNORE TABLE scheduler CHANGE COLUMN caltype caltype ENUM ( 'temp', 'pat', 'block' );
		ALTER IGNORE TABLE scheduler CHANGE COLUMN calstatus calstatus ENUM ( 'scheduled', 'confirmed', 'attended', 'cancelled', 'noshow', 'tenative' ) NOT NULL DEFAULT 'scheduled';

		#	Version 0.6.3.1
		ALTER IGNORE TABLE scheduler CHANGE COLUMN calprenote calprenote VARCHAR (250);

		#	Version 0.6.5
		ALTER IGNORE TABLE scheduler ADD COLUMN calcreated TIMESTAMP (16) AFTER caldateof;
		ALTER IGNORE TABLE scheduler ADD COLUMN calmodified TIMESTAMP (16) AFTER calcreated;

		#	Version 0.6.6
		ALTER IGNORE TABLE scheduler ADD COLUMN calappttemplate INT UNSIGNED NOT NULL DEFAULT 0 AFTER calrecurid;

		ALTER IGNORE TABLE scheduler ADD COLUMN user INT UNSIGNED NOT NULL DEFAULT 0 AFTER calappttemplate;
	END IF;

	CALL FreeMED_Module_UpdateVersion( 'scheduler', 2 );
END
//
DELIMITER ;
CALL scheduler_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER scheduler_Delete
	AFTER DELETE ON scheduler
	FOR EACH ROW BEGIN
		IF OLD.caltype = 'pat' THEN
			DELETE FROM `patient_emr` WHERE module='scheduler' AND oid=OLD.id;
			DELETE FROM `workflow_status` WHERE DATE_FORMAT( stamp, '%Y-%m-%d' ) = OLD.caldateof AND patient = OLD.calpatient;
			DELETE FROM `workflow_status_summary` WHERE DATE_FORMAT( stamp, '%Y-%m-%d' ) = OLD.caldateof AND patient = OLD.calpatient;
		END IF;
	END;
//

CREATE TRIGGER scheduler_Insert
	AFTER INSERT ON scheduler
	FOR EACH ROW BEGIN
		IF NEW.caltype = 'pat' THEN
			INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user ) VALUES ( 'scheduler', NEW.calpatient, NEW.id, NEW.caldateof, CONCAT( LPAD( NEW.calhour, 2, '0' ), ':', LPAD( NEW.calminute, 2, '0' ), ' (', NEW.calduration, 'm) - ', NEW.calprenote ), NEW.user );
			CALL patientWorkflowUpdateStatus( NEW.calpatient, NEW.caldateof, 'scheduler', TRUE, NEW.user );
		END IF;
	END;
//

CREATE TRIGGER scheduler_Update
	AFTER UPDATE ON scheduler
	FOR EACH ROW BEGIN
		IF NEW.caltype = 'pat' THEN
			UPDATE `patient_emr` SET stamp=NEW.caldateof, patient=NEW.calpatient, summary=CONCAT( LPAD( NEW.calhour, 2, '0' ), ':', LPAD( NEW.calminute, 2, '0' ), ' (', NEW.calduration, 'm) - ', NEW.calprenote ), user=NEW.user WHERE module='scheduler' AND oid=NEW.id;
			#	Mark this depending on how we're dealing with this
			IF FIND_IN_SET( NEW.calstatus, 'noshow,cancelled' ) THEN
				CALL patientWorkflowUpdateStatus( NEW.calpatient, NEW.caldateof, 'scheduler', FALSE, NEW.user );
			ELSE
				CALL patientWorkflowUpdateStatus( NEW.calpatient, NEW.caldateof, 'scheduler', TRUE, NEW.user );
			END IF;
			CALL patientWorkflowStatusUpdateLookup ( NEW.calpatient, NEW.caldateof );
			IF NEW.caldateof != OLD.caldateof THEN
				CALL patientWorkflowStatusUpdateLookup ( NEW.calpatient, OLD.caldateof );
			END IF;
		END IF;
	END;
//

DELIMITER ;

DROP PROCEDURE IF EXISTS schedulerGenerateDailySchedule;

DELIMITER //

# Procedure: schedulerGenerateDailySchedule
#
#	Create a full appointment scheduler for a date, including
#	slots which are not filled by appointments.
#
# Parameters:
#
#	dt - Scheduler date ( DATE )
#
#	hStart - Starting scheduler hour ( INT UNSIGNED )
#
#	hEnd - Ending scheduler hour ( INT UNSIGNED )
#
#	ival - Interval in minutes between slots ( INT UNSIGNED )
#
#	prov - (optional) Provider id number or 0 ( INT UNSIGNED )
#
CREATE PROCEDURE schedulerGenerateDailySchedule (
	  IN dt DATE
	, IN hStart INT UNSIGNED
	, IN hEnd INT UNSIGNED
	, IN ival INT UNSIGNED
	, IN prov INT UNSIGNED
)
BEGIN
	DECLARE i, dTime TIME;
	DECLARE dLoop INT;

	-- Create cursor for scheduler events --
	DECLARE done BOOL DEFAULT FALSE;
	DECLARE tI, tH, tM, tD, tP, tPat INT;
	DECLARE tT ENUM ( 'pat', 'temp', 'block' );
	DECLARE tN VARCHAR (250);
	DECLARE cur CURSOR FOR SELECT c.id, c.calhour, c.calminute, c.calduration, c.calphysician, c.caltype, CASE c.caltype WHEN 'block' THEN '-' WHEN 'temp' THEN CONCAT( '[!] ', ci.cilname, ', ', ci.cifname, ' (', ci.cicomplaint, ')' ) ELSE CONCAT(p.ptlname, ', ', p.ptfname, ' (', p.ptid, ')') END, c.calpatient FROM scheduler c LEFT OUTER JOIN patient p ON c.calpatient = p.id LEFT OUTER JOIN callin ci ON c.calpatient = ci.id LEFT OUTER JOIN scheduler_status ss ON c.id = ss.csappt LEFT OUTER JOIN schedulerstatustype st ON st.id = ss.csstatus LEFT OUTER JOIN physician ph ON c.calphysician = ph.id WHERE caldateof = dt AND calhour >= hStart AND calhour < hEnd;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = TRUE;

	-- Create blank scheduler --
	DROP TEMPORARY TABLE IF EXISTS schedTable;
	CREATE TEMPORARY TABLE schedTable (
		  h INT
		, m INT
		, type ENUM ( 'pat', 'temp', 'block' ) NOT NULL
		, cont BOOL DEFAULT FALSE
		, id INT UNSIGNED DEFAULT 0
		, patient_id INT UNSIGNED DEFAULT 0
		, descrip VARCHAR (250) DEFAULT ''
		, duration INT UNSIGNED DEFAULT 0
	);
	SET i := MAKETIME( hStart, 0, 0 );
	WHILE TIME_TO_SEC( i ) < TIME_TO_SEC( MAKETIME( hEnd, 0, 0 ) ) DO
		INSERT INTO schedTable ( h, m ) VALUES ( HOUR( i ), MINUTE( i ) );
		SET i = SEC_TO_TIME( TIME_TO_SEC( i ) + ( ival * 60 ) );
	END WHILE;

	-- Get scheduler entries --
	OPEN cur;
	WHILE NOT done DO
		FETCH cur INTO tI, tH, tM, tD, tP, tT, tN, tPat;
		IF ( prov = 0 OR ( prov > 0 AND ( tP = prov ) ) ) THEN
			-- Create initial entry --
			UPDATE schedTable SET id = tI, descrip = tN, type = tT, patient_id = tPat, duration = tD WHERE h = tH AND m = tM;
			-- If duration is more than interval, handle con't --
			SET dTime = MAKETIME( tH, tM, 0 );
			IF tD > ival THEN
				SET dLoop = tD - ival;
				WHILE ( dLoop >= ival ) DO
					-- Determine temporary time increase --
					SET dTime = SEC_TO_TIME( TIME_TO_SEC( dTime ) + ( ival * 60  ) );
					-- Insert "continuation" entry --
					UPDATE schedTable SET id = tI, descrip = CONCAT( tN, " (con't)" ), type = tT, cont = TRUE, patient_id = tPat WHERE h = HOUR( dTime ) AND m = MINUTE( dTime );

					-- Decrease amount left for next iteration --
					SET dLoop = dLoop - ival;
				END WHILE;
			END IF;
		END IF;
	END WHILE;
	CLOSE cur;

	-- Output --
	SELECT * FROM schedTable;

	-- Cleanup --
	DROP TEMPORARY TABLE schedTable;
END;
//

DELIMITER ;

