# $Id$
# $Author$

THISDIR=lib/template/default/htmlarea/plugins/SpellChecker
SUBDIR=img lang

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf *.html *.cgi *.css *.js $(INSTDIR)/$(THISDIR)
	# Access perms for cgi
	chmod a+x $(INSTDIR)/$(THISDIR)/*.cgi
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done

clean:
	# Nothing

dist-clean:
	# Nothing
	
