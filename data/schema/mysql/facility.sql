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

CREATE TABLE IF NOT EXISTS `facility` (
	psrname			VARCHAR (100) NOT NULL,
	psraddr1		VARCHAR (50),
	psraddr2		VARCHAR (50),
	psrcity			VARCHAR (50),
	psrstate		CHAR (3),
	psrzip			CHAR (10),
	psrcountry		VARCHAR (50),
	psrnote			VARCHAR (40),
	psrdateentry		DATE,
	psrdefphy		INT UNSIGNED NOT NULL DEFAULT 0,
	psrphone		VARCHAR (16),
	psrfax			VARCHAR (16),
	psremail		VARCHAR (25),
	psrein			VARCHAR (9),
	psrintext		INT UNSIGNED NOT NULL DEFAULT 0,
	psrpos			INT UNSIGNED NOT NULL DEFAULT 0,
	psrx12id		VARCHAR (24),
	psrx12idtype		VARCHAR (10),
	id			SERIAL
) ENGINE=InnoDB;

