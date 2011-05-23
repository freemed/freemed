#!/bin/bash
# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2011 FreeMED Software Foundation
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

ENGINE=$1
TABLE=$2
DBUSER=$3
DBPASS=$4
DBNAME=$5
SKIP_FK=$6

# Get current path so we can be sure to load from proper location
OLDDIR="$(pwd)"
PWD="$(dirname "$0")/.."

if [ ! -f "${PWD}/controller.php" ]; then
	echo "ERROR: improper directory"
	exit 1
fi

if [ ! -f "${PWD}/data/schema/${ENGINE}/${TABLE}.sql" ]; then
	echo "ERROR: could not find schema for ${TABLE}"
	exit 1
fi

# Run actual MySQL command to do this...
case "${ENGINE}" in
	mysql)
	cd "${PWD}"
	if [ "${SKIP_FK}" == "1" ]; then
		cat "data/schema/${ENGINE}/${TABLE}.sql" | grep -v 'FOREIGN KEY' | mysql --user="${DBUSER}" --password="${DBPASS}" "${DBNAME}"
	else
		mysql --user="${DBUSER}" --password="${DBPASS}" "${DBNAME}" < "data/schema/${ENGINE}/${TABLE}.sql"
	fi
	;;

	*)
	;;
esac

# Restore old environment
cd "${OLDDIR}"

