#!/usr/bin/perl
# $Id$
# $Author$
# desc: med manager patfile.dat -> freemed patient database
# lic : GPL, v2
# $Log$
# Revision 1.4  2001/11/20 15:01:27  rufustfirefly
# fixed version number problem, added CVS tags
#

$program_description = "patfile.dat converter";
$version             = "2000-01-13";
$code_bugs_email     = "jeff\@univrel.pr.uconn.edu";
$ISO                 = "iso-8859-1";
$debug               = 0;

$program_name        = $0;
$arg_input           = shift;
$arg_icdfile         = shift;
$arg_insfile         = shift;
$arg_output          = shift;

($dummy,$dummy,$dummy,$day,$mon,$year,$dummy,$dummy,$dummy) =
  gmtime();
$mon  ++;       # months are returned as 0..11
$year += 1900;  # years are returned as num years since 1900
$day = "0".$day if (length($day) eq 1);
$mon = "0".$mon if (length($mon) eq 1);
$cur_date = $year."-".$mon."-".$day;

if ((length($arg_input)<3)   || (length($arg_output)<3) ||
    (length($arg_insfile)<3) || (length($arg_icdfile)<3)) {
  print "\n";
  print " syntax: $program_name <input file> <icd input file>\n";
  print "           <insurance coverage file> <output file>\n";
  print "   converts medical manager patient database to freemed\n";
  print "   import format (requires diagcode.dat file for cross\n";
  print "   referencing of Medical Manager ICD codes, and\n";
  print "   insfile.dat for patient insurance information)\n";
  print "\n";
  print "         *** med manager defaults ***\n";
  print "    input file              : patfile.dat\n";
  print "    icd input file          : diagcode.dat\n";
  print "    insurance coverage file : insfile.dat\n";
  print "\n";
  exit;
} # bad params...

# header
print "$program_description v$version\n";
print "(c) $year under the GPL, v2\n";
print "Please send all code bugs to $code_bugs_email\n\n";

open (PATDB, $arg_input)      || die "could not open $arg_input";
open (ICDDB, $arg_icdfile)    || die "could not open $arg_icdfile";
open (INSDB, $arg_insfile)    || die "could not open $arg_insfile";
open (OUTPUT, ">$arg_output") || die "could not open $arg_output";

# get Medical Manager Version from header Record...
# versions before 900 have non-year 2000 compliant dates...
$mmVers = 0;
$this_text = <ICDDB>;
chop($this_text);
@hdrwords = ();
  push (@hdrwords, $+) while $this_text =~ m{
       "([^\"\\]*(?:\\.[^\"\\]*)*)",?
     | ([^,]+),?
     | ,
  }gx;
$mmVers = int($hdrwords[3]);
print "Data from Medical Manager Version $mmVers\n";
print "Processing $arg_icdfile ... ";
if($debug) {
  print "\n";
}

# get associative values from ICD database to allow mapping of codes
$icdcount = 1;
@icdcodes = ();
while (<ICDDB>) {
  chop;
  $this_text = $_;
  @icdwords = ();
  push (@icdwords, $+) while $this_text =~ m{
       "([^\"\\]*(?:\\.[^\"\\]*)*)",?
     | ([^,]+),?
     | ,
  }gx;
  $code_number  = $icdwords[1]; 
  $code_descrip = $icdwords[2];
  if (length($code_descrip)>3) {
    $icdcodes[$code_number] = $icdcount;
    if($debug) {
      print "[$icdcount]\t$code_number\n";
    }
    $icdcount++;
  } # end of if code number exists
} # end of while
close (ICDDB);

print "done.\n";

# get associative values from insurance database
print "Processing $arg_insfile ... ";
if($debug) {
  print "\n";
}
<INSDB>;  # Skip the Medical Manager Header Record
$inscount = 1;
@insco1   = @insco2  = @insgrp1  = @insgrp2 =
@insid1   = @insid2  = @inslinks = @insplan = ();
while (<INSDB>) {
  chop;
  $this_text = $_;
  @inswords = ();
  push (@inswords, $+) while $this_text =~ m{
       "([^\"\\]*(?:\\.[^\"\\]*)*)",?
     | ([^,]+),?
     | ,
  }gx;
  $insco_number   = $inswords[ 3];
  $patient_number = $inswords[ 4];
  $group_number   = $inswords[ 5];
  $id_number      = $inswords[ 8];
  $related_how    = $inswords[10];
  if (length($patient_number)>3) {
    if (length($insco1[$patient_number])<1) {
      $this_type                = "1";
      $insco1 [$patient_number] = $insco_number;
      $insid1 [$patient_number] = $id_number;
      $insgrp1[$patient_number] = $group_number;
    } else {
      $this_type                = "2";
      $insco2 [$patient_number] = $insco_number;
      $insid2 [$patient_number] = $id_number;
      $insgrp2[$patient_number] = $group_number;
    }
    if($debug) {
      print "[$insco_number / $patient_number] ".
            "\t$this_type \t$id_number \t$group_number\n";
    }
    $inscount++;
  } # end of if code number exists
} # end of while
close (INSDB);
print "done.\n";

print "Processing $arg_input ... ";
if($debug) {
  print "\n";
}

# extract patient db
<PATDB>; # Skip the Medical Manager Header Record...
$count        = 1;
while (<PATDB>) {
  chop;
  $this_text = $_;
  @words = ();
  push (@words, $+) while $this_text =~ m{
       "([^\"\\]*(?:\\.[^\"\\]*)*)",?
     | ([^,]+),?
     | ,
  }gx;

  # extract the data
  $pt_record_number       = $words[ 1];
  $pt_first_date          = $words[ 9];
  $pt_referring_doc       = $words[10];
  $pt_pcp_doc             = $words[15];
  $pt_last_name           = $words[16];
  $pt_first_name          = $words[17];
  $pt_middle_name         = $words[18];
  $pt_address_1           = $words[19];
  $pt_address_2           = $words[20];
  $pt_city                = $words[21];
  $pt_state               = $words[22];
  $pt_zip                 = $words[23];
  $pt_phone               = $words[24];
  $pt_sex                 = $words[26];
  $pt_dob                 = $words[27];
  $pt_pc                  = $words[28];
  $pt_status              = $words[37];
  $pt_icd1                = $words[42];
  $pt_icd2                = $words[43];
  $pt_icd3                = $words[51];
  $pt_icd4                = $words[52];
  $pt_marital_status      = $words[53];
  $pt_employed            = $words[54];
  $pt_employer_name       = $words[55];
  if($mmVers >= 800) { # Snag the Set Coverage Priority List
    $pt_1_plan = $words[59];
    $pt_2_plan = $words[61];
  }
  
  if (length($pt_last_name)>1) {
    # necessary conversions
    $pt_sex =~ tr/[A-Z]/[a-z]/;
    $code_descrip =~ s/,/\\,/g;

    # map the ICD codes properly
    $pt_diag_1 = $icdcodes[$pt_icd1] if (length($pt_icd1)>0);
    $pt_diag_2 = $icdcodes[$pt_icd2] if (length($pt_icd2)>0);
    $pt_diag_3 = $icdcodes[$pt_icd3] if (length($pt_icd3)>0);
    $pt_diag_4 = $icdcodes[$pt_icd4] if (length($pt_icd4)>0);

    # map the insurance companies properly
    if($mmVers < 800) { # Set Coverage Priority Started in 8.00
      $pt_ins1    = $insco1 [$pt_record_number];
      $pt_ins2    = $insco2 [$pt_record_number];
      $pt_insno1  = $insid1 [$pt_record_number];
      $pt_insno2  = $insid2 [$pt_record_number];
      $pt_insgrp1 = $insgrp1[$pt_record_number];
      $pt_insgrp2 = $insgrp2[$pt_record_number];
    } else {
      if($pt_1_plan == $insco1[$pt_record_number]) {
        $pt_ins1    = $insco1 [$pt_record_number];
        $pt_insno1  = $insid1 [$pt_record_number];
        $pt_insgrp1 = $insgrp1[$pt_record_number];
      } else {
        if($pt_1_plan == $insco2[$pt_record_number]) {
          $pt_ins1    = $insco2 [$pt_record_number];
          $pt_insno1  = $insid2 [$pt_record_number];
          $pt_insgrp1 = $insgrp2[$pt_record_number];
        } else {
          $pt_ins1 = 0;
          $pt_insno1 = "";
          $pt_insgrp1 = "";
        }
      }
      if($pt_2_plan == $insco1[$pt_record_number]) {
        $pt_ins2    = $insco1 [$pt_record_number];
        $pt_insno2  = $insid1 [$pt_record_number];
        $pt_insgrp2 = $insgrp1[$pt_record_number];
      } else {
        if($pt_2_plan == $insco2 [$pt_record_number]) {
          $pt_ins2    = $insco2 [$pt_record_number];
          $pt_insno2  = $insid2 [$pt_record_number];
          $pt_insgrp2 = $insgrp2[$pt_record_number];
        } else {
          $pt_ins2 = 0;
          $pt_insno2 = "";
          $pt_insgrp2 = "";
        }
      }
    }

    if($mmVers < 900) {
      if($pt_pc) {
        $pt_dob = "18".substr ($pt_dob, 0, 2)."-".
                   substr     ($pt_dob, 2, 2)."-".
                   substr     ($pt_dob, 4, 2);
      } else {
        $pt_dob = "19".substr ($pt_dob, 0, 2)."-".
                   substr     ($pt_dob, 2, 2)."-".
                   substr     ($pt_dob, 4, 2);
      }
    } else {
      $pt_dob = substr ($pt_dob, 0, 4)."-".
                substr ($pt_dob, 4, 2)."-".
                substr ($pt_dob, 6, 2);
    }
    
    print OUTPUT "$cur_date,$cur_date,0,0,0,".
                 "$pt_ref_doc,$pt_pcp,".
                 "$pt_phy1,$pt_phy2,$pt_phy3,$pt_phy4,".
                 "sta,0,".
                 "$pt_doc,".
                 "$pt_last_name,$pt_first_name,$pt_middle_name,".
                 "$pt_address_1,$pt_address_2,".
                 "$pt_city,$pt_state,$pt_zip,$pt_country,".
                 "$pt_phone,$pt_work_phone,$pt_fax,,".
                 "$pt_sex,$pt_dob,$pt_ssn,,0000-00-00,0,0,0000-00-00,0,".
                 "0,0,0,,0,0000-00-00,".
                 "$pt_diag_1,$pt_diag_2,$pt_diag_3,$pt_diag_4,".
                 "$pt_record_number,0,$pt_marital,,0,0,0,S,".
                 "$pt_ins1,$pt_ins2,$pt_ins3,".
                 "$pt_insno1,$pt_insno2,$pt_insno3,".
                 "$pt_insgrp1,$pt_insgrp2,$pt_insgrp3,,".
                 "$ISO,$count\n";
    if($debug) {
      print "[$count] $pt_last_name,\t$pt_first_name\t$pt_middle_name\t".
            "\t\($pt_dob\)\tID=$pt_record_number\n";
    }
    $count++;
  } # end of if code number exists
} # end of while

print "done.\n";

close (PATDB);
close (OUTPUT);
