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

# File: Tool - Reassign Appointments

DROP PROCEDURE IF EXISTS tool_ReassignAppointments;
DELIMITER //

# Function: tool_ReassignAppointments
#
#	Reassign appointments
#
CREATE PROCEDURE tool_ReassignAppointments (
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

DELETE FROM `tools` WHERE tool_sp = 'tool_ReassignAppointments';

INSERT INTO `tools` (
		tool_name,
		tool_uuid,
		tool_locale,
		tool_desc,
		tool_sp,
		tool_param_count,
		tool_param_names,
		tool_param_types,
		tool_param_options,
		tool_param_optional
	) VALUES (
		'Reassign Appointments',
		'765e8c11-91c1-489b-be97-59d849ce9430',
		'en_US',
		'Reassign scheduler appointments to another provider.',
		'tool_ReassignAppointments',
		4,
                'Original Provider,New Provider,Starting Date,Ending Date',
                'Provider,Provider,Date,Date',
                ',,Start,End',
                '1,1,1,1'
	);

