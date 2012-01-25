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

DROP FUNCTION IF EXISTS TRANSLATE_CHARS;

DELIMITER //

# Function: TRANSLATE_CHARS
#
#	MySQL UDF to perform translation of strings
#
# Parameters:
#
#	s - Value to examine. VARCHAR(255)
#
#	f - From lookup table. VARCHAR (255)
#
#	t - To lookup table. VARCHAR (255)
#
# Returns:
#
#	VARCHAR (255) containing translated string
#
CREATE FUNCTION TRANSLATE_CHARS ( s VARCHAR(255), f VARCHAR(255), t VARCHAR(255) )
	RETURNS VARCHAR(255)
	LANGUAGE SQL
	DETERMINISTIC
BEGIN
	DECLARE d VARCHAR(255);
	DECLARE ch CHAR(1);
	DECLARE offset TINYINT(3) UNSIGNED;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET d = NULL;

	SET offset = 1;
	SET d = '';

	REPEAT
		SET ch = SUBSTRING( s FROM offset FOR 1 );
		SET d = CONCAT( d, SUBSTRING( t FROM INSTR( f, ch ) FOR 1 ) );
		SET offset = offset + 1;
	UNTIL offset > LENGTH( s ) END REPEAT;
	RETURN d;
END;
//

DELIMITER ;

DROP FUNCTION IF EXISTS STRING_TO_PHONE;

DELIMITER //

# Function: STRING_TO_PHONE
#
#	MySQL UDF to translate a string to a phone number string
#
# Parameters:
#
#	s - Value to examine. VARCHAR(255)
#
# Returns:
#
#	VARCHAR (255) containing translated string
#
CREATE FUNCTION STRING_TO_PHONE ( s VARCHAR(255) )
	RETURNS VARCHAR(255)
	LANGUAGE SQL
	DETERMINISTIC
BEGIN
	RETURN TRANSLATE_CHARS( LOWER( REPLACE( s, ' ', '' ) ), 'abcdefghijklmnopqrstuvwxyz', '22233344455566671778889991' );
END;
//

DELIMITER ;

DROP FUNCTION IF EXISTS DAYS_INT_TO_STRING;

DELIMITER //

# Function: DAYS_INT_TO_STRING
#
#	Converts days, passed in the format '0,1,2', into name of days('Sunday,Monday,Tuesday')
#
# Parameters:
#
#	s - Days in integer format and seperated by comma. VARCHAR(255)
#
# Returns:
#
#	VARCHAR (255) containing name of days and seperated by comma
#
CREATE FUNCTION DAYS_INT_TO_STRING ( s VARCHAR(255) )
	RETURNS VARCHAR(255)
	LANGUAGE SQL
	DETERMINISTIC
BEGIN
	DECLARE dat VARCHAR(11);
	DECLARE len INT(4);
	SET s = REPLACE(s,'0','Sun');
	SET s = REPLACE(s,'1','Mon');
	SET s = REPLACE(s,'2','Tue');
	SET s = REPLACE(s,'3','Wed');
	SET s = REPLACE(s,'4','Thu');
	SET s = REPLACE(s,'5','Fri');
	SET s = REPLACE(s,'6','Sat');
	SET len = LENGTH(s);
	RETURN SUBSTRING(s FROM 1 FOR len-1 );
END;
//

DELIMITER ;

DROP FUNCTION IF EXISTS PAYER_TYPE;

DELIMITER //

# Function: PAYER_TYPE
#
#	Converts paysrc, passed in the format '0,1,2', into name of pay sources
#
# Parameters:
#
#	paysrc - Days in integer format
#
# Returns:
#
#	VARCHAR (255) containing name of source
#

CREATE FUNCTION PAYER_TYPE (paysrc INT )
	RETURNS varchar(50) CHARSET latin1
	DETERMINISTIC
BEGIN
	DECLARE pay_source VARCHAR(50);
	IF paysrc = 0 THEN
		SET pay_source = 'Patient';
	ELSEIF paysrc = 1 THEN
		SET pay_source = 'Primary';
	ELSEIF paysrc = 2 THEN
		SET pay_source = 'Secondary';
	ELSEIF paysrc = 3 THEN
		SET pay_source = 'Tertiary';
	ELSEIF paysrc = 4 THEN
		SET pay_source = 'WorkComp';
	END IF;
	RETURN pay_source;
    END//

DELIMITER ;

