# $Id$
# $Author$

THISDIR=lib
SUBDIR=acl agata perl phpwebtools template tex xmlrpc

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf *.php $(INSTDIR)/$(THISDIR)
	# Hack up installing all of PEAR with no makefiles
	cp -rvf pear $(INSTDIR)/$(THISDIR)/
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done

clean:
	# Nothing

dist-clean:
	# Nothing

