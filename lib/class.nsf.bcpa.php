<?php
 // $Id$
 // desc: base class used by various nsf generators
 // lic : GPL, v2

include_once("lib/class.nsf.php");

if (!defined ("__NSFBCPA_PHP")) {

define ('__NSFBCPA_PHP', true);

class NSFBCPA extends NSF {
	// 95056 Keystone HP East
	// 95199 Keystone HP Central
	// 54771 Highmark Claim/Encounters

	var $naic_batchid = array("54720","95056", "95199");
	var $naic_encounters = array("54771");
	var $naic_tier1 = array(
		"93688",	//Amerihealth Delaware
		"60061",	//  "         NJ
		"54763",	//  "         Admin (Blair Mill)
		"54720",	//Capitol Blue Cross HealthOne
		"53252",	//Inter-County HP
		"95199",	// KHPC
		"95056",	// KHPE
		"54771",	// Highmark PA BS
		"54704"		// IBC Personal Choice
		);

	function NSFBCPA() {
		return;
	}

	function NSF_Setup($user,$pw,$formno,$insmod) {
		NSF::NSF_Setup($user,$pw,$formno,$insmod);
		return;
	}

	function Insurer($procstack) {
		$row = $procstack[0];
		
		// cov types should be the same for entire stack
        $bill_request_type = $row[proccurcovtp]; 

        if ($bill_request_type == PRIMARY)
		{
            $buffer = $this->PrimaryInfo($procstack);
			if ($row[proccov2] > 0)
            	$buffer .= $this->SecondaryInfo($procstack);
		}

        if ($bill_request_type == SECONDARY)
		{
            $buffer = $this->PrimaryInfo($procstack);
            $buffer .= $this->SecondaryInfo($procstack);
		}
        return $buffer;

	}


	function SecondaryInfo($procstack) {
		
		unset($GLOBALS[da0]);
		unset($GLOBALS[da1]);
		unset($GLOBALS[da2]);

		NSF::SecondaryInfo($procstack);

		global $da1,$da0,$da2;

		$row = $procstack[0];
		$cov = $row[proccov2];
		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			echo "ERROR- No coverage in Secondary<BR>";
			return;
		}

		$da0[instypcd]="";
		$covtpid = $coverage->local_record[covinstp];
		if ($covtpid > 0)
		{
			$covtype = freemed::get_link_rec($covtpid,"covtypes");
			$da0[instypcd] = $covtype[covtpname];
		}


		// if billing BC as the Secondary
		if ( ($da0[clmfileind]=="P") AND ($da0[clmsource]=="G") )
		{
			echo "ERROR - BC Secondary not supported at this time<BR>";
			return;	
			// need 2 fields in the coverage record
			//  par with coverage
			//  benefits exhausted.
			if (empty($da0[instypcd]))
				echo "Warning - Insurance type may be required for BC SecondaryInfo<BR>";
			$hit = fm_value_in_array($this->naic_tier1,$da0[payerid]);
			if (!$hit)
			{
				$patid="";
				$patid = str_pad($patid,3," "); // 3 space prefix
				$patid .= $da0[patidno];
				$da0[patidno] = $patid;
			}
			if (empty($da0[patgrpno]))
				$da0[patgrpno] = "999999";

			// BC is Secondary 
			$buffer = "";
			$buffer  = $this->RenderFixedRecord ($this->formno,$this->record_types["da0"]);
			$buffer  .= $this->RenderFixedRecord ($this->formno,$this->record_types["da1"]);
			$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["da2"]);
			return $buffer;
		}

		if ($da0[clmfileind]=="P")
		{
			echo "ERROR - Not sure about Billing other coverage when BC is primary<BR>";
			return "";
		}

		// Billing BC as Primary with something as the secondary
		// the secondary should be Informational claimsource=I

		if ($da0[instypcd] == "MG")  // secondary is MG
			$da0[clmsource] = "Z";

		if ($da0[clmsource] != "Z") // not Medigap
		{
			if (empty($da0[instypcd]))  // and instype not specified
			{
				$da0[instypcd] = "OT";  // default to other
				echo "Warning - BC No Coverage type for SecondaryInfo defaulting to OT<BR>";
			}
		}

		$buffer = "";

		if ($da0[payerid] == "PAPER")
			$da0[payerid] = "99999";

		$buffer  = $this->RenderFixedRecord ($this->formno,$this->record_types["da0"]);

		if ($da0[payerid] == "99999")
		{
			$buffer  .= $this->RenderFixedRecord ($this->formno,$this->record_types["da1"]);
		}
		$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["da2"]);
		return $buffer;
	}


	function PrimaryInfo($procstack)
	{
		
		unset($GLOBALS[da0]);
		unset($GLOBALS[da1]);
		unset($GLOBALS[da2]);

		NSF::PrimaryInfo($procstack);

		global $da1,$da0,$da2;

		$row = $procstack[0];
		$cov = $row[proccov1];

		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			echo "ERROR- No coverage in Medicare Primary<BR>";
			return;
		}

		$da0[instypcd]="";

		// if billing BC as Primary
		if ( ($da0[clmfileind]=="P") AND ($da0[clmsource]=="G") )
		{
			$hit = fm_value_in_array($this->naic_tier1,$da0[payerid]);
			if (!$hit)
			{
				$patid="";
				$patid = str_pad($patid,3," "); // 3 space prefix
				$patid .= $da0[patidno];
				$da0[patidno] = $patid;
			}
			if (empty($da0[patgrpno]))
				$da0[patgrpno] = "999999";
			// BC is Primary 
			$buffer = "";
			$buffer  = $this->RenderFixedRecord ($this->formno,$this->record_types["da0"]);
			$buffer  .= $this->RenderFixedRecord ($this->formno,$this->record_types["da1"]);
			$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["da2"]);
			return $buffer;

		}

		$covtpid = $coverage->local_record[covinstp];
		if ($covtpid > 0) {
			$covtype = freemed::get_link_rec($covtpid,"covtypes");
			$da0[instypcd] = $covtype[covtpname];
		} else {
			echo "ERROR - No Insurance type for PrimaryInfo<BR>";
		}
			
		if (empty($da0[instypcd]))
			echo "ERROR - Insurance type required PrimaryInfo<BR>";

		$da0[patgrpname] = "";
		$da0[patgrpname] = $coverage->local_record[covplanname];

		if (empty($da0[patgrpname]))
			echo "ERROR - Coverage Plan Name required for PrimaryInfo<BR>";

		$buffer = "";
		$buffer  = $this->RenderFixedRecord ($this->formno,$this->record_types["da0"]);
		$buffer  .= $this->RenderFixedRecord ($this->formno,$this->record_types["da1"]);
		$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["da2"]);
		return $buffer;

	} // end PrimaryInfo

	function ClaimHeader($procstack) {
		unset($GLOBALS[ca0]);
		unset($GLOBALS[cb0]);

		NSF::ClaimHeader($procstack);

		global $ca0;

		$row = $procstack[0];
		$cov = $row[proccurcovid];
		$pat = $row[procpatient];	

		$patient = new Patient($pat);
		if (!$patient)
		{
			echo "Error in claimheader no patient<BR>";
			return;
		}

		$coverage = new Coverage($cov);
		if (!$coverage)
		{
			echo "Error. patient coverage invalid ClaimHeader<BR>";
			return;
		}


		$ca0[patstudent] = "N";
		if ( ($patient->local_record[ptstatus] != 0) AND ($coverage->covdep != 0) )
		{
			//patient has student status and not the insured
			$status = freemed::get_link_field($patient->local_record[ptstatus],"ptstatus","ptstatus");
			if (!$status)
				echo "ERROR - Failed to get ptstatus ClaimHeader<BR>";

			$datediff = date_diff($patient->local_record[ptdob]);
			$yrdiff = $datediff[0];

			if ($yrdiff > 18)
			{
				if ($status == "HCF")
					$ca0[patstudent] = "F";
				if ($status == "HCP")
					$ca0[patstudent] = "P";
			}
		}


		// see if patient already paid anything
		$paid = $this->GetPatientPaid($procstack);

		if ($paid > 0)
			$ca0[patpdind] = "Y";
		else
			$ca0[patpdind] = "N";


		$buffer  = $this->RenderFixedRecord ($this->formno,$this->record_types["ca0"]);

		return $buffer;



	} // end claim header

	function FileHeader() {
		unset($GLOBALS[aa0]);
		NSF::FileHeader();

		global $aa0;
		$aa0[recvrtype] = "G"; // BC
		$aa0[nsfvernoloc] = "00301";

		$buffer  = $this->RenderFixedRecord ($this->formno,$this->record_types["aa0"]);
		return $buffer;

	} // end fileheader


	function ProviderHeader($procstack) {
		unset($GLOBALS[ba0]);
		unset($GLOBALS[ba1]);
		NSF::ProviderHeader($procstack);

		global $ba0,$ba1;
		$row = $procstack[0]; // all rows are the same
		$doc = $row[procphysician];
		$cov = $row[proccurcovid];
		$physician = new Physician($doc);
		if (!$physician) {
			echo "Error no physician<BR>";
			return;
		}
		$coverage = new Coverage($cov);
		if (!$coverage) {
			echo "Error no coverage<BR>";
			return;
		}
		$insco = $coverage->covinsco;
		if (!$insco) {
			echo "Error no insco<BR>";
			return;
		}

		$grp = $insco->local_record[inscogroup];
		if (!$grp)
		{
			$name = $insco->local_record[insconame];
			echo "ERROR Failed getting inscogroup for $name<BR>";
		}

		$provider_id = $this->GetProviderGroupID($physician->local_record[phygrpprac],$grp);
		if ($provider_id=="0")  // not group bill
		{
			$provider_id = $this->GetProviderID($physician->local_record[phyidmap],$grp);
			if ($provider_id=="0")
				echo "ERROR - Providerid INVALID ProviderHeader<BR>";
		}

		$provider_id = str_pad($provider_id,10,"0",STR_PAD_LEFT); // fill zeros to left
        $ba0[bsid] = $this->CleanNumber($provider_id);
        $ba0[emcid] = $this->CleanNumber($provider_id);
		$ba0[provno] = $ba0[emcid];

		$naic = $insco->inscoid;
		$naic = strtoupper($naic);

		// some naic for bc pa diferrentiate
		// a claim from an encounter
		if (fm_value_in_array($this->naic_batchid,$naic)) {
			// if we have an encounter use 300
			// else use 310
			// since im not sure how to tell if we have
			// an encounter im assuming all claims maybe a procedure code
			// can tell.
			//$ba0[batchid] = "310";
			echo "ERROR - Encounters not supported<BR>";
		}
		$tmpnaic = "";
		if ($naic=="54771") { // highmark
			//look up the coverage type in the coverage record
			// here we would differentiate between vision
			// claims and medical claims.
			// Im assuming a medical claim
			// we could use the specialty code here to check for 57 optometry
			//$tmpnaic = "MS ";
			echo "ERROR - This NAIC $naic Needs Vision or Medical Values<BR>";
		}
		$tmpnaic .= $naic;
		$ba0[prodnaic] = $tmpnaic;

        $ba1[emcid] = $this->CleanNumber($provider_id);


		$buffer = "";
		$buffer = $this->RenderFixedRecord ($this->formno,$this->record_types["ba0"]);
		$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["ba1"]);
		return $buffer;


	} // end providerheader

	function ClaimData($procstack) {
		unset($GLOBALS[ea0]);
		unset($GLOBALS[ea1]);
		NSF::ClaimData($procstack);

		global $ea0,$ea1;

		$ea0[provsigind] = "Y";  // sig on file


		$buffer = "";
		$buffer = $this->RenderFixedRecord ($this->formno,$this->record_types["ea0"]);
		if ($ea1[recid] != "XXX")
			$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["ea1"]);
		return $buffer;


	} // end Claimdata


	function ServiceDetail($procstack) {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		global $ba0,$ca0;

		$row = $procstack[0];
		$doc = $row[procphysician];
		$cov = $row[proccurcovid];

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
        if ($pos == 0)
        {
            // plug with Office code
            $pos="11";
            echo "Warning: Plugged pos with Office Code 11<BR>";
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

			NSF::ServiceDetail($row);  // call base class for each row in stack
			global $fa0;

			$fa0[seqno] = $seq;
			$fa0[pos]   = $pos;
		
   			$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["fa0"]);

		} // end for each proc in procstack

		return $buffer;

	} // end servicedetail





	function ClaimTrailer($procstack,$buffer) {
		unset($GLOBALS[xa0]);
		NSF::ClaimTrailer($procstack,$buffer);
		global $xa0;

   		$buffer = $this->RenderFixedRecord ($this->formno,$this->record_types["xa0"]);
		return $buffer;


	} // end claimtrailer

	function ProviderTrailer($procstack, $buffer) {
		unset($GLOBALS[ya0]);
		NSF::ProviderTrailer($procstack, $buffer);	
		global $ya0;
   		$buffer = $this->RenderFixedRecord ($this->formno,$this->record_types["ya0"]);
		return $buffer;


	} // end provider trailer


	function FileTrailer($buffer) {
		unset($GLOBALS[za0]);
		NSF::FileTrailer($buffer);
		global $za0;
   		$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["za0"]);
		return $buffer;
	} // end file trailer

} // end NSF class

} // End define NSF_PHP

?>
