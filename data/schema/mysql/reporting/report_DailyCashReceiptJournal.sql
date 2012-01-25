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

# File: Daily Cash Receipt Journal

DROP PROCEDURE IF EXISTS report_DailyCashReceiptJournal_en_US;
DELIMITER //

# Function: report_DailyCashReceiptJournal_en_US
#
#	Daily Cash Receipt Journal
#

CREATE PROCEDURE report_DailyCashReceiptJournal_en_US ( IN cDate DATE,IN facID INT)
BEGIN
	SET @sql = CONCAT(
		"( SELECT ",
			"(SELECT pracname from practice ORDER BY id LIMIT 1) as practice,",
			"pt.id AS patientnum,"
			"CONCAT(SUBSTRING(pt.ptlname FROM 1 FOR 3),', ',SUBSTRING(pt.ptfname FROM 1 FOR 1)) AS patientname, ",
			"pr.id AS 'Trans Num' ,",
			"pr.payrecdtadd as 'Trans Date' ,",
			"0 AS 'Void Amount',",
			"pr.payrecamt AS 'Amount Received', ",
			"pr.payrecdescrip AS Reason, ",
			"CONCAT(DATE_FORMAT(prec.procdt, '%m/%d/%Y'),' - ',IFNULL(DATE_FORMAT(prec.procdtend, '%m/%d/%Y'),'')) AS 'Service Dates', ",
			"CASE ",
				"WHEN (pr.payreccat IN (0,8,11) AND pr.payreclink!=0 AND cp.cptcode!='80101') THEN i.insconame ",
				"WHEN (pr.payreccat IN (0,8,11) AND pr.payreclink=0 AND cp.cptcode!='80101') THEN 'Not Assigned To Third Party' ",
				"WHEN (pr.payreccat IN (0,8,11) AND cp.cptcode='80101') THEN 'Not Assigned To Third Party' END AS 'Source', ",
			"CASE ",
				"WHEN (pr.payreccat IN (0,8,11) AND pr.payreclink!=0 AND cp.cptcode!='80101') THEN '3rd Party Pmt' ",
				"WHEN (pr.payreccat IN (0,8,11) AND pr.payreclink=0 AND cp.cptcode!='80101') THEN 'Self Pay' ",
				"WHEN (pr.payreccat IN (0,8,11) AND cp.cptcode='80101') THEN 'UA/BSN' ELSE 'aaa' END AS 'SubType', ",
			"'Cash Receipts' AS Type, ",
			"f.psrname AS 'Facility Name' ",
		"FROM cpt cp,facility f,procrec prec, payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
			"LEFT OUTER JOIN patient pt ON pt.id = pr.payrecpatient ",
		"WHERE ",
			"pr.payreccat IN (0,8,11) AND ",
			"pr.payrecdtadd='",cDate,"' AND ",
			"prec.id=pr.payrecproc AND ",
			"prec.procpos=",facID," AND ",
			"prec.proccpt=cp.id AND ",
			"prec.procpos=f.id ",
			"ORDER BY SubType, Source )"
	);
	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;

END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_DailyCashReceiptJournal_en_US';
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
		'Daily Cash Receipt Journal',
		'9e2b8711-8ac6-416d-bfb5-e12d075ee4a0',
		'en_US',
		'Daily Cash Receipt Journal',
		'jasper',
		'sub_report',
		'report_DailyCashReceiptJournal_en_US',
		2,
		'Date,Facility',
		'Date,Facility',
		'0,0',
		'sub_DailyCashReceiptJournal_en_US'
	);

