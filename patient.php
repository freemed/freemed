<?php
 // $Id$
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

  freemed_open_db ($LoginCookie); // authenticate user

// NULL at top so next() works...
$t_vars = array("NULL", "ptguar","ptrelguar", "ptguarstart", "ptguarend",
    "ptins", "ptinsno", "ptinsgrp", "ptinsstart", "ptinsend"); 

switch ($action) {
  case "add": case "addform":
  case "mod": case "modform":
    // addform and modform not used due to "notebook"
   $book = new notebook ( array ("action", "_auth", "id", "been_here"),
     NOTEBOOK_COMMON_BAR|NOTEBOOK_STRETCH, 3);
   $book->set_submit_name (_("OK"));
   switch ($action) {
     case "add": case "addform":
      if (empty($been_here)) {
        $ins_disp_inactive=false; // TODO! not implemented
        $been_here = "1"; // set been_here
      } // end of checking empty been_here
      $action_name = _("Add");
      break; // end internal add

     case "mod": case "modform":
      if (empty($been_here)) {
        $result = $sql->query("SELECT * FROM patient ".
          "WHERE ( id = '$id' )");

        $r = $sql->fetch_array($result); // dump into array r[]
	extract($r); // pull variables in from array

	reset($t_vars);
	while ($i=next($t_vars)) { // for all these TEXT items
	  if (strlen($$i)>0)
            $$i = fm_split_into_array($$i); // pull the array items
	  else
	    $$i = array (); // set to null array if strlen<1
	} // for each TEXT item

        $ptstate      = strtoupper ($ptstate);

        // 19990728 -- next of kin pull and remake
        $ptnextofkin  = htmlentities ($ptnextofkin);

        // resplit email
        if (strlen($ptemail)>3) {
          $ptemail_array = explode ("@", $ptemail);
          $ptemail1      = $ptemail_array[0];
          $ptemail2      = $ptemail_array[1];
        } // end of resplit email
	
        $ins_disp_inactive=false;
        $been_here = "1"; // set been_here
      } // end of checking empty been_here
      $action_name = _("Modify");
      break; // end internal mod
   } // end of internal switch add/mod

   // ** DISPLAY ADD/MOD ***

   if (!isset($num_inscos)) { // first time through
     $num_inscos=count($ptins);
     if ($num_inscos>0) $has_insurance=true;
   } // checking for unset num_inscos
   
   $book->add_page (
     _("Primary Information"),
     array ("ptlname", "ptfname", "ptmname",
            date_vars("ptdob"),
            "ptaddr1", "ptaddr2", "ptcity", "ptstate", "ptzip", "ptcountry",
            "has_insurance"),
     "
       <!-- primary information page -->
    <TABLE CELLSPACING=0 CELLPADDING=2 BORDER=0>

    <TR><TD ALIGN=RIGHT>
     <$STDFONT_B>"._("Last Name")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"ptlname\" SIZE=25 MAXLENGTH=50
     VALUE=\"".prepare($ptlname)."\">
    </TD></TR>
    
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("First Name")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"ptfname\" SIZE=25 MAXLENGTH=50
     VALUE=\"".prepare($ptfname)."\">
    </TD></TR>

    <TR><TD ALIGN=RIGHT>
     <$STDFONT_B>"._("Middle Name")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
     <INPUT TYPE=TEXT NAME=ptmname SIZE=25 MAXLENGTH=50
      VALUE=\"".prepare($ptmname)."\">
    </TD></TR>

    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Address Line 1")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"ptaddr1\" SIZE=25 MAXLENGTH=45
     VALUE=\"".prepare($ptaddr1)."\">
    </TD></TR>

    <TR><TD ALIGN=RIGHT>
     <$STDFONT_B>"._("Address Line 2")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"ptaddr2\" SIZE=25 MAXLENGTH=45
     VALUE=\"".prepare($ptaddr2)."\">
    </TD></TR>

    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("City").", "._("State").", "._("Zip")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"ptcity\" SIZE=10 MAXLENGTH=45
     VALUE=\"".prepare($ptcity)."\">
    <INPUT TYPE=TEXT NAME=\"ptstate\" SIZE=3 MAXLENGTH=2
     VALUE=\"".prepare($ptstate)."\"> 
    <INPUT TYPE=TEXT NAME=\"ptzip\" SIZE=10 MAXLENGTH=10
     VALUE=\"".prepare($ptzip)."\">
    </TD></TR>

    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Date of Birth")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".date_entry("ptdob")."
    </TD></TR>

    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Has Insurance")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    
    <INPUT TYPE=CHECKBOX NAME=\"has_insurance\" ".
      (($has_insurance) ? "CHECKED" : "").">
    </TD></TR>

    </TABLE>

     ");

     $book->add_page(
       _("Contact"),
       array (
         "ptaddr1", "ptaddr2", "ptcity", "ptstate", "ptzip",
	 "ptcountry", phone_vars("pthphone"), phone_vars("ptwphone"),
	 phone_vars("ptfax")
         ),
       "
  <TABLE CELLPADDING=2 CELLSPACING=0 BORDER=0>
  <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Country")." : <$STDFONT_E>
  </TD><TD ALIGN=LEFT>
    ".country_pulldown("ptcountry")."
  </TD></TR>

  <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Home Phone")." : <$STDFONT_E>
  </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("pthphone")."
  </TD></TR>

  <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Work Phone")." : <$STDFONT_E>
  </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("ptwphone")."
  </TD></TR>
    
  <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Fax Number")." : <$STDFONT_E>
  </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("ptfax")."
  </TD></TR>
  
  <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Email Address")." : <$STDFONT_E>
  </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=ptemail1 SIZE=20 MAXLENGTH=40
     VALUE=\"".prepare($ptemail1)."\"> <B>@</B>
    <INPUT TYPE=TEXT NAME=ptemail2 SIZE=20 MAXLENGTH=40
     VALUE=\"".prepare($ptemail2)."\">
  </TD></TR>
  </TABLE>
       "
     );

     $book->add_page(
       _("Personal"),
       array (
         "ptsex", "ptmarital", "ptssn", "ptid",
	 "ptdmv", "ptbilltype", "ptbudg"
	 ),
       "
  <TABLE>
  <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Gender")." : <$STDFONT_E>
  </TD><TD ALIGN=LEFT>
    ".select_widget("ptsex",
      array (
        _("Female")        => "f",
        _("Male")          => "m",
        _("Transgendered") => "t") )."
  </TD></TR>

  <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Marital Status")." : <$STDFONT_E>
  </TD><TD ALIGN=LEFT>
    ".select_widget("ptmarital",
      array (
        _("Single")    => "single",
	_("Married")   => "married",
	_("Divorced")  => "divorced",
	_("Separated") => "separated",
	_("Widowed")   => "widowed") )."
  </TD></TR>
	
  <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Social Security Number")." : <$STDFONT_E>
  </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"ptssn\" SIZE=9 MAXLENGTH=10
     VALUE=\"".prepare($ptssn)."\">
  </TD></TR>

  <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Internal Practice ID #")." : <$STDFONT_E>
  </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"ptid\" SIZE=10 MAXLENGTH=10
     VALUE=\"".prepare($ptid)."\">
  </TD></TR>
    
  <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Driver's License (No State)")." : <$STDFONT_E>
  </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=ptdmv SIZE=10 MAXLENGTH=9
     VALUE=\"".prepare($ptdmv)."\">
  </TD></TR>
       
  <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Type of Billing")." : <$STDFONT_E>
  </TD><TD ALIGN=LEFT>
    ".select_widget("ptbilltype",
        array (
	  _("Monthly Billing On Account") => "mon",
	  _("Statement Billing")          => "sta",
	  _("Charge Card Billing")        => "chg",
	  _("NONE SELECTED")              => ""
	) )."
  </TD></TR>

  <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Monthly Budget Amount")." : <$STDFONT_E>
  </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=ptbudg SIZE=10 MAXLENGTH=20
     VALUE=\"".prepare($ptbudg)."\">
   </TD></TR>
  </TABLE>
     ");


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
      ".number_pulldown("num_other_docs", 0, 4)."
    </TD></TR>

    </TABLE>
     ");     

  if ($has_insurance) { 

    // push the current insco onto the stack
    if ($add_this_insco AND ($ptins_>0)) { // not persistent! use once!
      $add_this_insco = false;
      if (isset($ptins_arr)) {
        $ptins[$ptins_arr]      = $ptins_;
        $ptinsno[$ptins_arr]    = $ptinsno_;
        $ptinsgrp[$ptins_arr]   = $ptinsgrp_;
        $ptinsstart[$ptins_arr] = fm_date_assemble("ptinsstart_");
        $ptinsend[$ptins_arr]   = fm_date_assemble("ptinsend_");
        unset($ptins_arr);
      } else {
        $ptins[]      = $ptins_;
        $ptinsno[]    = $ptinsno_;
        $ptinsgrp[]   = $ptinsgrp_;
        $ptinsstart[] = fm_date_assemble("ptinsstart_");
        $ptinsend[]   = fm_date_assemble("ptinsend_");
      }
    }
    if (is_array($ptins)) {
      while (list($idx,$val)=each($ptins)) {
        $ptins_active[$idx]=( $ins_disp_inactive OR
          (!date_in_range($cur_date,$ptinsstart[$idx],$ptinsend[$idx])) );
	// unsure why, but have to take !date_in_range...
      } // for each insurance co, determine if active
      reset($ptins);
      for ($arr_idx=0;$arr_idx<count($ptins);) { // increment at bottom
        if (!isset($ptinsmod)) $ptinsmod=-1; // sanity check
	if ($ptinsmod==$arr_idx) { // push it onto the current one
          $ptins_      = $ptins[$arr_idx];
          $ptinsno_    = $ptinsno[$arr_idx];
          $ptinsgrp_   = $ptinsgrp[$arr_idx];
          $ptinsstart_ = $ptins[$arr_idx]; // what's the 
          $ptinsend_   = $ptins[$arr_idx]; // opposite of fm_date_assemble?
	  $ptins_arr   = $arr_idx;
	  unset($ptins[$arr_idx]); // take it off the stack, no duplication
	}
        if ($ptinsdel[$arr_idx]) unset($ptins[$arr_idx]); // delete 
        $arr_idx++; // increment at *end*!
      } // for each insco listed
    } // if ptins is an array
    
    $ins_r = freemed_search_query( array ($ins_s_val => $ins_s_field),
               array ("insconame", "inscostate", "inscocity"), "insco", 
	       "ptins_");
    
    $book->add_page(
      _("Insurance"),
      array ("ptins", "ptinsno", "ptinsgrp", "ptinsstart",
        "ptinsend", "ins_disp_inactive", "ins_s_val", "ins_s_field", 
	"ptins_", "ptinsno_", "ptinsgrp_", date_vars("ptinsstart_"), 
	date_vars("ptinsend_"), "ptins_arr"),
      "
    <TABLE CELLSPACING=0 CELLPADDING=2 BORDER=0 WIDTH=\"100%\">
     <TR><TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Display Inactive Insurance Companies")." : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=CHECKBOX NAME=\"ins_disp_inactive\"".
       /*($ins_disp_inactive ? " CHECKED" : "").*/" CHECKED>
     </TD></TR>
     <TR><TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Insurance")."<FONT SIZE=\"-1\">".
       select_widget("ins_s_field", array (
        _("Name") => "insconame",
	_("City") => "inscocity" ) )."</FONT><$STDFONT_E>
      <$STDFONT_B>"._("like")."<FONT SIZE=\"-1\">
        <INPUT TYPE=TEXT NAME=\"ins_s_val\" 
        VALUE=\"$ins_s_val\" SIZE=15 MAXLENGTH=20></FONT>
      <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <$STDFONT_B>"._("Add This Insurance Company")." : 
       <INPUT TYPE=CHECKBOX NAME=\"add_this_insco\">
       ".(isset($ptins_arr) ? 
         "<INPUT TYPE=HIDDEN NAME=\"ptins_arr\" VALUE=\"$ptins_arr\">" 
	                       : "")."
     </TD></TR>
      <TR><TD ALIGN=RIGHT>
        <$STDFONT_B>
         "._("Insurance Company")." :
        <$STDFONT_E>
      </TD><TD ALIGN=LEFT>
        <$STDFONT_B SIZE=\"-1\">
         ".freemed_display_selectbox($ins_r, 
            "#insconame# (#inscocity#, #inscostate# #inscozip#)", 
	    "ptins_")."
        <$STDFONT_E>
      </TD></TR>
	
      <TR><TD ALIGN=RIGHT>
        <$STDFONT_B>
         "._("Insurance ID / Group ID")." : 
	<$STDFONT_E>
      </TD><TD ALIGN=LEFT>
	<$STDFONT_B SIZE=\"-1\">
	  <INPUT NAME=\"ptinsno_\"  VALUE=\"".$ptinsno_."\"  SIZE=15>
	   <INPUT NAME=\"ptinsgrp_\" VALUE=\"".$ptinsgrp_."\" SIZE=15>
	<$STDFONT_E>
      </TD></TR>
	
      <TR><TD ALIGN=RIGHT>
        <$STDFONT_B>"._("Start Date")."<$STDFONT_E>
	<$STDFONT_B SIZE=\"-1\">
	  ".date_entry("ptinsstart_", 1990, "mdy")."
	<$STDFONT_E>
      </TD><TD ALIGN=LEFT>
	<$STDFONT_B>"._("End Date")."<$STDFONT_E>
	<$STDFONT_B SIZE=\"-1\">
	  ".date_entry("ptinsend_", 1990, "mdy")."
	<$STDFONT_E>
      </TD></TR>
      <TR WIDTH=\"100%\"><TD COLSPAN=2>
        ".freemed_display_arraylist(
	    array (_("Insurer"  ) => "ptins",
	           _("Start"    ) => "ptinsstart",
	           _("End"      ) => "ptinsend",
	           _("ID Number") => "ptinsno",
	           _("Group"    ) => "ptinsgrp"), 
	    array ("insco" => "insconame",
	           "",
		   "",
		   "",
		   ""))."
      </TD></TR>
    </TABLE>
      "
    );

    // push the current guarantor onto the stack
    if ($add_this_guar AND ($ptguar_>0)) { // not persistent! use once!
      $add_this_guar = false;
      $ptguar[]      = $ptguar_;
      $ptrelguar[]   = $ptrelguar_;
      $ptguarstart[] = fm_date_assemble("ptguarstart_");
      $ptguarend[]   = fm_date_assemble("ptguarend_");
    }
    $guar_r = freemed_search_query( array ($guar_s_val => $guar_s_field),
               array ("ptlname", "ptfname", "ptdob"), "patient", "");
    if (is_array($ptguar)) {
      while (list($idx,$val)=each($ptguar)) {
        $ptguar_active[$idx]=( true /* $guar_disp_inactive OR
          (!date_in_range($cur_date,$ptinsstart[$idx],$ptinsend[$idx])) */);
	// unsure why, but have to take !date_in_range...
      } // for each insurance co, determine if active
      reset($ptguar);
      for ($arr_idx=0;$arr_idx<count($ptguar);) { // increment at bottom
        if (!isset($ptguarmod)) $ptguarmod=-1; // sanity check
	if ($ptguarmod==$arr_idx) { // push it onto the current one
          $ptguar_ = $ptins[$arr_idx];echo $arr_idx;
          $ptrelguar_ = $ptrelguar[$arr_idx];
          $ptguarstart_ = $ptguarstart[$arr_idx]; // what's the 
          $ptguarend_ = $ptguarend[$arr_idx]; // opposite of fm_date_assemble?
	  $ptguar[$arr_idx]=-1; // take it off the stack so it's not added again
	  $arr_idx++;
	}
        if ($ptguardel[$arr_idx]) {$ptins[$arr_idx]=-1; $arr_idx++;} // delete 
        if ($ptins[$arr_idx]==-1) continue; // take this one off the list
        if ($ins_disp_inactive OR !date_in_range($cur_date,
            $ptinsstart[$arr_idx], $ptinsend[$arr_idx]) ) {
        } // if it's a visible insco
	$arr_idx++;
      } // for each guar listed
    } // if inscos already exist
   
   $dep_r = freemed_search_query ( 
        array ($dep_s_val => $dep_s_field, $dep_s_val2 => $dep_s_field2),
        array ("ptlname", "ptfname"), "patient", 
        "ptguar_" );
   
   $book->add_page(
     _("Guarantor"),
     array ("ptguar", "ptrelguar", "ptguarstart", "ptguarend",
            "ptguar_","ptrelguar_","ptguarstart_","ptguarend_",
            "guar_disp_inactive", "dep_s_field", "dep_s_val",
            "dep_s_field2", "dep_s_val2"),
     "
    <TABLE CELLSPACING=0 CELLPADDING=2 BORDER=0 WIDTH=\"100%\">
    <TR><TD ALIGN=RIGHT>
     <$STDFONT_B>"._("Guarantor").
     select_widget("dep_s_field", array (
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
     select_widget("dep_s_field2", array (
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
          "#ptlname#, #ptfname#", "ptguar_")."
    </TD></TR>
    
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Relation to Guarantor")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".select_widget("ptrelguar_", array (
        _("Self")    => "S",
        _("Child")   => "C",
        _("Husband") => "H",
        _("Wife")    => "W",
        _("Other")   => "O"
	) )."
    </TD></TR>
    
    <TR><TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Add This Guarantor")." : 
       <INPUT TYPE=CHECKBOX NAME=\"add_this_guar\"><$STDFONT_E>
    </TD><TD ALIGN=LEFT>

    </TD></TR>
    
    <TR WIDTH=\"100%\"><TD COLSPAN=2>
        ".freemed_display_arraylist(
	    array (_("Guarantor"   ) => "ptguar",
	           _("Relationship") => "ptrelguar",
	           _("Start"       ) => "ptguarstart",
	           _("End"         ) => "ptguarend"),
	    array ("patient" => "ptlname",
	           "",
		   "",
		   ""))."
    </TD></TR>
	
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
	 reset($t_vars);while ($i=next($t_vars)) 
	                  $$i = fm_join_from_array($$i);

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
           NULL ) ";
	 break; // end add
       case "mod": case "modform":
         // collapse the TEXT variables...
	 reset($t_vars);while ($i=next($t_vars)) 
	                  if (is_array($$i)) $$i = implode(':', $$i);
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


