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

# File: Report - Patient Receipt

DROP PROCEDURE IF EXISTS `report_PatientReceipt_en_US`;

DELIMITER //

CREATE PROCEDURE `report_PatientReceipt_en_US`(IN procrecid INT)
    BEGIN

	#Declaring variables		
	DECLARE  payrec_id,payrec_cat, payrec_amt,no_more_records,payrec_proc,procrec_cat,procrec_src INT DEFAULT 0;
	DECLARE  payrec_payment,payrec_charge DECIMAL(11,2);
	DECLARE  payrec_type VARCHAR(50);
	DECLARE  payrec_desc VARCHAR(255);
	DECLARE  payrec_patient VARCHAR(255);
	DECLARE  payrec_date DATE;

	#Cursor
	DECLARE  cur_procrec CURSOR FOR 
	SELECT pr.id AS Id, pr.payrecdt AS pay_date, pr.payrecdescrip AS pay_desc, pr.payrecamt AS pay_amount, pr.payrectype AS pay_type, 
	pr.payrecproc as pay_proc,pr.payreccat as pay_cat,pr.payrecsource as pay_src,
	CONCAT( p.ptlname, ', ', p.ptfname, ' ', p.ptmname ) AS patient_name
	FROM payrec pr
        left join patient p on p.id= pr.payrecpatient
	WHERE pr.payrecproc = procrecid
	ORDER BY pr.id,pr.payreccat ASC;

	#CONTINUE HANDLER
	DECLARE  CONTINUE HANDLER FOR NOT FOUND 
	SET  no_more_records = 1;
	
	#creating table for  ledger information 
	CREATE TEMPORARY TABLE ledger_info (
		Id int(11) NOT NULL ,
		date DATE ,
		dsc VARCHAR(255) ,
		type VARCHAR(50) ,
		charge DECIMAL(11,2) ,
	        payment DECIMAL(11,2), 
		patient VARCHAR(255), 
		PRIMARY KEY (Id)
	);

	#Opening Cursor
	OPEN  cur_procrec;
	
	#Start Fetching records
	FETCH  cur_procrec INTO payrec_id,payrec_date,payrec_desc,payrec_amt,payrec_type,payrec_proc,procrec_cat,procrec_src,payrec_patient;
	REPEAT 

	IF procrec_cat = 1 THEN
		SET payrec_type    = 'Adjustment';
		SET payrec_payment = payrec_amt;
		SET payrec_charge  = 0;
	ELSEIF procrec_cat = 2 THEN
		SET payrec_type    = 'Refund';
		SET payrec_payment = 0;
		SET payrec_charge  = payrec_amt;
	ELSEIF procrec_cat = 3 THEN
		SET payrec_type    = 'Denial';
		SET payrec_payment = 0;
		SET payrec_charge  = -1*payrec_amt;
	ELSEIF procrec_cat = 4 THEN
		SET payrec_type    = 'Rebill';
		SET payrec_payment = 0;
		SET payrec_charge  = 0;
	ELSEIF procrec_cat = 5 THEN
		SET payrec_type    = 'Charge';
		SET payrec_payment = 0;
		SET payrec_charge  = payrec_amt;
	ELSEIF procrec_cat = 6 THEN
		SET payrec_type    = CONCAT('Transfer to ',PAYER_TYPE(procrec_src));
		SET payrec_payment = 0;
		SET payrec_charge  = 0;
	ELSEIF procrec_cat = 7 THEN
		SET payrec_type    = 'Withhold';
		SET payrec_payment = 0;
		SET payrec_charge  = -1*payrec_amt;
	ELSEIF procrec_cat = 8 THEN
		SET payrec_type    = 'Deductable';
		SET payrec_payment = -1*payrec_amt;
		SET payrec_charge  = 0;
	ELSEIF procrec_cat = 9 THEN
		SET payrec_type    = 'Allowed Amount - Fee Adjusted';
		SET payrec_payment = 0;
		SET payrec_charge  = -1*payrec_amt;
	ELSEIF procrec_cat = 10 THEN
		SET payrec_type    = CONCAT('Billed ',PAYER_TYPE(procrec_src));
		SET payrec_payment = 0;
		SET payrec_charge  = 0;
	ELSEIF procrec_cat = 11 THEN
		SET payrec_type    = 'Copay';
		SET payrec_payment = payrec_amt;
		SET payrec_charge  = 0;
	ELSEIF procrec_cat = 12 THEN
		SET payrec_type    = 'Writeoff';
		SET payrec_payment = 0;
		SET payrec_charge  = -1*payrec_amt;
	ELSE 
		SET payrec_type = CONCAT('Payment ',PAYER_TYPE(procrec_src));
		SET payrec_payment = payrec_amt;
		SET payrec_charge  = 0;
	END IF;

	
	INSERT  INTO ledger_info(Id,date,dsc,type,charge,payment,patient)
	VALUES  (payrec_id,payrec_date,payrec_desc,payrec_type,payrec_charge,payrec_payment,payrec_patient);

	FETCH  cur_procrec INTO payrec_id,payrec_date,payrec_desc,payrec_amt,payrec_type,payrec_proc,procrec_cat,procrec_src,payrec_patient;
	UNTIL  no_more_records = 1
	END REPEAT;
	CLOSE  cur_procrec;
	SELECT * from ledger_info;
	DROP TEMPORARY TABLE ledger_info;
 
END//

DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_PatientReceipt_en_US';

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
		'Patient Receipt',
		'911cacf6-fb2b-4bbd-9e9b-cf1cb72aecac',
		'en_US',
		'Patient Receipt',
		'jasper',
		'billing_report',
		'report_PatientReceipt_en_US',
		1,
		'Procedure ',
		'int',
		'0',
		'PatientReceipt_en_US'
	);

