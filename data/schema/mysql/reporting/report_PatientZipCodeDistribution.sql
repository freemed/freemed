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

# File: Report - Patient Zip Code Distribution

DROP PROCEDURE IF EXISTS report_PatientZipCodeDistribution_en_US ;
DELIMITER //

# Function: report_PatientZipCodeDistribution_en_US
#
#	Distribution of patients by zip codes.
#
CREATE PROCEDURE report_PatientZipCodeDistribution_en_US ( )
BEGIN
	SELECT COUNT(*) INTO @C FROM patient WHERE ptarchive=0;
	SET @sql = CONCAT(
		"SELECT CONCAT(ptcity, ', ', ptstate) AS 'City', ptzip AS 'Zip', ROUND(( COUNT( ptzip ) / ", @C, " ) * 100, 2) AS 'Percentage', COUNT(ptid) AS 'Count' FROM patient WHERE ptarchive=0 GROUP BY ptzip ORDER BY 'Percentage' DESC;"
	) ;

	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;
END //

DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_PatientZipCodeDistribution_en_US';

INSERT INTO `reporting` (
		report_name,
		report_uuid,
		report_locale,
		report_desc,
		report_sp,
		report_param_count
	) VALUES (
		'Patient Zip Code Distribution',
		'b2ed1a07-e078-4a6a-821b-b21df43ab583',
		'en_US',
		'Distribution of patients by zip code.',
		'report_PatientZipCodeDistribution_en_US',
		0
	);

