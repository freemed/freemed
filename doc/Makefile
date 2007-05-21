# $Id$
# $Author$

THISDIR=doc

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -rvf [A-Z]* naturaldocs $(INSTDIR)/$(THISDIR)
	#	No installation for apache 1.x anymore by default
	#mkdir -p $(DESTDIR)/etc/apache/conf.d
	#cp -vf freemed.apache.conf $(DESTDIR)/etc/apache/conf.d/freemed.conf
	#mkdir -p $(DESTDIR)/etc/apache-ssl/conf.d
	#cp -vf freemed.apache.conf $(DESTDIR)/etc/apache-ssl/conf.d/freemed.conf
	mkdir -p $(DESTDIR)/etc/apache2/conf.d
	cp -vf freemed.apache.conf $(DESTDIR)/etc/apache2/conf.d/freemed.conf

clean:
	# Nothing

dist-clean:
	# Nothing
	
