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
   //freemed_display_box_top ("Generate Insurance Claim Forms");

   // get the actual form
   $this_form = freemed_get_link_rec ($whichform, "fixedform");

   // zero the buffer and counter
   $buffer = "";
   $counter = 0;
   $current_patient = 0;

   // get list of all patient who need to be billed
   $b_result = fdb_query ("SELECT DISTINCT payrecpatient
                           FROM $database.payrec
                           WHERE (
                             payreccat = '5' AND
                             payreclink = '0'
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

   // zero form buffer
   $form_buffer = "";

   // loop for all patients
   while ($b_r = fdb_fetch_array ($b_result)) {

     // get current patient
     $current_patient = $b_r[payrecpatient];
     $this_patient = new Patient ($current_patient);
     echo "
      <B>Processed ".$this_patient->fullName()." ($current_patient)</B>
      <BR>\n\n
     ";
     flush ();

     $debug=true;

     // decide which ones we are generating
     $result = fdb_query ("SELECT * FROM $database.payrec
                           WHERE ( 
                             payreccat = '5' AND
                             payreclink = '0' AND
                             payrecpatient = '$current_patient'
                           ) ORDER BY payrecpatient,payrecdt");

     if (!$result or ($result==0))
       die ("Malformed SQL query ($current_patient)");

     // create a new diagnosisSet stack
     $diag_set = new diagnosisSet (); // new stack of size 4

     if ($debug) echo "\nBuilding patient information...<BR>\n";

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
                           $this_form[ffcheckchar] : " " );
     $ptsex[female]   = ( ($this_patient->ptsex == "f") ?
                           $this_form[ffcheckchar] : " " );
     $ptsex[trans]    = ( ($this_patient->ptsex == "t") ?
                           $this_form[ffcheckchar] : " " );
     $ptid            = $this_patient->local_record["ptid"];

     // relationship to guarantor
     $ptreldep[self]   = ( ($this_patient->ptreldep == "S") ?
                            $this_form[ffcheckchar] : " " );
     $ptreldep[child]  = ( ($this_patient->ptreldep == "C") ?
                            $this_form[ffcheckchar] : " " );
     $ptreldep[spouse] = 
       ( (($this_patient->ptreldep == "H") or
          ($this_patient->ptreldep == "W")) ?
          $this_form[ffcheckchar] : " " );
     $ptreldep[husband]= ( ($this_patient->ptreldep == "H") ?
                            $this_form[ffcheckchar] : " " );
     $ptreldep[wife]   = ( ($this_patient->ptreldep == "W") ?
                            $this_form[ffcheckchar] : " " );
     $ptreldep[other]  = ( ($this_patient->ptreldep == "O") ?
                            $this_form[ffcheckchar] : " " );

     // marital status
     $ptmarital[single]    =
       ( ($this_patient->ptmarital == "single") ?
          $this_form[ffcheckchar] : " " );
     $ptmarital[married]   = 
       ( ($this_patient->ptmarital == "married") ?
          $this_form[ffcheckchar] : " " );
     $ptmarital[divorced]  = 
       ( ($this_patient->ptmarital == "divorced") ?
          $this_form[ffcheckchar] : " " );
     $ptmarital[separated] = 
       ( ($this_patient->ptmarital == "separated") ?
          $this_form[ffcheckchar] : " " );

     // employment status
     $ptemployed[yes] =
       ( ($this_patient->isEmployed) ?
          $this_form[ffcheckchar] : " " );
     $ptemployed[no] =
       ( !($this_patient->isEmployed) ?
          $this_form[ffcheckchar] : " " );

     // address information
     $ptaddr[line1]   = $this_patient->local_record["ptaddr1"  ];
     $ptaddr[line2]   = $this_patient->local_record["ptaddr2"  ];
     $ptaddr[city]    = $this_patient->local_record["ptcity"   ];
     $ptaddr[state]   = $this_patient->local_record["ptstate"  ];
     $ptaddr[zip]     = $this_patient->local_record["ptzip"    ];
     $ptaddr[country] = $this_patient->local_record["ptcountry"];
     $ptphone[full]   = $this_patient->local_record["pthphone" ];

     // doctor link/information
     $this_physician  = new Physician
                        ($this_patient->local_record["ptdoc"]);
     $phy[name]       = $this_physician->fullName();
     $phy[practice]   = $this_physician->practiceName();
     $phy[addr1]      = $this_physician->local_record["phyaddr1a"];
     $phy[addr2]      = $this_physician->local_record["phyaddr2a"];
     $phy[city]       = $this_physician->local_record["phycitya" ];
     $phy[state]      = $this_physician->local_record["phystatea"];
     $phy[zip]        = $this_physician->local_record["phyzipa"  ];
     $phy[phone]      = $this_physician->local_record["phyphonea"];

     // what is this related to?
     $employment = "n"; // PULL THIS FROM EOC LATER !! FIX ME !!
     $related_employment[yes] =
       ( ( $employment == "y" ) ? $this_form[ffcheckchar] : " " );
     $related_employment[no]  =
       ( ( $employment == "n" ) ? $this_form[ffcheckchar] : " " );
     $auto = "n"; // PULL THIS FROM EOC LATER !! FIX ME !!
     $related_auto[yes] =
       ( ( $auto == "y" ) ? $this_form[ffcheckchar] : " " );
     $related_auto[no]  =
       ( ( $auto == "n" ) ? $this_form[ffcheckchar] : " " );
     $related_auto[state] =    // FIIIIIIIX  MEEEEEEEEEE!!!
       ( ( $auto == "y" ) ? $eoc_state_name : "  " );
     $other = "n"; // PULL THIS FROM EOC LATER !! FIX ME !!
     $related_other[yes] =
       ( ( $other == "y" ) ? $this_form[ffcheckchar] : " " );
     $related_other[yes] =
       ( ( $other == "y" ) ? $this_form[ffcheckchar] : " " );

     // current date hashes
     $curdate[mmddyy]   = date ("mdy");
     $curdate[mmddyyyy] = date ("mdY");
     $curdate[m]        = date ("m");
     $curdate[d]        = date ("d");
     $curdate[sy]       = date ("y");
     $curdate[y]        = date ("Y");

     // check for guarantor information
     if ($this_patient->local_record[ptdep] == 0) {
       // if self insured, transfer data to guarantor arrays
       $guarname      = $ptname;  // assign name information
       $guaraddr      = $ptaddr;  // assign address information
       $guardob       = $ptdob;   // assign date of birth info
       $guarsex       = $ptsex;   // assign gender information
     } else {
       // if it is someone else, get *their* information
       $guarantor = new Patient ($this_patient->local_record[ptdep]);
       ########  NOT COMPLETED YET ###########
     } // end checking for dependant

     if ($debug) echo "\nRunning through charges/procedures ... <BR>\n";

     flush();

     // zero current number of charges
     $number_of_charges = 0;
     // and zero the arrays
     for ($j=0;$j<=$this_form[ffloopnum];$j++)
       $itemdate[$j]   = $itemdate_m[$j]  = $itemdate_d[$j]  =
       $itemdate_y[$j] = $itemdate_sy[$j] = $itemcharges[$j] =
       $itemunits[$j]  = $itempos[$j]     = $itemvoucher[$j] =
       $itemcpt[$j]    = $itemcptmod[$j]  = "";

     // grab form information form
     $this_form = freemed_get_link_rec ($whichform, "fixedform");

     // queue all entries
     while ($r = fdb_fetch_array ($result)) {
       $number_of_charges++; // increment number of charges

       if ($debug) echo "\nThis form, charge $number_of_charges <BR>\n";
       flush();

       // get the current procedure
       #if ($r[payrecproc] > 0)
         $p = freemed_get_link_rec ($r[payrecproc], "procedure");

       if ($debug) echo "\nRetrieved procedure $r[payrecproc] <BR>\n";
       flush();

       ////if there's room, pull another procedure:
       //// @ are enough diagnosis spots left for the nonrepeated diags?
       //// @ are we above the bottom of the form (is this proc number
       ////   six or less?

       if ($debug) echo "\nCurrent stack size = $diag_set->stack_size <BR>\n";
       if (!($diag_set->testAddSet ($p[procdiag1], $p[procdiag2],
                                    $p[procdiag3], $p[procdiag4])) or
            ($number_of_charges > $this_form[ffloopnum]         )){
         if ($debug) echo "\nNew form time ... <BR>\n";
         echo "$number_of_charges > $this_form[ffloopnum] <BR>\n";
         flush();

         // drop the current form to the buffer
         $form_buffer .= render_fixedForm ($whichform);
         // reset the counter to 1, for the first...
         $number_of_charges = 1;
         // and zero the arrays
         for ($j=0;$j<=$this_form[ffloopnum];$j++)
           $itemdate[$j]   = $itemdate_m[$j]  = $itemdate_d[$j]  =
           $itemdate_y[$j] = $itemdate_sy[$j] = $itemcharges[$j] =
           $itemunits[$j]  = $itempos[$j]     = $itemvoucher[$j] =
           $itemcpt[$j]    = $itemcptmod[$j]  = "";
       } else {
         // DONT DO ANYTHING IN THIS CASE (PLACEHOLDER)       
       } // end checking if the set will fit

       // pull into current array
       if ($debug) echo "\nnumber of charges = $number_of_charges <BR>\n";
       flush();
       $itemdate    [$number_of_charges] = $p[procdt];
       $itemdate_m  [$number_of_charges] = substr($p[procdt], 5, 2);
       $itemdate_d  [$number_of_charges] = substr($p[procdt], 8, 2);
       $itemdate_y  [$number_of_charges] = substr($p[procdt], 0, 4);
       $itemdate_sy [$number_of_charges] = substr($p[procdt], 2, 2);
       $itemcharges [$number_of_charges] = $p[proccharges];
       $itemunits   [$number_of_charges] = $p[procunits];
       $itempos     [$number_of_charges] = $p[procpos];
       $itemvoucher [$number_of_charges] = $p[procvoucher];
       $itemcpt     [$number_of_charges] =
          freemed_get_link_field ($p[proccpt], "cpt", "cptcode");
       $itemcptmod  [$number_of_charges] =
          freemed_get_link_field ($p[proccptmod], "cptmod", "cptmod");
       $itemdiagref [$number_of_charges] =
          $diag_set->xrefList ($p[procdiag1], $p[procdiag2],
                               $p[procdiag3], $p[procdiag4]);
     } // end of looping for all charges

     // render last form
     $form_buffer .= render_fixedForm ($whichform);

   } // end of while there are no more patients

   #################### TAKE THIS OUT AFTER TESTING #######################
   #echo "<PRE>\n".fm_prep($form_buffer)."\n</PRE>\n";
   ########################################################################

   echo "
    <FORM ACTION=\"echo.php3\" METHOD=POST>
     <CENTER>
      <$STDFONT_B><B>Preview</B><$STDFONT_E>
     </CENTER>
     <BR>
     <TEXTAREA NAME=\"text\" ROWS=10 COLS=81
     >".fm_prep($form_buffer)."</TEXTAREA>
    <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\"Get HCFA Rendered Text File\">
    </CENTER>
    </FORM>
   ";

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
