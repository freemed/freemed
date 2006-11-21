# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2006 FreeMED Software Foundation
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

SOURCE patient.sql

CREATE TABLE IF NOT EXISTS `coverage` (
	covdtadd		DATE,
	covdtmod		DATE,
	covpatient		INT UNSIGNED NOT NULL DEFAULT 0,
	coveffdt		TEXT,
	covinsco		INT UNSIGNED,
	covpatinsno		VARCHAR (50),
	covpatgrpno		VARCHAR (50),
	covtype			INT UNSIGNED,
	covstatus		INT UNSIGNED,
	covrel			CHAR (2) NOT NULL DEFAULT 'S',
	covlname		VARCHAR (50),
	covfname		VARCHAR (50),
	covmname		CHAR (1),
	covaddr1		VARCHAR (25),
	covaddr2		VARCHAR (25),
	covcity			VARCHAR (25),
	covstate		CHAR (3),
	covzip			VARCHAR (10),
	covdob			DATE,
	covsex			ENUM ( 'm', 'f', 't' ),
	covssn			CHAR (9),
	covinstp		INT UNSIGNED,
	covprovasgn		INT UNSIGNED,
	covbenasgn		INT UNSIGNED,
	covrelinfo		INT UNSIGNED,
	covrelinfodt		DATE,
	covplanname		VARCHAR (33),
	covisassigning		INT UNSIGNED NOT NULL DEFAULT 1,
	covschool		VARCHAR (50),
	covemployer		VARCHAR (50),
	covcopay		REAL,
	covdeduct		REAL,
	id			INT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY 		( id ),

	#	Define keys

	KEY			( covpatient, covinsco, covrel ),
	FOREIGN KEY		( covpatient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

