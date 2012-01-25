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

# File: 'Daily Adjustment Journal',

DROP PROCEDURE IF EXISTS report_DailyAdjustmentJournal_en_US;
DELIMITER //

# Function: report_DailyAdjustmentJournal_en_US
#
#	'Daily Adjustment Journal',
#

CREATE PROCEDURE report_DailyAdjustmentJournal_en_US (IN cDate DATE,IN facID INT)
BEGIN
	SET @sql = CONCAT(
		"SELECT data.*,(SELECT pracname from practice ORDER BY id LIMIT 1) as practice FROM "
		"((SELECT ",
			"'Invoices' as 'Type',",
			"'3rd Party Adj' AS 'Sub Type', ",
			"IFNULL(i.insconame,'Others') AS Source, ",
			"pt.id AS patientnum,"
			"CONCAT(SUBSTRING(pt.ptlname FROM 1 FOR 3),', ',SUBSTRING(pt.ptfname FROM 1 FOR 1)) AS patientname, ",
			"pr.id AS 'Trans Num', ",
			"pr.payrecdtadd AS 'Trans Date', ",
			"pr.payrecamt AS 'Invoice Amount', ",			
			"0 AS 'Credit Amount', ",
			"pr.payrecdescrip AS Reason, ",
			"CONCAT(DATE_FORMAT(prec.procdt, '%m/%d/%Y'),' - ',IFNULL(DATE_FORMAT(prec.procdtend, '%m/%d/%Y'),'')) AS 'Service Dates', ",
			"f.psrname AS 'Facility Name', ",
			"1 AS subtypeid, "
			"i.id AS inscoid "
		"FROM facility f,procrec prec,payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
			"LEFT OUTER JOIN patient pt ON pt.id = pr.payrecpatient ",
		"WHERE ",
			"pr.payreccat = 5 AND ",
			"pr.payreclink!=0 AND ",
			"pr.payrecdtadd='",cDate,"' AND ",
			"prec.id=pr.payrecproc AND ",
			"prec.procpos=",facID," AND ",
			"prec.procpos=f.id ) ",
		"UNION ",
		"(SELECT ",
			"'Invoices' as 'Type',",
			"'Lab Fee' AS 'Sub Type', ",
			"'Not Assigned To Third Party' AS Source, ",
			"pt.id AS patientnum,"
			"CONCAT(SUBSTRING(pt.ptlname FROM 1 FOR 3),', ',SUBSTRING(pt.ptfname FROM 1 FOR 1)) AS patientname, ",
			"pr.id AS 'Trans Num', ",
			"pr.payrecdtadd AS 'Trans Date', ",
			"prec.proclabcharges AS 'Invoice Amount', ",			
			"0 AS 'Credit Amount', ",
			"pr.payrecdescrip AS Reason, ",
			"CONCAT(DATE_FORMAT(prec.procdt, '%m/%d/%Y'),' - ',IFNULL(DATE_FORMAT(prec.procdtend, '%m/%d/%Y'),'')) AS 'Service Dates', ",
			"f.psrname AS 'Facility Name', ",
			"2 AS subtypeid, "
			"0 AS inscoid "
		"FROM facility f,procrec prec,payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
			"LEFT OUTER JOIN patient pt ON pt.id = pr.payrecpatient ",
		"WHERE ",
			"pr.payreccat = 5 AND ",
			"pr.payrecdtadd='",cDate,"' AND ",
			"prec.proclabcharges > 0 AND ",
			"prec.id=pr.payrecproc AND ",
			"prec.procpos=",facID," AND ",
			"prec.procpos=f.id ) ",
		"UNION ",
		"(SELECT ",
			"'Invoices' as 'Type',",
			"'Self Pay' AS 'Sub Type', ",
			"'Not Assigned To Third Party' AS Source, ",
			"pt.id AS patientnum,"
			"CONCAT(SUBSTRING(pt.ptlname FROM 1 FOR 3),', ',SUBSTRING(pt.ptfname FROM 1 FOR 1)) AS patientname, ",
			"pr.id AS 'Trans Num', ",
			"pr.payrecdtadd AS 'Trans Date', ",
			"pr.payrecamt AS 'Invoice Amount', ",			
			"0 AS 'Credit Amount', ",
			"pr.payrecdescrip AS Reason, ",
			"CONCAT(DATE_FORMAT(prec.procdt, '%m/%d/%Y'),' - ',IFNULL(DATE_FORMAT(prec.procdtend, '%m/%d/%Y'),'')) AS 'Service Dates', ",
			"f.psrname AS 'Facility Name', ",
			"3 AS subtypeid, "
			"0 AS inscoid "
		"FROM facility f,procrec prec,payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
			"LEFT OUTER JOIN patient pt ON pt.id = pr.payrecpatient ",
		"WHERE ",
			"pr.payreccat = 5 AND ",
			"pr.payreclink=0 AND ",
			"pr.payrecdtadd='",cDate,"' AND ",
			"prec.id=pr.payrecproc AND ",
			"prec.procpos=",facID," AND ",
			"prec.procpos=f.id ) ",
		"UNION ",
		"(SELECT ",
			"'Credits' as 'Type',",
			"'3rd Party Adj' AS 'Sub Type', ",
			"IFNULL(i.insconame,'Others') AS Source, ",
			"pt.id AS patientnum,"
			"CONCAT(SUBSTRING(pt.ptlname FROM 1 FOR 3),', ',SUBSTRING(pt.ptfname FROM 1 FOR 1)) AS patientname, ",
			"pr.id AS 'Trans Num', ",
			"pr.payrecdtadd AS 'Trans Date', ",
			"0 AS 'Invoice Amount', ",			
			"pr.payrecamt AS 'Credit Amount', ",
			"pr.payrecdescrip AS Reason, ",
			"CONCAT(DATE_FORMAT(prec.procdt, '%m/%d/%Y'),' - ',IFNULL(DATE_FORMAT(prec.procdtend, '%m/%d/%Y'),'')) AS 'Service Dates', ",
			"f.psrname AS 'Facility Name', ",
			"1 AS subtypeid, "
			"i.id AS inscoid "
		"FROM facility f,procrec prec,payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
			"LEFT OUTER JOIN patient pt ON pt.id = pr.payrecpatient ",
		"WHERE ",
			"pr.payreccat IN (1,2,7,9,12) AND ",
			"pr.payreclink!=0 AND ",
			"pr.payrecdtadd='",cDate,"' AND ",
			"prec.id=pr.payrecproc AND ",
			"prec.procpos=",facID," AND ",
			"prec.procpos=f.id ORDER BY Source) ",
		"UNION ",
		"(SELECT ",
			"'Credits' as 'Type',",
			"'Self Pay' AS 'Sub Type', ",
			"'Not Assigned to Third Party' AS Source, ",
			"pt.id AS patientnum,"
			"CONCAT(SUBSTRING(pt.ptlname FROM 1 FOR 3),', ',SUBSTRING(pt.ptfname FROM 1 FOR 1)) AS patientname, ",
			"pr.id AS 'Trans Num', ",
			"pr.payrecdtadd AS 'Trans Date', ",
			"0 AS 'Invoice Amount', ",			
			"pr.payrecamt AS 'Credit Amount', ",
			"pr.payrecdescrip AS Reason, ",
			"CONCAT(DATE_FORMAT(prec.procdt, '%m/%d/%Y'),' - ',IFNULL(DATE_FORMAT(prec.procdtend, '%m/%d/%Y'),'')) AS 'Service Dates', ",
			"f.psrname AS 'Facility Name', ",
			"2 AS subtypeid, "
			"0 AS inscoid "
		"FROM facility f,procrec prec,payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
			"LEFT OUTER JOIN patient pt ON pt.id = pr.payrecpatient ",
		"WHERE ",
			"pr.payreccat IN (1,2,12) AND ",
			"pr.payreclink=0 AND ",
			"pr.payrecdtadd='",cDate,"' AND ",
			"prec.id=pr.payrecproc AND ",
			"prec.procpos=",facID," AND ",
			"prec.procpos=f.id )) data ORDER BY data.Type, data.subtypeid, data.inscoid ASC"
	);
	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;

END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_DailyAdjustmentJournal_en_US';
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
		'Daily Adjustment Journal',
		'f2b76f1e-e2e7-4b16-822b-a31d6a2c0dae',
		'en_US',
		'Daily Adjustment Journal',
		'jasper',
		'sub_report',
		'report_DailyAdjustmentJournal_en_US',
		2,
		'Date,Facility',
		'Date,Facility',
		'0,0',
		'sub_DailyAdjustmentJournal_en_US'
	);

