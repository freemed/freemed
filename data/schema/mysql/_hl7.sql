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

# File: MySQL HL7 Stored Procedure Library

DROP PROCEDURE IF EXISTS HL7v2_Resolve_PID;
DROP PROCEDURE IF EXISTS HL7v2_Resolve_PID_Direct;
DELIMITER //

# Function: HL7v2_Resolve_PID
#
#	Resolve HL7 PID segment with criteria
#
# Parameters:
#
#	OUT pid - Output value. INT UNSIGNED.
#
#	IN patient_id - Patient ID. VARCHAR (100).
#
#	IN patient_lname - Patient last name. VARCHAR (100).
#
#	IN patient_fname - Patient first name. VARCHAR (100).
#
#	IN patient_mname - Patient middle name. VARCHAR (100).
#
CREATE PROCEDURE HL7v2_Resolve_PID (
		OUT pid INT UNSIGNED,
		IN patient_id VARCHAR (100),
		IN patient_lname VARCHAR (100),
		IN patient_fname VARCHAR (100),
		IN patient_mname VARCHAR (100)
		)
BEGIN
	DECLARE res INT UNSIGNED DEFAULT 0;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '22000' SET res = 1;

	#	First, try basic patient_id resolution
	SELECT id INTO res FROM patient WHERE ptid=patient_id;

	IF res = 0 THEN
		# Try again, this time with fname, mname, lname
		SELECT id INTO res FROM patient WHERE TO_UPPER(ptlname)=TO_UPPER(patient_lname) AND TO_UPPER(ptfname)=TO_UPPER(patient_fname) AND TO_UPPER(ptmname)=TO_UPPER(patient_mname);
	END IF;

	SET pid = res;
END //

# Function: HL7v2_Resolve_PID_Direct
#
#	Resolve HL7 PID segment with criteria
#
# Parameters:
#
#	IN patient_id - Patient ID. VARCHAR (100).
#
#	IN patient_lname - Patient last name. VARCHAR (100).
#
#	IN patient_fname - Patient first name. VARCHAR (100).
#
#	IN patient_mname - Patient middle name. VARCHAR (100).
#
# Returns:
#
#	pid - Output value. INT UNSIGNED.
#
# SeeAlso:
#	<HL7v2_Resolve_PID>
#
CREATE PROCEDURE HL7v2_Resolve_PID_Direct (
		IN patient_id VARCHAR (100),
		IN patient_lname VARCHAR (100),
		IN patient_fname VARCHAR (100),
		IN patient_mname VARCHAR (100)
		)
BEGIN
	CALL HL7v2_Resolve_PID ( @output, patient_id, patient_lname, patient_fname, patient_mname );
	SELECT @output;
END //
DELIMITER ;

