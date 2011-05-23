# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2011 FreeMED Software Foundation
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

# File: Report - Patient Receipt Short

DROP PROCEDURE IF EXISTS `report_PatientReceiptShort_en_US`;

DELIMITER //

CREATE PROCEDURE `report_PatientReceiptShort_en_US`(IN payrecid INT,IN facId INT)
    BEGIN

	SET @sql = CONCAT(
		"SELECT ",
			"(SELECT pracname from practice ORDER BY id LIMIT 1) as practice,",
			"CONCAT(pt.ptlname,', ',pt.ptfname,' ',pt.ptmname) AS 'Client', ",
			"f.psrname AS 'facname', ",
			"f.psraddr1 AS 'facadd1',",
			"f.psraddr2 AS 'facadd2',",
			"CONCAT(f.psrcity,', ',f.psrstate,' ',f.psrzip) AS 'faccityinfo',"
			"f.psrphone AS 'Phone',",
			"pt.ptaddr1 AS 'ptadd1',",
			"CONCAT(pt.ptcity,', ',pt.ptstate,' ',pt.ptzip) AS 'ptcityinfo',"
			"CASE WHEN prec.payreccat=0 THEN 'Payment' WHEN prec.payreccat=11 THEN ",
			"'COPAY' WHEN prec.payreccat=8 THEN 'Deductable' END AS 'type',",		
			"(SELECT SUM(pr1.procbalcurrent) from procrec pr1 where pr1.procpatient=prec.payrecpatient) AS 'New Balance',",
			"prec.payrecamt AS 'Amount',",
			"CASE WHEN prec.payrectype=0 THEN 'Cash' ",
				"WHEN prec.payrectype=1 THEN 'Cheque' ",
				"WHEN prec.payrectype=2 THEN 'Money Order' ",
				"WHEN prec.payrectype=3 THEN 'Credit Card' ",
				"WHEN prec.payrectype=4 THEN 'Traveler\\'s Check' ",
				"WHEN prec.payrectype=5 THEN 'EFT' END AS 'paytype', ",
			"prec.payrecdescrip AS 'Description' ",
		"FROM ",
			"procrec pr,payrec prec ",
			"LEFT JOIN patient pt ON prec.payrecpatient = pt.id ",
			"LEFT JOIN facility f ON f.id = ",facId," ",
		"WHERE ",
			"prec.id = ",payrecid," AND ",
			"prec.payrecproc = pr.id "
	);
	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;
 
END//

DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_PatientReceiptShort_en_US';

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
		'Patient Receipt Short',
		'141e3014-bbcc-4dda-9706-518e2cb96a10',
		'en_US',
		'Patient Receipt Short',
		'jasper',
		'billing_report',
		'report_PatientReceiptShort_en_US',
		2,
		'Transaction Id,Facility',
		'int,int',
		'0,0',
		'PatientReceiptShort_en_US'
	);

