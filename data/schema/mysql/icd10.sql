# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2011 FreeMED Software Foundation
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

### Drop and recreate ###

DROP TABLE IF EXISTS icd10;
DROP TABLE IF EXISTS icd9to10crosswalk;

CREATE TABLE IF NOT EXISTS `icd10` (
	  icd10code		VARCHAR (7) NOT NULL UNIQUE
	, icd10descrip		VARCHAR (100) NOT NULL
	, icdmetadesc		VARCHAR (100)
	, id			SERIAL

	#	Define keys

	, KEY			( icd10code )
	, KEY			( icd10descrip )
);

CREATE TABLE IF NOT EXISTS `icd9to10crosswalk` (
	  icd9code		VARCHAR (6) NOT NULL
	, icd10code		VARCHAR (7) NOT NULL
	, txflags		CHAR (5) NOT NULL

	, KEY			( icd9code )
);

### Load stock data ###

LOAD DATA LOCAL INFILE "data/source/icd-10-cm/icd10.psv"
	INTO TABLE icd10
	FIELDS TERMINATED BY '|';
# ^^ expect warnings, we're not populating all columns.

LOAD DATA LOCAL INFILE "data/source/icd-10-cm/icd9to10crosswalk.psv"
	INTO TABLE icd9to10crosswalk
	FIELDS TERMINATED BY '|';

