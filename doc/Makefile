# $Id$
# $Author$

THISDIR=doc

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf [A-B]* CH* [D-Z]* $(INSTDIR)/$(THISDIR)
	mkdir -p $(DESTDIR)/etc/apache/conf.d
	cp -vf freemed.apache.conf $(DESTDIR)/etc/apache/conf.d/freemed.conf
	mkdir -p $(DESTDIR)/etc/apache-ssl/conf.d
	cp -vf freemed.apache.conf $(DESTDIR)/etc/apache-ssl/conf.d/freemed.conf

clean:
	# Nothing

dist-clean:
	# Nothing
	
