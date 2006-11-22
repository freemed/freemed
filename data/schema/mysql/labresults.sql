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

SOURCE data/schema/mysql/patient.sql
SOURCE data/schema/mysql/labs.sql

CREATE TABLE IF NOT EXISTS `labresults` (
	labid			INT UNSIGNED NOT NULL,
	labpatient		INT UNSIGNED NOT NULL,
	labobsnote		TEXT,
	labobscode		VARCHAR (150),
	labobsdescrip		VARCHAR (250),
	labobsvalue		TEXT,
	labobsunit		VARCHAR (150),
	labobsranges		VARCHAR (50),
	labobsabnormal		CHAR (5),
	labobsstatus		CHAR (1),
	labobsreported		TIMESTAMP (14),
	labobsfiller		VARCHAR (60),
	id			INT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY 		( id ),

	#	Define keys

	KEY			( labpatient, labid ),
	FOREIGN KEY		( labpatient ) REFERENCES patient ( id ) ON DELETE CASCADE,
	FOREIGN KEY		( labid ) REFERENCES labs ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

