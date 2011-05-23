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

#----- Drop all old tables -----

DROP TABLE IF EXISTS pqri_source;

#----- Table definitions -----

CREATE TABLE pqri_source (
	  topic_measure		INT UNSIGNED
	, coding_system		ENUM ( 'CPT_II', 'C4', 'I9', 'HCPCS' ) NOT NULL
	, code			VARCHAR ( 20 )
	, modifier		ENUM ( '', '1P', '2P', '3P', '8P', '? 52, 53, 73 or 74' ) NOT NULL
	, place_of_service	CHAR (3) NOT NULL DEFAULT ''
	, effective_date	VARCHAR (20)

	, KEY			( coding_system, code )
);

#----- Import PQRI from CSV export files -----

LOAD DATA LOCAL INFILE 'data/source/pqri/2009_PQRI_SingleSource_PUBLIC_CodeMaster_120808.csv'
	INTO TABLE pqri_source
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;

