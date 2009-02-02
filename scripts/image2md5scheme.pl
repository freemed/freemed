#!/usr/bin/perl
# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2009 FreeMED Software Foundation
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
# Stupid little script to move images from old repository type to new
# MD5-type repository. This only generates the shell commands necessary
# to do this, and doesn't actually perform the moves.
# 

use Digest::MD5  qw(md5 md5_hex md5_base64);

my $pid, $record, $type, $original;

while ($original = shift) {

	# Split into components
	( $pid, $record, $type ) = split(/\./, $original, 3);

	# Determine md5 hash
	my $md5hash = md5_hex($pid);

	# Create output filename
	my $dir = substr($md5hash, 0, 2) . '/' .
		substr($md5hash, 2, 2). '/' .
		substr($md5hash, 4, 2). '/' .
		substr($md5hash, 6, 2). '/' .
		substr($md5hash, 8, 24);
	
	my $filename = $record . '.' . $type;

	print "mkdir -p $dir\n";
	print "mv -v $original $dir/$filename\n";
}
