# $Id$
# $Author$
# Top-level FreeMED Makefile

prefix=/usr
INSTDIR=$(DESTDIR)$(prefix)/share/freemed
SUBDIR=data doc img lib locale modules scripts

all:
	# Nothing to do

install:
	mkdir -p $(INSTDIR)
	cp -vf .htaccess *.php *.html $(INSTDIR)
	for d in $(SUBDIR); do \
		make -C $$d install DESTDIR=$(DESTDIR) INSTDIR=$(INSTDIR); \
	done

clean:
	# Nothing to do
	
dist-clean:
	# Nothing to do
