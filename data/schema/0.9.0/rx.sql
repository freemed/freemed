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

CREATE TABLE `rx` (
	rxdtadd			DATE,
	rxdtmod			DATE,
	rxphy			INT UNSIGNED,
	rxpatient		INT UNSIGNED,
	rxdtfrom		DATE,
	rxdrug			VARCHAR (150),
	rxform			VARCHAR (32),
	rxdosage		VARCHAR (128),
	rxquantity		REAL,
	rxsize			VARCHAR (32),
	rxunit			VARCHAR (32),
	rxinterval		ENUM( 'b.i.d.', 't.i.d.', 'q.i.d.', 'q. 3h', 'q. 4h', 'q. 5h', 'q. 6h', 'q. 8h', 'q.d.', 'h.s.', 'q.h.s.', 'q.A.M.', 'q.P.M.', 'a.c.', 'p.c.', 'p.r.n.' ),
	rxsubstitute		ENUM( 'may substitute', 'may not substitute' ),
	rxrefills		INT UNSIGNED,
	rxperrefill		INT UNSIGNED,
	rxorigrx		INT UNSIGNED,
	rxnote			TEXT,
	locked			INT UNSIGNED DEFAULT 0,
	id			INT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY 		( id ),

	#	Default key

	KEY			( rxpatient )
);

