#!/usr/bin/perl
# $Id$
# $Author$
#
# This is a simple script which generates the MD5 hash equivalent to
# its argument, and can be used for finding patient record images.

use Digest::MD5  qw(md5 md5_hex md5_base64);

my $original = shift;
my $md5hash = md5_hex($original);
print $md5hash;
