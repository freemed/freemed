# $Id$
# $Author$

THISDIR=lib/agata/fpdf-font/makefont
SUBDIR=

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf *.map *.php $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done

clean:
	# Nothing

dist-clean:
	# Nothing

