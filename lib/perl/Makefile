# $Id$
# $Author$

THISDIR=lib/perl
SUBDIR=Config Encode Frontier XML

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	#cp -vf *.php $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done

clean:
	# Nothing

dist-clean:
	# Nothing

