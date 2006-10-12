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

CREATE TABLE `messages` (
	msgby		INT UNSIGNED,
	msgtime		TIMESTAMP (14) DEFAULT NOW(),
	msgfor		INT UNSIGNED,
	msgrecip	TEXT,
	msgpatient	INT UNSIGNED,
	msgperson	VARCHAR (50),
	msgurgency	INT UNSIGNED DEFAULT 3,
	msgsubject	VARCHAR (75),
	msgtext		TEXT,
	msgread		INT UNSIGNED DEFAULT 0,
	msgunique	VARCHAR(32),
	msgtag		VARCHAR(32),
	id		INT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY 	(id),

	# Define keys

	KEY 		( msgfor ),
	KEY 		( msgpatient )
);

