# $Id$
# $Author$

THISDIR=scripts
SUBDIR=fax_import

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf .ht* *.pl *.sh cfg-value new_fax  $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR) DESTDIR=$(DESTDIR); \
	done

clean:
	# Nothing

dist-clean:
	# Nothing
	
