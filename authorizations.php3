<?php
 # file: progress_notes.php3
 # note: patient authorizations module
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

 $record_name = "Authorizations";
 $page_name   = "authorizations.php3";
 $db_name     = "authorizations";
 include ("global.var.inc");
 include ("freemed-functions.inc");

 freemed_open_db ($LoginCookie); // authenticate
 freemed_display_html_top ();
 freemed_display_banner ();

 if ($patient<1) {
   freemed_display_box_top ("$record_name $Module :: $ERROR",
                            $page_name, $_ref);
   echo "
     <$HEADERFONT_B>You must specify a patient!<$HEADERFONT_E>
   ";
   freemed_display_box_bottom ();
   freemed_close_db ();
   freemed_display_html_bottom ();
   DIE(""); // go on to a better place
 }

 switch ($action) { // master action switch
   case "addform":
   case "modform":
     switch ($action) { // internal action switch
      case "addform":
       $this_action = "$Add";
       $next_action = "add";
       break; // end internal addform
      case "modform":
       $this_action = "$Modify";
       $next_action = "mod";

       if (($patient<1) OR (empty($patient))) {
         freemed_display_box_top ("$record_name :: $ERROR", $page_name, 
           "$page_name?patient=$patient");
         echo "
           <$HEADERFONT_B>You must call this with a patient!<$HEADERFONT_E>
         ";
         freemed_display_box_bottom ();
         DIE("");
       }
       $r                 = freemed_get_link_rec ($id, $db_name);
       $authdtbegin       = $r[authdtbegin];
       $authdtend         = $r[authdtend];
       $authtype          = $r[authtype];
       $authnum           = $r[authnum];
       $authprov          = $r[authprov];
       $authprovid        = $r[authprovid];
       $authinsco         = $r[authinsco]; 
       $authvisits        = $r[authvisits];
       $authcomment       = $r[authcomment];
       break; // end internal modform
     } // end internal action switch
     freemed_display_box_top ("$this_action $record_name", $page_name,
      "manage.php3?id=$patient");
     $pnotesdt     = $cur_date;

     $this_patient = new Patient ($patient);

     echo "
       <P>
       <$HEADERFONT_B>$Patient: <A HREF=\"manage.php3?$_auth&id=$patient\"
         >".$this_patient->fullName(true)."</A><$HEADERFONT_E>
       <P>

       <FORM ACTION=\"$page_name\" METHOD=POST>
       <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"$next_action\">
       <INPUT TYPE=HIDDEN NAME=\"id\"      VALUE=\"$id\">
       <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">

       <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3
        VALIGN=MIDDLE ALIGN=CENTER>
        
       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>Starting Date : <$STDFONT_E><BR>
        </TD>
        <TD ALIGN=LEFT>
     ";
     fm_date_entry("authdtbegin");
     echo "
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>Ending Date : <$STDFONT_E><BR>
        </TD>
        <TD ALIGN=LEFT>
     ";
     fm_date_entry("authdtend");
     echo "
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>Authorization Number : <$STDFONT_E><BR>
        </TD>
        <TD ALIGN=LEFT>
         <INPUT TYPE=TEXT NAME=\"authnum\" SIZE=30
          MAXLENGTH=25 VALUE=\"".fm_prep($authnum)."\">
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>Authorization Type : <$STDFONT_E><BR>
        </TD>
        <TD ALIGN=LEFT>
         <SELECT NAME=\"authtype\">
          <OPTION VALUE=\"0\" ".
          ( ($authtype <  1) ? "SELECTED" : "" ).">NONE SELECTED
          <OPTION VALUE=\"1\" ".
          ( ($authtype == 1) ? "SELECTED" : "" ).">physician
          <OPTION VALUE=\"2\" ".
          ( ($authtype == 2) ? "SELECTED" : "" ).">insurance company
          <OPTION VALUE=\"3\" ".
          ( ($authtype == 3) ? "SELECTED" : "" )."
           >certificate of medical neccessity
          <OPTION VALUE=\"4\" ".
          ( ($authtype == 4) ? "SELECTED" : "" ).">surgical
          <OPTION VALUE=\"5\" ".
          ( ($authtype == 5) ? "SELECTED" : "" ).">worker's compensation
          <OPTION VALUE=\"6\" ".
          ( ($authtype == 6) ? "SELECTED" : "" ).">consulation
         </SELECT>
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>Authorizing Provider : <$STDFONT_E><BR>
        </TD>
        <TD ALIGN=LEFT>
         <SELECT NAME=\"authprov\">
     ";
     freemed_display_physicians ($authprov);
     echo "
         </SELECT>
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>Physician Identifier : <$STDFONT_E><BR>
        </TD>
        <TD ALIGN=LEFT>
         <INPUT TYPE=TEXT NAME=\"authprovid\" SIZE=20 MAXLENGTH=15
          VALUE=\"".fm_prep($authprovid)."\">
         </SELECT>
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>Authorizing Insurance Company : <$STDFONT_E><BR>
        </TD>
        <TD ALIGN=LEFT>
         <SELECT NAME=\"authinsco\">
     ";
     freemed_display_insco ($authinsco);
     echo "
         </SELECT>
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>Number of Visits : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
     ";
     fm_number_select ("authvisits", 0, 100);
     echo "
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>Comment : <$STDFONT_E><BR>
        </TD>
        <TD ALIGN=LEFT>
         <INPUT TYPE=TEXT NAME=\"authcomment\" SIZE=30 MAXLENGTH=100
          VALUE=\"".fm_prep($authcomment)."\">
        </TD>
       </TR>
 
       </TABLE>

       <CENTER>
       <INPUT TYPE=SUBMIT VALUE=\"  $this_action  \">
       <INPUT TYPE=RESET  VALUE=\" $Clear \">
       </CENTER>
       </FORM>

       <CENTER>
        <A HREF=\"$page_name?$_auth&patient=$patient\"
         ><$STDFONT_B>Abort $this_action<$STDFONT_E></A>
       </CENTER>
     ";
     freemed_display_box_bottom ();
     break;

   case "add":
     freemed_display_box_top ("$Adding $record_name", $page_name, 
       "manage.php3?id=$patient");
     echo "
       <CENTER><$STDFONT_B><B>$Adding . . . </B>
     ";

       // actual addition
     $query = "INSERT INTO $db_name VALUES (
       '$cur_date',
       '0000-00-00',
       '".addslashes($patient)             ."',
       '".fm_date_assemble("authdtbegin")  ."',
       '".fm_date_assemble("authdtend")    ."',
       '".addslashes($authnum)             ."',
       '".addslashes($authtype)            ."',
       '".addslashes($authprov)            ."',
       '".addslashes($authprovid)          ."',
       '".addslashes($authinsco)           ."',
       '".addslashes($authvisits)          ."',
       '0',
       '0',
       '".addslashes($authcomment)         ."',
       NULL ) "; // actual add query
     $result = fdb_query ($query);
     if ($debug1) echo "(query = '$query') ";
     if ($result)
       echo " <B> $Done. </B><$STDFONT_E></CENTER>\n";
     else
       echo " <B> <FONT COLOR=#ff0000>$ERROR</FONT> </B><$STDFONT_E></CENTER>\n";
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

   case "mod":
     freemed_display_box_top ("$Modifying $record_name", $_ref, $_ref);
     echo "<B><$STDFONT_B>$Modifying . . . <$STDFONT_E></B>\n";
     $query = "UPDATE $db_name SET
       authdtmod      = '$cur_date',
       authdtbegin    = '".fm_date_assemble("authdtbegin")."',
       authdtend      = '".fm_date_assemble("authdtend")  ."',
       authnum        = '".addslashes($authnum)           ."',
       authtype       = '".addslashes($authtype)          ."',
       authprov       = '".addslashes($authprov)          ."',
       authprovid     = '".addslashes($authprovid)        ."',
       authinsco      = '".addslashes($authinsco)         ."',
       authvisits     = '".addslashes($authvisits)        ."',
       authcomment    = '".addslashes($authcomment)       ."'
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

   default:
     // in case of emergency, break glass -- default shows all things from
     // specified patient...

     $query = "SELECT * FROM $db_name WHERE (authpatient='$patient')
        ORDER BY authdtbegin,authdtend";
     $result = fdb_query ($query);
     $rows = ( ($result > 0) ? fdb_num_rows ($result) : 0 );

     $this_patient = new Patient ($patient);
     
     if ($rows < 1) {
       freemed_display_box_top ("$record_name", $page_name, $_ref);
       echo "
         <P>
         <CENTER>
          <$STDFONT_B>
          $Patient :
           <A HREF=\"manage.php3?$_auth&id=$patient\"
            >".$this_patient->fullName(true)."</A>
          <$STDFONT_E>
         </CENTER>
         <P>
         <CENTER>
         <$STDFONT_B>This patient has no authorizations.<$STDFONT_E>
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
       <CENTER>
       <$STDFONT_B>
       $Patient : <A HREF=\"manage.php3?$_auth&id=$patient\"
         >".$this_patient->fullName(true)."</A>
       <$STDFONT_E>
       </CENTER>
       <P>
     ";
     freemed_display_actionbar();
     echo "
       <P>
       <TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0 CELLPADDING=2
        ALIGN=CENTER VALIGN=MIDDLE BGCOLOR=\"#000000\">
       <TR>
        <TD><$STDFONT_B COLOR=\"#ffffff\">Dates<$STDFONT_E></TD>
        <TD><$STDFONT_B COLOR=\"#ffffff\">&nbsp;<$STDFONT_E></TD>
        <TD><$STDFONT_B COLOR=\"#ffffff\">$Action<$STDFONT_E></TD>
       </TR>
     ";
     while ($r = fdb_fetch_array ($result)) {
       $id         = $r[id];
       $_alternate = freemed_bar_alternate_color ($_alternate);
       echo "
        <TR BGCOLOR=$_alternate>
         <TD>
          <$STDFONT_B>$r[authdtbegin] / $r[authdtend]<$STDFONT_E>
         </TD>
         <TD>
          <$STDFONT_B>RESERVED FOR FUTURE USE<$STDFONT_E>
         </TD>
         <TD>
       ";
       if (freemed_get_userlevel($LoginCookie)>$database_level)
         echo "
           &nbsp;
           <A HREF=\"$page_name?$_auth&action=modform&patient=$patient&id=$id\"
            >MOD</A>
         ";
       if (freemed_get_userlevel($LoginCookie)>$delete_level)
         echo "
           &nbsp;
           <A HREF=\"$page_name?$_auth&action=del&patient=$patient&id=$id\"
            >DEL</A>
         ";
       echo "
         </TD>
        </TR>
       ";
     } // end master while fetch loop
     echo "
       </TABLE>";
     
     freemed_display_actionbar();
     
     echo "
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
