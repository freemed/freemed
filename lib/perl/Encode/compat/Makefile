# $Id$
# $Author$

THISDIR=lib/perl/Encode/compat
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

