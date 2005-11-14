# $Id$
# $Author$

THISDIR=data/cache
SUBDIR=packages pdf quest_hl7

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	chmod a+w $(INSTDIR)/$(THISDIR)
	#cp -vf .ht* *.php $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		mkdir -p $(INSTDIR)/$(THISDIR)/$$d; \
		chmod a+w $(INSTDIR)/$(THISDIR)/$$d; \
	done

clean:
	# Nothing

dist-clean:
	# Nothing
	
