#!/bin/bash
#	$Id$
#	$Author$

FILE=$1

if [ "${FILE}" = "" ]; then
	echo "	syntax: $0 file"
	exit -1
fi

rm -f "${FILE}.gpg"
gpg \
	-r "freemed@$(hostname)" \
	--homedir=data/keys \
	--no-options \
	--sign \
	--quiet \
	--primary-keyring data/keys/pubring.pub \
	"${FILE}"
