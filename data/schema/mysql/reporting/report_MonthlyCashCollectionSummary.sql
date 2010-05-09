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

# File: Monthly Report - Cash Collections Summary

DROP PROCEDURE IF EXISTS report_MonthlyCashCollectionSummary_en_US;
DELIMITER //

# Function: report_MonthlyCashCollectionSummary_en_US
#
#	Monthly Report - Cash Collections Summary
#

CREATE PROCEDURE report_MonthlyCashCollectionSummary_en_US (IN startDate DATE,IN endDate DATE)
BEGIN
	SET @sql = CONCAT(
		"SELECT ",
			"pt.ptid AS patientid, ",
			"CONCAT(pt.ptlname,', ',pt.ptfname,' ',pt.ptmname) AS patientname, ",
			"IFNULL(i.insconame,'Others') AS insurance,",
			"( SELECT IFNULL(SUM(payrecamt),0) FROM payrec p1 WHERE p1.payrecpatient = pr.payrecpatient AND p1.payreclink = pr.payreclink AND p1.payreccat = 0 AND p1.payrectype = 0 AND p1.payrecdt>='",startDate,"' AND p1.payrecdt<='",endDate,"' ) AS cash, ",
			"( SELECT IFNULL(SUM(payrecamt),0) FROM payrec p2 WHERE p2.payrecpatient = pr.payrecpatient AND p2.payreclink = pr.payreclink AND p2.payreccat = 0 AND p2.payrectype IN (1,4) AND p2.payrecdt>='",startDate,"' AND p2.payrecdt<='",endDate,"' ) AS checque, ",
			"( SELECT IFNULL(SUM(payrecamt),0) FROM payrec p3 WHERE p3.payrecpatient = pr.payrecpatient AND p3.payreclink = pr.payreclink AND p3.payreccat = 0 AND p3.payrectype = 3 AND p3.payrecdt>='",startDate,"' AND p3.payrecdt<='",endDate,"' ) AS creditcard,",
			"( SELECT IFNULL(SUM(payrecamt),0) FROM payrec p4 WHERE p4.payrecpatient = pr.payrecpatient AND p4.payreclink = pr.payreclink AND p4.payreccat=11 AND p4.payrecdt>='",startDate,"' AND p4.payrecdt<='",endDate,"' ) AS copay ",
		"FROM payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
			"LEFT OUTER JOIN patient pt ON pt.id = pr.payrecpatient ",
		"WHERE "
			"pr.payreccat IN ( 0, 11 ) AND ",
			"pr.payrecdt>='",startDate,"' AND pr.payrecdt<='",endDate,"' "
			"GROUP BY pr.payrecpatient, pr.payreclink" 
	);
	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;

END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_MonthlyCashCollectionSummary_en_US';
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
		'Monthly Cash Collection Summary',
		'33386735-7445-4457-9943-6a3152cc81b6',
		'en_US',
		'Monthly Cash Collection Summary',
		'jasper',
		'report',
		'report_MonthlyCashCollectionSummary_en_US',
		2,
		'Start Date,End Date',
		'Date,Date',
		'0',
		'report_MonthlyCashCollectionSummary_en_US'
	);

