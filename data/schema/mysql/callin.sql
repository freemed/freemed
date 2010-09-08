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

CREATE TABLE IF NOT EXISTS `callin` (
	  cilname		VARCHAR (50) NOT NULL
	, cifname		VARCHAR (50) NOT NULL
	, cimname		VARCHAR (50) DEFAULT ''
	, cihphone		VARCHAR (16)
	, ciwphone		VARCHAR (16)
	, cidob			DATE
	, cicomplaint		TEXT NOT NULL
	, cidatestamp		TIMESTAMP (16) DEFAULT NOW()
	, cifacility		INT UNSIGNED NOT NULL
	, ciphysician		INT UNSIGNED DEFAULT 0
	, ciisinsured		INT UNSIGNED DEFAULT 0
	, coveffdt		TEXT
	, covinsco		INT UNSIGNED
	, covpatinsno		VARCHAR (50) NOT NULL
	, covpatgrpno		VARCHAR (50)
	, covtype		INT UNSIGNED
	, covstatus		INT UNSIGNED DEFAULT 0
	, covrel		CHAR (2) NOT NULL DEFAULT 'S'
	, covlname		VARCHAR (50)
	, covfname		VARCHAR (50)
	, covmname		CHAR (1)
	, covaddr1		VARCHAR (25)
	, covaddr2		VARCHAR (25)
	, covcity		VARCHAR (25)
	, covstate		CHAR (3)
	, covzip		VARCHAR (10)
	, covdob		DATE
	, covsex		ENUM ( 'm', 'f', 't' )
	, covssn		CHAR (9)
	, covinstp		INT UNSIGNED
	, covprovasgn		INT UNSIGNED
	, covbenasgn		INT UNSIGNED
	, covrelinfo		INT UNSIGNED
	, covrelinfodt		DATE
	, covplanname		VARCHAR (33)
	, covisassigning	INT UNSIGNED NOT NULL DEFAULT 1
	, covschool		VARCHAR (50)
	, covemployer		VARCHAR (50)
	, covcopay		REAL
	, covdeduct		REAL
	, ciuser		INT UNSIGNED NOT NULL
	, citookcall		VARCHAR (50) NOT NULL
	, cipatient		INT UNSIGNED DEFAULT 0
	, ciarchive		INT UNSIGNED DEFAULT 0
	, id			SERIAL
);

DROP PROCEDURE IF EXISTS Callin_Convert_From_Patient;
DELIMITER //
CREATE PROCEDURE Callin_Convert_From_Patient ( IN callinpatient INT UNSIGNED )
BEGIN
	DECLARE newPatientId INT UNSIGNED;

	#Create a new patient record
	INSERT INTO `patient` (
		ptdtadd,	
		ptdtmod,
		ptlname,
		ptfname,
		ptmname,
		ptdob,
		ptdoc,
		ptsuffix,
		pthphone,
		ptwphone
	)SELECT
		NOW(),NOW(),cilname, cifname, cimname, cidob, ciphysician, 'Sr', cihphone, ciwphone
	FROM callin
	WHERE id = callinpatient;

	SELECT MAX(id) INTO newPatientId FROM patient;

	#Convert all scheduler entries over
	UPDATE scheduler SET
		calpatient = newPatientId, caltype = 'pat'
	WHERE
		calpatient = callinpatient AND caltype = 'temp';

	#Checking and Adding Coverage
	INSERT INTO `coverage` (
			 covdtadd,covdtmod,coveffdt, covinsco, covpatinsno, covpatgrpno, covtype, covstatus, covrel, covlname, covfname, covmname
			, covaddr1, covaddr2, covcity, covstate, covzip, covdob, covsex, covssn, covinstp, covprovasgn, covbenasgn
			, covrelinfo, covrelinfodt, covplanname, covisassigning, covschool, covemployer, covcopay, covdeduct,covpatient
	)SELECT
			 NOW(),NOW(),coveffdt, covinsco, covpatinsno, covpatgrpno, covtype, covstatus, covrel, covlname, covfname, covmname
			, covaddr1, covaddr2, covcity, covstate, covzip, covdob, covsex, covssn, covinstp, covprovasgn, covbenasgn
			, covrelinfo, covrelinfodt, covplanname, covisassigning, covschool, covemployer, covcopay, covdeduct,newPatientId
	FROM callin
	WHERE id = callinpatient and ciisinsured = 1;		
	

	#Converting Callin Initial Intake to Patient Initial Intake
	UPDATE treatment_initial_intake SET
		patient = newPatientId, intaketype = 'pat'
	WHERE
		patient = callinpatient AND intaketype = 'callin';

	#	Alter the original record to point here
	UPDATE callin SET cipatient = newPatientId, ciarchive = 1 WHERE id = callinpatient;
	#	Send back this value
	SELECT newPatientId;
END
//
DELIMITER ;

DELIMITER //

DROP TRIGGER IF EXISTS  `callin_delete`//

CREATE
    /*[DEFINER = { user | CURRENT_USER }]*/
    TRIGGER `callin_delete` AFTER DELETE ON `callin`
    FOR EACH ROW BEGIN
	/*deleting entry from treatment_initial_intake*/
	DELETE FROM treatment_initial_intake WHERE patient = OLD.id AND intaketype = 'callin';
    END//

DELIMITER ;

