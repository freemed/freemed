<?php
 # file: progress_notes.php3
 # note: progress notes module for patient management
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL

 $record_name = "Progress Notes";
 $page_name   = "progress_notes.php3";
 $db_name     = "pnotes";
 include ("global.var.inc");
 include ("freemed-functions.inc");

 freemed_open_db ($LoginCookie); // authenticate
 freemed_display_html_top ();
 freemed_display_banner ();

 if ($patient<1) {
   freemed_display_box_top ("$record_name $Module :: $ERROR",
                            $page_name, $_ref);
   echo "
     <$HEADERFONT_B>$Must_Specify_A_Patient<$HEADERFONT_E>
   ";
   freemed_display_box_bottom ();
   freemed_close_db ();
   freemed_display_html_bottom ();
   DIE(""); // go on to a better place
 }

 switch ($action) { // master action switch
   case "addform":
     freemed_display_box_top ("$record_name $Entry", $page_name,
      "manage.php3?id=$patient");
     $pnotesdt     = $cur_date;

     $this_patient = new Patient ($patient);

     echo "
       <$HEADERFONT_B>$Patient: <A HREF=\"manage.php3?$_auth&id=$patient\"
         >".$this_patient->fullName(true)."</A><$HEADERFONT_E>
       <P>

       <!-- prototype for patient management bar -->

       <CENTER>
        <A HREF=\"manage.php3?$_auth&id=$patient\"
         ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
        <B>|</B>
        <A HREF=\"$page_name?$_auth&patient=$patient\"
         ><$STDFONT_B>$View_Modify $record_name<$STDFONT_E></A>
       </CENTER>

       <!-- end prototype -->

       <P>
       <FORM ACTION=\"$page_name\">
       <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"add\">
       <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">

       <$STDFONT_B>Related Episode(s) : <$STDFONT_B><BR>
     ";
     freemed_multiple_choice ("SELECT * FROM eoc WHERE ".
                              "eocpatient='$patient'",
                              "eocdescrip:eocstartdate:eocdtlastsimilar",
                              "pnoteseoc",
                              $pnoteseoc,
                              false);
     echo "
       <P>
       <$STDFONT_B>$Applicable_Date : <$STDFONT_E><BR>
     ";
     fm_date_entry("pnotesdt");
     echo "
       <P>

       <$STDFONT_B>$Subjective : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_S\" ROWS=8 COLS=45
         WRAP=VIRTUAL></TEXTAREA>
       <P>

       <$STDFONT_B>$Objective : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_O\" ROWS=8 COLS=45
         WRAP=VIRTUAL></TEXTAREA>
       <P>

       <$STDFONT_B>$Assessment : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_A\" ROWS=8 COLS=45
         WRAP=VIRTUAL></TEXTAREA>
       <P>

       <$STDFONT_B>$Plan : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_P\" ROWS=8 COLS=45
         WRAP=VIRTUAL></TEXTAREA>
       <P>

       <$STDFONT_B>$Interval : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_I\" ROWS=8 COLS=45
         WRAP=VIRTUAL></TEXTAREA>
       <P>

       <$STDFONT_B>$Education : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_E\" ROWS=8 COLS=45
         WRAP=VIRTUAL></TEXTAREA>
       <P>

       <$STDFONT_B>$Prescription : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_R\" ROWS=8 COLS=45
         WRAP=VIRTUAL></TEXTAREA>
       <P>

       <CENTER>
       <INPUT TYPE=SUBMIT VALUE=\"  $Add  \">
       <INPUT TYPE=RESET  VALUE=\" $Clear \">
       </CENTER>
       </FORM>

       <CENTER>
        <A HREF=\"$_ref?$_auth&patient=$patient\"
         ><$STDFONT_B>$Abort_Addition<$STDFONT_E></A>
       </CENTER>
     ";
     freemed_display_box_bottom ();
     break;

   case "add":
     freemed_display_box_top ("$Adding $record_name", $page_name, 
       "manage.php3?id=$patient");
     echo "
       <$STDFONT_B><B>$Adding . . . </B>
     ";
       // preparation of values
     $pnotesdtadd = $cur_date;
     $pnotesdtmod = $cur_date;
     $pnotesdt  = fm_date_assemble("pnotesdt");
     $pnotespat = $patient;
     $pnoteseoc_blob = addslashes (fm_join_from_array ($pnoteseoc));

       // remove the 's, etc from the blobs
     $pnotes_S_blob = addslashes ($pnotes_S);
     $pnotes_O_blob = addslashes ($pnotes_O);
     $pnotes_A_blob = addslashes ($pnotes_A);
     $pnotes_P_blob = addslashes ($pnotes_P);
     $pnotes_I_blob = addslashes ($pnotes_I);
     $pnotes_E_blob = addslashes ($pnotes_E);
     $pnotes_R_blob = addslashes ($pnotes_R);

       // actual addition
     $query = "INSERT INTO pnotes VALUES (
       '$pnotesdt',
       '$pnotesdtadd',
       '$pnotesdtmod',
       '$pnotespat',
       '$pnoteseoc_blob',
       '$pnotes_S_blob',
       '$pnotes_O_blob',
       '$pnotes_A_blob',
       '$pnotes_P_blob',
       '$pnotes_I_blob',
       '$pnotes_E_blob',
       '$pnotes_R_blob',
       '$__ISO_SET__',
       NULL ) "; // actual add query
     $result = fdb_query ($query);
     if ($debug1) echo "(query = '$query') ";
     if ($result)
       echo " <B> $Done. </B><$STDFONT_E>\n";
     else
       echo " <B> <FONT COLOR=#ff0000>$FAILED</FONT> </B><$STDFONT_E>\n";
     echo "
       <BR><BR>
       <CENTER><A HREF=\"manage.php3?$_auth&id=$patient\"
        ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
       <B>|</B>
       <A HREF=\"$page_name?$_auth&patient=$patient\"
        ><$STDFONT_B>$record_name<$STDFONT_E></A>
       </CENTER>
       <BR>
     ";
     freemed_display_box_bottom ();
     break;

   case "modform":
     if (($id<1) OR (strlen($id)<1)) {
       freemed_display_box_top ("$record_name :: $ERROR", $page_name, 
         "$page_name?patient=$patient");
       echo "
         <$HEADERFONT_B>$Must_Call_With_Patient_ID<$HEADERFONT_E>
       ";
       freemed_display_box_bottom ();
       DIE("");
     }

     // get data first...
     $r = freemed_get_link_rec ($id, "pnotes");

     $pnotesdt   = $r["pnotesdt"];
     
     $patient  = $r["pnotespat"];
     //   // use link_rec instead of multiple fields 19990910
     // $patient_rec = freemed_get_link_rec ($patient, "patient");
     // $ptlname    = $patient_rec["ptlname"];
     // $ptfname    = $patient_rec["ptfname"];
     // $ptmname    = $patient_rec["ptmname"];
     // $ptdob      = $patient_rec["ptdob"  ];
       // use patient class instead of doofy way 19991217
     $this_patient  = new Patient ($patient);

     freemed_display_box_top ("$Modify $record_name", $_ref, $_ref);
     echo "
       <P>
       <$HEADERFONT_B>Patient : <A HREF=\"manage.php3?$_auth&id=$patient\"
         >".$this_patient->fullName(true)."</A><$HEADERFONT_E>
       <P>

       <!-- prototype for patient management bar -->

       <CENTER>
        <A HREF=\"manage.php3?$_auth&id=$patient\"
         ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
        <B>|</B>
        <A HREF=\"$page_name?$_auth&$patient=$patient&action=addform\"
         ><$STDFONT_B>$Add $record_name<$STDFONT_E></A>
       </CENTER>

       <!-- end prototype -->

       <P>
       <FORM ACTION=\"$page_name\" METHOD=POST>
       <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"mod\"     >
       <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
       <INPUT TYPE=HIDDEN NAME=\"id\"      VALUE=\"$id\"     >

       <$STDFONT_B>Related Episode(s) : <$STDFONT_E><BR>
     ";
     freemed_multiple_choice ("SELECT * FROM eoc WHERE ".
                              "eocpatient='$patient'",
                              "eocdescrip:eocdtlastsimilar",
                              "pnoteseoc",
                              $r[pnoteseoc]);
     echo "
       <P>
       <$STDFONT_B>$Applicable_Date : <$STDFONT_E><BR>
     ";
     fm_date_entry("pnotesdt");
     echo "
       <P>

       <$STDFONT_B>$Subjective : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_S\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".fm_prep($r[pnotes_S])."</TEXTAREA>
       <P>

       <$STDFONT_B>$Objective : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_O\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".fm_prep($r[pnotes_O])."</TEXTAREA>
       <P>

       <$STDFONT_B>$Assessment : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_A\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".fm_prep($r[pnotes_A])."</TEXTAREA>
       <P>

       <$STDFONT_B>$Plan : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_P\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".fm_prep($r[pnotes_P])."</TEXTAREA>
       <P>

       <$STDFONT_B>$Interval : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_I\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".fm_prep($r[pnotes_I])."</TEXTAREA>
       <P>

       <$STDFONT_B>$Education : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_E\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".fm_prep($r[pnotes_E])."</TEXTAREA>
       <P>

       <$STDFONT_B>$Prescription : <$STDFONT_E><BR>
        <TEXTAREA NAME=\"pnotes_R\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".fm_prep($r[pnotes_R])."</TEXTAREA>
       <P>

       <CENTER>
        <INPUT TYPE=SUBMIT VALUE=\" $Change \">
        <INPUT TYPE=RESET  VALUE=\" $Restore \">
       </CENTER>
       </FORM>
       <P>
       <CENTER><A HREF=\"manage.php3?$_auth&id=$patient\"
        ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
       <B>|</B>
       <A HREF=\"$page_name?$_auth&action=addform&patient=$patient\"
        ><$STDFONT_B>$Add $record_name<$STDFONT_E></A>
       </CENTER>
       <P>
     ";
     freemed_display_box_bottom ();
     break;

   case "mod":
     freemed_display_box_top ("$Modifying $record_name", $_ref, $_ref);
     echo "
       <B><$STDFONT_B>$Modifying . . . <$STDFONT_E></B>
     ";
     //$pnotesdt = $pnotesdt_y ."-". $pnotesdt_m ."-". $pnotesdt_d;
     $pnotesdt  = fm_date_assemble("pnotesdt");
     $pnoteseoc_blob = addslashes (fm_join_from_array ($pnoteseoc));
     $pnotes_S_ = addslashes ($pnotes_S);
     $pnotes_O_ = addslashes ($pnotes_O);
     $pnotes_A_ = addslashes ($pnotes_A);
     $pnotes_P_ = addslashes ($pnotes_P);
     $pnotes_I_ = addslashes ($pnotes_I);
     $pnotes_E_ = addslashes ($pnotes_E);
     $pnotes_R_ = addslashes ($pnotes_R);
     $query = "UPDATE pnotes SET
       pnotespat      = '$patient',
       pnoteseoc      = '$pnoteseoc_blob',
       pnotesdt       = '$pnotesdt',
       pnotesdtmod    = '$cur_date',
       pnotes_S       = '$pnotes_S_',
       pnotes_O       = '$pnotes_O_',
       pnotes_A       = '$pnotes_A_',
       pnotes_P       = '$pnotes_P_',
       pnotes_I       = '$pnotes_I_',
       pnotes_E       = '$pnotes_E_',
       pnotes_R       = '$pnotes_R_',
       iso            = '$__ISO_SET__'
       WHERE id='$id'";
     $result = fdb_query ($query);
     if ($debug==1) echo "query = \"$query\", result = \"$result\"<BR>\n";
     if ($result) echo "<B><$STDFONT_B>$Done.<$STDFONT_E></B>\n";
      else echo "<B><$STDFONT_B>$FAILED<$STDFONT_E></B>\n";
     echo "
       <P>
       <CENTER>
        <A HREF=\"manage.php3?$_auth&id=$patient\"
         ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
        <B>|</B>
        <A HREF=\"$page_name?$_auth&patient=$patient\"
         ><$STDFONT_B>$View_Modify $record_name<$STDFONT_E></A>
        <BR>
        <A HREF=\"$page_name?$_auth&patient=$patient&action=addform\"
         ><$STDFONT_B>$Add $record_name<$STDFONT_E></A>
       </CENTER>
     ";
     freemed_display_box_bottom ();
     break;

   case "display":
     if (($id<1) OR (strlen($id)<1)) {
       freemed_display_box_top ("$record_name $View :: $ERROR", $page_name);
       echo "
         <$HEADERFONT_B>$Specify_Notes_to_Display<$HEADERFONT_E>
         <P>
         <CENTER><A HREF=\"$page_name?$_auth&patient=$patient\"
          ><$STDFONT_B>$record_name $Menu<$STDFONT_E></A> |
          <A HREF=\"manage.php3?$_auth&id=$patient\"
          ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
         </CENTER>
       ";
       freemed_display_box_bottom ();
       freemed_display_html_bottom ();
     }
      // if it is legit, grab the data
     $r = freemed_get_link_rec ($id, "pnotes");
     $pnotesdt = $r ["pnotesdt"];
     $pnotesdt_formatted = substr ($pnotesdt, 0, 4). "-".
                           substr ($pnotesdt, 5, 2). "-".
                           substr ($pnotesdt, 8, 2);
     $pnotes_S = htmlentities ( $r ["pnotes_S"] );
     $pnotes_O = htmlentities ( $r ["pnotes_O"] );
     $pnotes_A = htmlentities ( $r ["pnotes_A"] );
     $pnotes_P = htmlentities ( $r ["pnotes_P"] );
     $pnotes_I = htmlentities ( $r ["pnotes_I"] );
     $pnotes_E = htmlentities ( $r ["pnotes_E"] );
     $pnotes_R = htmlentities ( $r ["pnotes_R"] );
     $pnotespat = $r ["pnotespat"];
     $pnoteseoc = fm_split_into_array ($r["pnoteseoc"]);

     $this_patient = new Patient ($pnotespat);  // 19991217

     $pnotesdtadd = htmlentities ( $r ["pnotesdtadd"] );
     $pnotesdtmod = htmlentities ( $r ["pnotesdtmod"] );
     freemed_display_box_top ("$record_name View");
     if (freemed_get_userlevel($LoginCookie)>$database_level)
       $__MODIFY__ = " |
         <A HREF=\"$page_name?$_auth&patient=$patient&id=$id&action=modform\"
          ><$STDFONT_B>$Modify_Notes<$STDFONT_E></A>
       "; // add this if they have modify privledges
     echo "
       <P>
       <CENTER><A HREF=\"$page_name?$_auth&patient=$pnotespat\"
        ><$STDFONT_B>$record_name $Menu<$STDFONT_E></A> |
        <A HREF=\"manage.php3?$_auth&id=$pnotespat\"
        ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A> $__MODIFY__
       </CENTER>
       <P>

       <$HEADERFONT_B>$Patient : <A HREF=\"manage.php3?$_auth&id=$patient\"
         >".$this_patient->fullName(true)."</A><$HEADERFONT_E>
       <BR>
       <CENTER>
        <$STDFONT_B>
        <B>Relevant Date : </B>
         $pnotesdt_formatted
        <$STDFONT_E>
       </CENTER>
       <P>
     ";
     if (count($pnoteseoc)>0) {
      echo "
       <CENTER>
        <$STDFONT_B><B>Related Episode(s)</B><$STDFONT_E>
        <BR>
      ";
      for ($i=0;$i<count($pnoteseoc);$i++) {
        $e_r     = freemed_get_link_rec ($pnoteseoc[$i]+0, "eoc"); 
        $e_id    = $e_r["id"];
        $e_desc  = $e_r["eocdescrip"];
        $e_first = $e_r["eocstartdate"];
        $e_last  = $e_r["eocdtlastsimilar"];
        echo "
         <A HREF=\"episode_of_care.php3?$_auth&patient=$patient&action=manage".
         "&id=$e_id\"
         ><$STDFONT_B>$e_desc / $e_first to $e_last<$STDFONT_E></A><BR>
        ";
      } // end looping for all EOCs
      echo "
       </CENTER>
      ";
     } // end checking for EOC stuff

     if (!empty($pnotes_S)) echo "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>$Subjective</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           $pnotes_S
         <$STDFONT_E>
       </TD></TR></TABLE>
       ";
      if (!empty($pnotes_O)) echo "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>$Objective</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           $pnotes_O
         <$STDFONT_E>
       </TD></TR></TABLE>
       ";
      if (!empty($pnotes_A)) echo "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>$Assessment</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           $pnotes_A
         <$STDFONT_E>
       </TD></TR></TABLE>
       ";
      if (!empty($pnotes_P)) echo "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>$Plan</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           $pnotes_P
         <$STDFONT_E>
       </TD></TR></TABLE>
       ";
      if (!empty($pnotes_I)) echo "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>$Interval</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           $pnotes_I
         <$STDFONT_E>
       </TD></TR></TABLE>
       ";
      if (!empty($pnotes_E)) echo "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>$Education</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           $pnotes_E
         <$STDFONT_E>
       </TD></TR></TABLE> 
       ";
      if (!empty($pnotes_R)) echo "
      <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>$Prescription</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           $pnotes_R
         <$STDFONT_E>
       </TD></TR></TABLE>
      ";
        // back to your regularly sceduled program...
      echo "
       <P>
       <CENTER><A HREF=\"$page_name?$_auth&patient=$pnotespat\"
        ><$STDFONT_B>$record_name $Menu<$STDFONT_E></A> |
        <A HREF=\"manage.php3?$_auth&id=$pnotespat\"
        ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A> $__MODIFY__
       </CENTER>
       <P>
     ";

     freemed_display_box_bottom ();
     break;
   default:
     // in case of emergency, break glass -- default shows all things from
     // specified patient...

     $query = "SELECT * FROM pnotes WHERE (pnotespat='$patient')
        ORDER BY pnotesdt";
     $result = fdb_query ($query);
     $rows = fdb_num_rows ($result);

     $this_patient = new Patient ($patient);
     
     if ($rows < 1) {
       freemed_display_box_top ("$record_name", $page_name, $_ref);
       echo "
         <P>
         <CENTER>
          <$STDFONT_B>
          <B>$Patient</B> :
           <A HREF=\"manage.php3?$_auth&id=$patient\"
            >".$this_patient->fullName(true)."</A>
          <$STDFONT_E>
         </CENTER>
         <P>
         <CENTER>
         <$STDFONT_B>$This_Patient_Has_No_Notes<$STDFONT_E>
         </CENTER>
         <P>
         <CENTER>
         <A HREF=\"$page_name?$_auth&action=addform&patient=$patient\"
          ><$STDFONT_B>$Add $record_name<$STDFONT_E></A>
         <B>|</B>
         <A HREF=\"manage.php3?$_auth&id=$patient\"
          ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
         </CENTER>
         <P>
       ";
       freemed_display_box_bottom ();
       freemed_close_db ();
       freemed_display_html_bottom ();
       DIE("");
     } // if there are none...

       // or else, display them...
     freemed_display_box_top ("$record_name",
      "manage.php3?id=$patient", $page_name);
     $this_patient = new Patient ($patient);
     echo "
       <P>
       <B>$Patient</B> : <A HREF=\"manage.php3?$_auth&id=$patient\"
         >".$this_patient->fullName(true)."</A>
       <P>
       <$HEADERFONT_B>$Existing $record_name<$HEADERFONT_E>
       <P>
       <TABLE BORDER=2 CELLSPACING=2 CELLPADDING=1 BGCOLOR=#ffffff
        ALIGN=CENTER VALIGN=CENTER>
     ";
     while ($r = fdb_fetch_array ($result)) {
       $pnotesdt   = $r["pnotesdt"];
       $pnotesdesc = $r["pnotesdesc"];
       $id         = $r["id"      ];
       if (empty($pnotesdesc)) $pnotesdesc="NO DESCRIPTION";
       echo "
         <TR><TD BGCOLOR=#ffffff>
         <A HREF=\"$page_name?$_auth&action=display&patient=$patient&id=$id\"
          ><$STDFONT_B>$pnotesdt / $pnotesdesc<$STDFONT_E></A>
       ";
       if (freemed_get_userlevel($LoginCookie)>$database_level)
         echo "
           &nbsp;
           <A HREF=\"$page_name?$_auth&action=modform&patient=$patient&id=$id\"
            >$MOD</A>
         ";
       if (freemed_get_userlevel($LoginCookie)>$delete_level)
         echo "
           &nbsp;
           <A HREF=\"$page_name?$_auth&action=del&patient=$patient&id=$id\"
            >$DEL</A>
         ";
       echo "
         </TD></TR>
       ";
     } // end master while fetch loop
     echo "
       </TABLE>
       <P>
       <CENTER>
        <A HREF=\"manage.php3?$_auth&id=$patient\"
         ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
       <B>|</B>
       <A HREF=\"$page_name?$_auth&action=addform&patient=$patient\"
        ><$STDFONT_B>$Add $record_name<$STDFONT_E></A>
       </CENTER>
       <P>
     ";
     freemed_display_box_bottom ();
     break;
 } // end master action switch

 freemed_close_db ();
 freemed_display_html_bottom ();

?>
