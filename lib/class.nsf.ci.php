<?php
 // $Id$
 // desc: base class used by various nsf generators
 // lic : GPL, v2

include "lib/class.nsf.php";

if (!defined ("__NSFCI_PHP")) {

define ('__NSFCI_PHP', true);

class NSFCI extends NSF
{

	function NSFCI()
	{
		return;
	}

	function NSF_Setup($user,$pw,$formno,$insmod)
	{
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
		$coverage = CreateObject('FreeMED.Coverage', $cov);
		if (!$coverage)
		{
			echo "ERROR- No coverage in Secondary<BR>";
			return;
		}

		if ($da0[clmsource] == "H")  // champus
		{
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
			else
			{
				echo "Warning - No Coverage type for Champus Secondary<BR>";
			
			}
				
		}

		$buffer = "";
		$buffer  = $this->RenderFixedRecord ($this->formno,$this->record_types["da0"]);

		if ( ($da0[payerid] == "PAPER") OR ($da0[clmfileind] == "I") )
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
		$billto = $row[proccurcovtp];

		$coverage = CreateObject('FreeMED.Coverage', $cov);
		if (!$coverage)
		{
			echo "ERROR- No coverage in Primary<BR>";
			return;
		}

		if ($da0[clmsource] == "H")  // champus
		{
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
			else
			{
				echo "Warning - No Coverage type for Champus Primary<BR>";
			
			}
				
		}

		$buffer = "";
		$buffer  = $this->RenderFixedRecord ($this->formno,$this->record_types["da0"]);

		if ( ($da0[payerid] == "PAPER") OR ($da0[clmfileind] == "I") )
		{
			$buffer  .= $this->RenderFixedRecord ($this->formno,$this->record_types["da1"]);
		}
		$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["da2"]);
		return $buffer;

		



	} // end BillPrimary

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

		$coverage = CreateObject('FreeMED.Coverage', $cov);
		if (!$coverage)
		{
			echo "Error in claimheader no coverage<BR>";
			return;
		}

		$physician = CreateObject('FreeMED.Physician', $doc);
		if (!$physician)
		{
			echo "Error in claimheader no physician<BR>";
			return;
		}

		$patient = CreateObject('FreeMED.Patient', $pat);
		if (!$patient)
		{
			echo "Error in claimheader no patient<BR>";
			return;
		}


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

		} // end else status

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

		if ($this->insmod == "CH")
		{
			// cut cb0 record for champus only.
			// required if patient is under 18 and the time of the oldest date of service
			$svcdate = $row[procdt]; // since ordered by date this should be oldest
			$datediff = date_diff($patient->local_record[ptdob],$svcdate);
			$diffyr = $datediff[0];
			//echo "diffyear $diffyr<BR>";
			if ($diffyr < 18)
			{
				$cb0[patcntl] = $ca0[patcntl];
				$cb0[recid] = "CB0";  // generate
				// we assume the guarantor is the responsible party
				if ($coverage->covdep != 0)
				{
					$guar = CreateObject('FreeMED.Guarantor', $coverage->covdep);
					if (!$guar)
					echo "Error getting guarantor in CB0 record<BR>";
					$cb0[respfname] = $this->CleanChar($guar->guarfname);
					$cb0[resplname] = $this->CleanChar($guar->guarlname);
				}
				else
				{
					echo "Error in Champus CB0 record for Procedure $row[procdt]<BR>";
					echo "Under aged patient does not have Guarantor<BR>";
				}


			} // end diffyr < 18

		} // end if CH

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
		if ($this->insmod == "CH")
			$aa0[recvrtype] = "H"; // champus
		else
			$aa0[recvrtype] = "F"; // commercial
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
		$physician = CreateObject('FreeMED.Physician', $doc);
		if (!$physician)
		{
			echo "Error no physician<BR>";
			return;
		}
		$coverage = CreateObject('FreeMED.Coverage', $cov);
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

		$provider_id = "0";
        $grp = $insco->local_record[inscogroup];
        if (!$grp)
        {
            $name = $insco->local_record[insconame];
            echo "Failed getting inscogroup for $name<BR>";
        }

		$provider_id = $this->GetProviderGroupID($physician->local_record[phygrpprac],$grp);
		if ($provider_id=="0")  // not group bill
		{
			$provider_id = $this->GetProviderID($physician->local_record[phyidmap],$grp);
			if ($provider_id=="0")
				echo "ERROR - Providerid INVALID ProviderHeader<BR>";

		}


		$upin = $physician->local_record[phyupin];
		$ba0[upin] = $this->CleanNumber($upin);
		$ba0[ciid] = $this->CleanNumber($provider_id); // commercial provider id

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

		$row = $procstack[0];
		$cov = $row[proccurcovid];
		$coverage = CreateObject('FreeMED.Coverage', $cov);
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


		$inscoid = $this->CleanNumber($insco->local_record[inscoid]); // NAIC #
		if ($inscoid=="MHN")
		{
			global $ba0;
			$ea0[refprovid] = $ba0[ciid];
		}

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

		$coverage = CreateObject('FreeMED.Coverage', $cov);
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

		$physician = CreateObject('FreeMED.Physician', $doc);
		if (!$physician)
		{
			echo "Error in ServiceDetail no physician<BR>";
		}

		$payerid = $this->CleanNumber($insco->local_record[inscoid]);

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

			NSF::ServiceDetail($row);  // call base class
			global $fa0;

			$fa0[seqno] = $seq;
			$fa0[pos]   = $pos;
		
			if ($this->insmod == "CH")
			{	
				// champus also wants the referring provider upin here
				// if there is one.
				if ($row[procrefdoc] != 0)
				{
					$fa0[refprid] = $ea0[refprovupin];
				}
				// backward compatability with old code
				// before group billing implemented
				if (empty($fa0[rendprid]))
				{
					$rendprid = $physician->local_record[physsn];
					$rendprid = $this->CleanNumber($rendprid);
					$fa0[rendprid] = $rendprid;
				}

			}

   			$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["fa0"]);

			if ($payerid == "REG06")  // champus region 6
			{
				// champus requires the fb1 record 
				$fb1[recid] = "FB1";
				$fb1[seqno] = $seq;
				$fb1[patcntl] = $ca0[patcntl];
				$fb1[renprvlname] = $ba0[prlname];
				$fb1[renprvfname] = $ba0[prfname];
				$rendprid = $physician->local_record[physsn];
				$rendprid = $this->CleanNumber($rendprid);
				$fb1[renprvupin] = $rendprid;
   				$buffer .= $this->RenderFixedRecord ($this->formno,$this->record_types["fb1"]);
	
			}

		}
		return $buffer;

	} // end servicedetail

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
