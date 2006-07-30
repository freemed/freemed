# $Id$
# $Author$

THISDIR=lib
SUBDIR=acl agata7 pear perl phpwebtools template tex xmlrpc

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf *.php $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		cp -rvf $$d $(INSTDIR)/$(THISDIR)/; \
	done

clean:
	# Nothing

dist-clean:
	# Nothing

