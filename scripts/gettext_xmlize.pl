#!/usr/bin/perl -I/usr/share/freemed/lib/perl
#
#	$Id$
#	$Author$
#

use HTML::Entities;

my $input = shift or die options();

open INPUT, $input or die("Could not open $input\n");
my $count = -1;
my $in = 0;
my @buf;

# Print header
print "<?xml version=\"1.0\"?>\n";
print "<gettextXML>\n";
print "<information>\n";

while (<INPUT>) {
	my $line = $_;
	# Trim
	$line =~ s/\s$//g;
	if ($line =~ /^msgid /) {
		$count = $count + 1;
		if ($count == 1) {
			# If one, dump header
			print join("\n", @buf);
			print "</information>\n";
		} elsif ($count gt 0) {
			print "\t<translated>".striplead(join("\n", @buf))."</translated>\n";
			print "</translation>\n";
		}
		undef (@buf);
		$line =~ /msgid "([^\"]+)"/;
		push @buf, htmlentities($1);
	}
	# Ignore commented lines
	if (!($line =~ /^#/)) {
		if ($line =~ /^msgstr /) {
			if ($count gt 0) {
				print "<translation>\n";
				print "\t<original>".striplead(join("\n", @buf))."</original>\n";
			}
			$line =~ /msgstr "([^\"]+)"/;
			undef (@buf);
			push @buf, htmlentities($1);
		} else {
			if ($line =~ /^\"/) {
				my $tmp = $line;
				$line =~ s/^\"//g;
				$line =~ s/\"$//g;
				$line =~ s/\\n$//g;
				if ($count gt 0) {
					push @buf, htmlentities($line);
				} else {
					# Deal with initial stuff ...
					my $header;
					$line =~ /([^\:]+): ([^\"]+)/;
					if ($1 eq "Content-Transfer-Encoding") {
						$header = tag('ContentTransferEncoding', $2);
					} else {
						$header = "<!-- type = $1, data = $2 -->\n";
					}
					push @buf, "\t".$header if $header;
				}
			}
		}
	}
}

# Last translation ...
if ($count gt 0) {
	print "\t<translated>".striplead(join("\n", @buf))."</translated>\n";
	print "</translation>\n";
}

# Print trailer
print "\n</gettextXML>\n\n";

close INPUT;

#---------------------------------------------------------------------------

sub htmlentities { my $i = shift; return ( $i ? HTML::Entities::encode_entities($i, 'numeric', '>&') : $i); }

sub striplead { my $i = shift; $i =~ s/^\n//g; return $i; }

sub tag { my ($t, $c) = @_; return "<$t>".htmlentities($c)."</$t>"; }

sub options { return "$0 inputfile > outputfile\n"; }

