<?php
 // $Id$
 // desc: base class used by various nsf generators
 // lic : GPL, v2

include_once("lib/render_forms.php");

if (!defined ("__NSF_PHP")) {

define ('__NSF_PHP', true);

class NSF
{

	var $form_buffer;
    var $batchno = "0000";
    var $batchid = "000000";
    var $subno = "000000";
    var $formno;
    var $insmod;
	var $userid;
	var $password;

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

	function NSF()
	{
		return;
	}
	function NSF_Setup($user,$pw,$formno,$insmod)
	{
		$this->userid = $user;
		$this->password = $pw;
		$this->formno = $formno;
		$this->insmod = $insmod;
		return;
	}

	function Insurer($procstack)
    {
		$row = $procstack[0];
		
		// cov types should be the same for entire stack
        $bill_request_type = $row[proccurcovtp]; 

        if ($bill_request_type == PRIMARY)
            $buffer = $this->PrimaryInfo($procstack);
        else
            $buffer = $this->SecondaryInfo($procstack);
        return $buffer;

    }


	function SecondaryInfo($procstack)
	{
		
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		global $da0,$da1,$da2;

		$row = $procstack[0]; // all rows are the same
		$billto = $row[proccurcovtp];
		$cov = $row[proccov2];
		$pat = $row[procpatient];

		$da0[recid] = "DA0";
		$da1[recid] = "XXX";
		$da2[recid] = "XXX";

		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			echo "Error Insurer no coverage covid".$row[proccov2]." procid ".$row[id]."<BR>";
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
			
		$da0[recid] = "DA0";
		$da0[seqno] = "02";
		$da0[patcntl] = $ca0[patcntl];
		$da0[payerid] = $this->CleanNumber($insco->local_record[inscoid]); // NAIC #
		$da0[payername] = $this->CleanChar($insco->insconame);
		$da0[patgrpno] = $this->CleanNumber($coverage->covpatgrpno);

		if ($billto == SECONDARY)	
			$da0[clmfileind] = "P"; // request pay from secondary
		else
			$da0[clmfileind] = "I"; // secondary is info only

		$clmsource = $this->GetClaimSource($insco);
		//echo "clmsource $clmsource<BR>";
		if (empty($clmsource))
			echo "ERROR - No Claim Source in SecondaryInfo<BR>";
		else
			$da0[clmsource] = $clmsource;

		if ($coverage->local_record[covbenasgn] == 1)	
			$da0[assign] = "Y";
		else
			$da0[assign] = "N";
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

		$auth_num = $this->VerifyAuth($procstack);
		if ($auth_num != "0")
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

		return;

	} // end SecondaryInfo

	function PrimaryInfo($procstack)
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		//unset($GLOBALS[da0]);
		//unset($GLOBALS[da1]);
		//unset($GLOBALS[da2]);

		global $da1,$da0,$da2;

		$row = $procstack[0]; // all rows are the same
        $billto = $row[proccurcovtp]; 
		$cov = $row[proccov1];
		$pat = $row[procpatient];

		$da0[recid] = "DA0";

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
		if ($billto == PRIMARY)	
			$da0[clmfileind] = "P"; // request pay from primary
		else
			$da0[clmfileind] = "I"; // primary is info only

		$clmsource = $this->GetClaimSource($insco);
		//echo "clmsource $clmsource<BR>";
		if (empty($clmsource))
			echo "ERROR - No Claim Source in PrimaryInfo<BR>";
		else
			$da0[clmsource] = $clmsource;

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

		$auth_num = $this->VerifyAuth($procstack);

		if ($auth_num != "0")
		{
			if ($da0[payerid] == "MHN") // Managed Health Network are IDIOTS!
				$da0[mcaidid] = $this->CleanNumber($auth_num);
			else
				$da0[authno] = $this->CleanNumber($auth_num);
		}

		$da1[recid] = "DA1";
		$da1[seqno] = "01";
		$da1[patcntl] = $ca0[patcntl];
		$da1[payeraddr1] = $this->CleanChar($insco->local_record[inscoaddr1]);
		$da1[payeraddr2] = $this->CleanChar($insco->local_record[inscoaddr2]);
		$da1[payercity] = $this->CleanChar($insco->local_record[inscocity]);
		$da1[payerstate] = $this->CleanChar($insco->local_record[inscostate]);
		$da1[payerzip] = $this->CleanNumber($insco->local_record[inscozip]);

		if ($billto != PRIMARY) // if not billing the primary
		{
			// show amounts paid by primary
			$this->PrimaryPaidAmounts($procstack);
		}

	

		$da2[recid] = "DA2";
		$da2[seqno] = "01";
		$da2[patcntl] = $ca0[patcntl];
		//echo "da2 addr city state zip $addr1 $city $state $zip<BR>";
		$da2[insrdaddr1] = $addr1;
		$da2[insrdcity] = $city;
		$da2[insrdstate] = $state;
		$da2[insrdzip] = $zip;


		if ($da0[payerid] == "MHN")
			$da0[payerid] == "PAPER"; // They dont do edi either!!

		return;

		
	} // end PrimaryInfo


	function ClaimHeader($procstack)
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		
		global $ca0, $cb0;

		$row = $procstack[0]; // all rows are the same
		$cov = $row[proccurcovid];
		$pat = $row[procpatient];
		$doc = $row[procphysician];

		$ca0[recid] = "CA0";

		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			echo "Error in claimheader no coverage<BR>";
			return;
		}

		$physician = new Physician($doc);
		if (!$physician)
		{
			echo "Error in claimheader no physician<BR>";
			return;
		}

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

		$ca0[deathind] = "N";
		if ($patient->local_record[ptdead] > 0)
		{
			$ca0[deathind] = "Y";
			$ca0[patdtdead] = $this->CleanNumber($patient->local_record[ptdeaddt]);
		}

		$ca0[patlname] = $this->CleanChar($patient->local_record[ptlname]);
		$ca0[patfname] = $this->CleanChar($patient->local_record[ptfname]);
		$ca0[patdob] = $this->CleanNumber($patient->local_record[ptdob]);
		$ca0[patsex] = $this->CleanChar($patient->local_record[ptsex]);
		$ca0[pataddr1] = $this->CleanChar($patient->local_record[ptaddr1]);
		$ca0[pataddr2] = $this->CleanChar($patient->local_record[ptaddr2]);
		$ca0[patcity] = $this->CleanChar($patient->local_record[ptcity]);
		$ca0[patstate] = $this->CleanChar($patient->local_record[ptstate]);
		$ca0[patzip] = $this->CleanNumber($patient->local_record[ptzip]);
		$ca0[patphone] = $this->CleanNumber($patient->local_record[pthphone]);

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

		if ($patient->local_record[ptempl] == "y")
			$ca0[patempl] = "1"; //full time
		if ($patient->local_record[ptempl] == "p")
			$ca0[patempl] = "2";  // part time
		if ($patient->local_record[ptempl] == "n")
			$ca0[patempl] = "3";  // not employed
		if ($patient->local_record[ptempl] == "s")
			$ca0[patempl] = "4";  // self employed
		if ($patient->local_record[ptempl] == "r")
			$ca0[patempl] = "5";  // retired
		if ($patient->local_record[ptempl] == "m")
			$ca0[patempl] = "6";  // active military
		if ($patient->local_record[ptempl] == "u")
			$ca0[patempl] = "9";  // unknown

		$covtype = $coverage->local_record[covtype];
		$othrins = 3;  // no other coverage

		if ( ($covtype == PRIMARY) AND ($row[proccov.(SECONDARY)] != 0) )
				$othrins = "1"; // has other coverage in this bill
		
		if ( ($covtype == SECONDARY) AND ($row[proccov.(PRIMARY)] != 0) )
				$othrins = "1"; // has other coverage in this bill

		// NSF does not handle tertiary!

		$ca0[patothrins] = $othrins;


		//$buffer = "";		
   		//$buffer  = render_fixedRecord ($this->formno,$this->record_types["ca0"]);
		//if ($cb0[recid] == "CB0")  // only required for champus
   			//$buffer  .= render_fixedRecord ($this->formno,$this->record_types["cb0"]);
		//return $buffer;
		return;

	} // end claimheader

	function FileHeader()
    {

		//unset($GLOBALS[aa0]);
		global $aa0,$cur_date;
		$aa0[recid] = "AA0";
		$aa0[submtrid] = $this->CleanChar($this->userid);
		$aa0[subtype] = "U";

		$this->subno++;
		// this should be saved in the ch table. gotton when started.
        // incremented as used then saved when done.
		$aa0[subno] = $this->subno;  // once in 7 months!!!

		$aa0[createdt] = $this->CleanNumber($cur_date);
		$aa0[recvrid] = "MIXED";  // file contains claims for multiple payers
		$aa0[nsfverno] = "00301";
		$aa0[testprod] = "PROD";
		$aa0[password] = $this->CleanChar($this->password);
		$aa0[vendorid] = "FREMED";

		
		//$buffer = render_fixedRecord ($this->formno,$this->record_types["aa0"]);
		//return $buffer;
		return;

	} // end fileheader


	function ProviderHeader($procstack)
	{
		
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		//unset($GLOBALS[ba0]);
		//unset($GLOBALS[ba1]);
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

		$ba0[taxid] = "";
		if ($SESSION["default_facility"] != 0)
		{
			$fac = 0;
			$fac = freemed::get_link_rec(
				$SESSION["default_facility"],
				"facility"
			);
			if (!$fac)
				echo "ERROR - Failed getting default facility<BR>";
			$ba0[posname] = $this->CleanChar($fac[psrname]);
			$ba0[taxid] = $this->CleanNumber($fac[psrein]);
			$ba0[idtype] = "E";
			
		}

		if (empty($ba0[taxid]))
		{
			echo "Warning - No EIN using Providers SSN<BR>";
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


		//$provider_id = "0";
        //$grp = $insco->local_record[inscogroup];
        //if (!$grp)
        //{
        //    $name = $insco->local_record[insconame];
        //    echo "Failed getting inscogroup for $name<BR>";
        //}

        //$providerids = explode(":",$physician->local_record[phyidmap]);
        //$provider_id = $providerids[$grp];

		//$upin = $physician->local_record[phyupin];
		//$ba0[upin] = $this->CleanNumber($upin);
		//$ba0[ciid] = $this->CleanNumber($provider_id); // commercial provider id


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


		//$buffer = "";
   		//$buffer = render_fixedRecord ($this->formno,$this->record_types["ba0"]);
   		//$buffer .= render_fixedRecord ($this->formno,$this->record_types["ba1"]);
		//return $buffer;
		return; 
		
	}  // end provider


	function ClaimData($procstack)
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		global $ea0,$ea1,$ca0;
		
		$row = $procstack[0];

		$cov = $row[proccurcovid];

		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			echo "Error no coverage ClaimData<BR>";
			return;
		}
		$insco = $coverage->covinsco;
		if (!$insco)
		{
			echo "Error ClaimData no insurance<BR>";
			return;
		}

		$ea0[recid] = "EA0";
		$ea1[recid] = "XXX";

		$ea0[patcntl] = $ca0[patcntl];

		$ea0[relemp] = "N";
		$ea0[accident] = "N";
		$ea0[symptomind] = "0";
		$ea0[accsympdt] = " ";

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
			$eoc_row = freemed::get_link_rec($row[proceoc], "eoc");
        else
            echo "Warning - No EOC for this procedure $row[procdt]<BR>";

        if ($eoc_row)
        {
            $eoc_row = freemed::get_link_rec($row[proceoc], "eoc");
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

			if ($ea0[accident] == "A" OR 
				$ea0[accident] == "O" OR 
				$ea0[symtomind] == "1" OR
				$ea0[symtomind] == "2")
					$ea0[accsympdt] = $this->CleanNumber($accident_date);

			if ($eoc_row[eochospital] > 0)
			{
				$ea0[admitdt] = $this->CleanNumber($eoc_row[eochosadmdt]);
				$ea0[dischargdt] = $this->CleanNumber($eoc_row[eochosdischrgdt]);
			}
			if ($eoc_row[eocdistype] != 0)
				$ea0[distype] = $eoc_row[eocdistype];
			echo "distype ".$eoc_row[eocdistype]." nsf ".$eoc_row[eocdistype]."<BR>";

			if ( ($eoc_row[eocdistype] == "1") OR ($eoc_row[eocdistype] == "2") )
			{
				$ea0[disfromdt] = $this->CleanNumber($eoc_row[eocdisfromdt]);
				$ea0[distodt] = $this->CleanNumber($eoc_row[eocdistodt]);

			}
			if ($eoc_row[eocsimsympind] > 0)
			{
				$ea0[samesympind] = "Y";
				$ea0[samesympdt] = $this->CleanNumber($eoc_row[eocdtlastsimilar]);
			}


        }  // end got eoc rec


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
		$fac_row = freemed::get_link_rec($row[procpos], "facility");

        if ($fac_row)
        {
            // use code from facility
            if ($fac_row[psrpos] == 0)
            {
                echo "Facility does not have a pos code<BR>";
            }
            $cur_pos = freemed::get_link_rec($fac_row[psrpos], "pos");
            if (!$cur_pos)
                echo "Failed reading pos table";
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

		if ($ba0[prspec] == "31") // podiatry?
		{
			if (substr($row[procrefdt],0,1) != "0")
				$ea0[dtlastseen] = $this->CleanNumber($row[procrefdt]);
			else
				echo "Warning - Date last seen is zero for Podiatry bill<BR>";

		}

		if ($row[procatnddoc] > 0) // attending doc?
		{
			$ea1[recid] = "EA1";
			$ea1[patcntl] = $ca0[patcntl];

			$atnddoc = new Physician($row[procatnddoc]);
			if (!$atnddoc)
			echo "Error getting attending physician<BR>";
			$ea1[supvprovupin] = $this->CleanChar($atnddoc->local_record[phyupin]);
			$ea1[supvprovlnam] = $this->CleanChar($atnddoc->local_record[phylname]);
			$ea1[supvprovfnam] = $this->CleanChar($atnddoc->local_record[phyfname]);
		}

		return;

	}  // end claimdata

	function ServiceDetail($proc)
    {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		unset ($GLOBALS[fa0]);
		unset ($GLOBALS[fb1]);
		global $fa0,$fb1,$ca0,$ba0;

		$row = $proc;
		$doc = $row[procphysician];
		$cov = $row[proccurcovid];

		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			echo "Error ServiceDetail no coverage<BR>";
			return;
		}

		$insco = $coverage->covinsco;
		if (!$insco)
		{
			echo "Error ServiceDetail no insurance<BR>";
			return;
		}

		$physician = new Physician($doc);
		if (!$physician)
		{
			echo "Error in ServiceDetail no physician<BR>";
		}

		$grp = $insco->local_record[inscogroup];
		if (!$grp)
		{
			$name = $insco->local_record[insconame];
			echo "ERROR  - Failed getting Insurance Group in ServiceDetail!<BR>";
		}

		$provider_id = $this->GetProviderGroupID($physician->local_record[phygrpprac],$grp);
		if ($provider_id != "0")
		{
			// if group practice then get the rendering providerid
			$provider_id = $this->GetProviderID($physician->local_record[phyidmap],$grp);
			if ($provider_id=="0")
				echo "ERROR - No Rendering Provider ID in ServiceDetail!<BR>";
		}


		$payerid = $this->CleanNumber($insco->local_record[inscoid]);

		$diagset = new diagnosisSet();

		$fa0[recid] = "FA0";
		$fa0[patcntl] = $ca0[patcntl];
		$fa0[startdt] = $this->CleanNumber($row[procdt]);
		$fa0[enddt] = $this->CleanNumber($row[procdt]);

		if ($provider_id != "0")
			$fa0[rendprid] = $this->CleanNumber($provider_id);

	
		if ($row[proccptmod] != 0)
		{
			$itemcptmod  = freemed::get_link_field ($row[proccptmod], "cptmod", "cptmod");
			if (!$itemcptmod)
				echo "ERROR - Failed reading cptmod table<BR>";
			$fa0[cptmod1] = $itemcptmod;
		}
		if ($row[proccptmod2] != 0)
		{
			$itemcptmod  = freemed::get_link_field ($row[proccptmod2], "cptmod", "cptmod");
			if (!$itemcptmod)
				echo "ERROR - Failed reading cptmod table<BR>";
			$fa0[cptmod2] = $itemcptmod;
		}

		$cur_cpt = freemed::get_link_rec ($row[proccpt], "cpt");
		if (!$cur_cpt)
			echo "ERROR - Failed reading cpt table<BR>";
		$cur_insco = $insco->local_record[id];
		//echo "insco $cur_insco<BR>";
		$tos_stack = fm_split_into_array ($cur_cpt[cpttos]);
		$tosid = ( ($tos_stack[$cur_insco] < 1) ?
				  $cur_cpt[cptdeftos] :
				  $tos_stack[$cur_insco] );
		// tos prefix used by champus
		//echo "cpt prefix $cur_cpt[cpttosprfx]";
		$tosprfx_stack = fm_split_into_array ($cur_cpt[cpttosprfx]);
		$tosprfxid = ( ($tosprfx_stack[$cur_insco] < 1) ?
				  "0" :
				  $tosprfx_stack[$cur_insco] );

		if ($tosid == 0)
		{
			echo "ERROR - No default type of service for this proc $row[procdt]<BR>";
			$tos = "XX";
		}
		else
		{
			$cur_tos = freemed::get_link_rec($tosid, "tos");
			if (!$cur_tos)
				echo "ERROR - Failed reading TOS table<BR>";
			$tos = $cur_tos[tosname];
		}

		if ($payerid=="REG06") // champus REG06 wanst wird tos codes
		{
			if ($tosprfxid == 0)
			{
				echo "ERROR - No REG06 TOS Prefix for this proc $row[procdt]<BR>";
				$tos = "XX";
			}
			else
			{
				//echo "prfxid $tosprfxid<BR>";
				$cur_tos = freemed::get_link_rec($tosprfxid, "tos");
				if (!$cur_tos)
					echo "ERROR - Failed REG06 reading prefix tos table<BR>";
				$tosprfx = $cur_tos[tosname];
			}
			// make champus tos
			$tos = $tosprfx.$tos;			
			if (strlen($tos) > 3)
			{
				echo "ERROR - Invalid REG06 TOS proc $row[procdt]<BR>";
				$tos = "XX";
			}
		}
	
		if ( (strlen($tos) < 2)	AND ($tos > 0) AND ($tos < 10) )
			$tos = "0".$tos;

		$fa0[tos] = $tos;
	
		$cur_cpt = freemed::get_link_rec ($row[proccpt], "cpt");
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

		return;

	} // end servicedetail


	function ClaimTrailer($procstack,$buffer)
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		//unset($GLOBALS[xa0]);
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
		$xa0[clmreccount] = $cxx+$dxx+$exx+$fxx+$gxx+$hxx;

		$count = count($procstack);

		if ($count == 0)
		{
			echo "Error no procedures in ClaimTrailer<BR?";
			return;
		}

		$total = 0;
		// see if patient already paid anything
        $total_paid_bypatient = 00.00;

		for ($i=0;$i<$count;$i++)
		{
			$row = $procstack[$i];
			$total += $row[procbalorig];
/*
        	$query = "SELECT * FROM payrec WHERE payrecproc='$row[id]' AND
                                            payrecsource='0' AND
                                            payreccat='".PAYMENT."'";
        	$pay_result = $sql->query($query) or DIE("Query failed for patient payments");
        	while ($pay_row = $sql->fetch_array($pay_result))
        	{
            	$total_paid_bypatient += $pay_row[payrecamt];
        	}
*/
			
		}
		$data = $this->MakeDecimal($total,2);
		$xa0[totalcharge] = $data;
		//$data = $this->MakeDecimal($total_paid_bypatient,2);
		$data = $this->GetPatientPaid($procstack);
		$xa0[patamtpd] = $data;
		
   		//$buffer = render_fixedRecord ($this->formno,$this->record_types["xa0"]);
		//return $buffer;
		return;

	} // end claimtrailer


	function ProviderTrailer($procstack, $buffer)
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		reset ($this->rendorform_variables);
		while (list($k,$v)=each($this->rendorform_variables)) global $$v;

		//unset($GLOBALS[ya0]);
		//unset($GLOBALS[za0]);
		global $ya0;
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
		//$tot++;  // account for this ya0 record
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


   		//$buffer = render_fixedRecord ($this->formno,$this->record_types["ya0"]);
		//return $buffer;
		return;

	} // end provider trailer


	function FileTrailer($buffer)
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		reset ($this->rendorform_variables);
		while (list($k,$v)=each($this->rendorform_variables)) global $$v;

		//unset($GLOBALS[za0]);
		global $za0;

		$za0[recid] = "ZA0";
		$za0[subid] = $aa0[submtrid];
		$za0[recvrid] = $aa0[recvrid];

		$za0[filesvclinecnt] = $this->svclinecnt;
		$za0[filereccnt] = $this->batchreccnt;
		$za0[fileclmcnt] = $this->batchclmcnt;
		$za0[batchcnt] = $this->batchcnt;
		$za0[filetotchg] = $this->batchtotchg;

		$this->svclinecnt=0;
		$this->batchreccnt=0;
		$this->batchclmcnt=0;
		$this->batchcnt=0;
		$this->batchtotchg=0;

   		$zabuffer = render_fixedRecord ($this->formno,$this->record_types["za0"]);
		$new_buffer = $buffer.$zabuffer;
		return $new_buffer;


	} // end file trailer

	// convert int to float
	// then strip out the "."
	function MakeDecimal($data,$places)
    {
        $data = bcadd($data,0,$places);
        $data = $this->CleanNumber($data);
        return $data;
    }

	// all reporting data must stipped of junk
    // and all upper cased
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


    // all reporting data must stipped of junk
    // and all upper cased then for number all blanks
    // removed
    function CleanNumber($data)
    {

        $data = $this->CleanChar($data);
        $data = str_replace(" ","",$data);
        $data = trim($data);
        return $data;

    } // end cleannumber

	
	function GetRelationShip($rel,$type="NSF")
	{
		if ($type=="NSF")
		{
			if ($rel == "S")
				$patrel = "01";
			if ($rel == "LR")  // medicare legal rep
				$patrel = "01";
			if ($rel == "H" OR $rel == "W")
				$patrel = "02";
			if ($rel == "C")
				$patrel = "03";
			if ($rel == "D") // Natural Child insured not financially resp.
				$patrel = "04";
			if ($rel == "SC") // Step child
				$patrel = "05";
			if ($rel == "FC") // Foster child
				$patrel = "06";
			if ($rel == "WC") // Ward of Court
				$patrel = "07";
			if ($rel == "HD") // Handicapped Dependent
				$patrel = "10";
			if ($rel == "SD") // Sponsered Dependent
				$patrel = "16";

			return $patrel;

		}


	} // end getrelationship

	// allow derived classes to use us for rendering records
	// the child does not need render_forms inc.

	function RenderFixedRecord($whichform,$rectype)
	{

		return render_fixedRecord ($whichform,$rectype); 


	} // end RenderFixedRecord

	function PrimaryPaidAmounts($procstack)
	{
		global $sql,$da1;

		$count = count($procstack);

		$payerpaid = 0;
		$patpaid = 0;
		$total = 0;

		for ($i=0;$i<$count;$i++)
		{
			$row = $procstack[$i];
			$total += $row[procbalorig];

			$query = "SELECT * FROM payrec WHERE payrecproc='".$row[id]."' AND
                                            payrecsource='".PRIMARY."' AND
											payreclink='".$row[proccov1]."' AND
                                            payreccat='".PAYMENT."'";
            $pay_result = $sql->query($query) or DIE("Query failed for primary payments");
            while ($pay_row = $sql->fetch_array($pay_result))
            {
                $payerpaid += $pay_row[payrecamt];
            }

        	$query = "SELECT * FROM payrec WHERE payrecproc='".$row[id]."' AND
                                            payrecsource='".PATIENT."' AND
                                            payreccat='".PAYMENT."'";
        	$pay_result = $sql->query($query) or DIE("Query failed for patient payments");
        	while ($pay_row = $sql->fetch_array($pay_result))
        	{
            	$patpaid += $pay_row[payrecamt];
        	}
		}	
		$da1[payeramtpd] = $this->MakeDecimal($payerpaid,2);

		$baldue = $total - $patpaid; 
		$da1[baldue] = $this->MakeDecimal($baldue,2);
	
		if ($payerpaid == 0)
			$da1[zeropayind] = "Z";  
		else
			$da1[zeropayind] = "N";
		return;

	} // end PrimaryPaidAmounts

	function VerifyAuth($procstack)
	{
		$row = $procstack[0];
		$auth_num="0";

		if ($row[procauth] != 0)
        {
            $auth_row = freemed::get_link_rec($row[procauth],"authorizations");
            if (!$auth_row)
                echo "Failed to read procauth";
			$auth_num = $auth_row[authnum];
            if ( (empty($auth_num)) )
            {
                echo "ERROR - Authorization number Invalid<BR>";
                return "0";
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
					echo "Warning: Authorization $auth_num has expired for procedure $procdt<BR>";
				}
				if ($auth_row[authvisitsremain] == 0)
				{
					echo "Warning: No Remaining visits for Authorization $auth_num procedure $procdt<BR>";
				}	

			}
        }
        else
        {
            echo "Warning - No Authorization for this procedure<BR>";
        }
		return $auth_num;

	} // end VerifyAuth

	function GetClaimSource($insco)
	{
		$insmodrec=0;
		$insmodrec = freemed::get_link_rec($insco->modifiers[0],"insmod");
		if (!$insmodrec)
			DIE("Failed getting insurance modifier");
		$insmod = $insmodrec[insmod];
		//echo "insmod $insmod inscomodifier ".$insco->modifiers[0]."<BR>";

		$clmsource="";
		if ($insmod == "WC")
			$clmsource = "B";
		if ($insmod == "CI")
			$clmsource = "F";
		if ($insmod == "BL")
			$clmsource = "G";
		if ($insmod == "HM")
			$clmsource = "I";
		if ($insmod == "FI")
			$clmsource = "J";
		if ($insmod == "CH")
			$clmsource = "H";
		if ($insmod == "MB")
			$clmsource = "C";
		//echo "clmsource $clmsource<BR>";
		return $clmsource;


	}

	function GetProviderID($phymap,$insgrp)
	{

		$provider_id="0";

        // assume it's not a group bill
        $providerids = explode(":",$phymap);
		$prid = $providerids[$insgrp];
		if (!empty($prid))
        	$provider_id = $prid;

		return $provider_id;

	}

	function GetProviderGroupID($phygrp,$insgrp)
	{

		$provider_id="0";


		if ($phygrp > 0)
		{
			// we can have a group but necessarily an ID for this insurance
			// So. if we have a group defined (phygrpprac) AND a groupid exists for this group
			// then use this groupid for the bill. FA0-23 will use the *rendering* provider id
			$phygroup_rec = freemed::get_link_rec($phygrp,"phygroup");
			if (!$phygroup_rec)
				echo "ERROR - Failed getting group rec in GetProviderGroupID<BR>";
			$provider_group_ids = explode(":",$phygroup_rec[phygroupidmap]);
			$provider_group_id = $provider_group_ids[$insgrp];
			if (!empty($provider_group_id))
				$provider_id = $provider_group_id;
		}
		return $provider_id;

	}

	function GetPatientPaid($procstack)
	{
		global $sql;

		$total_paid_bypatient = 00.00;
		$count = count($procstack);
		for ($i=0;$i<$count;$i++)
		{
			$row = $procstack[$i];

			$query = "SELECT * FROM payrec WHERE payrecproc='$row[id]' AND
											payrecsource='0' AND
											payreccat='".PAYMENT."'";
			$pay_result = $sql->query($query) or DIE("Query failed for patient payments");
			while ($pay_row = $sql->fetch_array($pay_result))
			{
				$total_paid_bypatient += $pay_row[payrecamt];
			}
		}

		$data = $this->MakeDecimal($total_paid_bypatient,2);
		return $data;


	}



} // end NSF class


} // End define NSF_PHP

?>
