#!/usr/bin/perl
# $Id$
# $Author$
# Converts fixed form files to the PHP structures needed for the v0.6.1
# FixedFormRenderer architecture. - Jeff

my $filename = shift;
my $line;

open (HANDLE, $filename) || die "Failed to open $filename\n";
while ($line = <HANDLE>) {
	chop $line;
	( $name, $form, $a, $b, $c, $d, $e, $f, $row, $col, $len, $data, $format, $comment ) = split (/,/, $line); 
	@rows = split (/:/, $row);
	@cols = split (/:/, $col);
	@lens = split (/:/, $len);
	@datas = split (/:/, $data);
	@formats = split (/:/, $format);
	@comments = split (/:/, $comment);

	print "---------------------------------------------\n\n";
	print "\t\t\$this->name = '".$name."';\n";
	print "\t\t\$this->description = '".$form."';\n";
	print "\t\t\$this->form = array (\n";
	for ($i=0; $i < $#rows; $i++) {
		# Fix "datas"
		$datas[$i] =~ s/\\/\\\\/g;

		# Print it out
		print "\t\t\tarray ( ".
			"'".$rows[$i]."', ".
			"'".$cols[$i]."', ".
			"'".$lens[$i]."', ".
			"'".$datas[$i]."', ".
			"'".$formats[$i]."', ".
			"'".$comments[$i]."', ".
			"'".$lens[$i]."', ".
			"'".$lens[$i]."' ".
			"),\n";
	}
	print "\t\t);\n";
}

close (HANDLE);
