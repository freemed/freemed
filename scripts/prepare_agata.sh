#!/bin/bash
#
#	$Id$
#	jeff@freemedsoftware.org
#
#	Prepare agata for insertion into svn
#

VERSION=$1

if [ "${VERSION}" == "" -o ! -f "./agata-${VERSION}.zip" ]; then
	echo "Invalid or not specified version"
	exit
fi

#	Remove old and extract new
rm agata agata7 -Rf
unzip agata-${VERSION}.zip
mv agata agata7

#	Remove all temporary files
( cd agata7 ; find . -print0 | grep -FzZ /. | xargs -0 rm -Rvf {} )

#	Remove everything we don't use
rm -Rvf agata7/{api,dictionary,images,interface,output,projects,reports,resources,sql,themes,web,*.{bat,bmp,db,exe,ico,php,sql,xpm}} 

#	Remove all sxw and odt files
( cd agata7 ; rm $(find . | grep '.sxw') $(find . | grep '.odt') -vf )
