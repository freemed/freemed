<?php
  # file: simplerep.php3
  # version: 19991203
  #
  # note: basic manutention for Simple Report Templates
  # i.e. certificates, repetitive stuff
  # Templates will be invoked from the manage patient module
  # then (after possible manual edition) dumped in a database 
  # the patient's file should be updated with a link to those.
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

  $page_name="simplerep.php3";        // for help info, later
  $db_name  ="simplereport";          // get this from jeff
  $record_name="Simple Template";     // such as Room for Rooms module
                                      // or "CPT Modifiers" for cptmod
  $order_field="sr_type, sr_label";   // what field the records are
                                      // sorted by... multiples can
                                      // be used with commas
                                      // ("value_a, value_b")
  $separate_add_section=true;         // if you need the addform action
                                      // keep this, if not, set to false

    // *** includes section ***

  include ("lib/freemed.php");         // load global variables
  include ("lib/API.php");  // API functions

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

if ($action=="display") {

  $query = "SELECT * FROM $db_name ".
   "ORDER BY $order_field";

  $result = $sql->query($query);
  if ($result) {
    freemed_display_box_top ($record_name, $_ref, $page_name);

    if (strlen($_ref)<5) {
      $_ref="main.php";
    } // if no ref, then return to home page...

    freemed_display_actionbar($page_name, $_ref);

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$Label</B></TD>
       <TD><B>$Category</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = $sql->fetch_array($result)) {

      $sr_label = $r["sr_label"];
      $sr_type  = $r["sr_type" ];
      $id       = $r["id"      ];

        // alternate the bar color
      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug==1) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$sr_label</TD>
        <TD><I>$sr_type</I></TD>
        <TD><A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>$lang_MOD$id_mod</FONT></A>
      ";
      if (freemed_get_userlevel($LoginCookie)>$delete_level)
        echo "
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del\"
          ><FONT SIZE=-1>$lang_DEL$id_mod</FONT></A>
        "; // show delete
      echo "
        </TD></TR>
      ";

    } // while there are no more


    echo "
      </TABLE>
    "; // end table (fixed 19990617)

    if (strlen($_ref)<5) {
      $_ref="main.php";
    } // if no ref, then return to home page...


    freemed_display_actionbar ($page_name, $_ref);

    freemed_display_box_bottom (); // display bottom of the box

  // in case we came from a choose form let's create a link for it

if ($patient) {  
  echo "
        <BR><CENTER>
        <A HREF=\"$page_name?&action=choose&patient=$patient\">
         $Return_to_report_selection</A>
        <BR></CENTER>
       ";
             }

  } else {
    echo "\n<B>$No_Records_Found</B>\n";
  }

  freemed_close_db ();
  freemed_display_html_bottom ();
  DIE ("");   // DIE, DIE, php3, DIE!

} elseif ($action=="addform") {

  freemed_display_box_top (_("Add")." "._($record_name));

  echo "
    <BR><BR>
    <TABLE WIDTH=100% BORDER=0 CELLPADDING=2>
    <TR><TD>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\">

    <$STDFONT_B>"._("Label")." :<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=\"sr_label\" SIZE=30 MAXLENGTH=50 
     VALUE=\"".prepare($sr_label)."\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>"._("Category")." : <$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=\"sr_type\" SIZE=3 MAXLENGTH=2
     VALUE=\"".prepare($sr_type)."\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Text ($Male $Adult): <$STDFONT_E><BR>
    </TD><TD></TD></TR>
    <TR><TD COLSPAN=2>
    <TEXTAREA NAME=\"sr_text\" ROWS=8 COLS=45
     WRAP=VIRTUAL>".prepare($sr_text)."</TEXTAREA>
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Text ($Female $Adult): <$STDFONT_E><BR>
    </TD><TD></TD></TR>
    <TR><TD COLSPAN=2>
    <TEXTAREA NAME=\"sr_textf\" ROWS=8 COLS=45
     WRAP=VIRTUAL>".prepare($sr_textf)."</TEXTAREA>
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Text ($Male $Child): <$STDFONT_E><BR>
    </TD><TD></TD></TR>
    <TR><TD COLSPAN=2>
    <TEXTAREA NAME=\"sr_textcm\" ROWS=8 COLS=45
    WRAP=VIRTUAL>".prepare($sr_textcm)."</TEXTAREA>
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Text ($Female $Child): <$STDFONT_E><BR>
    </TD><TD></TD></TR>
    <TR><TD COLSPAN=2>
    <TEXTAREA NAME=\"sr_textcf\" ROWS=8 COLS=45
     WRAP=VIRTUAL>".prepare($sr_textcf)."</TEXTAREA>
    </TD></TR>
    </TABLE>
    <BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" "._("Add")." \"  >
    <INPUT TYPE=RESET  VALUE=\" "._("Clear")." \">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <BR><BR>
    <CENTER>
    <A HREF=\"db_maintenance.php?$_auth\"
     >$Abandon_Addition</A>
    </CENTER>
  ";


    freemed_close_db ();
    freemed_display_html_bottom ();
    DIE ("");   // DIE, DIE, php3, DIE!
//////////////////////////////////////////////////
} elseif ($action=="add") {

  freemed_display_box_top(_("Adding")." "._($record_name));

  echo "
    <P><CENTER>
    <$STDFONT_B>"._("Adding")." ... 
  ";

    // build the query to database backend (usually MySQL):
    // the last value has to be NULL so that it auto
    // increments record numbers.
  $query = "INSERT INTO $db_name VALUES (
           '".addslashes($sr_label)."',
	   '".addslashes($sr_type)."',
	   '".addslashes($sr_text)."',
	   '".addslashes($sr_textf)."',
	   '".addslashes($sr_textcm)."',
	   '".addslashes($sr_textcf)."',
	   NULL)" ;

    // query the db with new values
  $result = $sql->query($query);

  if ($result) { echo "<B>"._("done").".</B>"; }
   else        { echo "<B>"._("ERROR")."</B>"; }

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
    <P>
  "; // readability fix 19990714

freemed_display_box_bottom (); // display the bottom of the box
freemed_close_db ();
freemed_display_html_bottom ();
DIE ("");


} elseif ($action=="modform") {
  /////////////////// MODIFY FORM /////////////////////////////
  /////////////////////////////////////////////////////////////
  freemed_display_box_top ("$Modify $record_name", $page_name);

  # here, we have the difference between adding and
  # modifying...

  if (strlen($id)<1) {
    echo "

     <B><CENTER>$Please_use_MODIFY
       $record_name!</B>
     </CENTER>

     <P>
    ";

    if ($debug==1) {
      echo "
        ID = [<B>$id</B>]
        <P>
      ";
    }

    freemed_display_box_bottom (); // display the bottom of the box
    echo "
      <CENTER>
      <A HREF=\"main.php?$_auth\"
       >$Return_to_the_Main_Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  // if there _IS_ an ID tag presented, we must extract the record
  // from the database, and proverbially "fill in the blanks"

    // grab record number "id"
  $result = $sql->query("SELECT * FROM $db_name ".
    "WHERE ( id = '$id' )");

    // display for debugging purposes
  if ($debug==1) {
    echo " <B>RESULT</B> = [$result]<BR><BR> ";
  }

  $r = $sql->fetch_array($result); // dump into array r[]

    // this dumps the result of the query (the record to
    // be modified) into the variables with those names,
    // for easy use by us.
  $sr_label   = $r["sr_label"];
  $sr_type    = $r["sr_type" ];
  $sr_text    = htmlentities ( $r["sr_text"   ] ) ; // adult male
  $sr_textf   = htmlentities ( $r["sr_textf"  ] ) ; // adult female
  $sr_textcm  = htmlentities ( $r["sr_textcm" ] ) ; // child male
  $sr_textcf  = htmlentities ( $r["sr_textcf" ] ) ; // child female


  $sr_text    = stripslashes ($sr_text)    ;
  $sr_textf   = stripslashes ($sr_textf)   ;
  $sr_textcm  = stripslashes ($sr_textcm)  ;
  $sr_textcf  = stripslashes ($sr_textcf)  ;

  echo "
    <P>
    <TABLE WIDTH=100% BORDER=0 CELLPADDING=2>
    <TR><TD> 
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <$STDFONT_B>$Label :<$STDFONT_E>
    </TD><TD> 
    <INPUT TYPE=TEXT NAME=sr_label SIZE=30 MAXLENGTH=50 
     VALUE=\"$sr_label\">
    </TD></TR>
    <TR><TD>  
    <$STDFONT_B>$Category :<$STDFONT_E>
    </TD><TD>     
    <INPUT TYPE=TEXT NAME=sr_type SIZE=2 MAXLENGTH=2
     VALUE=\"$sr_type\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Text ($Male $Adult): <$STDFONT_E><BR>
    </TD><TD></TD></TR>
    <TR><TD COLSPAN=2>
    <TEXTAREA NAME=\"sr_text\" ROWS=8 COLS=45
     WRAP=VIRTUAL>$sr_text</TEXTAREA>
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Text ($Female $Adult): <$STDFONT_E><BR>
    </TD><TD></TD></TR>
    <TR><TD COLSPAN=2>
    <TEXTAREA NAME=\"sr_textf\" ROWS=8 COLS=45
     WRAP=VIRTUAL>$sr_textf</TEXTAREA>
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Text ($Male $Child): <$STDFONT_E><BR>
    </TD><TD></TD></TR>
    <TR><TD COLSPAN=2>
    <TEXTAREA NAME=\"sr_textcm\" ROWS=8 COLS=45
    WRAP=VIRTUAL>$sr_textcm</TEXTAREA>
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Text ($Female $Child): <$STDFONT_E><BR>
    </TD><TD></TD></TR>
    <TR><TD COLSPAN=2>
    <TEXTAREA NAME=\"sr_textcf\" ROWS=8 COLS=45
    WRAP=VIRTUAL>$sr_textcf</TEXTAREA>
    </TD></TR>
    </TABLE>
    <BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
    </CENTER></FORM>
  ";
 
  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <BR><BR>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_Modification</A>
    </CENTER>
  ";

    freemed_close_db ();
    freemed_display_html_bottom ();
    DIE ("");


} elseif ($action=="mod") {
   //////////////////////////////////////////////////////
   #      M O D I F Y - R O U T I N E
   //////////////////////////////////////////////////////
  freemed_display_box_top ("$Modifying $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

    // prepare data

   $sr_text_blob    = addslashes ($sr_text)   ;
   $sr_textf_blob   = addslashes ($sr_textf)  ;
   $sr_textcm_blob  = addslashes ($sr_textcm) ;
   $sr_textcf_blob  = addslashes ($sr_textcf) ;

    // build update query:
    // only set the values that need to be
    // changed... for example, don't set the
    // creation date in a modify. also,
    // remember the commas...

  $query = "UPDATE $db_name SET ".
    "sr_label  = '$sr_label', ".
    "sr_type   = '$sr_type',  ".
    "sr_text   = '$sr_text_blob',  ".
    "sr_textf  = '$sr_textf_blob', ". 
    "sr_textcm = '$sr_textcm_blob',".
    "sr_textcf = '$sr_textcf_blob' ".
    "WHERE id='$id'";

  $result = $sql->query($query); // execute query

  if ($debug==1) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>$done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  } // end of error reporting clause

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth&action=view\"
     ><$STDFONT_B>$Return_to_reports_menu<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom (); // display box bottom 

  freemed_close_db ();
  freemed_display_html_bottom ();
  DIE ("");

///////////////////////////////////////////////////

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $record_name", $page_name);

    // select only "id" record, and delete
  $result = $sql->query("DELETE FROM $db_name
    WHERE (id = \"$id\")");

  echo "
    <P>
    <I>$record_name <B>$id</B> $Deleted<I>.
    <BR>
  ";
  if ($debug==1) {
    echo "
      <BR><B>RESULT:</B><BR>
      $result<BR><BR>
    ";
  }
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Update_Delete_Another</A></CENTER>
  ";
  freemed_display_box_bottom ();
  freemed_close_db ();
  freemed_display_html_bottom ();
  DIE ("");

} elseif ($action=="export") {

  freemed_display_box_top ("$Export $record_name", $_ref, $page_name);

  echo "
    <P>
    <$STDFONT_B>$Exporting_data ...
  ";

  $result = freemed_export_stock_data ($db_name);

  if ($result) echo "$Done.";
   else echo "$ERROR";

  echo "
    <P>

    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>

    <P>
  ";

  freemed_display_box_bottom ();
  freemed_close_db ();
  freemed_display_html_bottom ();
  DIE ("");


} elseif ($action=="choose") {
  /////////////////////////////////////////////////////////////////////
  ////////       C H O O S E    R O U T I N E       ///////////////////

  // check to see if chained from a patient module...
  if ((strlen($patient)<1) OR ($patient < 1)) {
    freemed_display_box_top ("Simple Reports Module :: $ERROR", $_ref);
    echo "
      <$HEADERFONT_B>
       $Must_Have_Patient_to_Make_Reports
      <$HEADERFONT_E>
    ";
    freemed_display_box_bottom ();
    freemed_close_db ();
    freemed_display_html_bottom ();
    DIE ("");
  
    }

   // get the user
    $_user = explode (":", $LoginCookie)       ;
    $user  = $_user[0]                         ;
   // set the variable if user is a physician
      if (freemed_get_link_field ($user, "user", "usertype")=="phy") {
          $is_physician = true                 ;                     }

    
    // if there is a patient... Let's load some variables
      $pat = new Patient ($patient);

      $ptdoc       = $pat->local_record ["ptdoc"   ];
      $ptlname     = $pat->local_record ["ptlname" ];
      $ptfname     = $pat->local_record ["ptfname" ];
      $ptmname     = $pat->local_record ["ptmname" ]; // 19990909
      $ptdob       = $pat->local_record ["ptdob"   ]; // 19990730 (thanks Max)
      $ptsex       = $pat->local_record ["ptsex"   ]; // 19990926 
      $ptaddr1     = $pat->local_record ["ptaddr1" ];
      $ptaddr2     = $pat->local_record ["ptaddr2" ];
      $ptcity      = $pat->local_record ["ptcity"  ];
      $ptstate     = $pat->local_record ["ptstate" ];
      $ptcountry   = $pat->local_record ["ptcountry" ];      
      $ptzip       = $pat->local_record ["ptzip"   ];
      $ptfax       = $pat->local_record ["ptfax"   ];
      $ptfaxext    = $pat->local_record ["ptfaxext"];
      $ptemail     = $pat->local_record ["ptemail" ];
      $ptid        = $pat->local_record ["ptid"    ];
      $ptssn       = $pat->local_record ["ptssn"   ];
      $ptempl      = $pat->local_record ["ptempl"  ];
      $ptemp1      = $pat->local_record ["ptemp1"  ];
      $ptemp2      = $pat->local_record ["ptemp2"  ];
      $ptins1      = $pat->payer[0]->local_record["payerinsco"  ];
      $ptins2      = $pat->payer[1]->local_record["payerinsco"  ];
      $ptins3      = $pat->payer[2]->local_record["payerinsco"  ];

      $_auth   = "_ref=$page_name";
//
     $pt_abbrev_sex = $ptsex ;

// let's try some birthdate translation: disassemble ptdob
  $ptdob1    = substr ($ptdob, 0, 4);   // year part
  $ptdob2    = substr ($ptdob, 5, 2);   // month part
  $ptdob3    = substr ($ptdob, 8, 2);   // day part

// now we assemble a timestamp with that
  $ptdob_timestamp = mktime (0, 0, 0, $ptdob2, $ptdob3, $ptdob1); 

// then set it to our format preset in lib/freemed.php
   $ptdob = strftime("$local_date_display", $ptdob_timestamp)     ;


// we use ptdob_timestamp to calculate if it is a youngster or not

$ptage = date("Y") - $ptdob1   ;

  if ( $ptage < 15 ) {

    if ($pt_abbrev_sex == "m") {
        $ptsex       = "$Child"       ;
        $textident   = "male_child"   ;
    } else   {
        $ptsex       = "$Child"       ;
        $textident   = "female_child" ;
    }

  } else { // if over 15 years old

    if ($pt_abbrev_sex == "m") {
        $ptsex       = "$Mr"          ;
        $textident   = "male_adult"   ;
    } else   {
        $ptsex       = "$Mrs"         ;
        $textident   = "female_adult" ;
    }

  } // end of 15 years if/else


  // if user is a physician we prefer using his identity
      if ($is_physician) {
  $physician_number = freemed_get_link_field ($user, "user", "userrealphy");
  // set the patient's doc to the current physician
  $ptdoc = $physician_number ;
   // get the physician name at least

     $doc_arr = freemed_get_link_rec ($ptdoc, "physician") ;
    
  // And now into discrete variables

    $phylname         = $doc_arr ["phylname"    ];
    $phyfname         = $doc_arr ["phyfname"    ];
    $phytitle         = $doc_arr ["phytitle"    ];
    $phymname         = $doc_arr ["phymname"    ];
    $phypracname      = $doc_arr ["phypracname" ];

  } // end of checking for physician

//

  freemed_display_box_top ("$Choose_Report_for_Patient $ptid", $page_name);

     echo "
       <TABLE WIDTH=100% BORDER=1 CELLPADDING=3 BGCOLOR=#FFFFFF><TR><TD>
       <CENTER><FONT FACE=\"Arial, Helvetica, Verdana\">
      <B>".$pat->fullName(true)." # $patient</B>
      </FONT></CENTER>
       </TD></TR></TABLE>
          ";
  // now we must choose in a form the report we want and pass some data

  echo "
    <FORM ACTION=\"$page_name\" METHOD=POST>
       ";
  echo "
    <TABLE WIDTH=100% BORDER=0 VALIGN=MIDDLE ALIGN=CENTER>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Report : <$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"chosenrep\">
       "; // break for reports list

  freemed_display_simplereports ();

  echo "
    </SELECT><BR>
    </TD></TR>
       ";
  // choose the physician only if user is not a physician
  // default doc is then taken form patient's in-house doc
  if (empty($is_physician)) {
  echo "
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Physician : <$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"ptdoc\">
       ";

  freemed_display_physicians ($ptdoc);

  echo "
    </SELECT>
    </TD></TR>
    <BR>
       ";
                            }  else {
echo "
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Physician : <$STDFONT_E>
    </TD><TD>
         $phylname $phymname $phyfname $phytitle
    </TD></TR>
     ";
                                    }
  // end conditional physician choice
  // show full academic fluff?
 echo "
    <TR><TD>&nbsp;
    </TD><TD>
    <INPUT TYPE=CHECKBOX NAME=\"full_titles\" VALUE=\"yes\">
    <I>$Show_full_titles</I>
    </TD></TR>
      ";

  // select the facility (used for the headers)


  echo "
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Facility : <$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"facility\">
       ";

  freemed_display_facilities ($default_facility);

  echo "
    </SELECT>
    </TD></TR>
    <TR><TD>&nbsp;
    </TD><TD>
   <INPUT TYPE=CHECKBOX NAME=\"suppress_headers\" VALUE=\"yes\">
   <I>$Suppress_headers</I>
    </TD></TR>
    <BR>
       ";

  // select where to look for the destinatary's address


  echo "
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Destinatary_is : <$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"destinatary\">
      <OPTION VALUE=\"0\">$NONE_SELECTED
      <OPTION VALUE=\"dest_pat\">$The_Patient
      <OPTION VALUE=\"dest_doc\">$A_Physician
      <OPTION VALUE=\"dest_emp1\">$Patients_primary_employer
      <OPTION VALUE=\"dest_emp2\">$Patients_secundary_employer
      <OPTION VALUE=\"dest_ins1\">$Patients_primary_insurer
      <OPTION VALUE=\"dest_ins2\">$Patients_secundary_insurer
      <OPTION VALUE=\"dest_ins3\">$Patients_tertiary_insurer
      <OPTION VALUE=\"dest_contact\">$Select_from_contact_list
      <OPTION VALUE=\"dest_other\">$Other
     </SELECT>
    </TD></TR>
       ";

  // select the delivery method for dispatching


  echo "
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Delivery_Method : <$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"delivery\">
      <OPTION VALUE=\"0\">$NONE_SELECTED
      <OPTION VALUE=\"del_html\">HTML
      <OPTION VALUE=\"del_print\">$Printer
      <OPTION VALUE=\"del_fax\">$Fax
      <OPTION VALUE=\"del_email\">$E_mail (ISO)
      <OPTION VALUE=\"del_email_pdf\">$E_mail (PDF)
     </SELECT>
    </TD></TR>
       ";

  // close the stuff

  echo "
    </TABLE>
    <P>
    <CENTER>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"chosen\"> 
    <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
    <INPUT TYPE=HIDDEN NAME=\"ptlname\" VALUE=\"$ptlname\">
    <INPUT TYPE=HIDDEN NAME=\"ptfname\" VALUE=\"$ptfname\">
    <INPUT TYPE=HIDDEN NAME=\"ptmname\" VALUE=\"$ptmname\">
    <INPUT TYPE=HIDDEN NAME=\"ptdob\" VALUE=\"$ptdob\">
    <INPUT TYPE=HIDDEN NAME=\"ptsex\" VALUE=\"$ptsex\">
    <INPUT TYPE=HIDDEN NAME=\"ptaddr1\" VALUE=\"$ptaddr1\">
    <INPUT TYPE=HIDDEN NAME=\"ptaddr2\" VALUE=\"$ptaddr2\">
    <INPUT TYPE=HIDDEN NAME=\"ptcity\" VALUE=\"$ptcity\">
    <INPUT TYPE=HIDDEN NAME=\"ptcountry\" VALUE=\"$ptcountry\">
    <INPUT TYPE=HIDDEN NAME=\"ptstate\" VALUE=\"$ptstate\">
    <INPUT TYPE=HIDDEN NAME=\"ptzip\" VALUE=\"$ptzip\"> 
    <INPUT TYPE=HIDDEN NAME=\"ptfax\" VALUE=\"$ptfax\">
    <INPUT TYPE=HIDDEN NAME=\"ptfaxext\" VALUE=\"$ptfaxext\">
    <INPUT TYPE=HIDDEN NAME=\"ptemail\" VALUE=\"$ptemail\">
    <INPUT TYPE=HIDDEN NAME=\"ptid\" VALUE=\"$ptid\">
    <INPUT TYPE=HIDDEN NAME=\"ptssn\" VALUE=\"$ptssn\">
    <INPUT TYPE=HIDDEN NAME=\"ptempl\" VALUE=\"$ptempl\">
    <INPUT TYPE=HIDDEN NAME=\"ptemp1\" VALUE=\"$ptemp1\">
    <INPUT TYPE=HIDDEN NAME=\"ptemp2\" VALUE=\"$ptemp2\">
    <INPUT TYPE=HIDDEN NAME=\"ptins1\" VALUE=\"$ptins1\">
    <INPUT TYPE=HIDDEN NAME=\"ptins2\" VALUE=\"$ptins2\">
    <INPUT TYPE=HIDDEN NAME=\"ptins3\" VALUE=\"$ptins3\">
    <INPUT TYPE=HIDDEN NAME=\"ptage\" VALUE=\"$ptage\">
    <INPUT TYPE=HIDDEN NAME=\"textident\" VALUE=\"$textident\">
    ";
   if ($is_physician) { 
    echo "
    <INPUT TYPE=HIDDEN NAME=\"ptdoc\" VALUE=\"$ptdoc\">
         ";           }     
    echo "
    <INPUT TYPE=SUBMIT VALUE=\" $Proceed \">
    <INPUT TYPE=RESET  VALUE=\" $Clear \">
    </CENTER></FORM>
       ";

  freemed_display_box_bottom ();

 echo "
    <CENTER><A HREF=\"manage.php?$_auth&id=$patient\"
     ><$STDFONT_B>$Manage_Patient<$STDFONT_E></CENTER>
    </P>
  ";

 echo "
    <CENTER><A HREF=\"simplerep.php3?$_auth&action=view&patient=$patient\"
     ><$STDFONT_B>$Edit_add_simple_reports<$STDFONT_E></CENTER>
    </P>
  ";


  freemed_close_db ();
  freemed_display_html_bottom ();
  DIE ("");

}  elseif ($action=="chosen") {

////////////////////////////////////////////////////////////////////

// sanity check
// send ppl back if they omitted to choose a report or a facility
 
if ($chosenrep==0) 
  {
 freemed_display_box_top ("$Edit_report_for_patient $ptid", $page_name);

   echo "
       <TABLE WIDTH=100% BORDER=1 CELLPADDING=3 BGCOLOR=#FFFFFF><TR><TD>
       <CENTER><FONT FACE=\"Arial, Helvetica, Verdana\">
      <B>$ptsex $ptlname, $ptfname $ptmname [<I>$ptdob</I>] # $patient </B>
      </FONT></CENTER>
       </TD></TR></TABLE>
       <BR>
          ";

  echo "<BR><B><STRONG>
  $Please_choose $Report $Before $Proceeding
        </STRONG></B>";

 echo "
    <CENTER><A HREF=\"simplerep.php3?$_auth&action=choose&patient=$patient\"
     ><$STDFONT_B>$Return_to_report_selection<$STDFONT_E></CENTER>
    </P>
  ";
    freemed_display_box_bottom ();
    freemed_close_db ();
    freemed_display_html_bottom ();
    DIE ("");   // DIE, DIE, php3, DIE!
  }
// end of sanity check
////////////////////////
// Verify that, if the user is a physician, is not signing for another

   // extract the current user from the cookie
    $_user = explode (":", $LoginCookie)       ;
    $user  = $_user[0]                         ;
   // check if user is a physician
      if (freemed_get_link_field($user, "user", "usertype")=="phy")
          $is_physician = true                 ;
      if ($is_physician)
  $physician_number = freemed_get_link_field ($user, "user", "userrealphy");
   // compare both numbers, if they're the same we're happy.
   // otherwise error out: do not allow a physician signing for another
   //    if ( $physician_number != $ptdoc ) {
   //         $choose_error = "$A_physician_must_sign_his_own_name";
   //                                       }


/////////////////////////////////////////
// set some variables for string building

  $Space         = " "                               ;
  $Points        = ":"                               ;
  $P             = "<P>"                             ;
  $Comma         = ","                               ;



// fetch the template data selected in $chosenrep

  $result = $sql->query("SELECT * FROM $db_name ".
    "WHERE ( id = '$chosenrep' )");

                  
  $r = $sql->fetch_array($result); // dump into array r[]

  $sr_label   = $r["sr_label"]                     ;
  $sr_type    = $r["sr_type" ]                     ;
  $sr_text    = htmlentities ( $r["sr_text"   ] )  ;
  $sr_textf   = htmlentities ( $r["sr_textf"  ] )  ;
  $sr_textcm  = htmlentities ( $r["sr_textcm" ] )  ;
  $sr_textcf  = htmlentities ( $r["sr_textcf" ] )  ;

  // immediately choose the right text to work with 
  // according to the patient's age and sex
  
      if ($textident == "male_child")   { $sr_text = $sr_textcm ; }
  elseif ($textident == "female_child") { $sr_text = $sr_textcf ; }
  elseif ($textident == "female_adult") { $sr_text = $sr_textf  ; }
  elseif ($textident == "male_adult")   { $sr_text = $sr_text   ; }

  // strip the slashes outta da text

  $sr_text  = stripslashes ($sr_text) ;

  
  // let's get the facility for the header

   $fac_array = freemed_get_link_rec ($facility, "facility");

    $psrname      = $fac_array ["psrname"     ];
    $psraddr1     = $fac_array ["psraddr1"    ];
    $psraddr2     = $fac_array ["psraddr2"    ];
    $psrcity      = $fac_array ["psrcity"     ];
    $psrstate     = $fac_array ["psrstate"    ];
    $psrzip       = $fac_array ["psrzip"      ];
    $psrcountry   = $fac_array ["psrcountry"  ];
    $psrnote      = $fac_array ["psrnote"     ];
    $psrdateentry = $fac_array ["psrdateentry" ];
    $psrdefphy    = $fac_array ["psrdefphy"    ];
    $psrphone     = $fac_array ["psrphone"     ];
    $psrfax       = $fac_array ["psrfax"       ];
    $psremail     = $fac_array ["psremail"     ];


 // Get the good doctor information into an array

  $doc_arr = freemed_get_link_rec ($ptdoc, "physician") ;
    
  // And now into discrete variables

    $phylname         = $doc_arr ["phylname"    ];
    $phyfname         = $doc_arr ["phyfname"    ];
    $phytitle         = $doc_arr ["phytitle"    ];
    $phymname         = $doc_arr ["phymname"    ];
    $phypracname      = $doc_arr ["phypracname" ];
    $phyaddr1a        = $doc_arr ["phyaddr1a"   ];
    $phyaddr2a        = $doc_arr ["phyaddr2a"   ];
    $phycitya         = $doc_arr ["phycity1a"   ];
    $phystatea        = $doc_arr ["phystatea"   ];
    $phyzipa          = $doc_arr ["phyzipa"     ];
    $phyphonea        = $doc_arr ["phyphonea"   ];
    $phyfaxa          = $doc_arr ["phyfaxa"     ];
    $phyaddr1b        = $doc_arr ["phyaddr1b"   ];
    $phyaddr2b        = $doc_arr ["phyaddr2b"   ];
    $phycityb         = $doc_arr ["phycityb"    ];
    $phystateb        = $doc_arr ["phystateb"   ];
    $phyzipb          = $doc_arr ["phyzipb"     ];
    $phyphoneb        = $doc_arr ["phyphoneb"   ];
    $phyfaxb          = $doc_arr ["phyfaxb"     ];
    $phyemail         = $doc_arr ["phyemail"    ];
    $phycellular      = $doc_arr ["phycellular" ];
    $phypager         = $doc_arr ["phypager"    ];
    $phyupin          = $doc_arr ["phypin"      ];
    $physsn           = $doc_arr ["physsn"      ];
    //
    $phydeg1          = $doc_arr ["phydeg1"     ];
    $phydeg2          = $doc_arr ["phydeg2"     ];
    $phydeg3          = $doc_arr ["phydeg3"     ];
    $physpe1          = $doc_arr ["physpe1"     ];
    $physpe2          = $doc_arr ["physpe2"     ];
    $physpe3          = $doc_arr ["physpe3"     ];
    $phyid1           = $doc_arr ["phyid1"      ];
    $phystatus        = $doc_arr ["phystatus"   ];
    $phyref           = $doc_arr ["phyref"      ];
    $phyrefcount      = $doc_arr ["phyrefcount" ];
    $phyrefamt        = $doc_arr ["phyrefamt"   ];
    $phyrefcoll       = $doc_arr ["phyrefcoll"  ];


  // Get the physician's additional degrees in cleartext if they exist

 if ($phydeg1 != 0) {

  $deg_arr = freemed_get_link_rec ($phydeg1, "degrees") ;
    $degdegree1       = $deg_arr ["degdegree"  ];
    $degname1         = $deg_arr ["degname"    ];
     if ($full_titles == "yes") {
  $phytitle = $phytitle.$Comma.$Space.$degdegree1 ;
                                }
  // Check if there is a second degree

     if ($phydeg2 != 0) {

  $deg_arr = freemed_get_link_rec ($phydeg2, "degrees") ;
    $degdegree2       = $deg_arr ["degdegree"  ];
    $degname2         = $deg_arr ["degname"    ];
     if ($full_titles == "yes") {
  $phytitle = $phytitle.$Comma.$Space.$degdegree2 ;
                                }
  // Look also for a third degree
          if ($phydeg3 != 0) {

  $deg_arr = freemed_get_link_rec ($phydeg3, "degrees") ;
    $degdegree3       = $deg_arr ["degdegree"  ];
    $degname3         = $deg_arr ["degname"    ];
     if ($full_titles == "yes") {
  $phytitle = $phytitle.$Comma.$Space.$degdegree3 ;
                                }
                             }
                        }                        
                    }

  // Get the specialities now if there are any

  if ($physpe1 != 0 ) {
  
  $spe_arr = freemed_get_link_rec ($physpe1, "specialties") ;
    $specname1        = $spe_arr ["specname"  ];
    $specdesc1        = $spe_arr ["specdesc"  ];


  // Get also a second speciality if exists      
       if ($physpe2 != 0)  {

     $spe_arr = freemed_get_link_rec ($physpe2, "specialties") ;
       $specname2        = $spe_arr ["specname"  ];
       $specdesc2        = $spe_arr ["specdesc"  ];
            
  // if there's a second then maybe there's also a third speciality
       if ($physpe3 != 0)  {

     $spe_arr = freemed_get_link_rec ($physpe3, "specialties") ;
       $specname3        = $spe_arr ["specname"  ];
       $specdesc3        = $spe_arr ["specdesc"  ];
                          }
                        }
                      }
                                   

  // Build the signature lines

      
  $signature_line_1 = $phyfname.$Space.$phymname.$Space.$phylname.$Comma.$Space.$phytitle ;
  if ($full_titles == "yes") {

  $signature_line_2 = $specname1.$Space.$specname2.$Space.$specname3 ;
                             } else {
  $signature_line_2 = $Space ;
                                    }

  //Now we do get the destinatary -if already available from our data-
  // And set the corresponding prompts if we need some further data
  // Let's start with reports addressed to the patient: we already have all the data

 // Most of the reports are actually given to the PATIENT



if ($destinatary == "dest_pat") {
  // test if there is no delivery method selected and create warning string
        if ($delivery == "0")    {
           $choose_error    = "$No_delivery_method_selected" ;


                                 }
  // now the print handler 
        if ($delivery == "del_print")  {
           $form_action    = "lout"                ;
           $submit_button  = "$Print"              ;
           $page_name      = "simplerep_lout.php3" ;
   // build the destinatary lines 
   $dest_line_1 = $ptsex.$Space.$ptfname.$Space.$ptmname.$Space.$ptlname  ;
   $dest_line_2 = $ptaddr1                                                ;
   $dest_line_3 = $ptaddr2                                                ;
   $dest_line_4 = $ptstate.$Space.$ptzip.$Space.$ptcity.$Space.$ptcountry ;
                                        }
   // plain HTML for printouts handler
 
        if ($delivery == "del_html")  {
           $form_action    = "html"                ;
           $submit_button  = "$Show HTML"          ;
           $page_name      = "simplerep_html.php3" ;
   // build the destinatary lines 
   $dest_line_1 = $ptsex.$Space.$ptfname.$Space.$ptmname.$Space.$ptlname  ;
   $dest_line_2 = $ptaddr1                                                ;
   $dest_line_3 = $ptaddr2                                                ;
   $dest_line_4 = $ptstate.$Space.$ptzip.$Space.$ptcity.$Space.$ptcountry ;
                                        }
   // fax handler
         if ($delivery == "del_fax")   {
            $form_action       = "lout"                ; 
            $submit_button     = "$Send $Fax"          ;
            $page_name         = "simplerep_lout.php3" ;
            $fax_dest_number   = "$ptfax"              ;
            $fax_origin_number = "$phyfaxa"            ;
  // what if the patient has no fax number ?        
            if (strlen($ptfax)<5) { 
               $choose_error = "$Patient_has_no_fax"   ;
                                  }
            $fax_notify_email  = "$phyemail"           ;
              if ( strlen($phyemail)<4 ) {
            $fax_notify_email  = "$psremail"           ;        
                                         } 

   // build the destinatary lines
   $dest_line_1 = $ptsex.$Space.$ptfname.$Space.$ptmname.$Space.$ptlname  ;
   $dest_line_2 = $Fax.$Points.$Space.$fax_dest_number                    ;
                                       }
  // e-mail handler (plain text mail)
         if ($delivery == "del_email")   {
            $form_action     = "lout"             ; 
            $submit_button   = "$Send $E_mail"    ;
            $page_name       = "simplerep_plaintext_email.php3" ;
            $email_dest_addr = "$ptemail"         ;
  // what if the patient has no e-mail address        
            if (strlen($ptemail)<4) { 
               $choose_error = "$Patient_has_no_email" ;
                                  }
   // build the destinatary lines
   $dest_line_1 = $ptsex.$Space.$ptfname.$Space.$ptmname.$Space.$ptlname  ;
   $dest_line_2 = $E_mail.$Points.$Space.$email_dest_addr                 ;
   // set the reply-to from the selected doctor
            $email_replyto_addr = "$phyemail"  ;
   // if physician has no e-mail, use the facility e-mail
          if (strlen($phyemail)<4) { 
            $email_replyto_addr = "$psremail"   ;   
                                   }

                                         } 
  // handle pdf e-mails
 if ($delivery == "del_email_pdf")   {
            $form_action     = "lout"                   ; 
            $submit_button   = "$Send $E_mail (PDF)"    ;
            $page_name       =  "simplerep_lout.php3"   ;
            $email_dest_addr = "$ptemail"               ;
  // what if the patient has no e-mail address        
            if (strlen($ptemail)<4) { 
               $choose_error = "$Patient_has_no_email"  ;
                                  }
   // set the reply-to from the selected doctor
            $email_replyto_addr = "$phyemail"  ;
   // if physician has no e-mail, use the facility e-mail
          if (strlen($phyemail)<4) { 
            $email_replyto_addr = "$psremail"   ;   
                                   }
   // build the destinatary lines
   $dest_line_1 = $ptsex.$Space.$ptfname.$Space.$ptmname.$Space.$ptlname  ;
   $dest_line_2 = $E_mail.$Points.$Space.$email_dest_addr                 ;


                                         } 





                              }  // end of things patient-addressed



if ($destinatary == "dest_ins1") {
  // test if there is no delivery method selected and create warning string
        if ($delivery == "0")    {
           $choose_error    = "$No_delivery_method_selected" ;
                                 }









                                }   // end of 1st insurer



if ($destinatary == "dest_ins2") {










                                }   // end of 2nd insurer



if ($destinatary == "dest_ins3") {










                                }   // end of 3d insurer








  // now we build the header (facility) strings, excepted if we suppress them

        if (suppress_headers != "yes") {
  $header_line_1 = $psrname                          ;
  $header_line_2 = $psraddr1                         ;
  $header_line_3 = $psraddr2                         ;
  $header_line_4 = $psrstate.$Space.$psrzip.$Space.$psrcity.$Space.$psrcountry ;
  $header_line_5 = $Phone.$Points.$Space.$psrphone   ;
  $header_line_6 = $Fax.$Points.$Space.$psrfax       ;
  $header_line_7 = $E_mail.$Points.$Space.$psremail  ;
                                            }

  // we need the current date

$local_rep_date = strftime("$local_date_display")     ;

  // Now we build the dateline

$date_line = $psrcity.$Comma.$Space.$Datequalif.$Space.$local_rep_date  ;

  // check no telltale variables have been maliciously included

   $sr_text = str_replace ( "\$db_user" , "" , $sr_text )       ;
   $sr_text = str_replace ( "\$db_password" , "" , $sr_text )   ;


  // let's insert our variables inside the text
    $new_text =  fm_eval ( $sr_text ) ;  // that one isn't working for now
    $sr_text = $new_text ;               // as of 19991103

  //eval( "\$sr_text = \"$sr_text\";" );

////////  all preliminaries before this, generate html after /////

 freemed_display_box_top ("$Edit_report_for_patient $ptid", $page_name);

     echo "
       <TABLE WIDTH=100% BORDER=1 CELLPADDING=3 BGCOLOR=#FFFFFF><TR><TD>
       <CENTER><FONT FACE=\"Arial, Helvetica, Verdana\">
      <B>$ptsex. $ptlname, $ptfname $ptmname [<I>$ptdob</I>] # $patient </B>
      </FONT></CENTER>
       </TD></TR></TABLE>
       <BR>
          ";

if ($delivery != "del_email")  {

//  if (($delivery == "del_print") or ($delivery == "del_fax")) 

  if  ($suppress_headers != "yes") {
  // conditionally send the header lines
   echo "<CENTER>
  <H2>$header_line_1</H2><P>
     $header_line_2 <P>
     $header_line_3 <P>
     $header_line_4 <P>
     $header_line_5 <P>
     $header_line_6 <P>
     $header_line_7 <P>
      </CENTER>
      <P>
        " ;
                                  }                         





  // conditionally show the destinatary if we are printing
  if ( ($delivery == "del_print") or ($delivery == "del_html") ) {
   echo "
      <TABLE WIDTH=100% BORDER=0 CELLPADDING=2>
      <TR>
      <TD WIDTH=40%>   
      <P ALIGN=RIGHT>
      <B> $To :</B>      
      </P> 
      </TD><TD WIDTH=60%>
      $dest_line_1
      </TD></TR>
      <TD WIDTH=40%></TD>
      <TD WIDTH=60%>
      $dest_line_2
      </TD></TR>
      <TD WIDTH=40%></TD>
      <TD WIDTH=60%>
      $dest_line_3
      </TD></TR>
      <TD WIDTH=40%></TD>
      <TD WIDTH=60%>
      $dest_line_4
      </TD></TR>
      </TABLE>
   ";
                                 }
  // conditionally show
  if ( $delivery == "del_fax" )  {
   echo "
      <TABLE WIDTH=100% BORDER=0 CELLPADDING=2>
      <TR>
      <TD WIDTH=50%>   
      <P ALIGN=RIGHT>
      <B> $To :</B>      
      </P> 
      </TD><TD WIDTH=50%>
      $dest_line_1
      </TD></TR>
      <TD WIDTH=50%></TD>
      <TD WIDTH=50%>
      $dest_line_2
      </TD></TR>
      </TABLE>
        " ;
            
                               }

  } // end non-email displays


  // start with the form
         
   echo "
    <P>

    <FORM ACTION=\"$page_name\" METHOD=POST>
    <TABLE WIDTH=100% BORDER=0 CELLPADDING=2>
 
        " ;



  if ( ( $delivery == "del_email_pdf" ) or ( $delivery == "del_email" ) ) {
   echo "
      <TR><TD>
      <$STDFONT_B>$From : <$STDFONT_E>
      </TD><TD>
       \"$signature_line_1\" &lt; $email_replyto_addr &gt;
      </TD></TR>
      <TR><TD>   
      <$STDFONT_B>$To : <$STDFONT_E>      
      </TD><TD>
       \"$dest_line_1\" &lt; $email_dest_addr &gt;
      </TD></TR>
      <TR><TD>
      <$STDFONT_B>$Copy_to : <$STDFONT_E>
      </TD><TD>
     <INPUT TYPE=TEXT NAME=email_copy_to SIZE=50 MAXLENGTH=50
      VALUE=\"$email_copy_to\">
      </TD></TR>
      <TR><TD>
      <$STDFONT_B>$Subject : <$STDFONT_E>
      </TD><TD>
      <INPUT TYPE=TEXT NAME=sr_label SIZE=50 MAXLENGTH=50
      VALUE=\"$sr_label\">
      </TD></TR>
      <TR><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>

        " ;
                                     }


   echo "
    <TR><TD COLSPAN=2>
    <INPUT TYPE=TEXT NAME=date_line SIZE=50 MAXLENGTH=50
     VALUE=\"$date_line\">
     </TD></TR>
     <TR><TD COLSPAN=2> 
     <TEXTAREA NAME=\"sr_text\" ROWS=10 COLS=60
       WRAP=VIRTUAL>$sr_text</TEXTAREA>
     </TD></TR>
         " ;


    echo "
     <TR><TD COLSPAN=2>
     $signature_line_1
     </TD></TR> 

     <TR><TD COLSPAN=2>
      $signature_line_2
     </TD></TR> 
         " ;

if ( $delivery == "del_email" )  {
   if ( $suppress_headers != "yes" ) {
  
  echo "
    <TR><TD COLSPAN=2>&nbsp;</TD></TR>
    <TR><TD COLSPAN=2>$header_line_1</TD></TR>
    <TR><TD COLSPAN=2>$header_line_2</TD></TR>
    <TR><TD COLSPAN=2>$header_line_3</TD></TR>
    <TR><TD COLSPAN=2>$header_line_4</TD></TR>
    <TR><TD COLSPAN=2>$header_line_5</TD></TR>
    <TR><TD COLSPAN=2>$header_line_6</TD></TR>
    <TR><TD COLSPAN=2>$header_line_7</TD></TR>
       " ;
                                     }
                                   } // end email-specific display



   // show the printer selection droplist if printer is the delivery method
  if ($delivery == "del_print") {

  echo "
    <TR><TD>
    <$STDFONT_B>$Printer : <$STDFONT_E>
    <SELECT NAME=\"printer\">
       "; // break for printerlist

  freemed_display_printerlist ($default_printer) ;

  echo "
    </SELECT>
    </TD><TD>
    </TD></TR>
       ";
                                }
    // end printer stuff
echo "
    </TABLE>
    <BR>
    <CENTER>
     ";
   // show error message if any

   if ($choose_error) 
    echo "<B>$choose_error</B><BR>   
         "; 

   // conditional creation of the action buttons if no error was generated
   if (empty($choose_error)) {
    echo "
    <INPUT TYPE=HIDDEN NAME=\"header_line_1\" VALUE=\"$header_line_1\">
    <INPUT TYPE=HIDDEN NAME=\"header_line_2\" VALUE=\"$header_line_2\">
    <INPUT TYPE=HIDDEN NAME=\"header_line_3\" VALUE=\"$header_line_3\">
    <INPUT TYPE=HIDDEN NAME=\"header_line_4\" VALUE=\"$header_line_4\">
    <INPUT TYPE=HIDDEN NAME=\"header_line_5\" VALUE=\"$header_line_5\">
    <INPUT TYPE=HIDDEN NAME=\"header_line_6\" VALUE=\"$header_line_6\">
    <INPUT TYPE=HIDDEN NAME=\"header_line_7\" VALUE=\"$header_line_7\">
    <INPUT TYPE=HIDDEN NAME=\"suppress_headers\" VALUE=\"$suppress_headers\">
    <INPUT TYPE=HIDDEN NAME=\"dest_line_1\" VALUE=\"$dest_line_1\">
    <INPUT TYPE=HIDDEN NAME=\"dest_line_2\" VALUE=\"$dest_line_2\">
    <INPUT TYPE=HIDDEN NAME=\"dest_line_3\" VALUE=\"$dest_line_3\">
    <INPUT TYPE=HIDDEN NAME=\"dest_line_4\" VALUE=\"$dest_line_4\">
    <INPUT TYPE=HIDDEN NAME=\"signature_line_1\" VALUE=\"$signature_line_1\">
    <INPUT TYPE=HIDDEN NAME=\"signature_line_2\" VALUE=\"$signature_line_2\">
    <INPUT TYPE=HIDDEN NAME=\"delivery\" VALUE=\"$delivery\">
    <INPUT TYPE=HIDDEN NAME=\"fax_dest_number\" VALUE=\"$fax_dest_number\">
    <INPUT TYPE=HIDDEN NAME=\"fax_origin_number\" VALUE=\"$fax_origin_number\">
    <INPUT TYPE=HIDDEN NAME=\"fax_notify_email\" VALUE=\"$fax_notify_email\">
    <INPUT TYPE=HIDDEN NAME=\"email_dest_addr\" VALUE=\"$email_dest_addr\">
    <INPUT TYPE=HIDDEN NAME=\"email_replyto_addr\" VALUE=\"$email_replyto_addr\">
        " ;
  if ( ( $delivery != "del_email_pdf" ) or ( $delivery != "del_email" ) ) {
   echo "
    <INPUT TYPE=HIDDEN NAME=\"sr_label\" VALUE=\"$sr_label\">
        " ; }

   echo "
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"$form_action\"> 
    <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
    <INPUT TYPE=HIDDEN NAME=\"ptdoc\" VALUE=\"$ptdoc\">
    <INPUT TYPE=HIDDEN NAME=\"ptid1\" VALUE=\"$ptid1\">
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
         ";
                             }
    echo "
    </CENTER>
    </FORM>
         ";

  freemed_display_box_bottom ();

 echo "
    <CENTER><A HREF=\"simplerep.php3?$_auth&action=choose&patient=$patient\"
     ><$STDFONT_B>$Return_to_report_selection<$STDFONT_E></CENTER>
    </P>
  ";


 echo "
    <CENTER><A HREF=\"manage.php?$_auth&id=$patient\"
     ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></CENTER>
    </P>
  ";

  // ritual ablutions

    freemed_close_db ();
    freemed_display_html_bottom ();
    DIE ("");           // samhain time.
     
//////////////////////////////////////////////////////////////////////
//  P R I N T     R O U T I N E S  moved to simplerep_lout.php3 19991020

//////////////////////////////////////////////////////////
// H T M L routines moved to html.php3 19991014

//////////////////////////////////////////////////////////
//  F A X I N G   R O U T I N E S   moved to simplerep_lout.php3 19991026 ///

//////////////////////////////////////////////////////////
//  M A I L I N G  R O U T I N E S moved to simplerep_plaintext_mail.php3 19991029


//////////////////////////////////////////////////////////////////////
//  M A I L I N G   R O U T I N E S (PDF)  moved to simplerep_lout.php3 19991026

//////////////////////////////////////////////////////////////////////
//  M A I L I N G     R O U T I N E S (XML)   ////////
} elseif ($action == "email_xml") {

    //  VOLUNTEERS WANTED FOR XML
    freemed_display_box_top ("Simple Reports Module :: PRINT", $_ref);
    echo "
      <$HEADERFONT_B>
      Awesome XML e-mailing stuff performing here sometime in the future!
      <$HEADERFONT_E>
    ";
    freemed_display_box_bottom ();
    freemed_close_db ();
    freemed_display_html_bottom ();
    DIE ("");





//////////////////////////////////////////////////////////////////////
} else {

  $query = "SELECT * FROM $db_name ".
   "ORDER BY $order_field";

  freemed_display_box_top ($record_name, $_ref, $page_name);

  echo freemed_display_itemlist (
    $sql->query("SELECT * FROM $db_name ORDER BY $order_field"),
    $page_name,
    array (
      _("Label")	=>	"sr_label",
      _("Category")	=>	"sr_type",
    ),
    array (

    )
  );

  freemed_display_box_bottom (); // display bottom of the box

} 
freemed_close_db ();
freemed_display_html_bottom ();

?>
