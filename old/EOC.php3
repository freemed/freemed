<?php
 # file: episode_of_care.php3
 # desc: episode of care database module
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

 $page_name   = "episode_of_care.php3";
 $record_name = "Episode of Care";
 $db_name     = "episodeofcare";

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
      break;
     case "modform":
      $go = "mod";
      $this_action = "$Modify";
       // check to see if an id was submitted
      if ($id<1) {
       freemed_display_box_top ("$record_name :: $ERROR");
       echo "
         You must select a record to modify.
       ";
       freemed_display_box_bottom ();
       freemed_close_db ();
       freemed_display_html_bottom ();
       DIE("");
      } // end of if.. statement checking for id #

      if ($been_here != "yes") {
         // now we extract the data, since the record was given...
        $query  = "SELECT * FROM $database.$db_name WHERE id='$id'";
        $result = fdb_query ($query);
        $r      = fdb_fetch_array ($result);

        $eocdiagfamily  = $r["eocdiagfamily"];  // diagnosis family
        $eoctype        = $r["eoctype"      ];  // episode type

         // authorization 3D array -------------------------------------------
        $eocauthdtb     = fm_split_into_array ($r["eocauthdtb"    ]); 
        $eocauthdte     = fm_split_into_array ($r["eocauthdte"    ]);
          // date splitting for authorization table...
        //for ($split=0;$split<count($eocauthdt_b);$split++) {
        //  $eocauthdtb_y[$split] = substr ($eocauthdtb[$split], 0, 4);
        //  $eocauthdtb_m[$split] = substr ($eocauthdtb[$split], 5, 2);
        //  $eocauthdtb_d[$split] = substr ($eocauthdtb[$split], 8, 2);
        //  $eocauthdte_y[$split] = substr ($eocauthdte[$split], 0, 4);
        //  $eocauthdte_m[$split] = substr ($eocauthdte[$split], 5, 2);
        //  $eocauthdte_d[$split] = substr ($eocauthdte[$split], 8, 2);
        //} // end date splitting for authorization table
        $eocauthvisits  = fm_split_into_array ($r["eocvisits"     ]);
        $eocauthperson  = fm_split_into_array ($r["eocauthperson" ]);
        for ($j=0; $j<=count($eocauthperson); $j++) {
         if ($eocauthperson[$j]<0) {       // for insurance co
           $eocauthperson_ins[$j] = -($eocauthperson[$j]);
           $eocauthperson_phy[$j] = "0";
           $eocauthptype     [$j] = "ins";
         } elseif ($eocauthperson[$j]>0) { // for physicians
           $eocauthperson_ins[$j] = "0";
           $eocauthperson_phy[$j] = $eocauthperson[$j];
           $eocauthptype     [$j] = "phy";
         } else {                          // if blank
           $eocauthperson_ins[$j] = "0";
           $eocauthperson_phy[$j] = "0";
           $eocauthptype     [$j] = "";
         } // end of internal if loop for eocauthperson
        } // end looping for eocauthperson
        $eocauthcomment = fm_split_into_array ($r["eocauthcomment"]);

         // disability 3D array ----------------------------------------------
        $eocdisdtb      = fm_split_into_array ($r["eocdisdtb"     ]);
        $eocdisdte      = fm_split_into_array ($r["eocdisdte"     ]);
          // date splitting for disability table...
        //for ($split=0;$split<count($eocauthdt_b);$split++) {
        //  $eocdisdtb_y[$split] = substr ($eocdisdtb[$split], 0, 4);
        //  $eocdisdtb_m[$split] = substr ($eocdisdtb[$split], 5, 2);
        //  $eocdisdtb_d[$split] = substr ($eocdisdtb[$split], 8, 2);
        //  $eocdisdte_y[$split] = substr ($eocdisdte[$split], 0, 4);
        //  $eocdisdte_m[$split] = substr ($eocdisdte[$split], 5, 2);
        //  $eocdisdte_d[$split] = substr ($eocdisdte[$split], 8, 2);
        //} // end date splitting for authorization table
        $eocdistype     = fm_split_into_array ($r["eocdistype"    ]);
        $eocdistypedur  = fm_split_into_array ($r["eocdistypedur" ]);
        $eocdisbound    = fm_split_into_array ($r["eocdisbound"   ]);
        $eocdispercent  = fm_split_into_array ($r["eocdispercent" ]);
        $eocdiscause    = fm_split_into_array ($r["eocdiscause"   ]);
        $eocdiscomment  = fm_split_into_array ($r["eocdiscomment" ]);

         // facility 3D array ------------------------------------------------
        $eocfacdtadmit  = fm_split_into_array ($r["eocfacdtadmit" ]);
        $eocfacdtdisch  = fm_split_into_array ($r["eocfacdtdisch" ]);
        $eocfaclink     = fm_split_into_array ($r["eocfaclink"    ]);
        $eocfacreleased = fm_split_into_array ($r["eocfacreleased"]);
        $eocfaccomment  = fm_split_into_array ($r["eocfaccomment" ]);

        break;
      } // end checking if we have been here yet...
   } // end of interior switch
   freemed_display_box_top ("$this_action $record_name");

   $auth_prev_line_total = count ($eocauthdtb_y); // previous # of lines
     // display the top of the repetitive table
   if ($auth_prev_line_total == 0) {
     // $authins[0]="ON";  // why isn't this needed?
     $auth_first_insert = true;
   }
   $dis_prev_line_total = count ($eocdisdtb_y); // previous # of lines
     // display the top of the repetitive table
   if ($dis_prev_line_total == 0) {
     // $disins[0]="ON";  // why isn't this needed?
     $dis_first_insert = true;
   }
   $fac_prev_line_total = count ($eocfacdtadmit_y); // previous # of lines
   echo "FAC prev line total = $fac_prev_line_total <BR>";
     // display the top of the repetitive table
   if ($fac_prev_line_total == 0) {
     $eocfaclink[0] = $default_facility; // set by default...
     $fac_first_insert = true;
   }

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
   $ptinfo = freemed_get_link_rec ($patient, "patient");
   $ptlname = $ptinfo ["ptlname"];
   $ptfname = $ptinfo ["ptfname"];
   $ptmname = $ptinfo ["ptmname"];
   $ptdob   = $ptinfo ["ptdob"  ];

   echo "
    <CENTER>
     <B>Patient:</B> 
     <A HREF=\"manage.php3?$_auth&id=$patient\"
      >$ptlname, $ptfname $ptmname [$ptdob]</A>
    </CENTER><P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"yes\">
     <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"$id\">
     <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
    <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Description<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocdescrip\" SIZE=25 MAXLENGTH=100
       VALUE=\"".fm_prep($eocdescrip)."\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Related to Pregnancy?<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <SELECT NAME=\"eocrelpreg\">
       <OPTION VALUE=\"no\"  $eocrelpreg_n>No
       <OPTION VALUE=\"yes\" $eocrelpreg_y>Yes
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Date of First Occurance<$STDFONT_E></TD>
      <TD ALIGN=LEFT>
  ";
  fm_date_entry("eocstartdate");
  echo "
      <!-- <INPUT TYPE=TEXT NAME=\"eocstartdate_y\" SIZE=5 MAXLENGTH=4
       VALUE=\"".fm_prep($eocstartdate_y)."\"> <B>-</B>
      <INPUT TYPE=TEXT NAME=\"eocstartdate_m\" SIZE=3 MAXLENGTH=2
       VALUE=\"".fm_prep($eocstartdate_m)."\"> <B>-</B>
      <INPUT TYPE=TEXT NAME=\"eocstartdate_d\" SIZE=3 MAXLENGTH=2
       VALUE=\"".fm_prep($eocstartdate_d)."\"> -->
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Related to Employment<$STDFONT_E></TD>
      <TD ALIGN=LEFT>
      <SELECT NAME=\"eocrelemp\">
       <OPTION VALUE=\"no\"  $eocrelemp_n>No
       <OPTION VALUE=\"yes\" $eocrelemp_y>Yes
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Date of Last Similar<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_date_entry("eocdtlastsimilar");
   echo "
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Related to Automobile Accident<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
     <SELECT NAME=\"eocrelauto\">
       <OPTION VALUE=\"no\"  $eocrelauto_n>No
       <OPTION VALUE=\"yes\" $eocrelauto_y>Yes
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Referring Physician<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <SELECT NAME=\"eocreferrer\">
   ";
   freemed_display_physicians ($eocreferrer);
   echo "
      </SELECT>
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Related to Other Cause<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
     <SELECT NAME=\"eocrelother\">
       <OPTION VALUE=\"no\"  $eocrelother_n>No
       <OPTION VALUE=\"yes\" $eocrelother_y>Yes
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Facility<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <SELECT NAME=\"eocfacility\">
   ";
   if (empty($eocfacility)) $eocfacility = $default_facility;
   freemed_display_facilities ($eocfacility);
   echo "
      </SELECT>
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>State/Providence<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelstpr\" SIZE=5 MAXLENGTH=5
       VALUE=\"$eocrelstpr\">
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Diagnosis Family<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
    ";
    // compact and display eocdiagfamily
    $eocdiagfamily = fm_join_from_array ($eocdiagfamily);
    freemed_multiple_choice ("SELECT * FROM $database.diagfamily
      ORDER BY dfname, dfdescrip", "dfname:dfdescrip", "eocdiagfamily",
      $eocdiagfamily, false);
    echo "
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Episode Type<$STDFONT_E></TD>
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
       <OPTION VALUE=\"\"                          >NONE SELECTED
       <OPTION VALUE=\"acute\"             $type_a >acute
       <OPTION VALUE=\"chronic\"           $type_c >chronic
       <OPTION VALUE=\"chronic recurrent\" $type_cr>chronic recurrent
       <OPTION VALUE=\"historical\"        $type_h >historical
      </SELECT>
     </TD>
    </TR>
    </TABLE>
    <P>

    <!-- virtual 3D SQL table for Authorizations -->

    <CENTER>
    <P>
     <$STDFONT_B><B>Authorizations</B><$STDFONT_E>
    <BR>
    </CENTER>

    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=4 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
     <TR BGCOLOR=#000000>
      <TD><$STDFONT_B COLOR=#ffffff>#<$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><CENTER><B>Ins/Del</B></CENTER>
        <$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Date Begin/End</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Auth #</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B># Approved Visits</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Auth Party (phy/ins)</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Comment</B><$STDFONT_E></TD>
     </TR>
    ";

   $i = 0;              // zero the loop variable
   $cur_line_count = 0; // zero the current line count (displayed)

   while (($i < $auth_prev_line_total) OR ($auth_first_insert)) {
     if (!fm_value_in_array ($authdel, $i)) {
      // check for problems ...
      if ( (empty($eocauthdtb_y[$i])) or
           (empty($eocauthdtb_m[$i])) or
           (empty($eocauthdtb_d[$i])) )
                                  { $num_color = "#ff0000"; }
       else                       { $num_color = "#000000"; }
      // select proper checked...
      $ptype_phy=""; $ptype_ins="";
      if ($eocauthptype[$i]=="phy") $ptype_phy="CHECKED";
      if ($eocauthptype[$i]=="ins") $ptype_ins="CHECKED";
      // print actual record
      $_alternate = freemed_bar_alternate_color ($_alternate);
      echo "
       <TR BGCOLOR=\"$_alternate\">
        <TD><$STDFONT_B COLOR=\"$num_color\">".($cur_line_count+1)."<$STDFONT_E></TD>
        <TD><CENTER>
            <INPUT TYPE=CHECKBOX NAME=\"authins$brackets\"
             VALUE=\"$cur_line_count\">
            <INPUT TYPE=CHECKBOX NAME=\"authdel$brackets\"
             VALUE=\"$cur_line_count\"></CENTER></TD>
        <TD>
      ";
      fm_date_entry("eocauthdtb", $i);
      echo "
        <BR>
      ";
      fm_date_entry("eocauthdte", $i);
      echo "
        </TD>
        <TD><INPUT TYPE=TEXT NAME=\"eocauthnum$brackets\" SIZE=10
          MAXLENGTH=30 VALUE=\"".fm_prep($eocauthnum[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"eocauthvisits$brackets\" SIZE=5
          MAXLENGTH=3 VALUE=\"".fm_prep($eocauthvisits[$i])."\"></TD>
        <TD>
          <!-- phy or insco -->
          <INPUT TYPE=RADIO NAME=\"eocauthptype$brackets\" VALUE=\"phy\" $ptype_phy>
           <SELECT NAME=\"eocauthperson_phy$brackets\">
       ";
       freemed_display_physicians($eocauthperson_phy[$i]);
       echo "
           </SELECT><BR>
          <INPUT TYPE=RADIO NAME=\"eocauthptype$brackets\" VALUE=\"ins\" $ptype_ins>
           <SELECT NAME=\"eocauthperson_ins$brackets\">
       ";
       freemed_display_insco($eocauthperson_ins[$i]);
       echo "
           </SELECT>
        </TD>
        <TD><INPUT TYPE=TEXT NAME=\"eocauthcomment$brackets\" SIZE=15
          MAXLENGTH=100 VALUE=\"".fm_prep($eocauthcomment[$i])."\"></TD>
       </TR>
       ";
       $cur_line_count++;
     } // end checking for delete to display
     if (fm_value_in_array($authins, $i)) { // if there is an insert
       $_alternate = freemed_bar_alternate_color ($_alternate);
       echo "
        <TR BGCOLOR=\"$_alternate\">
         <TD><$STDFONT_B COLOR=\"#ff0000\">".($cur_line_count+1)."<$STDFONT_E></TD>
         <TD><CENTER><INPUT TYPE=CHECKBOX NAME=\"authins$brackets\"
              VALUE=\"$cur_line_count\">
             <INPUT TYPE=CHECKBOX NAME=\"authdel$brackets\"
              VALUE=\"$cur_line_count\"></CENTER></TD>
         <TD>
      ";
      fm_date_entry("eocauthdtb", -2);
      echo "
         <BR>
      ";
      fm_date_entry("eocauthdte", -2);
      echo "
         </TD>
         <TD><INPUT TYPE=TEXT NAME=\"eocauthnum$brackets\" SIZE=10
           MAXLENGTH=30 VALUE=\"\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"eocauthvisits$brackets\" SIZE=5
           MAXLENGTH=3 VALUE=\"\"></TD>
         <TD>
          <!-- phy or insco -->
          <INPUT TYPE=RADIO NAME=\"eocauthptype$brackets\" VALUE=\"phy\">
           <SELECT NAME=\"eocauthperson_phy$brackets\">
       ";
       freemed_display_physicians();
       echo "
           </SELECT><BR>
          <INPUT TYPE=RADIO NAME=\"eocauthptype$brackets\" VALUE=\"ins\">
           <SELECT NAME=\"eocauthperson_ins$brackets\">
       ";
       freemed_display_insco();
       echo "
           </SELECT>
        </TD>
         <TD><INPUT TYPE=TEXT NAME=\"eocauthcomment$brackets\" SIZE=15
           MAXLENGTH=100 VALUE=\"\"></TD>
        </TR>
       ";
       $cur_line_count++;
     } // end of insert
     $i++;                       // increase loop
     $auth_first_insert = false; // to be sure of _no_ endless looping
   } // end of while

    // display the bottom of the repetitive table
   echo "
     </TABLE>
   ";

   // beginning of DISABILITY 3D table ---------------------------------------
   // ------------------------------------------------------------------------

   echo "
    <!-- virtual 3D SQL table for Disabilities -->

    <CENTER>
    <P>
     <$STDFONT_B><B>Disabilities</B><$STDFONT_E>
    <BR>
    </CENTER>

    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=4 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
     <TR BGCOLOR=#000000>
      <TD><$STDFONT_B COLOR=#ffffff>#<$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><CENTER><B>Ins/Del</B></CENTER>
        <$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Date Begin/End</B>
        <$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Type</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>% Body</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Cause</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Comment</B><$STDFONT_E></TD>
     </TR>
    ";
   $i = 0;              // zero the loop variable
   $cur_line_count = 0; // zero the current line count (displayed)

   while (($i < $dis_prev_line_total) OR ($dis_first_insert)) {
     if (!fm_value_in_array ($facdel, $i)) {
      // check for problems ...
      if ( (empty($eocdisdtb_y[$i]))   or 
           (empty($eocdisdtb_m[$i]))   or
           (empty($eocdisdtb_d[$i]))   or
           (empty($eocdistype[$i]))    or
           (empty($eocdistypedur[$i])) or
           ($eocdispercent[$i]<=0)     or
           ($eocdispercent[$i]>100)    )
                                  { $num_color = "#ff0000"; }
       else                       { $num_color = "#000000"; }
      // print actual record
      $_alternate = freemed_bar_alternate_color ($_alternate);
      echo "
       <TR BGCOLOR=\"$_alternate\">
        <TD><$STDFONT_B COLOR=\"$num_color\">".($cur_line_count+1)."<$STDFONT_E></TD>
        <TD><CENTER>
            <INPUT TYPE=CHECKBOX NAME=\"disins$brackets\"
             VALUE=\"$cur_line_count\">
            <INPUT TYPE=CHECKBOX NAME=\"disdel$brackets\"
             VALUE=\"$cur_line_count\"></CENTER></TD>
        <TD>
      ";
      fm_date_entry("eocdisdtb", $i);
      echo "
          <BR>
      ";
      fm_date_entry("eocdisdte", $i);
      echo "
        </TD>
        <TD>
       ";
       $t_n = $t_p = $t_t = $td_n = $td_t = $tp_p = $homebound = "";
       switch ($eocdistype[$i]) {
         case "":          $t_n = "SELECTED"; break;
         case "permanent": $t_p = "SELECTED"; break;
         case "temporary": $t_t = "SELECTED"; break;
       }
       switch ($eocdistypedur[$i]) {
         case "":        $td_n = "SELECTED"; break;
         case "total":   $td_t = "SELECTED"; break;
         case "partial": $td_p = "SELECTED"; break;
       }
       if (strtolower($eocdisbound[$i]=="on")) $homebound="CHECKED";
       echo "
         <SELECT NAME=\"eocdistype$brackets\">
          <OPTION VALUE=\"\"          $t_n>--SELECT--
          <OPTION VALUE=\"permanant\" $t_p>permanent
          <OPTION VALUE=\"temporary\" $t_t>temporary
         </SELECT>
          <BR>
         <SELECT NAME=\"eocdistypedur$brackets\">
          <OPTION VALUE=\"\"         $td_n>--SELECT--
          <OPTION VALUE=\"total\"    $td_t>total
          <OPTION VALUE=\"partial\"  $td_p>partial
         </SELECT>
          <BR>
         <INPUT NAME=\"eocdisbound$brackets\" TYPE=CHECKBOX
          $homebound>Homebound 
        </TD>
        <TD><INPUT TYPE=TEXT NAME=\"eocdispercent$brackets\" SIZE=4
          MAXLENGTH=3 VALUE=\"".fm_prep($eocdispercent[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"eocdiscause$brackets\" SIZE=10
          MAXLENGTH=100 VALUE=\"".fm_prep($eocdiscause[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"eocdiscomment$brackets\" SIZE=10
          MAXLENGTH=100 VALUE=\"".fm_prep($eocdiscomment[$i])."\"></TD>
       </TR>
       ";
       $cur_line_count++;
     } // end checking for delete to display
     if (fm_value_in_array($disins, $i)) { // if there is an insert
       $_alternate = freemed_bar_alternate_color ($_alternate);
       echo "
        <TR BGCOLOR=\"$_alternate\">
         <TD><$STDFONT_B COLOR=\"#ff0000\">".($cur_line_count+1)."<$STDFONT_E></TD>
         <TD><CENTER><INPUT TYPE=CHECKBOX NAME=\"disins$brackets\"
              VALUE=\"$cur_line_count\">
             <INPUT TYPE=CHECKBOX NAME=\"disdel$brackets\"
              VALUE=\"$cur_line_count\"></CENTER></TD>
         <TD>
       ";
       fm_date_entry("eocdisdtb", -2);
       echo "
         <BR>
       ";
       fm_date_entry("eocdisdte", -2);
       echo "
         </TD>
         <TD>
         <SELECT NAME=\"eocdistype$brackets\">
          <OPTION VALUE=\"\"         >--SELECT--
          <OPTION VALUE=\"permanant\">permanent
          <OPTION VALUE=\"temporary\">temporary
         </SELECT>
          <BR>
         <SELECT NAME=\"eocdistypedur$brackets\">
          <OPTION VALUE=\"\"       >--SELECT--
          <OPTION VALUE=\"total\"  >total
          <OPTION VALUE=\"partial\">partial
         </SELECT>
          <BR>
         <INPUT NAME=\"eocdisbound$brackets\" TYPE=CHECKBOX
          >Homebound 
         </TD>
         <TD><INPUT TYPE=TEXT NAME=\"eocdispercent$brackets\" SIZE=4
           MAXLENGTH=3 VALUE=\"".fm_prep($eocdispercent[$i])."\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"eocdiscause$brackets\" SIZE=10
           MAXLENGTH=100 VALUE=\"".fm_prep($eocdiscause[$i])."\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"eocdiscomment$brackets\" SIZE=10
           MAXLENGTH=100 VALUE=\"\"></TD>
        </TR>
       ";
       $cur_line_count++;
     } // end of insert
     $i++;                       // increase loop
     $dis_first_insert = false;  // to be sure of _no_ endless looping
   } // end of while

    // display the bottom of the repetitive table
   echo "
     </TABLE>
   ";

   // ------------------------------------------------------------------------
   // end of DISABILITY 3D table ---------------------------------------------


   // beginning of FACILITY 3D table -----------------------------------------
   // ------------------------------------------------------------------------
   echo "
    <!-- virtual 3D SQL table for Facilities -->

    <CENTER>
    <P>
     <$STDFONT_B><B>Facilities</B><$STDFONT_E>
    <BR>
    </CENTER>

    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=4 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
     <TR BGCOLOR=#000000>
      <TD><$STDFONT_B COLOR=#ffffff>#<$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><CENTER><B>Ins/Del</B></CENTER>
        <$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Date Admitted/Discharged</B>
        <$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Facility</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Released Date/To</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Comment</B><$STDFONT_E></TD>
     </TR>
    ";
   $i = 0;              // zero the loop variable
   $cur_line_count = 0; // zero the current line count (displayed)

   while (($i < $fac_prev_line_total) OR ($fac_first_insert)) {
     if (!fm_value_in_array ($facdel, $i)) {
      // check for problems ...
      if ( (empty($eocfacdtadmit_y[$i])) or 
           (empty($eocfacdtadmit_m[$i])) or
           (empty($eocfacdtadmit_d[$i])) )
                                  { $num_color = "#ff0000"; }
       else                       { $num_color = "#000000"; }
      // print actual record
      $_alternate = freemed_bar_alternate_color ($_alternate);
      echo "
       <TR BGCOLOR=\"$_alternate\">
        <TD><$STDFONT_B COLOR=\"$num_color\">".($cur_line_count+1)."<$STDFONT_E></TD>
        <TD><CENTER>
            <INPUT TYPE=CHECKBOX NAME=\"facins$brackets\"
             VALUE=\"$cur_line_count\">
            <INPUT TYPE=CHECKBOX NAME=\"facdel$brackets\"
             VALUE=\"$cur_line_count\"></CENTER></TD>
        <TD>
      ";
      fm_date_entry("eocfacdtadmit", $i);
      echo "
          <BR>
      ";
      fm_date_entry("eocfacdtdisch", $i);
      echo "
        </TD>
        <TD>
         <SELECT NAME=\"eocfaclink$brackets\">
       ";
       freemed_display_facilities ($eocfaclink[$i]);
       echo "
         </SELECT>
        </TD>
        <TD>
       ";
       fm_date_entry("eocfacreleased", $i);
       echo " 
        <BR>
       ";
       // fix up release to dates...
       $frto_home = $frto_nurs = $frto_hosp = $frto_tran = $frto_deat = "";
       switch($eocreleaseto[$i]) {
         case "home":      $frto_home = "SELECTED"; break;
         case "nursing":   $frto_nurs = "SELECTED"; break;
         case "hospital":  $frto_hosp = "SELECTED"; break;
         case "transfer":  $frto_tran = "SELECTED"; break;
         case "death":     $frto_deat = "SELECTED"; break;
       } // end switch for release to...
       echo "
         <SELECT NAME=\"eocfacreleaseto\">
          <OPTION VALUE=\"home\"     $frto_home>home
          <OPTION VALUE=\"nursing\"  $frto_nurs>nursing home
          <OPTION VALUE=\"hospital\" $frto_hosp>hospital
          <OPTION VALUE=\"transfer\" $frto_tran>transfer
          <OPTION VALUE=\"death\"    $frto_deat>death
         </SELECT>
        </TD>
        <TD><INPUT TYPE=TEXT NAME=\"eocfaccomment$brackets\" SIZE=10
          MAXLENGTH=100 VALUE=\"".fm_prep($eocfaccomment[$i])."\"></TD>
       </TR>
       ";
       $cur_line_count++;
     } // end checking for delete to display
     if (fm_value_in_array($facins, $i)) { // if there is an insert
       $_alternate = freemed_bar_alternate_color ($_alternate);
       echo "
        <TR BGCOLOR=\"$_alternate\">
         <TD><$STDFONT_B COLOR=\"#ff0000\">".($cur_line_count+1)."<$STDFONT_E></TD>
         <TD><CENTER><INPUT TYPE=CHECKBOX NAME=\"facins$brackets\"
              VALUE=\"$cur_line_count\">
             <INPUT TYPE=CHECKBOX NAME=\"facdel$brackets\"
              VALUE=\"$cur_line_count\"></CENTER></TD>
         <TD>
       ";
       fm_date_entry("eocfacdtadmit", -2);
       echo "
         <BR>
       ";
       fm_date_entry("eocfacdtdisch", -2);
       echo "
         </TD>
         <TD>
          <SELECT NAME=\"eocfaclink$brackets\">
        ";
        freemed_display_facilities ();
        echo "
          </SELECT>
         </TD>
         <TD>
        ";
        fm_date_entry("eocfacreleased", -2);
        echo "
         <BR>
         <SELECT NAME=\"eocfacreleaseto\">
          <OPTION VALUE=\"home\"    >home
          <OPTION VALUE=\"nursing\" >nursing home
          <OPTION VALUE=\"hospital\">hospital
          <OPTION VALUE=\"transfer\">transfer
          <OPTION VALUE=\"death\"   >death
         </SELECT>
         </TD>
         <TD><INPUT TYPE=TEXT NAME=\"eocfaccomment$brackets\" SIZE=10
           MAXLENGTH=100 VALUE=\"\"></TD>
        </TR>
       ";
       $cur_line_count++;
     } // end of insert
     $i++;                       // increase loop
     $fac_first_insert = false;  // to be sure of _no_ endless looping
   } // end of while

    // display the bottom of the repetitive table
   echo "
     </TABLE>
   ";

   // ------------------------------------------------------------------------
   // end of FACILITY 3D table -----------------------------------------------

   if ($eocrelauto=="yes") echo "
      <!-- conditional auto table -->

     <CENTER>
     <P>
      <$STDFONT_B><B>Automobile Related Information</B><$STDFONT_E>
     <BR>
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Auto Insurance<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoname\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautoname\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Case Number<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautocase\" SIZE=10 MAXLENGTH=20
       VALUE=\"$eocrelautocase\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Address (Line 1)<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoaddr1\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautoaddr1\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Contact Name<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautorcname\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautorcname\">
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Address (Line 2)<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoaddr2\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautoaddr2\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Contact Phone #<$STDFONT_E></TD>
     <TD ALIGN=LEFT><B>(</B>
      <INPUT TYPE=TEXT NAME=\"eocrelautorcphone1\" SIZE=4 MAXLENGTH=3
       VALUE=\"".fm_prep($eocrelautorcphone1)."\"> <B>)</B>
      <INPUT TYPE=TEXT NAME=\"eocrelautorcphone2\" SIZE=4 MAXLENGTH=3
       VALUE=\"".fm_prep($eocrelautorcphone2)."\"> <B>-</B>
      <INPUT TYPE=TEXT NAME=\"eocrelautorcphone3\" SIZE=5 MAXLENGTH=4
       VALUE=\"".fm_prep($eocrelautorcphone3)."\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>City, St/Pr,<BR>Postal Code<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautocity\" SIZE=10 MAXLENGTH=100
       VALUE=\"$eocrelautocity\"> <B>,</B>
      <INPUT TYPE=TEXT NAME=\"eocrelautostpr\" SIZE=4 MAXLENGTH=3
       VALUE=\"$eocrelautostpr\">
      <INPUT TYPE=TEXT NAME=\"eocrelautozip\" SIZE=11 MAXLENGTH=10
       VALUE=\"$eocrelautozip\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Email Address<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautorcemail\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelautorcemail\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Country<$STDFONT_E></TD>
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
   "; // end of conditional auto info



   if ($eocrelemp=="yes") echo "
      <!-- conditional employment table -->

     <CENTER>
     <P>
      <$STDFONT_B><B>Employment Related Information</B><$STDFONT_E>
     <BR>
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Name of Employer<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempname\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelempname\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>File Number<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempfile\" SIZE=10 MAXLENGTH=20
       VALUE=\"$eocrelempfile\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Address (Line 1)<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempaddr1\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelempaddr1\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Contact Name<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelemprcname\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelemprcname\">
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Address (Line 2)<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempaddr2\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelempaddr2\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Contact Phone #<$STDFONT_E></TD>
     <TD ALIGN=LEFT><B>(</B>
      <INPUT TYPE=TEXT NAME=\"eocrelemprcphone1\" SIZE=4 MAXLENGTH=3
       VALUE=\"".fm_prep($eocrelemprcphone1)."\"> <B>)</B>
      <INPUT TYPE=TEXT NAME=\"eocrelemprcphone2\" SIZE=4 MAXLENGTH=3
       VALUE=\"".fm_prep($eocrelemprcphone2)."\"> <B>-</B>
      <INPUT TYPE=TEXT NAME=\"eocrelemprcphone3\" SIZE=5 MAXLENGTH=4
       VALUE=\"".fm_prep($eocrelemprcphone3)."\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>City, St/Pr,<BR>Postal Code<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempcity\" SIZE=10 MAXLENGTH=100
       VALUE=\"$eocrelempcity\"> <B>,</B>
      <INPUT TYPE=TEXT NAME=\"eocrelempstpr\" SIZE=4 MAXLENGTH=3
       VALUE=\"$eocrelempstpr\">
      <INPUT TYPE=TEXT NAME=\"eocrelempzip\" SIZE=11 MAXLENGTH=10
       VALUE=\"$eocrelempzip\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Email Address<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelemprcemail\" SIZE=20 MAXLENGTH=100
       VALUE=\"$eocrelemprcemail\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Country<$STDFONT_E></TD>
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
   "; // end of conditional employment info




   if ($eocrelpreg=="yes") echo "
      <!-- conditional pregnancy table -->

     <CENTER>
     <P>
      <$STDFONT_B><B>Pregnancy Related Information</B><$STDFONT_E>
     <BR>
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Length of Cycle (days)<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelpregcycle\" SIZE=3 MAXLENGTH=2
       VALUE=\"$eocrelpregcycle\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Last Menstrual Period<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_date_entry("eocrelpreglastper");
   echo "
      <!-- <INPUT TYPE=TEXT NAME=\"eocrelpreglastper_y\" SIZE=5 MAXLENGTH=4
       VALUE=\"".fm_prep($eocrelpreglastper_y)."\"> <B>-</B>
      <INPUT TYPE=TEXT NAME=\"eocrelpreglastper_m\" SIZE=3 MAXLENGTH=2
       VALUE=\"".fm_prep($eocrelpreglastper_m)."\"> <B>-</B>
      <INPUT TYPE=TEXT NAME=\"eocrelpreglastper_d\" SIZE=3 MAXLENGTH=2
       VALUE=\"".fm_prep($eocrelpreglastper_d)."\"> -->
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Gravida<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelpreggravida\" SIZE=3 MAXLENGTH=2
       VALUE=\"$eocrelpreggravida\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Date of Confinement<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ";
   fm_date_entry("eocrelpregconfine");
   echo "
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Para<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelpregpara\" SIZE=3 MAXLENGTH=2
       VALUE=\"$eocrelpregpara\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Miscarries<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelpregmiscarry\" SIZE=3 MAXLENGTH=2
       VALUE=\"$eocrelpregmiscarry\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Abortions<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelpregabort\" SIZE=3 MAXLENGTH=2
       VALUE=\"$eocrelpregabort\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>&nbsp; <!-- placeholder --><$STDFONT_E></TD>
     <TD ALIGN=LEFT>
       &nbsp; <!-- placeholder -->
     </TD>
     </TR>
     </TABLE>
     </CENTER>
   "; // end of conditional pregnancy info

   if ($eocrelother=="yes") echo "
      <!-- conditional employment table -->

     <CENTER>
     <P>
      <$STDFONT_B><B>Employment Related Information</B><$STDFONT_E>
     <BR>
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>More Information<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelothercomment\" SIZE=35 MAXLENGTH=100
       VALUE=\"$eocrelothercomment\">
     </TD>
     </TR>
    </TABLE>
    ";

   echo "
     <P>
     <CENTER>
     <SELECT NAME=\"action\">
      <OPTION VALUE=\"$action\">Update
      <OPTION VALUE=\"$go\">$this_action
      <OPTION VALUE=\"view\">Back to Menu
     </SELECT>
     <INPUT TYPE=SUBMIT VALUE=\"go!\">
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

     // assemble all 3D SQL array dates
   for ($i=0;$i<count($eocauthdtb_y);$i++)
     $eocauthdtb[$i]    = fm_date_assemble ("eocauthdtb",    $i);
   for ($i=0;$i<count($eocauthdte_y);$i++)
     $eocauthdte[$i]    = fm_date_assemble ("eocauthdte",    $i);
   for ($i=0;$i<count($eocdisdtb_y);$i++)
     $eocdisdtb[$i]     = fm_date_assemble ("eocdisdtb",     $i);
   for ($i=0;$i<count($eocdisdte_y);$i++)
     $eocdisdte[$i]     = fm_date_assemble ("eocdisdte",     $i);
   for ($i=0;$i<count($eocfacdtadmit_y);$i++)
     $eocfacdtadmit[$i] = fm_date_assemble ("eocfacdtadmit", $i);
   for ($i=0;$i<count($eocfacdtdisch_y);$i++)
     $eocfacdtdisch[$i] = fm_date_assemble ("eocfacdtdisch", $i);

   // squoosh arrays into values...
   $eocauthdtb_a        = fm_join_from_array ($eocauthdtb);
   $eocauthdte_a        = fm_join_from_array ($eocauthdte);
   $eocauthvisits_a     = fm_join_from_array ($eocauthvisits);
   $eocauthperson_a     = fm_join_from_array ($eocauthperson);
   $eocauthcomment_a    = fm_join_from_array ($eocauthcomment);

   $eocdisdtb_a         = fm_join_from_array ($eocdisdtb);
   $eocdisdte_a         = fm_join_from_array ($eocdisdte);
   $eocdistype_a        = fm_join_from_array ($eocdistype);
   $eocdistypedur_a     = fm_join_from_array ($eocdistypedur);
   $eocdisbound_a       = fm_join_from_array ($eocdisbound);
   $eocdispercent_a     = fm_join_from_array ($eocdispercent);
   $eocdiscause_a       = fm_join_from_array ($eocdiscause);
   $eocdiscomment_a     = fm_join_from_array ($eocdiscomment);

   $eocfacdtadmit_a     = fm_join_from_array ($eocfacdtadmit);
   $eocfacdtdisch_a     = fm_join_from_array ($eocfacdtdisch);
   $eocfaclink_a        = fm_join_from_array ($eocfaclink);
   $eocfacreleased      = fm_join_from_array ($eocfacreleased);
   $eocfacreleaseto     = fm_join_from_array ($eocfacreleaseto);
   $eocfaccomment       = fm_join_from_array ($eocfaccomment);

   // move patient over
   $eocpatient = $patient;

   switch ($action) {
    case "add":
     $query = "INSERT INTO $database.$db_name VALUES (
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

       '".addslashes($eocauthdtb_a)               ."',
       '".addslashes($eocauthdte_a)               ."',
       '".addslashes($eocauthvisits_a)            ."',
       '".addslashes($eocauthperson_a)            ."',
       '".addslashes($eocautcomment_a)            ."',

       '".addslashes($eocdisdtb_a)                ."',
       '".addslashes($eocdisdte_a)                ."',
       '".addslashes($eocdistype_a)               ."',
       '".addslashes($eocdistypedur_a)            ."',
       '".addslashes($eocdisbound_a)              ."',
       '".addslashes($eocdispercent_a)            ."',
       '".addslashes($eocdiscause_a)              ."',
       '".addslashes($eocdiscomment_a)            ."',

       '".addslashes($eocfacdtadmit_a)            ."',
       '".addslashes($eocfacdtdisch_a)            ."',
       '".addslashes($eocfaclink_a)               ."',
       '".addslashes($eocfacreleased_a)           ."',
       '".addslashes($eocfacreleasedto_a)         ."',
       '".addslashes($eocfaccomment_a)            ."',

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
      $query = "UPDATE $database.$db_name SET
        value         = '".addslashes($value)."'
        WHERE id='$id'";
      break;
   } // end of action switch...

   $result = fdb_query ($query);
   if ($result) { echo "$Done."; }
    else        { echo "$ERROR"; }
   echo "
     <P>
     <CENTER><A HREF=\"$page_name?$_auth\"
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
   $query = "DELETE * FROM $database.$db_name WHERE id='$id'";
   $result = fdb_query ($query);
   if ($result) { echo "$Done\n";    }
    else        { echo "$ERROR\n";   }
   echo "
    <$STDFONT_E>
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
      ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER> 
   ";
   freemed_display_box_bottom ();
   break;

  default: // default action -- menu
   if ($patient<1) {
     freemed_display_box_top ("$record_name :: $ERROR");
     echo "
      <P>
      <$STDFONT_B>You must specify a patient!<$STDFONT_E>
      <P>
     ";
     freemed_display_box_bottom ();
     freemed_close_db ();
     freemed_display_html_bottom ();
     DIE ("");
   } // kick the bucket if no patient

   freemed_display_box_top ("$record_name");
   $result = fdb_query ("SELECT * FROM $database.$db_name
                         ORDER BY eocstartdate WHERE
                         eocpatient='$patient'");
   if (fdb_num_rows($result)>0) {

    // display action bar
    freemed_display_actionbar ();

    // display table top
    echo "
      <P>
      <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=2 BORDER=0
       BGCOLOR=#000000 VALIGN=MIDDLE ALIGN=CENTER>
      <TR BGCOLOR=#000000>
       <TD BGCOLOR=#000000>
        <$STDFONT_B COLOR=#ffffff>Starting Date<$STDFONT_E></TD>
       <TD BGCOLOR=#000000>
        <$STDFONT_B COLOR=#ffffff>Description<$STDFONT_E></TD>
       <TD BGCOLOR=#000000>
        <$STDFONT_B COLOR=#ffffff>Action<$STDFONT_E></TD>
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
        <A HREF=\"$page_name?$_auth&action=modform&id=$id\"
         ><$STDFONT_B SIZE=-1>$lang_MOD<$STDFONT_E></A>
       ";

      if (freemed_get_userlevel($LoginCookie)>$delete_level)
       echo "
        <A HREF=\"$page_name?$_auth&action=del&id=$id\"
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
       <B><$STDFONT_B>No $record_name for specified patient.<$STDFONT_E></B>
       <P>
       <A HREF=\"$page_name?$_auth&action=addform\"
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
