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
	var $CATEGORY_VERSION = "0";

	var $bill_request_type;
	var $ins_upin = array("HCPHM", "HCPMC", "19572");
	var $ins_medicaid = array("26374", "26375", "MSC33", "SET22", "SPH11", "88833");
	var $ins_commercial = array("MSC11","MSC22","MSC33","88811","88822","88833","75201","19572","94999");
    var $form_buffer;
    var $batchno = "0000";
    var $batchid = "000000";
    var $subno = "000000";
    var $pat_processed;
    var $patient_forms;
    var $patient_cov;
    var $patient_procs;
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
		"za0" => "16"
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
			//if ($bill_request_type > 0)
			//{

			//$query = "SELECT covpatient,id,covtype FROM coverage WHERE covtype='".PRIMARY."' OR covtype='".SECONDARY."'";
			$query = "SELECT DISTINCT procpatient,proccurcovid,proccurcovtp FROM procrec ".
               "WHERE proccurcovtp='".PRIMARY."' OR proccurcovtp='".SECONDARY."'".
               " AND procbilled='0' AND procbillable='0' AND procbalcurrent>'0'";
			$result = $sql->query($query);
			if (!$sql->results($result)) 
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
					$insmod = freemed_get_link_rec($coverage->covinsco->modifiers[0],"insmod");
					if (!$insmod)
						DIE("Failed getting insurance modifier");
					if ($insmod[insmod] != "CI")
						continue;
				}
				$this->bill_request_type = $row[proccurcovtp];
				$this->GenerateFixedForms($row[procpatient], $row[proccurcovid]);
			}
			//}
	
			if (!empty($this->form_buffer))
			{
				$new_buffer = $this->FileHeader();
				$this->form_buffer = $new_buffer.$this->form_buffer;
				$this->form_buffer = $this->FileTrailer($this->form_buffer);	
				//$new_buffer="";
				$recs = explode("\n",$this->form_buffer);
				$count = count($recs);
				if ($count == 0)
				{
					echo "Error No records generated<BR>";
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
					$filename = "/tmp/mcsi_comm_bills-".$cur_date.gmdate("Hi").".data";
        			$fp = fopen($filename,"w");

        			if (!$fp)
        			{
            			echo "Error opening $filename<BR>";
        			}

        			$rc = fwrite($fp,$file_buffer);

        			if ($rc <= 0)
            			echo "Error writing $filename<BR>";
					else
        				echo "Wrote bills to $filename<BR>";
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

	function Insurer($procstack)
	{
		$bill_request_type = $this->bill_request_type;;

		if ($bill_request_type == PRIMARY)
			$buffer = $this->BillPrimary($procstack);
		else
			$buffer = $this->BillSecondary($prockstack);
		return $buffer;

	}

	function BillSecondary($procstack)
	{
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
			echo "Error Insurer no coverage<BR>";
			return;
		}

		$insco = $coverage->covinsco;
		if (!$insco)
		{
			echo "Error Insurer no insurance<BR>";
			return;
		}

		// cov2 is actually the primary coverage
		$coverage2 = new Coverage($cov2);
		if (!$coverage2)
		{
			echo "Error Insurer no primary coverage<BR>";
			return;
		}

		$insco2 = $coverage2->covinsco;
		if (!$insco2)
		{
			echo "Error Insurer no primary insurance<BR>";
			return;
		}

		$patient = new Patient($pat);
		if (!$patient)
		{
			echo "Error Insurer no patient<BR>";
			return;
		}
			
		$da0[seqno] = "01";
		$da0[patcntl] = $ca0[patcntl];
		$da0[clmfileind] = "I";
		$da0[clmsource] = " ";
		//$da0[instypcd] = $insco2->modifiers[0];
		$da0[payerid] = $this->CleanNumber($insco2->local_record[inscoid]); // NAIC #
		$da0[payername] = $this->CleanChar($insco2->insconame);
		$da0[patgrpno] = $this->CleanNumber($coverage2->covpatgrpno);
		$da0[assign] = "Y";
		$da0[patsigsrc] = "C";

		if ($coverage2->covreldep == "S")
			$da0[patrel] = "01";
		if ($coverage2->covreldep == "H" OR $coverage2->covreldep == "W")
			$da0[patrel] = "02";
		if ($coverage2->covreldep == "C")
			$da0[patrel] = "03";

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
				echo "Error Insurer guarantor failed<BR>";
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
            $auth_row = freemed_get_link_rec($row[procauth],"authorizations");
            if (!$auth_row)
                echo "Failed to read procauth";
			$auth_num = $auth_row[authnum];
            if (!$auth_num)
            {
                echo "Authorization number Invalid";
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
					echo "Warning: Authorization $auth_num has expired for procedure $procdt";

				}
			}
			$da0[authno] = $this->CleanNumber($auth_num);
        }
        else
        {
            echo "Warning - No Authorization for this procedure<BR>";
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
		$da0[clmfileind] = "I";
		$da0[clmsource] = " ";
		$da0[instypcd] = $insco->modifiers[0];
		$da0[payerid] = $this->CleanNumber($insco->local_record[inscoid]); // NAIC #
		$da0[payername] = $this->CleanChar($insco->insconame);
		$da0[patgrpno] = $this->CleanNumber($coverage->covpatgrpno);
		$da0[assign] = "Y";
		$da0[patsigsrc] = "C";

		if ($coverage->covreldep == "S")
			$da0[patrel] = "01";
		if ($coverage->covreldep == "H" OR $coverage->covreldep == "W")
			$da0[patrel] = "02";
		if ($coverage->covreldep == "C")
			$da0[patrel] = "03";
		
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
				echo "Error Insurer guarantor failed<BR>";
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

   		$buffer .= render_fixedRecord ($whichform,$this->record_types["da0"]);
   		$buffer .= render_fixedRecord ($whichform,$this->record_types["da1"]);
   		$buffer .= render_fixedRecord ($whichform,$this->record_types["da2"]);
		return $buffer;
		
	} // end do secondary bill

	function BillPrimary($procstack)
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		unset($GLOBALS[da0]);
		unset($GLOBALS[da2]);

		global $da0,$da2;

		$row = $procstack[0]; // all rows are the same
		$cov = $row[proccurcovid];
		$pat = $row[procpatient];

		$da0[recid] = "DA0";
		$da2[recid] = "DA2";

		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			echo "Error Insurer no coverage<BR>";
			return;
		}

		$insco = $coverage->covinsco;
		if (!$insco)
		{
			echo "Error Insurer no insurance<BR>";
			return;
		}

		$patient = new Patient($pat);
		if (!$patient)
		{
			echo "Error Insurer no patient<BR>";
			return;
		}
			
		$da0[seqno] = "01";
		$da0[patcntl] = $ca0[patcntl];
		
		$da0[clmfileind] = "P";
		$da0[clmsource] = "F";

		//$da0[instypcd] = $insco->modifiers[0];
		$da0[payerid] = $this->CleanNumber($insco->local_record[inscoid]); // NAIC #
		$da0[payername] = $this->CleanChar($insco->insconame);
		$da0[patgrpno] = $this->CleanNumber($coverage->covpatgrpno);
		$da0[assign] = "Y";
		$da0[patsigsrc] = "C";

		if ($coverage->covreldep == "S")
			$da0[patrel] = "01";
		if ($coverage->covreldep == "H" OR $coverage->covreldep == "W")
			$da0[patrel] = "02";
		if ($coverage->covreldep == "C")
			$da0[patrel] = "03";

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
				echo "Error Insurer guarantor failed<BR>";
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
            $auth_row = freemed_get_link_rec($row[procauth],"authorizations");
            if (!$auth_row)
                echo "Failed to read procauth";
			$auth_num = $auth_row[authnum];
            if (!$auth_num)
            {
                echo "Authorization number Invalid";
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
					echo "Warning: Authorization $auth_num has expired for procedure $procdt";

				}
			}
			$da0[authno] = $this->CleanNumber($auth_num);
        }
        else
        {
            echo "Warning - No Authorization for this procedure<BR>";
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
		//echo "da2 addr city state zip $addr1 $city $state $zip<BR>";
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

	function ClaimHeader($procstack)
	{
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
		$patient = new Patient($pat);
		if (!$patient)
		{
			echo "Error in claimheader no patient<BR>";
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

		if ($patient->local_record[ptstatus] == 0)
		{
			$ca0[patstudent] = "N";
		}
		else
		{
			// look up the status record.
			$status = freemed_get_link_field($patient->local_record[ptstatus],"ptstatus","ptstatus");
			if (!$status)
				echo "Error failed to get ptstatus<BR>";
			if ($status == "HC")
				$ca0[patstudent] = "N";
			else
			{
				$datediff = date_diff($patient->local_record[ptdob]);
				$yrdiff = $datediff[0];
				echo "year diff $yrdiff<BR>";
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
			echo "Error. patient coverage invalid<BR>";
			return;
		}
		$covtype = $coverage->local_record[covtype];
		$othrins = 3;  // no other coverage

		if ( ($covtype == PRIMARY) AND ($row[proccov.(SECONDARY)] != 0) )
				$othrins = "1"; // has other coverage
		
		if ( ($covtype == SECONDARY) AND ($row[proccov.(PRIMARY)] != 0) )
				$othrins = "1"; // has other coverage

		// NSF does not handle tertiary!

		$ca0[othrins] = $othrins;

		$ca0[clmeditind] = "F"; // comercial filing
		$ca0[clmtype] = " ";

		// need origin codes champus provider id if payer is REG06

		// cb0 is required for champus (REG06) if the patient is
        // under 18 at the time of service. difference between patdob and the highest date of service
		// is < 18

		$buffer = "";		
   		$buffer  = render_fixedRecord ($whichform,$this->record_types["ca0"]);
		return $buffer;

	} // end patient

	function FileHeader()
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		unset($GLOBALS[aa0]);
		global $aa0;
		$aa0[recid] = "AA0";
		$aa0[submtrid] = "FFOREST";
		$aa0[subtype] = "U";

		$this->subno++;
		// this should be saved in the ch table. gotton when started.
        // incremented as used then saved when done.
		$aa0[subno] = $this->subno;  // once in 7 months!!!

		$aa0[createdt] = $this->CleanNumber($cur_date);
		$aa0[recvrid] = "MIXED";  // file contains claims for multiple payers
		$aa0[recvrtype] = "F";
		$aa0[nsfverno] = "00301";
		$aa0[testprod] = "TEST";
		$aa0[password] = "FORESTER";
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

	function ProviderHeader($procstack)
	{
		
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
			echo "Error no physician<BR>";
			return;
		}
		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			echo "Error no coverage<BR>";
			return;
		}
		$insco = $coverage->covinsco;
		if (!$insco)
		{
			echo "Error no insco<BR>";
			return;
		}
		
		
	
		$ba0[batchtype] = "100";  
		
		$this->batchno++;

		$ba0[batchnum] = $this->batchno; // needs incrementer

		$this->batchid++;
		// this should be saved in the ch table. gotton when started.
        // incremented as used then saved when done.
		$ba0[batchid] = $this->batchid;  // only used once for 30 days!!!

		if ($default_facility != 0)
		{
			$fac = 0;
			$fac = freemed_get_link_rec($default_facility,"facility");
			if (!$fac)
				echo "Error getting facility<BR>";
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
		$ba0[prspec]  = $this->CleanNumber($physician->local_record[physpe1]);

		$provider_id = "0";
        $grp = $insco->local_record[inscogroup];
        if (!$grp)
        {
            $name = $insco->local_record[insconame];
            echo "Failed getting inscogroup for $name<BR>";
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

	function ClaimData($procstack)
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		unset($GLOBALS[ea0]);
		unset($GLOBALS[ea1]);
		global $ea0,$ea1;
		
		$row = $procstack[0];

		$ea0[recid] = "EA0";
		$ea1[recid] = "XXX";

		$ea0[patcntl] = $ca0[patcntl];

		$ea0[relemp] = "N";
		$ea0[accident] = "N";
		$ea0[symptomind] = "0";
		$ea0[relinfoind] = "Y";

		//echo "referer $row[procrefdoc]<BR>";
		if ($row[procrefdoc] != 0)
		{
			$refdoc = new Physician($row[procrefdoc]);
			if (!$refdoc)
				echo "Error getting referring physician<BR>";
			$ea0[refprovupin] = $this->CleanChar($refdoc->local_record[phyupin]);	
			$ea0[reflname] = $this->CleanChar($refdoc->local_record[phylname]);	
			$ea0[reffname] = $this->CleanChar($refdoc->local_record[phyfname]);	
		}

		if ($row[proceoc] != 0)
        {
            $eoc_row = freemed_get_link_rec($row[proceoc], "eoc");
            if (!$eoc_row)
                echo "Failed reading eoc record<BR>";

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
            echo "Warning - No EOC for this procedure $row[procdt]<BR>";
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
            echo "Error in GenClaimSegment Stack count 0<BR>";
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
		//echo "stack count $diagcnt<BR>";

        if ($diagcnt == 0)
        {
            echo "Procedures do not have Diagnosis codes<BR>";
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
		$fac_row = freemed_get_link_rec($row[procpos], "facility");

        if ($fac_row)
        {
            // use code from facility
            if ($fac_row[psrpos] == 0)
            {
                echo "Facility does not have a pos code<BR>";
            }
            $cur_pos = freemed_get_link_rec($fac_row[psrpos], "pos");
            if (!$cur_pos)
                echo "Failed reading pos table";
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

	function ServiceDetail($procstack)
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		unset ($GLOBALS[fa0]);
		global $fa0;

		$row = $procstack[0];

		$diagset = new diagnosisSet();

		$pos = 0;
		$fac_row=0;
		$fac_row = freemed_get_link_rec($row[procpos], "facility");
        if ($fac_row)
        {
            // use code from facility
            if ($fac_row[psrpos] == 0)
            {
                echo "Facility does not have a pos code<BR>";
            }
            $cur_pos = freemed_get_link_rec($fac_row[psrpos], "pos");
            if (!$cur_pos)
                echo "Failed reading pos table";
            $pos = $cur_pos[posname];

        }
        if ($pos == 0)
        {
            // plug with Office code
            $pos="11";
            echo "Warning: Plugged pos with Office Code 11";
        }
		
		$count = count($procstack);

		if ($count == 0)
		{
			echo "Error no procedures in Service<BR?";
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
				$itemcptmod  = freemed_get_link_field ($row[proccptmod], "cptmod", "cptmod");
				if (!$itemcptmod)
                	echo "Failed reading cptmod table<BR>";
				$fa0[cptmod1] = $itemcptmod;
			}

			$cur_cpt = freemed_get_link_rec ($row[proccpt], "cpt");
            if (!$cur_cpt)
                echo "Failed reading cpt table<BR>";
            $cur_insco = $insco->local_record[id];
            $tos_stack = fm_split_into_array ($cur_cpt[cpttos]);
            $tosid = ( ($tos_stack[$cur_insco] < 1) ?
                      $cur_cpt[cptdeftos] :
                      $tos_stack[$cur_insco] );
			if ($tosid == 0)
            {
                echo "No default type of service for this proc $row[procdt]<BR>";
                $tos = "TOSXXXX";
            }
            else
            {
                $cur_tos = freemed_get_link_rec($tosid, "tos");
                if (!$cur_tos)
                    echo "Failed reading tos table<BR>";
                $tos = $cur_tos[tosname];
            }

			$fa0[tos] = $tos;
		
			$cur_cpt = freemed_get_link_rec ($row[proccpt], "cpt");
            if (!$cur_cpt)
                echo "Failed reading cpt table<BR>";

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
				//echo "xref $diag_xref[$x]<BR>";
				$xoff = $x+1;
				$var = "diag".$xoff;
				$fa0[$var] = $diag_xref[$x];
			}
			$data = $this->MakeDecimal($row[procunits],1);
			$fa0[units] = $data;

   			$buffer .= render_fixedRecord ($whichform,$this->record_types["fa0"]);
			unset ($GLOBALS[fa0]);
			global $fa0;

		}
		return $buffer;

	} // end servicedetail

	function ClaimTrailer($procstack,$buffer)
	{
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
			echo "Error no procedures in Service<BR?";
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

	function ProviderTrailer($procstack, $buffer)
	{
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
		//echo "count $tot<BR>";
		$ya0[batchreccnt] = $tot;
		$ya0[svclinecnt] = $fxx;
		$ya0[batchclmcnt] = $cxx;
		$ya0[batchtotchg] = $xa0[totalcharge]; // only one batch per control break

		$this->batchcnt++;
		$this->batchreccnt += $tot;
		$this->svclinecnt += $fxx;
		$this->batchclmcnt += $cxx;
		$this->batchtotchg += $xa0[totalcharge];

		//$za0[recid] = "ZA0";
		//$za0[subid] = $aa0[submtrid];
		//$za0[recvrid] = $aa0[recvrid];
		//$za0[filesvclinecnt] = $ya0[svclinecnt];
		//$za0[filereccnt] = $ya0[batchreccnt];
		//$za0[fileclmcnt] = $ya0[batchclmcnt];
		//$za0[batchcnt] = "0001";  
		//$za0[filetotchg] = $ya0[batchtotchg];

   		$buffer = render_fixedRecord ($whichform,$this->record_types["ya0"]);
		return $buffer;

	} // end provider trailer
	
	function FileTrailer($buffer)
	{
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
		//	echo "Error getting buffer<BR>";
		//}
		
		$za0[filesvclinecnt] = $this->svclinecnt;
		$za0[filereccnt] = $this->batchreccnt;
		$za0[fileclmcnt] = $this->batchclmcnt;
		$za0[batchcnt] = $this->batchcnt;
		$za0[filetotchg] = $this->batchtotchg;

   		$zabuffer = render_fixedRecord ($whichform,$this->record_types["za0"]);
		$new_buffer = $buffer.$zabuffer;
		return $new_buffer;

		//$za0[filesvclinecnt] = $ya0[svclinecnt];
		//$za0[filereccnt] = $ya0[batchreccnt];
		//$za0[fileclmcnt] = $ya0[batchclmcnt];
		//$za0[batchcnt] = "0001";  
		//$za0[filetotchg] = $ya0[batchtotchg];

	} // end file trailer

	function ProcessClaims($procstack)
	{

		$buffer  = $this->ProviderHeader($procstack); // batch header	
		$buffer .= $this->ClaimHeader($procstack); // claim headers
		$buffer .= $this->Insurer($procstack);    // insurance records 
		$buffer .= $this->ClaimData($procstack);    // claim data EA0-EA1
		$buffer .= $this->ServiceDetail($procstack);    // service FA0-FB0-FB1
		//$this->ClaimTrailer($procstack);    // clm trlr XA0
		//$this->ProviderTrailer($procstack); // batch trailer
		return $buffer;
	}

	function MakeDecimal($data,$places)
	{
		$data = bcadd($data,0,$places);
		$data = $this->CleanNumber($data);
		return $data;		
	
	}
	function CleanChar($data)
    {
            $data = stripslashes($data);
            $data = str_replace("/"," ",$data);
            $data = str_replace("'"," ",$data);
            $data = str_replace("-"," ",$data);
            $data = str_replace(";"," ",$data);
            $data = str_replace("(","",$data);
            $data = str_replace(")","",$data);
            $data = str_replace(":"," ",$data);
            $data = str_replace("."," ",$data);
            $data = str_replace(","," ",$data);
            $data = trim($data);
            $data = strtoupper($data);
            return $data;
    } // end cleanchar

    function CleanNumber($data)
    {
            $data = $this->CleanChar($data);
            $data = str_replace(" ","",$data);
            $data = trim($data);
            return $data;
    } // end cleannumber

    function NewKey($row)
    {
		   	$pos = $row["procpos"];
		   	$doc = $row["procphysician"];
		   	$ref = $row["procrefdoc"];
	   	   	$auth = $row["procauth"];
           	$eoc = $row["proceoc"];
           	$cov1 = $row["proccov1"];
           	$cov2 = $row["proccov2"];

			$date = $row["procdt"];
            $date = str_replace("-","",$date);
			$datey = substr($date,0,4);
			$datem = substr($date,4,2);
           	$newkey = $pos.$doc.$ref.$eoc.$auth.$cov1.$cov2.$datey.$datem;
            return $newkey;

    } // end newkey

	function GenerateFixedForms($parmpatient, $parmcovid)
	{
		
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		reset ($this->rendorform_variables);
		while (list($k,$v)=each($this->rendorform_variables)) global $$v;


	    // zero the buffer and counter
	    $buffer = "";
	    $counter = 0;
	    $current_patient = 0;

		$bill_request_type = $this->bill_request_type;;

	    // get list of all patient who need to be billed
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


        // grab form information form
        $this_form = freemed_get_link_rec ($whichform, "fixedform");


		$patient = new Patient($parmpatient);
		if (!$patient)
			DIE("Failed to get Patient");
		$coverage = new Coverage($parmcovid);
		if (!$coverage)
			DIE("Failed to get coverage");

		$patname = $patient->fullName(false);

		$insname = $coverage->covinsco->insconame;
		echo "Processing $patname for $insname<BR>";	
		
     //
     // grab all the procedure for this patient
     //
		$bill_request_type = $this->bill_request_type;;
	    $query = "SELECT * FROM procrec
							  WHERE (proccurcovtp = '$bill_request_type' AND
									 proccurcovid = '$parmcovid' AND
									 procbalcurrent > '0' AND
									 procpatient = '$parmpatient' AND
                                     procbillable = '0' AND
                                     procbilled = '0') 
                      ORDER BY procpos,procphysician,procrefdoc,proceoc,procauth,proccov1,proccov2,procdt";
     	$result = $sql->query ($query);

     	if (!$result or ($result==0))
       		DIE ("Failed getting procedures");

     	$first_procedure = 0;
		$proccount=0;
		$totprocs = 0;
     	while ($r = $sql->fetch_array ($result)) 
		{
       		if ($first_procedure == 0)
       		{
				$prev_key = $this->NewKey($r);
				$diagset = new diagnosisSet();
           		$first_procedure = 1;
       		}

			$this->patient_procs[$parmpatient][$totprocs] = $r[id];
			$totprocs++;
	
			$cur_key = $this->NewKey($r);

			//echo "physician $r[procphysician]<BR>";

       		$render_form = true; // reset to render form

       		if (!($diagset->testAddSet ($r[procdiag1], $r[procdiag2],
                                    $r[procdiag3], $r[procdiag4])) OR
            	($proccount == $this_form[ffloopnum]         )  OR
            	($prev_key != $cur_key) )
       		{
         		if ($prev_key != $cur_key)
				{
              		$prev_key = $cur_key;
					//echo "keychange $r[procphysician]<BR>";
				}

         		// drop the current form to the buffer
		 		$form_buffer = $this->ProcessClaims($procstack); // batch trailer
         		//$form_buffer .= render_fixedRecord ($whichform);
				$form_buffer .= $this->ClaimTrailer($procstack,$form_buffer);
				$form_buffer .= $this->ProviderTrailer($procstack,$form_buffer);
         		//$this->form_buffer .= render_fixedRecord ($whichform);
				$this->form_buffer .= $form_buffer;

         		// reset the diag_set array
         		unset ($diagset);
		 		unset ($procstack);
				reset ($this->rendorform_variables);
				//while (list($k,$v)=each($this->rendorform_variables)) 
				//{
				//	unset($$v);
					//echo "$$v<BR>";
				//}
				$proccount=0;
         		$diagset = new diagnosisSet ();
         		$test_AddSet = $diagset->testAddSet ($r[procdiag1], 
								$r[procdiag2], 
								$r[procdiag3], 
								$r[procdiag4]);
         		if (!$test_AddSet)
           			 DIE("AddSet failed!!");

       		} 

			$procstack[$proccount] = $r;
			$proccount++;



     	} // end of looping for all charges


	 	if ($proccount > 0)
		{
        	// drop the current form to the buffer
			reset ($this->rendorform_variables);
			//while (list($k,$v)=each($this->rendorform_variables)) 
			//	unset($$v);
		 	$form_buffer = $this->ProcessClaims($procstack); // batch trailer
         	//$form_buffer .= render_fixedRecord ($whichform);
			$form_buffer .= $this->ClaimTrailer($procstack,$form_buffer);
			$form_buffer .= $this->ProviderTrailer($procstack,$form_buffer);
         	//$this->form_buffer .= render_fixedRecord ($whichform);
			$this->form_buffer .= $form_buffer;
		}
		

     	$this->pat_processed++;
     	$this->patient_forms[$this->pat_processed] = $parmpatient;
     	$this->patient_cov[$this->pat_processed] = $parmcovid;


   } // end generateFixed

   function ShowBillsToMark()
   {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		reset ($this->rendorform_variables);
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
	   $result = $sql->query ("SELECT * FROM fixedform WHERE id='2'");
							 //ORDER BY ffname, ffdescrip");
	   while ($r = $sql->fetch_array ($result)) {
		echo "
		 <OPTION VALUE=\"$r[id]\">".prepare($r[ffname])."
		";
	   } // end looping through results                         

	   echo "
		 </TD>
		</TR>
		<TR>
		   <TD ALIGN=RIGHT>
			<$STDFONT_B>Write To File : <$STDFONT_E>
		   </TD><TD ALIGN=LEFT>
			<SELECT NAME=\"write_to_file\">
			 <OPTION VALUE=\"0\">"._("No")."
			 <OPTION VALUE=\"1\">"._("Yes")."
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
	

} // end class CommercialMCSIFormsModule

register_module("CommercialMCSIFormsModule");

} // end if not defined

?>
