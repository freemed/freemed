<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__GENERATE_STATEMENTS_MODULE_PHP__")) {

define (__GENERATE_STATEMENTS_MODULE_PHP__, true);

// class GenerateStatementsModule extends freemedModule
class GenerateStatementsModule extends freemedBillingModule {

	// override variables
	var $MODULE_NAME = "Generate Statement Billing";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";

	var $PACKAGE_MINIMUM_VERSION = "0.2.1";

	var $CATEGORY_NAME = "Billing";
	var $CATEGORY_VERSION = "0.1";

    var $form_buffer;
    var $pat_processed;
	var $formno;
	var $renderform_variables = array(
		"stmnt",
		"itemdate",
		"itemcharges",
		"itemcpt",
		"itemdesc",
		"itempaid",
		"itemprice",
		"itembal",
		"totitemcharges",
		"totitemprice",
		"totitempaid",
		"totitembal"
		);

	// contructor method
	function GenerateStatementsModule ($nullvar = "") {
		// call parent constructor
		$this->freemedBillingModule($nullvar);
	} // end function GenerateStatementsModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) return false;
		return true;
	} // end function check_vars

	// override main function

	function addform() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		if (!$been_here)
		{
			$this->view();
			return;
		}

/*
		$this->form_buffer = "";
		$str = "ABCDEFGHIJ";
		for ($i=0;$i<70;$i++)
		{
			$this->form_buffer .= ($i+1);
			for ($x=0;$x<10;$x++)
			{
				$this->form_buffer .= $str;
			}	
			$this->form_buffer .= "\n";
		}
		$this->ShowBillsToMark();
		return;
*/

		if ($viewaction=="geninsform")
		{
		    $this->form_buffer = "";
			$this->pat_processed = 0;
			// patient bills
			$query = "SELECT DISTINCT procpatient FROM procrec WHERE proccurcovtp='0'
						AND procbalcurrent>'0' AND procbilled='0'";
			$result = $sql->query($query);
			if (!$sql->results($result)) 
			{
				$display_buffer .= "No patients to be billed.<BR>\n";
				template_display();
			}
		
			while($row = $sql->fetch_array($result))
			{	
				$this->GenerateFixedForms($row[procpatient], 0);
			}

			//$this->form_buffer = strtoupper($this->form_buffer);

			if ($this->pat_processed > 0)
			{
				$this->ShowBillsToMark();
			}
			else
			{
				$display_buffer .= "
				<P>
				<CENTER>
				<B>"._("Nothing to Bill!")."</B>
				</CENTER>
				<P>
				<CENTER>
				<A HREF=\"$this->page_name?module=$module\"
				>"._("Return to Statement Generation Menu")."</A>
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
		trigger_error("Bad action passed in generate statements module", E_USER_ERROR);

	}
	
	function GenerateFixedForms($parmpatient, $parmcovid) {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		reset ($this->renderform_variables);
		while (list($k,$v)=each($this->renderform_variables)) global $$v;

	    // zero the buffer 
	    $buffer = "";
     	// get current patient information

     	$this_patient = CreateObject('FreeMED.Patient', $parmpatient);
        if (!$this_patient)
			trigger_error("Failed retrieving patient", E_USER_ERROR);
			
     	$display_buffer .= "
      	<B>"._("Processing")." ".$this_patient->fullName()."
      	</B><BR>\n\n
     	";
     	flush ();

        // grab form information form
        $this->formno = freemed::get_link_rec ($whichform, "fixedform");

		// current date hashes
		$curdate[mmddyy]   = date ("m d y");
		$curdate[mmddyyyy] = date ("m d Y");
		$curdate[m]        = date ("m");
		$curdate[d]        = date ("d");
		$curdate[y]        = date ("Y");
		$curdate[sy]       = substr ($curdate[y], 2, 2);

		// grab all the procedures to bill for this patient
		$result = $this->GetProcstoBill(0,0,$parmpatient,1);

		$rowcount = $sql->num_rows($result);
		if ( ($rowcount <= 0) OR ($result==0) )
			trigger_error("Should have bills for $this_patient->local_record[ptid]");

		
		// procedure callback function will handle all the data
		$this->MakeStack($result,$this->formno[ffloopnum]);
		$this->pat_processed++;
		$this->patient_forms[$this->pat_processed] = $parmpatient;




   } // end generateFixed


	function Provider($stack) {
		global $display_buffer;
		reset ($this->renderform_variables);
		while (list($k,$v)=each($this->renderform_variables)) global $$v;
		global $sql;

		$row = $stack[0];
		$doc = CreateObject('FreeMED.Physician', $row[procphysician]);

		$stmnt[phyname] = $doc->fullName();
		
	}

	function Facility($stack) {
		global $display_buffer;
		reset ($this->renderform_variables);
		while (list($k,$v)=each($this->renderform_variables)) global $$v;
		global $sql, $SESSION;
		
		$row = $stack[0];
		$fac = $SESSION["default_facility"];

		if ($fac <= 0)
		{
			$display_buffer .= "No default facility using procedure POS<BR>";	
			$fac = $row[procpos];
		}

		$facility = freemed::get_link_rec($fac,"facility");
		
		$stmnt[facname] = $facility[psrname];
		$stmnt[facaddr1] = $facility[psraddr1];
		$stmnt[facaddr2] = $facility[psraddr2];
		$stmnt[facaddr3] = $facility[psrcity].", ".$facility[psrstate].". ".$facility[psrzip];
		$stmnt[facphone] = $facility[psrphone];
		

	}
	
   	function Patient($stack) {
		global $display_buffer;
		// patient/insurance section is top half of form

		reset ($this->renderform_variables);
		while (list($k,$v)=each($this->renderform_variables)) global $$v;
		global $sql;

		$row = $stack[0];


		// get current patient information
		$this_patient = CreateObject('FreeMED.Patient', $row[procpatient]);
		if (!$this_patient)
			trigger_error("Failed retrieving patient", E_USER_ERROR);

		$ptname = $this_patient->fullName();
		$stmnt[patname] = $ptname;

     	$ptdob[full]     = $this_patient->ptdob;
     	$ptdob[month]    = substr ($ptdob[full], 5, 2);  
     	$ptdob[day]      = substr ($ptdob[full], 8, 2);  
     	$ptdob[year]     = substr ($ptdob[full], 0, 4);
     	$ptdob[syear]    = substr ($ptdob[full], 2, 2);
     	$ptdob[mmddyy]   = $ptdob[month].
                        $ptdob[day].
                        $ptdob[syear];
     	$ptdob[mmddyyyy] = $ptdob[month].
                        $ptdob[day].
                        $ptdob[year];
     	$ptsex[male]     = ( ($this_patient->ptsex == "m") ?
                           $this->formno[ffcheckchar] : " " );
     	$ptsex[female]   = ( ($this_patient->ptsex == "f") ?
                           $this->formno[ffcheckchar] : " " );
     	$ptsex[trans]    = ( ($this_patient->ptsex == "t") ?
                           $this->formno[ffcheckchar] : " " );
     	$ptssn           = $this_patient->local_record["ptssn"];
		
     	// address information
     	$city    = $this_patient->local_record["ptcity"   ];
     	$state   = $this_patient->local_record["ptstate"  ];
     	$zip     = $this_patient->local_record["ptzip"    ];
		
		$stmnt[pataddr1] = $this_patient->local_record["ptaddr1"  ];
		$stmnt[pataddr2] = $this_patient->local_record["ptaddr2"  ];
		$stmnt[pataddr3] = $city.", ".$state.". ".$zip;
     	$stmnt[patid]    = $this_patient->local_record["ptid"];

	  	return;

	}  // end of Patient

   	function ServiceLines($stack) {
		global $display_buffer;
		reset ($this->renderform_variables);
		while (list($k,$v)=each($this->renderform_variables)) global $$v;
		global $sql;

		$row = $stack[0];

		// zero current number of charges
		$number_of_charges = 0; 
		$totitemprice = 0;
		$totitemcharges = 0;
		$totitempaid = 0; 
		$totitembal = 0;
 
		// and zero the arrays
		for ($j=1;$j<=$this->formno[ffloopnum];$j++)
		{
		   $itemcpt[$j]    = 
		   $itemdesc[$j]    = 
		   $itemdate[$j]   = 
		   $itemprice[$j] =
		   $itemcharges[$j] =
		   $itempaid[$j] = 
		   $itembal[$j] = "";
		}


		$count = count($stack);
		for ($i=0;$i<$count;$i++)
		{
			$row = $stack[$i];
			$number_of_charges++;

			//$qded = "SELECT SUM(payrecamt) as sumamt,payrecproc FROM payrec ".
			//		"WHERE payreccat='".DEDUCTABLE."' AND payrecproc='".$row[id]."' GROUP BY payrecproc";
			//$dedres = $sql->query($qded);
			//if ($sql->num_rows($dedres) <= 0)
			//{
			//	$itemded[$number_of_charges] = bcadd(0,0,2);
			//}
			//else
			//{
			//	$dedrow = $sql->fetch_array($dedres);
			//	$itemded[$number_of_charges] = bcadd($dedrow[sumamt],0,2);
			//}
				

			$cur_cpt = freemed::get_link_rec($row[proccpt],"cpt");
			$itemcpt     [$number_of_charges] = $cur_cpt[cptcode];
			$itemdesc     [$number_of_charges] = $cur_cpt[cptnameext];
			$itemdate    [$number_of_charges] = $row[procdt];

			$itemprice [$number_of_charges] = bcadd($row[procbalorig], 0, 2);
				//($row[procamtallowed]) ? bcadd($row[procamtallowed], 0, 2) : bcadd($row[procbalorig], 0, 2);

			$charges = $row[procbalorig] - $row[proccharges];	
			$itemcharges[$number_of_charges] = bcadd($charges,0,2);
			$itempaid[$number_of_charges] = bcadd($row[procamtpaid],0,2);
			$itembal[$number_of_charges] = bcadd($row[procbalcurrent],0,2);

			$totitemprice += $itemprice[$number_of_charges];
			$totitemcharges += $itemcharges[$number_of_charges];
			$totitempaid += $itempaid[$number_of_charges];
			$totitembal += $itembal[$number_of_charges];
		}

		$totitemprice = bcadd($totitemprice,0,2);
		$totitemcharges = bcadd($totitemcharges,0,2);
		$totitempaid = bcadd($totitempaid,0,2);
		$totitembal = bcadd($totitembal,0,2);

		return;		
   	} // end service lines

   function ProcCallBack($stack) {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		reset ($this->renderform_variables);
		while (list($k,$v)=each($this->renderform_variables)) global $$v;

		$count = count($stack);
		if ($count == 0)
			return;
		$row = $stack[0];

		$this->Provider($stack);
		$this->Facility($stack);
		$this->Patient($stack);       // first hals part1
		$this->ServiceLines($stack);  // second half
		$this->form_buffer .= render_fixedForm ($whichform);

		//

   }


	function view() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
	
	    $display_buffer .= "
		<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3
		 VALIGN=MIDDLE ALIGN=CENTER>
		<TR>
		 <TD COLSPAN=2>
		  <CENTER>
		   <B>"._("Generate Statement Billing")."</B>
		  </CENTER>
		 </TD>
    	</TR>

		<FORM ACTION=\"$this->page_name\" METHOD=POST>
		<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
		<INPUT TYPE=HIDDEN NAME=\"viewaction\" VALUE=\"geninsform\">
		<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"$module\">

		<TR>
		 <TD ALIGN=RIGHT>
		  <CENTER>
		   Statement :
		  </CENTER>
		 </TD>
     	<TD ALIGN=LEFT>
      	<SELECT NAME=\"whichform\">
   		";
	   $result = $sql->query ("SELECT * FROM fixedform WHERE fftype='2'
							 ORDER BY ffname, ffdescrip");
	   while ($r = $sql->fetch_array ($result)) {
		$display_buffer .= "
		 <OPTION VALUE=\"$r[id]\">".prepare($r[ffname])."
		";
	   } // end looping through results                         
	   $display_buffer .= "
		  </SELECT>
		 </TD>
    	</TR>
		";

		$display_buffer .= "
		<TR>
		 <TD COLSPAN=2>
		  <CENTER>
		   <INPUT TYPE=SUBMIT VALUE=\""._("Go")."\">
		  </CENTER>
		 </TD>
		  <TD><INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"1\"></TD>
		</TR>

		</FORM>

		</TABLE>
	   ";
	} // end view functions


} // end class GenerateStatementsModule

register_module("GenerateStatementsModule");

} // end if not defined

?>
