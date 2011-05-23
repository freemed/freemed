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

# File: Report - Outstanding Patient Accounts By Provider

DROP PROCEDURE IF EXISTS report_OutstandingPatientAccountsByProvider_en_US ;
DELIMITER //

# Function: report_OutstandingPatientAccountsByProvider_en_US
#
#	Outstanding patient accounts arranged by provider
#
CREATE PROCEDURE report_OutstandingPatientAccountsByProvider_en_US ( )
BEGIN
	SET @sql = CONCAT(
		"SELECT ",
			"CONCAT(phy.phylname,', ', phy.phyfname, ' ', phy.phymname) AS 'Provider Name', ",
			"phy.id AS 'Provider ID', ",
			"ROUND(SUM(proc.procbalcurrent), 2) AS 'Total Outstanding' ",
            	"FROM ",
			"procrec AS proc, ",
			"physician AS phy ",
            	"WHERE ",
			"proc.procbalcurrent > 0 AND ",
			"proc.procphysician = phy.id ",
		"GROUP BY proc.procphysician ",
		"ORDER BY 'Provider Name' ",
		" ; "
	) ;

	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;
END //

DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_OutstandingPatientAccountsByProvider_en_US';

INSERT INTO `reporting` (
		report_name,
		report_uuid,
		report_locale,
		report_desc,
		report_category,
		report_sp,
		report_param_count
	) VALUES (
		'Outstanding Patient Accounts By Provider',
		'5ddd29ae-e0e2-47cc-a321-316be9c831b5',
		'en_US',
		'Summary of patient account amounts per provider.',
		'reporting_engine',
		'report_OutstandingPatientAccountsByProvider_en_US',
		0
	);

