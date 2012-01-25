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

# File: Unreturned Take Home

DROP PROCEDURE IF EXISTS report_UnreturnedTakeHome_en_US;
DELIMITER //

# Function: report_UnreturnedTakeHome_en_US
#
#	Unreturned Take Home
#

CREATE PROCEDURE report_UnreturnedTakeHome_en_US (IN facID INT)
BEGIN
	SET @sql = CONCAT(
		"SELECT ",
			"patient.ptid AS 'ID', ",
			"CONCAT(",
				"patient.ptlname,",
				"', ',",
				"patient.ptfname,",
				"' ',",
				"patient.ptmname",
			") AS 'Name', ",
			"SUM(doseplan.doseplantakehomecountgiven-doseplan.doseplantakehomecountreturned) AS 'remaining' ",
		"from ",
			"doseplan,patient ",
		"WHERE ",
			"doseplan.doseplanpatient=patient.id AND ",
			"patient.ptprimaryfacility=",facID," AND ",
			"(doseplan.doseplantakehomecountgiven-doseplan.doseplantakehomecountreturned)>0 ",
		"GROUP BY ID"
	);
	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;
END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_UnreturnedTakeHome_en_US';
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
		'Unreturned Take Home',
		'5578815c-b0e1-4208-bd05-832aa7be0b55',
		'en_US',
		'Unreturned Take Home',
		'jasper',
		'inventory_report',
		'report_UnreturnedTakeHome_en_US',
		1,
		'Facility',
		'Facility',
		'0',
		'UnreturnedTakeHome_en_US'
	);

