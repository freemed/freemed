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

# +--------------+------------------+------+-----+---------+----------------+
# | Field        | Type             | Null | Key | Default | Extra          |
# +--------------+------------------+------+-----+---------+----------------+
# | icd9code     | varchar(6)       | YES  |     | NULL    |                | 
# | icd10code    | varchar(7)       | YES  |     | NULL    |                | 
# | icd9descrip  | varchar(45)      | YES  |     | NULL    |                | 
# | icd10descrip | varchar(45)      | YES  |     | NULL    |                | 
# | icdmetadesc  | varchar(30)      | YES  |     | NULL    |                | 
# | icdng        | date             | YES  |     | NULL    |                | 
# | icddrg       | date             | YES  |     | NULL    |                | 
# | icdnum       | int(10) unsigned | YES  |     | NULL    |                | 
# | icdamt       | double           | YES  |     | NULL    |                | 
# | icdcoll      | double           | YES  |     | NULL    |                | 
# | id           | int(10) unsigned | NO   | PRI | NULL    | auto_increment | 
# +--------------+------------------+------+-----+---------+----------------+


COUNT=0
DATE=$( date +%Y-%m-%d ) 
cat $1 | grep ^\' | grep -v 'ICD-9-CM' | while read X; do
	CODE=$(echo "$X" | cut -d\' -f2)
	DESC=$(echo "$X" | cut -d\" -f2)
	if [ "$(echo $CODE)" != "" ]; then
		COUNT=$(( ${COUNT} + 1 ))
		CODE=$( echo ${CODE} | sed -e 's/ //g;' )
		echo ${CODE},${CODE},${DESC},${DESC},${DESC},${DATE},${DATE},0,0,0,${COUNT}
	fi
done

