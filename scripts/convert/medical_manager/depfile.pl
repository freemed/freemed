#!/usr/bin/perl
# file: depfile.pl
# desc: med manager depfile.dat -> freemed patient database
# code: jeff b (jeff@univrel.pr.uconn.edu)
# lic : GPL, v2

$program_description = "patfile.dat converter";
$version             = "20000-01-13";
$code_bugs_email     = "jeff\@univrel.pr.uconn.edu";
$ISO                 = "iso-8859-1";

$program_name        = $0;
$arg_offset          = shift;
$arg_input           = shift;
$arg_patfile         = shift;
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

if ((length($arg_input)<3) || (length($arg_output)<3) ||
                              (length($arg_icdfile)<3)) {
  print "\n";
  print " syntax: $program_name <offset> <input file> <patient file>\n";
  print "           <icd input file> <insurance coverage file>\n";
  print "           <output file>\n";
  print "   converts medical manager dependents database to freemed\n";
  print "   import format (requires diagcode.dat file for cross\n";
  print "   referencing of Medical Manager ICD codes, and\n";
  print "   insfile.dat for patient insurance information)\n";
  print "\n";
  print "         *** med manager defaults ***\n";
  print "    input file              : depfile.dat\n";
  print "    patient file            : patfile.dat\n";
  print "    icd input file          : diagcode.dat\n";
  print "    insurance coverage file : insfile.dat\n";
  print "\n";
  exit;
} # bad params...

# header
print "$program_description v$version\n";
print "(c) $year under the GPL, v2\n";
print "Please send all code bugs to $code_bugs_email\n\n";
print "Processing $arg_icdfile ... \n";

open (DEPDB, $arg_input)      || die "could not open $arg_input";
open (PATDB, $arg_patfile)    || die "could not open $arg_patfile";
open (ICDDB, $arg_icdfile)    || die "could not open $arg_icdfile";
open (INSDB, $arg_insfile)    || die "could not open $arg_insfile";
open (OUTPUT, ">$arg_output") || die "could not open $arg_output";

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
    print "[$icdcount] \t$code_number \n";
    $icdcount++;
  } # end of if code number exists
} # end of while
close (ICDDB);

print "done.\n";

# get associative values from insurance database
print "Processing $arg_insfile ... \n";
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
    print "[$insco_number / $patient_number] ".
          "\t$this_type \t$id_number \t$group_number\n";
    $inscount++;
  } # end of if code number exists
} # end of while
close (INSDB);
print "done.\n";

# get associative values from patient database
print "Processing $arg_patfile ... \n";
$ptcount = 1;
@pataddr1  = @pataddr2  = @patcount =
@patcity   = @patstate  = @patzip   =  ();
while (<PATDB>) {
  chop;
  $this_text = $_;
  @patwords = ();
  push (@patwords, $+) while $this_text =~ m{
       "([^\"\\]*(?:\\.[^\"\\]*)*)",?
     | ([^,]+),?
     | ,
  }gx;

  # extract the data
  $pt_record_number       = $patwords[ 1];
  $pt_first_date          = $patwords[ 9];
  $pt_referring_doc       = $patwords[10];
  $pt_pcp_doc             = $patwords[15];
  $pt_last_name           = $patwords[16];
  $pt_first_name          = $patwords[17];
  $pt_middle_name         = $patwords[18];
  $pt_address_1           = $patwords[19];
  $pt_address_2           = $patwords[20];
  $pt_city                = $patwords[21];
  $pt_state               = $patwords[22];
  $pt_zip                 = $patwords[23];
  $pt_phone               = $patwords[24];
  $pt_sex                 = $patwords[26];
  $pt_dob                 = $patwords[27];
  $pt_status              = $patwords[37];
  $pt_icd1                = $patwords[42];
  $pt_icd2                = $patwords[43];
  $pt_marital_status      = $patwords[53];
  $pt_employed            = $patwords[54];
  $pt_employer_name       = $patwords[55];

  if (length($pt_last_name)>1) {
  $pataddr1 [$pt_record_number] = $pt_address_1;
  $pataddr2 [$pt_record_number] = $pt_address_2;
  $patcity  [$pt_record_number] = $pt_city;
  $patstate [$pt_record_number] = $pt_state;
  $patzip   [$pt_record_number] = $pt_zip;
  $patcount [$pt_record_number] = $ptcount;
  print "[$ptcount / $pt_record_number] ".
        "\t$pt_last_name \t$pt_first_name\n";
  $ptcount++;
  } # end of length check
} # end of while
close (PATDB);
print "done.\n";

print "Processing $arg_input ... \n";
# extract patient db
$count        = $arg_offset + 1;
while (<DEPDB>) {
  chop;
  $this_text = $_;
  @words = ();
  push (@words, $+) while $this_text =~ m{
       "([^\"\\]*(?:\\.[^\"\\]*)*)",?
     | ([^,]+),?
     | ,
  }gx;

  # extract the data
  $pt_guarantor           = $words[ 2];
  $pt_first_name          = $words[ 3];
  $pt_dob                 = $words[ 4];
  $pt_sex                 = $words[ 5];
  $pt_relation_to_guar    = $words[ 6];
  $pt_last_name           = $words[ 7];
  $pt_record_number       = $words[ 8];
  $pt_icd1                = $words[ 9];
  $pt_icd2                = $words[10];
  $pt_middle_name         = $words[14];
  $pt_ssn                 = $words[15];
  $pt_icd3                = $words[18];
  $pt_icd4                = $words[19];
  $pt_status              = $words[37];
  $pt_marital_status      = $words[22];
  $pt_employed            = $words[22];
  
  if (length($pt_last_name)>1) {
    # necessary conversions
    $pt_sex =~ tr/[A-Z]/[a-z]/;
    $code_descrip =~ s/,/\\,/g;

    # get the mapping of the guarantor
    $guarantor    = $patcount [$pt_guarantor];

    # map the remainder of the patient information properly
    $pt_address_1 = $pataddr1 [$guarantor];
    $pt_address_2 = $pataddr2 [$guarantor];
    $pt_city      = $patcity  [$guarantor];
    $pt_state     = $patstate [$guarantor];
    $pt_zip       = $patzip   [$guarantor];

    # map the ICD codes properly
    $pt_diag_1 = $pt_diag_2 =
    $pt_diag_3 = $pt_diag_3 = 0;
    $pt_diag_1 = $icdcodes[$pt_icd1] if (length($pt_icd1)>0);
    $pt_diag_2 = $icdcodes[$pt_icd2] if (length($pt_icd2)>0);
    $pt_diag_3 = $icdcodes[$pt_icd3] if (length($pt_icd3)>0);
    $pt_diag_4 = $icdcodes[$pt_icd4] if (length($pt_icd4)>0);

    # map the insurance companies properly
    $pt_ins1    = $insco1 [$guarantor];
    $pt_ins2    = $insco2 [$guarantor];
    $pt_insno1  = $insid1 [$guarantor];
    $pt_insno2  = $insid2 [$guarantor];
    $pt_insgrp1 = $insgrp1[$guarantor];
    $pt_insgrp2 = $insgrp2[$guarantor];

    $pt_dob = "19".substr ($pt_dob, 0, 2)."-".
               substr     ($pt_dob, 2, 2)."-".
               substr     ($pt_dob, 4, 2);
    
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
                 "$pt_record_number,0,$pt_marital,,0,0,".
                 "$guarantor,$pt_relation_to_guar,".
                 "$pt_ins1,$pt_ins2,$pt_ins3,".
                 "$pt_insno1,$pt_insno2,$pt_insno3,".
                 "$pt_insgrp1,$pt_insgrp2,$pt_insgrp3,,".
                 "$ISO,$count\n";
    print "[$count (g = $guarantor)] ".
          "\t$pt_last_name,\t$pt_first_name\t$pt_middle_name\t".
          "\t\($pt_dob\)\tID=$pt_record_number\n".
          "(icd1=$pt_icd1/$pt_diag_1)\n";
    $count++;
  } # end of if code number exists
} # end of while

print "done.\n";

close (DEPDB);
close (OUTPUT);
