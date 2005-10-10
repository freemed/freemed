#!/bin/bash
#	$Id$
#	$Author$
#	Create full FreeMED dump

PHYSICAL="$(scripts/cfg-value PHYSICAL_LOCATION)"

if [ ! -d "${PHYSICAL}" ]; then
	echo "Could not find FreeMED!"
	exit -1
fi

mysqldump \
	--single-transaction \
	--user="$(scripts/cfg-value DB_USER)" \
	--password="$(scripts/cfg-value DB_PASS)" \
	freemed > data/backup/full
