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
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";

	var $PACKAGE_MINIMUM_VERSION = "0.2.1";

	var $CATEGORY_NAME = "Billing";
	var $CATEGORY_VERSION = "0";

    var $form_buffer;
    var $pat_processed;
	var $formno;
	var $renderform_variables = array(
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
		"refphy",
		"insco",
		"fac",
		"rendfac",
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
		"authorized",
	    "taxid",
		"boxein",
		"boxssn"
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
				$result = $this->CheckforInsBills();
				if ($result==0)
				{
					echo "Nothing to bill for this coverage type.<BR>\n";
					freemed_display_box_bottom();
					freemed_display_html_bottom();
					DIE("");
				}
			
				while($row = $sql->fetch_array($result))
				{
					$this->GenerateFixedForms($row[procpatient], $row[proccurcovid]);
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
					echo "No patients to be billed.<BR>\n";
					freemed_display_box_bottom();
					freemed_display_html_bottom();
					DIE("");
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
		trigger_error("Bad action passed in generateforms module", E_USER_ERROR);

	}
/*

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
			$pat = $processed[$i];
			$procs = count($procids[$pat]);
			
			for ($x=0;$x<$procs;$x++)
			{
				$prc = $procids[$pat][$x];
				//echo "proc $prc for patient $pat<BR>";
       			// start of insert loop for billed legder entries
       			$query = "SELECT procbalcurrent,proccurcovid,proccurcovtp FROM procrec";
				$query .= " WHERE id='".$prc."'";
       			$result = $sql->query($query);
       			if (!$result)
       			{
       				echo "Mark failed getting procrecs<BR>";
       				DIE("Mark failed getting procrecs");
       			}
				//echo "proc query $query<BR>";
       			$bill_tran = $sql->fetch_array($result);
       			$cur_bal = $bill_tran[procbalcurrent];
          		$proc_id = $bill_tran[id];
          		$cov_id  = $bill_tran[proccurcovid];
          		$cov_tp  = $bill_tran[proccurcovtp];
				$payreccat = BILLED;
				$query = $sql->insert_query("payrec",
					array (
						"payrecdtadd" => $cur_date,
						"payrecdtmod" => $cur_date,
						"payrecpatient" => $pat,
						"payrecdt" => $cur_date,
						"payreccat" => $payreccat,
						"payrecproc" => $prc,
						"payreclink" => $cov_id,
						"payrecsource" => $cov_tp,
						"payrecamt" => $cur_bal,
						"payrecdescrip" => "Billed",
						"payreclock" => "unlocked"
						)	
					);
				//echo "payrec insert query $query<BR>";
           		$pay_result = $sql->query ($query);
           		if ($pay_result)
               		echo "<$STDFONT_B>Adding Bill Date to ledger.<$STDFONT_E><BR> \n";
           		else
               		echo "<$STDFONT_B>Failed Adding Bill Date to ledger!!<$STDFONT_E><BR> \n";

       			$query = "UPDATE procrec SET procbilled = '1',procdtbilled = '".addslashes($cur_date)."'".
						 " WHERE id = '".$prc."'";
				//echo "procrec update query $query<BR>";
       			$proc_result = $sql->query ($query);
       			if ($result) 
				{ 
					echo _("done").".<BR>\n"; 
				}
       			else        
				{ 
					echo _("ERROR")."<BR>\n"; 
				}

			} // end proces for patient loop
			
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
*/
	
	function GenerateFixedForms($parmpatient, $parmcovid)
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		while (list($k,$v)=each($this->renderform_variables)) global $$v;

	    // zero the buffer 
	    $buffer = "";
     	// get current patient information
     	$this_patient = new Patient ($parmpatient);
        if (!$this_patient)
			trigger_error("Failed retrieving patient", E_USER_ERROR);
			
     	echo "
      	<B>"._("Processing")." ".$this_patient->fullName()."
      	<BR>\n\n
     	";
     	flush ();

		$this_coverage = new Coverage($parmcovid);
        if (!$this_coverage)
		{
			trigger_error("No coverage", E_USER_ERROR);
		}

        // grab form information form
        $this->formno = freemed_get_link_rec ($whichform, "fixedform");

		// current date hashes
		$curdate[mmddyy]   = date ("m d y");
		$curdate[mmddyyyy] = date ("m d Y");
		$curdate[m]        = date ("m");
		$curdate[d]        = date ("d");
		$curdate[y]        = date ("Y");
		$curdate[sy]       = substr ($curdate[y], 2, 2);

		// grab all the procedures to bill for this patient
		$result = $this->GetProcstoBill($this_coverage->id,$this_coverage->covtype,$this_coverage->covpatient);

		if (!$result or ($result==0))
			trigger_error("Should have bills for $this_patient->local_record[ptid]");

		// procedure callback function will handle all the data
		$this->MakeStack($result,$this->formno[ffloopnum]);
		$this->pat_processed++;
		$this->patient_forms[$this->pat_processed] = $parmpatient;


   } // end generateFixed

	
   	function Insurance($stack)
   	{

		$row = $stack[0];
		if (!$row)
			return;

		if ($row[proccurcovtp] == PRIMARY)
			$this->BillPrimary($stack);
		else
			$this->BillSecondary($stack);
		return;
		
	}

   	function BillPrimary($stack)
   	{
		reset ($this->renderform_variables);
		while (list($k,$v)=each($this->renderform_variables)) global $$v;
		global $sql;

		$row = $stack[0];
		$this_coverage = new Coverage($row[proccurcovid]);
        if (!$this_coverage)
		{
			trigger_error("No coverage", E_USER_ERROR);
		}

		$this_insco = $this_coverage->covinsco;
        if (!$this_insco)
		{
				trigger_error("Insurance data fetch failed", E_USER_ERROR);
		}
		if (!is_object($this_insco))
		{
				trigger_error("Insurance company not object", E_USER_ERROR);
		}

		$insco[number]     = $this_coverage->covpatinsno;
		$insco[group]      = $this_coverage->covpatgrpno;
		$insco[name]       = ( (empty($this_insco->inscoalias)) ? $this_insco->insconame : $this_insco->inscoalias);
		$insco[line1]      = $this_insco->local_record[inscoaddr1];
		$insco[line2]      = $this_insco->local_record[inscoaddr2];
		$insco[city]       = $this_insco->local_record[inscocity];
		$insco[state]      = $this_insco->local_record[inscostate];
		$insco[zip]        = $this_insco->local_record[inscozip];
		return;

	}

   	function BillSecondary($stack)
   	{
		reset ($this->renderform_variables);
		while (list($k,$v)=each($this->renderform_variables)) global $$v;
		global $sql;

		$row = $stack[0];

		$this_coveragep = new Coverage($row[proccov1]);
        if (!$this_coveragep)
		{
			trigger_error("No primary coverage", E_USER_ERROR);
		}

		$this_inscop = $this_coveragep->covinsco;
        if (!$this_inscop)
		{
			trigger_error("Insurance data fetch failed", E_USER_ERROR);
		}
		if (!is_object($this_inscop))
		{
			trigger_error("Insurance company not object", E_USER_ERROR);
		}

		$this_coverage = new Coverage($row[proccurcovid]);
        if (!$this_coverage)
		{
			trigger_error("No coverage", E_USER_ERROR);
		}

		$this_insco = $this_coverage->covinsco;
        if (!$this_insco)
		{
				trigger_error("Insurance data fetch failed", E_USER_ERROR);
		}
		if (!is_object($this_insco))
		{
				trigger_error("Insurance company not object", E_USER_ERROR);
		}

		$insco[number]     = $this_coverage->covpatinsno;
		$insco[group]      = $this_coverage->covpatgrpno;
		$insco[name]       = ( (empty($this_insco->inscoalias)) ? $this_insco->insconame : $this_insco->inscoalias);
		$insco[line1]      = $this_insco->local_record[inscoaddr1];
		$insco[line2]      = $this_insco->local_record[inscoaddr2];
		$insco[city]       = $this_insco->local_record[inscocity];
		$insco[state]      = $this_insco->local_record[inscostate];
		$insco[zip]        = $this_insco->local_record[inscozip];

		// show primary as other

		return;

	}

   	function Patient($stack)
   	{
		// patient/insurance section is top half of form

		reset ($this->renderform_variables);
		while (list($k,$v)=each($this->renderform_variables)) global $$v;
		global $sql;

		$row = $stack[0];

		// get current patient information
		$this_patient = new Patient ($row[procpatient]);
		if (!$this_patient)
			trigger_error("Failed retrieving patient", E_USER_ERROR);

		$this_coverage = new Coverage($row[proccurcovid]);
        if (!$this_coverage)
		{
			trigger_error("No coverage", E_USER_ERROR);
		}

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
                           $this->formno[ffcheckchar] : " " );
     	$ptsex[female]   = ( ($this_patient->ptsex == "f") ?
                           $this->formno[ffcheckchar] : " " );
     	$ptsex[trans]    = ( ($this_patient->ptsex == "t") ?
                           $this->formno[ffcheckchar] : " " );
     	$ptssn           = $this_patient->local_record["ptssn"];
     	$ptid            = $this_patient->local_record["ptid"];
		
     	// relationship to guarantor
     	$ptreldep[self]   = ( (($this_coverage->covreldep == "S") or
                            ($this_coverage->covdep == 0)) ?
                            $this->formno[ffcheckchar] : " " );
     	$ptreldep[child]  = ( ($this_coverage->covreldep == "C") ?
                            $this->formno[ffcheckchar] : " " );
     	$ptreldep[spouse] = 
       		( (($this_coverage->covreldep == "H") or
          	($this_coverage->covreldep == "W")) ?
          	$this->formno[ffcheckchar] : " " );
     	$ptreldep[husband]= ( ($this_coverage->covreldep == "H") ?
                            $this->formno[ffcheckchar] : " " );
     	$ptreldep[wife]   = ( ($this_coverage->covreldep == "W") ?
                            $this->formno[ffcheckchar] : " " );
     	$ptreldep[other]  = ( ($this_coverage->covreldep == "O") ?
                            $this->formno[ffcheckchar] : " " );

     	// marital status
     	$ptmarital[single]    =
       		( ($this_patient->ptmarital == "single") ? $this->formno[ffcheckchar] : " " );
     	$ptmarital[married]   = 
       		( ($this_patient->ptmarital == "married") ?
          	$this->formno[ffcheckchar] : " " );
     	$ptmarital[divorced]  = 
       		( ($this_patient->ptmarital == "divorced") ?
          	$this->formno[ffcheckchar] : " " );
     	$ptmarital[separated] = 
       		( ($this_patient->ptmarital == "separated") ?
          	$this->formno[ffcheckchar] : " " );
     	$ptmarital[other]  = 
       		( (($this_patient->ptmarital == "divorced") OR 
       		  ($this_patient->ptmarital == "separated")) ?
          	$this->formno[ffcheckchar] : " " );

     	// employment status
     	$ptemployed[yes] =
       		( ($this_patient->ptempl == "y") ?
          	$this->formno[ffcheckchar] : " " );
     	// part time student
     	$ptemplpart[yes] =
       		( ($this_patient->ptempl == "p") ?
          	$this->formno[ffcheckchar] : " " );
     	// full time student
     	$ptemplfull[yes] =
       		( ($this_patient->ptempl == "f") ?
          	$this->formno[ffcheckchar] : " " );

     	// address information
     	$ptaddr[line1]   = $this_patient->local_record["ptaddr1"  ];
     	$ptaddr[line2]   = $this_patient->local_record["ptaddr2"  ];
     	$ptaddr[city]    = $this_patient->local_record["ptcity"   ];
     	$ptaddr[state]   = $this_patient->local_record["ptstate"  ];
     	$ptaddr[zip]     = $this_patient->local_record["ptzip"    ];
     	$ptaddr[country] = $this_patient->local_record["ptcountry"];
     	$ptphone[full]   = $this_patient->local_record["pthphone" ];

		if ($this_coverage->covdep == 0) 
		{
			// if self insured clear all of the guarantor fields
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
		}
		if ($this_coverage->covdep > 0) 
		{
			// patient is NOT the insured
			$guarantor = new Guarantor($this_coverage->id);
			if (!$guarantor)
				trigger_error("Guarantor information fetch failed", E_USER_ERROR);
			$guarname[last]    = $guarantor->guarlname;
			$guarname[first]   = $guarantor->guarfname;
			$guarname[middle]  = $guarantor->guarmname;
			$guardob[full]     = $guarantor->guardob;
			$guardob[month]      = substr ($guardob[full], 5, 2);  
			$guardob[day]        = substr ($guardob[full], 8, 2);  
			$guardob[year]       = substr ($guardob[full], 0, 4);
			$guarsex[male]     = ( ($guarantor->guarsex == "m") ?
								   $this->formno[ffcheckchar] : " " );
			$guarsex[female]   = ( ($guarantor->guarsex == "f") ?
								   $this->formno[ffcheckchar] : " " );
			$guarsex[trans]    = ( ($guarantor->guarsex == "t") ?
								   $this->formno[ffcheckchar] : " " );
			// address information
			if ($guarantor->guarsame) // pat and guar have same addr?
			{
				// yes use patient address
				$guaraddr[line1]   = $this_patient->local_record["ptaddr1"  ];
				$guaraddr[line2]   = $this_patient->local_record["ptaddr2"  ];
				$guaraddr[city]    = $this_patient->local_record["ptcity"   ];
				$guaraddr[state]   = $this_patient->local_record["ptstate"  ];
				$guaraddr[zip]     = $this_patient->local_record["ptzip"    ];
			}
			else
			{
				// use guarantor address
				$guaraddr[line1]   = $guarantor->guaraddr1;
				$guaraddr[line2]   = $guarantor->guaraddr2;
				$guaraddr[city]    = $guarantor->guarcity;
				$guaraddr[state]   = $guarantor->guarstate;
				$guaraddr[zip]     = $guarantor->guarzip;
			}
		}

		// eoc start
		// see if eoc exists else issue warning
		$eocs = explode (":", $row[proceoc]);
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
			   ( ( $employment == "yes" ) ? $this->formno[ffcheckchar] : " " );
			$related_employment[no]  =
			   ( ( $employment == "no" ) ? $this->formno[ffcheckchar] : " " );
			$auto = $eoc[eocrelauto];
			$related_auto[yes] =
			   ( ( $auto == "yes" ) ? $this->formno[ffcheckchar] : " " );
			$related_auto[no]  =
			   ( ( $auto == "no" ) ? $this->formno[ffcheckchar] : " " );
			$related_auto[state] =    
			   ( ( $auto == "yes" ) ? $eoc[eocrelautostpr] : "  " );
			$other = $eoc[eocrelother];
			$related_other[yes] =
			   ( ( $other == "yes" ) ? $this->formno[ffcheckchar] : " " );
			$related_other[no] =
			   ( ( $other == "no" ) ? $this->formno[ffcheckchar] : " " );
			if ($debug) echo "\n$employment $auto $other<BR>\n";

		}
		else
		{     // plug defaults if no eoc is present
		   $employment = "n"; // default
		   $related_employment[yes] =
			 ( ( $employment == "y" ) ? $this->formno[ffcheckchar] : " " );
		   $related_employment[no]  =
			 ( ( $employment == "n" ) ? $this->formno[ffcheckchar] : " " );
		   $auto = "n"; // default
		   $related_auto[yes] =
			 ( ( $auto == "y" ) ? $this->formno[ffcheckchar] : " " );
		   $related_auto[no]  =
			 ( ( $auto == "n" ) ? $this->formno[ffcheckchar] : " " );
		   $related_auto[state] =    
			 ( ( $auto == "y" ) ? $eoc_state_name : "  " );
		   $other = "n"; // default
		   $related_other[yes] =
			 ( ( $other == "y" ) ? $this->formno[ffcheckchar] : " " );
		   $related_other[no] =
			 ( ( $other == "n" ) ? $this->formno[ffcheckchar] : " " );
		}
		// eoc end

	  	return;

	}  // end of Patient

   	function ServiceLines($stack)
   	{
		reset ($this->renderform_variables);
		while (list($k,$v)=each($this->renderform_variables)) global $$v;
		global $sql;

		$row = $stack[0];

		$this_coverage = new Coverage($row[proccurcovid]);
        if (!$this_coverage)
		{
			trigger_error("No coverage", E_USER_ERROR);
		}

		$this_insco = $this_coverage->covinsco;
		if (!$this_insco)
		{
			trigger_error("Insurance data fetch failed", E_USER_ERROR);
		}

		// not the object just the id;
		$cur_insco = $this_coverage->local_record[covinsco];

		// here we should have date of first symptom, injury or last mestral
		// fix me

		// doctor link/information
		$this_physician  = new Physician
				   ($row["procphysician"]);
		$phy[name]       = $this_physician->fullName();
		$phy[practice]   = $this_physician->practiceName();
		if (empty($phy[practice]))
			$phy[practice] = $phy[name];
		$phy[addr1]      = $this_physician->local_record["phyaddr1a"];
		$phy[addr2]      = $this_physician->local_record["phyaddr2a"];
		$phy[city]       = $this_physician->local_record["phycitya" ];
		$phy[state]      = $this_physician->local_record["phystatea"];
		$phy[zip]        = $this_physician->local_record["phyzipa"  ];
		$phy[phone]      = $this_physician->local_record["phyphonea"];

		// pull physician # for insco
		$insco[phyid]      = ( ($this_insco->local_record[inscogroup] < 1) ?
		 "" :
		 ($this_physician->getMapId($this_insco->local_record[inscogroup]))
		 );

		if ($default_facility>0)
		{
		   $dfltfac = freemed_get_link_rec($default_facility,"facility");
		   $taxid = $dfltfac[psrein];
		   $boxein = "X";
		   $boxssn = "";
		}
		else
		{
		   $taxid = $this_physician->local_record["physsn"];
		   $boxssn = "X";
		   $boxein = "";

		}


		$this_facility     = freemed_get_link_rec ($row[procpos], "facility");
		$pos = $this_facility[psrpos];
		//echo "pos $pos<BR>";
		$cur_pos = freemed_get_link_rec($pos, "pos");
		$pos = $cur_pos[posname];
		if ($pos==0)
		  $pos=11;
		//echo "pos $pos<BR>";
		if ($pos > 12) // if done out of office
		{
		   $rendfac[name] = $this_facility[psrname];
		   $rendfac[addr1] = $this_facility[psraddr1];
		   $rendfac[city] = $this_facility[psrcity];
		   $rendfac[state] = $this_facility[psrstate];
		   $rendfac[zip] = $this_facility[psrzip];
		   $rendfac[ein] = $this_facility[psrein];
		}
		else
		{
		   $rendfac[name] = "SAME";
		   $rendfac[addr1] = "";
		   $rendfac[city] = "";
		   $rendfac[state] = "";
		   $rendfac[zip] = "";

		}
		if ($row[procrefdoc]>0)
		{
			$refdoc  = new Physician ($row[procrefdoc]);
			$refphy[upin]      = $refdoc->local_record[phyupin];
			$refphy[name]      = $refdoc->local_record[phyfname].", ".
							  $refdoc->local_record[phylname];
		}
		else
		{
			$refphy[upin] = "";
			$refphy[name] = "";

		}

		$this_auth = freemed_get_link_rec ($row[procauth], "authorizations");
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


		// zero current number of charges
		$number_of_charges = 0; $total_charges = 0; $total_paid = 0;
		// and zero the arrays
		for ($j=1;$j<=$this->formno[ffloopnum];$j++)
		{
		   $itemdate[$j]   = $itemdate_m[$j]  = $itemdate_d[$j]  =
		   $itemdate_y[$j] = $itemdate_sy[$j] = $itemcharges[$j] =
		   $itemunits[$j]  = $itempos[$j]     = $itemvoucher[$j] =
		   $itemcpt[$j]    = $itemcptmod[$j]  = $itemtos[$j]     =
		   $itemdiagref[$j] = $itemauthnum[$j] = "";
		}
		$diag_set = new diagnosisSet();

		$count = count($stack);

		for ($i=0;$i<$count;$i++)
		{	
			$row = $stack[$i];
			$diag_set->testAddSet($row[procdiag1], $row[procdiag2],
                                    $row[procdiag3], $row[procdiag4]);
		}
		
		for ($i=0;$i<$count;$i++)
		{
			$row = $stack[$i];
			$number_of_charges++;

			$cur_cpt = freemed_get_link_rec ($row[proccpt], "cpt");
        	$tos_stack = fm_split_into_array ($cur_cpt[cpttos]);
        	$this_tos = ( ($tos_stack[$cur_insco] < 1) ?
                  $cur_cpt[cptdeftos] :
                  $tos_stack[$cur_insco] );
			$itemdate    [$number_of_charges] = $row[procdt];
			$itemdate_m  [$number_of_charges] = substr($row[procdt],     5, 2);
			$itemdate_d  [$number_of_charges] = substr($row[procdt],     8, 2);
			$itemdate_y  [$number_of_charges] = substr($row[procdt],     0, 4);
			$itemdate_sy [$number_of_charges] = substr($row[procdt],     2, 2);
			$itemcharges [$number_of_charges] =
			($row[procamtallowed]) ? bcadd($row[procamtallowed], 0, 2) : bcadd($row[procbalorig], 0, 2);
			$itemunits   [$number_of_charges] = bcadd($row[procunits],   0, 0);
			//$itempos     [$number_of_charges] = "11";  // KLUDGE!! KLUDGE!!
			$itempos     [$number_of_charges] = $pos;
			$itemvoucher [$number_of_charges] = $row[procvoucher];
			$itemcpt     [$number_of_charges] = $cur_cpt[cptcode];
			$itemtos     [$number_of_charges] =
			  freemed_get_link_field ($this_tos, "tos", "tosname");
			$itemcptmod  [$number_of_charges] =
			  freemed_get_link_field ($row[proccptmod], "cptmod", "cptmod");
			$itemdiagref [$number_of_charges] =
			  $diag_set->xrefList ($row[procdiag1], $row[procdiag2],
								   $row[procdiag3], $row[procdiag4]);
			$itemauthnum [$number_of_charges] = $this_auth [authnum];
			$total_paid    += $row[procamtpaid];
			$total_charges += $itemcharges[$number_of_charges];
		}

		$ptdiag = $diag_set->getStack(); // get pt diagnoses
		$current_balance = bcadd($total_charges - $total_paid, 0, 2);
		$total_charges   = bcadd($total_charges, 0, 2);
		$total_paid      = bcadd($total_paid,    0, 2);

		return;		
   	} // end service lines

   function ProcCallBack($stack)
   {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		reset ($this->renderform_variables);
		while (list($k,$v)=each($this->renderform_variables)) global $$v;

		$count = count($stack);
		if ($count == 0)
			return;
		$row = $stack[0];

		$this->Patient($stack);       // first hals part1
		$this->Insurance($stack);     // first half part 2
		$this->ServiceLines($stack);  // second half
		$this->form_buffer .= render_fixedForm ($whichform);

		//

   }
/*
   function ShowBillsToMark()
   {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		reset ($this->rendorform_variables);
		while (list($k,$v)=each($this->renderform_variables)) global $$v;
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
       ".$this_patient->fullName(false)."
       (<A HREF=\"manage.php?$_auth&id=$patient_forms[$i]\"
        >".$this_patient->local_record["ptid"]."</A>) <BR>
     ";
     $pat = $this->patient_forms[$i];
     $patprocs = count($this->patient_procs[$pat]);
     //echo "procs for $pat is $patprocs<BR>";
     for ($x=0;$x<$patprocs;$x++)
     {
         echo "<INPUT TYPE=HIDDEN NAME=\"procids[".$pat."][".$x."]\"
         VALUE=\"".$this->patient_procs[$pat][$x]."\">\n";
     }

   } // end looping for all processed patients
   echo "
    <P>
    <INPUT TYPE=SUBMIT VALUE=\""._("Mark as Billed")."\">
    </FORM>
    <P>
   ";

		
	} // end ShowMarkBilled
*/


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
