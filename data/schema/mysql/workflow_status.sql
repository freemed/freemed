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

CREATE TABLE IF NOT EXISTS `workflow_status` (
	stamp			TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	patient			BIGINT UNSIGNED NOT NULL DEFAULT 0,
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	status_type		INT UNSIGNED NOT NULL,
	status_completed	BOOL DEFAULT FALSE,
	id			SERIAL,

	#	Define keys

	FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `workflow_status_summary` (
	stamp			TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	patient			BIGINT UNSIGNED NOT NULL DEFAULT 0,
	completed		TEXT,
	uncompleted		TEXT,
	id			SERIAL,

	#	Define keys

	FOREIGN KEY		( patient ) REFERENCES patient.id ON DELETE CASCADE
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS patientWorkflowStatusUpdateLookup;

DELIMITER //
CREATE PROCEDURE patientWorkflowStatusUpdateLookup ( IN pt INT UNSIGNED, IN dt DATE )
BEGIN
	DECLARE c TEXT;
	DECLARE u TEXT;
	DECLARE found BOOL;

	SELECT GROUP_CONCAT( status_type ) INTO c FROM workflow_status WHERE patient = pt AND DATE_FORMAT( stamp, '%Y-%m-%d' ) = dt AND status_completed = TRUE GROUP BY patient;
	SELECT GROUP_CONCAT( status_type ) INTO u FROM workflow_status WHERE patient = pt AND DATE_FORMAT( stamp, '%Y-%m-%d' ) = dt AND status_completed = TRUE GROUP BY patient;

	#----- See if lookup already exists, adjust query accordingly
	SELECT COUNT(*) > 0 INTO found FROM workflow_status_summary WHERE patient = pt AND DATE_FORMAT( stamp, '%Y-%m-%d' ) = dt;

	IF found THEN
		UPDATE workflow_status_summary SET completed = c, uncompleted = u WHERE patient = pt AND DATE_FORMAT( stamp, '%Y-%m-%d' ) = dt;
	ELSE
		INSERT INTO workflow_status_summary ( stamp, patient, completed, uncompleted ) VALUES ( dt, pt, c, u );
	END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS workflow_status_Upgrade;
DELIMITER //
CREATE PROCEDURE workflow_status_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Triggers
	DROP TRIGGER workflow_status_Delete;
	DROP TRIGGER workflow_status_Insert;
	DROP TRIGGER workflow_status_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL workflow_status_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER workflow_status_Delete
	AFTER DELETE ON workflow_status
	FOR EACH ROW BEGIN
		CALL patientWorkflowStatusUpdateLookup( OLD.patient, DATE_FORMAT( OLD.stamp, '%Y-%m-%d' ) );
	END;
//

CREATE TRIGGER workflow_status_Insert
	AFTER INSERT ON workflow_status
	FOR EACH ROW BEGIN
		CALL patientWorkflowStatusUpdateLookup( NEW.patient, DATE_FORMAT( NEW.stamp, '%Y-%m-%d' ) );
	END;
//

CREATE TRIGGER workflow_status_Update
	AFTER UPDATE ON workflow_status
	FOR EACH ROW BEGIN
		CALL patientWorkflowStatusUpdateLookup( NEW.patient, DATE_FORMAT( NEW.stamp, '%Y-%m-%d' ) );
	END;
//

DELIMITER ;

#----- Aggregation functions

DROP PROCEDURE IF EXISTS patientWorkflowStatusByDate;

DELIMITER //
CREATE PROCEDURE patientWorkflowStatusByDate ( IN dt DATE )
BEGIN
	DECLARE t_id INT UNSIGNED;
	DECLARE t_status_name VARCHAR (250);
	DECLARE t_status_module VARCHAR (250);
	
	DECLARE fClause TEXT;
	DECLARE fClause_tmp TEXT;
	DECLARE done INT DEFAULT 0;
	DECLARE cur CURSOR FOR
		SELECT id, status_name, status_module FROM workflow_status_type WHERE active = TRUE ORDER BY status_order;

	#	Handle SQL exceptions and bad states
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	OPEN cur;
	FETCH cur INTO t_id, t_status_name, t_status_module;
	SET fClause = '';
	WHILE NOT done DO
		SET fClause_tmp = fClause;
		SET fClause = CONCAT( fClause_tmp, ", CASE FIND_IN_SET( w.completed, '", t_id, "' ) WHEN TRUE THEN TRUE ELSE FALSE END AS '", t_status_module, "' " );
		FETCH cur INTO t_id, t_status_name, t_status_module;
	END WHILE;
	CLOSE cur;
	SET @sql = CONCAT(
		"SELECT CONCAT( p.ptlname, ', ', p.ptfname, ' ', p.ptmname, ' (', p.ptid, ')' ) AS patient, w.patient AS patient_id ", fClause, " FROM workflow_status_summary w LEFT OUTER JOIN patient p ON w.patient=p.id WHERE DATE_FORMAT( stamp, '%Y-%m-%d' ) = '", dt, "'"
	) ;

	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;
END//
DELIMITER ;

