#!/bin/bash
#
#	$Id$
#	jeff@freemedsoftware.org
#
#	Flip Djvu image
#

INPUT=$1

if [ "${INPUT}" == "" ]; then exit 1; fi
if [ ! -f "${INPUT}" ]; then exit 1; fi

TEMPDIR="/tmp/flipdjvu-$$"
SECOND="${TEMPDIR}/output.ps"
FINAL="${TEMPDIR}/$(basename "${INPUT}")"

mkdir -p "${TEMPDIR}"

djvups -format=ps "${INPUT}" "${SECOND}"
djvudigital --dpi=200 --psrotate=180 "${SECOND}" "${FINAL}"
mv -f "${FINAL}" "${INPUT}"

rmdir -Rf "${TEMPDIR}"

