# $Id$
# $Author$

THISDIR=lib/perl/XML
SUBDIR=

all:
	# STUB

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf *.pm $(INSTDIR)/$(THISDIR)
	@for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done

clean:
	# STUB

dist-clean:
	# STUB

