<?php
 // file: generate_fixed_forms.php3
 // desc: generate fixed forms
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

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

//

   // get list of all patient who need to be billed
//                             payreccat = '5' AND
   $b_result = fdb_query ("SELECT DISTINCT payrecpatient
                           FROM payrec
                           WHERE (
                             payreccat = '".PROCEDURE."' AND
                             payreclink < '3'
                           ) ORDER BY payrecpatient");
   // 0 = 1st insurance
   // 1 = 2nd insurance
   // 2 = 3rd insurance
   // 3 = workers' comp
   // 4 = patient/guarantor

   if (!$b_result or (fdb_num_rows($b_result)<1)) {
     echo "
      <P>
      <CENTER>
       <$STDFONT_B>"._("There is nothing to be billed.")."<$STDFONT_E>
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

   $pats_processed = 0;
   $still_going    = true;
   $current_skip   = 0;

   // skip kludge - moved to new kludge below
   //if ($skip > 0)
   //  for ($i=1;$i<=$skip;$i++)
   //    $null_me = fdb_fetch_array ($b_result);

   // loop for all patients
   while (($b_r = fdb_fetch_array ($b_result)) and ($still_going)) {

    // pull current patient
    $current_patient = $b_r[payrecpatient];

    $current_status = fdb_num_rows( fdb_query (
      "SELECT * FROM procrec
       WHERE (
         (procpatient    = '$current_patient') AND
         (procbilled     = '0') AND
         (procbillable   = '0') AND
         (procbalcurrent > '0')
       )"
      ) );
    if (($current_status < 1) or ($current_skip < $skip)) {
      if ($current_status >= 1) {
        // then we know this is just a skip...
        $current_skip++;
      }
      //echo "
      // <B>Skipping record # $current_patient</B><BR>
      //";
      next; // skip
    } else { // begin process patient

     // get current patient information
     $this_patient = new Patient ($current_patient);
     echo "
      <B>"._("Processing")." ".$this_patient->fullName()."
        (<A HREF=\"manage.php3?$auth&id=$current_patient\"
         >".$this_patient->local_record[ptid]."</A>)</B>
      <BR>\n\n
     ";
     flush ();

     // grab current insurance company
     if ($this_patient->local_record[ptdep] == 0) {
       $this_insco = $this_patient->insco[($b_r[payreclink])];
     } else { // if get from guarantor
       $guarantor = new Patient ($this_patient->local_record[ptdep]);
       $this_insco = $guarantor->insco[($b_r[payreclink])];
     }

     //$debug=true;

     // decide which ones we are generating
     $result = fdb_query ("SELECT a.* FROM payrec AS a,
                                           procrec AS b 
                           WHERE ( 
                             a.payreccat = '5' AND
                             a.payreclink < '3' AND
                             a.payrecpatient = '$current_patient' AND
                             a.payrecproc = b.id AND
                             b.procbillable = '0' AND
                             b.procbalcurrent > '0'
                           ) ORDER BY payrecpatient,payrecdt");

     if (!$result or ($result==0))
       die ("Malformed SQL query ($current_patient)");

     // set number of charges to zero
     $number_of_charges = 0;

     // create a new diagnosisSet stack
     $diag_set = new diagnosisSet (); // new stack of size 4

     if ($debug) echo "\n"._("Building patient information")."...<BR>\n";

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
     $ptssn           = $this_patient->local_record["ptssn"];
     $ptid            = $this_patient->local_record["ptid"];

     // relationship to guarantor
     $ptreldep[self]   = ( (($this_patient->ptreldep == "S") or
                            ($this_patient->local_record["ptdep"] == 0)) ?
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
       ( ($this_patient->ptempl == "y") ?
          $this_form[ffcheckchar] : " " );
     // no is not an option here. should be partime/fulltime student FIXME !!!
     $ptemployed[no] =
       ( !($this_patient->ptempl == "n") ?
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
     $related_other[no] =
       ( ( $other == "n" ) ? $this_form[ffcheckchar] : " " );

     // insco information
     if ($this_patient->local_record[ptdep] == 0) {
       $this_insco = new InsuranceCompany (
              $this_patient->local_record["ptins".($b_r[payreclink]+1)]);
       $insco[number]     = $this_patient->local_record["ptinsno".
                            ($b_r[payreclink]+1)];
       $insco[group]      = $this_patient->local_record["ptinsgrp".
                            ($b_r[payreclink]+1)];
     } else { // if there *is* a guarantor
       $this_insco = new InsuranceCompany (
              $guarantor->local_record["ptins".($b_r[payreclink]+1)]);
       $insco[number]     = $guarantor->local_record["ptinsno".
                            ($b_r[payreclink]+1)];
       $insco[group]      = $guarantor->local_record["ptinsgrp".
                            ($b_r[payreclink]+1)];
     } // end checking for insco
     
     $insco[name]       = $this_insco->inscoalias;
     $insco[line1]      = $this_insco->local_record[inscoaddr1];
     $insco[line2]      = $this_insco->local_record[inscoaddr2];
     $insco[city]       = $this_insco->local_record[inscocity];
     $insco[state]      = $this_insco->local_record[inscostate];
     $insco[zip]        = $this_insco->local_record[inscozip];

     // pull all insco mods
     unset ($inscomod);  // clear the array first
     for ($mod_loop=0;$mod_loop<count($this_insco->modifiers);$mod_loop++) {
       $mod_key = freemed_get_link_field ($this_insco->modifiers[$mod_loop],
                  "insmod", "insmod");
       $inscomod[$mod_key] = $this_form[ffcheckchar];
     } // end of modifiers loop

     // pull physician # for insco
     $insco[phyid]      = ( ($this_insco->local_record[inscogroup] < 1) ?
         "" :
         ($this_physician->getMapId($this_insco->local_record[inscogroup]))
         );

     // pull facility
     $this_facility     = freemed_get_link_rec ($default_facility, "facility");
     $fac[name]         = $this_facility[psrname];
     $fac[line1]        = $this_facility[psraddr1];
     $fac[line2]        = $this_facility[psraddr2];
     $fac[city]         = $this_facility[psrcity];
     $fac[state]        = $this_facility[psrstate];
     $fac[zip]          = $this_facility[psrzip];
     $fac[ein]          = $this_facility[psrein];

     // current date hashes
     $curdate[mmddyy]   = date ("m d y");
     $curdate[mmddyyyy] = date ("m d Y");
     $curdate[m]        = date ("m");
     $curdate[d]        = date ("d");
     $curdate[y]        = date ("Y");
     $curdate[sy]       = substr ($curdate[y], 2, 2);

     // pull referring physician information
     //$referring_physician = freemed_get_link_rec (
     //   $this_patient->local_record[ptrefdoc], "physician");
     //$refphy[name]      = $referring_physician[phyfname].
     //   ( !empty($referring_physician[phymname]) ? " " : "").
     //   $referring_physician[phymname]." ".
     //   $referring_physician[phylname];
     //$refphy[upin]      = $referring_physician[phyupin];

     // check for guarantor information
     if ($this_patient->local_record[ptdep] == 0) {
       // if self insured, transfer data to guarantor arrays
       //$guarname      = $ptname;  // assign name information
       //$guaraddr      = $ptaddr;  // assign address information
       //$guardob       = $ptdob;   // assign date of birth info
       //$guarsex       = $ptsex;   // assign gender information
       // clear all of the guarantor fields
       unset ($guarname);
       unset ($guaraddr);
       unset ($guardob);
       unset ($guarsex);
       unset ($guarphone);
     } else {
       // if it is someone else, get *their* information
       $guarname[last]    = $guarantor->local_record["ptlname"];
       $guarname[first]   = $guarantor->local_record["ptfname"];
       $guarname[middle]  = $guarantor->local_record["ptmname"];
       $guardob[full]     = $guarantor->local_record["ptdob"  ];
       $guardob[month]      = substr ($guardob[full], 5, 2);  
       $guardob[day]        = substr ($guardob[full], 8, 2);  
       $guardob[year]       = substr ($guardob[full], 0, 4);
       $guarsex[male]     = ( ($guarantor->ptsex == "m") ?
                               $this_form[ffcheckchar] : " " );
       $guarsex[female]   = ( ($guarantor->ptsex == "f") ?
                               $this_form[ffcheckchar] : " " );
       $guarsex[trans]    = ( ($guarantor->ptsex == "t") ?
                               $this_form[ffcheckchar] : " " );
       $guaraddr[line1]   = $guarantor->local_record["ptaddr1"  ];
       $guaraddr[line2]   = $guarantor->local_record["ptaddr2"  ];
       $guaraddr[city]    = $guarantor->local_record["ptcity"   ];
       $guaraddr[state]   = $guarantor->local_record["ptstate"  ];
       $guaraddr[zip]     = $guarantor->local_record["ptzip"    ];
       $guarphone[full]   = $guarantor->local_record["pthphone" ];

       $insco[number]     = $guarantor->local_record["ptinsno".
                            ($b_r[payreclink]+1)];
       $insco[group]      = $guarantor->local_record["ptinsgrp".
                            ($b_r[payreclink]+1)];
       // PULL INSCO  HERE IF GUARANTOR !!!!!!!!!!!!!!!!!!!!!
       //       FIIIIIIIIIX MEEEEEEEEEEEE!
     } // end checking for dependant

     if ($debug) echo "\nRunning through charges/procedures ... <BR>\n";
     flush();

     // zero current number of charges
     $number_of_charges = 0; $total_charges = 0; $total_paid = 0;
     // and zero the arrays
     for ($j=0;$j<=$this_form[ffloopnum];$j++)
       $itemdate[$j]   = $itemdate_m[$j]  = $itemdate_d[$j]  =
       $itemdate_y[$j] = $itemdate_sy[$j] = $itemcharges[$j] =
       $itemunits[$j]  = $itempos[$j]     = $itemvoucher[$j] =
       $itemcpt[$j]    = $itemcptmod[$j]  = $itemtos[$j]     =
       $itemdiagref[$j] = $itemauthnum[$j] = "";
     unset ($ref); // kill referring doc

     // grab form information form
     $this_form = freemed_get_link_rec ($whichform, "fixedform");

     // by default, render the form
     $render_form = true;

     // clear $diag_set in case of legacy
     unset ($diag_set);
     $diag_set = new diagnosisSet ();

     // queue all entries
     $first_procedure = 0;
     while ($r = fdb_fetch_array ($result)) {
       $p = freemed_get_link_rec ($r[payrecproc], "procrec");
        // kludge to get eoc info from procedure. 
	if (first_procedure == 0)
        {
           $eocs = explode (":", $p[proceoc]);
           if ($eocs[0])
           {
                $eoc = freemed_get_link_rec ($eocs[0], "eoc");
                // what is this related to?
                $employment = $eoc[eocrelemp];
                $related_employment[yes] =
                   ( ( $employment == "yes" ) ? $this_form[ffcheckchar] : " " );
                $related_employment[no]  =
                   ( ( $employment == "no" ) ? $this_form[ffcheckchar] : " " );
                $auto = $eoc[eocrelauto];
                $related_auto[yes] =
                   ( ( $auto == "yes" ) ? $this_form[ffcheckchar] : " " );
                $related_auto[no]  =
                   ( ( $auto == "no" ) ? $this_form[ffcheckchar] : " " );
                $related_auto[state] =    
                   ( ( $auto == "yes" ) ? $eoc[eocrelautostpr] : "  " );
                $other = $eoc[eocrelother];
                $related_other[yes] =
                   ( ( $other == "yes" ) ? $this_form[ffcheckchar] : " " );
                $related_other[no] =
                   ( ( $other == "no" ) ? $this_form[ffcheckchar] : " " );
                $first_procedure = 1;
                if ($debug) echo "\n$employment $auto $other<BR>\n";


           }
         }
       if ($debug) echo "\n"._("Retrieved procedure")." $r[payrecproc] <BR>\n";
       flush();

       if ($p[procbalcurrent]<=0) {
         $render_form = false; // don't render the form if 0
         next; // skip if no charge
       } else {
         $render_form = true; // reset to render form
       }

       $number_of_charges++; // increment number of charges

       if ($debug) echo "\nThis form, charge $number_of_charges <BR>\n";
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
         // echo "$number_of_charges > $this_form[ffloopnum] <BR>\n";
         flush();

         $ptdiag          = $diag_set->getStack();     // get pt diagnoses
         $current_balance = bcadd ($total_charges - $total_paid, 0, 2);
         $total_charges   = bcadd ($total_charges, 0, 2);
         $total_paid      = bcadd ($total_paid,    0, 2);

         // drop the current form to the buffer
         if ($render_form)
           $form_buffer .= render_fixedForm ($whichform);
         $render_form  = true;
         $total_paid = $total_charges   =
                       $current_balance = 0;  // zero the charges

         // reset the counter to 1, for the first...
         $number_of_charges = 1;

         // reset the diag_set array
         unset ($diag_set);
         $diag_set = new diagnosisSet ();

         // and zero the arrays
         for ($j=0;$j<=$this_form[ffloopnum];$j++)
           $itemdate[$j]    = $itemdate_m[$j]  = $itemdate_d[$j]  =
           $itemdate_y[$j]  = $itemdate_sy[$j] = $itemcharges[$j] =
           $itemunits[$j]   = $itempos[$j]     = $itemvoucher[$j] =
           $itemcpt[$j]     = $itemcptmod[$j]  = $itemdiagref[$j] =
           $itemauthnum[$j] = $itemtos[$j]     = "";
       } else {
         // DONT DO ANYTHING IN THIS CASE (PLACEHOLDER)       
       } // end checking if the set will fit

       // pull into current array
       if ($debug) echo "\nnumber of charges = $number_of_charges <BR>\n";
       flush();
       $cur_cpt = freemed_get_link_rec ($p[proccpt], "cpt");
       $tos_stack = fm_split_into_array ($cur_cpt[cpttos]);
       $this_tos = ( ($tos_stack[$cur_insco] < 1) ?
                      $cur_cpt[cptdeftos] :
                      $tos_stack[$cur_insco] );
       $this_auth = freemed_get_link_rec ($p[procauth], "authorizations");
       $authorized[authnum] = $this_auth[authnum];

       if ($p[procrefdoc]>0) {
         $ref_physician  = new Physician ($p[procrefdoc]);
         $ref[physician] = $ref_physician->fullName();
         $ref[upin]      = $ref_physician->local_record["phyupin"];
         $ref[date]      = $p[procrefdt] ;
         $ref[y]         = substr ($ref[date], 0, 4);
         $ref[m]         = substr ($ref[date], 5, 2);
         $ref[d]         = substr ($ref[date], 8, 2);
         $ref[sy]        = substr ($ref[date], 2, 2);
         $ref[mmddyy]    = $ref[m]."-".$ref[d]."-".$ref[sy];
         $ref[mmddyyyy]  = $ref[m]."-".$ref[d]."-".$ref[y]; 
       }

       // kill zeros in ref dates
       $ref[y] = ( ($ref[y]>0) ? $ref[y] : "" );
       $ref[d] = ( ($ref[d]>0) ? $ref[d] : "" );
       $ref[m] = ( ($ref[m]>0) ? $ref[m] : "" );

       $itemdate    [$number_of_charges] = $p[procdt];
       $itemdate_m  [$number_of_charges] = substr($p[procdt],     5, 2);
       $itemdate_d  [$number_of_charges] = substr($p[procdt],     8, 2);
       $itemdate_y  [$number_of_charges] = substr($p[procdt],     0, 4);
       $itemdate_sy [$number_of_charges] = substr($p[procdt],     2, 2);
       $itemcharges [$number_of_charges] = bcadd($p[procbalorig], 0, 2);
       $itemunits   [$number_of_charges] = bcadd($p[procunits],   0, 0);
       $itempos     [$number_of_charges] = "11";  // KLUDGE!! KLUDGE!!
       $itemvoucher [$number_of_charges] = $p[procvoucher];
       $itemcpt     [$number_of_charges] = $cur_cpt[cptcode];
       $itemtos     [$number_of_charges] =
          freemed_get_link_field ($this_tos, "tos", "tosname");
       $itemcptmod  [$number_of_charges] =
          freemed_get_link_field ($p[proccptmod], "cptmod", "cptmod");
       $itemdiagref [$number_of_charges] =
          $diag_set->xrefList ($p[procdiag1], $p[procdiag2],
                               $p[procdiag3], $p[procdiag4]);
       $itemauthnum [$number_of_charges] = $this_auth [authnum];

       $total_paid    += $p[procamtpaid];
       $total_charges += $itemcharges[$number_of_charges];
       if ($debug) echo "\ndiagref = $itemdiagref[$number_of_charges] <BR>\n";

     } // end of looping for all charges

     $ptdiag = $diag_set->getStack(); // get pt diagnoses
     $current_balance = bcadd($total_charges - $total_paid, 0, 2);
     $total_charges   = bcadd($total_charges, 0, 2);
     $total_paid      = bcadd($total_paid,    0, 2);

     // render last form
     if ($render_form)
       $form_buffer .= render_fixedForm ($whichform);
     $render_form = true; // reset to true for rendering the form
     $total_paid = $total_charges = $current_balance = 0;  // zero the charges

     $pat_processed++;
     $patient_forms[$pat_processed] = $this_patient->local_record["id"];
     if (($num_patients != 0) and ($pat_processed >= $num_patients))
       $still_going = false;

    } // end of conditional for checking for skip

   } // end of while there are no more patients

   #################### TAKE THIS OUT AFTER TESTING #######################
   #echo "<PRE>\n".prepare($form_buffer)."\n</PRE>\n";
   ########################################################################

   echo "
    <FORM ACTION=\"echo.php3/form.txt\" METHOD=POST>
     <CENTER>
      <$STDFONT_B><B>"._("Preview")."</B><$STDFONT_E>
     </CENTER>
     <BR>
     <TEXTAREA NAME=\"text\" ROWS=10 COLS=81
     >".prepare($form_buffer)."</TEXTAREA>
    <P>
    <CENTER>
     <SELECT NAME=\"type\">
      <OPTION VALUE=\"\">"._("Render to Screen")."
      <OPTION VALUE=\"application/x-rendered-text\">"._("Render to File")."
     </SELECT>
     <INPUT TYPE=SUBMIT VALUE=\""._("Get HCFA Rendered Text File")."\">
    </CENTER>
    </FORM>
    <P>
   ";

   // present the form so that we can mark as billed
   echo "
    <CENTER>
    <$STDFONT_B><B>"._("Mark as Billed")."</B><$STDFONT_E>
    </CENTER>
    <BR>
    <FORM ACTION=\"$page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"".prepare($_auth)."\">
     <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mark\">
   ";
   for ($i=1;$i<=$pat_processed;$i++) {
     $this_patient = new Patient ($patient_forms[$i]);
     echo "
       <INPUT TYPE=CHECKBOX NAME=\"processed$brackets\" 
        VALUE=\"".$patient_forms[$i]."\" CHECKED>
       ".$this_patient->fullName(false)."
       (<A HREF=\"manage.php3?$_auth&id=$patient_forms[$i]\"
        >".$this_patient->local_record["ptid"]."</A>) <BR>
     ";
   } // end looping for all processed patients
   echo "
    <P>
    <INPUT TYPE=SUBMIT VALUE=\""._("Mark as Billed")."\">
    </FORM>
    <P>
   ";

   freemed_display_box_bottom ();
   break; // end of action geninsform

  case "mark": // mark as billed action
   freemed_display_box_top (_("Mark as Billed"));
   if (count($processed)<1) {
    echo "
     <P>
     <CENTER>
      <$STDFONT_B><B>"._("Nothing set to be marked!")."</B><$STDFONT_E>
     </CENTER>
     <P>
     <CENTER>
      <A HREF=\"$page_name?$_auth\"
      ><$STDFONT_B>"._("Back")."<$STDFONT_E></A>
     </CENTER>
     <P>
    ";
   } else {
     for ($i=0;$i<count($processed);$i++) {
       echo "
         "._("Marking")." ".$processed[$i]." ... 
       ";
       $query = "UPDATE procrec
                 SET procbilled = '1'
                 WHERE (
                   (procpatient    = '".$processed[$i]."') AND
                   (procbilled     = '0') AND
                   (procbalcurrent > '0')
                 )";
       $result = fdb_query ($query);
       if ($result) { echo "$Done.<BR>\n"; }
        else        { echo "$ERROR<BR>\n"; }
     }
     echo "
      <P>
      <CENTER>
       <A HREF=\"$page_name?$_auth\"
       ><$STDFONT_B>"._("Back")."<$STDFONT_E></A>
      </CENTER>
      <P>
     ";
   } // end checking if there is work to do
   freemed_display_box_bottom ();
   break; // end of mark as billed action

  default:
   freemed_display_box_top (_("Fixed Forms Generation"));
   echo "
    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3
     VALIGN=MIDDLE ALIGN=CENTER>

    <TR>
     <TD COLSPAN=2>
      <CENTER>
       <$STDFONT_B><B>"._("Generate Insurance Claim Forms")."</B><$STDFONT_E>
      </CENTER>
     </TD>
    </TR>

    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"".prepare($_auth)."\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"geninsform\">

    <TR>
     <TD ALIGN=RIGHT>
      <CENTER>
       <$STDFONT_B>"._("Claim Form")." : <$STDFONT_E>
      </CENTER>
     </TD>
     <TD ALIGN=LEFT>
      <SELECT NAME=\"whichform\">
   ";
   $result = fdb_query ("SELECT * FROM fixedform WHERE fftype='1'
                         ORDER BY ffname, ffdescrip");
   while ($r = fdb_fetch_array ($result)) {
    echo "
     <OPTION VALUE=\"$r[id]\">".prepare($r[ffname])."
    ";
   } // end looping through results                         
   echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <CENTER>
       <$STDFONT_B>"._("Number of Patients")." : <$STDFONT_E>
      </CENTER>
     </TD>
     <TD ALIGN=LEFT>
   ".fm_number_select ("num_patients", 0, 200)."
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Skip # of Pats to Bill")." : <$STDFONT_E>
     </TD>
     <TD ALIGN=LEFT>
   ".fm_number_select ("skip", 0, 100)."
     </TD>
    </TR>

    <TR>
     <TD COLSPAN=2>
      <CENTER>
       <INPUT TYPE=SUBMIT VALUE=\""._("Go")."\">
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
