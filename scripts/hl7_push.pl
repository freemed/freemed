#!/usr/bin/perl
# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2007 FreeMED Software Foundation
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
#	HL7 push script
#

# Auto-detect the path for libraries and the FreeMED install
use FindBin;
use lib "$FindBin::Bin/../lib/perl";
my $rootpath = "$FindBin::Bin/..";

use MIME::Base64;
use Frontier::Client;
use Frontier::RPC2;
use Config::IniFiles;
use Sys::Syslog;
use Data::Dumper;

my $report_type = shift || '';

# Open XML-RPC and local configuration files
my $xmlrpc_config = new Config::IniFiles( -file => $rootpath.'/data/config/xmlrpc.ini' );

# Open syslog
openlog('hl7_push', 'cons,pid', 'root');

# Create XML-RPC objects
my $xmlrpc = Frontier::Client->new (
	url => $xmlrpc_config->val('freemed', 'url'),
	username => $xmlrpc_config->val('freemed', 'username'),
	password => $xmlrpc_config->val('freemed', 'password'),
	debug => 0
);
my $xmlrpc_coder = Frontier::RPC2->new;

my $buffer;
while(<>) {
	$buffer .= $_;
}

my $result = $xmlrpc->call(
	'FreeMED.Transport.parse',
	['HL7v2', $xmlrpc_coder->base64(encode_base64($buffer))]
);
print $result;

