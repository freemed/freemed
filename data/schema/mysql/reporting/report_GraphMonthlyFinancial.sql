# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2007 FreeMED Software Foundation
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

# File: Report - Graph Monthly Financials

DROP PROCEDURE IF EXISTS report_GraphMonthlyFinancial_en_US ;
DELIMITER //

# Function: report_GraphMonthlyFinancial_en_US
#
#	Show financial totals by month.
#
CREATE PROCEDURE report_GraphMonthlyFinancial_en_US ( )
BEGIN
	CREATE TEMPORARY TABLE payM ( m VARCHAR(10), mLabel VARCHAR(20), tChg DOUBLE, tPay DOUBLE );
	#	Create initial records to aggregate totals into
	INSERT INTO payM SELECT DISTINCT(CONCAT(DATE_FORMAT(payrecdt, '%Y-%m'),'-01')),NULL,0,0 FROM payrec WHERE payrecdt > '1900-00-00';
	UPDATE payM SET mLabel = DATE_FORMAT( m, '%b\n%Y' );
	#	Aggregate payments
	UPDATE payM SET tPay = ( SELECT ROUND(SUM( payrec.payrecamt ), 2) FROM payrec WHERE payM.m = DATE_FORMAT(payrec.payrecdt, '%Y-%m-01') AND FIND_IN_SET(payrec.payrectype,'1,11') );
	#	Aggregate charges
	UPDATE payM SET tChg = ( SELECT ROUND(SUM( procrec.proccharges ), 2) FROM procrec WHERE payM.m = DATE_FORMAT(procrec.procdt, '%Y-%m-01') );
	#	Convert all NULLs to 0's
	UPDATE payM SET tChg = 0 WHERE ISNULL(tChg);
	UPDATE payM SET tPay = 0 WHERE ISNULL(tPay);
	#	Return data
	SELECT mLabel AS 'Month', tChg AS 'Charges', tPay AS 'Payments' FROM payM ORDER BY m;
	DROP TEMPORARY TABLE payM;
END //

DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_GraphMonthlyFinancial_en_US';

INSERT INTO `reporting` (
		report_name,
		report_type,
		report_uuid,
		report_locale,
		report_desc,
		report_sp,
		report_param_count
	) VALUES (
		'Graph Monthly Financials',
		'graph',
		'11810586-c7ab-43d6-b1bc-6d9823397f3a',
		'en_US',
		'Show financial totals by month.',
		'report_GraphMonthlyFinancial_en_US',
		0
	);

