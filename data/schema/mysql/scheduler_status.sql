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

CREATE TABLE IF NOT EXISTS `scheduler_status` (
	csstamp			TIMESTAMP (14) DEFAULT NOW(),
	cspatient		INT UNSIGNED NOT NULL DEFAULT 0,
	csappt			INT UNSIGNED NOT NULL DEFAULT 0,
	csnote			VARCHAR (250),
	csstatus		INT UNSIGNED NOT NULL DEFAULT 0,
	csuser			INT UNSIGNED NOT NULL,
	id			INT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY 		( id ),

	#	Define keys

	KEY			( cspatient, csstatus, csstamp ),
	FOREIGN KEY		( cspatient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

