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

while (<>) {
	chomp ( my $line = $_ );
	my @parts = split /~/, $line;
	my @out;

	$parts[0] =~ s/;/,/g;
	$parts[0] =~ s/, /,/g;
	#$parts[0] =~ s/,/\\,/g;
	push @out, $parts[0];

	my ( $doseform, $route ) = split /; /, $parts[1];
	push @out, $doseform;
	push @out, $route;

	push @out, $parts[2];
	push @out, $parts[3];

	$parts[4] =~ s/,/\\,/g;
	$parts[4] =~ s/;/,/g;
	$parts[4] =~ s/\*\*Federal Register determination that product was not discontinued or withdrawn for safety or efficacy reasons\*\*//g;
	push @out, $parts[4];

	push @out, $parts[5];
	push @out, $parts[6];
	push @out, $parts[7];
	push @out, $parts[8];
	push @out, $parts[9];
	push @out, $parts[10];
	push @out, $parts[11];
	push @out, $parts[12];

	print join("|", @out);
	print "\n";
}

