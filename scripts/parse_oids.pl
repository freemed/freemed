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

while ($line = <>) {
	$line =~ s/<tr><TD>//g; $line =~ s/<\/td><\/tr>//g;
	if ($line =~ /([0-9\.]+),([^,]+),(.*),([A-Za-z]+),/) {
		my $code = $1;
		my $short_name = $2;
		my $description = $3;
		my $status = $4;

		$description =~ s/,/\\,/g;

		print "$code,$short_name,$description,$status\n" if ($code);

		#print "code = $1\n";
		#print "short name = $2\n";
		#print "description = $3\n";
		#print "status = $4\n";
		#print "-----------------------------\n";
	}
}
