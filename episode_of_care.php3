<?php
 # file: episode_of_care.php3
 # desc: episode of care database module
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

 $page_name   = "episode_of_care.php3";
 $record_name = "Episode of Care";
 $db_name     = "eoc";

 include ("global.var.inc");
 include ("freemed-functions.inc");

 freemed_open_db ($LoginCookie);
 freemed_display_html_top ();
 freemed_display_banner ();

 switch ($action) {
  // trying to combine add and modify forms for simplicity
  case "addform": case "modform":
   switch ($action) {
     case "addform":
      $go = "add";
      $this_action = "$Add";
      $eocstartdate = $eocdtlastsimilar = $cur_date;
      break;
     case "modform":
      $go = "mod";
      $this_action = "$Modify";
       // check to see if an id was submitted
      if ($id<1) {
       freemed_display_box_top ("$record_name :: $ERROR");
       echo "
         $Must_select_record_to_modify
       ";
       freemed_display_box_bottom ();
       freemed_close_db ();
       freemed_display_html_bottom ();
       DIE("");
      } // end of if.. statement checking for id #

      if ($been_here != "yes") {
         // now we extract the data, since the record was given...
        $query  = "SELECT * FROM $db_name WHERE id='$id'";
        $result = fdb_query ($query);
        $r      = fdb_fetch_array ($result);

        $eocpatient         = $r["eocpatient"      ];
        $eocdescrip         = fm_prep($r["eocdescrip"]);
        $eocstartdate       = $r["eocstartdate"    ];
        $eocdtlastsimilar   = $r["eocdtlastsimilar"];
        $eocreferrer        = $r["eocreferrer"     ];
        $eocfacility        = $r["eocfacility"     ];
        $eocdiagfamily      = $r["eocdiagfamily"   ];  // diagnosis family
        $eocrelpreg         = $r["eocrelpreg"      ];
        $eocrelemp          = $r["eocrelemp"       ];
        $eocrelauto         = $r["eocrelauto"      ];
        $eocrelother        = $r["eocrelother"     ];
        $eocrelstpr         = fm_prep($r["eocrelstpr"]);
        $eocrelautoname     = fm_prep($r["eocrelautoname"]);
        $eocrelautoaddr1    = fm_prep($r["eocrelautoaddr1"]);
        $eocrelautoaddr2    = fm_prep($r["eocrelautoaddr2"]);
        $eocrelautocity     = fm_prep($r["eocrelautocity"]);
        $eocrelautostpr     = fm_prep($r["eocrelautostpr"]);
        $eocrelautozip      = fm_prep($r["eocrelautozip"]);
        $eocrelautocountry  = fm_prep($r["eocrelautocountry"]);
        $eocrelautocase     = fm_prep($r["eocrelautocase"]);
        $eocrelautorcname   = fm_prep($r["eocrelautorcname"]);
        $eocrelautorcphone  = $r["eocrelautorcphone"];
        $eocrelempname      = fm_prep($r["eocrelempname"]);
        $eocrelempaddr1     = fm_prep($r["eocrelempaddr1"]);
        $eocrelempaddr2     = fm_prep($r["eocrelempaddr2"]);
        $eocrelempcity      = fm_prep($r["eocrelempcity"]);
        $eocrelempstpr      = fm_prep($r["eocrelempstpr"]);
        $eocrelempzip       = fm_prep($r["eocrelempzip"]);
        $eocrelempcountry   = fm_prep($r["eocrelempcountry"]);
        $eocrelempfile      = fm_prep($r["eocrelempfile"]);
        $eocrelemprcname    = fm_prep($r["eocrelemprcname"]);
        $eocrelemprcphone   = $r["eocrelemprcphone"];
        $eocrelpregcycle    = $r["eocrelpregcycle"];
        $eocrelpreggravida  = $r["eocrelpreggravida"];
        $eocrelpregpara     = $r["eocrelpregpara"];
        $eocrelpregmiscarry = $r["eocrelpregmiscarry"];
        $eocrelpregabort    = $r["eocrelpregabort"];
        $eocrelpreglastper  = $r["eocrelpreglastper"];
        $eocrelpregconfine  = $r["eocrelpregconfine"];
        $eocrelothercomment = fm_prep($r["eocrelothercomment"]);
        $eoctype            = $r["eoctype"];
        break;
      } // end checking if we have been here yet...
   } // end of interior switch
   freemed_display_box_top ("$this_action $record_name");

   // fix the yes/no and multiple choice switches
   switch ($eocrelauto) {
     case "no":  $eocrelauto_n = "SELECTED"; break;
     case "yes": $eocrelauto_y = "SELECTED"; break;
   } // end eocrelauto (switch)
   switch ($eocrelemp) {
     case "no":  $eocrelemp_n = "SELECTED"; break;
     case "yes": $eocrelemp_y = "SELECTED"; break;
   } // end eocrelemp (switch)
   switch ($eocrelpreg) {
     case "no":  $eocrelpreg_n = "SELECTED"; break;
     case "yes": $eocrelpreg_y = "SELECTED"; break;
   } // end eocrelpreg (switch)
   switch ($eocrelother) {
     case "no":  $eocrelother_n = "SELECTED"; break;
     case "yes": $eocrelother_y = "SELECTED"; break;
   } // end eocrelother (switch)

    // grab important patient information
   $this_patient = new Patient ($patient);

   echo "
    <CENTER>
     <B>$Patient:</B> 
     <A HREF=\"manage.php3?$_auth&id=$patient\"
      >".$this_patient->fullName (true)."</A>
    </CENTER><P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"yes\">
     <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"$id\">
     <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
    <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Description<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocdescrip\" SIZE=25 MAXLENGTH=100
       VALUE=\"".fm_prep($eocdescrip)."\">
     </TD>
  ";
  if ($this_patient->isFemale()) { echo "
     <TD ALIGN=RIGHT><$STDFONT_B>$Related_to_Pregnancy<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <SELECT NAME=\"eocrelpreg\">
       <OPTION VALUE=\"no\"  $eocrelpreg_n>$lang_no
       <OPTION VALUE=\"yes\" $eocrelpreg_y>$lang_yes
      </SELECT>
     </TD>
  "; } else { echo "
     <TD ALIGN=RIGHT><$STDFONT_B><I>$Related_to_Pregnancy<$STDFONT_E></I></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=HIDDEN NAME=\"eocrelpreg\" VALUE=\"no\">
      <I><$STDFONT_B>no<$STDFONT_E></I>
     </TD>
  "; } // end checking if female
  echo "  
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Date_of_First_Occurance<$STDFONT_E></TD>
      <TD ALIGN=LEFT>
  ";
  fm_date_entry("eocstartdate");
  echo "
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Related_to_Employment<$STDFONT_E></TD>
      <TD ALIGN=LEFT>
      <SELECT NAME=\"eocrelemp\">
       <OPTION VALUE=\"no\"  $eocrelemp_n>$lang_no
       <OPTION VALUE=\"yes\" $eocrelemp_y>$lang_yes
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Date_of_Last_Similar<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_date_entry("eocdtlastsimilar");
   echo "
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Related_to_Automobile<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
     <SELECT NAME=\"eocrelauto\">
       <OPTION VALUE=\"no\"  $eocrelauto_n>$lang_no
       <OPTION VALUE=\"yes\" $eocrelauto_y>$lang_yes
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Referring_Physician<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <SELECT NAME=\"eocreferrer\">
   ";
   freemed_display_physicians ($eocreferrer);
   echo "
      </SELECT>
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Related_to_Other_Cause<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
     <SELECT NAME=\"eocrelother\">
       <OPTION VALUE=\"no\"  $eocrelother_n>$lang_no
       <OPTION VALUE=\"yes\" $eocrelother_y>$lang_yes
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Facility<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <SELECT NAME=\"eocfacility\">
   ";
   if (empty($eocfacility)) $eocfacility = $default_facility;
   freemed_display_facilities ($eocfacility);
   echo "
      </SELECT>
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>State Province<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelstpr\" SIZE=5 MAXLENGTH=5
       VALUE=\"$eocrelstpr\">
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Diagnosis_Family<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
    ";
    // compact and display eocdiagfamily
    $eocdiagfamily = fm_join_from_array ($eocdiagfamily);
    freemed_multiple_choice ("SELECT * FROM diagfamily
      ORDER BY dfname, dfdescrip", "dfname:dfdescrip", "eocdiagfamily",
      $eocdiagfamily, false);
    echo "
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Episode_Type<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
    ";
    // case statement for eoctype
    switch ($eoctype) {
      case "acute":             $type_a  = "SELECTED"; break;
      case "chronic":           $type_c  = "SELECTED"; break;
      case "chronic recurrent": $type_cr = "SELECTED"; break;
      case "historical":        $type_h  = "SELECTED"; break;
    } // end switch for $eoctype
    echo "
      <SELECT NAME=\"eoctype\">
       <OPTION VALUE=\"\"                          >$NONE_SELECTED
       <OPTION VALUE=\"acute\"             $type_a >$lang_acute
       <OPTION VALUE=\"chronic\"           $type_c >$lang_chronic
       <OPTION VALUE=\"chronic recurrent\" $type_cr>$lang_chronic_recurrent
       <OPTION VALUE=\"historical\"        $type_h >$lang_historical
      </SELECT>
     </TD>
    </TR>
    </TABLE>
    <P>
   ";

   if ($eocrelauto=="yes") { echo "
      <!-- conditional auto table -->

     <CENTER>
     <P>
      <$STDFONT_B><B>$Automobile_Related_Information</B><$STDFONT_E>
     <BR>
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Auto_Insurance<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoname\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautoname\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Case_Number<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautocase\" SIZE=10 MAXLENGTH=20
       VALUE=\"$eocrelautocase\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Address ($Line_1)<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoaddr1\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautoaddr1\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Contact_Name<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautorcname\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautorcname\">
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Address ($Line_2)<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoaddr2\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautoaddr2\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Contact_Phone<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_phone_entry("eocrelautorcphone");
   echo "
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$City, $St_Pr,<BR>$Postal_Code<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautocity\" SIZE=10 MAXLENGTH=100
       VALUE=\"$eocrelautocity\"> <B>,</B>
      <INPUT TYPE=TEXT NAME=\"eocrelautostpr\" SIZE=4 MAXLENGTH=3
       VALUE=\"$eocrelautostpr\">
      <INPUT TYPE=TEXT NAME=\"eocrelautozip\" SIZE=11 MAXLENGTH=10
       VALUE=\"$eocrelautozip\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Email_Address<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautorcemail\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautorcemail\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Country<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
       <INPUT TYPE=TEXT NAME=\"eocrelautocountry\" SIZE=10 MAXLENGTH=100
       VALUE=\"$eocrelautocountry\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>&nbsp; <!-- placeholder --><$STDFONT_E></TD>
     <TD ALIGN=LEFT>
       &nbsp; <!-- placeholder -->
     </TD>
     </TR>
     </TABLE>
     </CENTER>
   "; } // end of conditional auto info



   if ($eocrelemp=="yes") { echo "
      <!-- conditional employment table -->

     <CENTER>
     <P>
      <$STDFONT_B><B>$Employment_Related_Information</B><$STDFONT_E>
     <BR>
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Name_of_Employer<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempname\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelempname\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$File_Number<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempfile\" SIZE=10 MAXLENGTH=20
       VALUE=\"$eocrelempfile\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Address ($Line_1)<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempaddr1\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelempaddr1\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Contact_Name<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelemprcname\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelemprcname\">
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Address ($Line_2)<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempaddr2\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelempaddr2\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Contact_Phone<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_phone_entry("eocrelemprcphone");
   echo "
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$City, $St_Pr,<BR>$Postal_Code<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempcity\" SIZE=10 MAXLENGTH=100
       VALUE=\"$eocrelempcity\"> <B>,</B>
      <INPUT TYPE=TEXT NAME=\"eocrelempstpr\" SIZE=4 MAXLENGTH=3
       VALUE=\"$eocrelempstpr\">
      <INPUT TYPE=TEXT NAME=\"eocrelempzip\" SIZE=11 MAXLENGTH=10
       VALUE=\"$eocrelempzip\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Email_Address<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelemprcemail\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelemprcemail\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Country<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
       <INPUT TYPE=TEXT NAME=\"eocrelempcountry\" SIZE=10 MAXLENGTH=100
       VALUE=\"$eocrelempcountry\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>&nbsp; <!-- placeholder --><$STDFONT_E></TD>
     <TD ALIGN=LEFT>
       &nbsp; <!-- placeholder -->
     </TD>
     </TR>
     </TABLE>
     </CENTER>
   "; } // end of conditional employment info


   if ($eocrelpreg=="yes") { echo "
      <!-- conditional pregnancy table -->

     <CENTER>
     <P>
      <$STDFONT_B><B>$Pregnancy_Related_Information</B><$STDFONT_E>
     <BR>
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Length_of_Cycle<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_number_select ("eocrelpregcycle", 10, 40);
   echo "
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Last_Menstrual_Period<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_date_entry("eocrelpreglastper");
   echo "
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Gravida<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_number_select("eocrelpreggravida", 0, 15);
   echo "
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Date_of_Confinement<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_date_entry("eocrelpregconfine");
   echo "
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Para<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_number_select("eocrelpregpara", 0, 15);
   echo "
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>$Miscarries<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_number_select("eocrelpregmiscarry", 0, 15);
   echo "
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$Abortions<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_number_select("eocrelpregabort", 0, 15);
   echo "
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>&nbsp; <!-- placeholder --><$STDFONT_E></TD>
     <TD ALIGN=LEFT>
       &nbsp; <!-- placeholder -->
     </TD>
     </TR>
     </TABLE>
     </CENTER>
   "; } // end of conditional pregnancy info

   if ($eocrelother=="yes") { echo "
      <!-- conditional other table -->

     <CENTER>
     <P>
      <$STDFONT_B><B>$Other_Related_Information</B><$STDFONT_E>
     <BR>
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>$More_Information<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelothercomment\" SIZE=35 MAXLENGTH=100
       VALUE=\"$eocrelothercomment\">
     </TD>
     </TR>
    </TABLE>
    "; } // end of other conditional reason

   echo "
     <P>
     <CENTER>
     <SELECT NAME=\"action\">
      <OPTION VALUE=\"$action\">Update
      <OPTION VALUE=\"$go\">$this_action
      <OPTION VALUE=\"view\">$Return_to_Menu
     </SELECT>
     <INPUT TYPE=SUBMIT VALUE=\"$lang_go\">
     </CENTER>
    ";
   freemed_display_box_bottom ();
   break;

  // REAL ADD AND MODIFY FUNCTIONS ARE BELOW

  case "add": case "mod":
   switch ($action) {
     case "add":
       $this_action = "$Adding";
     case "mod":
       $this_action = "$Modifying";
   }
   freemed_display_box_top ("$this_action $record_name");
   echo "
     <$STDFONT_B>$this_action<$STDFONT_E> ...
   ";

   // compact 3d arrays into strings...
   $eocdiagfamily_a   = fm_join_from_array ($eocdiagfamily     );

     // assemble all "normal" dates
   $eocstartdate      = fm_date_assemble   ("eocstartdate"     );
   $eocdtlastsimilar  = fm_date_assemble   ("eocdtlastsimilar" );
   $eocrelpreglastper = fm_date_assemble   ("eocrelpreglastper");
   $eocrelpregconfine = fm_date_assemble   ("eocrelpregconfine");

     // assemble all phone numbers
   $eocrelautorcphone = fm_phone_assemble  ("eocrelautorcphone");
   $eocrelemprcphone  = fm_phone_assemble  ("eocrelemprcphone" );

   // move patient over
   $eocpatient = $patient;

   switch ($action) {
    case "add":
     $query = "INSERT INTO $db_name VALUES (
       '".addslashes($eocpatient)                 ."',
       '".addslashes($eocdescrip)                 ."',
       '".addslashes($eocstartdate)               ."',
       '".addslashes($eocdtlastsimilar)           ."',
       '".addslashes($eocreferrer)                ."',
       '".addslashes($eocfacility)                ."',
       '".addslashes($eocdiagfamily_a)            ."',
       '".addslashes($eocrelpreg)                 ."',
       '".addslashes($eocrelemp)                  ."',
       '".addslashes($eocrelauto)                 ."',
       '".addslashes($eocrelother)                ."',
       '".addslashes($eocrelstpr)                 ."',
       '".addslashes($eoctype)                    ."',
       '".addslashes($eocrelautoname)             ."',
       '".addslashes($eocrelautoaddr1)            ."',
       '".addslashes($eocrelautoaddr2)            ."',
       '".addslashes($eocrelautocity)             ."',
       '".addslashes($eocrelautostpr)             ."',
       '".addslashes($eocrelautozip)              ."',
       '".addslashes($eocrelautocountry)          ."',
       '".addslashes($eocrelautocase)             ."',
       '".addslashes($eocrelautorcname)           ."',
       '".addslashes($eocrelautorcphone)          ."',
       '".addslashes($eocrelempname)              ."',
       '".addslashes($eocrelempaddr1)             ."',
       '".addslashes($eocrelempaddr2)             ."',
       '".addslashes($eocrelempcity)              ."',
       '".addslashes($eocrelempstpr)              ."',
       '".addslashes($eocrelempzip)               ."',
       '".addslashes($eocrelempcountry)           ."',
       '".addslashes($eocrelempfile)              ."',
       '".addslashes($eocrelemprcname)            ."',
       '".addslashes($eocrelemprcphone)           ."',
       '".addslashes($eocrelemprcemail)           ."',
       '".addslashes($eocrelpregcycle)            ."',
       '".addslashes($eocrelpreggravida)          ."',
       '".addslashes($eocrelpregpara)             ."',
       '".addslashes($eocrelpregmiscarry)         ."',
       '".addslashes($eocrelpregabort)            ."',
       '".addslashes($eocrelpreglastper)          ."',
       '".addslashes($eocrelpregconfine)          ."',
       '".addslashes($eocrelothercomment)         ."',
       NULL )";
      break;
     case "mod":
      $query = "UPDATE $db_name SET
        eocpatient         = '".addslashes($eocpatient).        "',
        eocdescrip         = '".addslashes($eocdescrip).        "',
        eocstartdate       = '".addslashes($eocstartdate).      "',
        eocdtlastsimilar   = '".addslashes($eocdtlastsimilar).  "',
        eocreferrer        = '".addslashes($eocreferrer).       "',
        eocfacility        = '".addslashes($eocfacility).       "',
        eocdiagfamily      = '".addslashes($eocdiagfamily_a).   "',
        eocrelpreg         = '".addslashes($eocrelpreg).        "',
        eocrelemp          = '".addslashes($eocrelemp).         "',
        eocrelauto         = '".addslashes($eocrelauto).        "',
        eocrelother        = '".addslashes($eocrelother).       "',
        eocrelstpr         = '".addslashes($eocrelstpr).        "',
        eoctype            = '".addslashes($eoctype).           "',
        eocrelautoname     = '".addslashes($eocrelautoname).    "',
        eocrelautoaddr1    = '".addslashes($eocrelautoaddr1).   "',
        eocrelautoaddr2    = '".addslashes($eocrelautoaddr2).   "',
        eocrelautocity     = '".addslashes($eocrelautocity).    "',
        eocrelautostpr     = '".addslashes($eocrelautostpr).    "',
        eocrelautozip      = '".addslashes($eocrelautozip).     "',
        eocrelautocountry  = '".addslashes($eocrelautocountry). "',
        eocrelautocase     = '".addslashes($eocrelautocase).    "',
        eocrelautorcname   = '".addslashes($eocrelautorcname).  "',
        eocrelautorcphone  = '".addslashes($eocrelautorcphone). "',
        eocrelempname      = '".addslashes($eocrelempname).     "',
        eocrelempaddr1     = '".addslashes($eocrelempaddr1).    "',
        eocrelempaddr2     = '".addslashes($eocrelempaddr2).    "',
        eocrelempcity      = '".addslashes($eocrelempcity).     "',
        eocrelempstpr      = '".addslashes($eocrelempstpr).     "',
        eocrelempzip       = '".addslashes($eocrelempzip).      "',
        eocrelempcountry   = '".addslashes($eocrelempcountry).  "',
        eocrelempfile      = '".addslashes($eocrelempfile).     "',
        eocrelemprcname    = '".addslashes($eocrelemprcname).   "',
        eocrelemprcphone   = '".addslashes($eocrelemprcphone).  "',
        eocrelemprcemail   = '".addslashes($eocrelemprcemail).  "',
        eocrelpregcycle    = '".addslashes($eocrelpregcycle).   "',
        eocrelpreggravida  = '".addslashes($eocrelpreggravida). "',
        eocrelpregpara     = '".addslashes($eocrelpregpara).    "',
        eocrelpregmiscarry = '".addslashes($eocrelpregmiscarry)."',
        eocrelpregabort    = '".addslashes($eocrelpregabort).   "',
        eocrelpreglastper  = '".addslashes($eocrelpreglastper). "',
        eocrelpregconfine  = '".addslashes($eocrelpregconfine). "',
        eocrelothercomment = '".addslashes($eocrelothercomment)."' 
        WHERE id='$id'";
      break;
   } // end of action switch...

   $result = fdb_query ($query);
   if ($debug)  { echo " ( query = \"$query\" ) <BR>"; }
   if ($result) { echo "$Done."; }
    else        { echo "$ERROR"; }
   echo "
     <P>
     <CENTER>
      <A HREF=\"manage.php3?$_auth&id=$patient\"
      ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A> <B>|</B>
      <A HREF=\"$page_name?$_auth&patient=$patient\"
      ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A></CENTER>
     <BR>
   ";
   freemed_display_box_bottom ();
   break;

  case "del":
   freemed_display_box_top ("$Deleting $record_name");
   echo "
    <P>
    <$STDFONT_B>$Deleting ...
    ";
   $query = "DELETE FROM $db_name WHERE id='$id'";
   $result = fdb_query ($query);
   if ($result) { echo "$Done\n";    }
    else        { echo "$ERROR\n";   }
   echo "
    <$STDFONT_E>
    <P>
    <CENTER>
      <A HREF=\"manage.php3?$_auth&id=$patient\"
      ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A> <B>|</B>
     <A HREF=\"$page_name?$_auth\"
      ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER> 
   ";
   freemed_display_box_bottom ();
   break;

  case "manage": // view of entire episode (central control screen)
   if ($id<1) {
     freemed_display_box_top ("$record_name View :: $ERROR");
     echo "
       <P>
       <$STDFONT_B>You must specify an ID to view an Episode!<$STDFONT_E>
       <P>
       <CENTER>
        <A HREF=\"manage.php3?$_auth&id=$patient\"
        ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
       </CENTER>
     ";
     freemed_display_box_bottom ();
     freemed_close_db ();
     freemed_display_html_bottom ();
     DIE("");
   } // end checking for ID as valid
   freemed_display_box_top ("$record_name View");

   // create new patient object
   $this_patient = new Patient ($patient);

   // display header of box with patient information
   echo "
     <P>
      <CENTER>
      <$STDFONT_B>$Patient<$STDFONT_E> :
      <A HREF=\"manage.php3?$_auth&id=$patient\"
      ><$STDFONT_B>".$this_patient->fullName(true)."<$STDFONT_E></A>
      </CENTER>
     <P>
   ";

   // display vitals for current episode

   // procedures display
   echo "
     <!-- Outer Table -->
     <TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0 CELLPADDING=3
      ALIGN=CENTER VALIGN=MIDDLE BGCOLOR=\"#000000\">
     <TR><TD ALIGN=CENTER>
       <$STDFONT_B COLOR=\"#ffffff\" SIZE=+1>Procedures<$HEADERFONT_E>
     </TD></TR>
     <TR><TD>
   ";

   freemed_display_actionbar("procedure.php3");
   echo "
     <TR><TD>
     
     <TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0 CELLPADDING=2
      ALIGN=CENTER VALIGN=MIDDLE BGCOLOR=\"#000000\">
     <TR>
      <TD><$STDFONT_B COLOR=\"#ffffff\">Date&nbsp;<$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\">Procedure&nbsp;<$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\">Comment&nbsp;<$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\">Action&nbsp;<$STDFONT_E></TD>
     </TR>
   ";
   // special jimmy-rigged query to find in 3d array...
   $query = "SELECT * FROM procrec
             WHERE ((proceoc LIKE '$id:%') OR
                    (proceoc LIKE '%:$id') OR
                    (proceoc LIKE '%:$id:%') OR
                    (proceoc='$id'))
             ORDER BY procdt DESC";
   $result = fdb_query ($query);
   $_alternate=freemed_bar_alternate_color($_alternate);
   if (($result) and (fdb_num_rows($result)>0)) { // if there is a result
    while ($r = fdb_fetch_array ($result)) {
     $p_id     = $r["id"];
     $p_cpt    = $r["proccpt"];
     $p_cptmod = $r["proccptmod"];
     $p_dt     = $r["procdt"]; // date
     $p_co     = $r["proccomment"];
     $p_cpt_name = freemed_get_link_field($p_cpt,"cpt","cptcode");
     if (empty($p_co)) { $p_co = "NO DESCRIPTION"; }
     if (strlen ($p_co)>50) $p_co = substr ($p_co, 0, 50)."...";
     $_alternate=freemed_bar_alternate_color($_alternate);
     echo "
       <TR BGCOLOR=$_alternate>
        <TD>
         <A HREF=\"procedure.php3?$_auth&id=$p_id&action=view&".
         "patient=$patient\"
         ><$STDFONT_B>$p_dt<$STDFONT_E></A>
        </TD>
	<TD>
         <$STDFONT_B>$p_cpt_name&nbsp;<$STDFONT_E>
	</TD>
	<TD>
	 <$STDFONT_B>$p_co<$STDFONT_E>
	</TD>
	<TD>
     ";
     if (freemed_get_userlevel($LoginCookie)>$database_level)
       echo "
	  <A HREF=\"procedure.php3?$_auth&id=$p_id&action=modform&".
          "patient=$patient\"
          ><$STDFONT_B>MOD<$STDFONT_E></A>
       ";
     if (freemed_get_userlevel($LoginCookie)>$delete_level)
       echo "
	  &nbsp;<A HREF=\"procedure.php3?$_auth&id=$p_id&action=delete&".
          "patient=$patient\"
          ><$STDFONT_B>DEL<$STDFONT_E></A>
       ";
     echo "
	&nbsp;
	</TD>
       </TR>
     ";
    } // end of while
    echo "</TABLE>\n";
   } else { // if there is no result
    echo "
     <TR><TD ALIGN=CENTER BGCOLOR=$_alternate COLSPAN=4>
      <$STDFONT_B><I>No Procedures</I><$STDFONT_E>
     </TD></TR></TABLE>
    ";
   } // end if/else for result
   freemed_display_actionbar("procedure.php3");
   echo "
     </TD></TR></TABLE><!-- End Outer Table -->
   ";
   // end of procedures display
   echo "<BR>\n";
   // progress notes display
   echo "
     <!-- Outer Table -->
     <TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0 CELLPADDING=3
      ALIGN=CENTER VALIGN=MIDDLE BGCOLOR=\"#000000\">
     <TR><TD ALIGN=CENTER>
       <$STDFONT_B COLOR=\"#ffffff\" SIZE=+1>Progress Notes<$HEADERFONT_E>
     </TD></TR>
     <TR><TD>
   ";
   freemed_display_actionbar("progress_notes.php3");
   echo "
     <TR><TD>
     
     <TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0 CELLPADDING=2
      ALIGN=CENTER VALIGN=MIDDLE BGCOLOR=\"#000000\">
     <TR>
      <TD><$STDFONT_B COLOR=\"#ffffff\">Date<$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\">Action<$STDFONT_E></TD>
     </TR>
   ";
   // special jimmy-rigged query to find in 3d array...
   $result = 0;
   $query = "SELECT * FROM pnotes
             WHERE ((pnotespat='$patient') AND
                    ((pnoteseoc LIKE '$id:%') OR
                    (pnoteseoc LIKE '%:$id') OR
                    (pnoteseoc LIKE '%:$id:%') OR
                    (pnoteseoc='$id')))
             ORDER BY pnotesdt DESC";
   $result = fdb_query ($query);
   if (($result) and (fdb_num_rows($result)>0)) { // if there is a result
    while ($r = fdb_fetch_array ($result)) {
     $p_id = $r["id"];
     $p_dt = $r["pnotesdt"];
     $_alternate=freemed_bar_alternate_color($_alternate);
     echo "
      <TR BGCOLOR=$_alternate>
        <TD>
	  <A HREF=\"progress_notes.php3?$_auth&id=$p_id&action=display&".
          "patient=$patient\"
          ><$STDFONT_B>$p_dt ($p_id)<$STDFONT_E></A>
        </TD>
	<TD>
     ";
     if (freemed_get_userlevel($LoginCookie)>$database_level)
       echo "
	  <A HREF=\"progress_notes.php3?$_auth&id=$p_id&action=modform&".
          "patient=$patient\"
          ><$STDFONT_B>MOD<$STDFONT_E></A>
       ";
     if (freemed_get_userlevel($LoginCookie)>$delete_level)
       echo "
	  &nbsp;<A HREF=\"progress_notes.php3?$_auth&id=$p_id&action=delete&".
          "patient=$patient\"
          ><$STDFONT_B>DEL<$STDFONT_E></A>
       ";
     echo "
	&nbsp;
	</TD>
      </TR>
     ";
    } // end of while
   } else { // if there is no result
    echo "
     <TR BGCOLOR=$_alternate><TD COLSPAN=2 ALIGN=CENTER>
      <$STDFONT_B><I>No Progress Notes</I><$STDFONT_E>
     </TD></TR>
    ";
   } // end if/else for result
   echo "</TABLE>\n";
   freemed_display_actionbar("progress_notes.php3");
   echo "
     </TD></TR></TABLE><!-- End Outer Table -->
   ";
   // end of progress notes display

   // display management link at the bottom...
   echo "
     <P>
     <CENTER>
      <A HREF=\"$page_name?$_auth&patient=$patient\"
      ><$STDFONT_B>Choose Another $record_name<$STDFONT_E></A>
     </CENTER>
     <P>
   ";
   freemed_display_box_bottom ();
   break; // end of manage action

  default: // default action -- menu
   if ($patient<1) {
     freemed_display_box_top ("$record_name :: $ERROR");
     echo "
      <P>
      <$STDFONT_B>$Must_specify_patient<$STDFONT_E>
      <P>
     ";
     freemed_display_box_bottom ();
     freemed_close_db ();
     freemed_display_html_bottom ();
     DIE ("");
   } // kick the bucket if no patient

   freemed_display_box_top ("$record_name");
   $result = fdb_query ("SELECT * FROM $db_name
                         WHERE eocpatient='$patient'
                         ORDER BY eocstartdate DESC");
   if (($result>0) and (fdb_num_rows($result)>0)) {

    $this_patient = new Patient ($patient);
    echo "
      <P>
      <CENTER>
       <$STDFONT_B>$Patient : <$STDFONT_E>
       <A HREF=\"manage.php3?$_auth&id=$patient\"
       ><$STDFONT_B>".$this_patient->fullName(true)."<$STDFONT_E></A>
      </CENTER>
      <P>
    ";

    // display action bar
    freemed_display_actionbar ();

    // display table top
    echo "
      <P>
      <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=2 BORDER=0
       BGCOLOR=#000000 VALIGN=MIDDLE ALIGN=CENTER>
      <TR BGCOLOR=#000000>
       <TD BGCOLOR=#000000>
        <$STDFONT_B COLOR=#ffffff>$Starting Date<$STDFONT_E></TD>
       <TD BGCOLOR=#000000>
        <$STDFONT_B COLOR=#ffffff>$Description<$STDFONT_E></TD>
       <TD BGCOLOR=#000000>
        <$STDFONT_B COLOR=#ffffff>$Action<$STDFONT_E></TD>
      </TR>
     ";

    // loop for all
    while ($r = fdb_fetch_array ($result)) {
      $_alternate   = freemed_bar_alternate_color ($_alternate);
      $eocstartdate = fm_prep($r["eocstartdate"]);
      $eocdescrip   = fm_prep($r["eocdescrip"  ]);
      $id           =         $r["id"          ] ;

      echo "
        <TR BGCOLOR=\"$_alternate\">
         <TD>$eocstartdate</TD>
         <TD>$eocdescrip</TD>
         <TD>
       ";
      if (freemed_get_userlevel($LoginCookie)>$database_level)
       echo "
        <A HREF=\"$page_name?$_auth&action=manage&patient=$patient&id=$id\"
         ><$STDFONT_B SIZE=-2>MANAGE<$STDFONT_E></A>
       ";

      if (freemed_get_userlevel($LoginCookie)>$database_level)
       echo "
        <A HREF=\"$page_name?$_auth&action=modform&patient=$patient&id=$id\"
         ><$STDFONT_B SIZE=-1>$lang_MOD<$STDFONT_E></A>
       ";

      if (freemed_get_userlevel($LoginCookie)>$delete_level)
       echo "
        <A HREF=\"$page_name?$_auth&action=del&id=$id&patient=$patient\"
         ><$STDFONT_B SIZE=-1>$lang_DEL<$STDFONT_E></A>
       ";

      echo "
         &nbsp;</TD>
        </TR>
       ";
    } // end of while loop 

    // display table bottom
    echo "
      </TABLE>
      <P>
     ";
 
    // display bottom action bar
    freemed_display_actionbar ();
   } else { // if there aren't any records, tell us so
    echo "
      <P>
      <CENTER>
       <B><$STDFONT_B>$No_record_for_patient<$STDFONT_E></B>
       <P>
       <A HREF=\"$page_name?$_auth&action=addform&patient=$patient\"
        ><$STDFONT_B>$Add $record_name<$STDFONT_E></A>
      </CENTER>
      <P>
    ";
   }
   freemed_display_box_bottom ();
   break;
 } // end master switch

 freemed_close_db ();
 freemed_display_html_bottom ();
?>
