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

# File: Report - Account Paid Graph

DROP PROCEDURE IF EXISTS report_AccountPaid_en_US;
DELIMITER //

# Function: report_AccountPaid_en_US
#
#	Account Paid Graph
#


CREATE PROCEDURE report_AccountPaid_en_US(IN strDate DATE, IN endDate DATE )
BEGIN
	SET @sql = CONCAT(
		"SELECT ",
			"rs.proc_date AS 'Date', ",
			"SUM(rs.bal_orig) AS 'Charges', ",
			"SUM(amnt_paid) AS 'Payments', ",
			"SUM(rs.copay_amnt) AS 'Copays', ",
			"SUM(rs.charges) AS 'Adjustments' ",
		"FROM (",
			"SELECT ",
				"a.procdt AS 'proc_date', ",
				"a.procbalorig AS 'bal_orig', ",
				"(a.procamtpaid-IFNULL(SUM(pr.payrecamt),0)) AS 'amnt_paid', ",
				"a.proccharges AS 'charges', ",
				"IFNULL(SUM(pr.payrecamt),0) AS 'copay_amnt' ",
			"FROM ",
				"procrec AS a ",
				"LEFT OUTER JOIN payrec pr ON pr.payrecproc=a.id AND payreccat=11 ",
				"WHERE a.procdt >= '",strDate,
				"' AND a.procdt<='",endDate,
				"' AND a.procbalcurrent=0 ",
				"GROUP BY a.id ",
				"ORDER BY a.procdt "
				
		") AS rs ",
			" GROUP BY proc_date"
	);
	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;

END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_AccountPaid_en_US';

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
		'Account Paid Graph',
		'205a1bc7-8e1c-44b6-94e3-62a9a98c52e7',
		'en_US',
		'Account Paid Graph',
		'jasper',
		'billing_report',
		'report_AccountPaid_en_US',
		2,
		'Start Date,End Date',
		'Date,Date',
		'0,0',
		'AccountPaid_en_US'
	);
