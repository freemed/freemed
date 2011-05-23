#!/usr/bin/perl
# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2011 FreeMED Software Foundation
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
# Converts MySQL database dump of freemed database into phpgacl XML
# schema file format.
#

my $filename = shift;
my $line;
my $date = `date`;
$date =~ s/\n//;

open (HANDLE, $filename) || die "Failed to open $filename\n";
print "<?xml version=\"1.0\"?>\n";
print "<schema version=\"0.2\">\n";
print "<!-- \$Id\$ -->\n";
print "<!-- \$Author\$ -->\n";
print "\t<sql>\n";
print "\n<!-- Automatically generated from MySQL dump ($date) -->\n\n";
while ($line = <HANDLE>) {
	chop $line;
	if ($line =~ /INSERT/ and $line =~ /acl_/ and !($line =~ /acl_phpgacl/) and !($line =~ /'System'/) and !($line =~ /'User'/)) {
		print "\t\t<query>";
		print $line;
		print "</query>\n";

	}
}
print "\t</sql>\n";
print "</schema>\n";

close (HANDLE);
