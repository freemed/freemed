#!/bin/bash
# $Id$
# $Author$
# Quick script for changing _()'s to __()'s for gettextXML functions

for i in *.php; do
	echo -n "Processing $i ... "
	perl -pi -e "s/\_\(\"/__(\"/g;" "$i"
	# Remove triplicate slashes, in the event of an overzealous run
	perl -pi -e "s/\_\_\_\(\"/__(\"/g;" "$i"
	echo "[done]"
done
