<?php
 // $Id$
 // desc: procedure database module
 // lic : GPL, v2

if (!defined("__PROCEDURE_MODULE_PHP__")) {

define (__PROCEDURE_MODULE_PHP__, true);

class procedureModule extends freemedEMRModule {

	var $MODULE_NAME = "Procedures";
	var $MODULE_VERSION = "0.1";

	var $table_name  = "procrec";
	var $record_name = "Procedure";
    var $proc_fields = array(
            "procpatient",
            "proceoc",
			"proccpt",
            "proccptmod",
            "procdiag1",
            "procdiag2",
            "procdiag3",
            "procdiag4",
            "proccharges",      
            "procunits",
            "procvoucher",
            "procphysician",
            "procdt",		
            "procpos",
            "proccomment",
            "procbalorig",
            "procbalcurrent",	
            "procamtpaid",	
            "procbilled",
            "procbillable",
            "procauth",
            "procrefdoc",
            "procrefdt",			
			"proccurcovid",     
			"proccurcovtp",    
			"proccov1",       
			"proccov2",      
			"proccov3",     
			"proccov4"
		);    


	function procedureModule () {
		// call parent constructor
		$this->freemedEMRModule();
	} // end constructor procedureModule

	function add () { $this->form(); }
	function mod () { $this->form(); }

	function form () {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		if (!$been_here) {
    switch ($action) { // inner action switch
     case "addform":
	  global $procunits, $procdiag1,$procdiag2,$procdiag3,$procdiag4,$procphysician,$procrefdoc;
      $procunits = "1.0";        // default value for units
	  $this_patient = new Patient($patient);
      $procdiag1      = $this_patient->local_record[ptdiag1];
      $procdiag2      = $this_patient->local_record[ptdiag2];
      $procdiag3      = $this_patient->local_record[ptdiag3];
      $procdiag4      = $this_patient->local_record[ptdiag4];
	  $procphysician = $this_patient->local_record[ptdoc];
	  $procrefdoc = $this_patient->local_record[ptrefdoc];
      break; // end of addform (inner)
     case "modform":
	   while(list($k,$v)=each($this->proc_fields))
       {
            global $$v;
       }
      $this_data = freemed_get_link_rec ($id, $this->table_name);
      extract ($this_data); // extract all of this data
      break; // end of modform (inner)
    } // inner action switch
	global $been_here;
    $been_here = 1;
  } // end checking if been here
  $phys_query = "SELECT * FROM physician WHERE phyref='no' ".
                "ORDER BY phylname,phyfname";
  $phys_result = $sql->query($phys_query);

  // prep stuff for page one
  if (empty ($procdt)) $procdt = $cur_date; // show current date
  $icd_type = freemed_config_value("icd"); // '9' or '10'
  $cptmod_query = "SELECT * FROM cptmod ORDER BY cptmod,cptmoddescrip";
  $cptmod_result = $sql->query($cptmod_query);
  $icd_query = "SELECT * FROM icd9 ORDER BY icd$icd_type"."code";
  $icd_result = $sql->query($icd_query);

  $auth_r_buffer = "";
  $auth_res = $sql->query ("SELECT * FROM authorizations
                          WHERE (authpatient='$patient')");
  if ($auth_res > 0) { // begin if there are authorizations...
   while ($auth_r = $sql->fetch_array ($auth_res)) {
    $auth_r_buffer .= "
     <OPTION VALUE=\"$auth_r[id]\" ".
     ( ($auth_r[id]==$procauth) ? "SELECTED" : "" )
     .">$auth_r[authdtbegin] to $auth_r[authdtend]\n";
   } // end while looping for authorizations
  } // end if there are authorizations

  // stuff for page two

  // charge calculation routine lies here
  //   charge = units * relative_value(cpt) * 
  //            base_value(physician/provider)
  //   standard_fee = standard_fee [insurance co] unless 0 then
  //                = default_standard_fee
  //  (we display "standard fee" as what the bastards (insurance companies)
  //   are actually going to pay -- be sure to check for divide by zeros...)

  // step one:
  //   calculate the standard fee
  $insid = 0;
  $cov = fm_verify_patient_coverage($patient,PRIMARY);
  if ($cov > 0)
  {
      $primary = new Coverage($cov);
	  $insid = $primary->local_record[covinsco];
  }
  //$this_insco = new InsuranceCompany ($this->this_patient->payer[0]->local_record["payerinsnsco"]);
  $cpt_code = freemed_get_link_rec ($proccpt, "cpt"); // cpt code
  $cpt_code_fees = fm_split_into_array ($cpt_code["cptstdfee"]);
  //$cpt_code_stdfee = $cpt_code[$this_insco->id]; // grab proper std fee
  $cpt_code_stdfee = $cpt_code_fees[$insid]; // grab proper std fee
  if (empty($cpt_code_stdfee) or ($cpt_code_stdfee==0))
    $cpt_code_stdfee = $cpt_code["cptdefstdfee"]; // if none, do default
  $cpt_code_stdfee = bcadd ($cpt_code_stdfee, 0, 2);

  // step two:
  //   grab the relative value from the CPT db
  $relative_value = $cpt_code["cptrelval"];
  if ($debug) echo " (relative_value = \"$relative_value\")\n";

  // step three:
  //   calculate the base value
  $internal_type  = $cpt_code ["cpttype"]; // grab internal type
  if ($debug) 
    echo " (inttype = $internal_type) (procphysician = $procphysician) ";
  $this_physician = freemed_get_link_rec ($procphysician, "physician");
  $charge_map     = fm_split_into_array($this_physician ["phychargemap"]);
  $base_value     = $charge_map [$internal_type];
  if ($debug) echo "<BR>base value = \"$base_value\"\n";

  // step four:
  //   check for patient discount percentage
  $percentage = $this->this_patient->local_record["ptdisc"];
  if ($percentage>0) { $discount = $percentage / 100; }
   else              { $discount = 0;                 }
  if ($debug) echo "<BR>discount = \"$discount\"\n";

  // step five:
  //   calculate formula...
  $charge = ($base_value * $procunits * $relative_value) - $discount; 
  if ($charge == 0)
	$charge = $cpt_code_stdfee;
  if ($debug) echo " (charge = \"$charge\") \n";

  // step six:
  //   adjust values to proper precision
  $charge = bcadd ($charge, 0, 2);

  // ************** BUILD THE WIZARD ****************
  $wizard = new wizard ( array ("been_here", "action", "patient", "id",
	"module") );
  $wizard->add_page ("Step One",
		array_merge(array("procphysician", "proceoc", "procrefdoc",
						  "proccpt", "proccptmod", "procunits", 
					      "procdiag1", "procdiag2", "procdiag3", "procdiag4",		
					      "procpos", "procvoucher","proccomment",
							"procauth"),
						  date_vars("procdt"),date_vars("procrefdt")),
    html_form::form_table ( array (
      _("Provider") =>
        freemed_display_selectbox ($phys_result, "#phylname#, #phyfname#", "procphysician"),
      _("Date of Procedure") =>
        fm_date_entry ("procdt"),
      _("Episode of Care") =>
        freemed_multiple_choice ("SELECT * FROM eoc
                              WHERE eocpatient='$patient'
                              ORDER BY eocdtlastsimilar DESC",
                             "eocstartdate:eocdtlastsimilar:eocdescrip",
                             "proceoc",
                             $proceoc,
                             false),
      _("Procedural Code") =>
        freemed_display_selectbox(
          $sql->query("SELECT * FROM cpt ORDER BY cptcode,cptnameint"),
            "#cptcode# (#cptnameint#)", "proccpt").
          freemed_display_selectbox(
            $sql->query("SELECT cptmod,cptmoddescrip,id ".
              "FROM cptmod ORDER BY cptmod,cptmoddescrip"),
              "#cptmod# (#cptmoddescrip#)", "proccptmod"),
      _("Units") =>
        "<INPUT TYPE=TEXT NAME=\"procunits\" VALUE=\"".prepare($procunits)."\" ".
        "SIZE=10 MAXLENGTH=9>",
      _("Diagnosis Code")." 1" =>
        freemed_display_selectbox ($icd_result, (($icd_type=="9") ? 
          "#icd9code# (#icd9descrip#)" : "#icd10code# (#icd10descrip#)"), "procdiag1"),
      _("Diagnosis Code")." 2" =>
        freemed_display_selectbox ($icd_result, (($icd_type=="9") ? 
          "#icd9code# (#icd9descrip#)" : "#icd10code# (#icd10descrip#)"), "procdiag2"),
      _("Diagnosis Code")." 3" =>
        freemed_display_selectbox ($icd_result, (($icd_type=="9") ? 
          "#icd9code# (#icd9descrip#)" : "#icd10code# (#icd10descrip#)"), "procdiag3"),
      _("Diagnosis Code")." 4" =>
        freemed_display_selectbox ($icd_result, (($icd_type=="9") ? 
          "#icd9code# (#icd9descrip#)" : "#icd10code# (#icd10descrip#)"), "procdiag4"),
      _("Place of Service") =>
        freemed_display_selectbox(
          $sql->query("SELECT psrname,psrnote,id FROM facility"),
          "#psrname# [#psrnote#]", 
          "procpos"
        ),
      _("Voucher Number") =>
        "<INPUT TYPE=TEXT NAME=\"procvoucher\" VALUE=\"".prepare($procvoucher)."\" ".
        "SIZE=20>\n",
      _("Authorization") =>
        "<SELECT NAME=\"procauth\">\n".
        "<OPTION VALUE=\"0\" ".
        ( ($procauth==0) ? "SELECTED" : "" ).">NONE SELECTED\n".
        $auth_r_buffer.
        "</SELECT>\n",
      _("Referring Provider") =>
        freemed_display_selectbox (
          $sql->query("SELECT phylname,phyfname,id FROM physician 
                      WHERE phyref='yes'
                      ORDER BY phylname, phyfname"),
          "#phylname#, #phyfname#", "procrefdoc"
        ),
      _("Date of Last Visit") =>
        fm_date_entry ("procrefdt"),
      _("Comment") =>
        "<INPUT TYPE=TEXT NAME=\"proccomment\" VALUE=\"".prepare($proccomment)."\" ".
        "SIZE=30 MAXLENGTH=512>\n"
    ) )
  ); // end of page one

  $wizard->add_page ("Step Two: Confirm",
    array ("proccomment","procunits", "procbalorig", "procbillable"),
    html_form::form_table ( array (

     _("Procedural Code") =>
       prepare($cpt_code["cptcode"]),

     _("Units") =>
       prepare($procunits),

     _("Calculated Accepted Fee") =>
       $cpt_code_stdfee,

     _("Calculated Charge") =>
       "<INPUT TYPE=TEXT NAME=\"procbalorig\" SIZE=10 MAXLENGTH=9 ".
       "VALUE=\"".prepare($charge)."\">",

     _("Insurance Billable?") =>
       "<SELECT NAME=\"procbillable\">
        <OPTION VALUE=\"0\" ".
         ( ($procbillable == 0) ? "SELECTED" : "" ).">"._("Yes")."
        <OPTION VALUE=\"1\" ".
         ( ($procbillable != 0) ? "SELECTED" : "" ).">"._("No")."
       </SELECT>\n",

     _("Comment") =>
       prepare($proccomment)
   ) )
  );

  if (!$wizard->is_done() and !$wizard->is_cancelled()) {
    // display the wizard
    echo "<CENTER>".$wizard->display()."</CENTER>\n";
  } else if ($wizard->is_done()) {
    // process add/mod here
    $error_msg = "";
    // verify minimum requirements for a proc

    if ( (empty($proccpt)) OR ($proccpt == 0) )
	{
		$error_msg = "Must select a cpt code<BR>";
	}
    if ( (empty($procdiag1)) OR ($procdiag1 == 0) )
	{
		$error_msg = "Must select a Diagnosis code<BR>";
	}
    if ( (empty($procphysician)) OR ($procphysician == 0) )
	{
		$error_msg = "Must select a Physician<BR>";
	}
    if ( (empty($procdt)) OR ($procdt == 0) )
	{
		$error_msg = "Must Specify a Procedure Date<BR>";
	}
    if ( (empty($procpos)) OR ($procpos == 0) )
	{
		$error_msg = "Must Specify a Place of Service<BR>";
	}

	if (!empty($error_msg))
	{
		 if ($action == "add" OR $action=="addform")
  			$action="addform";
         else
            $action="modform";
		 echo "<CENTER>";
		 echo "$error_msg<BR>".
         "<A HREF=\"$this->page_name?$_auth&module=$module&action=addform&procvoucher=$procvoucher".
          "&patient=$patient&procdt=".fm_date_assemble("procdt").
	      "&proccpt=$proccpt".
	      "&procpos=$procpos".
          "&procdiag1=$procdiag1".
          "&procdiag2=$procdiag2".
          "&procdiag3=$procdiag3".
          "&procdiag4=$procdiag4".
          "&procphysician=$procphysician".
          "\">Try Again</A>".
		 "</CENTER>";
		 DIE("<CENTER>Errors Found</CENTER>");

	}

    echo "
      <P><CENTER>
      <$STDFONT_B>".
      ( (substr($action,0,3)=="add") ? _("Adding") : _("Modifying") ).
       " ... <$STDFONT_E>
    ";
    switch ($action) {
     case "addform": case "add":
       // form add query
	  $cov_ids = fm_get_active_coverage($patient);

	  $covmap[0] = 0;          // [0] coverage is always patient 
	  if (is_array($cov_ids))
	  {
			// make an array of ids indexed by coverage type
			$cnt = count($cov_ids);
			// clear the array plus 1 for the patient
			for ($i=0;$i<$cnt+1;$i++) { $covmap[$i] = 0; }
			for ($i=0;$i<$cnt;$i++)
			{
				// make array of coverages indexed by type
				$covtype = freemed_get_link_field($cov_ids[$i], "coverage", "covtype");
				if (!$covtype)
					DIE("Failed getting coverages");
				$covmap[$covtype] = $cov_ids[$i];
				
			}
			
			
	  }

	  $proccov1 = $proccov2 = $proccov3 = $proccov4 = 0;
	  $cnt = count($covmap);
	  $proccurcovid = 0;
	  $proccurcovtp = 0;
	  for ($i=1;$i<$cnt;$i++)
	  {
    	// get the first non zero coverage and type
    	// basically the first valid coverage becomes
    	// this procedures current coverage.
    	// while were at build proccov1-4;
    	if ( ($proccurcovid==0) AND ($covmap[$i] != 0) )
    	{
       		$proccurcovid = $covmap[$i];
       		$proccurcovtp = $i;
    	}
    	$var = "proccov".$i;
    	$$var = $covmap[$i];
	  } 

	
      $query = $sql->insert_query (
		$this->table_name,
		array (
            "procpatient"	=>	$patient,
            "proceoc",
			"proccpt",
            "proccptmod",
            "procdiag1",
            "procdiag2",
            "procdiag3",
            "procdiag4",
            "proccharges"       =>  $procbalorig,
            "procunits",
            "procvoucher",
            "procphysician",
            "procdt"			=>	fm_date_assemble("procdt"),
            "procpos",
            "proccomment",
            "procbalorig",
            "procbalcurrent"	=>	$procbalorig,
            "procamtpaid"		=>	"0",
            "procbilled"		=>	"0",
            "procbillable",
            "procauth",
            "procrefdoc",
            "procrefdt"			=>	fm_date_assemble("procrefdt"),
			"proccurcovid"        =>  $proccurcovid,
			"proccurcovtp"        =>  $proccurcovtp,
			"proccov1"        =>  $proccov1,
			"proccov2"        =>  $proccov2,
			"proccov3"        =>  $proccov3,
			"proccov4"        =>  $proccov4
		)
	);
	//$debug=true;
      $result = $sql->query ($query);
      if ($debug) echo " (query = $query, result = $result) <BR>\n";
      if ($result) { echo _("done")."."; }
       else        { echo _("ERROR");    }

      $this_procedure = $sql->last_record ($result);

      // form add query
      echo "
        <BR>
        <$STDFONT_B>"._("Committing to ledger")." ... <$STDFONT_E>
      ";
      $query = "INSERT INTO payrec VALUES (
            '$cur_date',
            '0000-00-00',
            '$patient',
            '".fm_date_assemble("procdt")."',
            '".PROCEDURE."',
            '$this_procedure',
            '$proccurcovtp',
            '$proccurcovid',
            '0',
            '',
            '$procbalorig',
            '".addslashes($proccomment)."',
            'unlocked',
            NULL )";
      $result = $sql->query ($query);
      if ($debug) echo " (query = $query, result = $result) <BR>\n";
      if ($result) { echo _("done")."."; }
       else        { echo _("ERROR");    }
      $this_procedure = $sql->last_record ($result, $this->table_name);
  
       // updating patient diagnoses
      echo "
        <BR>
        <$STDFONT_B>"._("Updating patient diagnoses")." ... <$STDFONT_E>
      ";
      $query = "UPDATE patient SET
            ptdiag1  = '$procdiag1',
            ptdiag2  = '$procdiag2',
            ptdiag3  = '$procdiag3',
            ptdiag4  = '$procdiag4'
            WHERE id = '$patient'";
      $result = $sql->query ($query);
      if ($debug) echo " (query = $query, result = $result) <BR>\n";
      if ($result) { echo _("done")."."; }
       else        { echo _("ERROR");    }
 
      echo "
        </CENTER>
        <P>
        <CENTER>
         <A HREF=\"manage.php?$_auth&id=$patient\"
         ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A> <B>|</B>
         <A HREF=\"$this->page_name?$_auth&module=PaymentModule&action=addform&patient=$patient\"
         ><$STDFONT_B>"._("Add Payment")."<$STDFONT_E></A> <B>|</B>
         <A HREF=\"$this->page_name?$_auth&module=$module&action=addform&procvoucher=$procvoucher".
          "&patient=$patient&procdt=".fm_date_assemble("procdt").
	      "&proccpt=$proccpt".
	      "&procpos=$procpos".
          "&procdiag1=$procdiag1".
          "&procdiag2=$procdiag2".
          "&procdiag3=$procdiag3".
          "&procdiag4=$procdiag4".
          "&procphysician=$procphysician".
          "\"
         ><$STDFONT_B>"._("Add Another")." "._($record_name)."<$STDFONT_E></A>
        </CENTER>
        <P>
      ";
      break; // end add

     case "modform": case "mod":
       $query = "UPDATE $this->table_name SET
            procpatient     = '$patient',
            proceoc         = '".addslashes(fm_join_from_array($proceoc))."',
            proccpt         = '$proccpt',
            proccptmod      = '$proccptmod',
            procdiag1       = '$procdiag1',
            procdiag2       = '$procdiag2',
            procdiag3       = '$procdiag3',
            procdiag4       = '$procdiag4',
            proccharges     = '$proccharges',
            procunits       = '$procunits',
            procvoucher     = '".addslashes($procvoucher).  "',
            procphysician   = '".addslashes($procphysician)."',
            procdt          = '".fm_date_assemble("procdt")."',
            procpos         = '".addslashes($procpos).      "',
            procbalorig     = '".addslashes($procbalorig).  "',
            proccomment     = '".addslashes($proccomment).  "',
            procauth        = '".addslashes($procauth).     "',
            procbillable    = '".addslashes($procbillable). "',
            procrefdoc      = '".addslashes($procrefdoc).   "',
            procrefdt       = '".fm_date_assemble("procrefdt")."'
            WHERE id='$id'";
       $result = $sql->query ($query);
       if ($debug) echo " (query = $query, result = $result) <BR>\n";
       if ($result) { echo _("done")."."; }
        else        { echo _("ERROR");    }
       echo "
        <P>
        <$STDFONT_B>"._("Committing to ledger")." ... <$STDFONT_E>
       ";
       // form add query
       $query = "UPDATE payrec SET
            payrecdtmod   = '$cur_date',
            payrecpatient = '$patient',
            payrecdt      = '".fm_date_assemble("procdt")."',
            payrecamt     = '$procbalorig',
            payrecdescrip = '".addslashes($proccomment)."'
            WHERE ( (payreccat='5') AND (payrecproc='$id') )";
       $result = $sql->query ($query);
       if ($debug) echo " (query = $query, result = $result) <BR>\n";
       if ($result) { echo _("done")."."; }
        else        { echo _("ERROR");    }

        // updating patient diagnoses
      echo "
        <P>
        <$STDFONT_B>"._("Updating patient diagnoses")." ... <$STDFONT_E>
      ";
      $query = "UPDATE patient SET
           ptdiag1  = '$procdiag1',
           ptdiag2  = '$procdiag2',
           ptdiag3  = '$procdiag3',
           ptdiag4  = '$procdiag4'
           WHERE id = '$patient'";
      $result = $sql->query ($query);
      if ($debug) echo " (query = $query, result = $result) <BR>\n";
      if ($result) { echo _("done")."."; }
       else        { echo _("ERROR");    }

      echo "
       <P>
       <CENTER>
        <A HREF=\"manage.php?$_auth&id=$patient\"
         ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
       </CENTER>
       <P>
      ";
      break; // end mod
    } // end switch
  } // end checking if done/cancelled
	} // end function procedureModule->form()

	function delete () {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

  echo "
   <P><CENTER>
   <$STDFONT_B>"._("Deleting")." ...
  ";
  $query = "DELETE FROM $this->table_name WHERE id='$id'";
  $result = $sql->query ($query);
  if ($result) { echo "["._("Procedure")."] "; }
   else        { echo "["._("ERROR")."] ";     }
  $query = "DELETE FROM payrec WHERE payrecproc='".addslashes($id)."'";
  $result = $sql->query ($query);
  if ($result) { echo "["._("Payment Record")."] "; }
   else        { echo "["._("ERROR")."] ";          }
  echo "
   <$STDFONT_E></CENTER>
   <P>
    <CENTER>
     <A HREF=\"$this->page_name?$_auth&module=$module&patient=$patient\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A> <B>|</B>
     <A HREF=\"manage.php?$_auth?id=$patient\"
     ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
    </CENTER>
   <P>
  ";
	} // end function procedureModule->delete()


	function view() {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

  $query = "SELECT * FROM $this->table_name
            WHERE procpatient='".addslashes($patient)."'
            ORDER BY procdt DESC";
  $result = $sql->query ($query);
  echo freemed_display_itemlist(
    $result,
    $this->page_name,
    array ( // control
      _("Date of Procedure")	=> "procdt",
      _("Procedure Code")	=> "proccpt",
      _("Modifier")		=> "proccptmod",
      _("Comment")		=> "proccomment"
    ),
    array ( // blanks
      "",
      "",
      "",
      _("NO COMMENT")
    ),
    array ( // xref
      "",
      "cpt"    => "cptcode",
      "cptmod" => "cptmod",
      ""
    )
  );
	} // end function procedureModule->view()

} // end class procedureModule

register_module ("procedureModule");

} // end if not defined

?>
