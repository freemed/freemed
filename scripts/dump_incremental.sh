#!/bin/bash
#	$Id$
#	$Author$
#	Create incremental FreeMED dump

PHYSICAL="$(scripts/cfg-value PHYSICAL_LOCATION)"

if [ ! -d "${PHYSICAL}" ]; then
	echo "Could not find FreeMED!"
	exit -1
fi

if [ ! -f "${PHYSICAL}/data/backup/full" ]; then
	echo "A full backup must exist before an incremental backup can be performed!"
	exit -1
fi

# Create new full (full.inc)
mysqldump \
	--single-transaction \
	--user="$(scripts/cfg-value DB_USER)" \
	--password="$(scripts/cfg-value DB_PASS)" \
	freemed > data/backup/full.inc

# Diff it
diff data/backup/full data/backup/full.inc > data/backup/incremental

# Remove temporary full database
rm -f data/backup/full.inc

