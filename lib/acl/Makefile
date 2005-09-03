# $Id$
# $Author$

THISDIR=lib/acl
SUBDIR=Cache_Lite admin adodb
FILES= .htaccess AUTHORS CHANGELOG CONTENTS COPYING.lib CREDITS example.php FAQ freemed-acl-defaults.xml gacl_api.class.php gacl.class.php Makefile profiler.inc README schema.xml setup.php TODO VERSION

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf $(FILES) $(INSTDIR)/$(THISDIR)
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done

clean:
	# Nothing

dist-clean:
	# Nothing

