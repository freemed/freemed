<?php
 // $Id$
 // lic : GPL, v2

LoadObjectDependency('FreeMED.BillingModule');

class MedicareNJPAMCSIFormsModule extends BillingModule {

	// override variables
	var $MODULE_NAME = "MCSI NSF Medicare NJ-PA";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $CATEGORY_NAME = "Billing";
	var $CATEGORY_VERSION = "0.1";

	var $bill_request_type;
    var $form_buffer;
    var $batchno = "0000";
    var $batchid = "000000";
    var $subno = "000000";
    var $pat_processed;
    var $formno;
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
	function MedicareNJPAMCSIFormsModule ($nullvar = "") {
		// call parent constructor
		$this->BillingModule($nullvar);
	} // end function MedicareFormsModule

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
            if ($result == 0) {
			$display_buffer .= __("No patients with this coverage type");
			template_display();
		}
		
			while($row = $sql->fetch_array($result))
			{	
				$coverage = CreateObject('FreeMED.Coverage', $row[proccurcovid]);
				if (!$coverage) {
					$display_buffer .= __("Failed getting coverage");
					template_display();
				}
				// commercial insurers only
				if ($coverage->covinsco) 
				{
					$insmod = freemed::get_link_rec($coverage->covinsco->modifiers[0],"insmod");
					if (!$insmod) {
						$display_buffer .= __("Failed getting insurance modifier");
						template_display();
					}
					if ($insmod[insmod] != "MB")
						continue;
				}
				$this->bill_request_type = $row[proccurcovtp];
				$this->GenerateFixedForms($row[procpatient], $row[proccurcovid]);
			}
			//}
	
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
					$billfilename = "/mcsi_medicare_bills-".$cur_date.gmdate("Hi").".data";
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
				<B>".__("Nothing to Bill!")."</B>
				</CENTER>
				<P>
				<CENTER>
				<A HREF=\"$this->page_name?module=$module\"
				>".__("Return to Fixed Forms Generation Menu")."</A>
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
		$display_buffer .= __("Something is wrong in generateforms");
		template_display();

	}


	function Insurer($procstack) {
		global $display_buffer;
		$bill_request_type = $this->bill_request_type;;

		if ($bill_request_type == PRIMARY)
			$buffer = $this->BillPrimary($procstack);
		else
			//$display_buffer .= "Error - Medicare Secondary Not supported at this Time<BR>";
			$buffer = $this->BillSecondary($prockstack);
		return $buffer;

	}

	function BillSecondary($procstack) {
		global $display_buffer;
		//$display_buffer .= "Error - Medicare Secondary Not supported at this Time<BR>";
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

		$coverage = CreateObject('FreeMED.Coverage', $cov);
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
		$coverage2 = CreateObject('FreeMED.Coverage', $cov2);
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

		$patient = CreateObject('FreeMED.Patient', $pat);
		if (!$patient)
		{
			$display_buffer .= "Error Insurer no patient<BR>";
			return;
		}

		$insmod = freemed::get_link_rec($coverage2->covinsco->modifiers[0],"insmod");
		if (!$insmod)
			$display_buffer .= "Error Primary does not contain an insurance modifier in BillSecondary<BR>";
		
		// Primary is not medicare.	

		$da0[seqno] = "01";
		$da0[patcntl] = $ca0[patcntl];
		$da0[clmfileind] = "I";   // the primary data is info
		$da0[clmsource] = " ";

		if ($insmod == "WC")
			$da0[clmsource] = "B";
		if ($insmod == "CI")
			$da0[clmsource] = "F";
		if ($insmod == "BL")
			$da0[clmsource] = "G";
		if ($insmod == "HM")
			$da0[clmsource] = "I";
		if ($insmod == "FI")
			$da0[clmsource] = "J";

		$covinstp = "";
		if ($coverage2->local_record[covinstp] > 0)
		{
			$covtype = freemed::get_link_rec($coverage2->local_record[covinstp],"covtypes");
			$covinstp = $covtype[covtpname];
			if (empty($covinstp))
			{
				$display_buffer .= "Error Primary does not contain a coverage type BillSecondary<BR>";
				$covinstp = "OT"; // default to other
			}
		}

		$da0[instypcd] = $covinstp;
		$da0[payerid] = $this->CleanNumber($insco2->local_record[inscoid]); // NAIC #
		$da0[payername] = $this->CleanChar($insco2->insconame);
		$da0[patgrpno] = $this->CleanNumber($coverage2->covpatgrpno);
		$da0[patgrpname] = $this->CleanChar($coverage2->covplanname);
		if ($coverage2->local_record[covbenasgn] == 1)
			$da0[assign] = "Y";
		else
			$da0[assign] = "N";

		$da0[patsigsrc] = "C"; // Signed HCFA 1500 on file.
		$da0[supinsind] = "P"; // This guy is primary?


		$da0[patrel] = GetRelationShip($coverage2->covreldep,"NSF");

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
			$guarantor = CreateObject('FreeMED.Guarantor', $coverage2->covdep);
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
/*
		// AUTH NOT NEEDED FOR MC NJ PA
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

		// AUTH NOT NEEDED FOR MC NJ PA
*/

		$da1[recid] = "DA1";
		$da1[seqno] = "01";
		$da1[patcntl] = $ca0[patcntl];
		$da1[payeraddr1] = $this->CleanChar($insco2->local_record[inscoaddr1]);
		$da1[payeraddr2] = $this->CleanChar($insco2->local_record[inscoaddr2]);
		$da1[payercity] = $this->CleanChar($insco2->local_record[inscocity]);
		$da1[payerstate] = $this->CleanChar($insco2->local_record[inscostate]);
		$da1[payerzip] = $this->CleanNumber($insco2->local_record[inscozip]);

/*
		// EOB VALUES NOT NEEDED FOR MC NJ PA	
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
		// EOB VALUES NOT NEEDED FOR MC NJ PA	
*/
		


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

		// now gen the secondary ins info which IS medicare
		unset($GLOBALS[da0]);
		unset($GLOBALS[da1]);
		unset($GLOBALS[da2]);
		global $da0,$da1,$da2;
		
		$da0[recid] = "DA0";
		$da0[seqno] = "02";
		$da0[patcntl] = $ca0[patcntl];
		$da0[clmfileind] = "P";
		$da0[clmsource] = "C";

		$covinstp = "";
		if ($coverage->local_record[covinstp] > 0)
		{
			$covtype = freemed::get_link_rec($coverage->local_record[covinstp],"covtypes");
			$covinstp = $covtype[covtpname];
			if (empty($covinstp))
			{
				$display_buffer .= "Error Medicare Secondary requires Coverage Insurance Type in BillSecondary<BR>";
				$covinstp = "XX"; // default to other
			}
		}

		$da0[instypcd] = $covinstp;

		//$da0[instypcd] = $insco->modifiers[0];
		//$da0[payerid] = $this->CleanNumber($insco->local_record[inscoid]); // NAIC #
		//$da0[payername] = $this->CleanChar($insco->insconame);
		//$da0[patgrpno] = $this->CleanNumber($coverage->covpatgrpno);
		//$da0[assign] = "Y";

		$da0[patsigsrc] = "C"; // signed HCFA 1500 on file

		$da0[patrel] = GetRelationShip($coverage->covreldep,"NSF");

		$da0[patidno] = $this->CleanNumber($coverage->covpatinsno);

/*
		//NOT REQUIRED FOR MC NJ PA
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
			$guarantor = CreateObject('FreeMED.Guarantor', $coverage->covdep);
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

		$da2[recid] = "DA2";
		$da2[seqno] = "02";
		$da2[patcntl] = $ca0[patcntl];
		$da2[insrdaddr1] = $addr1;
		$da2[insrdcity] = $city;
		$da2[insrdstate] = $state;
		$da2[insrdzip] = $zip;

*/

   		$buffer .= render_fixedRecord ($whichform,$this->record_types["da0"]);
   		//$buffer .= render_fixedRecord ($whichform,$this->record_types["da1"]);
   		//$buffer .= render_fixedRecord ($whichform,$this->record_types["da2"]);
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
		$cov2 = $row[proccov2];  // medicare requires info on the secondary.
		$pat = $row[procpatient];

		$da0[recid] = "DA0";
		$da2[recid] = "DA2";

		$coverage = CreateObject('FreeMED.Coverage', $cov);
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

		if ($cov2 != 0)
		{
			$coverage2 = CreateObject('FreeMED.Coverage', $cov2);
			if (!$coverage2)
			{
				$display_buffer .= "Error Insurer Failed secondary coverage<BR>";
				return;
			}

			$insco2 = $coverage2->covinsco;
			if (!$insco2)
			{
				$display_buffer .= "Error Insurer Failed secondary insurance<BR>";
				return;
			}
		}

		$patient = CreateObject('FreeMED.Patient', $pat);
		if (!$patient)
		{
			$display_buffer .= "Error Insurer no patient<BR>";
			return;
		}
			
		$da0[seqno] = "01";
		$da0[patcntl] = $ca0[patcntl];
		
		$da0[clmfileind] = "P";
		$da0[clmsource] = "C";  // C for medicare

		$da0[instypcd] = "MP";  // medicare primary
		//$da0[instypcd] = $insco->modifiers[0];
		$da0[payerid] = $this->CleanNumber($insco->local_record[inscoid]); // NAIC #
		$da0[payername] = $this->CleanChar($insco->insconame);
		//$da0[patgrpno] = $this->CleanNumber($coverage->covpatgrpno);
		$da0[assign] = "Y";
		$da0[patsigsrc] = "C";

		$da0[patrel] = "01";  // must be self for medicare
		$da0[patidno] = $this->CleanNumber($coverage->covpatinsno);

/*
		NOT REQUIRED FOR MEDICARE NJ PA
		$da0[patrel] = GetRelationShip($coverage->covreldep,"NSF");
		$patrel = $da0[patrel];
		ABOVE NOT REQUIRED FOR MEDICARE NJ PA
*/


/*
		NOT REQUIRED FOR MEDICARE NJ PA
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
			$guarantor = CreateObject('FreeMED.Guarantor', $coverage->covdep);
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

		ABOVE NOT REQUIRED FOR MEDICARE NJ PA
*/      

		$buffer = "";
   		$buffer  = render_fixedRecord ($whichform,$this->record_types["da0"]);

/*
		NOT REQUIRED FOR MEDICARE NJ PA
		if ($da0[payerid] == "PAPER")
		{
			$da1[recid] = "DA1";
			$da1[seqno] = "01";
			$da1[patcntl] = $ca0[patcntl];
			$da1[payeraddr1] = $this->CleanChar($insco->local_record[inscoaddr1]);
			$da1[payeraddr2] = $this->CleanChar($insco->local_record[inscoaddr2]);
			$da1[payercity] = $this->CleanChar($insco->local_record[inscocity]);
			$da1[payerstate] = $this->CleanChar($insco->local_record[inscostate]);
			$da1[payerzip] = $this->CleanNumber($insco->local_record[inscozip]);
   			$buffer  .= render_fixedRecord ($whichform,$this->record_types["da1"]);
		}

		if ($da0[patrel] != "01")
		{
			$da2[recid] = "DA2";
			$da2[seqno] = "01";
			$da2[patcntl] = $ca0[patcntl];
			$da2[insrdaddr1] = $addr1;
			$da2[insrdcity] = $city;
			$da2[insrdstate] = $state;
			$da2[insrdzip] = $zip;
   			$buffer .= render_fixedRecord ($whichform,$this->record_types["da2"]);
		}

		ABOVE NOT REQUIRED FOR MEDICARE NJ PA
*/

		unset($GLOBALS[da0]);
		unset($GLOBALS[da1]);
		unset($GLOBALS[da2]);
		global $da0,$da1,$da2;

		// process any secondary coverages now.
		if ($cov2 != 0)
		{
			$insmod = freemed::get_link_rec($coverage2->covinsco->modifiers[0],"insmod");
			if (!$insmod)
				DIE("Failed getting insurance modifier");

			if ($coverage2->local_record[covinstp] > 0)
			{
				$covtype = freemed::get_link_rec($coverage2->local_record[covinstp],"covtypes");
				$covinstp = $covtype[covtpname];
			}
			
			$da0[recid] = "DA0";
			$da0[seqno] = "02";
			$da0[patcntl] = $ca0[patcntl];
			$da0[clmfileind] = "I";
			$da0[patrel] = "01"; // must be self for medicare

			// make a da1 record incase one of these need it.

			$da1[recid] = "DA1";
			$da1[seqno] = "02";
			$da1[patcntl] = $ca0[patcntl];
			$da1[payeraddr1] = $this->CleanChar($insco2->local_record[inscoaddr1]);
			$da1[payeraddr2] = $this->CleanChar($insco2->local_record[inscoaddr2]);
			$da1[payercity] = $this->CleanChar($insco2->local_record[inscocity]);
			$da1[payerstate] = $this->CleanChar($insco2->local_record[inscostate]);
			$da1[payerzip] = $this->CleanNumber($insco2->local_record[inscozip]);

			if ($insmod[insmod] == "MC")  // Medicaid is secondary
			{
				$da0[clmsource] = "D";  // D for Medicade
				$da0[patsigsrc] = "C";  // Signed HCFA 1500
				$da0[mcaidid] = $this->CleanNumber($coverage2->covpatinsno);
   				$buffer  .= render_fixedRecord ($whichform,$this->record_types["da0"]);
				
				
			}

			// it is possible for the modifier to be commercial 
			// but the inst coverage type is Medigap
			if ($insmod[insmod] == "MG" OR $covinstp == "MG")
			{
				// medigap
				$da0[clmsource] = "F";  // F for Medigap
				$da0[instypcd] = "MG";  // Medigap
				$da0[payerid] = $this->CleanNumber($insco2->local_record[inscoid]); // NAIC #
				$da0[patsigsrc] = "C";
				$da0[patidno] = $this->CleanNumber($coverage2->covpatinsno);
   				$buffer  .= render_fixedRecord ($whichform,$this->record_types["da0"]);
   				$buffer  .= render_fixedRecord ($whichform,$this->record_types["da1"]);
			}

			if ($insmod[insmod] == "CI")
			{
				// medigap
				$da0[clmsource] = "F";  // F for Commercial
				$da0[instypcd] = "OT";  // Other type
				if (!empty($covinstp))
					$da0[instypcd] = $covinstp;
				$da0[payerid] = $this->CleanNumber($insco2->local_record[inscoid]); // NAIC #
				$da0[assign] = "Y";
				$da0[patidno] = $this->CleanNumber($coverage2->covpatinsno);
   				$buffer  .= render_fixedRecord ($whichform,$this->record_types["da0"]);
   				$buffer  .= render_fixedRecord ($whichform,$this->record_types["da1"]);
			}
		}

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

		$ca0[recid] = "CA0";
		$cb0[recid] = "XXX";
		$patient = CreateObject('FreeMED.Patient', $pat);
		if (!$patient)
		{
			$display_buffer .= "Error in claimheader no patient<BR>";
			return;
		}

		if (empty($patient->local_record[ptid]))
			$ca0[patcntl] = $patient->local_record[id];
		else
			$ca0[patcntl] = $this->CleanChar($patient->local_record[ptid]);

		$ca0[deathind] = "N";

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

		$coverage = CreateObject('FreeMED.Coverage', $cov);
		if (!$coverage)
		{
			$display_buffer .= "Error. patient coverage invalid<BR>";
			return;
		}

		if ($coverage->covreldep == "LR")  // Legal rep
			$ca0[legalrepind] = "Y";
		else
			$ca0[legalrepind] = "N";

		$covtype = $coverage->local_record[covtype];
		$othrins = 3;  // no other coverage

		if ( ($covtype == PRIMARY) AND ($row[proccov.(SECONDARY)] != 0) )
				$othrins = "1"; // has other coverage
		
		if ( ($covtype == SECONDARY) AND ($row[proccov.(PRIMARY)] != 0) )
				$othrins = "1"; // has other coverage

		// NSF does not handle tertiary!

		$ca0[othrins] = $othrins;

		$ca0[clmeditind] = "C"; // Medicare filing

		$ca0[clmtype] = " ";

		if ($row[procclmtp] > 0)
		{
			$type="";
			$clmtype = freemed::get_link_rec($row[procclmtp],"claimtypes");
			$type = $clmtype[clmtpname];
			if (empty($type))
				$display_buffer .= "Error getting Claimtypes in ClaimHeader<BR>";
			else
				$ca0[clmtype] = $type;
		}

		$buffer = "";		
   		$buffer  = render_fixedRecord ($whichform,$this->record_types["ca0"]);


		// cb0 is required if the patient is
        // under 18 

		if ($ca0[legalrepind] == "Y")
		{
				$cb0[patcntl] = $ca0[patcntl];
                // we assume the guarantor is the responsible party
                if ($coverage->covdep != 0)
                {
                    $guar = CreateObject('FreeMED.Guarantor', $coverage->covdep);
                    if (!$guar)
                        $display_buffer .= "Error getting guarantor in CB0 record<BR>";
                    $cb0[respfname] = $this->CleanChar($guar->guarfname);
                    $cb0[resplname] = $this->CleanChar($guar->guarlname);
                    $cb0[respaddr1] = $this->CleanChar($guar->guaraddr1);
                    $cb0[respaddr2] = $this->CleanChar($guar->guaraddr2);
                    $cb0[respcity] = $this->CleanChar($guar->guarcity);
                    $cb0[respstate] = $this->CleanChar($guar->guarstate);
                    $cb0[respzip] = $this->CleanNumber($guar->guarzip);
                    $buffer  .= render_fixedRecord ($whichform,$this->record_types["cb0"]);

                }
                else
                {
                    $display_buffer .= "Error in Medicare CB0 record for Procedure $row[procdt]<BR>";
                    $display_buffer .= "Under aged patient does not have Guarantor<BR>";
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
		$aa0[recvrtype] = "C";    // C for medicare part B
		$aa0[nsfverno] = "00301";
		$aa0[nsfvernoloc] = "00301";
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
		global $display_buffer;
		
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

		$physician = CreateObject('FreeMED.Physician', $doc);
		if (!$physician)
		{
			$display_buffer .= "Error no physician<BR>";
			return;
		}
		$coverage = CreateObject('FreeMED.Coverage', $cov);
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

		if ($_SESSION["default_facility"] != 0)
        {
            $fac = 0;
            $fac = freemed::get_link_rec($_SESSION["default_facility"], "facility");
            if (!$fac)
                $display_buffer .= "Error getting facility<BR>";
            $ba0[taxid] = $this->CleanNumber($fac[psrein]);
            $ba0[idtype] = "E";

        }
        else
        {
            $ba0[taxid] = $this->CleanNumber($physician->local_record[physsn]);
            $ba0[idtype] = "S";
        }

		// other id's are dependant on the ins NAIC no

		$ba0[prlname] = $this->CleanChar($physician->local_record[phylname]);
		$ba0[prfname] = $this->CleanChar($physician->local_record[phyfname]);
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

		//$upin = $physician->local_record[phyupin];
		//$ba0[upin] = $this->CleanNumber($upin);
		//$ba0[ciid] = $this->CleanNumber($provider_id); // commercial provider id
		$ba0[mcareid] = $this->CleanNumber($provider_id);
		$ba0[emcid] = $this->CleanNumber($provider_id);

		$state = $this->CleanChar($physician->local_record[phystatea]);

		//$display_buffer .= "state $state<BR>";
		if ($state == "PA")
			$ba0[prodnaic] = "M";
		if ($state == "NJ")
			$ba0[prodnaic] = "J";


		$buffer = "";
   		$buffer = render_fixedRecord ($whichform,$this->record_types["ba0"]);
   		//$buffer .= render_fixedRecord ($whichform,$this->record_types["ba1"]);
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

		$coverage = CreateObject('FreeMED.Coverage', $cov);
        if (!$coverage)
        {
            $display_buffer .= "Error Insurer no coverage in ClaimData<BR>";
            return;
        }

		$ea0[recid] = "EA0";
		$ea1[recid] = "XXX";

		$ea0[patcntl] = $ca0[patcntl];

		$ea0[relemp] = "N";
		$ea0[accident] = "N";
		$ea0[symptomind] = "0";

		if ($coverage->local_record[colverinfo] > 0)
			$ea0[relinfoind] = "Y";
		else
			$ea0[relinfoind] = "N";

		//$ea0[provsigind] = "Y";  // sig on file
		if ($coverage->local_record[colprocasgn] > 0)
			$ea0[provassign] = "A";  // accepts assignment
		else
			$ea0[provassign] = "N";  // accepts assignment

		$ea0[docind] = "9";  // no documentation by default

		//$display_buffer .= "referer $row[procrefdoc]<BR>";
		if ($row[procrefdoc] != 0)
		{
			$refdoc = CreateObject('FreeMED.Physician', $row[procrefdoc]);
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

        $diagset = CreateObject('FreeMED.diagnosis_set');
        for ($i=0;$i<$count;$i++)
        {
            $prow = $procstack[$i];

            // this should never overflow if the control break is working.
            $diagset->testAddSet($prow[procdiag1],
                             $prow[procdiag2],
                             $prow[procdiag3],
                             $prow[procdiag4]);

			if ($prow[proccert] > 0)
				$ea0[docind] = 5;

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

		$cov = $row[proccurcovid];

		$coverage = CreateObject('FreeMED.Coverage', $cov);  // all these should have the same cov.
		if (!$coverage)
		{
			$display_buffer .= "Error ServiceDetail no coverage<BR>";
			return;
		}
		$inscoid = $coverage->local_record[covinsco];
		if (!$inscoid)
		{
			$display_buffer .= "Error ServiceDetail no Insurance<BR>";
			return;
		}

			

		$diagset = CreateObject('FreeMED.diagnosis_set');

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
			$fa0[purchservind] = "N";

			if ($this->bill_request_type == SECONDARY)
			{
				$primpdtamt = $this->GetPrimaryAmountPaid($row);
				$fa0[primpdtamt] = $this->MakeDecimal($primpdtamt,2);
				$defltfee = $this->GetMedicareProcFee($row[proccpt],$inscoid);
				$fa0[obacceptamt] = $this->MakeDecimal($defltfee,2);

			}
		
			if ($row[proccptmod] != 0)
			{
				$itemcptmod  = freemed::get_link_field ($row[proccptmod], "cptmod", "cptmod");
				if (!$itemcptmod)
                	$display_buffer .= "Failed reading cptmod table<BR>";
				$fa0[cptmod1] = $itemcptmod;
			}

/*
			// TOS not required for Medicare
			$cur_cpt = freemed::get_link_rec ($row[proccpt], "cpt");
            if (!$cur_cpt)
                $display_buffer .= "Failed reading cpt table<BR>";
            $cur_insco = $insco->local_record[id];
            $tos_stack = fm_split_into_array ($cur_cpt[cpttos]);
            $tosid = ( ($tos_stack[$cur_insco] < 1) ?
                      $cur_cpt[cptdeftos] :
                      $tos_stack[$cur_insco] );
			if ($tosid == 0)
            {
                $display_buffer .= "No default type of service for this proc $row[procdt]<BR>";
                $tos = "TOSXXXX";
            }
            else
            {
                $cur_tos = freemed::get_link_rec($tosid, "tos");
                if (!$cur_tos)
                    $display_buffer .= "Failed reading tos table<BR>";
                $tos = $cur_tos[tosname];
            }
			// TOS not required for Medicare
			$fa0[tos] = $tos;
*/

		
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

   			//$buffer .= render_fixedRecord ($whichform,$this->record_types["fa0"]);

			if ($row[proccert] > 0)
			{
				// we may need to change the fa0 record in the cert code.
				$cert_buffer =  $this->GenCertRecords($row,$seq);
   				$buffer .= render_fixedRecord ($whichform,$this->record_types["fa0"]);
				$buffer .= $cert_buffer;
			}
			else
			{
   				$buffer .= render_fixedRecord ($whichform,$this->record_types["fa0"]);
			}

			unset($GLOBALS[fa0]);
			global $fa0;

		}
		return $buffer;

	} // end servicedetail

	function GenCertRecords($proc,$seq) {
		global $display_buffer;
		// these records are required for various type of certification.
		// currently only DMEPOS is supported  See the medicare DMERC spec.
		unset($GLOBALS[fb1]);
		unset($GLOBALS[gu0]);

		global $ba0,$ca0,$ea0,$fa0,$gu0,$fb1,$whichform;

		//$display_buffer .= "form $whichform in cert<BR>";
		$buffer = "";

		$fb1[recid] = "FB1";
		$fb1[seqno] = $seq;
		$fb1[patcntl] = $ca0[patcntl];
		$fb1[ordprvupin] = $ea0[refprovupin];
		$fb1[ordprvlname] = $ea0[reflname];
		$fb1[ordprvfname] = $ea0[reffname];


		$proccert = $proc[proccert];

		$certrow = 0;
		$certrow = freemed::get_link_rec($proccert,"certifications");
		if (!($certrow))
		{
			$display_buffer .= "Error getting cert<BR>";
			return;
		}
		
		$gu0[recid] = "GU0";
		$gu0[seqno] = $seq;
		$gu0[patcntl] = $ca0[patcntl];


		if ($certrow[certtype] == DMEPOS)
		{
			$fa0[rendprid] = $ba0[mcareid];
			//$display_buffer .= "fa0 rend $fa0[rendprid]<BR>";
			$gu0[certpos] = $fa0[pos];
			$gu0[certcpt] = $fa0[cpt];
			$gu0[certcptmod] = $fa0[cptmod1];

			if ($fa0[diag1] > 0)
				$gu0[certdiag1] = $ea0[diag1];
			if ($fa0[diag2] > 0)
				$gu0[certdiag2] = $ea0[diag2];
			if ($fa0[diag3] > 0)
				$gu0[certdiag3] = $ea0[diag3];
			if ($fa0[diag4] > 0)
				$gu0[certdiag4] = $ea0[diag4];

			$formdata = explode(":",$certrow[certformdata]);
			$gu0[certstatus] = $formdata[1];
			if ($gu0[certstatus] == "1")  // initial cert
				$gu0[certinitdate] = $this->CleanNumber($formdata[2]);
			else // recert
				$gu0[certrevdate] = $this->CleanNumber($formdata[3]);
			$timeneeded = $formdata[4];
			if ($timeneeded < 10) 
				$timeneeded = "0".$timeneeded;
			$gu0[certlenneed] = $timeneeded;
			$gu0[certdatesig] = $this->CleanNumber($formdata[5]);
			$gu0[certonfile] = $this->CleanChar($formdata[6]);
			
			if ($certrow[certformnum] == F0602) // TENS
			{
				$gu0[certformnum] = "0602";
				$gu0[l1n2] = $this->CleanChar($formdata[10]);  // q3
				$gu0[nl4n1] = $this->CleanNumber($formdata[11]);  // q4
				$gu0[l1n3] = $this->CleanChar($formdata[12]);  // q5
				$gu0[l1n4] = $this->CleanChar($formdata[13]);   // q6

				if ($formdata[7] > 0)  // rental
				{	
					$gu0[l1n1] = $this->CleanChar($formdata[8]); // q1
					$gu0[l8n1] = $this->CleanNumber($formdata[9]); // q2
				}
				else // purchase
				{

					$gu0[l1n5] = $this->CleanChar($formdata[14]);  // q7
					$gu0[l8n2] = $this->CleanNumber($formdata[15]);  // q8a
					$gu0[l8n3] = $this->CleanNumber($formdata[16]);  // q8b
					$gu0[l8n4] = $this->CleanNumber($formdata[17]);  // q9
					$gu0[l1n6] = $this->CleanChar($formdata[18]);  // q10
					$gu0[l1n7] = $this->CleanChar($formdata[19]);  // q11
					$gu0[l1n8] = $this->CleanChar($formdata[20]);  // q12
					

				}  // end rental?

			} // end TENS form
				

			$buffer .= render_fixedRecord ($whichform,$this->record_types["fb1"]);
			$buffer .= render_fixedRecord ($whichform,$this->record_types["gu0"]);
		}  // end DMEPOS cert type
	
		return $buffer;	

	} // end gencertrecords

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
		$payerpaid = 00.00;
		$total_deductable = 00.00;
		$total_copay = 00.00;

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

			if ($this->bill_request_type == SECONDARY)
			{
				// when medicare is secondary
				// total paid by primary
				$query = "SELECT * FROM payrec WHERE payrecproc='$row[id]' AND
												payrecsource='".PRIMARY."' AND
												payreclink = '$row[proccov1]' AND
												payreccat='".PAYMENT."'";
				$pay_result = $sql->query($query) or DIE("Query failed for primary payments");
				while ($pay_row = $sql->fetch_array($pay_result))
				{
					$payerpaid += $pay_row[payrecamt];
				}

				// total deductables
				$query = "SELECT * FROM payrec WHERE payrecproc='$row[id]' AND
												payrecsource='".PRIMARY."' AND
												payreclink = '$row[proccov1]' AND
												payreccat='".DEDUCTABLE."'";
				$pay_result = $sql->query($query) or DIE("Query failed for primary payments");
				while ($pay_row = $sql->fetch_array($pay_result))
				{
					$total_deductable += $pay_row[payrecamt];
				}
				// total copay
				$query = "SELECT * FROM payrec WHERE payrecproc='$row[id]' AND
												payrecsource='".PRIMARY."' AND
												payreclink = '$row[proccov1]' AND
												payreccat='".COPAY."'";
				$pay_result = $sql->query($query) or DIE("Query failed for primary payments");
				while ($pay_row = $sql->fetch_array($pay_result))
				{
					$total_copay += $pay_row[payrecamt];
				}

				$data = $this->MakeDecimal($payerpaid,2);
				$xa0[totpayeramt] = $data;
				$data = $this->MakeDecimal($total_deductable,2);
				$xa0[totdeduct] = $data;
				$data = $this->MakeDecimal($total_copay,2);
				$xa0[totcoins] = $data;
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
     	$this_patient = CreateObject('FreeMED.Patient', $parmpatient);
        if (!$this_patient)
			trigger_error("Failed retrieving patient", E_USER_ERROR);
			
     	$display_buffer .= "
      	<B>".__("Processing")." ".$this_patient->fullName()."
      	<BR>\n\n
     	";
     	flush ();

		$this_coverage = CreateObject('FreeMED.Coverage', $parmcovid);
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
		   <B>".__("Generate NSF Medicare Claims")."</B>
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
			 <OPTION VALUE=\"0\">".__("No")."
			 <OPTION VALUE=\"1\">".__("Yes")."
        </SELECT>
		<INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"1\">
       </TD>
    	</TR>
		";

		$display_buffer .= "
		<TR>
		 <TD ALIGN=RIGHT>
		  ".__("Userid")."</TD>
		 <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"userid\">
		 </TD>
		";

		$display_buffer .= "
		<TR>
		 <TD ALIGN=RIGHT>
		  ".__("Password")."</TD>
		 <TD ALIGN=LEFT>
		   <INPUT TYPE=PASSWORD NAME=\"password\">
		 </TD>
		";

		$display_buffer .= "
		<TR>
		 <TD COLSPAN=2>
		  <CENTER>
		   <INPUT TYPE=SUBMIT VALUE=\"".__("Go")."\">
		  </CENTER>
		 </TD>
		</TR>

		</FORM>

		</TABLE>
	   ";
	} // end view functions

	//misc helpers
	function GetPrimaryAmountPaid($procrec) {
		global $display_buffer;
		global $sql;

		$query = "SELECT * FROM payrec WHERE payrecproc='$procrec[id]' AND
										payrecsource='".PRIMARY."' AND
										payreclink = '$procrec[proccov1]' AND
										payreccat='".PAYMENT."'";
		$pay_result = $sql->query($query) or DIE("Query failed for primary payments");
		while ($pay_row = $sql->fetch_array($pay_result))
		{
			$payerpaid += $pay_row[payrecamt];
		}

		return $payerpaid;


	}

	function GetMedicareProcFee($cptid,$inscoid) {
		global $display_buffer;
		global $sql;

		$cur_cpt = 0;
		$mcfee = 0;
		$cur_cpt = freemed::get_link_rec ($cptid, "cpt");
		if (!$cur_cpt)
			$display_buffer .= "Failed reading cpt table in GetMedicareProcFee<BR>";

		$fee_stack = fm_split_into_array ($cur_cpt[cptstdfee]);
		$mcfee = $fee_stack[$inscoid];

		if ($mcfee == 0)
			$display_buffer .= "Error - No Standard Medicare fee for procedure<BR>";

		return $mcfee;
	}
	

} // end class MedicareNJPAMCSIFormsModule

register_module("MedicareNJPAMCSIFormsModule");

?>
