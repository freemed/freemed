<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

LoadObjectDependency('FreeMED.EDIModule');

class HighmarkEDIModule extends EDIModule {

	// override variables
	var $MODULE_NAME = "Highmark EDI";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $CATEGORY_NAME = "EDI";
	var $CATEGORY_VERSION = "0.1";
	var $tier1 = array("93688", "60061", "54763", "54720", "53252", "95199", "95056", "54771");

	var $fac_row;
	var $phygrp_row;
    var $bill_request_type;  // primary or secondary payer.
    var $bill_coverageid;  //  pointer to coverage table entry
	//var $billing_providerid;   // can be a physician or phygroup id depending on...
    var $group_physicians;       // physicians in group as "1:2:3" etc..
    var $ptinsno;
    var $ptinsgrp;
    var $ptinsnoS;
    var $ptinsgrpS;
	var $relationship_code;  // picked up in patient and used later in clm is scndry bill.
    var $CurPatient;
    var $Guarantor;
    var $Physician;
    var $InsuranceCo;
    var $Coverage;
    var $NaicNo;
    var $InsuranceCoS;  // secondary


	// contructor method
	function HighmarkEDIModule ($nullvar = "") {
		global $display_buffer;

		// init vars
		$this->fac_row = 0;
		$this->CurPatient = 0;
		$this->Guarantor = 0;
		$this->Physician = 0;
		$this->InsuranceCo = 0;
		$this->Coverage = 0;
		$this->InsuranceCoS = 0;  // secondary
		$this->ptinsno = 0;
		$this->ptinsgrp = 0;

		// call parent constructor
		$this->EDIModule($nullvar);
	} // end function HighmarkEDIModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) return false;
		return true;
	} // end function check_vars

	function display()
	{
		global $display_buffer;
		return;

	} //end addform

	function view()
	{
		
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global ${$k};

		$wizard = CreateObject('PHP.wizard', array("been_here", "module", "action", "patient"));
		
		$query = "SELECT DISTINCT a.id,a.insconame FROM insco as a,
						procrec as b,
						coverage as c
						WHERE b.procbalcurrent>'0' AND
							  b.procbilled='0' AND
							  b.procbillable='0' AND
							  b.proccurcovid=c.id AND
							  c.covinsco=a.id
						ORDER BY a.insconame";

		$wizard->add_page("Select Insurance Companies",
						array("instobill"),
						freemed::multiple_choice($query,"insconame","instobill","")
						);
		$wizard->add_page("Select Billing Provider",
							array("providertyp"),
						"<CENTER><TABLE ALIGN=CENTER BORDER=0 CELLSPACING=0 CELLPADDING=2>
                        <TR>
                        <TD ALIGN=RIGHT>
                        <INPUT TYPE=RADIO NAME=\"providertyp\" VALUE=\"0\" CHECKED>
                        </TD><TD ALIGN=LEFT>
                        ".__("Physician")."
                        </TD>
                        </TR>
                        <TR>
                        <TD ALIGN=RIGHT>
                        <INPUT TYPE=RADIO NAME=\"providertyp\" VALUE=\"1\">
                        </TD><TD ALIGN=LEFT>
                        ".__("Physician Group")."
                        </TD>
                        </TR>
                        </TABLE></CENTER>" );
		if ($providertyp==0) // Physician
		{
			$query = "SELECT DISTINCT a.id,a.phylname,a.phyfname FROM physician as a,procrec as b
						WHERE b.procbalcurrent>'0' AND
							  b.procbilled='0' AND
							  b.procbillable='0' AND
							  a.id = b.procphysician";
							
			$wizard->add_page("Select Physicians",
								array("providerid"),
						freemed::multiple_choice($query,"##phylname##, ##phyfname## ##phymname##","providerid","")
						);
		}
		if ($providertyp==1) // Physician group
		{
			$query = "SELECT DISTINCT a.id,a.phygroupname FROM phygroup as a,procrec as b
						WHERE b.procbalcurrent>'0' AND
							  b.procbilled='0' AND
							  b.procbillable='0' AND
							  b.procpos = a.phygroupfac";
							
			$wizard->add_page("Select Physicians Group",
								array("providerid"),
						freemed::multiple_choice($query,"phygroupname","providerid","")
						);
		}
		
		if (!$wizard->is_done() and !$wizard->is_cancelled())
        {
            $display_buffer .= "<CENTER>".$wizard->display()."</CENTER>";
            return;
        }
		if ($wizard->is_cancelled())
        {
            // if the wizard was cancelled
            $display_buffer .= "<CENTER>CANCELLED<BR></CENTER><BR>\n";
			$display_buffer .= "
        	<P>
        	<CENTER>
        	<A HREF=\"$this->page_name?module=$module\">
        	".__("Back")."</A>
        	</CENTER>
        	<P>
        	";
        }
		if (!is_array($instobill))
        {
            // if the wizard was cancelled
            $display_buffer .= "<CENTER>Must Select Insurance<BR></CENTER><BR>\n";
			$display_buffer .= "
        	<P>
        	<CENTER>
        	<A HREF=\"$this->page_name?module=$module\">
        	".__("Back")."</A>
        	</CENTER>
        	<P>
        	";
			return;
        }
		if (!is_array($providerid))
        {
            // if the wizard was cancelled
			$msg = ($providertyp) ? "Groups" : "Physicians";
            $display_buffer .= "<CENTER>Must Select $msg<BR></CENTER><BR>\n";
			$display_buffer .= "
        	<P>
        	<CENTER>
        	<A HREF=\"$this->page_name?module=$module\">
        	".__("Back")."</A>
        	</CENTER>
        	<P>
        	";
			return;
        }
		
		if ($instobill[0] == -1)
		{
			//bill all
			$query = "SELECT DISTINCT a.id,a.insconame FROM insco as a,
						procrec as b,
						coverage as c
						WHERE b.procbalcurrent>'0' AND
							  b.procbilled='0' AND
							  b.procbillable='0' AND
							  b.proccurcovid=c.id AND
							  c.covinsco=a.id
						ORDER BY a.insconame";
			$result = $sql->query($query);
			if (!$result) {
				$display_buffer .= "Failed to get ins cos";
				template_display();
			}
			$inscnt=0;
			while($ins = $sql->fetch_array($result))
			{
				// reuse instobill since it's global
				$instobill[$inscnt] = $ins[id];
				$instobillname[$inscnt] = $ins[insconame];
				$inscnt++;
			}
		}
		else
		{
			// if not all then just the names of ones asked to do
			$inscnt = count($instobill);
			for ($i=0;$i<$inscnt;$i++)
			{	
				$insnme = freemed::get_link_field($instobill[$i],"insco","insconame");
				$instobillname[$i] = $insnme;
			}
			
		}

		$inscnt = count($instobill);

		if ($providerid[0] == -1)  // ALL is requested
		{
			// note these selects should be the same as above
			if ($providertyp==0)  // all physicians
			{
				$query = "SELECT DISTINCT a.id,a.phylname,a.phyfname FROM physician as a,procrec as b
						WHERE b.procbalcurrent>'0' AND
							  b.procbilled='0' AND
							  b.procbillable='0' AND
							  a.id = b.procphysician";
			}
			if ($providertyp==1)  // all physicians groups
			{
				$query = "SELECT DISTINCT a.id,a.phygroupname FROM phygroup as a,procrec as b
						WHERE b.procbalcurrent>'0' AND
							  b.procbilled='0' AND
							  b.procbillable='0' AND
							  b.procpos = a.phygroupfac";
			}
			$result = $sql->query($query);
			if (!$result) {
				$display_buffer .= "Failed to get physicians or groups";
				template_display();
			}
			$providers = 0;
			while($prov = $sql->fetch_array($result))
			{
				// reuse providers since it's global
				$providerid[$providers] = $prov[id];
				$providers++;
			}
		}
		$providers = count($providerid);
		//$display_buffer .= "providers $providers $providerid[0]<BR>";

		if (!$this->EDIOpen())
		{	
			$this->Error("EDIOpen Failed");
			return false;
		}	
		for ($i=0;$i<$inscnt;$i++)  // for each insco
		{
			if ($providertyp==0) // physicians
			for ($p=0;$p<$providers;$p++)  // for each physician
			{
				$query = "SELECT DISTINCT procpatient,proccurcovid
							FROM procrec as a,coverage as b
							WHERE a.procbalcurrent>'0' AND
								  a.procbillable='0' AND
								  a.procbilled='0' AND
								  a.procphysician = '$providerid[$p]' AND
								  b.covinsco = '$instobill[$i]' AND
							      a.proccurcovid = b.id";
				$provider_result = $sql->query($query);
				if (!$provider_result) {
					$display_buffer .= __("ERROR")." - No Physcians";
					template_display();
				}
				while($provrow = $sql->fetch_array($provider_result))
				{
					$this->Generate($provrow[procpatient], $provrow[proccurcovid], $providerid[$p], "PHY");
				}

			}

			if ($providertyp==1) // physicians groups
			for ($p=0;$p<$providers;$p++)
			{
				$pos = freemed::get_link_rec($providerid[$p],"phygroup");
				if (!$pos) {
					$display_buffer .= __("ERROR")." - failed getting facility for group";
					template_display();
				}
				$fac = $pos[phygroupfac];
				if (!$fac) {
					$display_buffer .= __("ERROR")." -  failed getting facility for group 2";
					template_display();
				}
				$query = "SELECT DISTINCT procpatient,proccurcovid
							FROM procrec as a,coverage as b
							WHERE a.procbalcurrent>'0' AND
								  a.procbillable='0' AND
								  a.procbilled='0' AND
								  a.procpos= '$fac' AND
								  b.covinsco = '$instobill[$i]' AND
							      a.proccurcovid = b.id";
				$provider_result = $sql->query($query);
				if (!$provider_result) {
					$display_buffer .= __("ERROR")." - No Physicians";
					template_display();
				}
				while($provrow = $sql->fetch_array($provider_result))
				{
					$this->Generate($provrow[procpatient], 
									$provrow[proccurcovid], 
									$fac, "GRP");
				}

			}

		}

		
		$this->EDIClose();
		$stream=false;
		$buffer = $this->GetEDIBuffer($stream);
		$display_buffer .= "$buffer";
		$buffer = $this->GetEDIErrors();
		$display_buffer .= "$buffer";
		
	
		// wizard must be done.
		
		


	}  // end view

	// START OF EDI CODE

	//
	function Provider() {
		global $display_buffer;
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;

		// find provider

		// if we have a facility use the EIN number else use the docs SSN

		if ($this->fac_row != 0)
		{
        	// we provided the EIN
        	$taxid_qualifier = "EI";
        	$provider_taxid = $this->fac_row[psrein];
		}
		else
		{
        	// we provided the SSN
        	$taxid_qualifier = "SY";
        	$provider_taxid = $this->Physician->local_record["physsn"];
		}	

		// build the provider info records
		// first the taxid or ssn or ein

		$this->edi_buffer .= "PRV*BI*";
		//$this->edi_buffer = $this->edi_buffer."PRV*BI*".$taxid_qualifier."*";
		if (empty($provider_taxid))
		{
			$this->Error("Provider taxid ein/ssn in invalid");
			$provider_taxid="TAXIDXXXXX";
		}

		$this->edi_buffer = $this->edi_buffer.$taxid_qualifier."*".$provider_taxid.$this->record_terminator;


		// now the provider detail section
		// follow up with name address info note ALL UPPERCASE TEXT IS REQUIRED!!!

		$this->edi_buffer .= "NM1*85*";

		//
		// NOTE::
		// if there is a facility we may need to append the pay-to provider
		// records later. this means someone other that the fac will get the check
		// probably the doc himself. ALSO, it is possible for the entire
		// facility to have a providerid which include ALL the docs. 
		// I don;t think freemed handles that now.

		if ($this->fac_row != 0)
		{
			$error_prefix = "Facility - ";

			$psrname = $this->CleanChar($this->fac_row[psrname]);
        	$this->edi_buffer = $this->edi_buffer."2*".$psrname;
			$this->edi_buffer .= $this->record_terminator;

			$addr1 = $this->CleanChar($this->fac_row[psraddr1]);
			$addr2 = $this->CleanChar($this->fac_row[psraddr2]);
			$city  = $this->CleanChar($this->fac_row[psrcity]);
			$state = $this->CleanChar($this->fac_row[psrstate]);
        	$zip   = $this->CleanNumber($this->fac_row[psrzip]);
			$zip = $this->CleanChar($zip);
		
		}
		else
		{   // use the physician info 
			$error_prefix = "Physician - ";
			$phylname = $this->CleanChar($this->Physician->phylname);
        	$this->edi_buffer = $this->edi_buffer."1*".$phylname."*";
			$phyfname = $this->CleanChar($this->Physician->phyfname);
        	$this->edi_buffer = $this->edi_buffer.$phyfname;
			$this->edi_buffer .= $this->record_terminator;

			$addr1 = $this->CleanChar($this->Physician->local_record["phyaddr1a"]);
			$addr2 = $this->CleanChar($this->Physician->local_record[phyaddr2a]);
			$city  = $this->CleanChar($this->Physician->local_record[phycitya]);	
			$state = $this->CleanChar($this->Physician->local_record[phystatea]);
			$zip   = $this->CleanNumber($this->Physician->local_record[phyzipa]);
			$zip = $this->CleanChar($zip);


		}

		if (empty($addr1))
		{
			$err = "".$error_prefix."Does not have a valid Address";
			$this->Error($err);
			$addr1 = "ADDR1XXXXX";
		}
		$this->edi_buffer = $this->edi_buffer."N3*".strtoupper($addr1);
		if (!empty($addr2))
		{
			$this->edi_buffer = $this->edi_buffer."*".$addr2;
			$this->edi_buffer .= $this->record_terminator;
		}
		else
		{
			$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		}

		if (empty($city))
		{
			$err = "".$error_prefix."Does not have a valid Address";
			$this->Error($err);
			$city = "CITYXXXXX";
		}
		if (empty($state))
		{
			$err = "".$error_prefix."Does not have a valid Address";
			$this->Error($err);
			$state = "STATEXXXXX";
		}
		if (empty($zip))
		{
			$err = "".$error_prefix."Does not have a valid Address";
			$this->Error($err);
			$zip = "ZIPXXXXX";
		}
		$this->edi_buffer = $this->edi_buffer."N4*".$city."*";
		$this->edi_buffer = $this->edi_buffer.$state."*";
		$this->edi_buffer = $this->edi_buffer.$zip.$this->record_terminator;

// Starting here we need to determine if billing a grp or a 
// physician

		// generate specialtycode record
		// specialty codes are noted in appendix 2 of the Highmark
		// X12 specification for bluecross;

		if ($this->phygrp_row != 0)
		{
			$specid = $this->phygrp_row[phygroupspe1];
		}
		else
		{
			$specid = $this->Physician->local_record["physpe1"];	
		}

		if ( ($specid == 0) OR (empty($specid)) )
		{
			$this->Error("Physician or Group must have a specialty code");
			$specialty_code = "SPECXXXXXX";
		}
		else
		{
			$spec_row = freemed::get_link_rec($specid, "specialties");
			if (!$spec_row)
			{
				$this->Error("Error in provider getting specialty code");
				$specialty_code = "SPECXXXXXX";
			}
			$specialty_code = $spec_row[specname];
			$specialty_code = $this->CleanNumber($specialty_code); 
		}

		//$dummy_specialty_code = "27";  // 27 = psychiatry we need to start carrying these.

		$this->edi_buffer = $this->edi_buffer."REF*87*".$specialty_code;
		$this->edi_buffer .= $this->record_terminator;

		// generate provider id record we get the provider number this doc/grp has
		// for this insurer.
		// phyidmap is as array of provider numbers for insurance companies
		// the group code in the insco is the offset into the array. it should be 		
		// the Docs/grp provider id for this insco.

		$provider_id = "0";
		$grp = $this->InsuranceCo->local_record[inscogroup];
		if (!$grp)
		{
			$name = $this->InsuranceCo->local_record[insconame];	
			$this->Error("Error - Failed to get inscogroup record for $name");
		}

		if ($this->phygrp_row != 0)
		{
			$providerids = explode(":",$this->phygrp_row[phygroupidmap]);
			$provider_id = $providerids[$grp];
		}
		else
		{
			$providerids = explode(":",$this->Physician->local_record[phyidmap]);
			$provider_id = $providerids[$grp];
		}

		if ($provider_id == "0")
		{
			$this->Error("Physician/Group does not have a valid provider ID");
			$provider_id = "PROVXXXXX";
		}

		$ref_qualifier = "BQ";  // tier 2 payer
		if (fm_value_in_array($this->tier1,$this->NaicNo))
			$ref_qualifier = "1B"; // tier 1 payer
			
			

		$this->edi_buffer = $this->edi_buffer."REF*".$ref_qualifier."*".strtoupper($provider_id);
		$this->edi_buffer .= $this->record_terminator;

		// billing provider contact info.  we will use the facility phone but a billing service
		// would want their phone number here. this is used along with the transaction ref
		// no to call someone if the recvr has any questions about the submission.

		if ($this->fac_row != 0)
        	$phone_no = $this->fac_row[psrphone];
		else
        	$phone_no = $this->Physician->local_record[phyphonea];


		$phone_no = $this->CleanNumber($phone_no);

		// the ** denotes that we don't use a contact name just a phone number
		// otherwise it would be *MYNAME*TE .....

		$this->edi_buffer = $this->edi_buffer."PER*PH**TE*";
	
		if (empty($phone_no))
		{
			$this->Error("Physician does not have a valid phone number");
			$phone_no = "XXX-XXX-XXXX";
		}


		$this->edi_buffer = $this->edi_buffer.$phone_no;

		$this->edi_buffer = $this->edi_buffer.$this->record_terminator;

		// pay to provider records. 
		// if we have a facility and a doc use the docs info here else we can skip it.
		// NOTE:: this assumes that the physicians address if any is his/her billing address
		// and should be different from the facility.
		// NOTE::
		// decided not to support this payto provider stuff at this time
		// need to know which address to assume if not empty then bill it.
		// maybe phyaddr1b ????
/* comment start
		if ($this->fac_row != 0)
		{
       		if (!empty($this->Physician->local_record[phyaddr1a]))
      		{ 
              // this says we have a payto provider as an individual
            	$this->edi_buffer = $this->edi_buffer."NM1*87*1".$this->record_terminator;
				$phyaddr1a = $this->CleanChar($this->Physician->local_record[phyaddr1a]);
             	$this->edi_buffer = $this->edi_buffer."N3*".$phyaddr1a;
               	if (!empty($this->Physician->local_record[phyaddr2a]))
					{
					$phyaddr2a = $this->CleanChar($this->Physician->local_record[phyaddr2a]);
					$this->edi_buffer = $this->edi_buffer."*".$phyaddr2a;
					$this->edi_buffer .= $this->record_terminator;
				}
            	else
				{
					$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
				}
            	$this->edi_buffer = $this->edi_buffer."N4*";
				$phycitya = $this->CleanChar($this->Physician->local_record[phycitya]);
            	$this->edi_buffer = $this->edi_buffer.$phycitya."*";
				$phystatea = $this->CleanChar($this->Physician->local_record[phystatea]);
            	$this->edi_buffer = $this->edi_buffer.$phystatea."*";
            	$zip = $this->CleanNumber($this->Physician->local_record[phyzipa]);
            	$zip = $this->CleanChar($zip);
            	$this->edi_buffer = $this->edi_buffer.$zip.$this->record_terminator;
     		}
		}
 comment end */

		

	} // end provider


	function Insurer() {
		global $display_buffer;
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;

		if ($this->InsuranceCo == 0)
		{
		    $this->Error("No Insurance info provided to Insurer function");	
			return false;
		}

		$this->edi_buffer = $this->edi_buffer."SBR*";


		if ($this->bill_request_type == PRIMARY) // primary
		{
        	$this->edi_buffer = $this->edi_buffer."P**";
		}
		elseif ($this->bill_request_type == SECONDARY) // secondary
        	$this->edi_buffer = $this->edi_buffer."S**";
		else
		{
        	$this->Error("Error - Payer MUST be a primary or secondary");
			$this->Error("        bill resuest type = $this->bill_request_type");
			$this->Error("        Primary Asummed");
			$this->bill_request_type = PRIMARY;
        	$this->edi_buffer = $this->edi_buffer."P**";
		}
		// this should be the Group number on the insureds card.

		$insgrp = $this->CleanChar($this->ptinsgrp);
		$insgrp = $this->CleanNumber($insgrp);

		if (empty($insgrp))
		{
			$this->Error("Insured does not have a vilid Group Number");
		    $this->edi_buffer = $this->edi_buffer."GRPXXXXX";
		}
		else
		{
		    $this->edi_buffer = $this->edi_buffer.strtoupper($insgrp);
		}

		$this->edi_buffer = $this->edi_buffer.$this->record_terminator;

		// get insurers name and address
		$insname = $this->InsuranceCo->local_record[insconame];
		$insname = $this->CleanChar($insname);

		$this->edi_buffer = $this->edi_buffer."NM1*IN*2*";
		$this->edi_buffer = $this->edi_buffer.$insname."*****NI*";

		// NOTE: we need to start carrying this in the insco rec.
		// the list is in appendex 3 and 7 of the higmark spec.
		// NOTE I changed this code to the what freemed calls the NEIC it should work ok
		// for either.
		//
		//
		//$insnaic = $this->InsuranceCo->local_record[inscoid];
		//if (empty($insnaic))
		//{
		//	$this->Error("No NAIC number for this ins co ");
		//	$insnaic = "NAICXXXX";
		//}

		
		$this->edi_buffer = $this->edi_buffer.$this->NaicNo.$this->record_terminator;
		$insaddr1 = $this->InsuranceCo->local_record[inscoaddr1];
		$insaddr2 = $this->InsuranceCo->local_record[inscoaddr2];
		$inscity = $this->InsuranceCo->local_record[inscocity];
		$insstate = $this->InsuranceCo->local_record[inscostate];
		$inszip = $this->InsuranceCo->local_record[inscozip];
		$insaddr1 = $this->CleanChar($insaddr1);
		$insaddr2 = $this->CleanChar($insaddr2);
		$inscity = $this->CleanChar($inscity);
		$insstate = $this->CleanChar($insstate);
		$inszip = $this->CleanChar($inszip);
		$inszip = $this->CleanNumber($inszip);


		$this->edi_buffer = $this->edi_buffer."N3*".$insaddr1;
		if (!empty($insaddr2))
		{
        	$this->edi_buffer = $this->edi_buffer." ".$insaddr2;
        	$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		}
		else
		{
        	$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		}
		$this->edi_buffer = $this->edi_buffer."N4*".$inscity."*";
		$this->edi_buffer = $this->edi_buffer.$insstate."*";
		$this->edi_buffer = $this->edi_buffer.$inszip.$this->record_terminator;

	}

	function Insured() {
		global $display_buffer;
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;


		if ($this->Guarantor != 0)
		{

        	// patient has a guarantor thus guarantor is the insured.
			$last_name = $this->Guarantor->guarlname;
			$last_name = $this->CleanChar($last_name);
			$first_name = $this->Guarantor->guarfname;
			$first_name = $this->CleanChar($first_name);
        	$dob = $this->CleanNumber($this->Guarantor->guardob);
			$sex = $this->CleanChar($this->Guarantor->guarsex);
			//$ptinsno = $this->CleanNumber($this->ptinsno); // ID number on the card
			//$ptinsno = $this->CleanChar($ptinsno);
			if (!$this->Guarantor->guarsame)
			{
				// usr guars addr if not the same as patient
				$addr1 = $this->Guarantor->guaraddr1;
				$addr1 = $this->CleanChar($addr1);
				$addr2 = $this->Guarantor->guaraddr2;
				$addr2 = $this->CleanChar($addr2);
				$city = $this->Guarantor->guarcity;
				$city = $this->CleanChar($city);
				$state = $this->Guarantor->guarstate;
				$state = $this->CleanChar($state);
				$zip = $this->CleanNumber($this->Guarantor->guarzip);
			}
			else
			{
				// else use the patients address
				$addr1 = $this->CurPatient->local_record[ptaddr1];
				$addr1 = $this->CleanChar($addr1);
				$addr2 = $this->CurPatient->local_record[ptaddr2];
				$addr2 = $this->CleanChar($addr2);
				$city = $this->CurPatient->local_record[ptcity];
				$city = $this->CleanChar($city);
				$state = $this->CurPatient->local_record[ptstate];
				$state = $this->CleanChar($state);
				$zip = $this->CleanNumber($this->CurPatient->local_record[ptzip]);
			}
		}
		else
		{
        	// patient is the insured.
			$last_name = $this->CurPatient->ptlname;
			$last_name = $this->CleanChar($last_name);
			$first_name = $this->CurPatient->ptfname;
			$first_name = $this->CleanChar($first_name);
			//$ptinsno = $this->CleanNumber($this->ptinsno); // ID number on the card
			//$ptinsno = $this->CleanChar($ptinsno);
			$addr1 = $this->CurPatient->local_record[ptaddr1];
			$addr1 = $this->CleanChar($addr1);
			$addr2 = $this->CurPatient->local_record[ptaddr2];
			$addr2 = $this->CleanChar($addr2);
			$city = $this->CurPatient->local_record[ptcity];
			$city = $this->CleanChar($city);
			$state = $this->CurPatient->local_record[ptstate];
			$state = $this->CleanChar($state);
        	$zip = $this->CleanNumber($this->CurPatient->local_record[ptzip]);
        	$dob = $this->CleanNumber($this->CurPatient->ptdob);
			$sex = $this->CleanChar($this->CurPatient->ptsex);

		}

		$ptinsno = $this->CleanNumber($this->ptinsno); // ID number on the card
		$ptinsno = $this->CleanChar($ptinsno);

		if (empty($ptinsno))
		{
			$this->Error("Patient does not have a valid Insurance ID");
			$ptinsno = "INSNOXXXXXXX";
		}


      	$this->edi_buffer = $this->edi_buffer."NM1*IL*1*";
		$this->edi_buffer = $this->edi_buffer.$last_name."*";
		$this->edi_buffer = $this->edi_buffer.$first_name."*";
		$this->edi_buffer .= "***C1*";
		$this->edi_buffer = $this->edi_buffer.$ptinsno;
		$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		$this->edi_buffer .= "N3*";
		$this->edi_buffer .= $addr1;
		if (!empty($addr2))
		{
			$this->edi_buffer = $this->edi_buffer."*".$addr2;
			$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		}
		else
		{
			$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		}
		$this->edi_buffer = $this->edi_buffer."N4*".$city."*";
		$this->edi_buffer = $this->edi_buffer.$state."*";
		$this->edi_buffer = $this->edi_buffer.$zip.$this->record_terminator;

		// mandatory when patient is the insured
		if ($this->Coverage->covdep == 0)
		{
			// patient is the insured
			$this->edi_buffer .= "DMG*D8*";
			$this->edi_buffer = $this->edi_buffer.$dob."*";
			$this->edi_buffer = $this->edi_buffer.$sex;
			$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		}


	}

	function Patient() {
		global $display_buffer;
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;

		//
		// start of patient info section
		//
		// Relationship codes are very strange here.
		// I'm only using what we carry in freemed but we should change this to carry
		// the x12 types instead.
		// 18 = Self  01 = spouse (Husband or Wife) 02 = Natural Child
		$this->relationship_code = "00";
		if ($this->Coverage->covreldep == "S")
        	$this->relationship_code = "18";

		if ( ($this->Coverage->covreldep == "H") OR ($this->Coverage->covreldep == "W") )
        	$this->relationship_code = "01";

		if ($this->Coverage->covreldep == "C")  // we assume natural child
        	$this->relationship_code = "02";

		$this->edi_buffer = $this->edi_buffer."PAT*";

		if ($this->relationship_code == "00")
		{
        	$this->Error("Error - Relationship of insured/patient is not ANSI X12 Compliant!");
        	$this->edi_buffer = $this->edi_buffer."XX*";
		}
		else
		{
        	$this->edi_buffer = $this->edi_buffer.strtoupper($this->relationship_code)."*";
		}

		$this->edi_buffer .= "**";  // unused fields

		//
		// Is patient a full time or part time student.
		// a student is someone 19 years or older, not handicapped and not the insured.
		// NOTE: How do we know if the patient is handicapped??

		$studentcode = "N"; // default not a student

		$ptstatus = $this->CurPatient->local_record[ptstatus];
		if ($ptstatus != 0)
		{
			$ptstatus = freemed::get_link_field($ptstatus,"ptstatus","ptstatus");
			if (!$ptstatus)
			{
        		$this->Error("Error - Failed getting ptstatus record");
        		$studentcode = "STUCODEXX";
			}
			else
			{
				$datediff = date_diff($this->CurPatient->local_record[ptdob]);
			
				if ( ($ptstatus != "HC") AND 
					 ($datediff[0] >= 19)AND
					 ($this->Coverage->covdep!=0) )  // got a student
				{
					if ($this->CurPatient->local_record[ptempl] == "y") 
					{
						$studentcode = "F";  // fulltime
					}
					if ($this->CurPatient->local_record[ptempl] == "p")
					{
						$studentcode = "P";
					}
				}
			}
		}

        $this->edi_buffer = $this->edi_buffer.$studentcode;


		// the PAT record also has dates of death which would go here


        $this->edi_buffer = $this->edi_buffer.$this->record_terminator;

		// patient detail. only required if not the insured
		// patients name
		// hope for correct relationship "S" above if we drop out of this.
		// when patient is not the insured we must show the patients name.
	
		if ($this->Coverage->covdep != 0) // patient is not the insured
		{
			// so show patients name and address 
			$this->edi_buffer = $this->edi_buffer."NM1*QC*1*";
			$ptfname = $this->CleanChar($this->CurPatient->ptfname);
			$ptlname = $this->CleanChar($this->CurPatient->ptlname);
			$this->edi_buffer = $this->edi_buffer.$ptlname."*";
			$this->edi_buffer = $this->edi_buffer.$ptfname;
			$this->edi_buffer = $this->edi_buffer.$this->record_terminator;

			if (!$this->Guarantor->guarsame) // address not the same
			{
				// N3 and N4 records go here to denote patients address is not
				// the same as the insureds so we have to show the patients
				// address also
				$addr1 = $this->CurPatient->local_record[ptaddr1];
				$addr1 = $this->CleanChar($addr1);
				$addr2 = $this->CurPatient->local_record[ptaddr2];
				$addr2 = $this->CleanChar($addr2);
				$city = $this->CurPatient->local_record[ptcity];
				$city = $this->CleanChar($city);
				$state = $this->CurPatient->local_record[ptstate];
				$state = $this->CleanChar($state);
				$zip = $this->CleanNumber($this->CurPatient->local_record[ptzip]);

				$this->edi_buffer .= "N3*";
				$this->edi_buffer .= $addr1;
				if (!empty($addr2))
				{
					$this->edi_buffer = $this->edi_buffer."*".$addr2;
					$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
				}
				else
				{
					$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
				}
				$this->edi_buffer = $this->edi_buffer."N4*".$city."*";
				$this->edi_buffer = $this->edi_buffer.$state."*";
				$this->edi_buffer = $this->edi_buffer.$zip.$this->record_terminator;
			}
			// pat dob and gender

			$this->edi_buffer = $this->edi_buffer."DMG*D8*";
			$dob = $this->CleanNumber($this->CurPatient->ptdob);
			$this->edi_buffer = $this->edi_buffer.$dob."*";
			$sex = $this->CleanChar($this->CurPatient->ptsex);
			$this->edi_buffer = $this->edi_buffer.$ptsex;
			$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		}

		// 

	}

	function GetClaims($count=false) {
		global $display_buffer;

		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;

		//$phy_select = "procphysician = '".$this->billing_providerid."' AND";
		//$phygroup_select = "procpos = '".$this->billing_providerid."'".
		//		" AND procphysician IN($this->group_physicians) AND";
		$phygroup_order = "ORDER BY procphysician,proceoc,procauth,procrefdoc,proccurcovid,proccov1,procdt";
		$normal_order = "ORDER BY procpos,proceoc,procauth,procrefdoc,proccurcovid,proccov1,procdt";
	
		$xtraselect = "";	
		if ($this->phygrp_row != 0)
		{
			$pos = $this->phygrp_row[phygroupfac];
			$physicians = $this->phygrp_row[phygroupdocs];
			$physicians = str_replace(":",",",$physicians);
			$xtraselect = "procpos = '".$pos."' AND procphysician IN($physicians) AND";
			//$xtraselect = $phygroup_select;
			$xtraorder  = $phygroup_order;
		}
		else
		{
			$physician = $this->Physician->local_record[id];
			$xtraselect = "procphysician = '".$physician."' AND"; 
			//$xtraselect = $phy_select;
			$xtraorder  = $normal_order;
		}

		$select = " * ";

		if ($count)
		{
			$select = " COUNT(*) ";
		}

		$current_patient = $this->CurPatient->id;

		$query = "SELECT".$select."FROM procrec 
                           WHERE (".$xtraselect."
                             proccurcovtp = '$this->bill_request_type' AND
                             proccurcovid = '$this->bill_coverageid' AND
                             procpatient = '$current_patient' AND
                             procbillable = '0' AND
                             procbilled = '0' AND
                             procbalcurrent > '0'
                           ) ".$xtraorder; 

		$display_buffer .= "query $query<BR>";


		$result = $sql->query($query);
		if ($count)
		{
			$numrows = $sql->fetch_array($result);
			$result = $numrows[0];
		}
		return $result;

	}
    function Claim() {
		global $display_buffer;
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;


		$current_patient = $this->CurPatient->id;

		// false says to return a result set
		$result = $this->GetClaims(false);
		if (!$result) {
			$display_buffer .= __("ERROR")." - Query failed";
			template_display();
		}

		if (!$sql->results($result))
		{

			$this->Error("No procedures to bill for this Patient");
			return;
		}

		$firstproc = 1;
		$proccount = 0;
		$loopcount = 13;

		//$rowcnt = $sql->num_rows($result);
		// while we have procedures
		while ($row = $sql->fetch_array($result))
		{
			if ($firstproc==1)
			{
				$firstproc=0;
				$prev_key = $this->NewKey($row);
				$diagset = CreateObject('FreeMED.diagnosis_set');
			}

	
			$cur_key = $this->NewKey($row);

			if (!($diagset->testAddSet($row[procdiag1],
									 $row[procdiag2],
									 $row[procdiag3],
									 $row[procdiag4])) OR
			 ($proccount > $loopcount) 				   OR
			 ($prev_key != $cur_key) )
			{
			//	$display_buffer .= "keys prec $prev_key cur $cur_key<BR>";
				if ($prev_key != $cur_key)
					$prev_key = $cur_key;

				$this->GenClaimSegment($procstack);
				$this->GenServiceSegment($procstack);
				// clear stack
				$proccount = 0;
				unset($diagset);
				unset($procstack);
				$diagset = CreateObject('FreeMED.diagnosis_set');
				$diagset->testAddSet($row[procdiag1],
								 $row[procdiag2],
								 $row[procdiag3],
								 $row[procdiag4]);
			}

			// add new stack entry
			//$display_buffer .= "add stack $proccount<BR>";
			$procstack[$proccount] = $row;
			$proccount++;
		}

		//$display_buffer .= " proccount $proccount<BR>";
		if ($proccount > 0)
		{
			$this->GenClaimSegment($procstack);
			$this->GenServiceSegment($procstack);
			
		}

	} // end Claim

	function GenClaimSegment($procstack) {
		global $display_buffer;
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;

		$row = $procstack[0];

		$pos = 0;
		if ($row[procpos] != 0)
		{
			$cur_pos = freemed::get_link_rec($row[procpos], "pos");
			if (!$cur_pos)
				$this->Error("Failed reading pos table");
			$pos = $cur_pos[posname];
			
		}
		if ($pos == 0)
		{
			// plug with Office code
			$pos="11"; 
			$this->Error("Warning: Plugged pos with Office Code 11");
		}

		$count = count($procstack);

		if ($count == 0)
		{
			$this->Error("Error in GenClaimSegment Stack count 0");
			return;
		}

		$procbal = 0.00;

		$diagset = CreateObject('FreeMED.diagnosis_set');

		for ($i=0;$i<$count;$i++)
		{
			$row = $procstack[$i];
			$procbal += $row[procbalcurrent];
			$dates[$i] = $row[procdt];

			// this should never overflow if the control break is working.
			$diagset->testAddSet($row[procdiag1],
							 $row[procdiag2],
							 $row[procdiag3],
							 $row[procdiag4]);

		}
		
		$mindt = min($dates);
		$maxdt = max($dates);

		// all rows should have the same eoc,auth
		$current_patient = $this->CurPatient->id;

		$accident_date = 0;
		$this->edi_buffer = $this->edi_buffer."CLM*".$current_patient."*";
		$this->edi_buffer = $this->edi_buffer.floor($procbal)."*";
		$this->edi_buffer = $this->edi_buffer."BL**".$pos.":B**A*Y*M";
		if ($row[proceoc] != 0)
		{
			$eoc_row = freemed::get_link_rec($row[proceoc], "eoc");
			if (!$eoc_row)
				$this->Error("Failed reading eoc record");

			// x12 also has ABUSE, and ANOTHER PARTY freemed is not carrying those
			if ($eoc_row[eocrelauto] == "yes")
			{
				$this->edi_buffer = $this->edi_buffer."**AA*".$eoc_row[eocrelautostpr];
				$accident_date = $this->CleanNumber($eoc_row[eocstartdate]);
			}
			if ($eoc_row[eocrelemp] == "yes")
			{
				$this->edi_buffer = $this->edi_buffer."**EM";
			}
			if ($eoc_row[eocrelother] == "yes")  // other Accident!
			{
				$this->edi_buffer = $this->edi_buffer."**OA";
			}
			
		}
		else
		{
			$this->Error("Warning - No EOC for this procedure $row[procdt]");
		}
			
		$this->edi_buffer .= $this->record_terminator;

		// start of dates of service.
		$this->edi_buffer = $this->edi_buffer."DTP*472*";
		$maxdt = $this->CleanNumber($maxdt);
		$mindt = $this->CleanNumber($mindt);

		if ($maxdt > $mindt)
		{
			// date range
			$this->edi_buffer = $this->edi_buffer."RD8*".$mindt."-".$maxdt;
		}
		else
		{
			// single date
			$this->edi_buffer = $this->edi_buffer."D8*".$mindt;
		}
		$this->edi_buffer = $this->edi_buffer.$procdt.$this->record_terminator;

		if (accident_date != 0)
		{
			$this->edi_buffer = $this->edi_buffer."DTP*439*D8*".$accident_date;
			$this->edi_buffer .= $this->record_terminator;
		}
		// there are about a dozen other date types that could go here
		// disability date first symptom and such.
		// also a conditional DSB record to denote the type of disability

		// see if patient already paid anything
		$total_paid_bypatient = 00.00;

		$query = "SELECT * FROM payrec WHERE payrecproc='$row[id]' AND 
											payrecsource='0' AND
											payreccat='".PAYMENT."'";
		$pay_result = $sql->query($query) or DIE("Query failed");
		while ($pay_row = $sql->fetch_array($pay_result))
		{

			$total_paid_bypatient += $pay_row[payrecamt];
		}
		if ($total_paid_bypatient > 00.00)
		{	
			$this->edi_buffer = $this->edi_buffer."AMT*F5*".$total_paid_bypatient;
			$this->edi_buffer .= $this->record_terminator;
		}
		
		if ($row[procauth] != 0)
		{
			$auth_row = freemed::get_link_rec($row[procauth],"authorizations");
			if (!$auth_row)
				$this->Error("Failed to read procauth");
			$auth_num = $this->CleanNumber($auth_row[authnum]);
			$auth_num = $this->CleanChar($auth_num);
			if (!$auth_num)
			{
				$this->Error("Authorization number Invalid");
				$auth_num = "AUTHXXXX";
			}
			//validate auth date for each procedure 
			$authdtbegin = $auth_row[authdtbegin];
			$authdtend = $auth_row[authdtend];
	
			for ($i=0;$i<$count;$i++)
			{
				$prow = $procstack[$i];
				$procdt = $row[procdt];
				if (!date_in_range($procdt,$authdtbegin,$authdtend))
				{
					$this->Error("Warning: Authorization $auth_num has expired for Procedure $procdt");

				}
			}

			$this->edi_buffer = $this->edi_buffer."REF*G1*".
					$auth_num.$this->record_terminator;
		}
		else
		{
			$this->Error("Warning - No Authorization for this procedure");
		}

		// there is a CRC record that can go here for reporting vision claims


		$this->edi_buffer = $this->edi_buffer."HI*";

		// temp to make it thru the test

		$diagstack = $diagset->getStack();
		$diagcnt = count($diagstack);
		
		if ($diagcnt == 0)
		{
			$this->Error("Procedures do not have Diagnosis codes");
			return;
		}
		

		for ($i=1;$diagcnt>=$i;$i++)
		{
			if ($i == 1)
				$this->edi_buffer = $this->edi_buffer."BR:";
			else
				$this->edi_buffer = $this->edi_buffer."*BQ:";
	
			$icd9code = $this->CleanNumber($diagstack[$i]);
			$this->edi_buffer .= $icd9code; 
			
			
		}

		$this->edi_buffer .= $this->record_terminator;

		$this->CLM_Provider_Detail($procstack);


		if ($this->bill_request_type == SECONDARY)  // billing the secondary?
		{
			// we must generate a 2320 and 2330 inner claim loop
			$this->CLM_SecondaryIns($procstack);
			//$this->Error("Not implemented to bill secondary");

		}

		//$display_buffer .= "Balance $procbal mindt $mindt maxdt $maxdt<BR>";
		
	} // end genClaimSegment

	function GenServiceSegment($procstack) {
		global $display_buffer;
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;

		$count = count($procstack);
		//$display_buffer .= "count $count<BR>";
		// write the service charges	

		//$LX_setno++;

		$diagset = CreateObject('FreeMED.diagnosis_set');

		for ($i=0;$i<$count;$i++)
		{
			$row = $procstack[$i];

			if ($i < 10)
				$LX_setno = "0".$i;
			else
				$LX_setno = $i;

			$LX_setno++; //can't be zero.

			$this->edi_buffer = $this->edi_buffer."LX*".$LX_setno.$this->record_terminator;


			$cur_cpt = freemed::get_link_rec ($row[proccpt], "cpt");
			if (!$cur_cpt)
				$this->Error("Failed reading cpt table");

			$diagset->testAddSet($row[procdiag1],
							 $row[procdiag2],
							 $row[procdiag3],
							 $row[procdiag4]);

			$diag_xref = $diagset->xrefList($row[procdiag1],
							 $row[procdiag2],
							 $row[procdiag3],
							 $row[procdiag4]);

			$diag_xref = str_replace(",",":",$diag_xref);
			$this->edi_buffer = $this->edi_buffer."SV1*HC:".
							$cur_cpt[cptcode].":".$diag_xref."*";
			$this->edi_buffer .= floor($row[procbalcurrent]);
			$this->edi_buffer .= "*UN*";
			$this->edi_buffer .= floor($row[procunits]);
			$this->edi_buffer .= "**";

			$cur_insco = $this->InsuranceCo->local_record[id];
			$tos_stack = fm_split_into_array ($cur_cpt[cpttos]);
       		$tosid = ( ($tos_stack[$cur_insco] < 1) ?
                      $cur_cpt[cptdeftos] :
                      $tos_stack[$cur_insco] );

			if ($tosid == 0)
			{
				$this->Error("No default type of service for this proc $row[procdt]");
				$tos = "TOSXXXX";
			}
			else
			{
				$cur_tos = freemed::get_link_rec($tosid, "tos");
				if (!$cur_tos)
					$this->Error("Failed reading tos table");
				$tos = $cur_tos[tosname];
			}

			$this->edi_buffer .= $tos;
			$this->edi_buffer .= $this->record_terminator;
		}
		return;
	} // end genServiceSegment


	function CLM_Provider_Detail($procstack) {
		global $display_buffer;
		// 2310-a rendering provider if processing a physician group
		// all records in the stack should have the same physician
		// in each record if the control break is working correctly

		$ls2310 = "";

		$row = $procstack[0];

		
		if ($this->phygrp_row != 0)
		{
			// if billing a grp, the billing providerid is the id for the
			// group and each physician is considered a rendering provider
			$ls2310 .= "NM1*82*1*";
			$physician = CreateObject('FreeMED.Physician', $row[procphysician]);
			if (!$physician)
			{
				$this->Error("Physician failed in CLM provider detail");
				$ls2310 .= "PHYERROR $row[procphysician]";
				$ls2310 .= $this->record_terminator;
			}
			$phylname = $physician->local_record[phylname];
			$phylname = $this->CleanChar($phylname);
			$phyfname = $physician->local_record[phyfname];
			$phyfname = $this->CleanChar($phyfname);
		
			$ls2310 = $ls2310.$phylname."*";
			$ls2310 = $ls2310.$phyfname."*";
			$ls2310 .= "***";

			$provider_id = "0";
			$grp = $this->InsuranceCo->local_record[inscogroup];
			if (!$grp)
			{
				$name = $this->InsuranceCo->local_record[insconame];
				$this->Error("Failed getting inscogroup for $name");
			}

			$providerids = explode(":",$physician->local_record[phyidmap]);
			$provider_id = $providerids[$grp];

			$err="";
			if (fm_value_in_array($this->tier1,$this->NaicNo))
			{
				$provider_qualifier = "BS"; // tier 1 payer
				$provider_id = $this->PadNumber($provider_id,"0","10");
				$err = "Physician does not have a valid Provider ID for this insurer".
				$proverr = "PROVXXXXX";

			}
			else
			{
				$provider_qualifier = "FI";  // tier 2 payer
				$provider_id = $physician->local_record[physsn];
				$provider_id = $this->CleanNumber($provider_id);
				$err = "Physician does not have a valid SSN".
				$proverr = "SSNXXXXX";
			}

			if ($provider_id == "0" OR empty($provider_id))
			{
				$this->Error($err);
				$provider_id = $proverr;
			}

			$ls2310 .= $provider_qualifier;
			$ls2310 .= "*";
			$ls2310 .= $provider_id;
			$ls2310 .= $this->record_terminator; 

			$specid = $physician->local_record["physpe1"];	
			if ( ($specid == 0) OR (empty($specid)) )
			{
				$this->Error("Physician must have a specialty code");
				$specialty_code = "SPECXXXXXX";
			}
			else
			{
				$spec_row = freemed::get_link_rec($specid, "specialties");
				if (!$spec_row)
				{
					$this->Error("Error in Rendering provider getting specialty code");
					$specialty_code = "SPECXXXXXX";
				}
				$specialty_code = $spec_row[specname];
				$specialty_code = $this->CleanNumber($specialty_code); 
			}

			$ls2310 = $ls2310."REF*87*".$specialty_code.$this->record_terminator;

		
			
			
		} // end rendering provider 2310a


		// 2310-b facility if inpatient outpatient skilled nursing
		// 2310-c referring provider

		// common end
		if (!empty($ls2310))
		{
			$this->edi_buffer .= "LS*2310";
			$this->edi_buffer .= $this->record_terminator; 
			$this->edi_buffer .= $ls2310;
			$this->edi_buffer .= "LE*2310";
			$this->edi_buffer .= $this->record_terminator; 
		}
		return true;
	} // end claim provider detail

	function CLM_SecondaryIns($procstack) {
		global $display_buffer;
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;
		// when billing the secondary we need to the provide BOTH
		// the primary info with the amt paid by the primary along with 
		// secondary insurance info.

		// we order and control break on curcovcd and cov1 (primary) so if we are here
		// all the procedures in the stack should have the same primary coverage

		$insco1_row = 0;

		// start loop 2320. 

		// 2320-a holds the primary info and 2320-b holds the secondary info

	
		$this->edi_buffer .= "LS*2320";
		$this->edi_buffer .= $this->record_terminator;

		$this->edi_buffer .= "SBR*P*";

		if ($this->relationship_code == "00")
		{
        	$this->Error("Error - Relationship of insured/patient is not ANSI X12 Compliant!");
        	$this->edi_buffer = $this->edi_buffer."XX*";
		}
		else
		{
        	$this->edi_buffer = $this->edi_buffer.strtoupper($this->relationship_code)."*";
		}

		// all rows in stack should have the same primary
		$row = $procstack[0];
		
		$this_coverage = 0;
		$this_coverage = CreateObject('FreeMED.Coverage', $row[proccov1]);
		if (!$this_coverage)
		{
			$this->Error("Failed to create coverage processing secondary insurer");
			return false;
		}

		$ptinsgrp = $this_coverage->covpatgrpno;
		$ptinsgrp = $this->CleanChar($ptinsgrp);
		$ptinsgrp = $this->CleanNumber($ptinsgrp);
		if (empty($ptinsgrp))
		{
			$this->Error("Invalid group found billing secondary");
			$ptinsgrp = "GRPXXXXX";
		}
		$this->edi_buffer .= $ptinsgrp;
		
		// we would provide the plan name here but it's optional
		// also skip other optional data

		$this->edi_buffer .= "****";
		
		// they want to know if primary benefits have been exausted 
		// NOTE don't think we handle that in freemed.
		// this is probably a bad thing but we say NO
		$this->edi_buffer .= "N";
		$this->edi_buffer .= $this->record_terminator;
		
		// see if primary already paid anything
		$total_paid_byprimary = 00.00;

		$count = count($procstack);
		for ($i=0;$i<$count;$i++) // total up amt paid by primary for each
								  // for each procedure in the claim.
		{
			$row = $procstack[$i];

			$query = "SELECT * FROM payrec WHERE payrecproc='$row[id]' AND 
												payreclink='$row[proccov1]' AND
												payreccat='".PAYMENT."'";
			$pay_result = $sql->query($query) or DIE("Query failed");
			while ($pay_row = $sql->fetch_array($pay_result))
			{

				$total_paid_byprimary += $pay_row[payrecamt];
			}
		}

		// this record is required either way

		$this->edi_buffer = $this->edi_buffer."AMT*D*".$total_paid_byprimary;
		$this->edi_buffer .= $this->record_terminator;
	
		// subscriber dob and geneder. it may be the patient or guar
		// data if we have a guar	

		$this->edi_buffer .= "DMG*D8*";

		if ($this_coverage->covdep == 0)
		{
			// pat dob and gender
			$dob = $this->CleanNumber($this->CurPatient->ptdob);
			$this->edi_buffer = $this->edi_buffer.$dob."*";
			$sex = $this->CleanChar($this->CurPatient->ptsex);
			$this->edi_buffer = $this->edi_buffer.$sex;
			$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		}
		else
		{
			// guar dob and gender
			$dob = $this->CleanNumber($this->Guarantor->guardob);
			$this->edi_buffer = $this->edi_buffer.$dob."*";
			$sex = $this->CleanChar($this->Guarantor->guarsex);
			$this->edi_buffer = $this->edi_buffer.$sex;
			$this->edi_buffer = $this->edi_buffer.$this->record_terminator;

		}

		// start loop 2320-b. this specifies the secondary which is now acting
		// as the primary.
		//
		$this->InsuranceCoS = $this_coverage->covinsco;
		if (!$this->InsuranceCoS)
		{
			$this->Error("Failed to get Secondar ins class");
			return;
		}
		$inscoid = $this->InsuranceCoS->local_record[inscoid];
		if (empty($inscoid))
		{
			$this->Error("No NAIC Number for this Ins Co");
			$inscoid = "NAICXXXX";
		}

		//$this->edi_buffer = $this->edi_buffer.$inscoid.$this->record_terminator;

		$insname = $this->InsuranceCoS->local_record[insconame];
		$insaddr1 = $this->InsuranceCoS->local_record[inscoaddr1];
		$insaddr2 = $this->InsuranceCoS->local_record[inscoaddr2];
		$inscity = $this->InsuranceCoS->local_record[inscocity];
		$insstate = $this->InsuranceCoS->local_record[inscostate];
		$inszip = $this->InsuranceCoS->local_record[inscozip];
		$insname = $this->CleanChar($insname);
		$insaddr1 = $this->CleanChar($insaddr1);
		$insaddr2 = $this->CleanChar($insaddr2);
		$inscity = $this->CleanChar($inscity);
		$insstate = $this->CleanChar($insstate);
		$inszip = $this->CleanChar($inszip);
		$inszip = $this->CleanNumber($inszip);

		$this->edi_buffer .= "OI*BL**Y**P";
		$this->edi_buffer .= $this->record_terminator;
		$this->edi_buffer = $this->edi_buffer."NM1*PR*2*";
		$this->edi_buffer = $this->edi_buffer.$insname."*****NI*";
		$this->edi_buffer = $this->edi_buffer.$inscoid.$this->record_terminator;
		
		//$display_buffer .= "addr $insaddr1 $insaddr2 $insname<BR>";

		$this->edi_buffer = $this->edi_buffer."N3*".$insaddr1;
		if (!empty($insaddr2))
		{
        	$this->edi_buffer = $this->edi_buffer." ".$insaddr2;
        	$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		}
		else
		{
        	$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		}
		$this->edi_buffer = $this->edi_buffer."N4*".$inscity."*";
		$this->edi_buffer = $this->edi_buffer.$insstate."*";
		$this->edi_buffer = $this->edi_buffer.$inszip.$this->record_terminator;

		
		if ($this->Guarantor != 0)
		{
        	// patient has a guarantor thus guarantor is the insured.
			$last_name = $this->Guarantor->guarlname;
			$last_name = $this->CleanChar($last_name);
			$first_name = $this->Guarantor->guarfname;
			$first_name = $this->CleanChar($first_name);
			//$ptinsno = $this->CleanNumber($this_coverage->covpatinsno); // ID number on the card
			//$ptinsno = $this->CleanChar($ptinsno);
			$addr1 = $this->Guarantor->guaraddr1;
			$addr1 = $this->CleanChar($addr1);
			$addr2 = $this->Guarantor->guaraddr2;
			$addr2 = $this->CleanChar($addr2);
			$city = $this->Guarantor->guarcity;
			$city = $this->CleanChar($city);
			$state = $this->Guarantor->guarstate;
			$state = $this->CleanChar($state);
        	$zip = $this->CleanNumber($this->Guarantor->guarzip);
        	$dob = $this->CleanNumber($this->Guarantor->guardob);
			$sex = $this->CleanChar($this->Guarantor->guarsex);

		}
		else
		{
        	// patient is the insured.
			$last_name = $this->CurPatient->ptlname;
			$last_name = $this->CleanChar($last_name);
			$first_name = $this->CurPatient->ptfname;
			$first_name = $this->CleanChar($first_name);
			//$ptinsno = $this->CleanNumber($this->ptinsnoS); // ID number on the card
			//$ptinsno = $this->CleanChar($ptinsno);
			$addr1 = $this->CurPatient->local_record[ptaddr1];
			$addr1 = $this->CleanChar($addr1);
			$addr2 = $this->CurPatient->local_record[ptaddr2];
			$addr2 = $this->CleanChar($addr2);
			$city = $this->CurPatient->local_record[ptcity];
			$city = $this->CleanChar($city);
			$state = $this->CurPatient->local_record[ptstate];
			$state = $this->CleanChar($state);
        	$zip = $this->CleanNumber($this->CurPatient->local_record[ptzip]);
        	$dob = $this->CleanNumber($this->CurPatient->ptdob);
			$sex = $this->CleanChar($this->CurPatient->ptsex);


		}

		$ptinsno = $this->CleanNumber($this_coverage->covpatinsno); // ID number on the card
		$ptinsno = $this->CleanChar($ptinsno);

		$this->edi_buffer = $this->edi_buffer."NM1*IL*1*";
		$this->edi_buffer = $this->edi_buffer.$last_name."*";
		$this->edi_buffer = $this->edi_buffer.$first_name."*";
		$this->edi_buffer .= "***C1*";
		$this->edi_buffer = $this->edi_buffer.$ptinsno;
		$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		$this->edi_buffer .= "N3*";
		$this->edi_buffer .= $addr1;
		if (!empty($addr2))
		{
			$this->edi_buffer = $this->edi_buffer."*".$addr2;
			$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		}
		else
		{
			$this->edi_buffer = $this->edi_buffer.$this->record_terminator;
		}
		$this->edi_buffer = $this->edi_buffer."N4*".$city;
		$this->edi_buffer = $this->edi_buffer.$state."*";
		$this->edi_buffer = $this->edi_buffer.$zip.$record_terminator;
		$this->edi_buffer .= "DMG*D8*";
		$this->edi_buffer = $this->edi_buffer.$dob."*";
		$this->edi_buffer = $this->edi_buffer.$sex;
		$this->edi_buffer = $this->edi_buffer.$this->record_terminator;

		// end
		$this->edi_buffer .= "LE*2320";
		$this->edi_buffer .= $this->record_terminator;
		
		return;
	}

	//
	//
	// we may be able to put most of generate in the base class
	// then overridie it here, call the base class, the all the internal functions
	// provid = id of physician or id of phygroup

	function Generate($patid, $coverageid, $provid, $provtype) {
		global $display_buffer;
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;
	
		if ($patid <= 0)
		{
			$this->Error("error in generate no patient specified");
			return false;
		}
		if ($coverageid <= 0)
		{
			$this->Error("error in generate no coverageid specified");
			return false;
		}
		if ($provid <= 0)
		{
			$this->Error("error in generate no providerid specified");
			return false;
		}
		if ($provtype=="")
		{
			$this->Error("error in generate no providertype specified");
			return false;
		}
		if ($provtype!="PHY" AND $provtype!="GRP")
		{
			$this->Error("error in generate no invalid providertype specified");
			return false;
		}
	
		//$this->billing_providerid = $provid;

		$this_coverage = 0;
		$this_coverage = CreateObject('FreeMED.Coverage', $coverageid);
		if (!$this_coverage)
		{
			$this->Error("error in generate coverage class failed");
			return false;
		}
		$this->Coverage = $this_coverage;
		$bill_request_type = $this_coverage->covtype;

		if ($bill_request_type == PATIENT)
		{
			$this->Error("bill request type cant be zero");
			return false;  // only primary (1) or secondary(2)
		}

		if ($bill_request_type > SECONDARY)
		{
			$this->Error("error in generate cant support 3rd payer at this time");
			return false;  // only primary (1) or secondary(2)
		}

		$this->bill_request_type = $bill_request_type;
		$this->bill_coverageid = $this_coverage->id;
	
		// clear prev patient if any

		$this->fac_row = 0;
		$this->phygrp_row = 0;
		$this->ptinsno = 0;
		$this->ptinsnoS = 0;
		$this->ptinsgrp = 0;
		$this->ptinsgrpS = 0;
		$this->Guarantor = 0;
		$this->CurPatient = 0;
		$this->Physician = 0;
		$this->InsuranceCo = 0;
		$this->InsuranceCoS = 0;


		// gather up the main data here


		$this->CurPatient = CreateObject('FreeMED.Patient', $patid);
		if (!$this->CurPatient)
		{
			$this->Error("error in Generate Patient class failed");
	 	    return false;
		}

		$ptlname = $this->CurPatient->ptlname;
		$ptfname = $this->CurPatient->ptfname;
		$this->Error("processing $ptlname, $ptfname");

		//$ptdep = $this->CurPatient->ptdep;
		//$display_buffer .= "ptdep $ptdep<BR>";
		if ($this_coverage->covdep != 0)   // use this guarantors ins
		{
			$this->Guarantor = CreateObject('FreeMED.Guarantor', $this_coverage->covdep);
			if (!$this->Guarantor)
			{
				$this->Error("error in Generate Patient Guar class failed ");
				return false;
			}
		}
	
		$this->ptinsno = $this_coverage->covpatinsno;
		$this->ptinsgrp = $this_coverage->covpatgrpno;
		$this->InsuranceCo = $this_coverage->covinsco;

		if (!$this->InsuranceCo)
		{
			$this->Error("error in Generate: Insurance class failed");
	 	    return false;
		}
		else
		{
			$insnaic = $this->InsuranceCo->local_record[inscoid];
			if (empty($insnaic))
			{
				$name = $this->InsuranceCo->local_record[insconame];
				$this->Error("Warning No NAIC number for this ins co $name");
				$this->NaicNo = "NAICXXXX";
			}
			else
				$this->NaicNo = $insnaic;
		}

		//if ( ($this->InsuranceCoS == 0) AND ($this->bill_request_type > PRIMARY) )
		//{
		//	$this->Error("error in Generate: Insurance Secondary class failed");
	 	//    return false;
		//}

		if ($provtype=="GRP")
		{
			$this->phygrp_row = freemed::get_link_rec($provid,"phygroup");
			if (!$this->phygrp_row)
			{
				$this->Error("error in generate Failed to get phy group");
				return;
			}
			$this->group_physicians = $this->phygrp_row[phygroupdocs];
			$this->group_physicians = str_replace(":",",",$this->group_physicians);
			$this->fac_row = freemed::get_link_rec($this->phygrp_row[phygroupfac],"facility");
			if (!$this->fac_row)
			{
				$this->Error("error in generate Failed to get facility");
				return;
			}
			
		}
		if ($provtype=="PHY")
		{
			$this->Physician = CreateObject('FreeMED.Physician', $provid);
			if (!$this->Physician)
			{
				$this->Error("error in generate: Physician class");
				return false;
			}
		}
		
		$bills = $this->GetClaims(true);

		if ($bills <= 0)
		{
			// this should never happen if the driver of this
			// class is doing the right thing.
			$this->Error("Nothing to bill for this Patient");
			return false;
		}
	

		// WARNING. the calling order is mandatory!!!

		$this->StartTransaction();

		if (!($this->RecvrSubmitter($facility)))
		{
			$this->Error("Error in Generate: RecvrSubitter Failed");
			return false;
		}
		$this->Provider();
		$this->Insurer();
		$this->Insured();
		$this->Patient();
		$this->Claim();
		$this->EndTransaction();
		return true;
	}

	function PadNumber($data,$padchar,$len) {
		global $display_buffer;
		$strlen = strlen($data);
		
		if ($strlen >= $len)
			return $data;

		$pad = $len - $strlen;
		for ($i=0;$i<$pad;$i++)
		{
			$data = $padchar.$data;
		}
		
	}

	function PadChar($data,$padchar,$len) {
		global $display_buffer;
		$strlen = strlen($data);
		
		if ($strlen >= $len)
			return $data;

		$pad = $len - $strlen;
		for ($i=0;$i<$pad;$i++)
		{
			$data .= $padchar;
		}
		
		
	}
	function NewKey($row) {
		global $display_buffer;
		if ($this->phygrp_row != 0)
		{
        	$key = $row[procphysician].$row[proceoc].
					$row[procauth].$row[procrefdoc].
					$row[proccurcovid].$row[proccov1];
		}
		else
		{
        	$key = $row[procpos].$row[proceoc].$row[procauth].$row[procrefdoc].
						$row[proccurcovid].$row[proccov1];
		}
        return $key;

    }

} // end class HighmarkEDIModule

register_module("HighmarkEDIModule");

?>
