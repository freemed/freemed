# $Id$
# $Author$

THISDIR=lib/agata/report
SUBDIR=

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf *.report $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done

clean:
	# Nothing

dist-clean:
	# Nothing

