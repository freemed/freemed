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
#
# Top-level FreeMED Makefile
#

prefix=/usr
INSTDIR=$(DESTDIR)$(prefix)/share/freemed
SUBDIR=data doc lib locale scripts ui
SUBDIRCOPY=services

all:
	# Nothing to do

install:
	mkdir -p $(INSTDIR)
	cp -vf .htaccess *.php *.html $(INSTDIR)
	for d in $(SUBDIR); do \
		make -C $$d install DESTDIR=$(DESTDIR) INSTDIR=$(INSTDIR); \
	done
	for d in $(SUBDIRCOPY); do \
		mkdir -p $(INSTDIR)/$$d; \
		cp -vf $$d/* $(INSTDIR)/$$d/; \
	done

clean:
	rm -vf phpcs.report.xml phpcs.report.html
	
dist-clean:
	# Nothing to do

phpcs.report.xml:
	./scripts/phpcs --standard=PHPCompatibility --report=xml \
		*.php \
		lib/*.php \
		lib/org/freemedsoftware/*/*.php \
		ui/*/*.php \
		ui/*/*/*.php \
		> phpcs.report.xml || echo "done"

phpcs.report.html: phpcs.report.xml
	xsltproc doc/phpcs.xsl phpcs.report.xml > phpcs.report.html

