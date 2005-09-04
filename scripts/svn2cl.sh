#!/bin/bash
# $Id$
# $Author$
#
#	Script to generate Changelog from Subversion XML output.
#

if [ ! -e ./scripts/svn2cl.sh ]; then \
	echo "You need to run this script from the FreeMED root directory."; \
	exit; \
fi

svn log -r HEAD:0 --xml --verbose | xsltproc --stringparam strip-prefix dir/subdir scripts/svn2cl.xsl - > ChangeLog
