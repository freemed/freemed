# $Id$
# $Author$

THISDIR=lib/acl
SUBDIR=Cache_Lite admin adodb

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf A* C{H,O,R}* [F-Z]* .ht* *.xml *.inc *.php $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done

clean:
	# Nothing

dist-clean:
	# Nothing

