# $Id$
# $Author$

THISDIR=data
SUBDIR=backup cache config en_US fax lout

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done
	cp -vf .ht* *.php $(INSTDIR)/$(THISDIR)

clean:
	# Nothing

dist-clean:
	# Nothing
	
