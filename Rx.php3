<?php
  # file: Rx.php3
  # note: prescription db/module functions
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL

  $page_name = "Rx.php3";
  $record_name = "Prescription";
  include ("lib/freemed.php");
  include ("lib/API.php");

  // note: for whoever wants to do this module -- prescription info depends
  // on drug info in a separate db, which hasn't been done yet. the display
  // action shows it in the browser window for printout. other than that,
  // you're on your own...                                           -jb-

  freemed_open_db ($LoginCookie); // authenticate
  freemed_display_html_top ();

  if ($action != "display")       // check if showing prescription... 
    freemed_display_banner ();

  // check to see if chained from a patient module...
  if ((strlen($patient)<1) OR ($patient < 1)) {
    freemed_display_box_top ("$record_name $Module :: $ERROR", $_ref);
    echo "
      <$HEADERFONT_B>
       $Must_Have_Patient_For_Prescriptions
      <$HEADERFONT_E>
    ";
    freemed_display_box_bottom ();
    freemed_display_bottom_links ($record_name, $page_name, $_ref);
    freemed_close_db ();
    freemed_display_html_bottom ();
    DIE ("");
  }

  // 19990924 -- check access for patient
  if (!freemed_check_access_for_patient($LoginCookie, $patient)) {
    freemed_display_box_top ("$record_name $Module :: $ERROR", $_ref);
    echo "
      <$HEADERFONT_B>
        $No_Access_To_This_Patient
      <$HEADERFONT_E>
    ";
    freemed_display_box_bottom ();
    freemed_display_bottom_links ();
    freemed_close_db ();
    freemed_display_html_bottom ();
    DIE ("");
  }

  switch ($action) { // master action switch
    case "display":
      freemed_display_box_top ("$record_name $Display");
      echo "
        <P>
        This function has NOT been implemented yet.<BR>
        Please wait and do not flame me yet -jeff
        <P>
      ";
      freemed_display_box_bottom ();
      break;
    case "addform":
      freemed_display_box_top ("$Add $record_name", $_ref, $page_name);
      $ptlname = freemed_get_link_field ($patient, "patient", "ptlname");
      $ptfname = freemed_get_link_field ($patient, "patient", "ptfname");
      $rxdtfrom = $cur_date;
      echo "
        <FORM ACTION=\"$page_name\" METHOD=POST>
        <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
        <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"add\">

        <P>
        <CENTER>
         <$STDFONT_B><B>$Patient : $ptlname, $ptfname</B><$STDFONT_E>
         <INPUT TYPE=HIDDEN NAME=\"rxpatient\" VALUE=\"$patient\">
        </CENTER>
        <P>

        <$STDFONT_B>$Drug : <$STDFONT_E>
      ";

      $rx_r = fdb_query("SELECT * FROM frmlry ORDER BY trdmrkname");
      echo freemed_display_selectbox (
        $rx_r, "#trdmrkname#", "rxdrug"
      )."

        <P>

        <$STDFONT_B>$Dosage : <$STDFONT_E>
        <INPUT TYPE=TEXT NAME=\"rxdosage\" VALUE=\"$rxdosage\"
         SIZE=20 MAXLENGTH=100>
        <P>

        <$STDFONT_B>$Starting_Date : <$STDFONT_E>
     ".fm_date_entry("rxdtfrom")."
        <P>

        <$STDFONT_B>$Duration ($In_Days, $Infinite) : <$STDFONT_E>
        <INPUT TYPE=TEXT NAME=\"rxduration\" VALUE=\"$rxduration\"
         SIZE=5 MAXLENGTH=5>
        <P>

        <$STDFONT_B>$Refills : <$STDFONT_E>
        <INPUT TYPE=TEXT NAME=\"rxrefills\" VALUE=\"$rxrefills\"
         SIZE=5 MAXLENGTH=4>
        <P>

        <$STDFONT_B>$Substitution : <$STDFONT_E>
        <SELECT NAME=\"rxsubstitute\">
         <OPTION VALUE=\"may not subsitute\">$May_Not_Substitute
         <OPTION VALUE=\"may substitute\"   >$May_Substitute
        </SELECT>
        <P>

        <CENTER>
        <INPUT TYPE=SUBMIT VALUE=\" $Add \">
        <INPUT TYPE=RESET  VALUE=\" $Clear \">
        </CENTER>
        </FORM>
      ";
      freemed_display_box_bottom ();
      break;
    case "add":
      freemed_display_box_top ("$Adding $record_name", $_ref, $page_name);
      echo "
        <P><$STDFONT_B><B>$Adding . . . </B><$STDFONT_E>
      ";
      $rxdtadd = $cur_date;
      //$rxdtfrom = $rxdtfrom_y. "-". $rxdtfrom_m. "-". $rxdtfrom_d;
      $rxdtfrom = fm_date_assemble("rxdtfrom");
      $query = "INSERT INTO rx VALUES (
        '$rxdtadd',
        '$rxdtmod',
        '$rxpatient',
        '$rxdtfrom',
        '$rxduration',
        '$rxdrug',
        '$rxdosage',
        '$rxrefills',
        '$rxsubstitute',
        '$rxmd5sum',
        NULL ) ";
      $result = fdb_query ($query);
      if ($debug==1)
        echo "
          <BR>query = \"$query\", result = \"$result\"<BR>
        ";
      if ($result) echo "\n<$STDFONT_B><B>$Done.</B><$STDFONT_E>\n";
       else echo "\n<$STDFONT_B><B>$ERROR</B><$STDFONT_E>\n";
      echo "
        <P>
        <CENTER>
        <A HREF=\"$page_name?$_auth&patient=$patient\"
         ><$STDFONT_B>$Manage_Prescriptions<$STDFONT_E></A> |
        <A HREF=\"manage.php?$_auth&id=$patient\"
         ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
        </CENTER>
        <P>
      ";
      freemed_display_box_bottom ();
      break;
    default:
      freemed_display_box_top ("$record_name", $_ref, $page_name);
      $ptlname = freemed_get_link_field ($patient, "patient", "ptlname");
      $ptfname = freemed_get_link_field ($patient, "patient", "ptfname");
      echo "
        <P>
         <CENTER>
          <$STDFONT_B><B>$Patient: $ptlname, $ptfname</B><$STDFONT_E>
         </CENTER>
        <P>
        <CENTER>
         <A HREF=\"$page_name?$_auth&patient=$patient&action=addform\"
         ><$STDFONT_B>$Add $record_name<$STDFONT_E></A> |
         <A HREF=\"manage.php?$_auth&id=$patient\"
         ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
        </CENTER>
        <P>
      ";

      $query = fdb_query ("SELECT * FROM 
         rx WHERE rxpatient='$patient'");
      $num_records = fdb_num_rows ($query);

      if ($num_records < 1) {
        // if there are no prescriptions yet
        echo "
          <TABLE WIDTH=100% BORDER=0 VALIGN=CENTER ALIGN=CENTER
           CELLSPACING=1 CELLPADDING=1 BGCOLOR=#000000><TR>
          <TD BGCOLOR=#000000><CENTER><$STDFONT_B COLOR=#ffffff>
          <B>No prescriptions for this patient</B></CENTER>
          </TD></TR></TABLE>
        ";
      } else {
        // or else, show them
        echo "
          <TABLE BORDER=1 CELLSPACING=1 CELLPADDING=1 ALIGN=CENTER
           BGCOLOR=#ffffff VALIGN=CENTER>
        "; // table header
        while ( $r = fdb_fetch_array ($query) ) {
          $rxdtfrom     = $r ["rxdtfrom"    ];
          $rxduration   = $r ["rxduration"  ];
          $rxdrug       = $r ["rxdrug"      ];
          $drug = freemed_get_link_field ($rxdrug, "frmlry", "trdmrkname");
          $id           = $r ["id"          ];
          $rxdtto       = $rxdtfrom;  // set to starting date
          if ($rxduration > 0) 
            for ($i=1; $i<$rxduration; $i++) 
              $rxdtto = freemed_get_date_next ($rxdtto); // increment date
          else
            $rxdtto = "unspecified";
          echo "
            <TR><TD>
             <A HREF=\"$page_name?$_auth&patient=$patient&id=$id&action=display\"
              ><$STDFONT_B>".fm_date_print($rxdtfrom)." / 
                           ".fm_date_print($rxdtto)." <$STDFONT_E></A>
              <B>[</B> <A HREF=
               \"frmlry.php3?$_auth&id=$rxdrug&action=modform\"
               ><$STDFONT_B><I>$drug</I><$STDFONT_E></A> <B>]</B>
            </TR></TD>
          "; 
        } // end while (WEND legacy code ??)
        echo "
          </TABLE>
        "; // end table
      }
      
      echo "
        <P>
        <CENTER>
         <A HREF=\"$page_name?$_auth&patient=$patient&action=addform\"
         ><$STDFONT_B>$Add $record_name<$STDFONT_E></A> |
         <A HREF=\"manage.php?$_auth&id=$patient\"
         ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
        </CENTER>
        <P>
      ";
      freemed_display_box_bottom ();
      break;
  } // end master action switch

  freemed_close_db ();
  freemed_display_html_bottom ();
?>
