#!/usr/bin/perl
# $Id$
# $Author$
#
# Stupid little script to move images from old repository type to new
# MD5-type repository. This only generates the shell commands necessary
# to do this, and doesn't actually perform the moves.

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
