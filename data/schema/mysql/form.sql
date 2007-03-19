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

CREATE TABLE IF NOT EXISTS `form` (
	f_uuid			CHAR (36) NOT NULL,
	f_name			VARCHAR (100) NOT NULL,
	f_template		VARCHAR (250) NOT NULL DEFAULT '',
	f_electronic_template	VARCHAR (250) NOT NULL DEFAULT '',
	id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

	#	Define keys

	PRIMARY KEY		( id ),
	KEY			( f_uuid )
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_element` (
	fe_id			BIGINT UNSIGNED NOT NULL DEFAULT 0,
	fe_name			VARCHAR (250) NOT NULL,
	fe_oid_mapping		VARCHAR (100) NOT NULL DEFAULT '',
	fe_x			REAL NOT NULL DEFAULT 0.0,
	fe_y			REAL NOT NULL DEFAULT 0.0,
	fe_h			REAL NOT NULL DEFAULT 0.0,
	fe_w			REAL NOT NULL DEFAULT 0.0,
	fe_conditional		VARCHAR (250) NOT NULL DEFAULT '',
	fe_confidential		ENUM ( 'no', 'yes' ) DEFAULT 'no',
	id			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	FOREIGN KEY		( fe_id ) REFERENCES form ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

