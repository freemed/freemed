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

CREATE PROCEDURE report_MonthlyCashCollectionSummary_en_US (IN startDate DATE, IN endDate DATE,IN facID INT)
BEGIN
	SET @sql = CONCAT(
		"SELECT ",
			"(SELECT pracname from practice ORDER BY id LIMIT 1) as practice,",
			"CASE WHEN pr.payreclink=0 THEN 1 ELSE 2 END type,",
			"pt.ptid AS patientid, ",
			"pt.id AS patientnum,"
			"CONCAT(SUBSTRING(pt.ptlname FROM 1 FOR 3),', ',SUBSTRING(pt.ptfname FROM 1 FOR 1)) AS patientname, ",
			"IFNULL(i.insconame,'Not Assigned to Third Party') AS insurance,",
			"( SELECT ",
				"IFNULL(SUM(payrecamt),0) FROM facility f,cpt c,payrec p1,procrec prc ",
				"WHERE p1.payrecpatient = pr.payrecpatient AND p1.payreclink = pr.payreclink AND ",
				"p1.payrecproc=prc.id AND prc.proccpt=c.id AND c.cptcode!='80101' AND f.id=",facID," ",
				"AND p1.payreccat = 0 AND p1.payrectype = 0 AND p1.payrecdt>='",startDate,"' AND p1.payrecdt<='",endDate,"' ) AS cash, ",
			"( SELECT ",
				"IFNULL(SUM(payrecamt),0) FROM facility f,cpt c,payrec p2,procrec prc ",
				"WHERE p2.payrecpatient = pr.payrecpatient AND p2.payreclink = pr.payreclink AND ",
				"p2.payrecproc=prc.id AND prc.proccpt=c.id AND c.cptcode!='80101' AND f.id=",facID," ",
				"AND p2.payreccat = 0 AND p2.payrectype IN (1,4) AND p2.payrecdt>='",startDate,"' AND p2.payrecdt<='",endDate,"' ) AS checque, ",
			"( SELECT ",
				"IFNULL(SUM(payrecamt),0) FROM facility f,cpt c,payrec p3,procrec prc ",
				"WHERE p3.payrecpatient = pr.payrecpatient AND p3.payreclink = pr.payreclink AND ",
				"p3.payrecproc=prc.id AND prc.proccpt=c.id AND c.cptcode!='80101' AND f.id=",facID," ",
				"AND p3.payreccat = 0 AND p3.payrectype = 3 AND p3.payrecdt>='",startDate,"' AND p3.payrecdt<='",endDate,"' ) AS creditcard,",
			"( SELECT ",
				"IFNULL(SUM(payrecamt),0) FROM facility f,cpt c,payrec p4,procrec prc ",
				"WHERE p4.payrecpatient = pr.payrecpatient AND p4.payreclink = pr.payreclink AND ",
				"p4.payrecproc=prc.id AND prc.proccpt=c.id AND c.cptcode!='80101' AND f.id=",facID," ",
				"AND p4.payreccat=11 AND p4.payrecdt>='",startDate,"' AND p4.payrecdt<='",endDate,"' ) AS copay ,",
			"( SELECT ",
				"IFNULL(SUM(payrecamt),0) FROM facility f,cpt c,payrec p5,procrec prc ",
				"WHERE p5.payrecpatient = pr.payrecpatient AND p5.payreclink = pr.payreclink AND ",
				"p5.payrecproc=prc.id AND prc.proccpt=c.id AND c.cptcode='80101' AND f.id=",facID," AND "
				"p5.payreccat IN(0,11) AND p5.payrecdt>='",startDate,"' AND p5.payrecdt<='",endDate,"' ) AS uabsn ,",
			"f.psrname AS 'Facility Name' ",
		"FROM facility f,procrec prec,payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
			"LEFT OUTER JOIN patient pt ON pt.id = pr.payrecpatient ",
		"WHERE "
			"prec.id=pr.payrecproc AND ",
			"prec.procpos=",facID," AND ",
			"prec.procpos=f.id AND ",
			"pr.payreccat IN ( 0, 11 ) AND ",
			"pr.payrecdt>='",startDate,"' AND pr.payrecdt<='",endDate,"' "
			"GROUP BY pr.payrecpatient, pr.payreclink ORDER by type" 
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
		'reporting_engine',
		'report_MonthlyCashCollectionSummary_en_US',
		3,
		'Start Date,End Date,Facility',
		'Date,Date,Facility',
		'0,0,0',
		'MonthlyCashCollectionSummary_en_US'
	);

