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

# File: Utility - Reassign Appointments

DROP PROCEDURE IF EXISTS utility_ReassignAppointments;
DELIMITER //

# Function: utility_ReassignAppointments
#
#	Reassign appointments
#
CREATE PROCEDURE utility_ReassignAppointments (
	  IN pFrom INT UNSIGNED
	, IN pTo INT UNSIGNED
	, IN dBegin DATE
	, IN dEnd DATE
)
BEGIN
	SET @sql = CONCAT(
		"UPDATE scheduler ",
			"SET calphysician = ", pTo, ", ",
				"calmodified = NOW() ",
			"WHERE ",
				"calphysician = ", pFrom," AND ",
				"( caldateof >= '", dBegin, "' AND ",
				"  caldateof <= '", dEnd,   "' ) ",
				"AND calstatus NOT IN ( 'cancelled', 'noshow', 'attended' )",
		" ; "
	) ;

	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;
END //

DELIMITER ;

#	Add indices

DELETE FROM `utilities` WHERE utility_sp = 'utility_ReassignAppointments';

INSERT INTO `utilities` (
		utility_name,
		utility_uuid,
		utility_locale,
		utility_desc,
		utility_sp,
		utility_param_count,
		utility_param_names,
		utility_param_types,
		utility_param_options,
		utility_param_optional
	) VALUES (
		'Reassign Appointments',
		'2957faf8-d93c-47f6-a749-8432cc9bed9f',
		'en_US',
		'Reassign scheduler appointments to another provider.',
		'utility_ReassignAppointments',
		4,
                'Original Provider,New Provider,Starting Date,Ending Date',
                'Provider,Provider,Date,Date',
                ',,Start,End',
                '1,1,1,1'
	);

