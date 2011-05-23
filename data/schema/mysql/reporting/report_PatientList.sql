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

# File: Report - Patient List

DROP PROCEDURE IF EXISTS report_PatientList_en_US ;
DELIMITER //

# Function: report_PatientList_en_US
#
#	List of patients in the system.
#
CREATE PROCEDURE report_PatientList_en_US ( )
BEGIN
	SET @sql = CONCAT(
		"SELECT p.ptlname AS 'Last Name', p.ptfname AS 'First Name', p.ptmname AS 'MI', pa.line1 AS 'Address', CONCAT(pa.city, ', ', pa.stpr,' ', pa.postal) AS 'City/State/Zip', ptid AS 'Patient ID', ptdob AS 'DOB' FROM patient p LEFT OUTER JOIN patient_address pa ON ( p.id = pa.patient AND pa.active = TRUE ) WHERE p.ptarchive = 0 ORDER BY p.ptlname, p.ptfname, p.ptmname, p.ptdob"
	) ;

	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;
END //

DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_PatientList_en_US';

INSERT INTO `reporting` (
		report_name,
		report_uuid,
		report_locale,
		report_desc,
		report_category,
		report_sp,
		report_param_count
	) VALUES (
		'Patient List',
		'771722fb-914c-44b1-a8da-3f22299905cf',
		'en_US',
		'List of patients in the system.',
		'reporting_engine',
		'report_PatientList_en_US',
		0
	);

