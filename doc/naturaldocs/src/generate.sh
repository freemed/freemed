#!/bin/bash
# $Id$
# $Author$
#
#	Script to generate naturaldocs documentation. Assumes that
#	NaturalDocs is installed in /usr/share/naturaldocs.
#

if [ ! -e ./doc/naturaldocs/src/generate.sh ]; then \
	echo "You need to run this script from the REMITT root directory."; \
	exit; \
fi

# Get minor version number
NV=$(perl /usr/share/naturaldocs/NaturalDocs -h | grep version | awk -F'version ' '{ print $2 }')
if [ "$(echo "${NV}" | grep '1.')" = "" ]; then \
	echo "You need to have NaturalDocs version >= 1.3 installed in /usr/share/naturaldocs/"; \
	exit; \
fi
echo "Found Naturaldocs v${NV}"

perl /usr/share/naturaldocs/NaturalDocs -i . -p ./doc/naturaldocs/src -o HTML ./doc/naturaldocs -xi doc -xi .svn

