# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2012 FreeMED Software Foundation
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

CREATE TABLE IF NOT EXISTS `pnotes_templates` (
	pntname			VARCHAR (150) NOT NULL,
	pntphy			INT UNSIGNED NOT NULL DEFAULT 0,
	pnt_S			TEXT,
	pnt_O			TEXT,
	pnt_A			TEXT,
	pnt_P			TEXT,
	pnt_I			TEXT,
	pnt_E			TEXT,
	pnt_R			TEXT,
	pntadded		TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	id			SERIAL,

	#	Define keys
	KEY			( pntphy )
);

