# $Id$
# $Author$

THISDIR=scripts/fax_import
SUBDIR=

all:
	# Nothing

install:
	mkdir -p $(INSTDIR)/$(THISDIR)
	cp -vf .ht* *.pl *.sh $(INSTDIR)/$(THISDIR)
	#----- Add fax import cron fragment
	mkdir -p $(DESTDIR)/etc/cron.d
	cp -vf cron.d $(DESTDIR)/etc/cron.d/freemed_fax_import
	chmod a+x $(DESTDIR)/etc/cron.d/freemed_fax_import
	for d in $(SUBDIR); do \
		make -C $$d install INSTDIR=$(INSTDIR); \
	done

clean:
	# Nothing

dist-clean:
	# Nothing
	
