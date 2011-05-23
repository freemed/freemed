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

# File: Tool - Move Tag

DROP PROCEDURE IF EXISTS tool_MoveTag;
DELIMITER //

# Function: tool_MoveTag
#
#	Move tag.
#
CREATE PROCEDURE tool_MoveTag (
	  IN tFrom VARCHAR (100)
	, IN tTo VARCHAR (100)
)
BEGIN
	SET @sql = CONCAT(
		"UPDATE patienttag ",
			"SET tag = '", REPLACE(tTo, "'", "\\'"), "' ",
			"WHERE tag = '", REPLACE(tFrom, "'", "\\'"), "' ",
		" ; "
	) ;

	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;
END //

DELIMITER ;

#	Add indices

DELETE FROM `tools` WHERE tool_sp = 'tool_MoveTag';

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
		'Move Patient Tag',
		'2957faf8-d93c-47f6-a749-8432cc9bed9f',
		'en_US',
		'Move a patient tag to another',
		'tool_MoveTag',
		2,
                'Original Tag,New Tag',
                'Tag,Tag',
                '',
                '1,1'
	);

