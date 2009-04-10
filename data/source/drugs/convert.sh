#!/bin/bash
# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2009 FreeMED Software Foundation
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
	echo " --> Converting $1 to $2 <-- "
	cat $1 | ../fixed-to-csv.pl $3 > ../../../drugs/$2.csv
}

echo "NDC/Orange Book Data Conversion Tool"
echo "(c) 2009 FreeMED Software Foundation under the GPL"
echo " "

rm -rf extract
rm ../../drugs/* -f

mkdir extract
(
	cd extract

	echo "Converting NDC files"
	echo " "

	echo -n " --> Extracting ziptext.zip ... "
	unzip -qLL ../ziptext.zip
	echo " done <--"

	cvt listings.txt ndc_listings 1-7,9-14,16-19,21-30,32-41,43-43,45-144
	cvt packages.txt ndc_packages 1-7,9-10,12-36,38-62
	cvt formulat.txt ndc_formulations 1-7,9-18,20-24,26-125
	cvt applicat.txt ndc_applications 1-7,9-14,16-18
	cvt firms.txt ndc_firms 1-6,8-72,74-113,115-154,156-164,166-205,207-236,238-239,241-249,251-280,282-321
	cvt routes.txt ndc_routes 1-7,9-11,13-252
	cvt doseform.txt ndc_dosage_form 1-7,9-11,13-252
	cvt tbldosag.txt ndc_tbl_dosage 1-3,5-104
	cvt tblroute.txt ndc_tbl_route 1-3,5-104
	cvt tblunit.txt ndc_tbl_unit 1-15,17-115
	cvt schedule.txt ndc_schedule 1-7,9-9

	echo " "
	echo "Converting Orange Book files"
	echo " "

	echo -n " --> Extracting ziptext.zip ... "
	unzip -qLL ../eobzip.zip
	echo " done <--"

	echo " --> Converting products.txt to orangebook_products <-- "
	cat products.txt | ../process_orangebook_products.pl > ../../../drugs/orangebook_products.tsv

	echo " "
)

rm extract -Rf

