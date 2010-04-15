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

# File: Report - Transaction Graph

DROP PROCEDURE IF EXISTS report_TransactionGraph_en_US;
DELIMITER //

# Function: report_TransactionGraph_en_US
#
#	Transaction Graph
#


CREATE PROCEDURE report_TransactionGraph_en_US(IN strDate DATE, IN endDate DATE )
BEGIN
	DECLARE total,cat,adjust,refund,denial,charge,withhold,deductable,feeadjust,copay,payment,writeoff,no_more_records INT UNSIGNED DEFAULT 0;

	DECLARE  recs CURSOR FOR 
	SELECT SUM(payrecamt) as payrectot,payreccat
	FROM payrec 
	WHERE payrecdt>=strDate AND payrecdt<=endDate GROUP BY payreccat ORDER BY payreccat; 

	
	DECLARE  CONTINUE HANDLER FOR NOT FOUND 
	SET  no_more_records = 1;

	OPEN  recs;

	FETCH  recs INTO total,cat;
	REPEAT 
	IF cat = 1 THEN
		SET adjust    = total;
	ELSEIF cat = 2 THEN
		SET refund    = total;
	ELSEIF cat = 3 THEN
		SET denial    = total;
	ELSEIF cat = 5 THEN
		SET charge    = total;
	ELSEIF cat = 7 THEN
		SET withhold    = total;
	ELSEIF cat = 8 THEN
		SET deductable    = total;
	ELSEIF cat = 9 THEN
		SET feeadjust    = total;
	ELSEIF cat = 11 THEN
		SET copay    = total;		
	ELSEIF cat = 12 THEN
		SET writeoff    = total;
	ELSEIF cat = 0 THEN 
		SET payment    = total;
	END IF;
	FETCH  recs INTO total,cat;
	UNTIL  no_more_records = 1
	END REPEAT;
	CLOSE  recs;
	select adjust,refund,denial,charge,withhold,deductable,feeadjust,copay,payment,writeoff;

END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_TransactionGraph_en_US';

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
		'Transaction Graph',
		'24eaac86-d9d4-435c-8e4b-e827fc0b5b4d',
		'en_US',
		'Transaction Graph',
		'jasper',
		'billing_report',
		'report_TransactionGraph_en_US',
		2,
		'Start Date,End Date',
		'Date,Date',
		'0,0',
		'TransactionGraph_en_US'
	);
