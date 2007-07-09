# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2007 FreeMED Software Foundation
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

CREATE TABLE IF NOT EXISTS `modules` (
	module_uid			CHAR (36) UNIQUE NOT NULL,
	module_name			VARCHAR (100) NOT NULL,
	module_class			VARCHAR (100) NOT NULL,
	module_table			VARCHAR (100) NOT NULL,
	module_version			VARCHAR (50) NOT NULL,
	module_version_installed	INT UNSIGNED NOT NULL DEFAULT 0,
	module_category			VARCHAR (50) NOT NULL,
	module_path			VARCHAR (250) NOT NULL,
	module_stamp			INT UNSIGNED NOT NULL,
	module_handlers			TEXT,
	module_associations		TEXT,
	module_meta			TEXT,
	module_hidden			TINYINT (3) NOT NULL DEFAULT 0,
	PRIMARY KEY 			( module_uid )
);

