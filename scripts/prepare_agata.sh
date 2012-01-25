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
#	Prepare agata for insertion into svn
#

VERSION=$1

if [ "${VERSION}" == "" -o ! -f "./agata-${VERSION}.zip" ]; then
	echo "Invalid or not specified version"
	exit
fi

#	Remove old and extract new
rm agata agata7 -Rf
unzip agata-${VERSION}.zip
mv agata agata7

#	Remove all temporary files
( cd agata7 ; find . -print0 | grep -FzZ /. | xargs -0 rm -Rvf {} )

#	Remove everything we don't use
rm -Rvf agata7/{api,dictionary,images,interface,output,projects,reports,resources,sql,themes,web,*.{bat,bmp,db,exe,ico,php,sql,xpm}} 

#	Remove all sxw and odt files
( cd agata7 ; rm $(find . | grep '.sxw') $(find . | grep '.odt') -vf )
