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

CREATE TABLE IF NOT EXISTS `scheduler` (
	caldateof		DATE,
	calcreated		TIMESTAMP (16),
	calmodified		TIMESTAMP (16),
	caltype			ENUM( 'temp', 'pat' ) NOT NULL DEFAULT 'pat',
	calhour			INT UNSIGNED,
	calminute		INT UNSIGNED,
	calduration		INT UNSIGNED,
	calfacility		INT UNSIGNED,
	calroom			INT UNSIGNED,
	calphysician		INT UNSIGNED,
	calpatient		INT UNSIGNED,
	calcptcode		INT UNSIGNED,
	calstatus		ENUM ( 'scheduled', 'confirmed', 'attended', 'cancelled', 'noshow', 'tenative' ) NOT NULL DEFAULT 'scheduled',
	calprenote		VARCHAR (250),
	calpostnote		TEXT,
	calmark			INT UNSIGNED,
	calgroupid		INT UNSIGNED,
	calrecurnote		VARCHAR (100),
	calrecurid		INT UNSIGNED,
	calappttemplate		INT UNSIGNED,
	id			SERIAL,

	# Define keys

	KEY			( caldateof, calhour, calminute ),
	FOREIGN KEY		( calpatient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

