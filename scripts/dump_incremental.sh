#!/bin/bash
# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
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

#	Create incremental FreeMED dump

PHYSICAL="$(scripts/cfg-value PHYSICAL_LOCATION)"

if [ ! -d "${PHYSICAL}" ]; then
	echo "Could not find FreeMED!"
	exit -1
fi

if [ ! -f "${PHYSICAL}/data/backup/full" ]; then
	echo "A full backup must exist before an incremental backup can be performed!"
	exit -1
fi

# Create new full (full.inc)
mysqldump \
	--single-transaction \
	--user="$(scripts/cfg-value DB_USER)" \
	--password="$(scripts/cfg-value DB_PASS)" \
	freemed > data/backup/full.inc

# Diff it
diff data/backup/full data/backup/full.inc > data/backup/incremental

# Remove temporary full database
rm -f data/backup/full.inc

