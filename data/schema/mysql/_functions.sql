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

# File: MySQL Function Library

DROP FUNCTION IF EXISTS REMOVE_FROM_SET;

DELIMITER //

# Function: REMOVE_FROM_SET
#
#	MySQL UDF to remove a value from a set.
#
# Parameters:
#
#	str - Value to remove from set. VARCHAR(255)
#
#	strlist - Set to operate on. TEXT
#
# Returns:
#
#	TEXT, strlist set without str value present.
#
CREATE FUNCTION REMOVE_FROM_SET( str VARCHAR(255), strlist TEXT )
	RETURNS TEXT
	DETERMINISTIC CONTAINS SQL
BEGIN
	DECLARE res TEXT DEFAULT NULL;

	IF str = strlist THEN
		RETURN NULL;
	ELSE
		SET res = REPLACE( CONCAT( ',', strlist, ',' ), CONCAT( ',', str, ',' ), ',' );
		RETURN SUBSTRING( res, 2, LENGTH(res) - 2 );
	END IF;
END//

DELIMITER ;

