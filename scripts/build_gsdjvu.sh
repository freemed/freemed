#!/bin/bash
# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2009 FreeMED Software Foundation
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

VERSION="1.3"
PKGLOC="/tmp"

echo "build_gsdjvu.sh for gsdjvu version ${VERSION}"
echo "by jeff@freemedsoftware.org"
echo " "
echo "Please note you must have root privileges to install this package."
echo " "

DEPS=""
if [ ! -f "/usr/include/jpeglib.h" -a ! -f "/usr/local/include/jpeglib.h" ]; then
	DEPS="$DEPS libjpeg"
fi
if [ ! -f "/usr/include/zlib.h" -a ! -f "/usr/local/include/zlib.h" ]; then
	DEPS="$DEPS zlib"
fi
if [ ! -f "/usr/include/png.h" -a ! -f "/usr/local/include/png.h" ]; then
	DEPS="$DEPS libpng"
fi
if [ "$DEPS" != "" ]; then
	echo " ! Failed to locate development headers for :"
	echo $DEPS
	echo " "
	echo "If you're using a Debian based distribution, try : "
	echo " sudo apt-get -y install build-essential libjpeg62-dev libpng12-dev zlib1g-dev"
	echo "before continuing with the build process."
	exit
fi

if [ ! -f "${PKGLOC}/gsdjvu-${VERSION}.tar.gz" ]; then
	echo -n " * Retrieving gsdjvu v${VERSION} ... "
	wget -q -c http://downloads.sourceforge.net/djvu/gsdjvu-${VERSION}.tar.gz -O "${PKGLOC}/gsdjvu-${VERSION}.tar.gz" 2>&1 > /dev/null
	echo "[done]"
else
	echo " * Already have gsdjvu v${VERSION}"
fi

if [ ! -f "${PKGLOC}/ghostscript-8.57.tar.bz2" ]; then
	echo -n " * Retrieving GPL ghostscript ... "
	wget -q -c http://downloads.sourceforge.net/ghostscript/ghostscript-8.57.tar.bz2 -O "${PKGLOC}/ghostscript-8.57.tar.bz2" 2>&1 > /dev/null
	echo "[done]"
else
	echo " * Already have GPL ghostscript package"
fi

if [ ! -f "ghostscript-fonts-std-8.11.tar.gz" ]; then
	echo -n " * Retrieving ghostscript fonts ... "
	wget -q -c http://downloads.sourceforge.net/ghostscript/ghostscript-fonts-std-8.11.tar.gz -O "${PKGLOC}/ghostscript-fonts-std-8.11.tar.gz" 2>&1 > /dev/null
	echo "[done]"
else
	echo " * Already have ghostscript font package"
fi

echo -n " * Extracting gsdjvu ... "
tar zxf ${PKGLOC}/gsdjvu-${VERSION}.tar.gz
echo "[done]"

echo -n " * Copying ghostscript packages ... "
mkdir -p gsdjvu-${VERSION}/BUILD/
cp ${PKGLOC}/ghostscript-fonts-std-8.11.tar.gz \
	${PKGLOC}/ghostscript-8.57.tar.bz2 \
	gsdjvu-${VERSION}/BUILD/
echo "[done]"

echo -n " * Patching build process to be non-interactive ... "
TEMP="$$.patch"
cat<<'EOF'>${TEMP}
52,63d51
< tmp=unk
< while [ "$tmp" != yes -a "$tmp" != YES ]; do
<   echo -n 'Please type "YES" or "NO": '
<   read tmp
<   if [ "$tmp" = no -o "$tmp" = NO ] ; then
<      echo "You must understand these terms before proceeding further."
<      exit 10
<   fi
< done
< 
< 
< 
EOF
patch -p0 gsdjvu-${VERSION}/build-gsdjvu < ${TEMP}
rm -f ${TEMP}
echo "[done]"

echo " * Beginning build process"
( cd gsdjvu-${VERSION}; echo ${INTERACTIVE} | ./build-gsdjvu )
echo " * Build finished."

echo -n " * Moving into /usr/local/gsdjvu ... "
mv -f gsdjvu-${VERSION}/BUILD/INST/gsdjvu /usr/local
( cd /usr/local/bin; ln -s /usr/local/gsdjvu/gsdjvu . )
echo "[done]"

if [ ! -f /usr/local/bin/gsdjvu ]; then
	echo " * Was not able to install gsdjvu, please install binaries manually."
	exit 1
fi

echo -n " * Cleaning up ... "
rm -rf gsdjvu-${VERSION}
echo "[done]"
