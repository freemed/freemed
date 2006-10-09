# $Id$
# $Author$

THISDIR=data
SUBDIR=backup cache config en_US fax keys ui report tex

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		cp -vrf $$d $(INSTDIR)/$(THISDIR); \
	done
	cp -vf .ht* *.php $(INSTDIR)/$(THISDIR)

clean:
	# Nothing

dist-clean:
	# Nothing
	
