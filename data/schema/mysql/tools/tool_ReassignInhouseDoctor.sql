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

# File: Tool - Reassign Appointments

DROP PROCEDURE IF EXISTS tool_ReassignInhouseDoctor;
DELIMITER //

# Function: tool_ReassignInhouseDoctor
#
#	Re-Assign In House Doctor
#
CREATE PROCEDURE tool_ReassignInhouseDoctor (
	  IN mdFrom INT UNSIGNED
	, IN mdTo INT UNSIGNED
	, IN dBegin DATE
)
BEGIN
	SET @sql = CONCAT(
		"UPDATE patient ",
			"SET ptdoc = ", mdTo, ", ",
				"ptdtmod = NOW() ",
			"WHERE ",
				"ptdoc = ", mdFrom," AND ",
				"DATE(ptdtadd) >= '", dBegin, "'",
		" ; "
	) ;

	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;
END //

DELIMITER ;

#	Add indices

DELETE FROM `tools` WHERE tool_sp = 'tool_ReassignInhouseDoctor';

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
		'Re-Assign In House Doctor',
		'66c59771-380f-4993-b3ba-faca2a31842c',
		'en_US',
		'Replace Patient\'s In House Doctor with another',
		'tool_ReassignInhouseDoctor',
		3,
        'Old Inhouse Doctor,New Inhouse Doctor,Starting Date',
        'Provider,Provider,Date',
        '',
        '1,1,1'
	);

