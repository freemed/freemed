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

# File: Report - Receivables Graph

DROP PROCEDURE IF EXISTS report_ReceivablesGraph_en_US;
DELIMITER //

# Function: report_ReceivablesGraph_en_US
#
#	Receivables Graph
#


CREATE PROCEDURE report_ReceivablesGraph_en_US(IN strDate DATE, IN endDate DATE )
BEGIN
	DECLARE patient INT UNSIGNED;
	DECLARE copay INT UNSIGNED;
	DECLARE insurance INT UNSIGNED;
	DECLARE total INT UNSIGNED;
	SELECT IFNULL(sum(payrecamt),0) INTO patient FROM payrec WHERE payrecdt >= strDate AND payrecdt <= endDate AND payreccat=0 AND payrecsource = 0; 
	SELECT IFNULL(sum(payrecamt),0) INTO copay FROM payrec WHERE payrecdt >= strDate AND payrecdt <= endDate AND payreccat=11 ; 
	SELECT IFNULL(sum(payrecamt),0) INTO insurance FROM payrec WHERE payrecdt >= strDate AND payrecdt <= endDate AND payreccat=0 AND payrecsource > 0 ; 
	SELECT (patient+copay+insurance) INTO total;
	select patient,copay,insurance,total;

END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_ReceivablesGraph_en_US';

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
		'Receivables Graph',
		'f1b55224-46a7-4d23-98b9-5e822e32131d',
		'en_US',
		'Receivables Graph',
		'jasper',
		'billing_report',
		'report_ReceivablesGraph_en_US',
		2,
		'Start Date,End Date',
		'Date,Date',
		'0,0',
		'ReceivablesGraph_en_US'
	);
