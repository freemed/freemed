# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2006 FreeMED Software Foundation
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
#
#	/etc/cron.d/freemed crontab fragment
#	Syntax: m h dom mon dow user command
#

# ----- FreeMED Fax import ----------------------------------------------------
# Examine files every 5 minutes
05,10,15,20,25,30,35,40,45,50,55 *	* * *	root	test -f "`ls -1 /var/spool/hylafax/recvq/*.tif* 2>&1 | head -1`" && /usr/share/freemed/scripts/fax_import/import_all_hylafax.sh

# ----- FreeMED Tickler -------------------------------------------------------
# Tickler for every year
30 2	1 1 *	root	/usr/share/freemed/scripts/tickler.pl yearly
# Tickler for every month
30 3	1 * *	root	/usr/share/freemed/scripts/tickler.pl monthly
# Tickler for every week
30 4	* * 1	root	/usr/share/freemed/scripts/tickler.pl weekly
# Tickler for every day
30 4	* * *	root	/usr/share/freemed/scripts/tickler.pl daily
# Tickler for every hour
0 *	* * *	root	/usr/share/freemed/scripts/tickler.pl hourly
# Tickler for every ten minutes (for now, disabled by default)
#05,10,15,20,25,30,35,40,45,50,55 *	* * *	root	/usr/share/freemed/scripts/tickler.pl 10min
