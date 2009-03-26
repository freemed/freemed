#!/bin/bash

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
		echo \"${CODE}\",\"${CODE}\",\"${DESC}\",\"${DESC}\",\"${DESC}\",${DATE},${DATE},0,0,0,${COUNT}
	fi
done

