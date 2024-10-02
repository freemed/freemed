#!/bin/bash
# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2012 FreeMED Software Foundation
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

GSVERSION="10.04.0"
PKGLOC="/tmp"

echo "build_gsdjvu.sh for gsdjvu"
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
	echo " sudo apt -y install build-essential libjpeg-dev libpng-dev zlib1g-dev"
	echo "before continuing with the build process."
	exit
fi

echo -n " * Retrieving gsdjvu ... "
rm -Rf djvu-gsdjvu-git
git clone -q https://git.code.sf.net/p/djvu/gsdjvu-git djvu-gsdjvu-git
echo "[done]"

if [ ! -f "${PKGLOC}/ghostscript-${GSVERSION}.tar.bz2" ]; then
	echo -n " * Retrieving GPL ghostscript ... "
	wget -q -c "https://github.com/ArtifexSoftware/ghostpdl-downloads/releases/download/gs$( echo $GSVERSION | sed -e 's/\.//g;' )/ghostscript.${GSVERSION}.tar.gz" -O "${PKGLOC}/ghostscript-${GSVERSION}.tar.gz" 2>&1 > /dev/null
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

echo -n " * Copying ghostscript packages ... "
mkdir -p djvu-gsdjvu-git/BUILD/
cp ${PKGLOC}/ghostscript-fonts-std-8.11.tar.gz \
	${PKGLOC}/ghostscript-${GSVERSION}.tar.gz \
	djvu-gsdjvu-git/BUILD/
echo "[done]"

echo -n " * Patching build process to be non-interactive ... "
TEMP="$$.patch"
cat<<'EOF'>${TEMP}
55,66d54
< tmp=unk
< while [ "$tmp" != yes -a "$tmp" != YES ]; do
<   $echon 'Please type "YES" or "NO": '
<   read tmp
<   if [ "$tmp" = no -o "$tmp" = NO ] ; then
<      echo "You must understand these terms before proceeding further."
<      exit 10
<   fi
< done
<
<
<
289a278,279
> 
> touch base/gserror.h
EOF
patch -p0 djvu-gsdjvu-git/build-gsdjvu < ${TEMP}
rm -f ${TEMP}
echo "[done]"

echo " * Beginning build process"
( cd djvu-gsdjvu-git; echo ${INTERACTIVE} | ./build-gsdjvu )
echo " * Build finished."

echo -n " * Moving into /usr/local/gsdjvu ... "
mv -f djvu-gsdjvu-git/BUILD/INST/gsdjvu /usr/local
( cd /usr/local/bin; ln -s /usr/local/gsdjvu/gsdjvu . )
echo "[done]"

if [ ! -f /usr/local/bin/gsdjvu ]; then
	echo " * Was not able to install gsdjvu, please install binaries manually."
	exit 1
fi

echo -n " * Cleaning up ... "
rm -rf djvu-gsdjvu-git
echo "[done]"
