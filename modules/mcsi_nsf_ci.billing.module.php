<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__COMMERCIALMCSIFORMS_MODULE_PHP__")) {

define (__COMMERCIALMCSIFORMS_MODULE_PHP__, true);

// class CommercialMCSIFormsModule extends freemedModule
class CommercialMCSIFormsModule extends freemedBillingModule {

	// override variables
	var $MODULE_NAME = "MCSI NSF Commercial";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";

	var $PACKAGE_MINIMUM_VERSION = "0.2.1";

	var $CATEGORY_NAME = "Billing";
	var $CATEGORY_VERSION = "0.1";

	var $bill_request_type;
	var $ins_upin = array("HCPHM", "HCPMC", "19572");
	var $ins_medicaid = array("26374", "26375", "MSC33", "SET22", "SPH11", "88833");
	var $ins_commercial = array("MSC11","MSC22","MSC33","88811","88822","88833","75201","19572","94999");
    var $form_buffer;
    var $batchno = "0000";
    var $batchid = "000000";
    var $subno = "000000";
    var $pat_processed;
    var $formno;
    var $insmod;

	var $record_types = array(
		"aa0" => "1",
		"ba0" => "2",
		"ba1" => "3",
		"ca0" => "4",
		"cb0" => "5",
		"da0" => "6",
		"da1" => "7",
		"da2" => "8",
		"ea0" => "9",
		"ea1" => "10",
		"fa0" => "11",
		"fb0" => "12",
		"fb1" => "13",
		"xa0" => "14",
		"ya0" => "15",
		"za0" => "16",
		"gu0" => "17"
		);

	var $rendorform_variables = array(
		"aa0",
		"ba0",
		"ba1",
		"ca0",
		"cb0",
		"da0",
		"da1",
		"da2",
		"ea0",
		"ea1",
		"fa0",
		"fb0",
		"fb1",
		"gu0",
		"ha0",
		"xa0",
		"ya0"
		);

	// contructor method
	function CommercialMCSIFormsModule ($nullvar = "") {
		// call parent constructor
		$this->freemedBillingModule($nullvar);
	} // end function CommercialFormsModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) return false;
		return true;
	} // end function check_vars

	// override main function


	function addform() {
		global $display_buffer;
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

			$result = $this->CheckforInsBills();
			if ($result == 0)
			{
				DIE("No patients with this coverage type");
			}
		
			while($row = $sql->fetch_array($result))
			{	
				$coverage = new Coverage($row[proccurcovid]);
				if (!$coverage)
					DIE("Failed getting coverage");
				// commercial insurers only
				if ($coverage->covinsco) 
				{
					$insmod = freemed::get_link_rec($coverage->covinsco->modifiers[0],"insmod");
					if (!$insmod)
						DIE("Failed getting insurance modifier");
					if ( ($insmod[insmod] == "CI") OR ($insmod[insmod] == "CH") )
					{
						$this->insmod = $insmod[insmod];
						$this->bill_request_type = $row[proccurcovtp];
						$this->GenerateFixedForms($row[procpatient], $row[proccurcovid]);

					}
				}
			}
	
			if (!empty($this->form_buffer))
			{
				$new_buffer = $this->FileHeader($userid,$password);
				$this->form_buffer = $new_buffer.$this->form_buffer;
				$this->form_buffer = $this->FileTrailer($this->form_buffer);	
				//$new_buffer="";
				$recs = explode("\n",$this->form_buffer);
				$count = count($recs);
				if ($count == 0)
				{
					$display_buffer .= "Error No records generated<BR>";
				}
				
				//$this->form_buffer = $new_buffer;
				if ($write_to_file)
				{
					$file_buffer = "";
					for ($i=0;$i<$count;$i++)
					{
						// file does not contain the newline char
						$file_buffer .= $recs[$i];
						$file_buffer .= "\n";
					}
					//$filename = PHYSICAL_LOCATION_BILLS."/mcsi_comm_bills-".$cur_date.gmdate("Hi").".data";
					$billfilename = "/mcsi_comm_bills-".$cur_date.gmdate("Hi").".data";
					$dirname = "/tmp";
					$httpdir = "/bills";
					$httpfilename = $httpdir.$billfilename;
					$filename = $dirname.$billfilename;
        			$fp = fopen($filename,"w");

        			if (!$fp)
        			{
            			$display_buffer .= "Error opening $filename<BR>";
        			}

        			$rc = fwrite($fp,$file_buffer);

        			if ($rc <= 0)
            			$display_buffer .= "Error writing $filename<BR>";
					else
        				$display_buffer .= "Wrote bills to <A HREF=\"$httpfilename\">$httpfilename</A><BR>";
				}

			}
			if ($this->pat_processed > 0)
			{
				$preview = ($write_to_file) ? 0 : 1; // no preview if written to file
				
				$this->ShowBillsToMark($preview);
			}
			else
			{
				$display_buffer .= "
				<P>
				<CENTER>
				<B>"._("Nothing to Bill!")."</B>
				</CENTER>
				<P>
				<CENTER>
				<A HREF=\"$this->page_name?module=$module\"
				>"._("Return to Fixed Forms Generation Menu")."</A>
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

	function Insurer($procstack) {
		global $display_buffer;
		$bill_request_type = $this->bill_request_type;;

		if ($bill_request_type == PRIMARY)
			$buffer = $this->BillPrimary($procstack);
		else
			$buffer = $this->BillSecondary($prockstack);
		return $buffer;

	}

	function BillSecondary($procstack) {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		unset($GLOBALS[da0]);
		unset($GLOBALS[da1]);
		unset($GLOBALS[da2]);
		global $da0,$da1,$da2;

		$row = $procstack[0]; // all rows are the same
		$cov = $row[proccurcovid];
		$cov2= $row[proccov1];    // we need primary ins if billing secondary
		$pat = $row[procpatient];

		$da0[recid] = "DA0";
		$da1[recid] = "XXX";
		$da2[recid] = "XXX";

		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			$display_buffer .= "Error Insurer no coverage<BR>";
			return;
		}

		$insco = $coverage->covinsco;
		if (!$insco)
		{
			$display_buffer .= "Error Insurer no insurance<BR>";
			return;
		}

		// cov2 is actually the primary coverage
		$coverage2 = new Coverage($cov2);
		if (!$coverage2)
		{
			$display_buffer .= "Error Insurer no primary coverage<BR>";
			return;
		}

		$insco2 = $coverage2->covinsco;
		if (!$insco2)
		{
			$display_buffer .= "Error Insurer no primary insurance<BR>";
			return;
		}

		$patient = new Patient($pat);
		if (!$patient)
		{
			$display_buffer .= "Error Insurer no patient<BR>";
			return;
		}
			
		$da0[seqno] = "01";
		$da0[patcntl] = $ca0[patcntl];
		$da0[clmfileind] = "I"; // show primary as info
		$da0[clmsource] = " "; // default unknown

		$mod = $insco2->modifiers[0];
	
		// find source of pay for primary
		// we *know* the secondary in commercial "F"	

		if ($mod=="WC")
			$da0[clmsource] = "B";
		if ($mod=="CI")
			$da0[clmsource] = "F";
		if ($mod=="BL")
			$da0[clmsource] = "G";
		if ($mod=="HM")
			$da0[clmsource] = "I";
		if ($mod=="FI")
			$da0[clmsource] = "J";
		if ($mod=="CH")
			$da0[clmsource] = "H";

		$da0[payerid] = $this->CleanNumber($insco2->local_record[inscoid]); // NAIC #
		$da0[payername] = $this->CleanChar($insco2->insconame);
		$da0[patgrpno] = $this->CleanNumber($coverage2->covpatgrpno);

		if ($coverage2->local_record[covbenasgn] == 1)	
			$da0[assign] = "Y";
		else
			$da0[assign] = "N";

		//$da0[assign] = "Y";
		$da0[patsigsrc] = "C"; // signed HCFA 1500 on file.

		if ($mod == "CH")
        {
			$fac_row=0;
			$fac_row = freemed::get_link_rec($row[procpos], "facility");

        	if ($fac_row)
        	{
				$da0[ppoid] = $fac_row[psrein];
			}
			$covtpid = $coverage2->local_record[covinstp];
			if ($covtpid > 0)	
			{
				$covtype = freemed::get_link_rec($covtpid,"covtypes");
				$da0[instypcd] = $covtype[covtpname];
			}
		}

		
		$da0[patrel] = $this->GetRelationShip($coverage2->covreldep,"NSF");

		$da0[patidno] = $this->CleanNumber($coverage2->covpatinsno);

		if ($coverage2->covdep == 0)  // patient is the insured
		{
			$da0[insrdlname] = $this->CleanChar($patient->ptlname);
			$da0[insrdfname] = $this->CleanChar($patient->ptfname);
			$da0[insrdmi] = $this->CleanChar($patient->ptmname);
			$da0[insrdsex] = $this->CleanChar($patient->ptsex);
			$da0[insrddob] = $this->CleanNumber($patient->ptdob);
			$addr1 = $this->CleanChar($patient->local_record[ptaddr1]);
			$city = $this->CleanChar($patient->local_record[ptcity]);
			$state = $this->CleanChar($patient->local_record[ptstate]);
			$zip = $this->CleanNumber($patient->local_record[ptzip]);
	
		}
		else
		{
			$guarantor = new Guarantor($coverage2->covdep);
			if (!$guarantor)
			{
				$display_buffer .= "Error Insurer guarantor failed<BR>";
				return;
			}	
			$da0[insrdlname] = $this->CleanChar($guarantor->guarlname);
			$da0[insrdfname] = $this->CleanChar($guarantor->guarfname);
			$da0[insrdmi] = $this->CleanChar($guarantor->guarmname);
			$da0[insrdsex] = $this->CleanChar($guarantor->guarsex);
			$da0[insrddob] = $this->CleanNumber($guarantor->guardob);

			if ($guarantor->guarsame)  // guar addr same as patient?
			{
				$addr1 = $this->CleanChar($patient->local_record[ptaddr1]);
				$city = $this->CleanChar($patient->local_record[ptcity]);
				$state = $this->CleanChar($patient->local_record[ptstate]);
				$zip = $this->CleanNumber($patient->local_record[ptzip]);
			}
			else
			{
				$addr1 = $this->CleanChar($guarantor->guaraddr1);
				$city = $this->CleanChar($guarantor->guarcity);
				$state = $this->CleanChar($guarantor->guarstate);
				$zip = $this->CleanNumber($guarantor->guarzip);
			}

		}

		if ($row[procauth] != 0)
        {
            $auth_row = freemed::get_link_rec($row[procauth],"authorizations");
            if (!$auth_row)
                $display_buffer .= "Failed to read procauth";
			$auth_num = $auth_row[authnum];
            if (!$auth_num)
            {
                $display_buffer .= "Authorization number Invalid";
                $auth_num = "AUTHXXXX";
            }
			$authdtbegin = $auth_row[authdtbegin];
			$authdtend = $auth_row[authdtend];
			$numprocs = count($procstack);
			
			for ($i=0;$i<$numprocs;$i++)
			{
				$procrow = $procstack[$i];
				$procdt = $procrow[procdt];	
			
				if (!date_in_range($procdt,$authdtbegin,$authdtend))
				{
					$display_buffer .= "Warning: Authorization $auth_num has expired for procedure $procdt<BR>";
				}
				if ($auth_row[authvisitsremain] == 0)
				{
					$display_buffer .= "Warning: No Remaining visits for Authorization $auth_num procedure $procdt<BR>";
				}	
			}
			$da0[authno] = $this->CleanNumber($auth_num);
        }
        else
        {
            $display_buffer .= "Warning - No Authorization for this procedure<BR>";
        }


		$da1[recid] = "DA1";
		$da1[seqno] = "01";
		$da1[patcntl] = $ca0[patcntl];
		$da1[payeraddr1] = $this->CleanChar($insco2->local_record[inscoaddr1]);
		$da1[payeraddr2] = $this->CleanChar($insco2->local_record[inscoaddr2]);
		$da1[payercity] = $this->CleanChar($insco2->local_record[inscocity]);
		$da1[payerstate] = $this->CleanChar($insco2->local_record[inscostate]);
		$da1[payerzip] = $this->CleanNumber($insco2->local_record[inscozip]);
	
		$count = count($procstack);

		$payerpaid = 0;
		$patpaid = 0;
		$total = 0;

		for ($i=0;$i<$count;$i++)
		{
			$prow = $procstatck[$i];
			$total += $row[procbalorig];

			$query = "SELECT * FROM payrec WHERE payrecproc='$row[id]' AND
                                            payrecsource='".PRIMARY."' AND
											payreclink = '$row[proccov1]' AND
                                            payreccat='".PAYMENT."'";
            $pay_result = $sql->query($query) or DIE("Query failed for primary payments");
            while ($pay_row = $sql->fetch_array($pay_result))
            {
                $payerpaid += $pay_row[payrecamt];
            }

        	$query = "SELECT * FROM payrec WHERE payrecproc='$row[id]' AND
                                            payrecsource='0' AND
                                            payreccat='".PAYMENT."'";
        	$pay_result = $sql->query($query) or DIE("Query failed for patient payments");
        	while ($pay_row = $sql->fetch_array($pay_result))
        	{
            	$patpaid += $pay_row[payrecamt];
        	}
		}	

		$da1[payeramtpd1] = $this->MakeDecimal($payerpaid,2);

		$baldue = $total - $patpaid; 
		$da1[baldue1] = $this->MakeDecimal($baldue,2);
	
		if ($payerpaid == 0)
			$da1[zeropayind1] = "Z";  
		else
			$da1[zeropayind1] = "N";
		


		$da2[recid] = "DA2";
		$da2[seqno] = "01";
		$da2[patcntl] = $ca0[patcntl];
		$da2[insrdaddr1] = $addr1;
		$da2[insrdcity] = $city;
		$da2[insrdstate] = $state;
		$da2[insrdzip] = $zip;

		$buffer = "";
   		$buffer  = render_fixedRecord ($whichform,$this->record_types["da0"]);
   		$buffer .= render_fixedRecord ($whichform,$this->record_types["da1"]);
   		$buffer .= render_fixedRecord ($whichform,$this->record_types["da2"]);

		// now gen the secondary ins info
		unset($GLOBALS[da0]);
		unset($GLOBALS[da1]);
		unset($GLOBALS[da2]);
		global $da0,$da1,$da2;
		
		$da0[recid] = "DA0";
		$da0[seqno] = "02";
		$da0[patcntl] = $ca0[patcntl];
		$da0[clmfileind] = "P"; // payment from secondary is requested
		$da0[clmsource] = "F";  // secondary in commercial
		//$da0[instypcd] = $insco->modifiers[0];
		$da0[payerid] = $this->CleanNumber($insco->local_record[inscoid]); // NAIC #
		$da0[payername] = $this->CleanChar($insco->insconame);
		$da0[patgrpno] = $this->CleanNumber($coverage->covpatgrpno);

		if ($coverage->local_record[covbenasgn] == 1)	
			$da0[assign] = "Y";
		else
			$da0[assign] = "N";

		$da0[patsigsrc] = "C";  // signed HCFA 1500 on file.

		$da0[patrel] = $this->GetRelationShip($coverage->covreldep,"NSF");
		
		$da0[patidno] = $this->CleanNumber($coverage->covpatinsno);

		if ($coverage->covdep == 0)  // patient is the insured
		{
			$da0[insrdlname] = $this->CleanChar($patient->ptlname);
			$da0[insrdfname] = $this->CleanChar($patient->ptfname);
			$da0[insrdmi] = $this->CleanChar($patient->ptmname);
			$da0[insrdsex] = $this->CleanChar($patient->ptsex);
			$da0[insrddob] = $this->CleanNumber($patient->ptdob);
			$addr1 = $this->CleanChar($patient->local_record[ptaddr1]);
			$city = $this->CleanChar($patient->local_record[ptcity]);
			$state = $this->CleanChar($patient->local_record[ptstate]);
			$zip = $this->CleanNumber($patient->local_record[ptzip]);
	
		}
		else
		{
			$guarantor = new Guarantor($coverage->covdep);
			if (!$guarantor)
			{
				$display_buffer .= "Error Insurer guarantor failed<BR>";
				return;
			}	
			$da0[insrdlname] = $this->CleanChar($guarantor->guarlname);
			$da0[insrdfname] = $this->CleanChar($guarantor->guarfname);
			$da0[insrdmi] = $this->CleanChar($guarantor->guarmname);
			$da0[insrdsex] = $this->CleanChar($guarantor->guarsex);
			$da0[insrddob] = $this->CleanNumber($guarantor->guardob);

			if ($guarantor->guarsame)  // guar addr same as patient?
			{
				$addr1 = $this->CleanChar($patient->local_record[ptaddr1]);
				$city = $this->CleanChar($patient->local_record[ptcity]);
				$state = $this->CleanChar($patient->local_record[ptstate]);
				$zip = $this->CleanNumber($patient->local_record[ptzip]);
			}
			else
			{
				$addr1 = $this->CleanChar($guarantor->guaraddr1);
				$city = $this->CleanChar($guarantor->guarcity);
				$state = $this->CleanChar($guarantor->guarstate);
				$zip = $this->CleanNumber($guarantor->guarzip);
			}

		}
		if (!empty($auth_num))
			$da0[authno] = $this->CleanNumber($auth_num);

		$da1[recid] = "DA1";
		$da1[seqno] = "02";
		$da1[patcntl] = $ca0[patcntl];
		$da1[payeraddr1] = $this->CleanChar($insco->local_record[inscoaddr1]);
		$da1[payeraddr2] = $this->CleanChar($insco->local_record[inscoaddr2]);
		$da1[payercity] = $this->CleanChar($insco->local_record[inscocity]);
		$da1[payerstate] = $this->CleanChar($insco->local_record[inscostate]);
		$da1[payerzip] = $this->CleanNumber($insco->local_record[inscozip]);

		$da2[recid] = "DA2";
		$da2[seqno] = "02";
		$da2[patcntl] = $ca0[patcntl];
		$da2[insrdaddr1] = $addr1;
		$da2[insrdcity] = $city;
		$da2[insrdstate] = $state;
		$da2[insrdzip] = $zip;

   		$buffer .= render_fixedRecord ($whichform,$this->record_types["da0"]);
		if ($da0[payerid] == "PAPER")
		{ 
        	$buffer  .= render_fixedRecord ($whichform,$this->record_types["da1"]);
		}
   		$buffer .= render_fixedRecord ($whichform,$this->record_types["da2"]);
		return $buffer;
		
	} // end do secondary bill

	function BillPrimary($procstack) {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		unset($GLOBALS[da0]);
		unset($GLOBALS[da1]);
		unset($GLOBALS[da2]);

		global $da1,$da0,$da2;

		$row = $procstack[0]; // all rows are the same
		$cov = $row[proccurcovid];
		$pat = $row[procpatient];

		$da0[recid] = "DA0";
		$da2[recid] = "DA2";

		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			$display_buffer .= "Error Insurer no coverage<BR>";
			return;
		}

		$insco = $coverage->covinsco;
		if (!$insco)
		{
			$display_buffer .= "Error Insurer no insurance<BR>";
			return;
		}

		$patient = new Patient($pat);
		if (!$patient)
		{
			$display_buffer .= "Error Insurer no patient<BR>";
			return;
		}
			
		$da0[seqno] = "01";
		$da0[patcntl] = $ca0[patcntl];
		
		$da0[clmfileind] = "P"; // primary coverage
		$da0[clmsource] = "F";  // commercial
	
		if ($this->insmod == "CH")
        {
			$da0[clmsource] = "H";
			// champus requires da0[instypcd] here
			$fac_row=0;
			$fac_row = freemed::get_link_rec($row[procpos], "facility");

        	if ($fac_row)
        	{
				$da0[ppoid] = $fac_row[psrein];
			}
			$covtpid = $coverage->local_record[covinstp];
			if ($covtpid > 0)	
			{
				$covtype = freemed::get_link_rec($covtpid,"covtypes");
				$da0[instypcd] = $covtype[covtpname];
			}
		}

		//$da0[instypcd] = $insco->modifiers[0];
		$da0[payerid] = $this->CleanNumber($insco->local_record[inscoid]); // NAIC #
		$da0[payername] = $this->CleanChar($insco->insconame);
		$da0[patgrpno] = $this->CleanNumber($coverage->covpatgrpno);

		if ($coverage->local_record[covbenasgn] == 1)	
			$da0[assign] = "Y";
		else
			$da0[assign] = "N";

		$da0[patsigsrc] = "C";  // signed HCFA 1500 on file.

		$da0[patrel] = $this->GetRelationShip($coverage->covreldep,"NSF");


		$da0[patidno] = $this->CleanNumber($coverage->covpatinsno);

		if ($coverage->covdep == 0)  // patient is the insured
		{
			$da0[insrdlname] = $this->CleanChar($patient->ptlname);
			$da0[insrdfname] = $this->CleanChar($patient->ptfname);
			$da0[insrdmi] = $this->CleanChar($patient->ptmname);
			$da0[insrdsex] = $this->CleanChar($patient->ptsex);
			$da0[insrddob] = $this->CleanNumber($patient->ptdob);
			$addr1 = $this->CleanChar($patient->local_record[ptaddr1]);
			$city = $this->CleanChar($patient->local_record[ptcity]);
			$state = $this->CleanChar($patient->local_record[ptstate]);
			$zip = $this->CleanNumber($patient->local_record[ptzip]);
	
		}
		else
		{
			$guarantor = new Guarantor($coverage->covdep);
			if (!$guarantor)
			{
				$display_buffer .= "Error Insurer guarantor failed<BR>";
				return;
			}	
			$da0[insrdlname] = $this->CleanChar($guarantor->guarlname);
			$da0[insrdfname] = $this->CleanChar($guarantor->guarfname);
			$da0[insrdmi] = $this->CleanChar($guarantor->guarmname);
			$da0[insrdsex] = $this->CleanChar($guarantor->guarsex);
			$da0[insrddob] = $this->CleanNumber($guarantor->guardob);

			if ($guarantor->guarsame)  // guar addr same as patient?
			{
				$addr1 = $this->CleanChar($patient->local_record[ptaddr1]);
				$city = $this->CleanChar($patient->local_record[ptcity]);
				$state = $this->CleanChar($patient->local_record[ptstate]);
				$zip = $this->CleanNumber($patient->local_record[ptzip]);
			}
			else
			{
				$addr1 = $this->CleanChar($guarantor->guaraddr1);
				$city = $this->CleanChar($guarantor->guarcity);
				$state = $this->CleanChar($guarantor->guarstate);
				$zip = $this->CleanNumber($guarantor->guarzip);
			}

		}
		if ($row[procauth] != 0)
        {
            $auth_row = freemed::get_link_rec($row[procauth],"authorizations");
            if (!$auth_row)
                $display_buffer .= "Failed to read procauth";
			$auth_num = $auth_row[authnum];
            if (!$auth_num)
            {
                $display_buffer .= "Authorization number Invalid";
                $auth_num = "AUTHXXXX";
            }
			$authdtbegin = $auth_row[authdtbegin];
			$authdtend = $auth_row[authdtend];
			$numprocs = count($procstack);
			
			for ($i=0;$i<$numprocs;$i++)
			{
				$procrow = $procstack[$i];
				$procdt = $procrow[procdt];	
			
				if (!date_in_range($procdt,$authdtbegin,$authdtend))
				{
					$display_buffer .= "Warning: Authorization $auth_num has expired for procedure $procdt<BR>";
				}
				if ($auth_row[authvisitsremain] == 0)
				{
					$display_buffer .= "Warning: No Remaining visits for Authorization $auth_num procedure $procdt<BR>";
				}	

			}
			$da0[authno] = $this->CleanNumber($auth_num);
        }
        else
        {
            $display_buffer .= "Warning - No Authorization for this procedure<BR>";
        }

		$da1[recid] = "DA1";
		$da1[seqno] = "01";
		$da1[patcntl] = $ca0[patcntl];
		$da1[payeraddr1] = $this->CleanChar($insco->local_record[inscoaddr1]);
		$da1[payeraddr2] = $this->CleanChar($insco->local_record[inscoaddr2]);
		$da1[payercity] = $this->CleanChar($insco->local_record[inscocity]);
		$da1[payerstate] = $this->CleanChar($insco->local_record[inscostate]);
		$da1[payerzip] = $this->CleanNumber($insco->local_record[inscozip]);

		$da2[recid] = "DA2";
		$da2[seqno] = "01";
		$da2[patcntl] = $ca0[patcntl];
		//$display_buffer .= "da2 addr city state zip $addr1 $city $state $zip<BR>";
		$da2[insrdaddr1] = $addr1;
		$da2[insrdcity] = $city;
		$da2[insrdstate] = $state;
		$da2[insrdzip] = $zip;

		$buffer = "";
   		$buffer  = render_fixedRecord ($whichform,$this->record_types["da0"]);
    
		if ($da0[payerid] == "PAPER")
		{ 
        	$buffer  .= render_fixedRecord ($whichform,$this->record_types["da1"]);
		}

   		$buffer .= render_fixedRecord ($whichform,$this->record_types["da2"]);
		return $buffer;

		
	} // end bill for primary

	function ClaimHeader($procstack) {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		
		unset($GLOBALS[ca0]);
		unset($GLOBALS[cb0]);
		global $ca0, $cb0;

		$row = $procstack[0]; // all rows are the same
		$cov = $row[proccurcovid];
		$pat = $row[procpatient];
		$doc = $row[procphysician];

		$ca0[recid] = "CA0";
		$cb0[recid] = "CB0";  // only required for champus

		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			$display_buffer .= "Error in claimheader no coverage<BR>";
			return;
		}

		$physician = new Physician($doc);
		if (!$physician)
		{
			$display_buffer .= "Error in claimheader no physician<BR>";
			return;
		}

		$patient = new Patient($pat);
		if (!$patient)
		{
			$display_buffer .= "Error in claimheader no patient<BR>";
			return;
		}

		if (empty($patient->local_record[ptid]))
			$ca0[patcntl] = $patient->local_record[id];
		else
			$ca0[patcntl] = $this->CleanChar($patient->local_record[ptid]);

		$ca0[patlname] = $this->CleanChar($patient->local_record[ptlname]);
		$ca0[patfname] = $this->CleanChar($patient->local_record[ptfname]);
		$ca0[patdob] = $this->CleanNumber($patient->local_record[ptdob]);
		$ca0[patsex] = $this->CleanChar($patient->local_record[ptsex]);
		$ca0[pataddr1] = $this->CleanChar($patient->local_record[ptaddr1]);
		$ca0[pataddr2] = $this->CleanChar($patient->local_record[ptaddr2]);
		$ca0[patcity] = $this->CleanChar($patient->local_record[ptcity]);
		$ca0[patstate] = $this->CleanChar($patient->local_record[ptstate]);
		$ca0[patzip] = $this->CleanNumber($patient->local_record[ptzip]);
		$ca0[patphone] = $this->CleanNumber($patient->local_record[ptphone]);

     	// marital status
        if ($patient->ptmarital == "single") 
     		$ca0[patmarital] = "S";
        if ($patient->ptmarital == "divorced") 
     		$ca0[patmarital] = "D";
        if ($patient->ptmarital == "seperated") 
     		$ca0[patmarital] = "S";
        if ($patient->ptmarital == "married") 
     		$ca0[patmarital] = "M";
        if ($patient->ptmarital == "widowed") 
     		$ca0[patmarital] = "W";

		$ca0[patstudent] = "N";  // default not student
		if ($patient->local_record[ptstatus] == 0)
		{
			$ca0[patstudent] = "N";
		}
		else
		{
			// look up the status record.
			$status = freemed::get_link_field($patient->local_record[ptstatus],"ptstatus","ptstatus");
			if (!$status)
				$display_buffer .= "Error failed to get ptstatus<BR>";
			if ($status == "HC")
				$ca0[patstudent] = "N";
			else
			{
				$datediff = date_diff($patient->local_record[ptdob]);
				$yrdiff = $datediff[0];
				if ( ($yrdiff > 18) AND ($status == "FT") )
					$ca0[patstudent] = "F";
				if ( ($yrdiff > 18) AND ($status == "PT") )
					$ca0[patstudent] = "P";
				//$display_buffer .= "year diff $yrdiff<BR>";
			}

		}
		if ($patient->local_record[ptempl] == "y")
			$ca0[patempl] = "1";
		if ($patient->local_record[ptempl] == "p")
			$ca0[patempl] = "2";
		if ($patient->local_record[ptempl] == "n")
			$ca0[patempl] = "3";
		if ($patient->local_record[ptempl] == "s")
			$ca0[patempl] = "4";
		if ($patient->local_record[ptempl] == "r")
			$ca0[patempl] = "5";
		if ($patient->local_record[ptempl] == "m")
			$ca0[patempl] = "5";
		if ($patient->local_record[ptempl] == "u")
			$ca0[patempl] = "9";

		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			$display_buffer .= "Error. patient coverage invalid<BR>";
			return;
		}
		$covtype = $coverage->local_record[covtype];
		$othrins = 3;  // no other coverage

		if ( ($covtype == PRIMARY) AND ($row[proccov.(SECONDARY)] != 0) )
				$othrins = "2"; // has other coverage not on this bill
		
		if ( ($covtype == SECONDARY) AND ($row[proccov.(PRIMARY)] != 0) )
				$othrins = "1"; // has other coverage in this bill

		// NSF does not handle tertiary!

		$ca0[othrins] = $othrins;

		// asseume commercial
		$ca0[clmeditind] = "F"; // comercial filing

		if ($this->insmod == "CH")
		{
			$ca0[clmeditind] = "H"; // champus filing
			$ca0[origin] = $this->CleanNumber($physician->local_record[phyzipa]);
			$ca0[billprno] = $ba0[taxid];
		}

		$ca0[clmtype] = " ";

		// need origin codes champus provider id if payer is REG06

		// cb0 is required for champus (REG06) if the patient is
        // under 18 at the time of service. difference between patdob and the lowest date of service
		// is < 18

		$buffer = "";		
   		$buffer  = render_fixedRecord ($whichform,$this->record_types["ca0"]);

		if ($this->insmod == "CH")
		{
			// cut cb0 record for champus only. 
			// required if patient is under 18 and the time of the oldest date of service
			$svcdate = $row[procdt]; // since ordered by date this should be oldest
			$datediff = date_diff($patient->local_record[ptdob],$svcdate);
			$diffyr = $datediff[0];
			//$display_buffer .= "diffyear $diffyr<BR>";
			if ($diffyr < 18)
			{
				$cb0[patcntl] = $ca0[patcntl];
				// we assume the guarantor is the responsible party
				if ($coverage->covdep != 0)
				{
					$guar = new Guarantor($coverage->covdep);
					if (!$guar)
						$display_buffer .= "Error getting guarantor in CB0 record<BR>";
					$cb0[respfname] = $this->CleanChar($guar->guarfname);
					$cb0[resplname] = $this->CleanChar($guar->guarlname);
   					$buffer  .= render_fixedRecord ($whichform,$this->record_types["cb0"]);
					
				}
				else
				{
					$display_buffer .= "Error in Champus CB0 record for Procedure $row[procdt]<BR>";
					$display_buffer .= "Under aged patient does not have Guarantor<BR>";
				}
								

			}

		}

		return $buffer;

	} // end patient

	function FileHeader($userid,$password) {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		unset($GLOBALS[aa0]);
		global $aa0;
		$aa0[recid] = "AA0";
		$aa0[submtrid] = $this->CleanChar($userid);
		$aa0[subtype] = "U";

		$this->subno++;
		// this should be saved in the ch table. gotton when started.
        // incremented as used then saved when done.
		$aa0[subno] = $this->subno;  // once in 7 months!!!

		$aa0[createdt] = $this->CleanNumber($cur_date);
		$aa0[recvrid] = "MIXED";  // file contains claims for multiple payers
        if ($this->insmod == "CH")
			$aa0[recvrtype] = "H"; // champus
		else
			$aa0[recvrtype] = "F"; // commercial
		$aa0[nsfverno] = "00301";
		$aa0[testprod] = "PROD";
		$aa0[password] = $this->CleanChar($password);
		$aa0[vendorid] = "FREMED";
	
		$ruler = "";
		//$cntr = "";
		//for ($i=0;$i<32;$i++)
		//{
		//	for ($n=0;$n<10;$n++)
		//	{	
		//		$z = $n+1;
		//		if ($z == 10)
		//			$z=0;
		//		$cntr .= $z;	
		//	}
		//	$ruler .= $cntr;
		//	$cntr = "";
		//}	
		
		$buffer = "";	
		if (!empty($ruler))
		{
			$buffer = $ruler."\n";
   			$buffer .= render_fixedRecord ($whichform,$this->record_types["aa0"]);
		}
		else
		{
   			$buffer = render_fixedRecord ($whichform,$this->record_types["aa0"]);
		}
		return $buffer;
		
	}

	function ProviderHeader($procstack) {
		
		global $display_buffer, $SESSION;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		unset($GLOBALS[aa0]);
		unset($GLOBALS[ba0]);
		unset($GLOBALS[ba1]);
		global $ba0, $ba1;

		$row = $procstack[0]; // all rows are the same
		$doc = $row[procphysician];
		$cov = $row[proccurcovid];

		$ba0[recid] = "BA0";
		$ba1[recid] = "BA1";

		$physician = new Physician($doc);
		if (!$physician)
		{
			$display_buffer .= "Error no physician<BR>";
			return;
		}
		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			$display_buffer .= "Error no coverage<BR>";
			return;
		}
		$insco = $coverage->covinsco;
		if (!$insco)
		{
			$display_buffer .= "Error no insco<BR>";
			return;
		}
		
		
	
		$ba0[batchtype] = "100";  
		
		$this->batchno++;

		$ba0[batchnum] = $this->batchno; // needs incrementer

		$this->batchid++;
		// this should be saved in the ch table. gotton when started.
        // incremented as used then saved when done.
		$ba0[batchid] = $this->batchid;  // only used once for 30 days!!!

		if ($SESSION["default_facility"] != 0)
		{
			$fac = 0;
			$fac = freemed::get_link_rec($SESSION["default_facility"] ,"facility");
			if (!$fac)
				$display_buffer .= "Error getting facility<BR>";
			$ba0[posname] = $this->CleanChar($fac[psrname]);
			$ba0[taxid] = $this->CleanNumber($fac[psrein]);
			$ba0[idtype] = "E";
			
		}
		else
		{
			$ba0[posname] = $this->CleanChar($physician->local_record[phypracname]);
			$ba0[taxid] = $this->CleanNumber($physician->local_record[physsn]);
			$ba0[idtype] = "S";
		}

		// other id's are dependant on the ins NAIC no

		$ba0[prlname] = $this->CleanChar($physician->local_record[phylname]);
		$fname = $physician->local_record[phyfname];

		$insid = $this->CleanChar($insco->local_record[inscoid]);

		if ($insid=="PAPER") // paper claim require credentials
			$fname = $fname." ".$physician->local_record[phytitle];

		$ba0[prfname] = $this->CleanChar($fname);

		$prspec  = $physician->local_record[physpe1];
        $specrow = freemed::get_link_rec($prspec,"specialties");
        $ba0[prspec]  = $this->CleanChar($specrow[specname]);


		$provider_id = "0";
        $grp = $insco->local_record[inscogroup];
        if (!$grp)
        {
            $name = $insco->local_record[insconame];
            $display_buffer .= "Failed getting inscogroup for $name<BR>";
        }

        $providerids = explode(":",$physician->local_record[phyidmap]);
        $provider_id = $providerids[$grp];
	

		$naic = $insco->inscoid;
		$naic = strtoupper($naic);

		$upin = $physician->local_record[phyupin];
		$ba0[upin] = $this->CleanNumber($upin);
		$ba0[ciid] = $this->CleanNumber($provider_id); // commercial provider id

		// these carriers want the upin number
		//if (fm_value_in_array($ins_upin,$naic))
		//{
		//	$upin = $physician->local_record[phyupin];
		//	$ba0[upin] = $this->CleanNumber($upin);
		//}
		// these carriers want the medicaid number
		if (fm_value_in_array($ins_medicaid,$naic))
		{
			$ba0[mcid] = $this->CleanNumber(provider_id);
		}
		// these carriers want the commercial provider number
		//if (fm_value_in_array($ins_commercial,$naic))
		//{
		//	$ba0[ciid] = $this->CleanNumber($provider_id);
		//}
	

		$ba1[batchtype] = $ba0[batchtype];
		$ba1[batchid] = $ba0[batchid];
		$ba1[batchnum] = $ba0[batchnum];
		$ba1[praddr1] = $this->CleanChar($physician->local_record[phyaddr1a]);
		$ba1[praddr2] = $this->CleanChar($physician->local_record[phyaddr2a]);
		$ba1[prcity] = $this->CleanChar($physician->local_record[phycitya]);
		$ba1[prstate] = $this->CleanChar($physician->local_record[phystatea]);
		$ba1[przip] = $this->CleanNumber($physician->local_record[phyzipa]);
		$ba1[prphone] = $this->CleanNumber($physician->local_record[phyphonea]);
		$ba1[prpayaddr1] = $this->CleanChar($physician->local_record[phyaddr1a]);
		$ba1[prpayaddr2] = $this->CleanChar($physician->local_record[phyaddr2a]);
		$ba1[prpaycity] = $this->CleanChar($physician->local_record[phycitya]);
		$ba1[prpaystate] = $this->CleanChar($physician->local_record[phystatea]);
		$ba1[prpayzip] = $this->CleanNumber($physician->local_record[phyzipa]);
		$ba1[prpayphone] = $this->CleanNumber($physician->local_record[phyphonea]);

		$buffer = "";
   		$buffer = render_fixedRecord ($whichform,$this->record_types["ba0"]);
   		$buffer .= render_fixedRecord ($whichform,$this->record_types["ba1"]);
		return $buffer;
		
	}  // end provider

	function ClaimData($procstack) {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		unset($GLOBALS[ea0]);
		unset($GLOBALS[ea1]);
		global $ea0,$ea1;
		
		$row = $procstack[0];

		$cov = $row[proccurcovid];

		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			$display_buffer .= "Error no coverage ClaimData<BR>";
			return;
		}

		$ea0[recid] = "EA0";
		$ea1[recid] = "XXX";

		$ea0[patcntl] = $ca0[patcntl];

		$ea0[relemp] = "N";
		$ea0[accident] = "N";
		$ea0[symptomind] = "0";

		if ($coverage->local_record[covrelinfo] == 0)
			$ea0[relinfoind] = "N";
		if ($coverage->local_record[covrelinfo] == 1)
			$ea0[relinfoind] = "Y";
		if ($coverage->local_record[covrelinfo] == 2)
			$ea0[relinfoind] = "M";

		$ea0[relinfodt] = $this->CleanNumber($coverage->local_record[covrelinfodt]);

		if ($coverage->local_record[covprovasgn] == 0)
			$ea0[provassign] = "N";  // provider accepts assigmnent
		if ($coverage->local_record[covprovasgn] == 1)
			$ea0[provassign] = "A";  // provider accepts assigmnent

		// NOTE champus needs this but no sure where to get it
		//$ea0[speclpgm]

		//$display_buffer .= "referer $row[procrefdoc]<BR>";
		if ($row[procrefdoc] != 0)
		{
			$refdoc = new Physician($row[procrefdoc]);
			if (!$refdoc)
				$display_buffer .= "Error getting referring physician<BR>";
			$ea0[refprovupin] = $this->CleanChar($refdoc->local_record[phyupin]);	
			$ea0[reflname] = $this->CleanChar($refdoc->local_record[phylname]);	
			$ea0[reffname] = $this->CleanChar($refdoc->local_record[phyfname]);	
		}

		if ($row[proceoc] != 0)
        {
            $eoc_row = freemed::get_link_rec($row[proceoc], "eoc");
            if (!$eoc_row)
                $display_buffer .= "Failed reading eoc record<BR>";

            if ($eoc_row[eocrelauto] == "yes")
            {
				$ea0[accident] = "A";
				$ea0[symptomind] = "0";
                $accident_date = $eoc_row[eocstartdate];
            }
            if ($eoc_row[eocrelemp] == "yes")
            {
				$ea0[relemp] = "Y";
                $accident_date = $eoc_row[eocstartdate];
            }
            if ($eoc_row[eocrelother] == "yes")  // other Accident!
            {
				$ea0[accident] = "O";
				$ea0[symptomind] = "0";
                $accident_date = $eoc_row[eocstartdate];
            }
            if ($eoc_row[eocrelpreg] == "yes")  // preg?
            {
				$ea0[symptomind] = "2";
                $accident_date = $eoc_row[eocpreglastper];
            }
			if ($eoc_row[eochospital] > 0)
			{
				$ea0[admitdt] = $this->CleanNumber($eoc_row[eochosadmdt]);
				$ea0[dischargdt] = $this->CleanNumber($eoc_row[eochosdischrgdt]);
			}

        }
        else
        {
            $display_buffer .= "Warning - No EOC for this procedure $row[procdt]<BR>";
        }

		if ($ea0[accident] == "A" OR 
			$ea0[accident] == "O" OR 
			$ea0[symtomind] == "1" OR
			$ea0[symtomind] == "2")
		{
			$ea0[accsympdt] = $this->CleanNumber($accident_date);
		}
		else
			$ea0[accsympdt] = " ";

		$count = count($procstack);
        if ($count == 0)
        {
            $display_buffer .= "Error in GenClaimSegment Stack count 0<BR>";
            return;
        }

        $diagset = new diagnosisSet();
        for ($i=0;$i<$count;$i++)
        {
            $prow = $procstack[$i];

            // this should never overflow if the control break is working.
            $diagset->testAddSet($prow[procdiag1],
                             $prow[procdiag2],
                             $prow[procdiag3],
                             $prow[procdiag4]);

        }

		$diagstack = $diagset->getStack();
        $diagcnt = count($diagstack);
		//$display_buffer .= "stack count $diagcnt<BR>";

        if ($diagcnt == 0)
        {
            $display_buffer .= "Procedures do not have Diagnosis codes<BR>";
            return;
        }


        for ($i=0;$i<$diagcnt;$i++)
        {
			$varno = $i+1;
			$var = "diag".$varno;
            $ea0[$var] = $this->CleanNumber($diagstack[$varno]);
            //$this->edi_buffer .= $icd9code;


        }

		// see if procpos is not home or office
		$pos = 0;
		$fac_row=0;
		$fac_row = freemed::get_link_rec($row[procpos], "facility");

        if ($fac_row)
        {
            // use code from facility
            if ($fac_row[psrpos] == 0)
            {
                $display_buffer .= "Facility does not have a pos code<BR>";
            }
            $cur_pos = freemed::get_link_rec($fac_row[psrpos], "pos");
            if (!$cur_pos)
                $display_buffer .= "Failed reading pos table";
            $pos = $cur_pos[posname];
        }
		if ($pos==0)
			$pos="11"; // office default

		// 11 = office 12 = home
        if ($pos > 12)
        {
			$ea0[faclabname] = $this->CleanChar($fac_row[psrname]);
			$ea1[recid] = "EA1";
			$ea1[patcntl] = $ca0[patcntl];
			// for these facilites the ien doubles as the facility id.
			$ea1[faclabid] = $this->CleanChar($fac_row[psrein]);
			$ea1[faclabaddr1] = $this->CleanChar($fac_row[psraddr1]);
			$ea1[faclabaddr2] = $this->CleanChar($fac_row[psraddr2]);
			$ea1[faclabcity] = $this->CleanChar($fac_row[psrcity]);
			$ea1[faclabstate] = $this->CleanChar($fac_row[psrstate]);
        }
		
		$buffer = "";
   		$buffer = render_fixedRecord ($whichform,$this->record_types["ea0"]);
		if ($ea1[recid] != "XXX")
   			$buffer .= render_fixedRecord ($whichform,$this->record_types["ea1"]);
		return $buffer;

	}  // end claimdata

	function ServiceDetail($procstack) {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		unset ($GLOBALS[fa0]);
		global $fa0;

		$row = $procstack[0];
		$doc = $row[procphysician];
		$cov = $row[proccurcovid];

		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			$display_buffer .= "Error ServiceDetail no coverage<BR>";
			return;
		}

		$insco = $coverage->covinsco;
		if (!$insco)
		{
			$display_buffer .= "Error ServiceDetail no insurance<BR>";
			return;
		}

		$physician = new Physician($doc);
		if (!$physician)
		{
			$display_buffer .= "Error in ServiceDetail no physician<BR>";
		}

		$diagset = new diagnosisSet();

		$pos = 0;
		$fac_row=0;
		$fac_row = freemed::get_link_rec($row[procpos], "facility");
        if ($fac_row)
        {
            // use code from facility
            if ($fac_row[psrpos] == 0)
            {
                $display_buffer .= "Facility does not have a pos code<BR>";
            }
            $cur_pos = freemed::get_link_rec($fac_row[psrpos], "pos");
            if (!$cur_pos)
                $display_buffer .= "Failed reading pos table";
            $pos = $cur_pos[posname];

        }
        if ($pos == 0)
        {
            // plug with Office code
            $pos="11";
            $display_buffer .= "Warning: Plugged pos with Office Code 11<BR>";
        }
		
		$count = count($procstack);

		if ($count == 0)
		{
			$display_buffer .= "Error no procedures in Service<BR?";
			return;
		}
		$buffer = "";

		for ($i=0;$i<$count;$i++)
		{
			$row = $procstack[$i];
			$seq = $i+1;
			if ($seq < 10)
				$seq = "0".$seq;

			$fa0[recid] = "FA0";
			$fa0[seqno] = $seq;
			$fa0[patcntl] = $ca0[patcntl];
			$fa0[startdt] = $this->CleanNumber($row[procdt]);
			$fa0[enddt] = $this->CleanNumber($row[procdt]);
			$fa0[pos]   = $pos;
		
			if ($row[proccptmod] != 0)
			{
				$itemcptmod  = freemed::get_link_field ($row[proccptmod], "cptmod", "cptmod");
				if (!$itemcptmod)
                	$display_buffer .= "Failed reading cptmod table<BR>";
				$fa0[cptmod1] = $itemcptmod;
			}

			$cur_cpt = freemed::get_link_rec ($row[proccpt], "cpt");
            if (!$cur_cpt)
                $display_buffer .= "Failed reading cpt table<BR>";
            $cur_insco = $insco->local_record[id];
			//$display_buffer .= "insco $cur_insco<BR>";
            $tos_stack = fm_split_into_array ($cur_cpt[cpttos]);
            $tosid = ( ($tos_stack[$cur_insco] < 1) ?
                      $cur_cpt[cptdeftos] :
                      $tos_stack[$cur_insco] );
			// tos prefix used by champus
			//$display_buffer .= "cpt prefix $cur_cpt[cpttosprfx]";
            $tosprfx_stack = fm_split_into_array ($cur_cpt[cpttosprfx]);
            $tosprfxid = ( ($tosprfx_stack[$cur_insco] < 1) ?
                      "0" :
                      $tosprfx_stack[$cur_insco] );

			if ($tosid == 0)
            {
                $display_buffer .= "No default type of service for this proc $row[procdt]<BR>";
                $tos = "XX";
            }
            else
            {
                $cur_tos = freemed::get_link_rec($tosid, "tos");
                if (!$cur_tos)
                    $display_buffer .= "Failed reading tos table<BR>";
                $tos = $cur_tos[tosname];
            }

			if ($this->insmod == "CH") // champus
			{
				if ($tosprfxid == 0)
				{
					$display_buffer .= "No Champus type of service prefix for this proc $row[procdt]<BR>";
					$tos = "XX";
				}
				else
				{
					//$display_buffer .= "prfxid $tosprfxid<BR>";
					$cur_tos = freemed::get_link_rec($tosprfxid, "tos");
					if (!$cur_tos)
						$display_buffer .= "Failed reading prefix tos table<BR>";
					$tosprfx = $cur_tos[tosname];
				}
				// make champus tos
				$tos = $tosprfx.$tos;			
				if (strlen($tos) > 3)
				{
					$display_buffer .= "Invalid Champus type of service proc $row[procdt]<BR>";
					$tos = "XX";
				}


			}
		
			if ( (strlen($tos) < 2)	AND ($tos > 0) AND ($tos < 10) )
				$tos = "0".$tos;

			$fa0[tos] = $tos;
		
			$cur_cpt = freemed::get_link_rec ($row[proccpt], "cpt");
            if (!$cur_cpt)
                $display_buffer .= "Failed reading cpt table<BR>";

            $diagset->testAddSet($row[procdiag1],
                             $row[procdiag2],
                             $row[procdiag3],
                             $row[procdiag4]);

            $diag_xref = $diagset->xrefList($row[procdiag1],
                             $row[procdiag2],
                             $row[procdiag3],
                             $row[procdiag4]);

			$fa0[cpt] = $cur_cpt[cptcode];

			if ($this->insmod == "CH")
			{	
				// champus also wants the referring provider upin here
				// if there is one.
				if ($row[procrefdoc] != 0)
				{
					$fa0[refprid] = $ea0[refprovupin];
				}
				$rendprid = $physician->local_record[physsn];
				$rendprid = $this->CleanNumber($rendprid);
				$fa0[rendprid] = $rendprid;

			}
			$data = $this->MakeDecimal($row[procbalorig],2);
			$fa0[charges] = $data;

            $diag_xref = explode(",",$diag_xref);
			for ($x=0;$x<count($diag_xref);$x++)
			{
				//$display_buffer .= "xref $diag_xref[$x]<BR>";
				$xoff = $x+1;
				$var = "diag".$xoff;
				$fa0[$var] = $diag_xref[$x];
			}
			$data = $this->MakeDecimal($row[procunits],1);
			$fa0[units] = $data;

   			$buffer .= render_fixedRecord ($whichform,$this->record_types["fa0"]);
			unset ($GLOBALS[fa0]);
			global $fa0;
			if ($this->insmod == "CH")
			{
				// champus requires the fb1 record 
				global $fb1;
				$fb1[recid] = "FB1";
				$fb1[seqno] = $seq;
				$fb1[patcntl] = $ca0[patcntl];
				$fb1[renprvlname] = $ba0[prlname];
				$fb1[renprvfname] = $ba0[prfname];
				$rendprid = $physician->local_record[physsn];
				$rendprid = $this->CleanNumber($rendprid);
				$fb1[renprvupin] = $rendprid;
   				$buffer .= render_fixedRecord ($whichform,$this->record_types["fb1"]);
				unset ($GLOBALS[fb1]);
				global $fb1;
	
			}

		}
		return $buffer;

	} // end servicedetail

	function ClaimTrailer($procstack,$buffer) {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		unset($GLOBALS[xa0]);
		global $xa0;

		$buff = explode("\n",$buffer);
		$buffcount = count($buff);

		$cxx = $dxx = $exx = $fxx = $gxx = $hxx = "00";
		for ($i=0;$i<$buffcount;$i++)
		{
			if (substr($buff[$i],0,1) == "C")
				$cxx++;
			if (substr($buff[$i],0,1) == "D")
				$dxx++;
			if (substr($buff[$i],0,1) == "E")
				$exx++;
			if (substr($buff[$i],0,1) == "F")
				$fxx++;
			if (substr($buff[$i],0,1) == "G")
				$gxx++;
			if (substr($buff[$i],0,1) == "H")
				$hxx++;
		}
		$xa0[recid] = "XA0";
		$xa0[patcntl] = $ca0[patcntl];
		$xa0[cxxcount] = $cxx;
		$xa0[dxxcount] = $dxx;
		$xa0[exxcount] = $exx;
		$xa0[fxxcount] = $fxx;
		$xa0[gxxcount] = $gxx;
		$xa0[hxxcount] = $hxx;

		$count = count($procstack);

		if ($count == 0)
		{
			$display_buffer .= "Error no procedures in Service<BR?";
			return;
		}

		$total = 0;
		// see if patient already paid anything
        $total_paid_bypatient = 00.00;

		for ($i=0;$i<$count;$i++)
		{
			$row = $procstack[$i];
			$total += $row[procbalorig];
        	$query = "SELECT * FROM payrec WHERE payrecproc='$row[id]' AND
                                            payrecsource='0' AND
                                            payreccat='".PAYMENT."'";
        	$pay_result = $sql->query($query) or DIE("Query failed for patient payments");
        	while ($pay_row = $sql->fetch_array($pay_result))
        	{
            	$total_paid_bypatient += $pay_row[payrecamt];
        	}
			
		}
		$data = $this->MakeDecimal($total,2);
		$xa0[totalcharge] = $data;
		$data = $this->MakeDecimal($total_paid_bypatient,2);
		$xa0[pattotpd] = $data;
		
   		$buffer = render_fixedRecord ($whichform,$this->record_types["xa0"]);
		return $buffer;

	} // end claimtrailer

	function ProviderTrailer($procstack, $buffer) {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		reset ($this->rendorform_variables);
		while (list($k,$v)=each($this->rendorform_variables)) global $$v;

		unset($GLOBALS[ya0]);
		global $ya0;
		unset($GLOBALS[za0]);
		global $za0;

		$ya0[recid] = "YA0";
		$ya0[emcprvid] = $ba0[emcprvid];
		$ya0[batchtype] = $ba0[batchtype];
		$ya0[batchno] = $ba0[batchnum];
		$ya0[batchid] = $ba0[batchid];
		$ya0[prvtaxid] = $ba0[taxid];


		$buff = explode("\n",$buffer);
		$buffcount = count($buff);

		$tot = $cxx = $fxx = 0;
		for ($i=0;$i<$buffcount;$i++)
		{
			//if (substr($buff[$i],0,1) == "")
			//	continue;
			//if (substr($buff[$i],0,1) == " ")
			//	continue;
			$tot++;
			if (substr($buff[$i],0,3) == "CA0")
				$cxx++;
			if (substr($buff[$i],0,3) == "FA0")
				$fxx++;
		}
		$tot++;  // account for this ya0 record
		//$display_buffer .= "count $tot<BR>";
		$ya0[batchreccnt] = $tot;
		$ya0[svclinecnt] = $fxx;
		$ya0[batchclmcnt] = $cxx;
		$ya0[batchtotchg] = $xa0[totalcharge]; // only one batch per control break

		$this->batchcnt++;
		$this->batchreccnt += $tot;
		$this->svclinecnt += $fxx;
		$this->batchclmcnt += $cxx;
		$this->batchtotchg += $xa0[totalcharge];


   		$buffer = render_fixedRecord ($whichform,$this->record_types["ya0"]);
		return $buffer;

	} // end provider trailer
	
	function FileTrailer($buffer) {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		reset ($this->rendorform_variables);
		while (list($k,$v)=each($this->rendorform_variables)) global $$v;
		unset($GLOBALS[za0]);
		global $za0;

		$za0[recid] = "ZA0";
		$za0[subid] = $aa0[submtrid];
		$za0[recvrid] = $aa0[recvrid];

		//$recs = explode("\n",$buffer);
		//$count = count($recs);
		//if ($count == 0)
		//{
		//	$display_buffer .= "Error getting buffer<BR>";
		//}
		
		$za0[filesvclinecnt] = $this->svclinecnt;
		$za0[filereccnt] = $this->batchreccnt;
		$za0[fileclmcnt] = $this->batchclmcnt;
		$za0[batchcnt] = $this->batchcnt;
		$za0[filetotchg] = $this->batchtotchg;

   		$zabuffer = render_fixedRecord ($whichform,$this->record_types["za0"]);
		$new_buffer = $buffer.$zabuffer;
		return $new_buffer;


	} // end file trailer

	// makestack callback function
	function ProcessClaims($procstack) {

		$buffer  = $this->ProviderHeader($procstack); // batch header	
		$buffer .= $this->ClaimHeader($procstack); // claim headers
		$buffer .= $this->Insurer($procstack);    // insurance records 
		$buffer .= $this->ClaimData($procstack);    // claim data EA0-EA1
		$buffer .= $this->ServiceDetail($procstack);    // service FA0-FB0-FB1
		return $buffer;
	}


	function GenerateFixedForms($parmpatient, $parmcovid) {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		while (list($k,$v)=each($this->renderform_variables)) global $$v;

	    // zero the buffer 
	    $buffer = "";
     	// get current patient information
     	$this_patient = new Patient ($parmpatient);
        if (!$this_patient)
			trigger_error("Failed retrieving patient", E_USER_ERROR);
			
     	$display_buffer .= "
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
        $this->formno = freemed::get_link_rec ($whichform, "fixedform");


		// grab all the procedures to bill for this patient
		$result = $this->GetProcstoBill($this_coverage->id,$this_coverage->covtype,$this_coverage->covpatient);

		if (!$result or ($result==0))
			trigger_error("Should have bills for $this_patient->local_record[ptid]");

		// procedure callback function will handle all the data
		$this->MakeStack($result,$this->formno[ffloopnum]);
		$this->pat_processed++;
		$this->patient_forms[$this->pat_processed] = $parmpatient;


   } // end generateFixed


	function ProcCallBack($stack) {
		global $display_buffer;
		$form_buffer = $this->ProcessClaims($stack); // batch trailer
		$form_buffer .= $this->ClaimTrailer($stack,$form_buffer);
		$form_buffer .= $this->ProviderTrailer($stack,$form_buffer);
		$this->form_buffer .= $form_buffer;
	}

	function ChampusTOS($cpt,$pos, $cptmod) {
		global $display_buffer;
		$display_buffer .= "pos $pos cpt $cpt mod $cptmod<BR>";
		if ($pos == "24") // Ambulatory Surgery
			$pos1="A";
		if ($pos == "21") // Inpatient
			$pos1="I";
		if ($pos == "22") // Outpatient
			$pos1="O";

		if ( ($ctp >= "59000") AND ($cpt <= "59899") )
		{
			$pos1="M";
			$pos2="9";
			return $pos1.$pos2;
		}
		if ( ($ctp >= "99341") AND ($cpt <= "99353") )
		{
			$pos2="9";
			return $pos1.$pos2;
		}
		if ( ($ctp >= "97010") AND ($cpt <= "99353") )
		{
			$pos2="9";
			return $pos1.$pos2;
		}
		if ( ($ctp >= "97010") AND ($cpt <= "97799") )
		{
			$pos2="9";
			return $pos1.$pos2;
		}
		if ( ($ctp >= "90801") AND ($cpt <= "90899") )
		{
			$pos2="9";
			return $pos1.$pos2;
		}
		if ($ctpmod == "29")
		{
			$pos2="9";
			return $pos1.$pos2;
		}
		if ( ($ctp >= "99221") AND ($cpt <= "99238") )
		{
			$pos2="9";
			return $pos1.$pos2;
		}
		if ($ctpmod == "80")
		{
			$pos2="8";
			return $pos1.$pos2;
		}
		if ($ctpmod == "30")
		{
			$pos2="7";
			return $pos1.$pos2;
		}
		if ( ($ctp >= "77261") AND ($cpt <= "77799") )
		{
			$pos2="6";
			return $pos1.$pos2;
		}
		if ( ($ctp >= "79000") AND ($cpt <= "79999") )
		{
			$pos2="6";
			return $pos1.$pos2;
		}
		if ( ($ctp >= "80002") AND ($cpt <= "89399") )
		{
			$pos2="5";
			return $pos1.$pos2;
		}
		if ( ($ctp >= "70010") AND ($cpt <= "79999") )
		{
			$pos2="4";
			return $pos1.$pos2;
		}
		if ( ($ctp >= "99241") AND ($cpt <= "99275") )
		{
			$pos2="3";
			return $pos1.$pos2;
		}
		if ( ($ctp >= "10040") AND ($cpt <= "58999") )
		{
			$pos2="2";
			return $pos1.$pos2;
		}
		if ( ($ctp >= "60000") AND ($cpt <= "69979") )
		{
			$pos2="2";
			return $pos1.$pos2;
		}
		if ( ($ctp >= "90700") AND ($cpt <= "99499") )
		{
			$pos2="1";
			return $pos1.$pos2;
		}
		$cptletter = substr($cpt,0,1);
		$cptletter = strtoupper($cptletter);
		if ($cptletter = "J")
		{
			$pos2="1";
			return $pos1.$pos2;
		}
		if ($ctpmod == "26")
		{
			$pos2="4";
			return $pos1.$pos2;
		}
		
		

	} //end champus TOS

	function view() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
	
	    $display_buffer .= "
		<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3
		 VALIGN=MIDDLE ALIGN=CENTER>
		<TR>
		 <TD COLSPAN=2>
		  <CENTER>
		   <B>"._("Generate NSF Commercial Claims")."</B>
		  </CENTER>
		 </TD>
    	</TR>

		<FORM ACTION=\"$this->page_name\" METHOD=POST>
		<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
		<INPUT TYPE=HIDDEN NAME=\"viewaction\" VALUE=\"geninsform\">
		<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"$module\">

		<TR>
		 <TD ALIGN=RIGHT>
		  <CENTER>
		   Claim Form :
		  </CENTER>
		 </TD>
     	<TD ALIGN=LEFT>
      	<SELECT NAME=\"whichform\">
   		";
	   $result = $sql->query ("SELECT * FROM fixedform WHERE id='2'");
							 //ORDER BY ffname, ffdescrip");
	   while ($r = $sql->fetch_array ($result)) {
		$display_buffer .= "
		 <OPTION VALUE=\"$r[id]\">".prepare($r[ffname])."
		";
	   } // end looping through results                         

	   $display_buffer .= "
		 </SELECT>
		 </TD>
		</TR>
		<TR>
		   <TD ALIGN=RIGHT>
			Write To File :
		   </TD><TD ALIGN=LEFT>
			<SELECT NAME=\"write_to_file\">
			 <OPTION VALUE=\"0\">"._("No")."
			 <OPTION VALUE=\"1\">"._("Yes")."
        </SELECT>
		<INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"1\">
       </TD>
    	</TR>
		";

		$display_buffer .= "
		<TR>
		 <TD ALIGN=RIGHT>
		  "._("Userid")."</TD>
		 <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"userid\">
		 </TD>
		";

		$display_buffer .= "
		<TR>
		 <TD ALIGN=RIGHT>
		  "._("Password")."</TD>
		 <TD ALIGN=LEFT>
		   <INPUT TYPE=PASSWORD NAME=\"password\">
		 </TD>
		";


		$display_buffer .= "
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
	

} // end class CommercialMCSIFormsModule

register_module("CommercialMCSIFormsModule");

} // end if not defined

?>
