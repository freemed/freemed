#!/usr/bin/perl
# file: diagcode.pl
# desc: med manager diagcode.dat -> freemed ICD database
# code: jeff b (jeff@univrel.pr.uconn.edu)
# lic : GPL, v2

$program_description = "diagcode.dat converter";
$version             = "19991230";
$code_bugs_email     = "jeff\@univrel.pr.uconn.edu";

$program_name        = $0;
$arg_input           = shift;
$arg_output          = shift;

($dummy,$dummy,$dummy,$day,$mon,$year,$dummy,$dummy,$dummy) =
  gmtime();
$mon  ++;       # months are returned as 0..11
$year += 1900;  # years are returned as num years since 1900
$day = "0".$day if (length($day) eq 1);
$mon = "0".$mon if (length($mon) eq 1);
$cur_date = $year."-".$mon."-".$day;

if ((length($arg_input)<3) || (length($arg_output)<3)) {
  print "\n";
  print " syntax: $program_name <input file> <output file>\n";
  print "   converts medical manager ICD database to freemed import format\n";
  print "\n";
  exit;
} # bad params...

# header
print "$program_description v$version\n";
print "(c) $year under the GPL, v2\n";
print "Please send all code bugs to $code_bugs_email\n\n";
print "Processing $arg_input ... ";

open (CODES, $arg_input)      || die "could not open $arg_input";
open (OUTPUT, ">$arg_output") || die "could not open $arg_output";

$count        = 1;
<CODES>;  # Skip the Medical Manager Header Record...
while (<CODES>) {
  chop;
  $this_text = $_;
  @words = ();
  push (@words, $+) while $this_text =~ m{
       "([^\"\\]*(?:\\.[^\"\\]*)*)",?
     | ([^,]+),?
     | ,
  }gx;
  $code_number = $words[1]; 
  $code_descrip = $words[2];
  if (length($code_descrip)>3) {
    print "[$count] \t$code_number \t$code_descrip\n";
    # $code_number  =~ tr/0-9\.//cd;
    $code_number  =~ s/,/\\,/g;
    $code_descrip =~ s/,/\\,/g;
    print OUTPUT "$code_number,,$code_descrip,,$code_descrip,".
                 "$cur_date,,0,0,0,$count\n";
    $count++;
  } # end of if code number exists
} # end of while

print "done.\n";

close (CODES);
close (OUTPUT);
