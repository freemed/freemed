#!/bin/bash
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

if [ ! -r drug_mlt.mdb ]; then
	if [ ! -r "$1" ]; then
		echo "drug_mlt.mdb needs to exist in the current directory, or needs to be passed"
		echo "as a parameter to this script."
		exit
	else
		P="$1"	
	fi
else
	P=drug_mlt.mdb
fi

if [ ! -x "$(which mdb-tables)" ]; then
	echo "mdbtools needs to be installed!"
	exit
fi

DRUG_MLT_TABLES=$( mdb-tables "$P" )

for T in $DRUG_MLT_TABLES; do
	echo " - Extracting ${T} -> ${T}.csv"
	mdb-export "$P" "${T}" > "$( dirname "$0" )/../data/multum/${T}.csv"
done

#	Remove denormalized table, since we don't use it
echo " - Removing ndc_denorm (denormalized table)"
rm -f "$( dirname "$0" )/../data/multum/ndc_denorm.csv"

echo " ! Completed ! "

