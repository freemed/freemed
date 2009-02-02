# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2009 FreeMED Software Foundation
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

CREATE TABLE IF NOT EXISTS `loinc` (
	loinc_num			CHAR (15) UNIQUE NOT NULL,
	component			VARCHAR (100) NOT NULL,
	property			VARCHAR (50) NOT NULL,
	type_aspct			CHAR (15) NOT NULL DEFAULT '',
	system				VARCHAR (100) NOT NULL,
	scale_typ			CHAR (15) NOT NULL DEFAULT '-',
	method_typ			VARCHAR (100) NOT NULL DEFAULT '*',
	answerlist			TEXT,
	status				CHAR (3) NOT NULL DEFAULT '',
	shortname			CHAR (50) NOT NULL DEFAULT '',
	external_copyright_notice	TEXT,

	#	Define keys

	PRIMARY KEY			( loinc_num ),
	KEY				( component, system, shortname )
);

