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

# File: Report - Patient Aging Report

DROP PROCEDURE IF EXISTS report_PatientAgingReport_en_US ;
DELIMITER //

# Function: report_PatientAgingReport_en_US
#
#	Patient aging report.
#
CREATE PROCEDURE report_PatientAgingReport_en_US ( )
BEGIN
	DECLARE done BOOL DEFAULT FALSE;
	DECLARE tPatientId, tCheckFor BIGINT UNSIGNED;
	DECLARE tPracticeId VARCHAR (20);
	DECLARE tPatientName VARCHAR (150);
	DECLARE c_blank CURSOR FOR
		SELECT CONCAT( p.ptlname, ', ', p.ptfname, ' ', IF(LENGTH(p.ptmname)>0, CONCAT(' ', p.ptmname), ''), IF(LENGTH(p.ptsuffix)>0, CONCAT( ' ', p.ptsuffix ), '' ) ), p.ptid, p.id FROM patient p LEFT OUTER JOIN procrec pr ON p.id = pr.procpatient WHERE pr.procbalcurrent > 0 GROUP BY p.id;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = TRUE;

	-- Create temporary table --
	DROP TEMPORARY TABLE IF EXISTS tmp_Aging;
	CREATE TEMPORARY TABLE tmp_Aging (
		  patient_name		VARCHAR (150)
		, practice_id		VARCHAR (20)
		, patient_id		BIGINT UNSIGNED NOT NULL DEFAULT 0
		, aging_0_30		DOUBLE ( 10, 2 ) DEFAULT 0.0
		, aging_31_60		DOUBLE ( 10, 2 ) DEFAULT 0.0
		, aging_61_90		DOUBLE ( 10, 2 ) DEFAULT 0.0
		, aging_91_120		DOUBLE ( 10, 2 ) DEFAULT 0.0
		, aging_120		DOUBLE ( 10, 2 ) DEFAULT 0.0

		, KEY		( patient_id )
	);

	-- Get all patients with an outstanding balance --
	OPEN c_blank;
	WHILE NOT done DO
		FETCH c_blank INTO tPatientName, tPracticeId, tPatientId;
		SELECT COUNT(*) INTO tCheckFor FROM tmp_Aging WHERE patient_id = tPatientId;
		IF tCheckFor < 1 THEN
			INSERT INTO tmp_Aging ( patient_name, practice_id, patient_id ) VALUES ( tPatientName, tPracticeId, tPatientId );
		END IF;
	END WHILE;

	-- Populate 0-30 --
	UPDATE tmp_Aging a SET a.aging_0_30 = ( SELECT SUM( p.procbalcurrent ) FROM procrec p WHERE p.procpatient = a.patient_id AND ( TO_DAYS( NOW() ) - TO_DAYS( p.procdt ) <= 30 ) GROUP BY p.procpatient );

	-- Populate 31-60 --
	UPDATE tmp_Aging a SET a.aging_31_60 = ( SELECT SUM( p.procbalcurrent ) FROM procrec p WHERE p.procpatient = a.patient_id AND ( TO_DAYS( NOW() ) - TO_DAYS( p.procdt ) >= 31 ) AND ( TO_DAYS( NOW() ) - TO_DAYS( p.procdt ) <= 60 ) GROUP BY p.procpatient );

	-- Populate 61-90 --
	UPDATE tmp_Aging a SET a.aging_61_90 = ( SELECT SUM( p.procbalcurrent ) FROM procrec p WHERE p.procpatient = a.patient_id AND ( TO_DAYS( NOW() ) - TO_DAYS( p.procdt ) >= 61 ) AND ( TO_DAYS( NOW() ) - TO_DAYS( p.procdt ) <= 90 ) GROUP BY p.procpatient );

	-- Populate 91-120 --
	UPDATE tmp_Aging a SET a.aging_91_120 = ( SELECT SUM( p.procbalcurrent ) FROM procrec p WHERE p.procpatient = a.patient_id AND ( TO_DAYS( NOW() ) - TO_DAYS( p.procdt ) >= 91 ) AND ( TO_DAYS( NOW() ) - TO_DAYS( p.procdt ) <= 120 ) GROUP BY p.procpatient );

	-- Populate 120+ --
	UPDATE tmp_Aging a SET a.aging_120 = ( SELECT SUM( p.procbalcurrent ) FROM procrec p WHERE p.procpatient = a.patient_id AND ( TO_DAYS( NOW() ) - TO_DAYS( p.procdt ) >= 120 ) GROUP BY p.procpatient );

	-- Get rid of nulls --
	UPDATE tmp_Aging SET aging_0_30 = 0.0 WHERE ISNULL( aging_0_30 );
	UPDATE tmp_Aging SET aging_31_60 = 0.0 WHERE ISNULL( aging_31_60 );
	UPDATE tmp_Aging SET aging_61_90 = 0.0 WHERE ISNULL( aging_61_90 );
	UPDATE tmp_Aging SET aging_91_120 = 0.0 WHERE ISNULL( aging_91_120 );
	UPDATE tmp_Aging SET aging_120 = 0.0 WHERE ISNULL( aging_120 );

	-- Output back to reporting engine --
	SELECT
		  patient_name AS 'Patient Name'
		, practice_id AS 'Patient ID'
		, aging_0_30 AS '0-30' 
		, aging_31_60 AS '31-60' 
		, aging_61_90 AS '61-90' 
		, aging_91_120 AS '91-120' 
		, aging_120 AS '120+' 
	FROM tmp_Aging;

	-- Cleanup --
	DROP TEMPORARY TABLE IF EXISTS tmp_Aging;	
END //

DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_PatientAgingReport_en_US';

INSERT INTO `reporting` (
		report_name,
		report_uuid,
		report_locale,
		report_desc,
		report_category,
		report_sp,
		report_param_count,
		report_param_names,
		report_param_types,
		report_param_options,
		report_param_optional
	) VALUES (
		'Patient Aging Report',
		'b05bfa37-6f0b-47e7-8920-94ea92a5932e',
		'en_US',
		'Patient aging',
		'reporting_engine',
		'report_PatientAgingReport_en_US',
		0,
		'',
		'',
		'',
		''
	);

