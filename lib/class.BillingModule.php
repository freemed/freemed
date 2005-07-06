<?php
	// $Id$
	// desc: module prototype
	// lic : GPL, v2

LoadObjectDependency('_FreeMED.BaseModule');

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
	function BillingModule () {
		// call parent constructor
		$this->BaseModule();
	} // end function BillingModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module))
		{
			trigger_error("No Module Defined", E_ERROR);
		}
		return true;
	} // end function check_vars

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $display_buffer;
		global $action;

		switch ($action) {
			case "add":
				$this->add();
				break;

			case "addform":
				$this->addform();
				break;

			case "del":
			case "delete":
				$this->del();
				break;

			case "mod":
			case "modify":
				$this->mod();
				break;

			case "modform":
				global $id;
				if (empty($id) or ($id<1)) {
					template_display();
				}
				$this->modform();
				break;

			case "transport":
				return $this->transport();
				break;

			case "view":
			default:
				$this->view();
				break;
		} // end switch action
	} // end function main

	// ********************** MODULE SPECIFIC ACTIONS *********************

	// function _add
	// - addition routine (can be overridden if need be)
	function _add () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$display_buffer .= "
			<P><CENTER>
			".__("Adding")." ...
		";

		$result = $sql->query (
			$sql->insert_query (
				$this->table_name,
				$this->variables
			)
		);

		if ($result) { $display_buffer .= "<B>".__("done").".</B>\n"; }
		 else        { $display_buffer .= "<B>".__("ERROR")."</B>\n"; }

		$display_buffer .= "
			</CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?module=$module\"
				>".__("back")."</A>
			</CENTER>
		";
	} // end function _add
	function add () { $this->_add(); }

	// function _del
	// - only override this if you *really* have something weird to do
	function _del () {
		global $display_buffer;
		global $id, $sql;
		$display_buffer .= "<P ALIGN=CENTER>".
			__("Deleting")." . . . \n";
		$query = "DELETE FROM $this->table_name ".
			"WHERE id = '".prepare($id)."'";
		$result = $sql->query ($query);
		if ($result) { $display_buffer .= __("done"); }
		 else        { $display_buffer .= "<FONT COLOR=\"#ff0000\">".__("ERROR")."</FONT>"; }
		$display_buffer .= "</P>\n";
	} // end function _del
	function del () { $this->_del(); }

	// function _mod
	// - modification routine (override if neccessary)
	function _mod () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$display_buffer .= "
			<P><CENTER>
			".__("Modifying")." ...
		";

		$result = $sql->query (
			$sql->update_query (
				$this->table_name,
				$this->variables,
				array (
					"id"	=>	$id
				)
			)
		);

		if ($result) { $display_buffer .= "<B>".__("done").".</B>\n"; }
		 else        { $display_buffer .= "<B>".__("ERROR")."</B>\n"; }

		$display_buffer .= "
			</CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?module=$module\"
				>".__("back")."</A>
			</CENTER>
		";
	} // end function _mod
	function mod() { $this->_mod(); }

	// function add/modform
	// - wrappers for form
	function addform () { $this->form(); }
	function modform () { $this->form(); }

	// function form
	// - add/mod form stub
	function form () {
		global $display_buffer;
		global $action, $id, $sql;

		if (is_array($this->form_vars)) {
			reset ($this->form_vars);
			while (list ($k, $v) = each ($this->form_vars)) global $$v;
		} // end if is array

		switch ($action) {
			case "addform":
				break;

			case "modform":
				$result = $sql->query ("SELECT * FROM ".$this->table_name.
					" WHERE ( id = '".prepare($id)."' )");
				$r = $sql->fetch_array ($result);
				extract ($r);
				break;
		} // end of switch action
		
	} // end function form

	// function view
	// - view stub
	function view () {
		global $display_buffer;
		global $sql;
		$result = $sql->query ("SELECT ".$this->order_fields." FROM ".
			$this->table_name." ORDER BY ".$this->order_fields);
		$display_buffer .= freemed_display_itemlist (
			$result,
			"module_loader.php",
			$this->form_vars,
			array ("", __("NO DESCRIPTION")),
			"",
			"t_page"
		);
	} // end function view

	function parent() {
		global $display_buffer;
		$display_buffer .= "parent parent<BR>";
	}


	function MakeDecimal($data,$places) {
		global $display_buffer;
		$data = bcadd($data,0,$places);
		$data = $this->CleanNumber($data);
		return $data;
	}


	// NewKey
	// Create billing control break key
	function NewKey($row) {
		global $display_buffer;
		// if any of these fields change while processing a
        // bill we need to cut a new bill

		$pos = $row["procpos"];
		$doc = $row["procphysician"];
		$ref = $row["procrefdoc"];
		$auth = $row["procauth"];
		$eoc = $row["proceoc"];
		$clmtp = $row["procclmtp"];
		$cov1 = $row["proccov1"];
		$cov2 = $row["proccov2"];

		$date = $row["procdt"];
		$date = str_replace("-","",$date);
		$datey = substr($date,0,4);
		$datem = substr($date,4,2);
		$newkey = $pos.$doc.$ref.$eoc.$clmtp.$auth.$cov1.$cov2.$datey.$datem;
		return $newkey;

	} // end newkey


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

	// process the resulst set from above and call ProcCallBack
    // function at every control break passing it the like 
    // procedures in an array. User should suppliy the
    // the ProcCallBack method
	function MakeStack($result,$maxloop) {
		global $display_buffer;
		global $sql;

		if (!$result) {
			return 0;
		}
		if (!$maxloop) {
			return 0;
		}

		$first_procedure = 0;
		$proccount=0;
		$totprocs = 0;
    		$diagset = CreateObject('FreeMED.diagnosis_set');
		while ($r = $sql->fetch_array ($result)) 
		{
			if ($first_procedure == 0)
			{
				$prev_key = $this->NewKey($r);
				$diagset = CreateObject('FreeMED.diagnosis_set');
				$first_procedure = 1;
			}

			// keep tally on all bills billed for this patient
			$pat = $r[procpatient];
			$this->patient_procs[$pat][$totprocs] = $r[id];
			$totprocs++;

			$cur_key = $this->NewKey($r);

			if (!($diagset->testAddSet ($r[procdiag1], $r[procdiag2],
										$r[procdiag3], $r[procdiag4])) OR
					($proccount == $maxloop         )  OR
					($prev_key != $cur_key) )
			{
				if ($prev_key != $cur_key)
				{
					$prev_key = $cur_key;
					//$display_buffer .= "keychange $r[procphysician]<BR>";
				}

				$this->ProcCallBack($procstack);
				//call_user_method($callbackfunc,$callbackobj,$procstack);
				//$callback($procstack);
				// reset the diag_set array
				unset ($diagset);
				unset ($procstack);
				$proccount=0;
				$diagset = CreateObject('FreeMED.diagnosis_set');
				$test_AddSet = $diagset->testAddSet ($r[procdiag1], 
								$r[procdiag2], 
								$r[procdiag3], 
								$r[procdiag4]);
				if (!$test_AddSet) {
					$display_buffer .= "AddSet failed!!";
					template_display();
				}

			} 

			$procstack[$proccount] = $r;
			$proccount++;



		} // end of looping for all charges

		// check for last record
		if ($proccount > 0)
		{
			//call_user_method($callbackfunc,$callbackobj,$procstack);
			$this->ProcCallBack($procstack);
		}

	} // end Makestack

   
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
