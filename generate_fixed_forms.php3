<?php
 # file: generate_fixed_forms.php3
 # desc: generate fixed forms
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

 $page_name = "generate_fixed_forms.php3";
 include ("global.var.inc");
 include ("freemed-functions.inc");
 include ("render_forms.inc");

 freemed_open_db ($LoginCookie);

 freemed_display_html_top ();
 freemed_display_banner ();

 switch ($action) { // master action switch
  case "geninsform":
   freemed_display_box_top ("Generate Insurance Claim Forms");

   // get the actual form
   $this_form = freemed_get_link_rec ($whichform, "fixedform");

   // zero the buffer and counter
   $buffer = "";
   $counter = 0;
   $current_patient = 0;

   // get list of all patient who need to be billed
   $b_result = fdb_query ("SELECT DISTINCT payrecpatient
                           WHERE (
                             payreccat = '5' AND
                             payrecbilled = 0
                           ) ORDER BY payrecpatient");

   if (!$b_result or (fdb_num_rows($b_result)<1)) {
     echo "
      <P>
      <CENTER>
       <$STDFONT_B>Nothing to be billed.<$STDFONT_E>
      </CENTER>
      <P>
     ";
     freemed_display_box_bottom ();
     freemed_close_db ();
     freemed_display_html_bottom ();
     DIE(""); // kill! kill! kill!
   } // if there is no result, end

   // loop for all patients
   while ($b_r = fdb_fetch_array ($b_result)) {

     // get current patient
     $current_patient = $b_r[payrecpatient];
     $this_patient = new Patient ($current_patient);
     echo "
      <LI>Processing ".$this_patient->fullName." ($current_patient)
     ";

     // zero current number of charges
     $number_of_charges = 0;

     // decide which ones we are generating
     $result = fdb_query ("SELECT * FROM $database.payrec
                           WHERE ( 
                             payreccat = '5' AND
                             payrecbilled = 0 AND
                             payrecpatient = '$current_patient'
                           ) ORDER BY payrecpatient,payrecdt");

     // queue all entries
     while ($r = fdb_fetch_array ($result)) {
       $number_of_charges++; // increment number of charges

       // get the current procedure
       $p = fdb_fetch_array ($r[payrecproc], "procedure");

       // pull into current array
       $c_dateofservice [$number_of_charges] = $p[procdt];
       $c_charges       [$number_of_charges] = $p[proccharges];
       $c_units         [$number_of_charges] = $p[procunits];
     } // end of looping for all charges

     // pull in proper variables
     $ptname[last]    = $this_patient->ptlname;
     $ptname[first]   = $this_patient->ptfname;
     $ptname[middle]  = $this_patient->ptmname;
     $ptdob[full]     = $this_patient->ptdob;
     $ptdob[month]    = substr ($ptdob[full], 5, 2);  
     $ptdob[day]      = substr ($ptdob[full], 8, 2);  
     $ptdob[year]     = substr ($ptdob[full], 0, 4);
     $ptdob[syear]    = substr ($ptdob[full], 2, 2);
     $ptdob[mmddyy]   = $ptdob[month].
                        $ptdob[day].
                        $ptdob[syear];
     $ptdob[mmddyyyy] = $ptdob[month].
                        $ptdob[day].
                        $ptdob[year];
     $ptsex[male]     = ( ($this_patient->ptsex == "m") ?
                           $this_form->ffcheckchar : " " );
     $ptsex[female]   = ( ($this_patient->ptsex == "f") ?
                           $this_form->ffcheckchar : " " );
     $ptsex[trans]    = ( ($this_patient->ptsex == "t") ?
                           $this_form->ffcheckchar : " " );

     // relationship to guarantor
     $ptreldep[self]   = ( ($this_patient->ptreldep == "S") ?
                            $this_form->ffcheckchar : " " );
     $ptreldep[child]  = ( ($this_patient->ptreldep == "C") ?
                            $this_form->ffcheckchar : " " );
     $ptreldep[spouse] = 
       ( (($this_patient->ptreldep == "H") or
          ($this_patient->ptreldep == "W")) ?
          $this_form->ffcheckchar : " " );
     $ptreldep[husband]= ( ($this_patient->ptreldep == "H") ?
                            $this_form->ffcheckchar : " " );
     $ptreldep[wife]   = ( ($this_patient->ptreldep == "W") ?
                            $this_form->ffcheckchar : " " );
     $ptreldep[other]  = ( ($this_patient->ptreldep == "O") ?
                           $this_form->ffcheckchar : " " );

     // marital status
     $ptmarital[single]    =
       ( ($this_patient->ptmarital == "single") ?
          $this_form->ffcheckchar : " " );
     $ptmarital[married]   = 
       ( ($this_patient->ptmarital == "married") ?
          $this_form->ffcheckchar : " " );
     $ptmarital[divorced]  = 
       ( ($this_patient->ptmarital == "divorced") ?
          $this_form->ffcheckchar : " " );
     $ptmarital[separated] = 
       ( ($this_patient->ptmarital == "separated") ?
          $this_form->ffcheckchar : " " );

     // address information
     $ptaddr[line1]   = $this_patient->local_record["ptaddr1"  ];
     $ptaddr[line2]   = $this_patient->local_record["ptaddr2"  ];
     $ptaddr[city]    = $this_patient->local_record["ptcity"   ];
     $ptaddr[state]   = $this_patient->local_record["ptstate"  ];
     $ptaddr[zip]     = $this_patient->local_record["ptzip"    ];
     $ptaddr[country] = $this_patient->local_record["ptcountry"];

     // generate form
     $current_form = render_fixedForm ($whichform);

   } // end of while there are no more patients

   freemed_display_box_bottom ();
   break; // end of action geninsform

  default:
   freemed_display_box_top ("Fixed Forms Generation Menu");
   echo "
    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3
     VALIGN=MIDDLE ALIGN=CENTER>

    <TR>
     <TD COLSPAN=2>
      <CENTER>
       <$STDFONT_B><B>Generate Insurance Claim Forms</B><$STDFONT_E>
      </CENTER>
     </TD>
    </TR>

    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"$_auth\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"geninsform\">

    <TR>
     <TD ALIGN=RIGHT>
      <CENTER>
       <$STDFONT_B>Claim Form : <$STDFONT_E>
      </CENTER>
     </TD>
     <TD ALIGN=LEFT>
      <SELECT NAME=\"whichform\">
   ";
   $result = fdb_query ("SELECT * FROM $database.fixedform WHERE fftype='1'
                         ORDER BY ffname, ffdescrip");
   while ($r = fdb_fetch_array ($result)) {
    echo "
     <OPTION VALUE=\"$r[id]\">".fm_prep($r[ffname])."
    ";
   } // end looping through results                         
   echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD COLSPAN=2>
      <CENTER>
       <INPUT TYPE=SUBMIT VALUE=\"go\">
      </CENTER>
     </TD>
    </TR>

    </FORM>

    </TABLE>
   ";
   freemed_display_box_bottom ();
   break;
 } // end of master action switch

 freemed_close_db ();
 freemed_display_html_bottom ();
?>