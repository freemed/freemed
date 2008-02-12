#!/bin/bash
# $Id$
#
# Authors:
#      Arthur de Jong <adejong@debian.org>
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 2005 Arthur de Jong
# Copyright (C) 1999-2008 FreeMED Software Foundation
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
#
#	Original copyright notice:
#
# Copyright (C) 2005 Arthur de Jong.
# 
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions
# are met:
# 1. Redistributions of source code must retain the above copyright
#    notice, this list of conditions and the following disclaimer.
# 2. Redistributions in binary form must reproduce the above copyright
#    notice, this list of conditions and the following disclaimer in
#    the documentation and/or other materials provided with the
#    distribution.
# 3. The name of the author may not be used to endorse or promote
#    products derived from this software without specific prior
#    written permission.

#
#	Script to generate Changelog from Subversion XML output.
#

if [ ! -e ./scripts/svn2cl.sh ]; then \
	echo "You need to run this script from the FreeMED root directory."; \
	exit; \
fi

svn log -r HEAD:0 --xml --verbose | xsltproc --stringparam strip-prefix dir/subdir scripts/svn2cl.xsl - > ChangeLog
