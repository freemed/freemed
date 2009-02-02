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
SOURCE data/schema/mysql/workflow_status.sql
SOURCE data/schema/mysql/schedulingrules.sql

CREATE TABLE IF NOT EXISTS `scheduler` (
	caldateof		DATE,
	calcreated		TIMESTAMP (16),
	calmodified		TIMESTAMP (16),
	caltype			ENUM( 'temp', 'pat', 'block', 'group' ) NOT NULL DEFAULT 'pat',
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
	calgroupmembers		TEXT,
	calrecurnote		VARCHAR (100),
	calrecurid		INT UNSIGNED NOT NULL DEFAULT 0,
	calappttemplate		INT UNSIGNED NOT NULL DEFAULT 0,
	calattendees		VARCHAR (250),
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	id			SERIAL

	# Define keys

	, KEY			( caldateof, calhour, calminute )
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

        # Version 2
        IF @V < 2 THEN
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

        # Version 3
	IF @V < 3 THEN
		#	Patch for attendees and "group" scheduling, since all other
		#	group scheduling pieces are in here from a legacy version.
		ALTER TABLE scheduler CHANGE COLUMN caltype caltype ENUM( 'temp', 'pat', 'block', 'group' ) NOT NULL DEFAULT 'pat';
		ALTER TABLE scheduler ADD COLUMN calattendees VARCHAR (250) AFTER calappttemplate;
		ALTER TABLE scheduler ADD COLUMN calgroupmembers TEXT AFTER calgroupid;
	END IF;

	CALL FreeMED_Module_UpdateVersion( 'scheduler', 3 );
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
	DECLARE f, dLoop, c INT;

	-- Create cursor for scheduler events --
	DECLARE done BOOL DEFAULT FALSE;
	DECLARE block TEXT;
	DECLARE tI, tH, tM, tD, tP, tPat INT;
	DECLARE tT ENUM ( 'pat', 'temp', 'block' );
	DECLARE tN, tPatName, tPName VARCHAR (250);
	DECLARE tStatus, tStatusColor VARCHAR (10);
	DECLARE tApptTime CHAR (5);
	DECLARE cur CURSOR FOR
		SELECT 
			c.id
			, c.calhour
			, c.calminute
			, c.calduration
			, c.calphysician
			, CONCAT( ph.phylname, ', ', ph.phyfname )
			, c.caltype
			, CASE c.caltype WHEN 'block' THEN '-' WHEN 'temp' THEN CONCAT( '[!] ', ci.cilname, ', ', ci.cifname, ' (', ci.cicomplaint, ')' ) ELSE CONCAT(p.ptlname, ', ', p.ptfname,  IF(LENGTH(p.ptmname)>0,CONCAT(' ',p.ptmname),'')
			, IF(LENGTH(p.ptsuffix)>0,CONCAT(' ',p.ptsuffix),''), ' (', p.ptid, ')') END
			, c.calpatient
			, c.calprenote
			, SUBSTRING_INDEX(GROUP_CONCAT(st.sname), ',', -1)
			, SUBSTRING_INDEX(GROUP_CONCAT(st.scolor), ',', -1)
		FROM scheduler c
			LEFT OUTER JOIN patient p ON c.calpatient = p.id
			LEFT OUTER JOIN callin ci ON c.calpatient = ci.id
			LEFT OUTER JOIN scheduler_status ss ON c.id = ss.csappt
			LEFT OUTER JOIN schedulerstatustype st ON st.id = ss.csstatus
			LEFT OUTER JOIN physician ph ON c.calphysician = ph.id
		WHERE
			caldateof = dt
			AND calhour >= hStart
			AND calhour < hEnd
			AND c.calstatus NOT IN ( 'noshow', 'cancelled' )
		GROUP BY c.id, ss.csappt;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = TRUE;

	-- Create blank scheduler --
	DROP TEMPORARY TABLE IF EXISTS schedTable;
	CREATE TEMPORARY TABLE schedTable (
		  apptTime CHAR (5)
		, h INT
		, m INT
		, type ENUM ( 'pat', 'temp', 'block' ) NOT NULL
		, cont BOOL DEFAULT FALSE
		, id INT UNSIGNED DEFAULT 0
		, provider_id INT UNSIGNED DEFAULT 0
		, provider VARCHAR (250) DEFAULT ''
		, patient_id INT UNSIGNED DEFAULT 0
		, patient VARCHAR (250) DEFAULT ''
		, note VARCHAR (250) DEFAULT ''
		, duration INT UNSIGNED DEFAULT 0
		, status VARCHAR (10) DEFAULT ''
		, status_color VARCHAR (10) DEFAULT ''
	);
	SET i := MAKETIME( hStart, 0, 0 );
	WHILE TIME_TO_SEC( i ) < TIME_TO_SEC( MAKETIME( hEnd, 0, 0 ) ) DO
		-- Create blank entry --
		INSERT INTO schedTable ( apptTime, h, m ) VALUES (
			  CONCAT( LPAD(HOUR(i),2,'0'), ':', LPAD(MINUTE(i),2,'0') )
			, HOUR( i )
			, MINUTE( i )
		);

		-- Check for provider blocking --
		CALL checkSchedulingRulesInternal(
			  0
			, IFNULL( prov, 0 )
			, dt
			, i
			, block
		);
		IF NOT ISNULL( block ) THEN
			UPDATE schedTable SET
				note = block,
				type = 'block',
				duration = ival
			WHERE h = HOUR( i ) AND m = MINUTE( i );
		END IF;

		-- Increment to next time slot --
		SET i = SEC_TO_TIME( TIME_TO_SEC( i ) + ( ival * 60 ) );
	END WHILE;

	-- Get scheduler entries --
	OPEN cur;
	WHILE NOT done DO
		FETCH cur INTO tI, tH, tM, tD, tP, tPName, tT, tPatName, tPat, tN, tStatus, tStatusColor;
		-- Make sure we don't process twice for any reason --
		SELECT COUNT(*) INTO c FROM schedTable WHERE id = tI;
		IF c < 1 THEN
			IF ( prov = 0 OR ( prov > 0 AND ( tP = prov ) ) ) THEN
				-- Create initial entry --
				SELECT id INTO f FROM schedTable WHERE h = tH AND m = tM LIMIT 1;
				IF f > 0 THEN
					-- Insert overbooking value --
					INSERT INTO schedTable ( apptTime, h, m, id, note, type, provider, provider_id, patient, patient_id, duration, status, status_color, cont ) VALUES ( CONCAT( LPAD(tH,2,'0'), ':', LPAD(tM,2,'0') ), tH, tM, tI, tN, tT, tPName, tP, tPatName, tPat, tD, tStatus, tStatusColor, TRUE );
				ELSE
					-- Update existing entry --
					UPDATE schedTable SET id = tI, note = tN, type = tT, patient = tPatName, patient_id = tPat, duration = tD, status = tStatus, status_color = tStatusColor, provider = tPName, provider_id = tP WHERE h = tH AND m = tM;
				END IF;
	
				-- If duration is more than interval, handle con't --
				SET dTime = MAKETIME( tH, tM, 0 );
				IF tD > ival THEN
					SET dLoop = tD - ival;
					WHILE ( dLoop >= ival ) DO
						-- Determine temporary time increase --
						SET dTime = SEC_TO_TIME( TIME_TO_SEC( dTime ) + ( ival * 60  ) );
						-- Insert "continuation" entry --
						SELECT id INTO f FROM schedTable WHERE h = HOUR( dTime ) AND m = MINUTE( dTime ) LIMIT 1;
						IF f > 0 THEN
							-- Insert overbooking value --
							INSERT INTO schedTable ( apptTime, h, m, id, note, type, provider, provider_id, patient, patient_id, status, status_color, cont ) VALUES ( CONCAT( LPAD(HOUR(dTime),2,'0'), ':', LPAD(MINUTE(dTime),2,'0') ), HOUR( dTime ), MINUTE( dTime ), tI, tN, tT, tPName, tP, CONCAT( tPatName, " (con't)"), tPat, tStatus, tStatusColor, TRUE );
						ELSE
							-- Update existing entry --
							UPDATE schedTable SET id = tI, note = tN, patient = CONCAT( tPatName, " (con't)" ), type = tT, cont = FALSE, patient_id = tPat, provider = tPName, provider_id = tP WHERE h = HOUR( dTime ) AND m = MINUTE( dTime );
						END IF;
	
						-- Decrease amount left for next iteration --
						SET dLoop = dLoop - ival;
					END WHILE;
				END IF;
			END IF;
		END IF;
	END WHILE;
	CLOSE cur;

	-- Output --
	SELECT
		  apptTime AS appointment_time
		, h AS hour
		, m AS minute
		, type AS resource_type
		, cont
		, id AS scheduler_id
		, patient_id
		, patient
		, provider_id
		, provider 
		, note
		, duration
		, status
		, status_color
	FROM schedTable
	ORDER BY apptTime, id;

	-- Cleanup --
	DROP TEMPORARY TABLE schedTable;
END;
//

DELIMITER ;

