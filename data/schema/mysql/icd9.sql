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

CREATE TABLE IF NOT EXISTS `icd9` (
	icd9code		VARCHAR (6) NOT NULL,
	icd10code		VARCHAR (7),
	icd9descrip		VARCHAR (100) NOT NULL UNIQUE,
	icd10descrip		VARCHAR (100),
	icdmetadesc		VARCHAR (100),
	icdng			DATE,
	icddrg			DATE,
	icdnum			INT UNSIGNED DEFAULT 0,
	icdamt			REAL DEFAULT 0.0,
	icdcoll			REAL DEFAULT 0.0,
	id			SERIAL

	#	Define keys

	, KEY			( icd9code )
	, KEY			( icd9descrip )
);

