<?php
 // note: patient database functions
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 //       adam b (gdrago23@yahoo.com)
 //       some small stuff by: max k <amk@span.ch>
 // lic : GPL, v2
 
  $page_name="patient.php"; // for help info, later
  $record_name="Patient";    // compatibility with API functions
  include ("lib/freemed.php");
  include ("lib/API.php");
  include ("lib/calendar-functions.php");

  SetCookie ("_ref", $page_name, time()+$_cookie_expire);

  if ( ($id>0) AND 
       ($action != "addform") AND ($action != "add") AND
       ($action != "delform") AND ($action != "del")) {
    SetCookie ("current_patient", $id, time()+$_cookie_expire);
    $current_patient = $id;   // patch for first time....
  } // end checking for current_patient value
  else
  {
  	$current_patient=0;
  }

  freemed_open_db ($LoginCookie); // authenticate user

// NULL at top so next() works...
//$t_vars = array("NULL", "ptguar","ptrelguar", "ptguarstart", "ptguarend",
//    "ptins", "ptinsno", "ptinsgrp", "ptinsstart", "ptinsend"); 

switch ($action) {
  case "add": case "addform":
  case "mod": case "modform":
    // addform and modform not used due to "notebook"
   $book = new notebook ( array ("action", "_auth", "id", "been_here"),
     NOTEBOOK_COMMON_BAR|NOTEBOOK_STRETCH, 3);
   $book->set_submit_name (_("OK"));
   switch ($action) {
     case "add": case "addform":
      if ( !$book->been_here() ) {
        // $ins_disp_inactive=false; // TODO! not implemented
      } // end of checking empty been_here
      $action_name = _("Add");
      break; // end internal add

     case "mod": case "modform":
      if ( !$book->been_here() ) {
      $result = $sql->query("SELECT * FROM patient ".
         "WHERE ( id = '".prepare($id)."' )");

      $r = $sql->fetch_array($result); // dump into array r[]
	  extract($r); // pull variables in from array

//	reset($t_vars);
//	while ($i=next($t_vars)) { // for all these TEXT items
//	  if (strlen($$i)>0)
 //           $$i = fm_split_into_array($$i); // pull the array items
//	  else
//	    $$i = array (); // set to null array if strlen<1
//	} // for each TEXT item

        $ptstate      = strtoupper ($ptstate);

        // 19990728 -- next of kin pull and remake
        $ptnextofkin  = htmlentities ($ptnextofkin);

        // resplit email
        if (strlen($ptemail)>3) {
          $ptemail_array = explode ("@", $ptemail);
          $ptemail1      = $ptemail_array[0];
          $ptemail2      = $ptemail_array[1];
        } // end of resplit email
	
        //$ins_disp_inactive=false;
        $been_here = "1"; // set been_here
      } // end of checking empty been_here
      $action_name = _("Modify");
      break; // end internal mod
   } // end of internal switch add/mod

   // ** DISPLAY ADD/MOD ***

   if (!isset($num_inscos))
   {
       // first time through
	   $num_inscos = 0;
	   // see if patient has insurance
       $ins_result = $sql->query("SELECT COUNT(*) FROM payer WHERE payerpatient='$current_patient'");
       if ($ins_result)
       {
           $ins_data = $sql->fetch_array($ins_result);
           if ($ins_data)
           {
               if ($ins_data[0] > 0)
               {
                   $num_inscos = $ins_data[0];
                   $has_insurance=true;
               }
           }
       }
	   if ($num_inscos == 0)
       {
			// if no insurance then check for a guarantor
           $ins_result = $sql->query("SELECT COUNT(*) FROM guarantor WHERE guarpatient='$current_patient'");
           if ($ins_result)
           {
               $ins_data = $sql->fetch_array($ins_result);
               if ($ins_data)
               {
                   if ($ins_data[0] > 0)
                   {
                       $num_inscos = $ins_data[0];
                       $has_insurance=true;
                   }
               }
           }
       }

    
   } // checking for unset num_inscos
   
   $book->add_page (
     _("Primary Information"),
     array ("ptlname", "ptfname", "ptmname",
            date_vars("ptdob"),
            "ptaddr1", "ptaddr2", "ptcity", "ptstate", "ptzip", "ptcountry",
            "has_insurance"),
		html_form::form_table ( array (
			_("Last Name") =>
				"<INPUT TYPE=TEXT NAME=\"ptlname\" SIZE=25 MAXLENGTH=50 ".
				"VALUE=\"".prepare($ptlname)."\">",
    
			_("First Name") =>
				"<INPUT TYPE=TEXT NAME=\"ptfname\" SIZE=25 MAXLENGTH=50 ".
				"VALUE=\"".prepare($ptfname)."\">",

			_("Middle Name") =>
				"<INPUT TYPE=TEXT NAME=\"ptmname\" SIZE=25 MAXLENGTH=50 ".
				"VALUE=\"".prepare($ptmname)."\">",

			_("Address Line 1") =>
				"<INPUT TYPE=TEXT NAME=\"ptaddr1\" SIZE=25 MAXLENGTH=45 ".
				"VALUE=\"".prepare($ptaddr1)."\">",

			_("Address Line 2") =>
				"<INPUT TYPE=TEXT NAME=\"ptaddr2\" SIZE=25 MAXLENGTH=45 ".
				"VALUE=\"".prepare($ptaddr2)."\">",

			_("City").", "._("State").", "._("Zip") =>
				"<INPUT TYPE=TEXT NAME=\"ptcity\" SIZE=10 MAXLENGTH=45 ".
				"VALUE=\"".prepare($ptcity)."\">\n".
				"<INPUT TYPE=TEXT NAME=\"ptstate\" SIZE=3 MAXLENGTH=2 ".
				"VALUE=\"".prepare($ptstate)."\">\n". 
				"<INPUT TYPE=TEXT NAME=\"ptzip\" SIZE=10 MAXLENGTH=10 ".
				"VALUE=\"".prepare($ptzip)."\">",

			_("Date of Birth") =>
				date_entry("ptdob"),

			_("Has Insurance") =>
				"<INPUT TYPE=CHECKBOX NAME=\"has_insurance\" ".
				(($has_insurance) ? "CHECKED" : "").">"

		) )
     );

     $book->add_page(
       _("Contact"),
       array (
         "ptaddr1", "ptaddr2", "ptcity", "ptstate", "ptzip",
	 "ptcountry", phone_vars("pthphone"), phone_vars("ptwphone"),
	 phone_vars("ptfax")
         ),
		html_form::form_table ( array (

			_("Country") =>
				html_form::country_pulldown("ptcountry"),

			_("Home Phone") =>
				fm_phone_entry ("pthphone"),

			_("Work Phone") =>
				fm_phone_entry ("ptwphone"),
    
			_("Fax Number") =>
				fm_phone_entry ("ptfax"),
  
			_("Email Address") =>
				"<INPUT TYPE=TEXT NAME=\"ptemail1\" SIZE=20 MAXLENGTH=40 ".
				"VALUE=\"".prepare($ptemail1)."\"> <B>@</B>\n".
				"<INPUT TYPE=TEXT NAME=\"ptemail2\" SIZE=20 MAXLENGTH=40 ".
				"VALUE=\"".prepare($ptemail2)."\">"

		) )
     );

     $book->add_page(
       _("Personal"),
       array (
         "ptsex", "ptmarital", "ptssn", "ptid",
	 "ptdmv", "ptbilltype", "ptbudg", "ptempl"
	 ),
	html_form::form_table ( array (

		_("Gender") =>
			html_form::select_widget("ptsex",
				array (
					 _("Female")        => "f",
					 _("Male")          => "m",
					 _("Transgendered") => "t"
				)
			),

		_("Marital Status") =>
			html_form::select_widget("ptmarital",
				array (
					_("Single")    => "single",
					_("Married")   => "married",
					_("Divorced")  => "divorced",
					_("Separated") => "separated",
					_("Widowed")   => "widowed"
				)
			),
	
		_("Employment Status") =>
			html_form::select_widget("ptempl",
				array (
					_("Yes")    => "y",
					_("No")     => "n",
				)
			),
		_("Social Security Number") =>
			"<INPUT TYPE=TEXT NAME=\"ptssn\" SIZE=9 MAXLENGTH=10 ".
			"VALUE=\"".prepare($ptssn)."\">",

		_("Internal Practice ID #") =>
			"<INPUT TYPE=TEXT NAME=\"ptid\" SIZE=10 MAXLENGTH=10 ".
			"VALUE=\"".prepare($ptid)."\">",
    
		_("Driver's License (No State)") =>
			"<INPUT TYPE=TEXT NAME=ptdmv SIZE=10 MAXLENGTH=9 ".
			"VALUE=\"".prepare($ptdmv)."\">",
       
		_("Type of Billing") =>
			html_form::select_widget("ptbilltype",
				array (
					_("Monthly Billing On Account") => "mon",
					_("Statement Billing")          => "sta",
					_("Charge Card Billing")        => "chg",
					_("NONE SELECTED")              => ""
				)
			),

		_("Monthly Budget Amount") =>
			"<INPUT TYPE=TEXT NAME=ptbudg SIZE=10 MAXLENGTH=20 ".
			"VALUE=\"".prepare($ptbudg)."\">"
		) )

	);


   $ref_phys_r = $sql->query("SELECT phylname,phyfname,id
                            FROM physician WHERE phyref='yes' 
                            ORDER BY phylname,phyfname");
   $int_phys_r = $sql->query("SELECT phylname,phyfname,id
                            FROM physician WHERE phyref='no' 
                            ORDER BY phylname,phyfname");
   $all_phys_r = $sql->query("SELECT phylname,phyfname,id
                            FROM physician
			    ORDER BY phylname,phyfname");

   if (!isset($num_other_docs)) { // first time through
     $num_other_docs=0;
     for ($i=1;$i<=4;$i++) // for ptphy[1..4]
       if (${"ptphy$i"}>0) 
         $num_other_docs++; // some days, i'm so clever it hurts.
   } // is !isset num_other_docs

   $book->add_page(
     _("Physician"),
     array ("ptdoc", "ptphy1", "ptphy2", "ptphy3", "ptphy4", "ptpcp",
            "ptrefdoc", "num_other_docs"),
     "
    <TABLE CELLSPACING=0 CELLPADDING=2 BORDER=0>

    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("In House Doctor")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($int_phys_r, "#phylname#, #phyfname#", "ptdoc")."
    </TD></TR>

    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Referring Doctor")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($ref_phys_r, "#phylname#, #phyfname#", "ptrefdoc")."
    </TD></TR>

    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Primary Care Physician")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($all_phys_r, "#phylname#, #phyfname#", "ptpcp")."
    </TD></TR>

    ".(($num_other_docs>0) ? "
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Other Physician 1")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($all_phys_r, "#phylname#, #phyfname#", "ptphy1")."
    </TD></TR>
    " : "").

    (($num_other_docs>1) ? "
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Other Physician 2")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($all_phys_r, "#phylname#, #phyfname#", "ptphy2")."
    </TD></TR>
    " : "").

    (($num_other_docs>2) ? "
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Other Physician 3")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($all_phys_r, "#phylname#, #phyfname#", "ptphy3")."
    </TD></TR>
    " : "").

    (($num_other_docs>3) ? "
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Other Physician 4")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($disp_phys_result, "#phylname#, #phyfname#", "ptphy4")."
    </TD></TR>
    " : "").

    "<TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Number of Other Physicians")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
      ".html_form::number_pulldown("num_other_docs", 0, 4)."
    </TD></TR>

    </TABLE>
     ");    

   $payer_error_msg = ""; 

  if ( ($has_insurance) AND ($current_patient > 0) ) { 
	
	// a patient must be added before anything can be done on the payers

	// if a selected payer and its an add 

    if ($add_this_insco)  
    { // not persistent! use once! we have a selection!

        if ( empty($payerpatientinsgrp_) OR
            empty($payerpatientinsno_) )   
        {
            $payer_error_msg = _("Please Specify Insurance ID/Number");
        }
        else
        if ($payerinsco_ <= 0) 
        {
            $payer_error_msg = _("Please select an Insurance Co.");
        }
        else
        {   // add this record
    		$add_this_insco = false;
  
            	// INSERT new rec
        	$query = "INSERT INTO payer VALUES('$payerinsco_',
                        '".fm_date_assemble("payerstartdt_")."',
                        '".fm_date_assemble("payerenddt_")."',
                        '$current_patient',
                        '$payerpatientinsgrp_',
                        '$payerpatientinsno_',
                        '$payertype_',
                        '0',NULL)";
        	$payer_result = $sql->query($query);
		if (!$payer_result)
		{
             		$payer_error_msg = "$query ";
             		$payer_error_msg .= _("Failed");
             		$payer_error_msg .= "<BR>";
		}
        }

    } // done add 

     // check for a mod request

    if (!isset($payerinscomod)) $payerinscomod=-1; // sanity check
    if ($payerinscomod >= 0) // modify this record
    {
        if ( empty($payerpatientinsgrp_) OR
            empty($payerpatientinsno_) ) 
        {
            $payer_error_msg = _("Please specify Insurance ID/Number");
        }
        else
        {
	    $query = "UPDATE payer SET payerstartdt = '".fm_date_assemble("payerstartdt_")."',
					payerenddt = '".fm_date_assemble("payerenddt_")."',
					payerpatientinsno = '$payerpatientinsno_',
					payerpatientgrp = '$payerpatientinsgrp_',
					payertype = '$payertype_'
					WHERE id = '$payerid[$payerinscomod]'";
            $payer_result = $sql->query($query);
	    if (!$payer_result)
	    {
             		$payer_error_msg = "$query ";
             		$payer_error_msg .= _("Failed");
             		$payer_error_msg .= "<BR>";
	    }
        }
    }

    // check for any deletes and clear the old array while we are checking.

    if (count($payerinsco) > 0)
    {
	$numins = count($payerinsco);
    	for ($arr_idx=0;$arr_idx<$numins;) 
    	{ // increment at bottom
        	if ($payerinscodel[$arr_idx]) 
        	{
           		$query = "UPDATE payer SET payerstatus='1' WHERE id='$payerid[$arr_idx]'";
          		$payer_result = $sql->query($query);
	  		if (!$payer_result)
		 	{
             		    $payer_error_msg = "$query ";
             		    $payer_error_msg .= _("Failed");
             		    $payer_error_msg .= "<BR>";
			}
        	}
        
	unset($payerinsco[$arr_idx]);
	unset($payerpatientinsno[$arr_idx]);
	unset($payerpatientinsgrp[$arr_idx]);
	unset($payerstartdt[$arr_idx]); 
	unset($payerenddt[$arr_idx]);
	unset($payerid[$arr_idx]);
	unset($payerstatus[$arr_idx]);
        $arr_idx++; // increment at *end*!
    	} // for each insco listed
    }

    // refresh the array from the db 

    $query = "SELECT * FROM payer WHERE payerpatient='$current_patient'";
    $payer_result = $sql->query($query);

    if ($payer_result)
    {
	$arr_idx = 0;
        $prim = $sec = $tert = $wc = 0;
	while($payer_rec = $sql->fetch_array($payer_result))
        {
		$payerinsco[$arr_idx] 		= $payer_rec[payerinsco];
		$payerpatientinsno[$arr_idx] 	= $payer_rec[payerpatientinsno];
		$payerpatientinsgrp[$arr_idx] 	= $payer_rec[payerpatientgrp];
		$payerstartdt[$arr_idx] 	= $payer_rec[payerstartdt];
		$payerenddt[$arr_idx]	 	= $payer_rec[payerenddt];
		$payerid[$arr_idx]		= $payer_rec[id];

		$act = 0;
          	if (date_in_range($cur_date,$payerstartdt[$arr_idx],$payerenddt[$arr_idx]))
		{
		        if ($payer_rec[payerstatus] == 1)
		        {
			    $payerstatus[$arr_idx] = _("Deleted");
		        }
                        else
                        {
			    $payerstatus[$arr_idx] = _("Active");
                            $act = 1;
                        }
		}
		else
		{
			$payerstatus[$arr_idx] = _("Expired");
		}
		switch ($payer_rec[payertype])
		{
		    case 0;
			$payertype[$arr_idx] = _("Primary");
                        if ($act) $prim++;
			break;
		    case 1;
			$payertype[$arr_idx] = _("Secondary");
                        if ($act) $sec++;
			break;
		    case 2;
			$payertype[$arr_idx] = _("Tertiary");
                        if ($act) $tert++;
			break;
		    case 3;
			$payertype[$arr_idx] = _("Work Comp");
                        if ($act) $wc++;
			break;
		//  case 4;
		//	$payertype[$arr_idx] = _("Patient");
		//	break;
		    default;
			$payertype[$arr_idx] = _("Unknown");
			break;
		}
		// display all inscos. just tell the user if active or inactive
        	$payerinsco_active[$arr_idx]=true;

		
		$arr_idx++;
	} //while we have payers
        // do some editing here
        if ($prim > 1)
            $payer_error_msg = "Too Many Primary Insurers!";
        if ($sec > 1)
            $payer_error_msg = "Too Many Secondary Insurers!";
        if ($tert > 1)
            $payer_error_msg = "Too Many Tertiary Insurers!";
        if ($wc > 1)
            $payer_error_msg = "Too Many Wrokers Comp Insurers!";
           
    } // if payer_result
    
    $ins_r = freemed_search_query( array ($ins_s_val => $ins_s_field),
               array ("insconame", "inscostate", "inscocity"), "insco", 
	       "payerinsco_");
    
    $book->add_page(
      _("Insurance"),
      array ("payerinsco", "payerpatientinsno", "payerpatientinsgrp", "payerstartdt",
        "payerstatus", "payertype", "payerenddt", "ins_s_val", "ins_s_field", 
	"payerid", "payertype_", "payerinsco_", "payerpatientinsno_", "payerpatientinsgrp_",
        date_vars("payerstartdt_"), 
	date_vars("payerenddt_"), "payer_arr"),
      "
    <TABLE CELLSPACING=0 CELLPADDING=0 BORDER=0 WIDTH=\"100%\">
     <TR><TD ALIGN=LEFT>
      <$STDFONT_B>"._("Add This Insurance Company          ")." :<$STDFONT_E>
       <INPUT TYPE=CHECKBOX NAME=\"add_this_insco\">
       ".(isset($payer_arr) ? 
         "<INPUT TYPE=HIDDEN NAME=\"payer_arr\" VALUE=\"$payer_arr\">" : "")."
     </TD>
     </TR>
     <TR><TD>&nbsp</TD></TR>
     <TR><TD ALIGN=LEFT>
      <$STDFONT_B>"._("Insurance")."<FONT SIZE=\"-1\">".
       html_form::select_widget("ins_s_field", array (
        _("Name") => "insconame",
	_("City") => "inscocity" ) )."</FONT><$STDFONT_E>
      <$STDFONT_B>"._("like")."<FONT SIZE=\"-1\">
        <INPUT TYPE=TEXT NAME=\"ins_s_val\" 
        VALUE=\"$ins_s_val\" SIZE=15 MAXLENGTH=20></FONT>
      <$STDFONT_E>
     </TD>
     </TR>
      <TR><TD ALIGN=LEFT>
        <$STDFONT_B>
         "._("Insurance Company")." :
        <$STDFONT_E>
        <$STDFONT_B SIZE=\"-1\">
         ".freemed_display_selectbox($ins_r, 
            "#insconame# (#inscocity#, #inscostate# #inscozip#)", 
	    "payerinsco_")."
        <$STDFONT_E>
      </TD>
      </TR>
	
      <TR><TD ALIGN=LEFT>
        <$STDFONT_B>
         "._("Insurance ID / Group ID")." : 
	<$STDFONT_E>
	<$STDFONT_B SIZE=\"-1\">
	  <INPUT NAME=\"payerpatientinsno_\"  VALUE=\"".$payerpatientinsno_."\"  SIZE=15>
	   <INPUT NAME=\"payerpatientinsgrp_\" VALUE=\"".$payerpatientinsgrp_."\" SIZE=15>
	<$STDFONT_E>
      </TD></TR>
	
      <TR><TD ALIGN=LEFT>
        <$STDFONT_B>"._("Start Date")."<$STDFONT_E>
	<$STDFONT_B SIZE=\"-1\">
	  ".date_entry("payerstartdt_", 1990, "mdy")."
	<$STDFONT_E>
      </TD>
      </TR>
      <TR>
      <TD ALIGN=LEFT>
	<$STDFONT_B>"._(" End Date")."<$STDFONT_E>
	<$STDFONT_B SIZE=\"-1\">
	  ".date_entry("payerenddt_", 1990, "mdy")."
	<$STDFONT_E>
      </TD></TR>
     <TR><TD ALIGN=LEFT>
      <$STDFONT_B>"._("Type")."<FONT SIZE=\"-1\">".
       html_form::select_widget("payertype_", array (
        _("Primary") => "0",
	_("Secondary") => "1", 
	_("Tertiary") => "2", 
	_("Work Comp") => "3" ) )."</FONT><$STDFONT_E>
     </TD>
      </TR>
     <TR> 
     <TD ALIGN LEFT>&nbsp</TD>
     </TR>
     <TR> 
     <TD ALIGN LEFT><B>$payer_error_msg</B></TD>
     </TR>
     <TR> 
     <TD ALIGN LEFT>&nbsp</TD>
     </TR>
      <TR WIDTH=\"100%\"><TD COLSPAN=2>
        ".freemed_display_arraylist(
	    array (_("Insurer"  ) => "payerinsco",
	           _("Start"    ) => "payerstartdt",
	           _("End"      ) => "payerenddt",
	           _("ID Number") => "payerpatientinsno",
	           _("Group"    ) => "payerpatientinsgrp", 
	           _("Type"     ) => "payertype", 
	           _("Status"   ) => "payerstatus"), 
	    array ("insco" => "insconame",
	           "",
		   "",
		   "",
		   "",
		   "",
		   ""))."
      </TD></TR>
    <TR><TD>".fm_htmlize_array("payerid",$payerid)."</TD></TR>
    </TABLE>
      "
    );

   // handle the guarantors
    if ($add_this_guar)  
    { // not persistent! use once! we have a selection!

        if ($guarguar_ <= 0) 
        {
            $guar_error_msg = _("Please select a Guarantor");
        }
        else
        {   // add this record
    		$add_this_insco = false;
  
            	// INSERT new rec
        	$query = "INSERT INTO guarantor VALUES('$current_patient',
			'$guarguar_',
			'$guarrel_',
                        '".fm_date_assemble("guarstartdt_")."',
                        '".fm_date_assemble("guarenddt_")."',
                        '0',
			NULL)";
        	$guar_result = $sql->query($query);
		if (!$guar_result)
		{
             	    $guar_error_msg = "$query ";
             	    $guar_error_msg .= _("Failed");
             	    $guar_error_msg .= "<BR>";
		}
        }

    } // done add 

     // check for a mod request

    if (!isset($guarguarmod)) $guarguarmod=-1; // sanity check
    if ($guarguarmod >= 0) // modify this record
    {
	    $query = "UPDATE guarantor SET guarstartdt = '".fm_date_assemble("guarstartdt_")."',
					guarenddt = '".fm_date_assemble("guarenddt_")."',
					guarrel = '$guarrel_'
					WHERE id = '$guarid[$guarguarmod]'";
            $guar_result = $sql->query($query);
	    if (!$guar_result)
	    {
               $guar_error_msg = "$query ";
               $guar_error_msg .= _("Failed");
               $guar_error_msg .= "<BR>";
	    }
    }

    // check for any deletes but only if it;s an array. either way delete the old array ents

    if (count($guarguar) > 0)
    {
	$numguars = count($guarguar);
    	for ($arr_idx=0;$arr_idx<$numguars;) 
    	{ // increment at bottom
        	if ($guarguardel[$arr_idx]) 
        	{
           		$query = "UPDATE guarantor SET guarstatus='1' WHERE id='$guarid[$arr_idx]'";
          		$guar_result = $sql->query($query);
	  		if (!$guar_result)
	    		{
               			$guar_error_msg = "$query ";
               			$guar_error_msg .= _("Failed");
               			$guar_error_msg .= "<BR>";
	    		}
        	}
        unset($guarpatient[$arr_idx]); 
	unset($guarguar[$arr_idx]);
	unset($guarrel[$arr_idx]);
	unset($guarstartdt[$arr_idx]); 
	unset($guarenddt[$arr_idx]);
	unset($guarstatus[$arr_idx]);
	unset($guarid[$arr_idx]);

        $arr_idx++; // increment at *end*!
    	} // for each insco listed
    }

    // refresh the array from the db 

    $query = "SELECT * FROM guarantor WHERE guarpatient='$current_patient'";
    $guar_result = $sql->query($query);

    if ($guar_result)
    {
	$arr_idx = 0;
        $act=0;
	while($guar_rec = $sql->fetch_array($guar_result))
        {
		$guarpatient[$arr_idx] 	= $guar_rec[guarpatient];
		$guarguar[$arr_idx] 	= $guar_rec[guarguar];
		$guarrel[$arr_idx] 	= $guar_rec[guarrel];
		$guarstartdt[$arr_idx] 	= $guar_rec[guarstartdt];
		$guarenddt[$arr_idx]	= $guar_rec[guarenddt];
		$guarstatus[$arr_idx]	= $guar_rec[guarstatus];
		$guarid[$arr_idx]	= $guar_rec[id];

		// display all guars. just tell the user if active or inactive

        	$guarguar_active[$arr_idx]=true;

          	if (date_in_range($cur_date,$guarstartdt[$arr_idx],$guarenddt[$arr_idx]))
		{
		    if ($guar_rec[guarstatus] == 1)
		    {
			$guarstatus[$arr_idx] = _("Deleted");
		    }
                    else
                    {
			$guarstatus[$arr_idx] = _("Active");
                        $act++;
                    }
		}
		else
		{
			$guarstatus[$arr_idx] = _("Expired");
		}

		$arr_idx++;
	}
        if ($act > 1)
            $guar_error_msg = "Too many Active Guarantors!!";
    }


   $dep_r = freemed_search_query ( 
        array ($dep_s_val => $dep_s_field, $dep_s_val2 => $dep_s_field2),
        array ("ptlname", "ptfname"), "patient", 
        "guarguar_" );
   
   $book->add_page(
     _("Guarantor"),
     array ("guarpatient", "guarguar", "guarrel", "guarstartdt", "guarenddt", "guarstatus",
            "guarid", "guarguar_","guarrel_","guarstartdt_","guarenddt_",
            "guar_disp_inactive", "dep_s_field", "dep_s_val",
            "dep_s_field2", "dep_s_val2"),
     "
    <TABLE CELLSPACING=0 CELLPADDING=2 BORDER=0 WIDTH=\"100%\">
    <TR><TD ALIGN=RIGHT>
     <$STDFONT_B>"._("Guarantor").
     html_form::select_widget("dep_s_field", array (
       _("Last Name")            =>"ptlname",
       _("First Name")           =>"ptfname",
       _("Internal Practice ID") =>"ptid"
     ) )."
     <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
     <$STDFONT_B>"._("like")."
      <INPUT TYPE=TEXT NAME=\"dep_s_val\" VALUE=\"".prepare($dep_s_val)."\">
     <$STDFONT_E> 
    </TD></TR>
    
    <TR><TD ALIGN=RIGHT>
     <$STDFONT_B>"._("Guarantor").
     html_form::select_widget("dep_s_field2", array (
       _("Last Name")            =>"ptlname",
       _("First Name")           =>"ptfname",
       _("Internal Practice ID") =>"ptid"
     ) )."
     <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
     <$STDFONT_B>"._("like")."
      <INPUT TYPE=TEXT NAME=\"dep_s_val2\" VALUE=\"".prepare($dep_s_val2)."\">
      <I>("._("Optional").")</I>
     <$STDFONT_E>
    </TD></TR>
    
    <TR><TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Guarantor")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
      ".freemed_display_selectbox($dep_r,
          "#ptlname#, #ptfname#", "guarguar_")."
    </TD></TR>
    
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Relation to Guarantor")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".html_form::select_widget("guarrel_", array (
        _("Self")    => "S",
        _("Child")   => "C",
        _("Husband") => "H",
        _("Wife")    => "W",
        _("Other")   => "O"
	) )."
    </TD></TR>

    <TR><TD ALIGN=RIGHT>
        <$STDFONT_B>"._("Start Date")."<$STDFONT_E>
        <$STDFONT_B SIZE=\"-1\">
          ".date_entry("guarstartdt_", 1990, "mdy")."
        <$STDFONT_E>
      </TD><TD ALIGN=LEFT>
        <$STDFONT_B>"._("End Date")."<$STDFONT_E>
        <$STDFONT_B SIZE=\"-1\">
          ".date_entry("guarenddt_", 1990, "mdy")."
        <$STDFONT_E>
      </TD></TR>
 
    <TR><TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Add This Guarantor")." :<$STDFONT_E> 
       <INPUT TYPE=CHECKBOX NAME=\"add_this_guar\">".(isset($guar_arr) ? 
	"<INPUT TYPE=HIDDEN NAME=\"guar_arr\" VALUE=\"$guar_arr\">" : "")."
     
    </TD><TD ALIGN=LEFT>&nbsp;</TD>
    </TR>
     <TR> 
     <TD ALIGN LEFT>&nbsp</TD>
     </TR>
     <TR> 
     <TD ALIGN LEFT><B>$guar_error_msg</B></TD>
     </TR>
     <TR> 
     <TD ALIGN LEFT>&nbsp</TD>
     </TR>
    
    <TR WIDTH=\"100%\"><TD COLSPAN=2>
        ".freemed_display_arraylist(
	    array (_("Guarantor"   ) => "guarguar",
	           _("Relationship") => "guarrel",
	           _("Start"       ) => "guarstartdt",
	           _("End"         ) => "guarenddt",
	           _("Status"      ) => "guarstatus"),
	    array ("patient" => "ptlname",
	           "",
		   "",
		   "",
		   ""))."
    </TD></TR>
    <TR><TD>".fm_htmlize_array("guarid",$guarid)."</TD></TR>
	
    </TABLE>
     "
   );

  } // if has insurance

   // show notebook
   freemed_display_html_top ();
   freemed_display_banner ();
   freemed_display_box_top("$record_name $action_name");
   if ( ($action=="modform") or ($action=="mod")) {
     $this_patient = new Patient ($id);
     echo freemed_patient_box ($this_patient);
   }

   if (!( $book->is_done() )) {
     echo "<CENTER>\n".$book->display()."</CENTER>\n";
   } else { // if it is done
     switch ($action) {
       case "add": case "addform":
         $ptdtadd = $cur_date; // current date of add...
         $ptdtmod = $cur_date; // current date for mod as well

         // next of kin prepare blob field
         $ptnextofkin = addslashes ($ptnextofkin);

         // assemble phone numbers
         $pthphone   = fm_phone_assemble ("pthphone");
         $ptwphone   = fm_phone_assemble ("ptwphone");
         $ptfax      = fm_phone_assemble ("ptfax");

         // assemble dates
         $ptdob      = fm_date_assemble("ptdob");
         // ptins{start,end} already assembled

         // knock state to upper case
         $ptstate  = strtoupper ($ptstate); 

         // assemble email
         if ((strlen($ptemail1)>0) AND (strlen($ptemail2)>3))
           $ptemail = $ptemail1 . "@" . $ptemail2;
       
         // collapse the TEXT variables...
	 //reset($t_vars);while ($i=next($t_vars)) 
	 //                 $$i = fm_join_from_array($$i);

         $query = "INSERT INTO patient VALUES (
           '$ptdtadd',
           '$ptdtmod',
           '$ptbal',
           '$ptbalfwd',
           '$ptunapp',
           '$ptdoc',
           '$ptrefdoc',
           '$ptpcp',
           '$ptphy1',
           '$ptphy2',
           '$ptphy3',
           '$ptphy4',
           '$ptbilltype',
           '$ptbudg',
           '$ptlname',
           '$ptfname',
           '$ptmname',
           '$ptaddr1',
           '$ptaddr2',
           '$ptcity',
           '$ptstate',
           '$ptzip',
           '$ptcountry',
           '$pthphone',
           '$ptwphone',
           '$ptfax',
           '$ptemail',
           '$ptsex',
           '$ptdob',
           '$ptssn',
           '$ptdmv',
           '$ptdtlpay',
           '$ptamtlpay',
           '$ptpaytype',
           '$ptdtbill',
           '$ptamtbill',
           '$ptstatus',
           '$ptytdchg',
           '$ptar',
           '$ptextinf',
           '$ptdisc',
           '$ptdol',
           '$ptdiag1',
           '$ptdiag2',
           '$ptdiag3',
           '$ptdiag4',
           '$ptid',
           '$pthistbal',
           '$ptmarital',
           '$ptempl',
           '$ptemp1',
           '$ptemp2',
           '$ptguar',
           '$ptrelguar',
           '$ptguarstart',
           '$ptguarend',
           '$ptins',
           '$ptinsno',
           '$ptinsgrp',
           '$ptinsstart',
           '$ptinsend',
           '$ptnextofkin',
           '$__ISO_SET__',
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL) ";
	 break; // end add
       case "mod": case "modform":
         // collapse the TEXT variables...
	 //reset($t_vars);while ($i=next($t_vars)) 
	 //                 if (is_array($$i)) $$i = implode(':', $$i);
         //$ptins{start,end} already fm_date_assemble'd
	 $ptdtmod  = $cur_date; // set modification date to current date
	 $pthphone = fm_phone_assemble ("pthphone");
	 $ptwphone = fm_phone_assemble ("ptwphone");
	 $ptfax    = fm_phone_assemble ("ptfax");
	 $ptdob       = fm_date_assemble("ptdob"); // assemble date of birth
	 $ptnextofkin = addslashes ($ptnextofkin); // 19990728 next of kin add
	 $ptstate  = strtoupper ($ptstate); // knock state to upper case
	 // reassemble email
	 if ((strlen($ptemail1)>1) AND (strlen($ptemail2)>3))
	   $ptemail = $ptemail1 . "@" . $ptemail2;
	 $query = "UPDATE patient SET ".
	   "ptdtmod     ='$ptdtmod',      ".
	   "ptdob       ='$ptdob',        ".
	   "ptbal       ='$ptbal',        ".
	   "ptbalfwd    ='$ptbalfwd',     ".
	   "ptunapp     ='$ptunapp',      ".
	   "ptrefdoc    ='$ptrefdoc',     ".
	   "ptpcp       ='$ptpcp',        ".
	   "ptphy1      ='$ptphy1',       ".
	   "ptphy2      ='$ptphy2',       ".
	   "ptphy3      ='$ptphy3',       ".
	   "ptphy4      ='$ptphy4',       ".
	   "ptbilltype  ='$ptbilltype',   ".
	   "ptbudg      ='$ptbudg',       ".
	   "ptdoc       ='$ptdoc',        ".
	   "ptlname     ='$ptlname',      ".
	   "ptfname     ='$ptfname',      ".
	   "ptmname     ='$ptmname',      ".
	   "ptaddr1     ='$ptaddr1',      ".
	   "ptaddr2     ='$ptaddr2',      ".
	   "ptcity      ='$ptcity',       ".
	   "ptstate     ='$ptstate',      ".
	   "ptzip       ='$ptzip',        ".
	   "ptcountry   ='$ptcountry',    ". // 19990728 country add
	   "pthphone    ='$pthphone',     ".
	   "ptwphone    ='$ptwphone',     ".
	   "ptfax       ='$ptfax',        ".
	   "ptemail     ='$ptemail',      ".
	   "ptsex       ='$ptsex',        ".
	   "ptdob       ='$ptdob',        ".
	   "ptssn       ='$ptssn',        ".
	   "ptdmv       ='$ptdmv',        ".
	   "ptdtlpay    ='$ptdtlpay',     ".
	   "ptamtlpay   ='$ptpaytype',    ".
	   "ptstatus    ='$ptstatus',     ".
	   "ptytdchg    ='$ptstatus',     ".
	   "ptar        ='$ptar',         ".
	   "ptextinf    ='$ptextinf',     ".
	   "ptdisc      ='$ptdisc',       ".
	   "ptdol       ='$ptdol',        ".
	   "ptdiag1     ='$ptdiag1',      ".
	   "ptdiag2     ='$ptdiag2',      ".
	   "ptdiag3     ='$ptdiag3',      ".
	   "ptdiag4     ='$ptdiag4',      ".
	   "ptid        ='$ptid',         ".
	   "pthistbal   ='$pthistbal',    ".
	   "ptmarital   ='$ptmarital',    ".
	   "ptempl      ='$ptempl',       ".
	   "ptemp1      ='$ptemp1',       ".
	   "ptemp2      ='$ptemp2',       ".
	   "ptguar      ='$ptguar',       ". // guars and ins's
	   "ptguarstart ='$ptguarstart',  ". // are collapsed arrays
	   "ptguarend   ='$ptguarend',    ".
	   "ptrelguar   ='$ptrelguar',    ".
	   "ptins       ='$ptins',        ".
	   "ptinsno     ='$ptinsno',      ".
	   "ptinsgrp    ='$ptinsgrp',     ".
	   "ptinsstart  ='$ptinsstart',   ". // are collapsed arrays
	   "ptinsend    ='$ptinsend',     ".
	   "ptnextofkin ='$ptnextofkin',  ". // 19990728 next of kin add
	   "iso         ='iso'            ". // 19991228
	   "WHERE id='$id'";
         break; // end mod
     } // end switch for action (done .. actual action)
     echo "
      <CENTER><$STDFONT_B>".( (($action=="mod") OR ($action=="modform")) ?
             _("Modifying") : _("Adding") )." ... ";
     $result = $sql->query($query);
     if ($result) echo _("Done");
     else echo _("Error");
     echo "
      <$STDFONT_E>
      <P><$STDFONT_B>
      <A HREF=\"manage.php?$_auth&id=$id\">
      "._("Manage This Patient")."
      </A><$STDFONT_E>
      
      </CENTER>
     ";
   } // end checking if done

   break; // end action add/mod

  case "delete":
  case "del":
    freemed_display_box_top (_("Deleting")." "._($record_name));
    echo "<CENTER>
     <P><$STDFONT_B>"._("Deleting")." ... ";
    $query = "DELETE FROM patient WHERE id='".addslashes($id)."'";
    $result = $sql->query ($query);
    if ($result) { echo _("done")."."; }
     else        { echo _("ERROR");    }
    echo "
     <$STDFONT_E>
     </CENTER>
     <P>
     <CENTER>
     <A HREF=\"patient.php?$_auth\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
     </CENTER>
    ";
  break; // end action delete

  case "find":
    switch ($criteria) {
      case "letter":
        $query = "SELECT ptlname,ptfname,ptdob,ptid,id FROM patient ".
         "WHERE (ptlname LIKE '$f1%') ".
         "ORDER BY ptlname, ptfname, ptdob";
        $_crit = "Last Names ($f1)";
        break;
      case "contains":
        $query = "SELECT ptlname,ptfname,ptdob,ptid,id FROM patient ".
         "WHERE ($f1 LIKE '%$f2%') ".
         "ORDER BY ptlname, ptfname, ptdob";
        $_crit = "Searching for \"$f2\"";
        break;
      case "soundex":
        $query = "SELECT ptlname,ptfname,ptdob,ptid,id FROM patient ".
         "WHERE (soundex($f1) = soundex('$f2')) ".
         "ORDER BY ptlname, ptfname, ptdob";
        $_crit = "Sounds Like \"$f2\"";
        break;
      case "all":
        $query = "SELECT ptlname,ptfname,ptdob,ptid,id FROM patient ".
         "ORDER BY ptlname, ptfname, ptdob";
        $_crit = "\"All Patients\"";
        break;
      case "dependants":
        $query = "SELECT ptlname,ptfname,ptdob,ptid,id FROM patient ".
         "WHERE (ptdep = '$f1') ".
         "ORDER BY ptlname, ptfname, ptdob";
        $_crit = "Dependents";
        break;
      case "guarantor":
        $query = "SELECT ptlname,ptfname,ptdob,ptid,id FROM patient ".
         "WHERE (id = '$f1') ".
         "ORDER BY ptlname, ptfname, ptdob";
        $_crit = "Guarantor";
        break;
      default:
        $_crit = "";
        break;
    } // end criteria search

    $result = $sql->query($query); 

      freemed_display_html_top ();
      freemed_display_banner ();
      freemed_display_box_top (_("$Patients_meeting_criteria $_crit"),
        $page_name);

      if (strlen($_ref)<5) {
        $_ref="main.php";
      } // if no ref, then return to home page...

      echo freemed_display_itemlist(
        $result,
	$page_name,
	array (
	  _("Last Name") =>     "ptlname",
	  _("First Name") =>    "ptfname",
	  _("Date of Birth") => "ptdob",
	  _("Practice ID") =>   "ptid"
	),
	array ("","",""),
	"", "", "",
	ITEMLIST_MOD|ITEMLIST_VIEW
      );

      echo "
       <P>
       <CENTER>
        <A HREF=\"$page_name?$_auth\"
        ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
       </CENTER>
       <P>
       ";
      freemed_display_box_bottom (); // display bottom of the box
  break; // end action find
 
  case "display":
  case "view":
    // KludgE AlerTx0r!
    header("Location:".ereg_replace("patient.php",
           "manage.php", $REQUEST_URI));
  break;

  default: // default action
    freemed_display_html_top ();
    freemed_display_banner ();
    freemed_display_box_top (_("Patients"), $_ref, $page_name);
  
    if (freemed_get_userlevel($LoginCookie)>$database_level) {
      echo "
        <TABLE WIDTH=100% BGCOLOR=#000000 BORDER=0 CELLSPACING=0
         CELLPADDING=0 VALIGN=TOP ALIGN=CENTER><TR><TD>
        <FONT FACE=\"Arial, Helvetica, Verdana\" COLOR=#ffffff>
      ";
      $result = $sql->query ("SELECT COUNT(*) FROM patient");
      if ($result) {
        $_res   = $sql->fetch_array ($result);
        $_total = $_res[0];               // total number in db
  
          // patched 19990622 for 1 and 0 values...
        if ($_total>1)
          echo "
            <CENTER>
             <B><I>$_total "._("Patient(s) In System")."</I></B>
            </CENTER>
          ";
        elseif ($_total==0)
          echo "
            <CENTER>
             <B><I>"._("No Patients In System")."</I></B>
            </CENTER>
          ";
        elseif ($_total==1)
          echo "
            <CENTER>
            <B><I>"._("One Patient In System")."</I></B>
            </CENTER>
          ";
      } else {
        echo "
          <CENTER>
           <B><I>"._("No Patients In System")."</I></B>
          </CENTER>
        ";
      } // if there are none...
      echo "
        </FONT>
        </TD></TR></TABLE>
      "; // end table statement for bar
    }

    if ($current_patient>0) {
      $patient = new Patient ($current_patient);
      echo "
        <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=0 ALIGN=CENTER
         VALIGN=CENTER BORDER=0><TR><TD ALIGN=CENTER><CENTER>
	 <$STDFONT_B><A HREF=\"manage.php?$_auth&id=$current_patient\"
         >"._("Patient")." : ".$patient->fullName(true)."</A>
	 <$STDFONT_E>
         </CENTER></TD></TR></TABLE>
      ";
    } // end check for current patient cookie

    echo "
      <BR>
      <CENTER>
       <B><$STDFONT_B>"._("Patients By Name")."<$STDFONT_E></B>
      <BR>
      <$STDFONT_B>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=A\">A</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=B\">B</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=C\">C</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=D\">D</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=E\">E</A>
  
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=F\">F</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=G\">G</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=H\">H</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=I\">I</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=J\">J</A>
  
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=K\">K</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=L\">L</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=M\">M</A>
      <$STDFONT_E>
      <BR>
      <$STDFONT_B>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=N\">N</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=O\">O</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=P\">P</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=Q\">Q</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=R\">R</A>
  
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=S\">S</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=T\">T</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=U\">U</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=V\">V</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=W\">W</A>
  
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=X\">X</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=Y\">Y</A>
      <A HREF=\"$page_name?$_auth&action=find&criteria=letter&f1=Z\">Z</A>
      <$STDFONT_E>

      <P>

      <FORM ACTION=\"$page_name\" METHOD=POST>
       <B><$STDFONT_B>"._("Patients Field Search")."<$STDFONT_E></B>
      <BR>
      <INPUT TYPE=HIDDEN NAME=\"action\"   VALUE=\"find\">
      <INPUT TYPE=HIDDEN NAME=\"criteria\" VALUE=\"contains\">
      <SELECT NAME=\"f1\">
       <OPTION VALUE=\"ptlname\" SELECTED>"._("Last Name")."
       <OPTION VALUE=\"ptfname\" >"._("First Name")."
       <OPTION VALUE=\"ptdob\"   >"._("Date of Birth")."
       <OPTION VALUE=\"ptid\"    >"._("Internal Practice ID")."
       <OPTION VALUE=\"ptcity\"  >"._("City")."
       <OPTION VALUE=\"ptstate\" >"._("State")."
       <OPTION VALUE=\"ptzip\"   >"._("Zip")."
       <OPTION VALUE=\"pthphone\">"._("Home Phone")."
       <OPTION VALUE=\"ptwphone\">"._("Work Phone")."
       <OPTION VALUE=\"ptemail\" >"._("Email Address")."
       <OPTION VALUE=\"ptssn\"   >"._("Social Security Number")."
       <OPTION VALUE=\"ptdmv\"   >"._("Driver's License")."
      </SELECT>
      <I><$STDFONT_B SIZE=\"-1\">"._("contains")."<$STDFONT_E></I>
      <INPUT TYPE=TEXT NAME=\"f2\" SIZE=15 MAXLENGTH=30>
      <INPUT TYPE=SUBMIT VALUE=\"find\">
      </FORM>
      <P>

      <FORM ACTION=\"$page_name\" METHOD=POST>
      <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"find\">
      <INPUT TYPE=HIDDEN NAME=\"criteria\" VALUE=\"soundex\">
      <B><$STDFONT_B>"._("Soundalike Search")."<$STDFONT_E></B><BR>
      <SELECT NAME=\"f1\">
       <OPTION VALUE=\"ptlname\" >"._("Last Name")."
       <OPTION VALUE=\"ptfname\" >"._("First Name")."
      </SELECT>
        <I><$STDFONT_B SIZE=\"-1\">"._("sounds like")."<$STDFONT_E></I>
      <INPUT TYPE=TEXT NAME=\"f2\" SIZE=15 MAXLENGTH=30>
      <INPUT TYPE=SUBMIT VALUE=\"find\">
      </FORM>
      <P>

      <A HREF=\"$page_name?$_auth&action=find&criteria=all&f1=\"
       ><$STDFONT_B>"._("Show all Patients")."<$STDFONT_E></A> |
      <A HREF=\"$page_name?$_auth&action=addform\"
       ><$STDFONT_B>"._("Add Patient")."<$STDFONT_E></A> |
      <A HREF=\"call-in.php?$_auth\"
       ><$STDFONT_B>"._("Call In Menu")."<$STDFONT_E></A>
      <P> 
      </CENTER>
      <CENTER>
      <A HREF=\"main.php?$_auth\"
      ><$STDFONT_B>"._("Return to Main Menu")."<$STDFONT_E></A>
      </CENTER>
    ";

    freemed_display_box_bottom (); 

    break; // end default action
} // end action

freemed_display_box_bottom();
freemed_close_db(); 
freemed_display_html_bottom ();
?>


