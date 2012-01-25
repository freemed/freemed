#!/usr/bin/perl
# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2012 FreeMED Software Foundation
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
#	Poll Quest web framework for HL7 messages
#

# Auto-detect the path for libraries and the FreeMED install
use FindBin;
use lib "$FindBin::Bin/../../lib/perl";
my $rootpath = "$FindBin::Bin/../..";

use WWW::Mechanize;
use MIME::Base64;
use Frontier::Client;
use Frontier::RPC2;
use Config::IniFiles;
use Sys::Syslog;
use Data::Dumper;

my $report_type = shift || '';

# Open XML-RPC and local configuration files
my $config = new Config::IniFiles( -file => $rootpath.'/data/config/quest_hl7.ini' );
my $xmlrpc_config = new Config::IniFiles( -file => $rootpath.'/data/config/xmlrpc.ini' );

# Open syslog
openlog('freemed_quest_hl7', 'cons,pid', 'root');

# Create XML-RPC objects
my $xmlrpc = Frontier::Client->new (
	url => $xmlrpc_config->val('freemed', 'url'),
	username => $xmlrpc_config->val('freemed', 'username'),
	password => $xmlrpc_config->val('freemed', 'password'),
	debug => 0
);
my $xmlrpc_coder = Frontier::RPC2->new;

# "Variables"
my $_username = $config->val('account', 'username');
my $_password = $config->val('account', 'password');
my $DEBUG = $config->val('account', 'debug');

syslog('info', 'Starting Quest HL7 run with username '.$_username);

# "Constants"
my $BASE = $config->val('quest', 'base');
my $LOGOUT = $config->val('quest', 'logout');
my $HL7URL;
my $NEWREPORTS;
my $CACHE = $config->val('cache', 'dir');

my @hl7;

# Make sure cache exists
mkdir $CACHE;

# Create mechanize object
my $m = WWW::Mechanize->new();
$m->agent_alias ( 'Windows IE 6' );	# Use Win32 IE6 to not arouse trouble

quest_login($_username, $_password);
my @reports = quest_get_report_list();
foreach my $oid (@reports) {
	my $sanitized_oid = $oid;
	$sanitized_oid =~ s/\~/\_/g;
	if (!quest_check_hl7_cached($sanitized_oid)) {
		my $filename = $CACHE . '/' . $sanitized_oid;
		syslog('info', 'Saving oid '.$oid);
		#print "Saving oid $oid ... " if ($DEBUG);
		open (FILE, ">$filename") or die "Could not open $filename for writing!\n";
		print FILE quest_get_hl7_report($oid);
		close FILE; # or die "ERROR WRITING\n";;
		die "Error writing $sanitized_oid\n" if ( ! -f $filename );
		push @hl7, $sanitized_oid;
		#print "done.\n" if ($DEBUG);
	}
}
quest_logout();

# If we're still debugging, show the list of HL7 messages we have received
print Dumper(\@hl7) if ($DEBUG);

if ($#hl7 < 1) {
	syslog('info', 'No HL7 messages currently polled');
	exit 1;
}

foreach my $oid (@hl7) {
	syslog('info', 'Pushing oid '.$oid.' to FreeMED');
	quest_hl7_to_freemed($oid);
}

#----------------------------------------------------------------------------------------

sub quest_login {
	my ($username, $password) = @_;

	$m->get($BASE);
	$m->submit_form(
		form_name => 'Login',
		fields => {
			'UserName' => $username,
			'Password' => $password
		}
	);

	# Check for login failure
	if ($m->content() =~ /alert\(\'Incorrect Login/) {
		syslog('notice', 'Failed to login with username '.$username);
		exit 1;
	}
}

sub quest_get_providers {
	my $p = $config->val('account', 'providers');
	my @providers = split /,/, $p;
	return \@providers;
}

sub quest_get_report_list {
	my $framepage = $m->content();
	my $mainFrame;
	if ($framepage =~ /mainFrame\" SRC=\"([^\"]+)\"/) {
		$mainFrame = $1;
	}

	# Get main frame
	$m->get($mainFrame);

	# Determine if this is the main frame or not
	if (!($m->content =~ /Welcome to Care360/)) {
		# Skip opening form, if there is one
		$m->submit();
	}

	# Get new reports
	$NEWREPORTS = $m->find_link(text_regex => qr/new reports for viewing/)->url();
	$m->get($NEWREPORTS);

	# Fill out the report form
	$m->form_name('ReportForm');
	$m->select('ClientList' => quest_get_providers());
	if ($report_type eq 'historic') { $m->field('ReportType', 'Previous'); }
	$m->submit();

	# Check for any reports
	if ($m->content() =~ /No reports found/) {
		syslog('info', 'No reports found during polling');
		exit 1;
	}

	# Get URL for producing HL7 output
	if ($m->content() =~ /download = window\.open\(\"([^\"]+)/) {
		$HL7URL = $1;
	}

	# Get array of HL7 reports, if they exist
	$_ = $m->content();
	my @oids = /\<A HREF=\"JAVASCRIPT\:OpenReportHL7\(\'([^\']+)/g;

	# If OIDs is size < 1, gracefully exit (no new reports)
	if ($#oids < 1) {
		syslog('info', 'No oids found during polling');
		exit 1;
	}

	# Return reference to OIDs	
	return @oids;
}

sub quest_get_hl7_report {
	my $oid = shift;

	# Test first one
	$m->get($HL7URL . $oid);

	return $m->content();
}

sub quest_check_hl7_cached {
	my $oid = shift;
	if ( -f "$CACHE/$oid" ) { return 1; } else { return 0; }
}

sub quest_logout {
	# Logout page
	$m->get($BASE.'/logout.jsp');
	$m->get($LOGOUT);
}

sub quest_hl7_to_freemed {
	my $oid = shift;

	my $filename = "$CACHE/$oid";
	syslog('info', 'Using filename '.$filename);
	my $message = `cat "$filename"`;
	my $result = $xmlrpc->call(
		'FreeMED.Transport.parse',
		['HL7v2', $xmlrpc_coder->base64(encode_base64($message))]
	);
	return $result;
}

