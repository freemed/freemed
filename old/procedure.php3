<?php
  # file: procedure.php3
  # note: procedure database services
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL

  $page_name="procedure.php3"; // for help info, later
  include "global.var.inc";
  include "freemed-functions.inc"; // generic functions

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top ();
  freemed_display_banner ();

//
//  MAIN BODY OF MODULE
//
//  LARGE IF LOOP THROUGH ACTIONS, WITH DEFAULT BEING

if ((strlen ($patient)<1) OR ($patient<1)) {
  freemed_display_box_top ("Procedure Module :: ERROR", $_ref, $page_name);
  echo "
    <P>
    <$STDFONT_B>Please specify a patient before attempting to<BR>
      use this menu.<$STDFONT_E>
    <P>
  ";
  freemed_display_box_bottom ();
  DIE("");
}

if ($action=="addform") {

  freemed_display_box_top ("Add Procedure", $_ref, $page_name);
  $p_lname = freemed_get_link_field ($patient, "patient", "ptlname");
  $p_fname = freemed_get_link_field ($patient, "patient", "ptfname");
  $p_guarantor = freemed_get_link_field ($patient, "patient", "ptdep");
  if ($p_guarantor<1) {
    $guarantor = "<B>SELF INSURED</B>";
    // do we really want this next bit? if there is no guarantor, do we
    // want there to be no link, or a link to the current patient?
    $proacctdep = $p_guarantor;
       // now, we grab the insurance data from the patient
    $procpriins = freemed_get_link_field ($patient, "patient", "ptins1");
    $procsecins = freemed_get_link_field ($patient, "patient", "ptins2");
  } else {
    $guarantor = freemed_get_link_field ($guarantor, "patient", "ptlname"). ", ".
       freemed_get_link_field ($guarantor, "patient", "ptfname");
    $proacctdep = $p_guarantor;
       // grab the insurance data from the guarantor
    $procpriins = freemed_get_link_field ($guarantor, "patient", "ptins1");
    $procsecins = freemed_get_link_field ($guarantor, "patient", "ptins2");
  } // end checking guarantor validity

  // pull other useful data over...
  // diagnoses as defaults...
  $procdx1 = freemed_get_link_field ($patient, "patient", "ptdiag1");
  $procdx2 = freemed_get_link_field ($patient, "patient", "ptdiag2");
  $procdx3 = freemed_get_link_field ($patient, "patient", "ptdiag3");
  $procdx4 = freemed_get_link_field ($patient, "patient", "ptdiag4");

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth&patient=$patient\"
     ><$STDFONT_B>Abort Addition<$STDFONT_E></A> |
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>Manage Patient<$STDFONT_E></A></CENTER>
     <P>

    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
    <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\"> 

    <$STDFONT_B>Patient : <B>$p_lname, $p_fname</B><$STDFONT_E>
    <INPUT TYPE=HIDDEN NAME=\"procacct\"
     VALUE=\"$patient\">
    <BR>

    <$STDFONT_B>Guarantor : $guarantor<$STDFONT_E>
    <INPUT TYPE=HIDDEN NAME=\"procacctdep\"
     VALUE=\"$procacctdep\">
    <BR>
   
    <$STDFONT_B>Incident Number : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procincident\" VALUE=\"$procincident\"
     SIZE=10 MAXLENGTH=10>
    <BR>


    <$STDFONT_B>CPT Code : <$STDFONT_E>
    <SELECT NAME=\"proccode\">
  ";

  freemed_display_cptcodes ($proccode);

  echo "
    </SELECT>
    <BR>

    <$STDFONT_B>CPT Modifiers : <$STDFONT_E><BR>
    <SELECT NAME=\"procmod1\">
  ";

  freemed_display_cptmods ($procmod1);

  echo "
    </SELECT><BR>
    <SELECT NAME=\"procmod2\">
  ";

  freemed_display_cptmods ($procmod1);

  echo "
    </SELECT><BR>  
    <SELECT NAME=\"procmod3\">
  ";

  freemed_display_cptmods ($procmod1);

  echo "
    </SELECT>  
    <P>

    <$STDFONT_B>ICD Codes : <$STDFONT_E><BR>
    1) <SELECT NAME=\"procdx1\">
  ";

  freemed_display_icdcodes ($procdx1);  // what code to list

  echo "
    </SELECT><BR>
    2) <SELECT NAME=\"procdx2\">
  ";

  freemed_display_icdcodes ($procdx2);

  echo "
    </SELECT><BR>
    3) <SELECT NAME=\"procdx3\">
  ";

  freemed_display_icdcodes ($procdx3);

  echo "
    </SELECT><BR>
    4) <SELECT NAME=\"procdx4\">
  ";

  freemed_display_icdcodes ($procdx4);

  echo "
    </SELECT>
    <BR>
    
    <$STDFONT_B>Procedure Comment : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"proccom\" VALUE=\"$proccom\"
     SIZE=30 MAXLENGTH=45>
    <BR>

    <$STDFONT_B>Service Provider : <$STDFONT_E>
    <SELECT NAME=\"procchg\">
  ";

  freemed_display_physicians ($procchg);

  echo "
    </SELECT>
    <BR>

    <$STDFONT_B>Place of Service : <$STDFONT_E>
    <SELECT NAME=\"procpos\">
  ";

  freemed_display_facilities ($procpos);

  echo "
    </SELECT>
    <BR>

    <$STDFONT_B>Type of Service : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procts\" VALUE=\"$procts\"
     SIZE=10 MAXLENGTH=30>
    <BR>

    <$STDFONT_B>Status : <$STDFONT_E>
     <SELECT NAME=\"procstatus\">
      <OPTION VALUE=1>open
      <OPTION VALUE=2>transfer &gt; patient
      <OPTION VALUE=3>approved estimate
      <OPTION VALUE=4>void
      <OPTION VALUE=5>transfer &gt; rebill
     </SELECT>
    <BR>

    <$STDFONT_B>Accounting GL Link : <$STDFONT_E>
    <!-- <INPUT TYPE=TEXT NAME=\"procdept\" VALUE=\"$procdept\"
     SIZE=10 MAXLENGTH=20> -->
    <BR>

    <$STDFONT_B>Voucher # : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procvouch\" VALUE=\"$procvouch\"
     SIZE=10 MAXLENGTH=20>
    <BR>

    <$STDFONT_B>Primary Insurance : <$STDFONT_E>
    <SELECT NAME=\"procpriins\">
  ";

  freemed_display_insco ($procpriins);

  echo "
    </SELECT>
    <BR>

    <$STDFONT_B>Secondary Insurance : <$STDFONT_E>
    <SELECT NAME=\"procsecins\">
  ";

  freemed_display_insco ($procsecins);

  echo "
    </SELECT>
    <BR>

    <$STDFONT_B>Units of Care : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procunits\" VALUE=\"$procunits\"
     SIZE=10 MAXLENGTH=10>
    <BR>

    <$STDFONT_B>Type of Charge : <$STDFONT_E>
     <SELECT NAME=\"proctype\">
      <OPTION VALUE=\"normal\"   $_type_n>normal
      <OPTION VALUE=\"finance\"  $_type_f>finance
      <OPTION VALUE=\"other\"    $_type_o>other
      <OPTION VALUE=\"contract\" $_type_c>contract
     </SELECT>
    <BR>

    <$STDFONT_B>Date of Service [from] : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procdate_y\" VALUE=\"$procdate_y\"
     SIZE=5 MAXLENGTH=4> <B>-</B>
    <INPUT TYPE=TEXT NAME=\"procdate_m\" VALUE=\"$procdate_m\"
     SIZE=3 MAXLENGTH=2> <B>-</B>
    <INPUT TYPE=TEXT NAME=\"procdate_d\" VALUE=\"$procdate_d\"
     SIZE=3 MAXLENGTH=2>
    <BR>

    <$STDFONT_B>Date of Service [to] : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procdate2_y\" VALUE=\"$procdate2_y\"
     SIZE=5 MAXLENGTH=4> <B>-</B>
    <INPUT TYPE=TEXT NAME=\"procdate2_m\" VALUE=\"$procdate2_m\"
     SIZE=3 MAXLENGTH=2> <B>-</B>
    <INPUT TYPE=TEXT NAME=\"procdate2_d\" VALUE=\"$procdate2_d\"
     SIZE=3 MAXLENGTH=2>
    <BR>

    <$STDFONT_B>Date Posted : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procdtpost_y\" VALUE=\"$procdtpost_y\"
     SIZE=5 MAXLENGTH=4> <B>-</B>
    <INPUT TYPE=TEXT NAME=\"procdtpost_m\" VALUE=\"$procdtpost_m\"
     SIZE=3 MAXLENGTH=2> <B>-</B>
    <INPUT TYPE=TEXT NAME=\"procdtpost_d\" VALUE=\"$procdtpost_d\"
     SIZE=3 MAXLENGTH=2>
    <BR>

    <$STDFONT_B>Date posted to responsible party : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procdtpostrep_y\" VALUE=\"$procdtpostrep_y\"
     SIZE=5 MAXLENGTH=4> <B>-</B>
    <INPUT TYPE=TEXT NAME=\"procdtpostrep_m\" VALUE=\"$procdtpostrep_m\"
     SIZE=3 MAXLENGTH=2> <B>-</B>
    <INPUT TYPE=TEXT NAME=\"procdtpostrep_d\" VALUE=\"$procdtpostrep_d\"
     SIZE=3 MAXLENGTH=2>
    <BR>

    <$STDFONT_B>Date Insurance Billed : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procdtinsbill_y\" VALUE=\"$procdtinsbill_y\"
     SIZE=5 MAXLENGTH=4> <B>-</B>
    <INPUT TYPE=TEXT NAME=\"procdtinsbill_m\" VALUE=\"$procdtinsbill_m\"
     SIZE=3 MAXLENGTH=2> <B>-</B>
    <INPUT TYPE=TEXT NAME=\"procdtinsbill_d\" VALUE=\"$procdtinsbill_d\"
     SIZE=3 MAXLENGTH=2>
    <BR>


    <$STDFONT_B>Date 2nd Insurance Billed : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procdtinsbill2_y\" VALUE=\"$procdtinsbill2_y\"
     SIZE=5 MAXLENGTH=4> <B>-</B>
    <INPUT TYPE=TEXT NAME=\"procdtinsbill2_m\" VALUE=\"$procdtinsbill2_m\"
     SIZE=3 MAXLENGTH=2> <B>-</B>
    <INPUT TYPE=TEXT NAME=\"procdtinsbill2_d\" VALUE=\"$procdtinsbill2_d\"
     SIZE=3 MAXLENGTH=2>
    <BR>

    <$STDFONT_B>Procedure Assignment : <$STDFONT_E>
     <SELECT NAME=\"procassign\">
      <OPTION VALUE=\"yes\"      >yes
      <OPTION VALUE=\"no\"       >no
      <OPTION VALUE=\"estimate\" >estimate
     </SELECT> 
    <BR>

    <$STDFONT_B>Taxed?<$STDFONT_E>
    <INPUT TYPE=CHECKBOX NAME=\"proctax\" CHECKED>
    <BR>

    <$STDFONT_B>Amount Approved (HMO fields) : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procaprov\" VALUE=\"$procaprov\"
     SIZE=10 MAXLENGTH=20>
    <BR>

    <$STDFONT_B>EMC Billing Flag : <$STDFONT_E>
    <SELECT NAME=\"procemc\">
      <OPTION VALUE=\"valid\"         >valid
      <OPTION VALUE=\"passed_prebill\">passed prebill
      <OPTION VALUE=\"no_emc\"        >no EMC
      <OPTION VALUE=\"emc_billed\"    >EMC billed
    </SELECT>
    <BR>

    <$STDFONT_B>Location Performed : <$STDFONT_E>
    <SELECT NAME=\"procloc\">
  ";

  freemed_display_facilities ($procloc);

  echo "
    </SELECT>
    <BR>

    <$STDFONT_B>Claim Number : <$STDFONT_E>
    <!-- <INPUT TYPE=TEXT NAME=\"procclmnum\"
     VALUE=\"$procclmnum\" SIZE=10 MAXLENGTH=15> -->
    <BR>
    <I>This is linked from the incident number.</I>
    <BR>

    <$STDFONT_B>Minutes of Anesthesia : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procmin\"
     VALUE=\"$procmin\" SIZE=10 MAXLENGTH=15>
    <BR>

    <$STDFONT_B>Treatment Plan : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procplan\"
     VALUE=\"$procplan\" SIZE=5 MAXLENGTH=10>
    <BR>
    <I>This will be a pull-down, selectable from the
    treatment plan db.</I>
    <BR>

    <$STDFONT_B>Charges : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procchg\" VALUE=\"$procchg\"
     SIZE=20 MAXLENGTH=32>
    <BR>

    <$STDFONT_B>Receipts : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procrcp\" VALUE=\"$procrpc\"
     SIZE=20 MAXLENGTH=32>
    <BR>

    <$STDFONT_B>Adjustments : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"procadj\" VALUE=\"$procadj\"
     SIZE=20 MAXLENGTH=32>
    <BR>

    <P>

    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" Add \">
    <INPUT TYPE=RESET  VALUE=\"Clear\">
    </CENTER></FORM>

    <P>
    <CENTER><A HREF=\"$page_name?$_auth&patient=$patient\"
     ><$STDFONT_B>Abort Addition<$STDFONT_E></A> |
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>Manage Patient<$STDFONT_E></A></CENTER>
     <P>
  ";
  freemed_display_box_bottom ();

} elseif ($action=="add") {

  # the actual add routine.
  # note that this is the first add routine written for
  # this package, so it may stink a little.

  freemed_display_box_top ("Adding Procedure", $_ref, $page_name);

  echo "
    <$STDFONT_B>Adding . . . 
  ";

  $procdtmod = $cur_date; // set current date for add

    // reassemble dates (19990722)
  $procdate = $procdate_y. "-". $procdate_m. "-". $procdate_d;
  $procdate2 = $procdate2_y. "-". $procdate2_m. "-". $procdate2_d;
  $procdtpost = $procdtpost_y. "-". $procdtpost_m. "-". $procdtpost_d;
  $procdtpostrep = $procdtpostrep_y. "-". $procdtpostrep_m.
                   "-". $procdtpostrep_d;
  $procdtinsbill = $procdtinsbill_y. "-". $procdtinsbill_m.
                   "-". $procdtinsbill_d;
  $procdtinsbill2 = $procdtinsbill2_y. "-". $procdtinsbill2_m.
                   "-". $procdtinsbill2_d;

   // fix fields with special characters
  $_proccom = addslashes ($proccom);

  $query = "INSERT INTO $database.proc VALUES ( ".
    "'$procacct',       ".
    "'$procacctdep',    ".
    "'$procincident',   ".
    "'$procdtmod',      ".
    "'$procchg',        ".
    "'$procrcp',        ".
    "'$procadj',        ".
    "'$proccode',       ".
    "'$procmod1',       ".
    "'$procmod2',       ".
    "'$procmod3',       ".
    "'$procdx1',        ".
    "'$procdx2',        ".
    "'$procdx3',        ".
    "'$procdx4',        ".
    "'$_proccom',       ".
    "'$procprov',       ".
    "'$procpos',        ".
    "'$proctos',        ".
    "'$procstatus',     ".
    "'$procdept',       ".
    "'$procvouch',      ".
    "'$procpriins',     ".
    "'$procsecins',     ".
    "'$procunits',      ".
    "'$proctype',       ".
    "'$procdate',       ". 
    "'$procdtpost',     ".
    "'$procdtpostrep',  ".
    "'$procdtinsbill',  ".
    "'$procdtinsbill2', ".
    "'$procassign',     ".
    "'$proctax',        ".
    "'$procaprov',      ".
    "'$procemc',        ".
    "'$procloc',        ".
    "'$procclmnum',     ".
    "'$procmin',        ".
    "'$procplan',       ".
    "  NULL ) ";

  $result = fdb_query($query);
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
      <B>done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>ERROR ($result)</B>\n"); 
  }

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth&patient=$patient\"
     ><$STDFONT_B>Procedures Menu<$STDFONT_E></A> |
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>Manage Patient<$STDFONT_E></A></CENTER>
     <P>
  ";

  freemed_display_box_bottom ();

  echo "
    <BR><BR>
    <CENTER>
     <A HREF=\"$page_name?$_auth\">Return
     to the Procedure Menu</A>
     <BR><BR>
     <A HREF=\"main.php3?$_auth\">Return to the
     Main Menu</A>
    </CENTER>
  ";

} elseif ($action=="modform") {

  freemed_display_box_top ("Modify Procedure", $_ref, $page_name);

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY someone!</B>
     </CENTER>

     <BR><BR>
    ";

    if ($debug==1) {
      echo "
        ID = [<B>$id</B>]
        <BR><BR>
      ";
    }

    freemed_display_box_bottom ();
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >Return to the Main Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  # if there _IS_ an ID tag presented, we must extract the record
  # from the database, and proverbially "fill in the blanks"

  $result = fdb_query("SELECT * FROM $database.proc ".
    "WHERE ( id = '$id' )");

  if ($debug==1) {
    echo " <B>RESULT</B> = [$result]<BR><BR> ";
  }

    # fdb_fetch_row shows array by index 0..n
    # fdb_fetch_array shows results by "fieldname"

  $r = fdb_fetch_array($result); // dump into array r[]

    # now comes the monotony
    # of horrendous repetition...
  $phylname    = $r["phylname"   ];
  $phyfname    = $r["phyfname"   ];


  switch ($phyref) {
    case "no":  $_pr1="SELECTED"; break;
    case "yes": $_pr2="SELECTED"; break;
    default:    $_pr0="SELECTED";
  } // this switch is to set the $phyref (not <INPUT>)

  echo "
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\">
    <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">

     INSERT MODIFY CODE HERE!!!


    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" Update \">
    <INPUT TYPE=RESET  VALUE=\"Clear\">
    </CENTER></FORM>
  ";
  freemed_display_box_bottom ();

} elseif ($action=="mod") {

  freemed_display_box_top ("Modifying Procedure", $_ref, $page_name);

  echo "
    <$STDFONT_B>Modifying . . . 
  ";

  $query = "UPDATE $database.proc SET ".
    "phylname   ='$phylname',    ".
    "phyrefcoll ='$phyrefcoll'   ". 
    "WHERE id='$id'";

     # this whole execute add section seems to be
     # screwed. I typed in the dump in MySQL and
     # it seems to work. I wonder why not from here
     # using root.

  $result = fdb_query($query);
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
      <B>done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>ERROR ($result)</B>\n"); 
  }

  freemed_display_box_bottom ();

  echo "
    <CENTER>
     <A HREF=\"main.php3?$_auth\">Return to the
     Main Menu</A>
    </CENTER>
  ";

} elseif ($action=="del") {

  freemed_display_box_top ("Deleting Procedure", $_ref, $page_name);

  $result = fdb_query("DELETE FROM $database.proc
    WHERE (id = \"$id\")");

  echo "
    <I><$STDFONT_B>Procedure $id deleted<$STDFONT_E></I>.
  ";
  if ($debug==1) {
    echo "
      <BR><B>RESULT:</B><BR>
      $result<BR><BR>
    ";
  }
  echo "
    <BR><BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=select\"
     >Delete Another</A></CENTER>
  ";

  freemed_display_box_bottom ();

  echo "
    <CENTER>
    <A HREF=\"main.php3?$_auth\">Return to the
    Main Menu</A></CENTER>
  ";

} else {  
  freemed_display_box_top ("Procedures", $_ref, $page_name);

  $p_lname = freemed_get_link_field ($patient, "patient", "ptlname");
  $p_fname = freemed_get_link_field ($patient, "patient", "ptfname");

  echo "
    <P><$STDFONT_B>
    <CENTER><B>Patient: $p_lname, $p_fname</B></CENTER><P>
  ";

  $query = "SELECT * FROM $database.proc WHERE procacct='$patient'
    ORDER BY procdate";
  $result = fdb_query ($query);
  if (fdb_num_rows ($result) < 1) {
    // if there are no procedures so far for this patient
    echo "
      <TABLE ALIGN=CENTER VALIGN=CENTER BORDER=0 CELLSPACING=0
       CELLPADDING=2 BGCOLOR=#000000 WIDTH=100%><TR BGCOLOR=#000000><TD>
       <$STDFONT_B COLOR=#ffffff>
         <CENTER><B>No procedures on file</B></CENTER><$STDFONT_E>
      </TD></TR></TABLE><P>
    ";
  } else {
    // if there are, loop and display
    echo "
      <TABLE ALIGN=CENTER BORDER=1 CELLSPACING=1 CELLPADDING=1
       VALIGN=CENTER BGCOLOR=#ffffff><TR BGCOLOR=#000000>
       <TD><$STDFONT_B COLOR=#ffffff>
         <CENTER><B>Procedures</B></CENTER><$STDFONT_E>
       </TD></TR>
    ";
    while ($r = fdb_fetch_array ($result) ) {
      $p_id = $r["id"        ];
      $p_dt = $r["procdate"];
      $p_cd = $r["proccode"];

      echo "
        <TR><TD><A HREF=\"$page_name?$_auth&patient=$patient&id=$p_id&action=modform\"
         ><$STDFONT_B>$p_dt - $p_cd<$STDFONT_E></A></TD></TR>
      ";
    } // end of while loop
     // end table
    echo "
      </TABLE><P>
    ";
  } // end of checking for past procedures...

  echo "
    <CENTER>
    <A HREF=\"$page_name?$_auth&patient=$patient&action=addform\"
     ><$STDFONT_B>Add Procedure<$STDFONT_E></A><BR>
    <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>Manage Patient<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom ();

  echo "
    <BR><BR>
    <CENTER>
    <A HREF=\"main.php3?$_auth\">Return to Main
     Menu</A>
    </CENTER>
  "; // close out with return to main menu tags
}

freemed_close_db (); // close database....

freemed_display_html_bottom (); // tail of document...

?>
