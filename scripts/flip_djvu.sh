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
#	Flip Djvu image
#

INPUT=$1

if [ "${INPUT}" == "" ]; then exit 1; fi
if [ ! -f "${INPUT}" ]; then exit 1; fi

TEMPDIR="/tmp/flipdjvu-$$"
SECOND="${TEMPDIR}/output.ps"
FINAL="${TEMPDIR}/$(basename "${INPUT}")"

mkdir -p "${TEMPDIR}"

djvups -format=ps "${INPUT}" "${SECOND}"
djvudigital --dpi=200 --psrotate=180 "${SECOND}" "${FINAL}"
mv -f "${FINAL}" "${INPUT}"

rmdir -Rf "${TEMPDIR}"

