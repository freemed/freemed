<?php
  # file: simplerep_lout.php3
  # version: 19991029
  #
  # note: This dumb module generates Basser Lout output for 
  # the simplerep module. 
  # It accepts being passed the following parameters and content :
  #
  # $sr_label         : name of the original template used
  #
  # $patient          : current patient's unique id
  #
  # $suppress_headers : self-explaining. if == "yes", no headers
  #                     allows for using preprinted paper
  # $header_line_1    : headers come from the facility information
  # $header_line_2
  # $header_line_3
  # $header_line_4
  # $header_line_5
  # $header_line_6
  # $header_line_7
  #
  # $dest_line_1      : destinatary informations
  # $dest_line_2
  # $dest_line_3
  # $dest_line_4
  #
  # $date_line        : contains the date and place of origin
  #
  # $sr_text          : the text of the report itself
  #
  # $signature_line_1 : Name and abbreviated titles
  # $signature_line_2 : additional specialities if set
  #
  # $ptdoc            : signing physician database id
  # $ptid1            : signing physician internal (facility) id
  #
  # moreover it calculates the following parameter :
  #
  # $da_key           : is the md5sum of the data and second of generation.
  #                     this unique string can be used to authenticate the 
  #                     document by phone, in case of doubts.
  # ------------------
  # $delivery         : the final delivery method ( del_print, del_fax... )
  #
  # Faxing
  #
  # $fax_dest_number  : fax destinatary number
  # $fax_origin_number: fax origin number
  # $fax_notify_email : e-mail address to notify about the fax
  #
  # NOTE: you must allow faxing for the nobody user in /etc/mgetty+sendfax/fax.allow
  # Make sure you understand the security issues around that.
  # 
  # Printing
  #
  # $printer          : printing is done with lpd to $printer. 
  #                     Must be a valid printcap printer name
  #
  # code: max k <amk@span.ch>
  #       jeff b (jeff@univrel.pr.uconn.edu) -- template
  # lic : GPL
  # 
  # please note that you _can_ remove the comments down below,
  # but everything above here should remain untouched. please
  # do _not_ remove my name or address from this file, since I
  # have worked very hard on it. the license must also always
  # remain GPL.                                     -- jeff b
  #


    // *** local variables section ***
    // complete these to reflect the data for this
    // module.
  $_pg_desc    = stripslashes ($sr_label) ;
  $page_name   = "simplerep_lout.php3"; // for help info, later
  $db_name     = "oldreports";        // get this from jeff
  $record_name = "prout";             // such as Room for Rooms module
                                      // or "CPT Modifiers" for cptmod
  $order_field="prout";                    // what field the records are
                                      // sorted by... multiples can
                                      // be used with commas
                                      // ("value_a, value_b")
  $separate_add_section=false;        // if you need the addform action
                                      // keep this, if not, set to false

    // *** includes section ***

  include ("global.var.inc");         // load global variables
  include ("freemed-functions.inc");  // API functions

    // *** setting _ref cookie ***
    // if you are going to be "chaining" out from this
    // function and want users to be able to return to
    // it, uncomment this and it will set the cookie to
    // return people using the bar.
//  SetCookie("_ref", $page_name, time()+$_cookie_expire);

    // *** authorizing user ***

  freemed_open_db ($LoginCookie);  // authenticate user

    // *** initializing page ***

    freemed_display_html_top ();  // generate top of page
    freemed_display_banner ();    // display package banner


// *** main action loop ***




if ($action == "lout") {

    freemed_display_box_top ("Simple Reports Module :: PRINT", $_ref);

// process some strings
  $sr_text     = stripslashes ($sr_text)    ;
  $date_line   = stripslashes ($date_line)  ;
  $sr_label    = stripslashes ($sr_label)   ;

// get the current time                     ;
  $time        = time()                     ;

  $da_stuff   = $header_line_1.$header_line_2.$header_line_3.$header_line_4.$header_line_5.$dest_line_1.$dest_line_2.$dest_line_3.$dest_line_4.$date_line.$sr_text.$signature_line_1.$signature_line_2.$time  ;  

  $da_key      = md5 ($da_stuff)                        ;
  $da_key1     = substr ($da_key, 0, 4)                 ;
  $da_key2     = substr ($da_key, 4, 4)                 ;
  $da_key3     = substr ($da_key, 8, 4)                 ;
  $da_key4     = substr ($da_key, 12, 4)                ;
  $da_key5     = substr ($da_key, 16, 4)                ;
  $da_key6     = substr ($da_key, 20, 4)                ;
  $da_key7     = substr ($da_key, 24, 4)                ;
  $da_key8     = substr ($da_key, 28, 4)                ;
  

  // get a random filename

$filename = "/dev/random"      ;
$fp = fopen ( $filename , "r") ;
  if (!($fp)) {
  echo "Random device could not be opened. Check your system" ; 
  exit ;
              }
  // read a random string

$rand_name = fread ( $fp , 12 ) ;
$fileclose = fclose ( $fp )     ;
  // error-handling code here


  // convert to hex
$rand_name = bin2hex ( $rand_name ) ;


  // open and read the lout defs file
$filename = "$physical_loc/data/lout/sr_defs"       ;
$fq = fopen ( $filename , "r") ;
  if (!($fq)) {
  echo "lout data file $filename could not be opened" ; 
  exit ;
              }
$sr_defs = fread ( $fq , filesize( $filename ) ) ; 
  // error handler here

$fileclose = fclose ( $fq )                      ;
  // error-handling code here

  // make a directory for our files
$md = mkdir ( "/tmp/$rand_name" , 0700 )         ;
  //error-handling code here



  // let's insert our variables inside the text
  eval( "\$sr_defs = \"$sr_defs\";" )          ;

  // write our file to the temp directory

$filename = "/tmp/$rand_name/sr_defs"           ;
$wf = fopen ( $filename , "w" )                 ;
  if (!($wf)) {
  echo "could not create $filename for writing" ; 
  exit ;
              }
$write = fwrite ( $wf , $sr_defs )              ;
  // error handler here

$fileclose = fclose ( $wf )                     ;
  // error handler here

$sourcefile = "$physical_loc/data/lout/sr_doc"  ;
$destfile   = "/tmp/$rand_name/sr_doc"          ;
$cp = copy ( $sourcefile , $destfile )          ;
  // error handler here

$sourcefile = "$physical_loc/data/lout/sr_sheet"  ;
$destfile   = "/tmp/$rand_name/sr_sheet"          ;
$cp = copy ( $sourcefile , $destfile )            ;
  // error handler here


$sourcefile = "$physical_loc/data/lout/sr_langdefs"  ;
$destfile   = "/tmp/$rand_name/sr_langdefs"          ;
$cp = copy ( $sourcefile , $destfile )               ;
  // error handler here

////////// Begin conditional actions according to the final delivery method

  if ($delivery == "del_print") {

   //  get the printer name

  $r = freemed_get_link_rec ( $printer, "printer" ) ;

  $prntname    = $r["prntname"  ] ;
  $prnthost    = $r["prnthost"  ] ;
  $prntaclvl   = $r["prntaclvl" ] ;


  // run lout
$cd = chdir ( "/tmp/$rand_name"  )         ;

print ("<PRE>")                            ;
$foo = system( "lout sr_sheet > sr_ps" )   ;
print ("</PRE>")                           ;


  // print it 
$filename = "/tmp/$rand_name/sr_ps"         ;
$the_print = system ( "lpr -P$prntname $filename" ) ;
  // error handler here

echo "
       <P> Print : $the_print <P> 
     ";
                                 
} elseif ($delivery=="del_fax") {

  // run lout
$cd = chdir ( "/tmp/$rand_name"  )         ;

print ("<PRE>")                            ;
$foo = system( "lout -EPS sr_sheet > sr_ps" )   ;
print ("</PRE>")                           ;
   $Space  =  " "  ;
   $Points = ":"   ;
   $numpag = "@P@" ;
   $totpag = "@M@" ;
  // generate the fax header
$faxhead = "   $From $Points $signature_line_1 $fax_origin_number $To $Points $dest_line_1 $dest_line_2 $Page $Points $numpag $Of $totpag" ;
  // write to sr_faxhead file
$faxhead_file = "/tmp/$rand_name/sr_faxhead"    ;
$fx           = fopen ( $faxhead_file , "w" )   ;
  if (!($fx)) {
  echo "could not create $filename for writing" ; 
  exit ;
              }
$write        = fwrite ( $fx , $faxhead )       ;
  // error handler here
$fileclose = fclose ( $fx )                     ;
  // error handler here
   

  // fax it 
$filename = "/tmp/$rand_name/sr_ps"                ;
$the_fax = system ( "faxspool -h $faxhead_file -f $fax_notify_email $fax_dest_number /tmp/$rand_name/sr_ps" ) ;
  // error handler here



echo "
       <P> Faxed : $the_fax <P> 
     ";

} elseif ( $delivery == "del_email_pdf" ) {

  // run lout
$cd = chdir ( "/tmp/$rand_name"  )              ;

print ("<PRE>")                                 ;
$foo = system( "lout -EPS sr_sheet > sr_ps" )   ;
  // error handler here

$ps_file  ="/tmp/$rand_name/sr_ps"              ;
$pdf_file ="/tmp/$rand_name/sr_pdf"             ;

$bar = system ( "ps2pdf $ps_file $pdf_file" )   ;


print ("</PRE>")                                ;



  /// mailing stuff here

                                            }  // end pdf

/////////// end conditional delivery actions


  //let's clean behind us
//$ foo = system ("rm -f /tmp/$rand_name/*" )   ;
  // error handler here

//$rd = rmdir ("/tmp/$rand_name")             ;
  // error handler somewhere in the future

  // we're all done, folks
  echo "<P> OK <P>"                 ;  // hope so 8-)


  freemed_display_box_bottom ();

 echo "
    <CENTER><A HREF=\"simplerep.php3?$_auth&action=choose&patient=$patient\"
     ><$STDFONT_B>$Return_to_report_selection<$STDFONT_E></CENTER>
    </P>
  ";


 echo "
    <CENTER><A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>$Manage_Patient<$STDFONT_E></CENTER>
    </P>
  ";

  // ritual ablutions

    freemed_close_db ()            ;
    freemed_display_html_bottom () ;
    DIE ("")                       ;     // and goat sacrifice.


}


?>


