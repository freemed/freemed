<?php
  # file: simplerep_html.php3
  # version: 19991104
  #
  # note: This dumb module generates plain ("ready-for-local-print")
  # html output for the simplerep module. 
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
  $_pg_desc    = "$Generate_HTML_for $sr_label";
  $page_name   = "simplerep_html.php3"; // for help info, later
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
//  freemed_display_banner ();    // display package banner

// *** main action loop ***


// H T M L



if ($action == "html") {

// process some strings
  $sr_label    = stripslashes ($sr_label)   ;
  $sr_text     = stripslashes ($sr_text)    ;
  $sr_text     = nl2br ($sr_text)           ;
  $date_line   = stripslashes ($date_line)  ;

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
  


  // output html


      // conditionally suppress the headers or show them
       if ($suppress_headers != "yes") {
  echo "  
    <CENTER>
    <H2>$header_line_1</H2>
    <TABLE WIDTH= 100% BORDER=0 CELLPADDING=2>
    <TR><TD ALIGN=CENTER>
    $header_line_2
    </TD></TR>
    <TR><TD ALIGN=CENTER>
    $header_line_3
    </TD></TR>
    <TR><TD ALIGN=CENTER>
    $header_line_4<P>
    </TD></TR>
    <TR><TD ALIGN=CENTER>
    $header_line_5<P>
    </TD></TR>
    <TR><TD ALIGN=CENTER>
    $header_line_6<P>
    </TD></TR>
    <TR><TD ALIGN=CENTER>
    $header_line_7<P>
    </TD></TR>
    </TABLE>
    </CENTER>
    <P>&nbsp;
    <P>&nbsp;
        ";
                                        } else {


 echo "  
    <CENTER>
    <H2>&nbsp;</H2>
    <TABLE WIDTH= 100% BORDER=0 CELLPADDING=2>
    <TR><TD ALIGN=CENTER>
    &nbsp;<P>
    </TD></TR>
    <TR><TD ALIGN=CENTER>
    &nbsp;<P>
    </TD></TR>
    <TR><TD ALIGN=CENTER>
    &nbsp;<P>
    </TD></TR>
    <TR><TD ALIGN=CENTER>
    &nbsp;<P>
    </TD></TR>
    </TABLE>
    </CENTER>
    <P>&nbsp;
    <P>&nbsp;
        ";


                                               }

    echo "
      <TABLE WIDTH=100% BORDER=0 CELLPADDING=2>
      <TR>
      <TD WIDTH=60%>   
      <P ALIGN=RIGHT>
      <B> $To :</B>      
      </P> 
      </TD><TD WIDTH=40%>
      $dest_line_1
      </TD></TR>
      <TD WIDTH=60%></TD>
      <TD WIDTH=40%>
      $dest_line_2
      </TD></TR>
      <TD WIDTH=60%></TD>
      <TD WIDTH=40%>
      $dest_line_3
      </TD></TR>
      <TD WIDTH=60%></TD>
      <TD WIDTH=40%>
      $dest_line_4
      </TD></TR>
      </TABLE>
      <P>&nbsp;
      <P>&nbsp;
    <CENTER>
    <P><H3><B>$sr_label</B></H3>
    <P>
    <TABLE WIDTH=80% BORDER=0 CELLPADDING=2>
    <TR><TD COLSPAN=2>
    $date_line
    </TD></TR>
    <TR><TD COLSPAN=2>
    &nbsp;
    </TD></TR>
    <TR><TD COLSPAN=2>
    $sr_text<P>
    </TD></TR>
    <TR><TD COLSPAN=2>
    <P>&nbsp;
    </TD></TR>
    <TR><TD COLSPAN=2>
    <P>&nbsp;
    </TD></TR>
    <TR><TD WIDTH=30%>
    &nbsp;
    </TD><TD WIDTH=70%>
    $signature_line_1
    </TD></TR>
    <TR><TD WIDTH=30%>
    &nbsp;
    </TD><TD WIDTH=70%>
    $signature_line_2
    </TD></TR>
    <TR><TD COLSPAN=2>
    <P>&nbsp;
    </TD></TR>
    <TR><TD COLSPAN=2>
    <P>&nbsp;
    </TD></TR>
    <TR><TD COLSPAN=2>
    <P>&nbsp;
    </TD></TR>
    <TR><TD COLSPAN=2>
    <SMALL><A HREF=\"simplerep.php3?$_auth&action=choose&patient=$patient\">$Verification_Key</A> : $da_key1 $da_key2 $da_key3 $da_key4 $da_key5 $da_key6 $da_key7 $da_key8
    </SMALL>
    </TD></TR>
    </TABLE>
    </CENTER>
    </BODY>
    </HTML>
       ";
    freemed_close_db ();
    DIE ("");




}





