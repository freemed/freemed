<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__GENERATEFORMS_MODULE_PHP__")) {

define (__GENERATEFORMS_MODULE_PHP__, true);

// class GenerateFormsModule extends freemedModule
class GenerateFormsModule extends freemedBillingModule {

	// override variables
	var $MODULE_NAME = "Generate Insurance Billing";
	var $MODULE_VERSION = "0.1";

	var $PACKAGE_MINIMUM_VERSION = "0.2.1";

	var $CATEGORY_NAME = "Billing";
	var $CATEGORY_VERSION = "0";

    var $form_buffer;
    var $pat_processed;
    var $patient_forms;
    var $patient_cov;
	var $rendorform_variables = array(
		"ptname",
		"ptdob",
		"ptsex",
		"ptid",
		"ptssn",
		"ptreldep",
		"ptmarital",
		"ptemployed",
		"ptemplpart",
		"ptemplfull",
		"ptaddr",
		"ptphone",
		"ptdiag",
		"phy",
		"ref",
		"insco",
		"fac",
		"curdate",
        "guarname",
        "guaraddr",
        "guardob",
        "guarsex",
        "guarphone",
        "itemdate",
		"itemdate_m",
		"itemdate_d",
        "itemdate_y",
		"itemdate_sy",
		"itemcharges",
        "itemunits",
	    "itempos",
        "itemvoucher",
        "itemcpt",
	    "itemcptmod",
	    "itemtos",
        "itemdiagref",
		"itemauthnum",
		"current_balance",
		"total_charges",
		"total_paid",
        "employment", 
        "related_employment",
        "related_auto",
        "related_other",
		"authorized"
		);

	// contructor method
	function GenerateFormsModule ($nullvar = "") {
		// call parent constructor
		$this->freemedBillingModule($nullvar);
	} // end function GenerateFormsModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) return false;
		return true;
	} // end function check_vars

	// override main function

	function addform()
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		if (!$been_here)
		{
			$this->view();
			return;
		}
		if ($viewaction=="geninsform")
		{
		    $this->form_buffer = "";
			$this->pat_processed = 0;
			if ($bill_request_type > 0)
			{
				$query = "SELECT covpatient,id FROM coverage WHERE covtype='$bill_request_type'";
				$result = $sql->query($query);
				if (!$sql->results($result)) 
				{
					DIE("No patients with this coverage type");
				}
			
				while($row = $sql->fetch_array($result))
				{	
					$this->GenerateFixedForms($row[covpatient], $row[id]);
				}
			}
			else
			{
				// patient bills
				$query = "SELECT DISTINCT procpatient FROM procrec WHERE proccurcovtp='$bill_request_type'
							AND procbalcurrent>'0'";
				$result = $sql->query($query);
				if (!$sql->results($result)) 
				{
					DIE("No patients to be Billed");
				}
			
				while($row = $sql->fetch_array($result))
				{	
					$this->GenerateFixedForms($row[procpatient], 0);
				}
			}
			if ($this->pat_processed > 0)
			{
				$this->ShowBillsToMark();
			}
			else
			{
				echo "
				<P>
				<CENTER>
				<$STDFONT_B><B>"._("Nothing to Bill!")."</B><$STDFONT_E>
				</CENTER>
				<P>
				<CENTER>
				<A HREF=\"$this->page_name?$_auth&module=$module\"
				><$STDFONT_B>"._("Return to Fixed Forms Generation Menu")."<$STDFONT_E></A>
				</CENTER>
				<P>
				";
			}
			return;

		} // end geninsform

		if ($viewaction=="mark")
		{
			$this->MarkBilled();
			return;
		}
		DIE("Something is wrong in generateforms");

	}

	function MarkBilled()
	{

		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

	   	if (count($processed)<1) 
		{
			echo "
		 	<P>
		 	<CENTER>
		  	<$STDFONT_B><B>"._("Nothing set to be marked!")."</B><$STDFONT_E>
		 	</CENTER>
		 	<P>
		 	<CENTER>
		  	<A HREF=\"$this->page_name?$_auth&module=$module\"
		  	><$STDFONT_B>"._("Return to Fixed Forms Generation Menu")."<$STDFONT_E></A>
		 	</CENTER>
		 	<P>
			";
			return;
       	} 
     	for ($i=0;$i<count($processed);$i++) 
		{
       		echo "
       		Marking ".$processed[$i]." ...<BR> 
       		";
       		// start of insert loop for billed legder entries
       		$query = "SELECT id,procbalcurrent,proccurcovid FROM procrec";
			$query .= " WHERE procpatient='$processed[$i]' AND proccurcovid='$proccovid[$i]'";
			$query .= " AND proccurcovtp='$billtype' AND procbilled='0' AND procbalcurrent>'0'";
       		$result = $sql->query($query);
       		if (!$result)
       		{
       			echo "Mark failed getting procrecs<BR>";
       			DIE("Mark failed getting procrecs");
       		}
       		while ($bill_tran = $sql->fetch_array($result))
       		{
       			$cur_bal = $bill_tran[procbalcurrent];
          		$proc_id = $bill_tran[id];
          		$cov_id  = $bill_tran[proccurcovid];
				$payreccat = BILLED;
				$query = $sql->insert_query("payrec",
					array (
						"payrecdtadd" => $cur_date,
						"payrecdtmod" => $cur_date,
						"payrecpatient" => $processed[$i],
						"payrecdt" => $cur_date,
						"payreccat" => $payreccat,
						"payrecproc" => $proc_id,
						"payreclink" => $cov_id,
						"payrecsource" => $billtype,
						"payrecamt" => $cur_bal,
						"payrecdescrip" => "Billed",
						"payreclock" => "unlocked"
						)	
					);

           		$pay_result = $sql->query ($query);
           		if ($pay_result)
               		echo "<$STDFONT_B>$Adding Bill Date to ledger.<$STDFONT_E><BR> \n";
           		else
               		echo "<$STDFONT_B>$Failed Adding Bill Date to ledger!!<$STDFONT_E><BR> \n";
       			$query = "UPDATE procrec SET procbilled = '1',procdtbilled = '".addslashes($cur_date)."'".
						 " WHERE id = '".$proc_id."'";
       			$proc_result = $sql->query ($query);
       			if ($result) 
				{ 
					echo _("done").".<BR>\n"; 
				}
       			else        
				{ 
					echo _("ERROR")."<BR>\n"; 
				}

       		}
     	} // end for processed
     	echo "
      	<P>
      	<CENTER>
       	<A HREF=\"$this->page_name?$_auth&module=$module\"
       	><$STDFONT_B>"._("Back")."<$STDFONT_E></A>
      	</CENTER>
      	<P>
     	";
	}

	function GenerateFixedForms($parmpatient, $parmcovid)
	{
		
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		while (list($k,$v)=each($this->rendorform_variables)) global $$v;


	    // zero the buffer and counter
	    $buffer = "";
	    $counter = 0;
	    $current_patient = 0;

	    //here start 
		 
	    // here end

	    // get list of all patient who need to be billed
	    // 0 = 1st insurance
	    // 1 = 2nd insurance
	    // 2 = 3rd insurance
	    // 3 = workers' comp
	    // 4 = patient/guarantor
	    $query = "SELECT procpatient from procrec
							  WHERE (proccurcovtp = '$bill_request_type' AND
									 proccurcovid = '$parmcovid' AND
									 procbalcurrent > '0' AND
									 procpatient = '$parmpatient' AND
                                     procbillable = '0' AND
                                     procbilled = '0')";
  
   		 $b_result = $sql->query($query);
                          

		 if (!$sql->results($b_result)) 
		 {
			return false;
	   	 } // if there is no result, end

		   // zero form buffer
		   //$form_buffer = "";

		   $pats_processed = 0;
		   $still_going    = true;
		   $current_skip   = 0;

		   // loop for all patients

    	// pull current patient
    	//$current_patient = $b_r[procpatient];
    	//if ($skip > 0)
    	//{
        //	if ($current_skip < $skip)
        //	{
        //  /  	$current_skip++;
        //    	continue;
        //	}
    	//}

     	// get current patient information
     	$this_patient = new Patient ($parmpatient);
        if (!$this_patient)
		{
			echo "Error no patient $current_patient<BR>";
			DIE("No Patient");
		}
			
     	echo "
      	<B>"._("Processing")." ".$this_patient->fullName()."
      	<BR>\n\n
     	";
     	flush ();
		$this_coverage = new Coverage($parmcovid);
        if (!$this_coverage)
		{
			if ($bill_request_type != 0)
			{
				echo "Error Coverage failure<BR>";
				DIE("No Coverage");
			}
		}
		
     	// grab current insurance company
     	//if ($this_patient->ptdep == 0) 
		//{
       	//	$this_insco = $this_patient->insco[$bill_request_type];
     	//	$ins_valid = $this_patient->payer[$bill_request_type]->local_record["payerinsco"];
     	//} 
		//else 
		//{ // if get from guarantor
       	//	$guarantor = new Patient ($this_patient->ptdep);
       	//	$this_insco = $guarantor->insco[$bill_request_type];
     	//	$ins_valid = $guarantor->payer[$bill_request_type]->local_record["payerinsco"];
     	//}
     	// make sure patient has prim sec ter or wc insurance
     	//if ( ($bill_request_type < 4) AND ($ins_valid == 0) )
     	//{
        // 	echo "<B>Error - Patient does not have insurance of this type</B><BR>\n";
        // 	flush();
     	//}
		
		


        // grab form information form
        $this_form = freemed_get_link_rec ($whichform, "fixedform");
     	// set number of charges to zero
     	$number_of_charges = 0;

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
     	$ptssn           = $this_patient->local_record["ptssn"];
     	$ptid            = $this_patient->local_record["ptid"];

     	// relationship to guarantor
     	$ptreldep[self]   = ( (($this_coverage->covreldep == "S") or
                            ($this_coverage->covdep == 0)) ?
                            $this_form[ffcheckchar] : " " );
     	$ptreldep[child]  = ( ($this_coverage->covreldep == "C") ?
                            $this_form[ffcheckchar] : " " );
     	$ptreldep[spouse] = 
       		( (($this_coverage->covreldep == "H") or
          	($this_coverage->covreldep == "W")) ?
          	$this_form[ffcheckchar] : " " );
     	$ptreldep[husband]= ( ($this_coverage->covreldep == "H") ?
                            $this_form[ffcheckchar] : " " );
     	$ptreldep[wife]   = ( ($this_coverage->covreldep == "W") ?
                            $this_form[ffcheckchar] : " " );
     	$ptreldep[other]  = ( ($this_coverage->covreldep == "O") ?
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
     	$ptmarital[other]  = 
       		( (($this_patient->ptmarital == "divorced") OR 
       		  ($this_patient->ptmarital == "separated")) ?
          	$this_form[ffcheckchar] : " " );

     	// employment status
     	$ptemployed[yes] =
       		( ($this_patient->ptempl == "y") ?
          	$this_form[ffcheckchar] : " " );
     	// part time student
     	$ptemplpart[yes] =
       		( ($this_patient->ptempl == "p") ?
          	$this_form[ffcheckchar] : " " );
     	// full time student
     	$ptemplfull[yes] =
       		( ($this_patient->ptempl == "f") ?
          	$this_form[ffcheckchar] : " " );

     	// no is not an option here. should be partime/fulltime student FIXME !!!
     	//$ptemployed[no] =
     	//  ( !($this_patient->ptempl == "n") ?
     	//     $this_form[ffcheckchar] : " " );

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

		$this_insco = $this_coverage->covinsco;
        if (!$this_insco)
		{
			if ($bill_request_type != 0)
			{
				echo "Error insco failure<BR>";
				DIE("No Insurance");
			}
		}
		if (!is_object($this_insco))
		{
			if ($bill_request_type != 0)
			{
				echo "Error insco failure<BR>";
				DIE("No Insurance");
			}
		}
     	// insco information
     	//if ($this_patient->ptdep == 0) {
       	//	$this_insco = new InsuranceCompany (
        //    $this_patient->payer[$bill_request_type]->local_record["payerinsco"]);
       	//	$insco[number]     = $this_patient->payer[$bill_request_type]->local_record["payerpatientinsno"];
       	//	$insco[group]     = $this_patient->payer[$bill_request_type]->local_record["payerpatientgrp"];
     	//} 
		//else 
		//{ // if there *is* a guarantor
       	//	$this_insco = new InsuranceCompany (
        //    $guarantor->payer[$bill_request_type]->local_record["payerinsco"]);
       	//	$insco[number]     = $guarantor->payer[$bill_request_type]->local_record["payerpatientinsno"];
       //		$insco[group]      = $guarantor->payer[$bill_request_type]->local_record["payerpatientgrp"];
     //	} // end checking for insco
     
     $insco[number]     = $this_coverage->covpatinsno;
     $insco[group]      = $this_coverage->covpatgrpno;
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
     if ($debug) echo "\n$default_facility<BR>\n";
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
     if ($this_coverage->covdep == 0) {
       // if self insured, transfer data to guarantor arrays
       // clear all of the guarantor fields
       $guarname[last] = "";
       $guarname[first] = "";
       $guarname[middle] = "";
       $guardob[full] = "";
       $guardob[month] = "";
       $guardob[day] = "";
       $guardob[year] = "";
       $guarsex[male] = "";
       $guarsex[female] = "";
       $guarsex[trans] = "";
       $guaraddr[line1] = "";
       $guaraddr[line2] = "";
       $guaraddr[city] = "";
       $guaraddr[state] = "";
       $guaraddr[zip] = "";
       $guarphone[full] = "";
     } else {
       // if it is someone else, get *their* information
	   $guarantor = new Guarantor($this_coverage->id);
	   if (!$guarantor)
	   {
		   echo "Guarantor failed<BR>";
		   DIE("Guarantor failed");
	   }	   
       $guarname[last]    = $guarantor->guarlname;
       $guarname[first]   = $guarantor->guarfname;
       $guarname[middle]  = $guarantor->guarmname;
       $guardob[full]     = $guarantor->guardob;
       $guardob[month]      = substr ($guardob[full], 5, 2);  
       $guardob[day]        = substr ($guardob[full], 8, 2);  
       $guardob[year]       = substr ($guardob[full], 0, 4);
       $guarsex[male]     = ( ($guarantor->guarsex == "m") ?
                               $this_form[ffcheckchar] : " " );
       $guarsex[female]   = ( ($guarantor->guarsex == "f") ?
                               $this_form[ffcheckchar] : " " );
       $guarsex[trans]    = ( ($guarantor->guarsex == "t") ?
                               $this_form[ffcheckchar] : " " );
       $guaraddr[line1]   = $guarantor->guaraddr1;
       $guaraddr[line2]   = $guarantor->guaraddr2;
       $guaraddr[city]    = $guarantor->guarcity;
       $guaraddr[state]   = $guarantor->guarstate;
       $guaraddr[zip]     = $guarantor->guarzip;
       //$guarphone[full]   = $guarantor->local_record["pthphone" ];

       //$insco[number]     = $guarantor->payer[$bill_request_type]->local_record["payerpatientinsno"];
       //$insco[group]      = $guarantor->payer[$bill_request_type]->local_record["payerpatientgrp"];
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
     //$this_form = freemed_get_link_rec ($whichform, "fixedform");

     // by default, render the form
     $render_form = true;

     // clear $diag_set in case of legacy
     unset ($diag_set);
     $diag_set = new diagnosisSet ();

     //
     // grab all the procedure for this patient
     //
	    $query = "SELECT * FROM procrec
							  WHERE (proccurcovtp = '$bill_request_type' AND
									 proccurcovid = '$parmcovid' AND
									 procbalcurrent > '0' AND
									 procpatient = '$parmpatient' AND
                                     procbillable = '0' AND
                                     procbilled = '0') 
                           	  ORDER BY proceoc,procauth,procdt";
     $result = $sql->query ($query);

     if (!$result or ($result==0))
       die ("Malformed SQL query ($current_patient)");

     // queue all entries   FOR EACH PROCEDURE to BILL
     $first_procedure = 0;
     while ($r = $sql->fetch_array ($result)) {
       //$p = freemed_get_link_rec ($r[payrecproc], "procrec");
       if ($first_procedure == 0)
       {
	   $prev_auth = $r["procauth"];
           $prev_eoc = $r["proceoc"];
           $prev_key = $prev_eoc.$prev_auth;
           $first_procedure = 1;
       }
	
       $cur_auth = $r["procauth"];
       $cur_eoc = $r["proceoc"];
       $cur_key = $cur_eoc.$cur_auth;

       if ($debug) echo "\nRetrieved procedure $r[payrecproc] <BR>\n";
       flush();

       if ($r[procbalcurrent]<=0) {
         $render_form = false; // don't render the form if 0
         continue; // skip if no charge
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
       if (!($diag_set->testAddSet ($r[procdiag1], $r[procdiag2],
                                    $r[procdiag3], $r[procdiag4])) OR
            ($number_of_charges > $this_form[ffloopnum]         )  OR
            ($prev_key != $cur_key) )
       {
			echo "Control break";
         if ($prev_key != $cur_key)
         {
              $prev_key = $cur_key;
         }
         if ($debug) echo "\nNew form time ... <BR>\n";
         // echo "$number_of_charges > $this_form[ffloopnum] <BR>\n";
         //flush();

         $ptdiag          = $diag_set->getStack();     // get pt diagnoses
         $current_balance = bcadd ($total_charges - $total_paid, 0, 2);
         $total_charges   = bcadd ($total_charges, 0, 2);
         $total_paid      = bcadd ($total_paid,    0, 2);

         // drop the current form to the buffer
         if ($render_form)
           $this->form_buffer .= render_fixedForm ($whichform);
         $render_form  = true;
         $total_paid = $total_charges   =
                       $current_balance = 0;  // zero the charges

         // reset the counter to 1, for the first...
         $number_of_charges = 1;

         // reset the diag_set array
         unset ($diag_set);
         $diag_set = new diagnosisSet ();
         $test_AddSet = $diag_set->testAddSet ($r[procdiag1], 
						$r[procdiag2], 
						$r[procdiag3], 
						$r[procdiag4]);
         if (!$test_AddSet)
            DIE("AddSet failed!!");

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
       $cur_cpt = freemed_get_link_rec ($r[proccpt], "cpt");
       $tos_stack = fm_split_into_array ($cur_cpt[cpttos]);
       $this_tos = ( ($tos_stack[$cur_insco] < 1) ?
                      $cur_cpt[cptdeftos] :
                      $tos_stack[$cur_insco] );
       $this_auth = freemed_get_link_rec ($r[procauth], "authorizations");
       $authorized[authnum] = $this_auth[authnum];

       if (!$this_auth[authnum])
       {
           echo "<B>Warning: Procedure not Authorized!!</B><BR>\n";
           flush();
       }
       else
       if (!date_in_range($cur_date,$this_auth[authdtbegin],$this_auth[authdtend]))
       {
           echo "<B>Warning: Authorization $this_auth[authnum] has expired!!</B><BR>\n";
           flush();

       }

       // eoc start
       // see if eoc exists else issue warning
       $eocs = explode (":", $r[proceoc]);
       if (!$eocs[0])
       {
           echo "<B>Warning: No EOC for this Procedure!!</B><BR>\n";
           flush();
       }
       // if we have an eoc use it else set the defaults to no
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
            $related_auto[state] =    // FIIIIIIIX  MEEEEEEEEEE!!!
               ( ( $auto == "yes" ) ? $eoc[eocrelautostpr] : "  " );
            $other = $eoc[eocrelother];
            $related_other[yes] =
               ( ( $other == "yes" ) ? $this_form[ffcheckchar] : " " );
            $related_other[no] =
               ( ( $other == "no" ) ? $this_form[ffcheckchar] : " " );
            if ($debug) echo "\n$employment $auto $other<BR>\n";

       }
       else
       {     // plug defaults if no eoc is present
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
       }
        // eoc end

       if ($r[procrefdoc]>0) {
         $ref_physician  = new Physician ($r[procrefdoc]);
         $ref[physician] = $ref_physician->fullName();
         $ref[upin]      = $ref_physician->local_record["phyupin"];
         $ref[date]      = $r[procrefdt] ;
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

       $itemdate    [$number_of_charges] = $r[procdt];
       $itemdate_m  [$number_of_charges] = substr($r[procdt],     5, 2);
       $itemdate_d  [$number_of_charges] = substr($r[procdt],     8, 2);
       $itemdate_y  [$number_of_charges] = substr($r[procdt],     0, 4);
       $itemdate_sy [$number_of_charges] = substr($r[procdt],     2, 2);
       $itemcharges [$number_of_charges] = 
   	    ($r[procamtallowed]) ? bcadd($r[procamtallowed], 0, 2) : bcadd($r[procbalorig], 0, 2);
       $itemunits   [$number_of_charges] = bcadd($r[procunits],   0, 0);
       $itempos     [$number_of_charges] = "11";  // KLUDGE!! KLUDGE!!
       $itemvoucher [$number_of_charges] = $r[procvoucher];
       $itemcpt     [$number_of_charges] = $cur_cpt[cptcode];
       $itemtos     [$number_of_charges] =
          freemed_get_link_field ($this_tos, "tos", "tosname");
       $itemcptmod  [$number_of_charges] =
          freemed_get_link_field ($r[proccptmod], "cptmod", "cptmod");
       $itemdiagref [$number_of_charges] =
          $diag_set->xrefList ($r[procdiag1], $r[procdiag2],
                               $r[procdiag3], $r[procdiag4]);
       $itemauthnum [$number_of_charges] = $this_auth [authnum];

       $total_paid    += $r[procamtpaid];
       $total_charges += $itemcharges[$number_of_charges];
       if ($debug) echo "\ndiagref = $itemdiagref[$number_of_charges] <BR>\n";

     } // end of looping for all charges

     $ptdiag = $diag_set->getStack(); // get pt diagnoses
     $current_balance = bcadd($total_charges - $total_paid, 0, 2);
     $total_charges   = bcadd($total_charges, 0, 2);
     $total_paid      = bcadd($total_paid,    0, 2);

     // render last form
	
     if ($render_form)
       $this->form_buffer .= render_fixedForm ($whichform);
     $render_form = true; // reset to true for rendering the form
     $total_paid = $total_charges = $current_balance = 0;  // zero the charges

     $this->pat_processed++;
     $this->patient_forms[$this->pat_processed] = $parmpatient;
     $this->patient_cov[$this->pat_processed] = $parmcovid;
     //if (($num_patients != 0) and ($pat_processed >= $num_patients))
     //  $still_going = false;


   } // end generateFixed

   function ShowBillsToMark()
   {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		while (list($k,$v)=each($this->rendorform_variables)) global $$v;
   #################### TAKE THIS OUT AFTER TESTING #######################
   #echo "<PRE>\n".prepare($form_buffer)."\n</PRE>\n";
   ########################################################################

   echo "
    <FORM ACTION=\"echo.php/form.txt\" METHOD=POST>
     <CENTER>
      <$STDFONT_B><B>"._("Preview")."</B><$STDFONT_E>
     </CENTER>
     <BR>
     <TEXTAREA NAME=\"text\" ROWS=10 COLS=81
     >".prepare($this->form_buffer)."</TEXTAREA>
    <P>
    <CENTER>
     <SELECT NAME=\"type\">
      <OPTION VALUE=\"\">"._("Render to Screen")."
      <OPTION VALUE=\"application/x-rendered-text\">Render to File
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
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"$_auth\">
     <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
     <INPUT TYPE=HIDDEN NAME=\"viewaction\" VALUE=\"mark\">
     <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"$module\">
     <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"$been_here\">
     <INPUT TYPE=HIDDEN NAME=\"billtype\" VALUE=\"$bill_request_type\">
   ";
   for ($i=1;$i<=$this->pat_processed;$i++) {
     $this_patient = new Patient ($this->patient_forms[$i]);
     echo "
       <INPUT TYPE=CHECKBOX NAME=\"processed$brackets\" 
        VALUE=\"".$this->patient_forms[$i]."\" CHECKED>
       <INPUT TYPE=HIDDEN NAME=\"proccovid$brackets\" 
        VALUE=\"".$this->patient_cov[$i]."\" CHECKED>
       ".$this_patient->fullName(false)."
       (<A HREF=\"manage.php?$_auth&id=$patient_forms[$i]\"
        >".$this_patient->local_record["ptid"]."</A>) <BR>
     ";
   } // end looping for all processed patients
   echo "
    <P>
    <INPUT TYPE=SUBMIT VALUE=\""._("Mark as Billed")."\">
    </FORM>
    <P>
   ";

		
	} // end ShowMarkBilled

	function view()
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
	
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

		<FORM ACTION=\"$this->page_name\" METHOD=POST>
		<INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"".prepare($_auth)."\">
		<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
		<INPUT TYPE=HIDDEN NAME=\"viewaction\" VALUE=\"geninsform\">
		<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"$module\">

		<TR>
		 <TD ALIGN=RIGHT>
		  <CENTER>
		   <$STDFONT_B>Claim Form : <$STDFONT_E>
		  </CENTER>
		 </TD>
     	<TD ALIGN=LEFT>
      	<SELECT NAME=\"whichform\">
   		";
	   $result = $sql->query ("SELECT * FROM fixedform WHERE fftype='1'
							 ORDER BY ffname, ffdescrip");
	   while ($r = $sql->fetch_array ($result)) {
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
		   <$STDFONT_B>"._("Number of Patients")." :"." <$STDFONT_E>
		  </CENTER>
		 </TD>
		 <TD ALIGN=LEFT>
	   ".fm_number_select ("num_patients", 0, 200)."
		 </TD>
		</TR>
	   ";

	   echo "
		<TR>
		 <TD ALIGN=RIGHT>
		  <$STDFONT_B>"._("Skip # of Pats to Bill :")."<$STDFONT_E>
		 </TD>
		 <TD ALIGN=LEFT>
	   ".fm_number_select ("skip", 0, 100)."
	   ";
	   echo "
		 </TD>
		</TR>
		<TR>
		   <TD ALIGN=RIGHT>
			<$STDFONT_B>To : <$STDFONT_E>
		   </TD><TD ALIGN=LEFT>
			<SELECT NAME=\"bill_request_type\">
         <OPTION VALUE=\"0\">"._("Patient")."
			 <OPTION VALUE=\"1\">"._("1st Insurance")."
			 <OPTION VALUE=\"2\">"._("2nd Insurance")."
         <OPTION VALUE=\"3\">"._("3rd Insurance")."
         <OPTION VALUE=\"4\">"._("Worker's Comp")."
        </SELECT>
		<INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"1\">
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
	} // end view functions
	

} // end class GenerateFormsModule

register_module("GenerateFormsModule");

} // end if not defined

?>
