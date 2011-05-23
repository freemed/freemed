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

# File: Report - Patient Demographic Report

DROP PROCEDURE IF EXISTS report_PatientDemographic_en_US;
DELIMITER //

# Function: report_PatientDemographic_en_US
#
#	Patient Demographic Report
#


CREATE PROCEDURE report_PatientDemographic_en_US()
BEGIN
	SET @sql = CONCAT(
		"SELECT ",
			"COUNT(*) AS total_patients, ",
			"SUM(LCASE(ptsex)='m') AS total_male, ",
			"SUM(LCASE(ptsex)='f') AS total_female ",
		"FROM ",
			"patient"
	);
	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;

END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_PatientDemographic_en_US';

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
		'Patient Demographic Report',
		'19c52863-7257-46c9-a4c0-7b2529905e50',
		'en_US',
		'Patient Demographic Report',
		'jasper',
		'billing_report',
		'report_PatientDemographic_en_US',
		0,
		'',
		'',
		'',
		'PatientDemographic_en_US'
	);
