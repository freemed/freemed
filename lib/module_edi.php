<?php
 // $Id$
 // desc: module prototype for EDI
 // lic : GPL, v2

// CURRENTLY, THIS IS A STUB, AND SHOULD NOT BE USED UNTIL IT IS
// FLESHED OUT                              -- THE MANAGEMENT :)

// EDI GLOBALS for freemed.php (global_var.inc)
// these are for testing till the reall stuff can be done
$BILLING_SERVICE="yes";
$EDI_VENDOR="EDIVNDID";
$EDI_SOURCE_NUMBER="ISRCN";
$EDI_INTERCHANGE_SENDER_ID="INCHGSNDID";
$EDI_INTERCHANGE_RECVR_ID="54771";        // highmark
$EDI_INTERCHANGE_CNTLNUM="INTCHGCTLNUM";
$EDI_TESTORPROD = "T";

if (!defined("__MODULE_EDI_PHP__")) {

define (__MODULE_EDI_PHP__, true);

// class freemedEDIModule
class freemedEDIModule extends freemedModule {

	// override variables
	var $CATEGORY_NAME = "Electronic Data Interchange";
	var $CATEGORY_VERSION = "0.1";

	// vars related to this edi class
	var $record_terminator;
	var $start_envelope; // this holds the ISA/GC headers
	var $end_envelope;   // this holds the ISA/GC trailer (GS/IEA)
	var $error_buffer;   // stack error messages
	var $edi_buffer;     // stack edi output
	var $sourceid;         // transmission source id
	var $billing_service;  // some records may need to know this
	var $interchange_senderid;  // all from your edi clearinghouse
	var $interchange_recvrid;
	var $interchange_cntrlnum;
	var $testorprod;          // sending test data only T or P

	// contructor method
	function freemedEDIModule () 
	{
		global $BILLING_SERVICE, $EDI_VENDOR, $EDI_SOURCE_NUMBER, $EDI_INTERCHANGE_SENDER_ID;
        global $EDI_INTERCHANGE_RECVR_ID, $EDI_INTERCHANGE_CNTLNUM, $EDI_TESTORPROD;

		// call parent constructor
		$this->freemedModule();

		$this->transaction_reference_number = "0";
		$this->current_transaction_set = "0000";
		$this->record_terminator = "~";
		$this->edi_buffer = "";
		$this->error_buffer = "";
		$this->start_envelope = "";
		$this->end_envelope = "";
		$this->billing_service = $BILLING_SERVICE;
		$this->vendorid = $EDI_VENDOR;
		$this->sourceid = $EDI_SOURCE_NUMBER;
		$this->interchange_senderid = $EDI_INTERCHANGE_SENDER_ID;
		$this->interchange_recvrid  = $EDI_INTERCHANGE_RECVR_ID;
		$this->interchange_cntrlnum = $EDI_INTERCHANGE_CNTLNUM;
		$this->testorprod = $EDI_TESTORPROD;


	} // end function freemedEDIModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		echo "checkvar of base $module<BR>";
		if (!isset($module)) return false;
		return true;
	} // end function check_vars

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $action, $patient, $LoginCookie;

		if (!isset($this_user))
			$this->this_user    = new User ($LoginCookie);

		switch ($action) {

			case "display";
				$this->display();
				break;

			case "view":
			default:
				$this->view();
				break;
		} // end switch action

	} // end function main

	// ********************** MODULE SPECIFIC ACTIONS *********************

	// function display
	// by default, a wrapper for view
	function display () 
	{ 
		$this->view(); 
	}
	function view () 
	{ 
		return; 
	}

	// ********************** EDI SPECIFIC ACTIONS *********************
	function StartISAHeader()
	{
		
		if ( (empty($this->vendorid))    OR
			 (empty($this->sourceid)) OR
			 (empty($this->interchange_senderid)) OR
			 (empty($this->interchange_recvrid)) OR
			 (empty($this->interchange_cntrlnum)) )
		{
				$this->Error("EDI Variables have not been set!!");
				return false;
		}

		$ten_blanks = "..........";
		$ten_blanks = str_replace("."," ",$ten_blanks);

		$this->start_envelope = $this->start_envelope."ISA*00*".$ten_blanks."*00*".$ten_blanks;
		$this->start_envelope = $this->start_envelope."*ZZ*".$this->interchange_senderid;
		$this->start_envelope = $this->start_envelope."*33*".$this->interchange_recvrid;
		$this->start_envelope = $this->start_envelope."*".gmdate("ymd")."*";
		$this->start_envelope = $this->start_envelope.gmdate("Hi")."*";
		$this->start_envelope = $this->start_envelope."U*03305*".$this->interchange_cntrlnum;
		$this->start_envelope = $this->start_envelope."*1*".$this->testorprod."*:";
		$this->start_envelope .= $this->record_terminator;

		
		// functional group header.
		
		$this->start_envelope = $this->start_envelope."GS*HC*";
		$sourceid = $this->sourceid;
		$len = strlen($sourceid);

		if ($len < 7)
		{
			// zero fill
			$diff = 7 - $len;
			$work = "";
			for ($i=0;$i<$diff;$i++)
			{
				$work .= "0";
			}
			$sourceid = $sourceid.$work;

		}
		$this->start_envelope = $this->start_envelope.$sourceid."*";
		$this->start_envelope = $this->start_envelope.$this->interchange_recvrid."*";
		$this->start_envelope = $this->start_envelope.gmdate(ymd)."*";
		$this->start_envelope = $this->start_envelope.gmdate(Hi)."*";
		// this is the same the transaction seq num. we hard code it assuming
		// we are not sending more than 1 functional group
		$this->start_envelope = $this->start_envelope."01*X*003051".$this->record_terminator;
		
		
		return true;
	}

	function EndISAHeader()
	{
		$this->end_envelope = $this->end_envelope."GE*".$this->current_transaction_set."*";
		$this->end_envelope .= $this->interchange_cntrlnum;
		$this->end_envelope .= $this->record_terminator;

		// NOTE: again the functional group number is 1. 

		$this->end_envelope = $this->end_envelope."IEA*01*".$this->interchange_cntrlnum;
		$this->end_envelope .= $this->record_terminator;
		return true;
		
		
	
	}

	function EDIOpen()
	{
		$ret = $this->StartISAHeader();
		return $ret;

	}

	function EDIClose()
	{
		$ret = $this->EndISAHeader();
		return $ret;

	}


	function StartTransaction()
	{
		$random = rand(1,99);
		$this->Error("Random = $random");
		
		if ($this->transaction_reference_number != 0)
		{
			//try to ensure no dupes. no guarantees tho
			while($random == $this->transaction_reference_number)
			{
				$random = rand(1,99);
			}
		}

		if (strlen($random)<2)
		{	
			$this->transaction_reference_number = 
			"0".$random;

		}
		else
		{
			$this->transaction_reference_number = $random;

		}

		$this->current_transaction_set++;
		$this->edi_buffer .= "ST*837*".$this->current_transaction_set.$this->record_terminator;
		$this->edi_buffer .= "BGN*00*";
		$this->edi_buffer .= $this->transaction_reference_number;
		$this->edi_buffer .= "*";
		$this->edi_buffer .= gmdate("ymd");
		$this->edi_buffer .= "*";
		$this->edi_buffer .= gmdate("his");
		$this->edi_buffer .= $this->record_terminator;
		
		// this code
		$this->edi_buffer .= "REF*F1*2BS~";

		$this->edi_buffer .= "REF*VR*";
		$this->edi_buffer .= strtoupper($this->vendorid); 
		$this->edi_buffer .= $this->record_terminator;
		//$ret = $this->RecvrSubmitter();
		return true;
		

	} // end StartTrans


	function EndTransaction()
	{
		$segcount = "00";
		$ST_seg = "ST*837*".$this->current_transaction_set.$this->record_terminator;
		$gotseg = false;

		$edi_records = explode($this->record_terminator,$this->edi_buffer);
		$edirec_count = count($edi_records);
		$edirec_count--;  // subtract the last this->record_terminator.

		for ($i=0;$i<$edirec_count;$i++)
		{
				if ($gotseg)
					$segcount++;
					
				if ($edi_records[$i] == $ST_seg)
					$gotseg = true;
		}
		$this->edi_buffer = $this->edi_buffer."SE*".$segcount."*";
		$this->edi_buffer .= $this->current_transaction_set;
		$this->edi_buffer .= $this->record_terminator;
		

	} // end of EndTrans
	
	function RecvrSubmitter($fac)
	{
		$entity = ($fac) ? 2 : 1;


		if (empty($this->sourceid))
		{
			$this->Error("Invalid EDI sourceid in RecvrSubmitter function!");
			return false;
		}

		$this->edi_buffer .= "NM1*41*";
		$this->edi_buffer = $this->edi_buffer.$entity."******94*";
		$this->edi_buffer .= strtoupper($this->sourceid);
		$this->edi_buffer .= $this->record_terminator;
		$this->edi_buffer =  $this->edi_buffer."NM1*40*2******94*865".$this->record_terminator;
		return true;
		
	} // end of RecvSubmitter


    function PutEDITBufferToFile()
	{
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;

		$filename = PHYSICAL_LOCATION_BILLS."/bills-".$cur_date.gmdate("Hi").".data";
		$this->Error("Writing bills to $filename");
		
		$fp = fopen($filename,"w");

		if (!$fp)
		{
			$this->Error("Error opening $filename");
			return;
		}

		$buffer = $this->start_envelope.$this->edi_buffer.$this->end_envelope;
		$rc = fwrite($fp,$buffer);

		if ($rc <= 0)
		{
			$this->Error("Error writing $filename");
			return;
		}
		$this->Error("Wrote bills to $filename");
		return;


	} // end putedibuffertofile


	function GetEDIBuffer($stream=false)
	{
		if ($stream)
		{
			$buffer = $this->start_envelope.$this->edi_buffer.$this->end_envelope;
			return $buffer;
		}

		$edi_records = explode($this->record_terminator,$this->edi_buffer);
		$edirec_count = count($edi_records);
		$edirec_count--;  // subtract the last this->record_terminator.
		$this->Error("info - edi rec count = $edirec_count");
		$buffer = $buffer.$this->start_envelope."<br>";
		for ($i=0;$i<$edirec_count;$i++)
		{
				
        		$buffer .= "$edi_records[$i]~<br>";
		}

		$buffer .= $this->end_envelope."<br>";

		return $buffer;

	} // end getedibuffer


	function GetEDIErrors()
	{

		$edi_records = explode($this->record_terminator,$this->error_buffer);
		$edirec_count = count($edi_records);
		$edirec_count--;  // subtract the last this->record_terminator.
		for ($i=0;$i<$edirec_count;$i++)
		{
				
        		$buffer .= "$edi_records[$i]<br>";
		}

		return $buffer;

	} // end get edierrors


	function Error($errormsg)
	{
		$this->error_buffer .= $errormsg;
		$this->error_buffer .= $this->record_terminator;

	} // end Error
	
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
			$data = stripslashes($data);
			$data = str_replace(".","",$data);
			$data = str_replace(",","",$data);
			$data = str_replace("(","",$data);
			$data = str_replace(")","",$data);
			$data = str_replace("-","",$data);
			$data = str_replace(" ","",$data);
			$data = trim($data);
			return $data;
	} // end cleannumber


} // end class freemedEDIModule

} // end if not defined

?>
