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

# File: Monthly Client Balance Due

DROP PROCEDURE IF EXISTS report_MonthlyClientBalanceDue_en_US;
DELIMITER //

# Function: report_MonthlyClientBalanceDue_en_US
#
#	Monthly Client Balance Due
#

CREATE PROCEDURE report_MonthlyClientBalanceDue_en_US ( IN endDate DATE,IN facID INT)
BEGIN
	SET @sql = CONCAT(		
		"SELECT ",
			"(SELECT pracname from practice ORDER BY id LIMIT 1) as practice, ",
			"IFNULL(i.id,0) AS insid,"
			"IFNULL(i.insconame,'Not Assigned To Third Party') AS Source, ",
			"pt.id AS patientnum,",
			"CONCAT(SUBSTRING(pt.ptlname FROM 1 FOR 3),', ',SUBSTRING(pt.ptfname FROM 1 FOR 1),' ',SUBSTRING(pt.ptmname FROM 1 FOR 1)) AS patientname, ",
			"f.psrname AS 'Facility Name', ",
			"CASE WHEN (td.tddischargedt IS NULL OR tii.admitdate>td.tddischargedt)",
				" THEN 'A' ELSE 'D' END as status, ",
			"CASE WHEN (td.tddischargedt IS NULL OR tii.admitdate>td.tddischargedt)",
				" THEN IF(SUM(p.procbalcurrent)>=0,SUM(p.procbalcurrent),0) ELSE 0 END as Debit, ",
			"CASE WHEN (td.tddischargedt IS NULL OR tii.admitdate>td.tddischargedt)",
				" THEN IF(SUM(p.procbalcurrent)<0,ABS(SUM(p.procbalcurrent)),0) ",
				" ELSE 0 END as Credit, ",
			"CASE WHEN (td.tddischargedt IS NULL OR tii.admitdate>td.tddischargedt) ",
				"THEN 0 ELSE SUM(p.procbalcurrent) END as Balance, ",
			"CASE WHEN (td.tddischargedt IS NULL OR tii.admitdate>td.tddischargedt) ",
				"THEN '' ELSE td.tddischargedt END as dchDate ",
		"FROM facility f,procrec p ",
			"LEFT OUTER JOIN patient pt ON pt.id = p.procpatient ",
			"LEFT OUTER JOIN coverage c ON c.id = p.proccurcovid  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
			"LEFT OUTER JOIN treatment_discharge td ON td.patient=p.procpatient AND td.tddischargedt=",
				"(SELECT MAX(tddischargedt) from treatment_discharge where p.procpatient)",
			"LEFT OUTER JOIN treatment_initial_intake tii ON tii.patient=p.procpatient AND tii.admitdate=",
				"(SELECT MAX(admitdate) from treatment_initial_intake where p.procpatient)",
		"WHERE ",
			"p.procdt<='",endDate,"' AND ",
			"p.procpos=",facID," AND ",
			"p.procpos=f.id ",
			"GROUP BY p.procpatient ORDER BY insid,patientnum"
	);
	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;

END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_MonthlyClientBalanceDue_en_US';
INSERT INTO `reporting` (
		report_name,
		report_uuid,
		report_locale,
		report_desc,
		report_type,
		report_category,
		report_sp,
		report_param_count,
		report_param_names,
		report_param_types,
		report_param_optional,
		report_formatting
	) VALUES (
		'Monthly Client Balance Due',
		'2f2503e3-9441-49cc-8a23-620b829e44f9',
		'en_US',
		'Monthly Client Balance Due',
		'jasper',
		'reporting_engine',
		'report_MonthlyClientBalanceDue_en_US',
		2,
		'End Date,Facility',
		'Date,Facility',
		'0,0',
		'MonthlyClientBalanceDue_en_US'
	);

