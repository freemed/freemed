<?php
 // file: episode_of_care.php3
 // desc: episode of care database module
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

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
      $this_action = "Add";
      $eocstartdate = $eocdtlastsimilar = $cur_date;
      break;
     case "modform":
      $go = "mod";
      $this_action = "Modify";
       // check to see if an id was submitted
      if ($id<1) {
       freemed_display_box_top (_("$record_name")." :: "._("ERROR"));
       echo _("Must select record to Modify");
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
   freemed_display_box_top (_("$this_action $record_name"));

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
     <$STDFONT_B>"._("Patient")." :
     <A HREF=\"manage.php3?$_auth&id=$patient\"
      >".$this_patient->fullName (true)."</A>
     <$STDFONT_E>
    </CENTER><P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"yes\">
     <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"$id\">
     <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
    <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD COLSPAN=4 ALIGN=CENTER BGCOLOR=\"#777777\">
      <$STDFONT_B SIZE=\"+1\" COLOR=\"#ffffff\">
      "._("General Information")."
      <$STDFONT_E>
     </TD>
    </TR>
    <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Description")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocdescrip\" SIZE=25 MAXLENGTH=100
       VALUE=\"".fm_prep($eocdescrip)."\">
     </TD>
  ";
  if ($this_patient->isFemale()) { echo "
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Related to Pregnancy")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <SELECT NAME=\"eocrelpreg\">
       <OPTION VALUE=\"no\"  $eocrelpreg_n>"._("No")."
       <OPTION VALUE=\"yes\" $eocrelpreg_y>"._("Yes")."
      </SELECT>
     </TD>
  "; } else { echo "
     <TD ALIGN=RIGHT><$STDFONT_B><I>"._("Related to Pregnancy")."<$STDFONT_E></I></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=HIDDEN NAME=\"eocrelpreg\" VALUE=\"no\">
      <I><$STDFONT_B>"._("No")."<$STDFONT_E></I>
     </TD>
  "; } // end checking if female
  echo "  
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Date of First Occurance")."<$STDFONT_E></TD>
      <TD ALIGN=LEFT>
  ";
  fm_date_entry("eocstartdate");
  echo "
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Related to Employment")."<$STDFONT_E></TD>
      <TD ALIGN=LEFT>
      <SELECT NAME=\"eocrelemp\">
       <OPTION VALUE=\"no\"  $eocrelemp_n>"._("No")."
       <OPTION VALUE=\"yes\" $eocrelemp_y>"._("Yes")."
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Date of Last Similar")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_date_entry("eocdtlastsimilar");
   echo "
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Related to Automobile")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
     <SELECT NAME=\"eocrelauto\">
       <OPTION VALUE=\"no\"  $eocrelauto_n>"._("No")."
       <OPTION VALUE=\"yes\" $eocrelauto_y>"._("Yes")."
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Referring Physician")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   $pref_r = fdb_query("SELECT * FROM physician WHERE phyref='yes'
                        ORDER BY phylname,phyfname");
   echo freemed_display_selectbox ($pref_r, 
     "#phylname#, #phyfname#", "eocreferrer")."
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Related to Other Cause")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
     <SELECT NAME=\"eocrelother\">
       <OPTION VALUE=\"no\"  $eocrelother_n>"._("No")."
       <OPTION VALUE=\"yes\" $eocrelother_y>"._("Yes")."
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Facility")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   if (empty($eocfacility)) $eocfacility = $default_facility;
   $fac_r = fdb_query("SELECT * FROM facility ORDER BY psrname,psrnote");
   
   echo 
     freemed_display_selectbox ($fac_r, "#psrname# [#psrnote#]", 
     "eocfacility")."
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("State/Province")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelstpr\" SIZE=5 MAXLENGTH=5
       VALUE=\"$eocrelstpr\">
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Diagnosis Family")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
    ";
    // compact and display eocdiagfamily
    echo freemed_multiple_choice ("SELECT * FROM diagfamily
           ORDER BY dfname, dfdescrip", "dfname:dfdescrip", "eocdiagfamily",
           fm_join_from_array($eocdiagfamily), false)."
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Episode Type")."<$STDFONT_E></TD>
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
       <OPTION VALUE=\"\"                          >"._("NONE SELECTED")."
       <OPTION VALUE=\"acute\"             $type_a >"._("acute")."
       <OPTION VALUE=\"chronic\"           $type_c >"._("chronic")."
       <OPTION VALUE=\"chronic recurrent\" $type_cr>"._("chronic recurrent")."
       <OPTION VALUE=\"historical\"        $type_h >"._("historical")."
      </SELECT>
     </TD>
    </TR>
    </TABLE>
    <P>
   ";

   if ($eocrelauto=="yes") { echo "
      <!-- conditional auto table -->

     <CENTER>
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=CENTER COLSPAN=4 BGCOLOR=\"#777777\">
      <$STDFONT_B SIZE=\"+1\" COLOR=\"#ffffff\">
      "._("Automobile Related Information")."
      <$STDFONT_E>
     </TD>
     </TR>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Auto Insurance")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoname\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautoname\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Case Number")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautocase\" SIZE=10 MAXLENGTH=20
       VALUE=\"$eocrelautocase\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Address (Line 1)")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoaddr1\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautoaddr1\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Contact Name")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautorcname\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautorcname\">
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Address (Line 2)")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoaddr2\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautoaddr2\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Contact Phone")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ".
   fm_phone_entry("eocrelautorcphone")
   ."
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("City, State/Prov, Postal Code")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautocity\" SIZE=10 MAXLENGTH=100
       VALUE=\"$eocrelautocity\"> <B>,</B>
      <INPUT TYPE=TEXT NAME=\"eocrelautostpr\" SIZE=4 MAXLENGTH=3
       VALUE=\"$eocrelautostpr\">
      <INPUT TYPE=TEXT NAME=\"eocrelautozip\" SIZE=11 MAXLENGTH=10
       VALUE=\"$eocrelautozip\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Email Address")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautorcemail\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautorcemail\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Country")."<$STDFONT_E></TD>
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
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=CENTER BGCOLOR=\"#777777\" COLSPAN=4>
     <$STDFONT_B SIZE=\"+1\" COLOR=\"#ffffff\">
     "._("Employment Related Information")."
     <$STDFONT_E>
     </TD>
     </TR>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Name of Employer")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempname\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelempname\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("File Number")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempfile\" SIZE=10 MAXLENGTH=20
       VALUE=\"$eocrelempfile\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Address (Line 1)")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempaddr1\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelempaddr1\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Contact Name")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelemprcname\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelemprcname\">
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Address (Line 2)")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempaddr2\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelempaddr2\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Contact Phone")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ".
   fm_phone_entry("eocrelemprcphone")
   ."
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("City, State/Prov, Postal Code")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempcity\" SIZE=10 MAXLENGTH=100
       VALUE=\"$eocrelempcity\"> <B>,</B>
      <INPUT TYPE=TEXT NAME=\"eocrelempstpr\" SIZE=4 MAXLENGTH=3
       VALUE=\"$eocrelempstpr\">
      <INPUT TYPE=TEXT NAME=\"eocrelempzip\" SIZE=11 MAXLENGTH=10
       VALUE=\"$eocrelempzip\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Email Address")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelemprcemail\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelemprcemail\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Country")."<$STDFONT_E></TD>
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
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=CENTER BGCOLOR=\"#777777\" COLSPAN=4>
     <$STDFONT_B SIZE=\"+1\" COLOR=\"#ffffff\">
     "._("Pregnancy Related Information")."
     <$STDFONT_E>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Length of Cycle")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ".fm_number_select ("eocrelpregcycle", 10, 40)."
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Last Menstrual Period")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ".fm_date_entry("eocrelpreglastper")."
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Gravida")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ".fm_number_select("eocrelpreggravida", 0, 15)."
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Date of Confinement")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_date_entry("eocrelpregconfine");
   echo "
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Para")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ".fm_number_select("eocrelpregpara", 0, 15)."
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Miscarries")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ".fm_number_select("eocrelpregmiscarry", 0, 15)."
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Abortions")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ".fm_number_select("eocrelpregabort", 0, 15)."
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
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=CENTER BGCOLOR=\"#777777\" COLSPAN=4>
     <$STDFONT_B SIZE=\"+1\" COLOR=\"#ffffff\">
      "._("Other Related Information")."
     <$STDFONT_E>
     </TD>
     </TR>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("More Information")."<$STDFONT_E></TD>
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
      <OPTION VALUE=\"$action\">"._("Update")."
      <OPTION VALUE=\"$go\">"._("$this_action")."
      <OPTION VALUE=\"view\">"._("Return to Menu")."
     </SELECT>
     <INPUT TYPE=SUBMIT VALUE=\""._("Go")."\">
     </CENTER>
    ";
   freemed_display_box_bottom ();
   break;

  // REAL ADD AND MODIFY FUNCTIONS ARE BELOW

  case "add": case "mod":
   switch ($action) {
     case "add":
       $this_action = "Adding";
     case "mod":
       $this_action = "Modifying";
   }
   freemed_display_box_top (_("$this_action $record_name"));
   echo "
     <$STDFONT_B>"._("$this_action")."<$STDFONT_E> ...
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
   if ($result) { echo _("Done"); }
    else        { echo _("ERROR"); }
   echo "
     <P>
     <CENTER>
      <A HREF=\"manage.php3?$_auth&id=$patient\"
      ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A> <B>|</B>
      <A HREF=\"$page_name?$_auth&patient=$patient\"
      ><$STDFONT_B>"._("Return to $record_name Menu")."<$STDFONT_E></A>
     </CENTER>
     <BR>
   ";
   freemed_display_box_bottom ();
   break;

  case "del":
   freemed_display_box_top (_("Deleting $record_name"));
   echo "
    <P>
    <$STDFONT_B>"._("Deleting")." ...
    ";
   $query = "DELETE FROM $db_name WHERE id='$id'";
   $result = fdb_query ($query);
   if ($result) { echo _("Done")."\n";    }
    else        { echo _("ERROR")."\n";   }
   echo "
    <$STDFONT_E>
    <P>
    <CENTER>
      <A HREF=\"manage.php3?$_auth&id=$patient\"
      ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A> <B>|</B>
     <A HREF=\"$page_name?$_auth\"
      ><$STDFONT_B>"._("Return to $record_name Menu")."<$STDFONT_E></A>
    </CENTER> 
   ";
   freemed_display_box_bottom ();
   break;

  case "display": // view of entire episode (central control screen)
   if ($id<1) {
     freemed_display_box_top (_("ERROR"));
     echo "
       <P>
       <$STDFONT_B>
       "._("You must specify an ID to view an Episode!")."
       <$STDFONT_E>
       <P>
       <CENTER>
        <A HREF=\"manage.php3?$_auth&id=$patient\"
        ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
       </CENTER>
     ";
     freemed_display_box_bottom ();
     freemed_close_db ();
     freemed_display_html_bottom ();
     DIE("");
   } // end checking for ID as valid
   freemed_display_box_top (_("$record_name View"));

   // create new patient object
   $this_patient = new Patient ($patient);

   // display header of box with patient information
   echo "
     <P>
      <CENTER>
      <$STDFONT_B>"._("Patient")."<$STDFONT_E> :
      <A HREF=\"manage.php3?$_auth&id=$patient\"
      ><$STDFONT_B>".$this_patient->fullName(true)."<$STDFONT_E></A>
      </CENTER>
     <P>
   ";

   $eoc = freemed_get_link_rec($id,"eoc");
   // display vitals for current episode
   echo "
     <P>
     <!-- Vitals Display Table -->
     <TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0 CELLPADDING=1
      ALIGN=CENTER VALIGN=MIDDLE>
     <TR>
      <TD ALIGN=CENTER>
       <$STDFONT_B>"._("Starting Date")."<$STDFONT_E>
      </TD>
      <TD ALIGN=CENTER>
       <$STDFONT_B>"._("Description")."<$STDFONT_E>
      </TD>
     </TR>
     <TR>
      <TD ALIGN=CENTER>
       <$STDFONT_B>$eoc[eocstartdate]<$STDFONT_E>
      </TD>
      <TD ALIGN=CENTER>
       <$STDFONT_B>".fm_prep($eoc[eocdescrip])."<$STDFONT_E>
      </TD>
     </TR>
     </TABLE>
     <!-- End Vitals Display Table -->
     <P>
   ";
   // procedures display
   // special jimmy-rigged query to find in 3d array...
   $query = "SELECT * FROM procrec
             WHERE ((proceoc LIKE '$id:%') OR
                    (proceoc LIKE '%:$id') OR
                    (proceoc LIKE '%:$id:%') OR
                    (proceoc='$id'))
             ORDER BY procdt DESC";
   $result = fdb_query ($query);
  
   $r_name = $record_name; // backup
   $record_name = "Procedure"
   echo freemed_display_itemlist (
     $result,
     "procedure.php3",
     array (
       _("Date") => "procdt",
       _("Procedure") => "proccpt",
       "" => "proccptmod",
       _("Comment") => "proccomment"
     ),
     array (
       "",
       "",
       "",
       _("NO COMMENT")
     ),
     array (
       "",
       "cpt" => "cptcode",
       "cptmod" => "cptmod",
       ""
     ),
   );
   // end of procedures display
   
   echo "
   <P>\n";
   
   // progress notes display
   
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
     
   $record_name = "Progress Notes";
   echo freemed_display_itemlist (
     $result,
     "progress_notes.php3",
     array (
       _("Date") => "pnotesdt"
     ),
     array (
       ""
     ),
   );

   $record_name = $r_name; // restore from backup var

   // end of progress notes display
   // display management link at the bottom...
   echo "
     <P>
     <CENTER>
      <A HREF=\"$page_name?$_auth&patient=$patient\"
      ><$STDFONT_B>"._("Choose Another $record_name")."<$STDFONT_E></A>
     </CENTER>
     <P>
   ";
   freemed_display_box_bottom ();
   break; // end of manage action

  default: // default action -- menu
   if ($patient<1) {
     freemed_display_box_top (_("$record_name :: ERROR"));
     echo "
      <P>
      <$STDFONT_B>"._("Must specify a patient!")."<$STDFONT_E>
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

    $this_patient = new Patient ($patient);
    echo "
      <P>
      <CENTER>
       <$STDFONT_B>"._("Patient")." : <$STDFONT_E>
       <A HREF=\"manage.php3?$_auth&id=$patient\"
       ><$STDFONT_B>".$this_patient->fullName(true)."<$STDFONT_E></A>
      </CENTER>
      <P>
    ".freemed_display_itemlist(
      $result,
      "episode_of_care.php3",
      array (
        _("Starting Date") => "eocstartdate",
	_("Description")   => "eocdescrip"
      ),
      array (
        "",
	_("NO DESCRIPTION")
      )
    );

   freemed_display_box_bottom ();
   break;
 } // end master switch

 freemed_close_db ();
 freemed_display_html_bottom ();
?>
