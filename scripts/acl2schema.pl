#!/usr/bin/perl
# $Id$
# $Author$
# Converts MySQL database dump of freemed database into phpgacl XML
# schema file format. - Jeff

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
