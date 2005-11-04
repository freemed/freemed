#!/usr/bin/perl -I/usr/share/freemed/lib/perl
#	$Id$
#	$Author$
#
#	HL7 push script
#

use MIME::Base64;
use Frontier::Client;
use Frontier::RPC2;
use Config::IniFiles;
use Sys::Syslog;
use Data::Dumper;

my $report_type = shift || '';

# Open XML-RPC and local configuration files
my $xmlrpc_config = new Config::IniFiles( -file => '/usr/share/freemed/data/config/xmlrpc.ini' );

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

