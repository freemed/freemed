<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__MODULE_BILLING_PHP__")) {

define ('__MODULE_BILLING_PHP__', true);

include "lib/render_forms.php";
include "lib/calendar-functions.php";

// class freemedBillingModule extends freeMedmodule
class freemedBillingModule extends freemedModule {

	// override variables
	var $CATEGORY_NAME = "Billing";
	var $CATEGORY_VERSION = "0.1";

	// vars to be passed from child modules
	var $order_field;
	var $form_vars;
	var $table_name;
    var $patient_forms;  // array of patient id's that we processed
    var $patient_procs;  // 2d array [patient][ids of procs processed]

	// contructor method
	function freemedBillingModule () {
		// call parent constructor
		$this->freemedModule();
	} // end function freemedBillingModule

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
					freemed_display_box_bottom ();
					freemed_display_html_bottom ();
					die ("");
				}
				$this->modform();
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
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		echo "
			<P><CENTER>
			<$STDFONT_B>"._("Adding")." ...
		";

		$result = $sql->query (
			$sql->insert_query (
				$this->table_name,
				$this->variables
			)
		);

		if ($result) { echo "<B>"._("done").".</B>\n"; }
		 else        { echo "<B>"._("ERROR")."</B>\n"; }

		echo "
			<$STDFONT_E></CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?$_auth&module=$module\"
				><$STDFONT_B>"._("back")."<$STDFONT_E></A>
			</CENTER>
		";
	} // end function _add
	function add () { $this->_add(); }

	// function _del
	// - only override this if you *really* have something weird to do
	function _del () {
		global $STDFONT_B, $STDFONT_E, $id, $sql;
		echo "<P ALIGN=CENTER>".
			"<$STDFONT_B>"._("Deleting")." . . . \n";
		$query = "DELETE FROM $this->table_name ".
			"WHERE id = '".prepare($id)."'";
		$result = $sql->query ($query);
		if ($result) { echo _("done"); }
		 else        { echo "<FONT COLOR=\"#ff0000\">"._("ERROR")."</FONT>"; }
		echo "<$STDFONT_E></P>\n";
	} // end function _del
	function del () { $this->_del(); }

	// function _mod
	// - modification routine (override if neccessary)
	function _mod () {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		echo "
			<P><CENTER>
			<$STDFONT_B>"._("Modifying")." ...
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

		if ($result) { echo "<B>"._("done").".</B>\n"; }
		 else        { echo "<B>"._("ERROR")."</B>\n"; }

		echo "
			<$STDFONT_E></CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?$_auth&module=$module\"
				><$STDFONT_B>"._("back")."<$STDFONT_E></A>
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
		global $sql;
		$result = $sql->query ("SELECT ".$this->order_fields." FROM ".
			$this->table_name." ORDER BY ".$this->order_fields);
		echo freemed_display_itemlist (
			$result,
			"module_loader.php",
			$this->form_vars,
			array ("", _("NO DESCRIPTION")),
			"",
			"t_page"
		);
	} // end function view

	function parent()
	{
		echo "parent parent<BR>";
	}


	function MakeDecimal($data,$places)
	{
		$data = bcadd($data,0,$places);
		$data = $this->CleanNumber($data);
		return $data;
	}


	// NewKey
	// Create billing control break key
	function NewKey($row)
	{
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

	// see if any insurance bills are due
	function CheckforInsBills($covtypes="")
	{
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
		//echo "$where<BR>";

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
	function GetProcstoBill($covid,$covtype,$covpatient)
	{
		global $sql;

		if (!$covid)
			return 0;
		if (!$covtype)
			return 0;
		if (!$covpatient)
			return 0;

		$query = "SELECT * FROM procrec
			WHERE (proccurcovtp = '$covtype' AND
			proccurcovid = '$covid' AND
			procbalcurrent > '0' AND
			procpatient = '$covpatient' AND
			procbillable = '0' AND
			procbilled = '0')
			ORDER BY procpos,procphysician,procrefdoc,proceoc,procclmtp,procauth,proccov1,proccov2,procdt";

		$result = $sql->query($query);
		if (!$sql->results($result))
			return 0;
		else
			return $result;

	}

	// process the resulst set from above and call ProcCallBack
    // function at every control break passing it the like 
    // procedures in an array. User should suppliy the
    // the ProcCallBack method
	function Makestack($result,$maxloop)
	{
		global $sql;

		if (!$result)
			return 0;
		if (!$maxloop)
			return 0;

		$first_procedure = 0;
		$proccount=0;
		$totprocs = 0;
    	$diagset = new diagnosisSet();
		while ($r = $sql->fetch_array ($result)) 
		{
			if ($first_procedure == 0)
			{
				$prev_key = $this->NewKey($r);
				$diagset = new diagnosisSet();
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
					//echo "keychange $r[procphysician]<BR>";
				}

				$this->ProcCallBack($procstack);
				//call_user_method($callbackfunc,$callbackobj,$procstack);
				//$callback($procstack);
				// reset the diag_set array
				unset ($diagset);
				unset ($procstack);
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

		// check for last record
		if ($proccount > 0)
		{
			//call_user_method($callbackfunc,$callbackobj,$procstack);
			$this->ProcCallBack($procstack);
		}

	} // end Makestack

   
	// mark all the procedures as billed and add a 
	// billed ledger entry for each procedure 

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
	} // end markbilled

	

	function ShowBillsToMark($preview=1)
   	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		reset ($this->rendorform_variables);
		while (list($k,$v)=each($this->renderform_variables)) global $$v;
   		#################### TAKE THIS OUT AFTER TESTING #######################
   		#echo "<PRE>\n".prepare($form_buffer)."\n</PRE>\n";
   		########################################################################

		if ($preview)
		{
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
		}

		// <CENTER>
		// <$STDFONT_B><B>"._("Mark as Billed")."</B><$STDFONT_E>
		// </CENTER>
		// present the form so that we can mark as billed
		echo "
		<BR>
		<FORM ACTION=\"$this->page_name\" METHOD=POST>
		 <INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"$_auth\">
		 <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
		 <INPUT TYPE=HIDDEN NAME=\"viewaction\" VALUE=\"mark\">
		 <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"$module\">
		 <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"$been_here\">
		 <INPUT TYPE=HIDDEN NAME=\"billtype\" VALUE=\"$bill_request_type\">
		";
		for ($i=1;$i<=$this->pat_processed;$i++) 
		{
			$this_patient = new Patient ($this->patient_forms[$i]);
			echo "
			<INPUT TYPE=CHECKBOX NAME=\"processed$brackets\" 
			VALUE=\"".$this->patient_forms[$i]."\" CHECKED>
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

   		} // end looping for all patient procs

		echo "
		<P>
		<INPUT TYPE=SUBMIT VALUE=\""._("Mark as Billed")."\">
		</FORM>
		<P>
		";

		
	} // end ShowMarkBilled


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


	}



} // end class freemedBillingModule

} // end if not defined

?>
