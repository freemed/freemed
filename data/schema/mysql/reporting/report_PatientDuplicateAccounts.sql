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

# File: Report - Patient Duplicate Accounts

DROP PROCEDURE IF EXISTS report_PatientDuplicateAccounts_en_US ;
DELIMITER //

# Function: report_PatientDuplicateAccounts_en_US
#
#	List of patients in the system.
#
CREATE PROCEDURE report_PatientDuplicateAccounts_en_US ( )
BEGIN
	SET @sql = CONCAT(
		"SELECT ptlname AS 'Last Name', ptfname AS 'First Name', ptid AS 'Practice ID', GROUP_CONCAT(id) AS 'IDs', COUNT(id) AS 'dupeCount' FROM patient WHERE NOT ISNULL(ptid) AND ptarchive = 0 GROUP BY ptid HAVING dupeCount > 1;"
	) ;

	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;
END //

DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_PatientDuplicateAccounts_en_US';

INSERT INTO `reporting` (
		report_name,
		report_uuid,
		report_locale,
		report_desc,
		report_category,
		report_sp,
		report_param_count
	) VALUES (
		'Patient Duplicate Accounts',
		'b1f1f894-7046-4f70-a40a-2ebad66aed27',
		'en_US',
		'Duplicate patient accounts in the system.',
		'reporting_engine',
		'report_PatientDuplicateAccounts_en_US',
		0
	);

