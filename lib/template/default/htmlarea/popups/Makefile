# $Id$
# $Author$

THISDIR=lib/template/default/htmlarea/popups
SUBDIR=

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf *.html *.js $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done

clean:
	# Nothing

dist-clean:
	# Nothing
	
