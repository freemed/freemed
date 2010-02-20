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

# File: Report - Patient Account Activity

DROP PROCEDURE IF EXISTS report_PatientAccountActivity_en_US ;
DELIMITER //

# Function: report_PatientAccountActivity_en_US
#
#	Patient account activity for a specified date range.
#
CREATE PROCEDURE report_PatientAccountActivity_en_US ( IN beginDate DATE, IN endDate DATE )
BEGIN
	SET @sql = CONCAT(
		"SELECT ",
			"CONCAT(pt.ptlname, ', ', pt.ptfname, ' ', pt.ptmname) AS 'Patient', ",
			"pt.ptid AS 'Patient ID', ",
			"DATE_FORMAT(proc.procdt, '%m/%d/%Y') AS 'Date Added', ",
			"CASE pay.payrectype WHEN 0 THEN 'Payment' WHEN 1 THEN 'Adjustment' WHEN 2 THEN 'Refund' WHEN 3 THEN 'Denial' WHEN 4 THEN 'Rebill' WHEN 5 THEN 'Procedure' WHEN 6 THEN 'Transfer' WHEN 7 THEN 'Withhold' WHEN 8 THEN 'Deductable' WHEN 9 THEN 'Fee Adjustment' WHEN 10 THEN 'Billed' WHEN 11 THEN 'Copay' WHEN 12 THEN 'Writeoff' ELSE '' END AS 'Type', ",
			"CONCAT( phy.phylname, ', ', phy.phyfname, ' ', phymname ) AS 'Provider', "
			"pay.payrecdescrip AS 'Description', ",
			"ROUND(proc.proccharges,2) AS 'Charged', ",
			"ROUND(pay.payrecamt,2) AS 'Paid' ",
            	"FROM payrec pay ",
			"LEFT OUTER JOIN procrec proc ON pay.payrecproc=proc.id ",
			"LEFT OUTER JOIN patient pt ON proc.procpatient=pt.id ",
			"LEFT OUTER JOIN physician phy ON proc.procphysician=phy.id ",
            	"WHERE ",
			"proc.procdt >= '", beginDate, "' AND proc.procdt <= '", endDate, "' ",
		"ORDER BY 'Patient Name' ; "
	) ;

	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;
END //

DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_PatientAccountActivity_en_US';

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
		'Patient Account Activity',
		'741edd33-d0c6-42e7-844e-d5014e746bbf',
		'en_US',
		'Patient account activity for a specified date range.',
		'reporting_engine',
		'report_PatientAccountActivity_en_US',
		2,
		'Starting Date,Ending Date',
		'Date,Date',
		'Start,End',
		'0,0'
	);

