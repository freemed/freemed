# $Id$
# $Author$

THISDIR=locale/en_US
SUBDIR=doc

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	#cp -vf README *.php $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done

clean:
	# Nothing

dist-clean:
	# Nothing

