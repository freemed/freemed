# $Id$
# $Author$

THISDIR=lib/template/default/htmlarea
SUBDIR=images lang plugins popups

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf *.css *.html *.txt *.js $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done

clean:
	# Nothing

dist-clean:
	# Nothing
	
