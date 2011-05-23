# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2011 FreeMED Software Foundation
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

all: clean
	@echo "Building icons ... "
	@for i in *.svg.gz; do \
		echo -n " - $$i "; \
		rsvg -w 64 -h 64 -f png "$$i" `echo "$$i" | sed -e 's/\.svg\.gz//;'`.64x64.png; echo -n " [ 64x64 ] "; \
		rsvg -w 32 -h 32 -f png "$$i" `echo "$$i" | sed -e 's/\.svg\.gz//;'`.32x32.png; echo -n " [ 32x32 ] "; \
		rsvg -w 24 -h 24 -f png "$$i" `echo "$$i" | sed -e 's/\.svg\.gz//;'`.24x24.png; echo -n " [ 24x24 ] "; \
		rsvg -w 16 -h 16 -f png "$$i" `echo "$$i" | sed -e 's/\.svg\.gz//;'`.16x16.png; echo -n " [ 16x16 ] "; \
		echo " "; \
	done
	@echo "Crushing ... "
	@for i in *.png; do \
		echo " - $$i "; \
		j=`echo $$i | sed 's/\.png//g;'`.orig.png; \
		mv $$i $$j -v; \
		pngcrush $$j $$i; \
		rm -vf $$j; \
		echo "-----------------------------------------"; \
	done
	@echo "Finished."

clean:
	rm -f *.png
