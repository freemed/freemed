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

echo "\$Id$"
echo "(c) 2006 by the FreeMED Software Foundation"
echo " "

if [ ! -f ./scripts/tsmarty2c.php ]; then
	echo "Needs to be run from the FreeMED root directory."
	exit
fi

VERSION=$( cat lib/freemed.php | grep DISPLAY_VERSION | cut -d'"' -f2 )

for UI in ui/*; do

	if [ -d ${UI} ] ; then

	echo " * Processing interface ${UI}"

	echo -n " - Creating translation catalog ... "
	php ./scripts/tsmarty2c.php ${UI}/view | xgettext --language=C -o - - > "locale/$(basename "${UI}").pot"
	echo "[done]"

	perl -pi -e "s/PACKAGE VERSION/FreeMED v${VERSION}/;" "locale/$(basename "${UI}").pot"

	echo " "

	fi

done

