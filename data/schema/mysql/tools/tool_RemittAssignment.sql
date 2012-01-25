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

# File: Tool - REMITT Assignment

DROP PROCEDURE IF EXISTS tool_RemittAssignment;
DELIMITER //

# Function: tool_RemittAssignment
#
#	Assign a set of REMITT parameters to all payers in the system.
#
CREATE PROCEDURE tool_RemittAssignment (
	  IN pFormat VARCHAR (100)
	, IN pTarget VARCHAR (100)
	, IN pTargetOption VARCHAR (100)
	, IN eFormat VARCHAR (100)
	, IN eTarget VARCHAR (100)
	, IN eTargetOption VARCHAR (100)
)
BEGIN
	SET @sql = CONCAT(
		"UPDATE insco ",
			"SET ",
			"  inscodefformat = '", REPLACE(pFormat, "'", "\\'"), "' ",
			", inscodeftarget = '", REPLACE(pTarget, "'", "\\'"), "' ",
			", inscodeftargetopt = '", REPLACE(pTargetOption, "'", "\\'"), "' ",
			", inscodefformate = '", REPLACE(eFormat, "'", "\\'"), "' ",
			", inscodeftargete = '", REPLACE(eTarget, "'", "\\'"), "' ",
			", inscodeftargetopte = '", REPLACE(eTargetOption, "'", "\\'"), "' ",
		" ; "
	) ;

	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;
END //

DELIMITER ;

#	Add indices

DELETE FROM `tools` WHERE tool_sp = 'tool_RemittAssignment';

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
		'REMITT Assignment',
		'a132a3f2-0f61-4f50-b3b6-ee4b0aad22d9',
		'en_US',
		'Assign a set of REMITT parameters to all payers in the system.',
		'tool_RemittAssignment',
		6,
                'Paper Format,Paper Target,Paper Target Option,Electronic Format,Electronic Target,Electronic Target Option',
                'Text,Text,Text,Text,Text,Text',
                '',
                '1,1,0,1,1,0'
	);

