<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

LoadObjectDependency('org.freemedsoftware.core.BaseModule');

class BillingModule extends BaseModule {

	// override variables
	var $CATEGORY_NAME = "Billing";
	var $CATEGORY_VERSION = "0.2";

	// vars to be passed from child modules
	var $order_field;
	var $form_vars;
	var $table_name;
	var $patient_forms;  // array of patient id's that we processed
	var $patient_procs;  // 2d array [patient][ids of procs processed]

	// contructor method
	public function __construct ( ) {
		// call parent constructor
		parent::__construct ();
	} // end function BillingModule

	// all reporting data must stipped of junk
	// and all upper cased
	function CleanChar($data) {
		global $display_buffer;
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
	function CleanNumber($data) {
		global $display_buffer;

		$data = $this->CleanChar($data);
		$data = str_replace(" ","",$data);
		$data = trim($data);
		return $data;

	} // end cleannumber

	// see if any insurance bills are due
	function CheckforInsBills($covtypes="") {
		global $display_buffer;
		global $sql;

		if (empty($covtypes))
			$covs = array(PRIMARY,SECONDARY);
		else
			$covs = $covtypes;

		$c = count($covs);
		$where = "WHERE (";
		for ($i=0;$i<$c;$i++)
		{
			if ($i != 0)
				$where .= " OR ";
			$where .= "proccurcovtp='".$covs[$i]."'";

		}
		$where .= ")";
		//$display_buffer .= "$where<BR>";

	//		"WHERE (proccurcovtp='".PRIMARY."' OR proccurcovtp='".SECONDARY."')".
		$query = "SELECT DISTINCT procpatient,proccurcovid,proccurcovtp FROM procrec ".
			$where.
			" AND procbilled='0' AND procbillable='0' AND procbalcurrent>'0'";
		$result = $sql->query($query);
		if (!$sql->results($result))
			return 0;
		else
			return $result;

	}

	// get a list of bills due for this patient
    // coverage and type
	function GetProcstoBill($covid,$covtype,$covpatient,$forpat=0) {
		global $display_buffer;
		global $sql;

		if ($forpat==0) { // not doing patient bills
			if (!$covid)
				return 0;
			if (!$covtype)
				return 0;
		}
		if (!$covpatient) {
			return 0;
		}

		$query = "SELECT * FROM procrec ".
			"WHERE (proccurcovtp = '$covtype' AND ".
			"proccurcovid = '$covid' AND ".
			"procbalcurrent > '0' AND ".
			"procpatient = '$covpatient' AND ".
//			"procbillable = '0' AND ".
			"procbilled = '0') ".
			"ORDER BY procpos,procphysician,procrefdoc,proceoc,procclmtp,procauth,proccov1,proccov2,procdt";

		$result = $sql->query($query);

		if (!$sql->results($result)) {
			return 0;
		} else {
			return $result;
		}
	}

	// mark all the procedures as billed and add a 
	// billed ledger entry for each procedure 

	function MarkBilled() {
		global $display_buffer;

		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

	   	if (count($processed)<1) 
		{
			$display_buffer .= "
		 	<P>
		 	<CENTER>
		  	<B>".__("Nothing set to be marked!")."</B>
		 	</CENTER>
		 	<P>
		 	<CENTER>
		  	<A HREF=\"$this->page_name?module=$module\"
		  	>".__("Return to Fixed Forms Generation Menu")."</A>
		 	</CENTER>
		 	<P>
			";
			return;
       	} 

     	for ($i=0;$i<count($processed);$i++) 
		{
       		$display_buffer .= "
       		Marking ".$processed[$i]." ...<BR> 
       		";
			$pat = $processed[$i];
			$procs = count($procids[$pat]);
			
			for ($x=0;$x<$procs;$x++)
			{
				$prc = $procids[$pat][$x];
				//$display_buffer .= "proc $prc for patient $pat<br/>";
       				// start of insert loop for billed legder entries
       				$query = "SELECT procbalcurrent,proccurcovid,proccurcovtp FROM procrec";
				$query .= " WHERE id='".$prc."'";
	       			$result = $sql->query($query);
       				if (!$result) {
	       				$display_buffer .= "Mark failed getting procrecs<br/>";
					template_display();
       				}
				//$display_buffer .= "proc query $query<BR>";
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
				//$display_buffer .= "payrec insert query $query<BR>";
           			$pay_result = $sql->query ($query);
	           		if ($pay_result) {
		               		$display_buffer .= __("Adding Bill Date to ledger.")."<br/>\n";
	           		} else {
               				$display_buffer .= __("Failed Adding Bill Date to ledger!!")."<br/>\n";
				}

       				$query = "UPDATE procrec SET procbilled = '1',procdtbilled = '".addslashes($cur_date)."'".
						 " WHERE id = '".$prc."'";
				//$display_buffer .= "procrec update query $query<BR>";
       				$proc_result = $sql->query ($query);
       				if ($result) { 
					$display_buffer .= __("done").".<BR>\n"; 
				} else { 
					$display_buffer .= __("ERROR")."<BR>\n"; 
				}

			} // end proces for patient loop
		
	     	} // end for processed
     	$display_buffer .= "
      	<P>
      	<CENTER>
       	<A HREF=\"$this->page_name?module=$module\"
       	>".__("Back")."</A>
      	</CENTER>
      	<P>
     	";
	} // end markbilled

	function GetRelationShip($rel,$type="NSF") {
		global $display_buffer;
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


	}

} // end class BillingModule

?>
