# $Id$
# $Author$

THISDIR=data
SUBDIR=backup cache config en_US fax form keys report

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		cp -vrf $$d $(INSTDIR)/$(THISDIR); \
	done
	cp -vf .ht* $(INSTDIR)/$(THISDIR)

clean:
	# Nothing

dist-clean:
	# Nothing
	
