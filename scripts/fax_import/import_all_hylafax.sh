#!/bin/bash
# $Id$
# $Author$

# This is the auto-import bash script, which loops through all entries in
# the Hylafax receive queue and forces them to be imported into FreeMED,
# first by being OCR'd and converted to DJVU, then by being transported
# via XML-RPC (using the XMLRPC::Lite / SOAP::Lite toolkit) into FreeMED.

# Check lsof to see if hylafax is still using the file first, otherwise
# faxes will be cut off.

( \
	cd /usr/share/freemed/scripts/fax_import/; \
	for f in /var/spool/hylafax/recvq/*.tif*; do \
		if [ "$(lsof | grep "$f")" == "" ]; then \
			echo "Importing $f"; \
			mv $f .; \
			./import_fax.pl `basename $f`; \
		fi; \
	done \
)
	
