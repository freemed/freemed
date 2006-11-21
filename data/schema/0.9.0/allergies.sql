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

CREATE TABLE `allergies` (
	allergy			VARCHAR (150) NOT NULL,
	severity		VARCHAR (150) NOT NULL,
	patient			INT UNSIGNED NOT NULL DEFAULT 0,
	reviewed		TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	id			INT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY 		( id ),

	#	Define keys

	KEY			( patient, allergy ),
	FOREIGN KEY		( patient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

