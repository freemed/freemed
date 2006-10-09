# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
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

CREATE TABLE `callin` (
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
	id			INT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY 		(id)
);

