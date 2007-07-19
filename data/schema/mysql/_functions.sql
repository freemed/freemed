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
END;
//

DELIMITER ;

DROP FUNCTION IF EXISTS SPLIT_ELEMENT;

DELIMITER //

# Function: SPLIT_ELEMENT
#
#	MySQL UDF to pull out set elements from a substring, from MySQL user manual
#
# Parameters:
#
#	x - Value to examine. VARCHAR( 255 )
#
#	delim - String to use as a delimited. VARCHAR( 12 )
#
#	pos - SET position of desired element. INT
#
# Returns:
#
#	VARCHAR( 255 ), SET element
#
CREATE FUNCTION SPLIT_ELEMENT( x VARCHAR( 255 ), delim VARCHAR( 12 ), pos INT )
	RETURNS VARCHAR( 255 )
	DETERMINISTIC
BEGIN
	RETURN REPLACE( SUBSTRING( SUBSTRING_INDEX( x, delim, pos ), LENGTH( SUBSTRING_INDEX( x, delim, pos - 1 ) ) + 1), delim, '' );
END;
//

DELIMITER ;

DROP FUNCTION IF EXISTS SUBSTR_COUNT;

DELIMITER //

# Function: SUBSTR_COUNT
#
#	MySQL UDF to count instances of a substring, from MySQL user manual
#
# Parameters:
#
#	s - Value to examine. VARCHAR(255)
#
#	ss - String to search for. VARCHAR(255)
#
# Returns:
#
#	TINYINT(3) UNSIGNED, number of occurrances of ss in s
#
CREATE FUNCTION SUBSTR_COUNT ( s VARCHAR(255), ss VARCHAR(255) ) RETURNS TINYINT(3) UNSIGNED
	LANGUAGE SQL
	NOT DETERMINISTIC
	READS SQL DATA
BEGIN
	DECLARE count TINYINT(3) UNSIGNED;
	DECLARE offset TINYINT(3) UNSIGNED;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET s = NULL;

	SET count = 0;
	SET offset = 1;

	REPEAT
		IF NOT ISNULL(s) AND offset > 0 THEN
			SET offset = LOCATE(ss, s, offset);
			IF offset > 0 THEN
				SET count = count + 1;
				SET offset = offset + 1;
			END IF;
		END IF;
	UNTIL ISNULL(s) OR offset = 0 END REPEAT;
	RETURN count;
END;
//

DELIMITER ;

DROP PROCEDURE IF EXISTS FreeMED_Module_UpdateVersion;

DELIMITER //

# Procedure: FreeMED_Module_UpdateVersion
#
#	Update internal version of FreeMED module for table versioning.
#
# Parameters:
#
#	t - IN VARCHAR(255), Table name of module
#
#	v - IN INT UNSIGNED, New version number
#
CREATE PROCEDURE FreeMED_Module_UpdateVersion ( IN t VARCHAR(255), IN v INT UNSIGNED )
	LANGUAGE SQL
	NOT DETERMINISTIC
	MODIFIES SQL DATA
BEGIN
	UPDATE modules SET module_version_installed = v WHERE module_table = t;
END;
//

DELIMITER ;

DROP PROCEDURE IF EXISTS FreeMED_Module_GetVersion;

DELIMITER //

# Procedure: FreeMED_Module_GetVersion
#
#	Lookup internal version of FreeMED module table
#
# Parameters:
#
#	t - IN VARCHAR(255), Table name of module
#
#	v - OUT INT UNSIGNED, Version
#
CREATE PROCEDURE FreeMED_Module_GetVersion ( IN t VARCHAR(255), OUT v INT UNSIGNED )
	LANGUAGE SQL
	NOT DETERMINISTIC
	READS SQL DATA
BEGIN
	SELECT IF(ISNULL(module_version_installed), 0, module_version_installed) INTO v FROM modules WHERE module_table = t;
END;
//

DELIMITER ;

