<?php
 // $Id$
 // desc: base class used by various nsf generators
 // lic : GPL, v2

include "lib/class.nsf.php";

if (!defined ("__NSFMC_PHP")) {

define ('__NSFMC_PHP', true);

class NSFMC extends NSF {

	function NSFMC() {
		return;
	}

	function NSF_Setup($user,$pw,$formno,$insmod) {
		NSF::NSF_Setup($user,$pw,$formno,$insmod);
		return;
	}

	function Insurer($procstack)
    {
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


	function SecondaryInfo($procstack)
    {
		
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


		// if billing medicare as the Secondary
		if ( ($da0[clmfileind]=="P") AND ($da0[clmsource]=="C") )
		{
			if (empty($da0[instypcd]))
				echo "ERROR - Insurance type required for Medicare SecondaryInfo<BR>";

			// Medicare is Secondary 
			$buffer = "";
			$buffer  = $this->RenderFixedRecord ($this->formno,$this->record_types["da0"]);

			if ($da0[payerid] == "PAPER")
			{
				$buffer  .= $this->RenderFixedRecord ($this->formno,$this->record_types["da1"]);
			}
			if ($da0[patrel] != "01") // Guarantor present
				$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["da2"]);
			return $buffer;
		}

		if ($da0[clmfileind]=="P")
		{
			echo "ERROR - Not sure about Billing other coverage when medicare is primary<BR>";
			return "";
		}

		// Billing Medicare as Primary with something as the secondary
		// the secondary should be Informational clamsource=I

		if ($da0[instypcd] == "MG")  // secondary is MG
			$da0[clmsource] = "Z";

		if ($da0[clmsource] != "Z") // not Medigap
		{
			if (empty($da0[instypcd]))  // and instype not specified
			{
				$da0[instypcd] = "OT";  // default to other
				echo "Warning - Medicare - No Coverage type for SecondaryInfo defaulting to OT<BR>";
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
		// if billing Medicare as Primary
		if ( ($da0[clmfileind]=="P") AND ($da0[clmsource]=="C") )
			$da0[instypcd] = "MP";

		if ($da0[instypcd] == "MP") 
		{
			// Medicare is Primary 
			$buffer = "";
			$buffer  = $this->RenderFixedRecord ($this->formno,$this->record_types["da0"]);

			if ($da0[payerid] == "PAPER")
			{
				$buffer  .= $this->RenderFixedRecord ($this->formno,$this->record_types["da1"]);
			}
			if ($da0[patrel] != "01") // Guarantor present
				$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["da2"]);
			return $buffer;

		}

		$covtpid = $coverage->local_record[covinstp];
		if ($covtpid > 0)
		{
			$covtype = freemed::get_link_rec($covtpid,"covtypes");
			$da0[instypcd] = $covtype[covtpname];
		}
		else
		{
			echo "ERROR - No Insurance type for Medicare Secondary in PrimaryInfo<BR>";
		
		}
			
		if (empty($da0[instypcd]))
			echo "ERROR - Insurance type required for Medicare PrimaryInfo<BR>";

		$da0[patgrpname] = "";
		$da0[patgrpname] = $coverage->local_record[covplanname];

		if (empty($da0[patgrpname]))
			echo "ERROR - Coverage Plan Name required for Medicare PrimaryInfo<BR>";

		$buffer = "";
		$buffer  = $this->RenderFixedRecord ($this->formno,$this->record_types["da0"]);
		$buffer  .= $this->RenderFixedRecord ($this->formno,$this->record_types["da1"]);
		$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["da2"]);
		return $buffer;

	} // end PrimaryInfo

	function ClaimHeader($procstack)
    {
		unset($GLOBALS[ca0]);
		unset($GLOBALS[cb0]);

		NSF::ClaimHeader($procstack);

		global $ca0,$cb0,$ba0;

		$row = $procstack[0]; // all rows are the same
		$cov = $row[proccurcovid];
		$pat = $row[procpatient];
		$doc = $row[procphysician];
		$cb0[recid] = "XXX";

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

		$ca0[clmeditind] = "C"; // Medicare filing
		$ca0[clmtype] = " ";


		$datediff = date_diff($patient->local_record[ptdob]);
		$yrdiff = $datediff[0];

		if ($yrdiff < 18)
			$ca0[legalrepind] = "Y";
		else
			$ca0[legalrepind] = "N";

		$ca0[patstudent] = "N";
		if ($patient->local_record[ptstatus] != 0)
		{
			// look up the status record.
			$status = freemed::get_link_field($patient->local_record[ptstatus],"ptstatus","ptstatus");
			if (!$status)
				echo "Error failed to get ptstatus<BR>";
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
				//echo "year diff $yrdiff<BR>";
			}

		}
		
		//CB0 is required for underaged patient;

		if ($ca0[legalrepind] == "Y")
		{
			// we assume the guarantor is the responsible party
			if ($coverage->covdep != 0)
			{
				$guar = new Guarantor($coverage->covdep);
				if (!$guar)
					echo "ERROR - Guarantor Failed in CB0 record<BR>";

				$cb0[recid] = "CB0";
				$cb0[patcntl] = $ca0[patcntl];
				$cb0[respfname] = $this->CleanChar($guar->guarfname);
				$cb0[resplname] = $this->CleanChar($guar->guarlname);
				$cb0[respaddr1] = $this->CleanChar($guar->guaraddr1);
				$cb0[respaddr2] = $this->CleanChar($guar->guaraddr2);
				$cb0[respcity] = $this->CleanChar($guar->guarcity);
				$cb0[respstate] = $this->CleanChar($guar->guarstate);
				$cb0[respzip] = $this->CleanNumber($guar->guarzip);

			}
			else
			{
				echo "ERROR - Medicare CB0 record for Procedure $row[procdt]<BR>";
				echo "ERROR - Under aged patient does not have Guarantor<BR>";
			}
		}



		$buffer = "";
		$buffer  = $this->RenderFixedRecord ($this->formno,$this->record_types["ca0"]);
		if ($cb0[recid] == "CB0")  // only required for champus
			$buffer  .= $this->RenderFixedRecord ($this->formno,$this->record_types["cb0"]);

		return $buffer;



	} // end claim header

	function FileHeader()
    {
		unset($GLOBALS[aa0]);
		NSF::FileHeader();

		global $aa0;
		$aa0[recvrtype] = "C"; // medicare
		$aa0[nsfvernoloc] = "00301";

		$buffer  = $this->RenderFixedRecord ($this->formno,$this->record_types["aa0"]);
		return $buffer;

	} // end fileheader


	function ProviderHeader($procstack)
    {
		unset($GLOBALS[ba0]);
		unset($GLOBALS[ba1]);
		NSF::ProviderHeader($procstack);

		global $ba0,$ba1;
		$row = $procstack[0]; // all rows are the same
		$doc = $row[procphysician];
		$cov = $row[proccurcovid];
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
        $ba0[mcareid] = $this->CleanNumber($provider_id);
        $ba0[emcid] = $this->CleanNumber($provider_id);

		$state = $this->CleanChar($physician->local_record[phystatea]);
		if ($state == "PA")
			$ba0[prodnaic] = "M";
		if ($state == "NJ")
			$ba0[prodnaic] = "J";


		$buffer = "";
		$buffer = $this->RenderFixedRecord ($this->formno,$this->record_types["ba0"]);
		$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["ba1"]);
		return $buffer;


	} // end providerheader

	function ClaimData($procstack)
    {
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


	function ServiceDetail($procstack)
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		//unset ($GLOBALS[fa0]);
		//unset ($GLOBALS[fb1]);
		//global $fa0,$fb1,$ca0;
		global $ba0,$ca0;

		$row = $procstack[0];
		$doc = $row[procphysician];
		$cov = $row[proccurcovid];

/*
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
		$payerid = $this->CleanNumber($insco->local_record[inscoid]);
*/


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

/*
			commented out just to save the code but not run it till
			it is implemented in the base class.
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
*/
		} // end for each proc in procstack

		return $buffer;

	} // end servicedetail


	function GenCertRecords($proc,$seq)
	{
		// these records are required for various type of certification.
		// currently only DMEPOS is supported  See the medicare DMERC spec.
		unset($GLOBALS[fb1]);
		unset($GLOBALS[gu0]);

		global $ba0,$ca0,$ea0,$fa0,$gu0,$fb1,$whichform;

		//echo "form $whichform in cert<BR>";
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
			echo "Error getting cert<BR>";
			return;
		}
		
		$gu0[recid] = "GU0";
		$gu0[seqno] = $seq;
		$gu0[patcntl] = $ca0[patcntl];


		if ($certrow[certtype] == DMEPOS)
		{
			$fa0[rendprid] = $ba0[mcareid];
			//echo "fa0 rend $fa0[rendprid]<BR>";
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



	function ClaimTrailer($procstack,$buffer)
    {
		unset($GLOBALS[xa0]);
		NSF::ClaimTrailer($procstack,$buffer);
		global $xa0;
   		$buffer = $this->RenderFixedRecord ($this->formno,$this->record_types["xa0"]);
		return $buffer;


	} // end claimtrailer

	function ProviderTrailer($procstack, $buffer)
    {
		unset($GLOBALS[ya0]);
		NSF::ProviderTrailer($procstack, $buffer);	
		global $ya0;
   		$buffer = $this->RenderFixedRecord ($this->formno,$this->record_types["ya0"]);
		return $buffer;


    } // end provider trailer


	function FileTrailer($buffer)
    {
		unset($GLOBALS[za0]);
		NSF::FileTrailer($buffer);
		global $za0;
   		$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["za0"]);
		return $buffer;
    } // end file trailer




} // end NSF class


} // End define NSF_PHP

?>
