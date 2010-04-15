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

# File: Report - Charges Graph

DROP PROCEDURE IF EXISTS report_ChargeGraph_en_US;
DELIMITER //

# Function: report_ChargeGraph_en_US
#
#	Charges Graph
#


CREATE PROCEDURE report_ChargeGraph_en_US(IN strDate DATE, IN endDate DATE )
BEGIN
	DECLARE withhold INT UNSIGNED;
	DECLARE deduct INT UNSIGNED;
	DECLARE denial INT UNSIGNED;
	DECLARE allowed INT UNSIGNED;
	DECLARE writeoff INT UNSIGNED;
	DECLARE total INT UNSIGNED;
	SELECT IFNULL(sum(payrecamt),0) INTO withhold FROM payrec WHERE payrecdt >= strDate AND payrecdt <= endDate AND payreccat=7 ; 
	SELECT IFNULL(sum(payrecamt),0) INTO deduct FROM payrec WHERE payrecdt >= strDate AND payrecdt <= endDate AND payreccat=8 ; 
	SELECT IFNULL(sum(payrecamt),0) INTO denial FROM payrec WHERE payrecdt >= strDate AND payrecdt <= endDate AND payreccat=3 ; 
	SELECT IFNULL(sum(payrecamt),0) INTO allowed FROM payrec WHERE payrecdt >= strDate AND payrecdt <= endDate AND payreccat=9 ; 
	SELECT IFNULL(sum(payrecamt),0) INTO writeoff FROM payrec WHERE payrecdt >= strDate AND payrecdt <= endDate AND payreccat=12 ; 
	SELECT (withhold+deduct+denial+allowed+writeoff) INTO total;
	select withhold,deduct,denial,allowed,writeoff,total;

END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_ChargeGraph_en_US';

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
		report_param_options,
		report_param_optional,
		report_formatting
	) VALUES (
		'Charges Graph',
		'1a4c1e43-74fb-43b4-91d7-93102219c161',
		'en_US',
		'Charges Graph',
		'jasper',
		'billing_report',
		'report_ChargeGraph_en_US',
		3,
		'Start Date,End Date,Chart Type',
		'Date,Date,List',
		'0,0,[Pie:pie;Bar:bar]',
		'0,0,0',
		'ChargeGraph_en_US'
	);
