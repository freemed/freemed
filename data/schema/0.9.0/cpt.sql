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

CREATE TABLE IF NOT EXISTS `cpt` (
	cptcode			CHAR (7) NOT NULL,
	cptnameint		VARCHAR (50),
	cptnameext		VARCHAR (50),
	cptgender		ENUM ( 'n', 'm', 'f' ) DEFAULT 'n',
	cpttaxed		ENUM ( 'n', 'y' ) DEFAULT 'n',
	cpttype			INT UNSIGNED DEFAULT 0,
	cptreqcpt		TEXT,
	cptexccpt		TEXT,
	cptreqicd		TEXT,
	cptexcicd		TEXT,
	cptrelval		REAL DEFAULT 1,
	cptdeftos		INT UNSIGNED DEFAULT 0,
	cptdefstdfee		REAL DEFAULT 0,
	cptstdfee		TEXT,
	cpttos			TEXT,
	cpttosprfx		TEXT,
	id			INT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY 		( id )
) ENGINE=InnoDB;

