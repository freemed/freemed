#!/usr/bin/perl
#	$Id$
#	$Author$

# Auto-detect the path for libraries and the FreeMED install
use FindBin;
use lib "$FindBin::Bin/../lib/perl";
my $rootpath = "$FindBin::Bin/..";

use XML::RAX;

my $action = shift || options();
my $package = shift || options();

my $TEMPDIR = '/tmp/'.$$;

if ($action eq 'install') {
	install_package( $package );
	exit;
} elsif ($action eq 'query') {
	query_package( $package );
	exit;
} else {
	options();
}

#----- Function library

sub extract_package {
	my $package = shift;

	# Extract package
	`mkdir -p ${TEMPDIR}`;
	`unzip "${package}" -d "${TEMPDIR}"`;

	if ( ! -f "${TEMPDIR}/package.xml" ) {
		print "ERROR: Invalid package '${package}'\n";
		exit;
	}
} # end sub extract_package

sub cleanup_package {
	# Remove temporary directory
	`rm -rf ${TEMPDIR}`;
} # end sub cleanup_package

sub query_package {
	my $package = shift;

	# Extract package to temporary directory
	extract_package($package);

	# Get package information
	my $r = new XML::RAX;
	$r->openfile("${TEMPDIR}/package.xml");
	$r->setRecord('information');
	my $rec = $r->readRecord();

	print "Name: ".$rec->getField('name')."\n";
	print "Version: ".$rec->getField('version')."\n";
	print "Description: ".$rec->getField('description')."\n";

	# Cleanup
	cleanup_package();
} # end sub query_package

sub install_package {
	my $package = shift;

	# Extract package to temporary directory
	extract_package($package);

	# Get package information
	my $r = new XML::RAX;
	$r->openfile("${TEMPDIR}/package.xml");
	$r->setRecord('directory');
	while (my $rec = $r->readRecord()) {
		my $destination = $rec->getField('destination');
		`mkdir -p "${rootdir}/${destination}"`;
	}
	$r->setRecord('file');
	while (my $rec = $r->readRecord()) {
		my $source = $rec->getField('source');
		my $destination = $rec->getField('destination');
		my $cmd = "cp \"${TEMPDIR}/${source}\" \"${rootdir}/${destination}\"";
		`${cmd}`;
	}

	# Cleanup
	cleanup_package();
} # end sub install_package

sub options {
	my $VERSION = '0.1';
	print "FreeMED Package Manager v${VERSION}\n";
	print "\tsyntax: $0 action package\n";
	exit;
} # end sub options

