# $Id$
# $Author$

THISDIR=lib/agata
SUBDIR=fpdf-font lib report

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf *.php .ht* html2* *.tpl *.tex dia.* $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done

clean:
	# Nothing

dist-clean:
	# Nothing

