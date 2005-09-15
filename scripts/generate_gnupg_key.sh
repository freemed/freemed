#!/bin/bash
#	$Id$
#	$Author$

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

gpg --no-options --batch -q --gen-key "${TEMPFILE}" 2>&1 > /dev/null

