#!/usr/bin/perl
# file: clmfile.pl
# desc: med manager clmfile.dat -> freemed insco database
# code: jeff b (jeff@univrel.pr.uconn.edu)
# lic : GPL, v2

$program_description = "clmfile.dat converter";
$version             = "2000-01-02";
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
  print "   converts medical manager insco database to freemed import format\n";
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

while (<CODES>) {
  chop;
  $this_text = $_;
  @words = ();
  push (@words, $+) while $this_text =~ m{
       "([^\"\\]*(?:\\.[^\"\\]*)*)",?
     | ([^,]+),?
     | ,
  }gx;
  $this_id        = $words[1];
  $insco_name     = $words[2];
  $insco_addr1    = $words[3];
  $insco_addr2    = $words[4];
  $insco_city     = $words[5];
  $insco_state    = $words[6];
  $insco_zip      = $words[7];
  $insco_phone    = $words[8];
  if (length($insco_name)>3) {
    print "[$this_id] $insco_name\n";
    $insco_name   =~ s/,/\\,/g;
    $insco_addr1  =~ s/,/\\,/g;
    $insco_addr2  =~ s/,/\\,/g;
    print OUTPUT "$cur_date,$cur_date,".
                 "$insco_name,$insco_name,".
                 "$insco_addr1,$insco_addr2,".
                 "$insco_city,$insco_state,$insco_zip,".
                 "$insco_phone,$insco_fax,$insco_contact,".
                 "$insco_id,$insco_website,$insco_email,".
                 "0,0,0,$this_id\n";
  } # end of if code number exists
} # end of while

print "done.\n";

close (CODES);
close (OUTPUT);
