<?php
 # file: procedure.php3
 # desc: procedure database interface
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

 $page_name   = "procedure.php3";
 $db_name     = "procedure";
 $record_name = "Procedure";
 include ("global.var.inc");
 include ("freemed-functions.inc");  // API

 freemed_open_db ($LoginCookie);
 $this_user = new User ($LoginCookie);

 freemed_display_html_top ();
 freemed_display_banner ();

if ($patient<1) {
  freemed_display_box_top ("$record_name :: $ERROR");
  echo "
   <P>
   <CENTER>
   <$STDFONT_B>You must select a patient!<$STDFONT_E>
   </CENTER>
   <P>
   <CENTER>
    <A HREF=\"patient.php3?$_auth\"
    ><$STDFONT_B>Select a Patient<$STDFONT_E></A>
   </CENTER>
   <P>
  ";
  freemed_display_box_bottom ();
  DIE("");
} else { // if there was a patient found
  $this_patient = new Patient ($patient);
} // end checking for patient

switch ($action) { // master action switch
 case "addform": case "modform": // add or modify form
  switch ($action) { // inner action switch
   case "addform":
    $next_action = "addform2";
    $this_action = "$Add";
    $procunits = "1.0";        // default value for units
    $procdiag1      = $this_patient->local_record[ptdiag1];
    $procdiag2      = $this_patient->local_record[ptdiag2];
    $procdiag3      = $this_patient->local_record[ptdiag3];
    $procdiag4      = $this_patient->local_record[ptdiag4];
    break; // end of addform (inner)
   case "modform":
    $next_action = "modform2";
    $this_action = "$Modify";
    $this_data = freemed_get_link_rec ($id, $db_name);
    // extract all of the data
    $procpatient    = $this_data["procpatient"   ];
    $proceoc        = $this_data["proceoc"       ];
    $proccpt        = $this_data["proccpt"       ];
    $proccptmod     = $this_data["proccptmod"    ];
    $procdiag1      = $this_data["procdiag1"     ];
    $procdiag2      = $this_data["procdiag2"     ];
    $procdiag3      = $this_data["procdiag3"     ];
    $procdiag4      = $this_data["procdiag4"     ];
    $proccharges    = $this_data["proccharges"   ];
    $procunits      = $this_data["procunits"     ];
    $procvoucher    = $this_data["procvoucher"   ];
    $procphysician  = $this_data["procphysician" ];
    $procdt         = $this_data["procdt"        ];
    $procpos        = $this_data["procpos"       ];
    $proccomment    = $this_data["proccomment"   ];
    $procbalorig    = $this_data["procbalorig"   ];
    $procbalcurrent = $this_data["procbalcurrent"];
    $procamtpaid    = $this_data["procamtpaid"   ];
    $procbilled     = $this_data["procbilled"    ];
    $procauth       = $this_data["procauth"      ];
    $procrefdoc     = $this_data["procrefdoc"    ];
    $procrefdt      = $this_data["procrefdt"     ];
    break; // end of modform (inner)
  } // inner action switch
  freemed_display_box_top ("$this_action $record_name");
  echo "
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
    <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"$id\">
    <P>
    <CENTER>
     <$STDFONT_B>$Patient : <$STDFONT_E>
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>".$this_patient->fullName(true)."<$STDFONT_E></A>
    </CENTER>
    <P>

    <TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
     ALIGN=CENTER>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Provider : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"procphysician\">
  ";
  freemed_display_physicians ($procphysician, "no");
  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Date of Procedure : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
  ";
  if (empty ($procdt)) $procdt = $cur_date; // show current date
  fm_date_entry ("procdt");
  echo "
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Episode of Care : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
  ";
  freemed_multiple_choice ("SELECT * FROM $database.eoc
                            WHERE eocpatient='$patient'
                            ORDER BY eocdtlastsimilar DESC",
                           "eocstartdate:eocdtlastsimilar:eocdescrip",
                           "proceoc",
                           $proceoc,
                           false);
  echo "
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>CPT Code/Modifier : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"proccpt\">
  ";
  freemed_display_cptcodes ($proccpt);
  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>&nbsp;</TD><TD ALIGN=LEFT>
      <SELECT NAME=\"proccptmod\">
  ";
  freemed_display_cptmods ($proccptmod);
  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Units : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"procunits\" VALUE=\"$procunits\"
       SIZE=10 MAXLENGTH=9>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Diagnosis Code 1 : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"procdiag1\">
  ";
  freemed_display_icdcodes ($procdiag1);
  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Diagnosis Code 2 : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"procdiag2\">
  ";
  freemed_display_icdcodes ($procdiag2);
  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Diagnosis Code 3 : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"procdiag3\">
  ";
  freemed_display_icdcodes ($procdiag3);
  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Diagnosis Code 4 : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"procdiag4\">
  ";
  freemed_display_icdcodes ($procdiag4);
  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Place of Service : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"procpos\">
  ";
  freemed_display_facilities ($procpos);
  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Type of Service : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"proctos\">
  ";
  freemed_display_tos ($proctos);
  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Voucher Number : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"procvoucher\" VALUE=\"$procvoucher\"
       SIZE=20>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Authorization : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"procauth\">
       <OPTION VALUE=\"0\" ".
        ( ($procauth==0) ? "SELECTED" : "" ).">NONE SELECTED
  ";
  $auth_res = fdb_query ("SELECT * FROM $database.authorizations
                          WHERE (authpatient='$patient')");
  if ($auth_res > 0) { // begin if there are authorizations...
   while ($auth_r = fdb_fetch_array ($auth_res)) {
    echo "
     <OPTION VALUE=\"$auth_r[id]\" ".
     ( ($auth_r[id]==$procauth) ? "SELECTED" : "" )
     .">$auth_r[authdtbegin] to $auth_r[authdtend]
    ";
   } // end while looping for authorizations
  } // end if there are authorizations
  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Referring Provider : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"procrefdoc\">
  ";
  freemed_display_physicians ($procrefdoc, "yes");
  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Date of Last Visit : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
  ";
  fm_date_entry ("procrefdt");
  echo "
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Comment : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"proccomment\" VALUE=\"$proccomment\"
       SIZE=30 MAXLENGTH=512>
     </TD>
    </TR>

    </TABLE>

    <P>
    <CENTER>
     <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"$next_action\">
     <INPUT TYPE=SUBMIT VALUE=\"$this_action\">
     <INPUT TYPE=RESET  VALUE=\"$Clear\"> 
    </CENTER>
    </FORM>
  ";
  freemed_display_box_bottom ();
  break; // end of add/modify form action

 case "addform2":
 case "modform2":
  switch ($action) {
    case "addform2":
     $next_action="add";
     $this_action="$Add";
     break;
    case "modform2":
     $next_action="mod";
     $this_action="$Modify";
     break;
  } // internal action switch (addform2,modform2)
  freemed_display_box_top ("$record_name Confirm");
  echo "
   <P>
    <CENTER>
     <$STDFONT_B>$Patient : <$STDFONT_E>
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>".$this_patient->fullName(true)."<$STDFONT_E></A>
    </CENTER>
   <P>

   <FORM ACTION=\"$page_name\" METHOD=POST>

    <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"$next_action\">
    <INPUT TYPE=HIDDEN NAME=\"_auth\"   VALUE=\"$_auth\">
    <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
    <INPUT TYPE=HIDDEN NAME=\"id\"      VALUE=\"$id\">

    <!-- embed all important variables -->
    <INPUT TYPE=HIDDEN NAME=\"procpatient\" VALUE=\"$procpatient\">
    <INPUT TYPE=HIDDEN NAME=\"proceoc\" VALUE=\"".
        fm_prep(fm_join_from_array($proceoc))."\">
    <INPUT TYPE=HIDDEN NAME=\"proccpt\" VALUE=\"$proccpt\">
    <INPUT TYPE=HIDDEN NAME=\"proccptmod\" VALUE=\"$proccptmod\">
    <INPUT TYPE=HIDDEN NAME=\"procdiag1\" VALUE=\"$procdiag1\">
    <INPUT TYPE=HIDDEN NAME=\"procdiag2\" VALUE=\"$procdiag2\">
    <INPUT TYPE=HIDDEN NAME=\"procdiag3\" VALUE=\"$procdiag3\">
    <INPUT TYPE=HIDDEN NAME=\"procdiag4\" VALUE=\"$procdiag4\">
    <INPUT TYPE=HIDDEN NAME=\"proccharges\" VALUE=\"$proccharges\">
    <INPUT TYPE=HIDDEN NAME=\"procunits\" VALUE=\"$procunits\">
    <INPUT TYPE=HIDDEN NAME=\"procvoucher\" VALUE=\"".
        fm_prep($procvoucher)."\">
    <INPUT TYPE=HIDDEN NAME=\"procphysician\" VALUE=\"$procphysician\">
    <INPUT TYPE=HIDDEN NAME=\"procdt_y\" VALUE=\"$procdt_y\">
    <INPUT TYPE=HIDDEN NAME=\"procdt_d\" VALUE=\"$procdt_d\">
    <INPUT TYPE=HIDDEN NAME=\"procdt_m\" VALUE=\"$procdt_m\">
    <INPUT TYPE=HIDDEN NAME=\"procpos\"  VALUE=\"$procpos\">
    <INPUT TYPE=HIDDEN NAME=\"proccomment\" VALUE=\"".
       fm_prep($proccomment)."\">
    <INPUT TYPE=HIDDEN NAME=\"procauth\" VALUE=\"".
       fm_prep($procauth)."\">
    <INPUT TYPE=HIDDEN NAME=\"procrefdoc\" VALUE=\"".
       fm_prep($procrefdoc)."\">
    <INPUT TYPE=HIDDEN NAME=\"procrefdt_y\" VALUE=\"".
       fm_prep($procrefdt_y)."\">
    <INPUT TYPE=HIDDEN NAME=\"procrefdt_m\" VALUE=\"".
       fm_prep($procrefdt_m)."\">
    <INPUT TYPE=HIDDEN NAME=\"procrefdt_d\" VALUE=\"".
       fm_prep($procrefdt_d)."\">

    <!-- calculate charges and allow change here -->
  ";

  // charge calculation routine lies here
  //   charge = units * relative_value(cpt) * 
  //            base_value(physician/provider)
  //   standard_fee = standard_fee [insurance co] unless 0 then
  //                = default_standard_fee
  //  (we display "standard fee" as what the bastards (insurance companies)
  //   are actually going to pay -- be sure to check for divide by zeros...)

  // step one:
  //   calculate the standard fee
  $this_insco = new InsuranceCompany ($this_patient->local_record["ptinsco1"]);
  $cpt_code = freemed_get_link_rec ($proccpt, "cpt"); // cpt code
  $cpt_code_fees = fm_split_into_array ($cpt_code["cptstdfee"]);
  $cpt_code_stdfee = $cpt_code[$this_insco->id]; // grab proper std fee
  if (empty($cpt_code_stdfee) or ($cpt_code_stdfee==0))
    $cpt_code_stdfee = $cpt_code["cptdefstdfee"]; // if none, do default
  $cpt_code_stdfee = bcadd ($cpt_code_stdfee, 0, 2);

  // step two:
  //   grab the relative value from the CPT db
  $relative_value = $cpt_code["cptrelval"];
  if ($debug) echo " (relative_value = \"$relative_value\")\n";

  // step three:
  //   calculate the base value
  $internal_type  = $cpt_code ["cpttype"]; // grab internal type
  if ($debug) 
    echo " (inttype = $internal_type) (procphysician = $procphysician) ";
  $this_physician = freemed_get_link_rec ($procphysician, "physician");
  $charge_map     = fm_split_into_array($this_physician ["phychargemap"]);
  $base_value     = $charge_map [$internal_type];
  if ($debug) echo "<BR>base value = \"$base_value\"\n";

  // step four:
  //   check for patient discount percentage
  $percentage = $this_patient->local_record["ptdisc"];
  if ($percentage>0) { $discount = $percentage / 100; }
   else              { $discount = 0;                 }
  if ($debug) echo "<BR>discount = \"$discount\"\n";

  // step five:
  //   calculate formula...
  $charge = ($base_value * $procunits * $relative_value) - $discount; 
  if ($debug) echo " (charge = \"$charge\") \n";

  // step six:
  //   adjust values to proper precision
  $charge = bcadd ($charge, 0, 2);

  echo "
   <P>
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3
    VALIGN=MIDDLE ALIGN=CENTER>

   <TR>
    <TD ALIGN=RIGHT>
     <$STDFONT_B>Procedural Code : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
     <$STDFONT_B>".fm_prep($cpt_code["cptcode"])."<$STDFONT_E>
    </TD>
   </TR>

   <TR>
    <TD ALIGN=RIGHT>
     <$STDFONT_B>Units : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
     <$STDFONT_B>".fm_prep($procunits)."<$STDFONT_E>
    </TD>
   </TR>

   <TR>
    <TD ALIGN=RIGHT>
     <$STDFONT_B>Calculated Accepted Fee : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
     <$STDFONT_B>$cpt_code_stdfee<$STDFONT_E>
    </TD>
   </TR>

   <TR>
    <TD ALIGN=RIGHT>
     <$STDFONT_B>Calculated Charge : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
     <INPUT TYPE=TEXT NAME=\"procbalorig\" SIZE=10 MAXLENGTH=9
      VALUE=\"".fm_prep($charge)."\">
    </TD>
   </TR>

   <TR>
    <TD ALIGN=RIGHT>
     <$STDFONT_B>Insurance Billable? : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
     <SELECT NAME=\"procbillable\">
      <OPTION VALUE=\"0\" ".
       ( ($procbillable == 0) ? "SELECTED" : "" ).">yes
      <OPTION VALUE=\"1\" ".
       ( ($procbillable != 0) ? "SELECTED" : "" ).">no
     </SELECT>
    </TD>
   </TR>

   <TR>
    <TD ALIGN=RIGHT>
     <$STDFONT_B>Comment : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
     <$STDFONT_B>".fm_prep($proccomment)."<$STDFONT_E>
    </TD>
   </TR>

   </TABLE>

   <P>
   <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\"$this_action\">
    <INPUT TYPE=RESET  VALUE=\"$Clear\">
   </CENTER>

   </FORM>
   <P>
  ";
  freemed_display_box_bottom ();
  break; // addform/modform confirm action (addform2,modform2)

 case "add": // add action
  freemed_display_box_top ("$Adding $record_name");
  echo "
    <P>
    <$STDFONT_B>$Adding ... <$STDFONT_E>
  ";

  // form add query
  $query = "INSERT INTO $database.procedure VALUES (
            '$patient',
            '".addslashes(fm_join_from_array($proceoc))."',
            '$proccpt',
            '$proccptmod',
            '$procdiag1',
            '$procdiag2',
            '$procdiag3',
            '$procdiag4',
            '".addslashes($proccharges).  "',
            '".addslashes($procunits).    "',
            '".addslashes($procvoucher).  "',
            '$procphysician',
            '".fm_date_assemble("procdt")."',
            '$procpos',
            '".addslashes($proccomment).  "',
            '$procbalorig',
            '$procbalorig',
            '0',
            '0',
            '".addslashes($procbillable). "',
            '".addslashes($procauth).     "',
            NULL )";
  $result = fdb_query ($query);
  if ($debug) echo " (query = $query, result = $result) <BR>\n";
  if ($result) { echo "$Done."; }
   else        { echo "$ERROR"; }

  $this_procedure = fdb_last_record ();

  // form add query
  echo "
    <P>
    <$STDFONT_B>Committing to ledger ... <$STDFONT_E>
  ";
  $query = "INSERT INTO $database.payrec VALUES (
            '$cur_date',
            '0000-00-00',
            '$patient',
            '".fm_date_assemble("procdt")."',
            '5',
            '$this_procedure',
            '0',
            '0',
            '0',
            '',
            '$procbalorig',
            '".addslashes($proccomment)."',
            'unlocked',
            NULL )";
  $result = fdb_query ($query);
  if ($debug) echo " (query = $query, result = $result) <BR>\n";
  if ($result) { echo "$Done."; }
   else        { echo "$ERROR"; }
  $this_procedure = fdb_last_record ($result, "procedure");
  
    // updating patient diagnoses
  echo "
    <P>
    <$STDFONT_B>Updating patient diagnoses ... <$STDFONT_E>
  ";
  $query = "UPDATE $database.patient SET
            ptdiag1  = '$procdiag1',
            ptdiag2  = '$procdiag2',
            ptdiag3  = '$procdiag3',
            ptdiag4  = '$procdiag4'
            WHERE id = '$patient'";
  $result = fdb_query ($query);
  if ($debug) echo " (query = $query, result = $result) <BR>\n";
  if ($result) { echo "$Done."; }
   else        { echo "$ERROR"; }
  
  echo "
    <P>
    <CENTER>
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A> <B>|</B>
     <A HREF=\"payment_record.php3?$_auth&action=addform&patient=$patient&".
     "procedure=$this_procedure\"
     ><$STDFONT_B>Add Payment<$STDFONT_E></A> <B>|</B>
     <A HREF=\"procedure.php3?$_auth&action=addform&procvoucher=$procvoucher".
      "&patient=$patient&procdt=".fm_date_assemble("procdt").
      "&procdiag1=$procdiag1".
      "&procdiag2=$procdiag2".
      "&procdiag3=$procdiag3".
      "&procdiag4=$procdiag4".
      "&procphysician=$procphysician".
      "\"
     ><$STDFONT_B>Add Another $record_name<$STDFONT_E></A>
    </CENTER>
    <P>
  ";
  freemed_display_box_bottom ();
  break; // end of add action

 case "mod": // modify action
  freemed_display_box_top ("$Modifying $record_name");
  echo "
    <P>
    <$STDFONT_B>$Modifying ... <$STDFONT_E>
  ";
  // form add query
  $query = "UPDATE $database.procedure SET
            procpatient     = '$patient',
            proceoc         = '".addslashes(fm_join_from_array($proceoc))."',
            proccpt         = '$proccpt',
            proccptmod      = '$proccptmod',
            procdiag1       = '$procdiag1',
            procdiag2       = '$procdiag2',
            procdiag3       = '$procdiag3',
            procdiag4       = '$procdiag4',
            proccharges     = '$proccharges',
            procunits       = '$procunits',
            procvoucher     = '".addslashes($procvoucher).  "',
            procphysician   = '".addslashes($procphysician)."',
            procdt          = '".fm_date_assemble("procdt")."',
            procpos         = '".addslashes($procpos).      "',
            procbalorig     = '".addslashes($procbalorig).  "',
            proccomment     = '".addslashes($proccomment).  "',
            procauth        = '".addslashes($procauth).     "',
            procbillable    = '".addslashes($procbillable). "'
            WHERE id='$id'";
  $result = fdb_query ($query);
  if ($debug) echo " (query = $query, result = $result) <BR>\n";
  if ($result) { echo "$Done."; }
   else        { echo "$ERROR"; }
  echo "
    <P>
    <$STDFONT_B>Updating patient ledger ... <$STDFONT_E>
  ";
  // form add query
  $query = "UPDATE $database.payrec SET
            payrecdtmod   = '$cur_date',
            payrecpatient = '$patient',
            payrecdt      = '".fm_date_assemble("procdt")."',
            payrecamt     = '$procbalorig',
            payrecdescrip = '".addslashes($proccomment)."'
            WHERE ( (payreccat='5') AND (payrecproc='$id') )";
  $result = fdb_query ($query);
  if ($debug) echo " (query = $query, result = $result) <BR>\n";
  if ($result) { echo "$Done."; }
   else        { echo "$ERROR"; }

    // updating patient diagnoses
  echo "
    <P>
    <$STDFONT_B>Updating patient diagnoses ... <$STDFONT_E>
  ";
  $query = "UPDATE $database.patient SET
            ptdiag1  = '$procdiag1',
            ptdiag2  = '$procdiag2',
            ptdiag3  = '$procdiag3',
            ptdiag4  = '$procdiag4'
            WHERE id = '$patient'";
  $result = fdb_query ($query);
  if ($debug) echo " (query = $query, result = $result) <BR>\n";
  if ($result) { echo "$Done."; }
   else        { echo "$ERROR"; }

  echo "
    <P>
    <CENTER>
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
    </CENTER>
    <P>
  ";
  freemed_display_box_bottom ();
  break; // end of modify action

 case "del": // delete action
  freemed_display_box_top ("$Deleting $record_name");
  echo "
   <P>
   <$STDFONT_B>$Deleting $record_name ...
  ";
  $query = "DELETE FROM $database.$db_name WHERE id='$id'";
  $result = fdb_query ($query);
  if ($result) { echo "[record] "; }
   else        { echo "[$ERROR] "; }
  $query = "DELETE FROM $database.payrec WHERE payrecproc='$id'
            AND payreccat='5'"; // delete record in payrec db
  $result = fdb_query ($query);
  if ($result) { echo "[payrec] "; }
   else        { echo "[$ERROR] "; }
  echo "
   <$STDFONT_E>
   <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>Return to Procedures<$STDFONT_E></A> <B>|</B>
     <A HREF=\"manage.php3?$_auth?id=$patient\"
     ><$STDFONT_B>Manage Patient<$STDFONT_E></A>
    </CENTER>
   <P>
  ";
  freemed_display_box_bottom ();
  break; // end of delete section

 default: // default action (master switch)
  freemed_display_box_top ("$record_name");
  $query = "SELECT * FROM $database.$db_name
            WHERE procpatient='$patient'
            ORDER BY procdt DESC";
  $result = fdb_query ($query);
  echo "
    <P>
    <CENTER>
     <$STDFONT_B>Patient : <$STDFONT_E>
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>".$this_patient->fullName(true)."<$STDFONT_E></A>
    </CENTER>
    <P>

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR BGCOLOR=#bbbbbb>
     <TD><B>Date of Procedure<B>&nbsp;</TD>
     <TD><B>Procedure Code</B>&nbsp;</TD>
     <TD><B>Modifier</B>&nbsp;</TD>
     <TD><B>Comment</B>&nbsp;</TD>
     <TD><B>Action</B></TD>
    </TR>
  ";
  $_alternate = freemed_bar_alternate_color ();
  if (($result<1) or (fdb_num_rows ($result)<1))
   echo "
    <TR>
     <TD COLSPAN=3 ALIGN=CENTER>
      <CENTER>
       <$STDFONT_B><U>There are no procedures for this patient.</U><$STDFONT_E>
      </CENTER>
     </TD>
    </TR>
   ";
  while ($r = fdb_fetch_array ($result)) {
    $_alternate = freemed_bar_alternate_color ($_alternate);
    $cptcode    = freemed_get_link_rec ($r["proccpt"], "cpt");
    $cptmod     = freemed_get_link_rec ($r["proccptmod"], "cptmod");
    if (empty($r["proccomment"])) $r["proccomment"]="NO COMMENT";
    echo "
     <TR BGCOLOR=$_alternate>
      <TD>".fm_prep($r["procdt"])."</TD>
      <TD>".fm_prep($cptcode["cptcode"]." (".$cptcode["cptnameint"].")")."</TD>
      <TD>".fm_prep($cptmod["cptmod"])."</TD>
      <TD>".fm_prep($r["proccomment"])."</TD>
      <TD>
    ";
    if (($this_user->getLevel())>$database_level)
     echo "
      <A HREF=\"$page_name?$_auth&patient=$patient&action=modform&id=".
      $r["id"]."\"
      ><$STDFONT_B SIZE=-2>$lang_MOD<$STDFONT_E></A>&nbsp;
     ";
    if (($this_user->getLevel())>$delete_level)
     echo "
      <A HREF=\"$page_name?$_auth&patient=$patient&action=del&id=".
      $r["id"]."\"
      ><$STDFONT_B SIZE=-2>$lang_DEL<$STDFONT_E></A>&nbsp;
     ";
    echo "&nbsp;
     </TR>
    ";
  } // end while looping for fetched array
  echo "
    </TABLE>
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth&patient=$patient&action=addform\"
     ><$STDFONT_B>$Add $record_name<$STDFONT_E></A> <B>|</B>
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
    </CENTER>
    <P>
  ";
  freemed_display_box_bottom ();
  break; // end of default action

} // end master action switch

freemed_display_html_bottom ();
freemed_close_db ();

?>
