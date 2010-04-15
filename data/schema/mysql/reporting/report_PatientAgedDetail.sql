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

# File: Report - Patient Aged Detail Report

DROP PROCEDURE IF EXISTS report_PatientAgedDetail_en_US;
DELIMITER //

# Function: report_PatientAgedDetail_en_US
#
#	Patient Aged Detail Report
#


CREATE PROCEDURE report_PatientAgedDetail_en_US()
BEGIN
	SET @sql = CONCAT(
		"SELECT ",
			"b.id AS ptid, ",
			"d.insconame AS insconame, ",
			"a.procbalcurrent AS procbalcurrent, ",
			"a.procdt AS procdt, ",
			"CONCAT(b.ptlname,', ',b.ptfname,' ',b.ptmname) AS ptname, ",
			"e.id AS id, ",
			"TO_DAYS(CURRENT_DATE)-TO_DAYS(a.procdt) as procage ",
		"FROM ",
			"procrec as a, ",
			"patient as b, ",
			"insco as d, ",
			"coverage as e ",
		"WHERE ",
			"a.procbalcurrent>0 ",
			"AND a.procbillable=0 ",
			"AND a.procpatient=b.id  ",
			"AND a.proccurcovid=e.id  ",
			"AND e.covinsco=d.id ",
			"ORDER BY b.ptlname,d.insconame,procage "
	);
	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;

END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_PatientAgedDetail_en_US';

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
		'Patient Aged Detail Report',
		'7a136219-335c-43a3-97c0-d959b8080fc7',
		'en_US',
		'Patient Aged Detail Report',
		'jasper',
		'billing_report',
		'report_PatientAgedDetail_en_US',
		0,
		'',
		'',
		'',
		'PatientAgedDetail_en_US'
	);
