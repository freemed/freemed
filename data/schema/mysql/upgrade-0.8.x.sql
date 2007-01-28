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

#----------------------------------------------------------------------------
#	Allergies (allergies)
#----------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS upgrade_08x_allergies;
DELIMITER //
CREATE PROCEDURE upgrade_08x_allergies ( )
BEGIN
	#	Holding variables
	DECLARE t_id INT DEFAULT 0;
	DECLARE t_stamp TIMESTAMP (16);
	DECLARE t_summary VARCHAR (250) DEFAULT '';
	DECLARE t_patient BIGINT DEFAULT 0;

	DECLARE done INT DEFAULT 0;
	DECLARE cur CURSOR FOR
		SELECT id, patient, reviewed, allergy FROM allergies;

	#	Handle SQL exceptions and bad states
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	OPEN cur;
	FETCH cur INTO t_id, t_patient, t_stamp, t_summary;
	WHILE NOT done DO
		DELETE FROM `patient_emr` WHERE module='allergies' AND oid=t_id;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'allergies', t_patient, t_id, t_stamp, t_summary );
		FETCH cur INTO t_id, t_patient, t_stamp, t_summary;
	END WHILE;
	CLOSE cur;

END//

DELIMITER ;

#----------------------------------------------------------------------------
#	Progress Notes (pnotes)
#----------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS upgrade_08x_pnotes;
DELIMITER //
CREATE PROCEDURE upgrade_08x_pnotes ( )
BEGIN
	#	Holding variables
	DECLARE t_id INT DEFAULT 0;
	DECLARE t_stamp TIMESTAMP (16);
	DECLARE t_summary VARCHAR (250) DEFAULT '';
	DECLARE t_patient BIGINT DEFAULT 0;

	DECLARE done INT DEFAULT 0;
	DECLARE cur CURSOR FOR
		SELECT id, pnotespat, pnotesdt, pnotesdescrip FROM pnotes;

	#	Handle SQL exceptions and bad states
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	OPEN cur;
	FETCH cur INTO t_id, t_patient, t_stamp, t_summary;
	WHILE NOT done DO
		DELETE FROM `patient_emr` WHERE module='pnotes' AND oid=t_id;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'pnotes', t_patient, t_id, t_stamp, t_summary );
		FETCH cur INTO t_id, t_patient, t_stamp, t_summary;
	END WHILE;
	CLOSE cur;

END//

DELIMITER ;

#----------------------------------------------------------------------------
#	Procedure Record (procrec)
#----------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS upgrade_08x_procrec;
DELIMITER //
CREATE PROCEDURE upgrade_08x_procrec ( )
BEGIN
	#	Holding variables
	DECLARE t_id INT DEFAULT 0;
	DECLARE t_stamp TIMESTAMP (16);
	DECLARE t_summary VARCHAR (250) DEFAULT '';
	DECLARE t_patient BIGINT DEFAULT 0;

	DECLARE done INT DEFAULT 0;
	DECLARE cur CURSOR FOR
		SELECT p.id, p.procpatient, p.procdt, CONCAT(c.cptcode, ' - ', c.cptnameint) FROM procrec p LEFT OUTER JOIN cpt c ON c.id = p.proccpt;

	#	Handle SQL exceptions and bad states
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	OPEN cur;
	FETCH cur INTO t_id, t_patient, t_stamp, t_summary;
	WHILE NOT done DO
		DELETE FROM `patient_emr` WHERE module='procrec' AND oid=t_id;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'procrec', t_patient, t_id, t_stamp, t_summary );
		FETCH cur INTO t_id, t_patient, t_stamp, t_summary;
	END WHILE;
	CLOSE cur;

END//

DELIMITER ;

#----------------------------------------------------------------------------
#	Prescription (rx)
#----------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS upgrade_08x_rx;
DELIMITER //
CREATE PROCEDURE upgrade_08x_rx ( )
BEGIN
	#	Holding variables
	DECLARE t_id INT DEFAULT 0;
	DECLARE t_stamp TIMESTAMP (16);
	DECLARE t_summary VARCHAR (250) DEFAULT '';
	DECLARE t_patient BIGINT DEFAULT 0;

	DECLARE done INT DEFAULT 0;
	DECLARE cur CURSOR FOR
		SELECT id, rxpatient, rxdtadd, rxdrug FROM rx;

	#	Handle SQL exceptions and bad states
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	OPEN cur;
	FETCH cur INTO t_id, t_patient, t_stamp, t_summary;
	WHILE NOT done DO
		DELETE FROM `patient_emr` WHERE module='rx' AND oid=t_id;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'rx', t_patient, t_id, t_stamp, t_summary );
		FETCH cur INTO t_id, t_patient, t_stamp, t_summary;
	END WHILE;
	CLOSE cur;

END//

DELIMITER ;

#----------------------------------------------------------------------------
#	Scheduler (scheduler)
#----------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS upgrade_08x_scheduler;
DELIMITER //
CREATE PROCEDURE upgrade_08x_scheduler ( )
BEGIN
	#	Holding variables
	DECLARE t_id INT DEFAULT 0;
	DECLARE t_stamp TIMESTAMP (16);
	DECLARE t_summary VARCHAR (250) DEFAULT '';
	DECLARE t_patient BIGINT DEFAULT 0;

	DECLARE done INT DEFAULT 0;
	DECLARE cur CURSOR FOR
		SELECT id, calpatient, caldateof, calprenote FROM procrec;

	#	Handle SQL exceptions and bad states
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

	OPEN cur;
	FETCH cur INTO t_id, t_patient, t_stamp, t_summary;
	WHILE NOT done DO
		DELETE FROM `patient_emr` WHERE module='scheduler' AND oid=t_id;
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary ) VALUES ( 'scheduler', t_patient, t_id, t_stamp, t_summary );
		FETCH cur INTO t_id, t_patient, t_stamp, t_summary;
	END WHILE;
	CLOSE cur;

END//

DELIMITER ;

#----------------------------------------------------------------------------
#	Execute stored PROCEDUREs
#----------------------------------------------------------------------------

LOCK TABLES;
CALL upgrade_08x_allergies ( );
CALL upgrade_08x_pnotes ( );
CALL upgrade_08x_procrec ( );
CALL upgrade_08x_rx ( );
CALL upgrade_08x_scheduler ( );
UNLOCK TABLES;

