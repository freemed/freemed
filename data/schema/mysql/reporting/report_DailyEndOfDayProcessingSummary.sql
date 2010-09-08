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

# File: Daily End Of Day Processing Summary

DROP PROCEDURE IF EXISTS report_DailyEndOfDayProcessingSummary_en_US;
DELIMITER //

# Function: report_DailyEndOfDayProcessingSummary_en_US
#
#	Daily End Of Day Processing Summary
#

CREATE PROCEDURE report_DailyEndOfDayProcessingSummary_en_US (IN cDate DATE,IN facID INT)
BEGIN
	SET @sql = CONCAT(
		"SELECT data.*, (SELECT pracname from practice ORDER BY id LIMIT 1) as practice FROM (( SELECT ",
			"'Cash Receipts' AS 'Summary Type',",
			"CONCAT('Third Party Pmt ',i.insconame) AS Source,",
			"IFNULL(SUM(pr.payrecamt),0) AS 'Collection & Charges', ",
			"0.00 AS 'Cash Adj & Credits', ",
			"f.psrname AS 'Facility Name' ",
		"FROM facility f, cpt cp,procrec p,payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
		"WHERE ",
			"pr.payrecproc=p.id AND ",
			"p.proccpt=cp.id AND ",
			"cp.cptcode!='80101' AND "
			"p.procpos=",facID," AND ",
			"p.procpos=f.id AND ",
			"pr.payreccat IN (0,8,11) AND ",
			"pr.payreclink!=0 AND ",
			"pr.payrecdtadd='",cDate,"' ",
			"GROUP BY pr.payreclink ) ",
		"UNION ",
		"( SELECT ",
			"'Cash Receipts' AS 'Summary Type',",
			"'Self Pay' AS Source,",
			"IFNULL(SUM(pr.payrecamt),0) AS 'Collection & Charges', ",
			"0.00 AS 'Cash Adj & Credits', ",
			"f.psrname AS 'Facility Name' ",
		"FROM facility f, procrec p, payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
		"WHERE ",
			"pr.payrecproc=p.id AND ",
			"p.procpos=",facID," AND ",
			"p.procpos=f.id AND ",
			"pr.payreccat IN ( 0,8,11 ) AND ",
			"pr.payreclink=0 AND ",
			"pr.payrecdt='",cDate,"' ",
			"GROUP BY pr.payreclink ) ",
		"UNION ",
		"( SELECT ",
			"'Cash Receipts' AS 'Summary Type',",
			"'UA/BSN' AS Source,",
			"( SELECT IFNULL(SUM(p1.payrecamt),0) FROM payrec p1 WHERE p1.payreclink = pr.payreclink AND p1.payreccat IN (0,8,11) AND p1.payrecdt='",cDate,"') AS 'Collection & Charges', ",
			"0 AS 'Cash Adj & Credits', ",
			"f.psrname AS 'Facility Name' ",
		"FROM facility f,cpt cp,procrec p, payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
		"WHERE ",			
			"pr.payrecproc=p.id AND ",
			"p.procpos=",facID," AND ",
			"p.procpos=f.id AND ",
			"p.proccpt=cp.id AND ",
			"cp.cptcode='80101' AND "
			"pr.payreccat IN ( 0,8,11 ) AND ",
			"pr.payrecdt='",cDate,"' ",
			"GROUP BY pr.payreclink ) ",
		"UNION ",
		"( SELECT ",
			"'Adjustments' AS 'Summary Type',",
			"CONCAT('Third Party Adj ',i.insconame) AS Source,",
			"( SELECT IFNULL(SUM(p1.payrecamt),0) FROM payrec p1 WHERE p1.payreclink = pr.payreclink AND p1.payreccat = 5 AND p1.payrecdt='",cDate,"') AS 'Collection & Charges', ",
			"( SELECT IFNULL(SUM(p1.payrecamt),0) FROM payrec p1 WHERE p1.payreclink = pr.payreclink AND p1.payreccat IN (1,2,7,9,12) AND p1.payrecdt='",cDate,"') AS 'Cash Adj & Credits', ",
			"f.psrname AS 'Facility Name' ",
		"FROM facility f, procrec p,payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
		"WHERE ",
			"pr.payrecproc=p.id AND ",
			"p.procpos=",facID," AND ",
			"p.procpos=f.id AND ",
			"pr.payreccat IN (5,1,2,7,9,12) AND ",
			"pr.payreclink!=0 AND ",
			"pr.payrecdt='",cDate,"' ",
			"GROUP BY pr.payreclink ) ",
		"UNION ",
		"( SELECT ",
			"'Adjustments' AS 'Summary Type',",
			"'Lab Fee' AS Source,",
			"SUM(p.proclabcharges)  AS 'Collection & Charges', ", 
			"0.00 AS 'Cash Adj & Credits', ",
			"f.psrname AS 'Facility Name' ",
		"FROM facility f, procrec p,payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
		"WHERE ",			
			"pr.payrecproc=p.id AND ",
			"p.procpos=",facID," AND ",
			"p.procpos=f.id AND ",
			"p.proclabcharges > 0 AND ",
			"pr.payreccat=5 AND ",
			"pr.payrecdt='",cDate,"' ",
			"GROUP BY 'Summary Type' ) ",
		"UNION ",
		"( SELECT ",
			"'Adjustments' AS 'Summary Type',",
			"'Self Pay' AS Source,",
			"( SELECT IFNULL(SUM(p1.payrecamt),0) FROM payrec p1 WHERE p1.payreclink = pr.payreclink AND p1.payreccat = 5 AND p1.payrecdt='",cDate,"') AS 'Collection & Charges', ",
			"( SELECT IFNULL(SUM(p1.payrecamt),0) FROM payrec p1 WHERE p1.payreclink = pr.payreclink AND p1.payreccat IN (1,2,7,9,12) AND p1.payrecdt='",cDate,"') AS 'Cash Adj & Credits', ",
			"f.psrname AS 'Facility Name' ",
		"FROM facility f, procrec p,payrec pr ",
			"LEFT OUTER JOIN coverage c ON c.id = pr.payreclink  ",
			"LEFT OUTER JOIN insco i ON i.id = c.covinsco ",
		"WHERE ",			
			"pr.payrecproc=p.id AND ",
			"p.procpos=",facID," AND ",
			"p.procpos=f.id AND ",
			"p.proclabcharges > 0 AND ",
			"pr.payreclink=0 AND ",
			"pr.payreccat IN (5,1,2,7,9,12) AND ",
			"pr.payrecdtadd='",cDate,"' ",
			"GROUP BY 'Summary Type' )",
		") data "
	);
	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;

END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_DailyEndOfDayProcessingSummary_en_US';
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
		'Daily End Of Day Processing Summary',
		'8d176859-f6e3-4bb2-9992-da815d39b4e7',
		'en_US',
		'Daily End Of Day Processing Summary',
		'jasper',
		'sub_report',
		'report_DailyEndOfDayProcessingSummary_en_US',
		2,
		'Date,Facility',
		'Date,Facility',
		'0,0',
		'sub_DailyEndOfDayProcessingSummary_en_US'
	);

