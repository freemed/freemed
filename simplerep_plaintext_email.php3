<?php
 // file: simplerep_plaintext_email.php3
 // version: 1999-11-03
 //
 // note: This module uses Basser Lout for hyhenating text from 
 // the simplerep module. Unlike simplerep_lout.php3 it only outputs
 // plaintext
 // It accepts being passed the following parameters and content :
 //
 // $sr_label         : name of the original template used
 //
 // $patient          : current patient's unique id
 //
 // $suppress_headers : self-explaining. if == "yes", no headers
 //                     allows for using preprinted paper
 // $header_line_1    : headers come from the facility information
 // $header_line_2
 // $header_line_3
 // $header_line_4
 // $header_line_5
 //
 // $dest_line_1      : destinatary informations
 // $dest_line_2
 // $dest_line_3
 // $dest_line_4
 //
 // $date_line        : contains the date and place of origin
 //
 // $sr_text          : the text of the report itself
 //
 // $signature_line_1 : Name and abbreviated titles
 // $signature_line_2 : additional specialities if set
 //
 // $ptdoc            : signing physician database id
 // $ptid1            : signing physician internal (facility) id
 //
 //
 // code: max k <amk@span.ch>
 //       jeff b (jeff@univrel.pr.uconn.edu) -- template
 // lic : GPL
 // 
 // please note that you _can_ remove the comments down below,
 // but everything above here should remain untouched. please
 // do _not_ remove my name or address from this file, since I
 // have worked very hard on it. the license must also always
 // remain GPL.                                     -- jeff b
 //


    // *** local variables section ***
    // complete these to reflect the data for this
    // module.
  $_pg_desc    = stripslashes ($sr_label) ;
  $page_name   = "simplerep_plaintext_email.php3" ;           
  $db_name     = "oldreports" ;       // get this from jeff
  $record_name = "prout" ;            // such as Room for Rooms module
                                      // or "CPT Modifiers" for cptmod
  $order_field="prout" ;              // what field the records are
                                      // sorted by... multiples can
                                      // be used with commas
                                      // ("value_a, value_b")
  $separate_add_section=false ;       // if you need the addform action
                                      // keep this, if not, set to false

    // *** includes section ***

  include ("lib/freemed.php") ;             // load global variables
  include ("lib/API.php") ;      // API functions
//  include ("lib/mail-functions.php") ; // Mailing functions

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

    freemed_display_box_top ("$_pg_desc", $_ref);

// process some strings
  $sr_text     = stripslashes ($sr_text)     ;
  $date_line   = stripslashes ($date_line)   ;
  $sr_label    = stripslashes ($sr_label)    ;
  $__ISO_SET__ = strtoupper ( $__ISO_SET__ ) ;
  $phpversion  = phpversion ( )              ;


  if ($suppress_headers == "yes") {
  // conditionally send the header lines
  $header_line_1 = "" ;  
  $header_line_2 = "" ;
  $header_line_3 = "" ;
  $header_line_4 = "" ;
  $header_line_5 = "" ;
                                  }





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

$sourcefile = "$physical_loc/data/lout/sr_doc_plaintext"  ;
$destfile   = "/tmp/$rand_name/sr_doc_plaintext"          ;
$cp = copy ( $sourcefile , $destfile )                    ;
  // error handler here

$sourcefile = "$physical_loc/data/lout/sr_plaintext_email"  ;
$destfile   = "/tmp/$rand_name/sr_plaintext_email"          ;
$cp = copy ( $sourcefile , $destfile )            ;
  // error handler here


$sourcefile = "$physical_loc/data/lout/sr_langdefs"  ;
$destfile   = "/tmp/$rand_name/sr_langdefs"          ;
$cp = copy ( $sourcefile , $destfile )               ;
  // error handler here

////////// Begin conditional actions according to the final delivery method

  if ($delivery == "del_email") {


  // run lout
$cd = chdir ( "/tmp/$rand_name"  )         ;

print ("<PRE>")                            ;
$foo = system( "lout -p sr_plaintext_email > sr_pt" )   ;
print ("</PRE>")                           ;


  // read it 
$filename = "/tmp/$rand_name/sr_pt"         ;
$fr = fopen ( $filename , "r") ;
  if (!($fr)) {
  echo "lout data file $filename could not be opened" ; 
  exit ;
              }
$sr_pt = fread ( $fr , filesize( $filename ) ) ; 
  // error handler here

$fileclose = fclose ( $fr )                      ;
  // error-handling code here



   $subject         = "$sr_label"       ;
   $message         = trim ( $sr_pt )   ;

// ---------------------------------
//   use the mailing functions (not working as of 19991103)


//    $mail = new mail_message()                                  ;
//    $mail->from = "\"$signature_line_1\" <$email_replyto_addr>" ;
//    $mail->headers = "X-Mailer: PHP$phpversion/".PACKAGENAME.VERSION."\n" ;
//    $mail->to = "\"$dest_line_1\" <$email_dest_addr>"           ;
//    $mail->subject = "$sr_label"                                ;
//    $mail->body = "$message"                                    ;
//    $mail->send();


// ----------------- old code with php mail


   $mailed = mail ( "\"$dest_line_1\" <$email_dest_addr>", 
                    "$subject", 
                    "$message", 
                    "From: \"$signature_line_1\" <$email_replyto_addr>\nReply-To: $email_replyto_addr\nMIME-Version: 1.0\nContent-Type: TEXT/PLAIN; charset=\"$__ISO_SET__\"\nContent-Transfer-Encoding: 8bit\nX-Mailer: PHP$phpversion/".PACKAGENAME.VERSION."\n" ) ;

/// ----------------- new code with sendmail

//$filename = "/tmp/$rand_name/the_mail"         ;
//$fs = fopen ( $filename , "w") ;
//  if (!($fs)) {
//  echo "lout data file $filename could not be opened" ; 
//  exit ;
              }

// fwrite ( $fs, "To: $email_dest_addr\n" )                               ;
// fwrite ( $fs, "Subject: $subject\n" )                                  ;
// fwrite ( $fs, "From: \"$signature_line_1\" <$email_replyto_addr>\n" )  ;
// fwrite ( $fs, "MIME-Version: 1.0\n" )                                  ;
// fwrite ( $fs, "Content-Type: TEXT/PLAIN; charset=\"$__ISO_SET__\"\n" ) ;
// fwrite ( $fs, "Content-Transfer-Encoding: 8bit\n" )                    ;
// fwrite ( $fs, "X-Mailer: PHP$phpversion/".PACKAGENAME.VERSION."\n" )      ;
// fwrite ( $fs, "\n" )                                                   ;
// fwrite ( $fs, "$sr_pt" )                                               ;
// fwrite ( $fs, "\n" )                                                   ;

// $fileclose = fclose ( $fs )                                            ;
  // error-handling code here

  // run sendmail

// $mailed = system ("sendmail -t < $filename" )   ; 




// ------------------------

    echo "<PRE>
         $Mailed : $mailed 
          </PRE>
         ";

if ( $mailed =="1" ) {

                     }  //database insertion here



                                 
} else { echo "<P>$Wrong_Delivery<P>" ; }

/////////// end conditional delivery actions


  //let's clean behind us
 $ foo = system ("rm -f /tmp/$rand_name/*" )   ;
  // error handler here

 $rd = rmdir ("/tmp/$rand_name")             ;
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
    <CENTER><A HREF=\"manage.php?$_auth&id=$patient\"
     ><$STDFONT_B>$Manage_Patient<$STDFONT_E></CENTER>
    </P>
  ";

  // ritual ablutions

    freemed_close_db ()            ;
    freemed_display_html_bottom () ;
    DIE ("")                       ;     // and goat sacrifice.


}


?>


