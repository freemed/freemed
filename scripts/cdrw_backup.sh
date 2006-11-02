#!/bin/bash
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

#
# Script for the cdrw_backup module. Params:
# 	cdrw_backup.sh (device) (driver) (speed)

# TODO: exclude CVS directories, pretty up the output, maybe i18n

DEV=$1
DRIVER=$2
SPEED=$3

# Get datestamp
DATESTAMP=`date +%Y%m%d`

# Make temporary ISO path
TMPPATH=/tmp/freemed_cdrw_${PPID}
rm -Rf ${TMPPATH}
mkdir -p ${TMPPATH}

# Create bz2 tar of freemed data
( cd /var/lib/mysql/freemed; tar cjvf ${TMPPATH}/database_${DATESTAMP}.tar.bz2 * 2>&1 > /dev/null )

# Create bz2 tar of freemed installation
( cd /usr/share; tar czvf ${TMPPATH}/freemed_${DATESTAMP}.tar.bz2 freemed phpwebtools 2>&1 > /dev/null )

# Use mkisofs to generate the image
#	-quiet \
mkisofs -o ${TMPPATH}/${DATESTAMP}.iso \
	-V FREEMED_${DATESTAMP} \
	-p "FreeMED CDRW Backup Module" \
	-R -T -l -J -graft-points -max-iso9660-filenames \
	database_${DATESTAMP}.tar.bz2=${TMPPATH}/database_${DATESTAMP}.tar.bz2 \
	freemed_${DATESTAMP}.tar.bz2=${TMPPATH}/freemed_${DATESTAMP}.tar.bz2 \
	2>&1

if [ ! -f ${TMPPATH}/${DATESTAMP}.iso ]; then
	echo "Could not properly generate ISO image for backup!"
	exit;
fi

# Remove temporary tarballs
rm -Rvf ${TMPPATH}/*.tar.bz2

# Burn the actual image to CD, and eject
cdrecord -eject dev=${DEV} speed=${SPEED} driver=${DRIVER} \
	-data ${TMPPATH}/${DATESTAMP}.iso 2>&1

rm -Rvf ${TMPPATH}
