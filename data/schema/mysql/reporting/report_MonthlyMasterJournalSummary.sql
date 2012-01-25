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

# File: Master Journal Summary

DROP PROCEDURE IF EXISTS report_MonthlyMasterJournalSummary_en_US;
DELIMITER //

# Function: report_MonthlyMasterJournalSummary_en_US
#
#	Monthly Report-Master Journal Summary
#

CREATE PROCEDURE report_MonthlyMasterJournalSummary_en_US ( IN startDate DATE,IN endDate DATE,IN facID INT)
BEGIN
	SET @sql = CONCAT(
		"SELECT data.*,f.psrname,(SELECT pracname from practice ORDER BY id LIMIT 1) as practice FROM ( ",
			"(SELECT ",
				"2 AS type,"
				"i.insconame AS '3rd Party',",
				"0 AS 'sales_intake', ",
				"0 AS 'sales_labfee', ",
				"0 AS 'sales_discharge', ",
				"0 AS 'sales_methadone', ",
				"(SELECT IFNULL(SUM(p1.proccharges),0) FROM payrec pr1,procrec p1, coverage c1 ",
					"WHERE  ",
					"pr1.payreccat=5 AND  ",
					"p1.id=pr1.payrecproc AND  ",
					"p1.proccurcovid!=0 AND ",
					"p1.proccurcovid=c1.id AND ",
					"p1.procpos=1 AND  ",
					"c1.covinsco=i.id AND ",
					"p1.procdtend<='",endDate,"' AND  ",
					"pr1.payrecdtadd >= '",startDate,"' AND ",
					"pr1.payrecdtadd <= '",endDate,"' ",
				") AS 'sales_other', ",
				"0 AS 'sales_allw_intake', ",
				"0 AS 'sales_allw_labfee', ",
				"0 AS 'sales_allw_discharge', ",
				"0 AS 'sales_allw_methadone', ",
				"(SELECT IFNULL(SUM(pr1.payrecamt),0) FROM payrec pr1,procrec p1, coverage c1 ",
					"WHERE  ",
					"pr1.payreccat IN (1,2,7,9,12) AND  ",
					"p1.id=pr1.payrecproc AND  ",
					"p1.proccurcovid!=0 AND ",
					"p1.proccurcovid=c1.id AND ",
					"p1.procpos=1 AND  ",
					"c1.covinsco=i.id AND ",
					"p1.procdtend<='",endDate,"' AND  ",
					"pr1.payrecdtadd >= '",startDate,"' AND ",
					"pr1.payrecdtadd <= '",endDate,"' ",
				") AS 'sale_all_other', ",
				"0 AS 'dfsales_intake', ",
				"0 AS 'dfsales_labfee', ",
				"0 AS 'dfsales_discharge', ",
				"0 AS 'dfsales_methadone', ",
				"(SELECT IFNULL(SUM(p1.proccharges),0) FROM payrec pr1,procrec p1, coverage c1 ",
					"WHERE  ",
					"pr1.payreccat=5 AND  ",
					"p1.id=pr1.payrecproc AND  ",
					"p1.proccurcovid!=0 AND ",
					"p1.proccurcovid=c1.id AND ",
					"p1.procpos=1 AND  ",
					"c1.covinsco=i.id AND ",
					"p1.procdtend>'",endDate,"' AND  ",
					"pr1.payrecdtadd >= '",startDate,"' AND ",
					"pr1.payrecdtadd <= '",endDate,"' ",
				") AS 'dfsales_other', ",
				"0 AS 'dfsales_allw_intake', ",
				"0 AS 'dfsales_allw_labfee', ",
				"0 AS 'dfsales_allw_discharge', ",
				"0 AS 'dfsales_allw_methadone', ",
				"(SELECT IFNULL(SUM(pr1.payrecamt),0) FROM payrec pr1,procrec p1, coverage c1 ",
					"WHERE  ",
					"pr1.payreccat IN (1,2,7,9,12) AND  ",
					"p1.id=pr1.payrecproc AND  ",
					"p1.proccurcovid!=0 AND ",
					"p1.proccurcovid=c1.id AND ",
					"p1.procpos=1 AND  ",
					"c1.covinsco=i.id AND ",
					"p1.procdtend>'",endDate,"' AND  ",
					"pr1.payrecdtadd >= '",startDate,"' AND ",
					"pr1.payrecdtadd <= '",endDate,"' ",
				") AS 'dfsale_all_other', ",
				"(SELECT IFNULL(SUM(pr1.payrecamt),0) FROM payrec pr1,procrec p1,cpt cp1,coverage c1,insco i1   ",
					"WHERE 	 ",
					"pr1.payreccat IN (0,8,11) AND  ",
					"pr1.payreclink!=0 AND  ",
					"pr1.payrecproc=p1.id AND  ",
					"cp1.id = p1.proccpt AND  ",
					"cp1.cptcode!='80101' AND  ",
					"pr1.payreclink=c1.id AND  ",
					"c1.covinsco=i1.id AND  ",
					"i1.id=i.id AND  ",
					"p1.procpos=1 AND  ",
					"p1.procdtend<='",endDate,"' AND  ",
					"pr1.payrecdtadd >= '",startDate,"' AND ",
					"pr1.payrecdtadd <= '",endDate,"' ",
				") AS 'cash_collected' ",
			"FROM insco i)",
			"UNION ALL ",
			"(SELECT ",
				"1 AS type,"
				"'None' AS '3rd Party',",
				"0 AS 'sales_intake', ",
				"(SELECT IFNULL(SUM(p1.proclabcharges),0) FROM payrec pr1,procrec p1 ",
					"WHERE pr1.payreccat=5 AND ",
					"p1.id=pr1.payrecproc AND ",
					"p1.proclabcharges>0 AND ",
					"p1.procpos=",facID," AND ",
					"p1.procdtend<='",endDate,"' AND  ",
					"pr1.payrecdtadd >= '",startDate,"' AND ",
					"pr1.payrecdtadd <= '",endDate,"' ",
				") AS 'sales_labfee', ",
				"0 AS 'sales_discharge', ",
				"(SELECT IFNULL(SUM(p1.proccharges),0) FROM payrec pr1,procrec p1 ",
					"WHERE pr1.payreccat=5 AND ",
					"p1.id=pr1.payrecproc AND ",
					"p1.proccurcovid=0 AND "
					"p1.procpos=",facID," AND ",
					"p1.procdtend<='",endDate,"' AND  ",
					"pr1.payrecdtadd >= '",startDate,"' AND ",
					"pr1.payrecdtadd <= '",endDate,"' ",
				") AS 'sales_methadone', ",
				"0 AS 'sales_other', ",
				"0 AS 'sales_allw_intake', ",
				"0 AS 'sales_allw_labfee', ",
				"0 AS 'sales_allw_discharge', ",
				"(SELECT IFNULL(SUM(pr1.payrecamt),0) FROM payrec pr1,procrec p1 ",
					"WHERE pr1.payreccat IN (1,2,7,9,12) AND ",
					"p1.id=pr1.payrecproc AND ",
					"p1.proccurcovid =0 AND ",
					"p1.procpos=",facID," AND ",
					"p1.procdtend<='",endDate,"' AND  ",
					"pr1.payrecdtadd >= '",startDate,"' AND ",
					"pr1.payrecdtadd <= '",endDate,"' ",
				") AS 'sales_allw_methadone', ",
				"0 AS 'sale_all_other', ",
				"0 AS 'dfsales_intake', ",
				"(SELECT IFNULL(SUM(p1.proclabcharges),0) FROM payrec pr1,procrec p1 ",
					"WHERE pr1.payreccat=5 AND ",
					"p1.id=pr1.payrecproc AND ",
					"p1.proclabcharges>0 AND ",
					"p1.procpos=",facID," AND ",
					"p1.procdtend>'",endDate,"' AND  ",
					"pr1.payrecdtadd >= '",startDate,"' AND ",
					"pr1.payrecdtadd <= '",endDate,"' ",
				") AS 'dfsales_labfee', ",
				"0 AS 'dfsales_discharge', ",
				"(SELECT IFNULL(SUM(p1.proccharges),0) FROM payrec pr1,procrec p1 ",
					"WHERE pr1.payreccat=5 AND ",
					"p1.id=pr1.payrecproc AND ",
					"p1.proccurcovid=0 AND "
					"p1.procpos=",facID," AND ",
					"p1.procdtend>'",endDate,"' AND  ",
					"pr1.payrecdtadd >= '",startDate,"' AND ",
					"pr1.payrecdtadd <= '",endDate,"' ",
				") AS 'dfsales_methadone', ",
				"0 AS 'dfsales_other', ",
				"0 AS 'dfsales_allw_intake', ",
				"0 AS 'dfsales_allw_labfee', ",
				"0 AS 'dfsales_allw_discharge', ",
				"(SELECT IFNULL(SUM(pr1.payrecamt),0) FROM payrec pr1,procrec p1 ",
					"WHERE pr1.payreccat IN (1,2,7,9,12) AND ",
					"p1.id=pr1.payrecproc AND ",
					"p1.proccurcovid =0 AND ",
					"p1.procpos=",facID," AND ",
					"p1.procdtend>'",endDate,"' AND  ",
					"pr1.payrecdtadd >= '",startDate,"' AND ",
					"pr1.payrecdtadd <= '",endDate,"' ",
				") AS 'dfsales_allw_methadone', ",
				"0 AS 'dfsale_all_other', ",
				"(SELECT IFNULL(SUM(pr1.payrecamt),0) FROM payrec pr1,procrec p1,cpt cp1 ",
					"WHERE pr1.payrecproc=p1.id AND ",
					"cp1.id = p1.proccpt AND ",
					"(",
						"(pr1.payreccat=0 AND pr1.payreclink=0) OR ",
						"(pr1.payreccat=0 AND cp1.cptcode='80101') OR ",
						"(pr1.payreccat IN (8,11)) ",
					") AND ",
					"p1.procpos=",facID," AND ",
					"p1.procdtend<='",endDate,"' AND  ",
					"pr1.payrecdtadd >= '",startDate,"' AND ",
					"pr1.payrecdtadd <= '",endDate,"' ",
				") AS 'cash_collected' )",			
		") data LEFT OUTER JOIN facility f ON f.id=",facID," ",			
			"WHERE ",
			"data.sales_other!=0 OR ",
			"data.sale_all_other!=0 OR ",
			"data.dfsales_other!=0 OR ",
			"data.dfsale_all_other!=0 OR ",
			"data.cash_collected!=0 ORDER BY data.type"
	);
	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;
END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_MonthlyMasterJournalSummary_en_US';
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
		'Monthly Report-Master Journal Summary',
		'57d41fa9-cb7d-4e5b-a586-44cf7f15f6c6',
		'en_US',
		'Monthly Report-Master Journal Summary',
		'jasper',
		'reporting_engine',
		'report_MonthlyMasterJournalSummary_en_US',
		3,
		'Start Date, End Date,Facility',
		'Date,Date,Facility',
		'0,0,0',
		'MonthlyMasterJournalSummary_en_US'
	);

