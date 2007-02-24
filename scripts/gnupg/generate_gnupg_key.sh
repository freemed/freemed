#!/bin/bash
# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2007 FreeMED Software Foundation
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

KEYDIR="$(scripts/cfg-value PHYSICAL_LOCATION)/data/keys/"

TEMPFILE="/tmp/gnupg.$$"

TEST_GNUPG=$(gpg --version 2>&1 | grep GnuPG)
if [ "${TEST_GNUPG}" = "" ]; then
	echo "GnuPG needs to be installed!"
	exit -1
fi

# Create keygen criteria based on system information. This probably won't work on
# anything that isn't vaguely POSIX compliant.

cat > "${TEMPFILE}" <<EOF
Key-Type: DSA
Key-Length: 4096
Name-Real: $(scripts/cfg-value INSTALLATION)
Name-Comment: FreeMED GnuPG Key
Name-Email: freemed@$(hostname)
Expire-Date: 0
%pubring ${KEYDIR}pubring.gpg
%secring ${KEYDIR}secring.gpg
%commit
EOF

gpg --homedir=data/keys --no-options --batch -q --gen-key "${TEMPFILE}" 2>&1 > /dev/null

