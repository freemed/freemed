#!/bin/bash
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

#
#	Dump gettext messages
#

D="$( cd "$(dirname "$0")"; pwd )"

(
	cd "$D" 

	# Convert everything to java properties files
	find src | grep '\.module\.xml$' | grep -v svn | \
		while read X; do
			Y=$( echo "$X" | sed -e 's/\.module\.xml$/.properties/;' )
			echo "$X => $Y"
			xsltproc dump-interface.xsl $X | sed -e 's/ /\\ /g;' | \
				sed -e 's/:/\\:/g;' > $Y
		done

	# Extract all strings
	xgettext -c -k'_' -o ../../locale/gwt.pot \
		$(find src | grep '\.java$' | grep -v svn) \
		$(find src | grep '\.properties$' | grep -v svn)

	# Remove any old build files
	rm -f src/main/webapp/resources/interface/*.properties 2>&1 >> /dev/null

)

