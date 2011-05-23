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

function cvt() {
	mkdir -p ../../../hcpcs 2>&1 >> /dev/null
	echo " --> Converting $1 to $2 <-- "
	cat $1 | ../fixed-to-csv.pl $3 > ../../../hcpcs/$2.csv
}

echo "HCPCS Source Data Conversion Tool"
echo "(c) 2009-2011 FreeMED Software Foundation under the GPL"
echo " "

rm -rf extract
rm ../../drugs/* -f

ZIP=11anweb.zip
FILE=11anweb_v3.txt

mkdir extract
(
	cd extract

	echo "Converting NDC files"
	echo " "

	echo -n " --> Extracting $ZIP ... "
	unzip -qLL ../${ZIP}
	echo " done <--"
	
	cvt $FILE hcpcs 1-5,6-10,11,12-91,92-119,120-121,128,129-134,147-154,171-180,181-183,205-209,230,231-232,233-240,241-243,244,245-252,253-256,257-259,260,261,268,269-276,277-284,285-292,293

	echo " "
)

rm extract -Rf

