# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2009 FreeMED Software Foundation
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

CREATE TABLE IF NOT EXISTS `callin` (
	cilname			VARCHAR (50) NOT NULL,
	cifname			VARCHAR (50) NOT NULL,
	cimname			VARCHAR (50) DEFAULT '',
	cihphone		VARCHAR (16),
	ciwphone		VARCHAR (16),
	cidob			DATE,
	cicomplaint		TEXT NOT NULL,
	cidatestamp		TIMESTAMP (16) DEFAULT NOW(),
	cifacility		INT UNSIGNED NOT NULL,
	ciphysician		INT UNSIGNED DEFAULT 0,
	ciuser			INT UNSIGNED NOT NULL,
	citookcall		VARCHAR (50) NOT NULL,
	cipatient		INT UNSIGNED DEFAULT 0,
	id			SERIAL
);

DROP PROCEDURE IF EXISTS Callin_Convert_From_Patient;
DELIMITER //
CREATE PROCEDURE Callin_Convert_From_Patient ( IN callinpatient INT UNSIGNED )
BEGIN
	DECLARE newPatientId INT UNSIGNED;
	DECLARE _cilname VARCHAR (50);
	DECLARE _cimname VARCHAR (50);
	DECLARE _cifname VARCHAR (50);
	DECLARE _cihphone VARCHAR (16);
	DECLARE _ciwphone VARCHAR (16);
	DECLARE _cidob DATE;
	DECLARE _ciphysician INT UNSIGNED;

	#	Get the old record
	SELECT
		cilname, cifname, cimname, cidob, ciphysician, cihphone, ciwphone
	INTO
		_cilname, _cifname, _cimname, _cidob, _ciphysician, _cihphone, _ciwphone
	FROM callin
	WHERE id=callinpatient;

	#	Create a new patient record
	INSERT INTO `patient` (
		ptlname,
		ptfname,
		ptmname,
		ptdob,
		ptdoc,
		pthphone,
		ptwphone
	) VALUES (
		_cilname,
		_cifname,
		_cimname,
		_cidob,
		_ciphysician,
		_cihphone,
		_ciwphone
	);
	SELECT MAX(id) INTO newPatientId FROM patient;

	#	Convert all scheduler entries over
	UPDATE scheduler SET
		calpatient = newPatientId, caltype = 'pat'
	WHERE
		calpatient = callinpatient AND caltype = 'temp';

	#	Alter the original record to point here
	UPDATE callin SET cipatient=newPatientId WHERE id=callinpatient;

	#	Send back this value
	SELECT newPatientId;
END
//
DELIMITER ;

