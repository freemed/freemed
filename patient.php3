<?php
 // file: patient.php3
 // note: patient database functions
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 //       adam b (gdrago23@yahoo.com)
 //       some small stuff by: max k <amk@span.ch>
 // lic : GPL, v2
 
  $page_name="patient.php3"; // for help info, later
  $record_name="Patient";    // compatibility with API functions
  include ("global.var.inc");
  include ("freemed-functions.inc");
  include ("freemed-calendar-functions.inc");

  SetCookie ("_ref", $page_name, time()+$_cookie_expire);

  if ( ($id>0) AND 
       ($action != "addform") AND ($action != "add") AND
       ($action != "delform") AND ($action != "del")) {
    SetCookie ("current_patient", $id, time()+$_cookie_expire);
    $current_patient = $id;   // patch for first time....
  } // end checking for current_patient value

  freemed_open_db ($LoginCookie); // authenticate user

$t_vars = array("ptguar","ptrelguar", "ptguarstart", "ptguarend",
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
        $result = fdb_query("SELECT * FROM patient ".
          "WHERE ( id = '$id' )");

        $r = fdb_fetch_array($result); // dump into array r[]
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


   $ref_phys_r = fdb_query("SELECT phylname,phyfname,id
                            FROM physician WHERE phyref='yes' 
                            ORDER BY phylname,phyfname");
   $int_phys_r = fdb_query("SELECT phylname,phyfname,id
                            FROM physician WHERE phyref='no' 
                            ORDER BY phylname,phyfname");
   $all_phys_r = fdb_query("SELECT phylname,phyfname,id
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
      $ptins[]      = $ptins_;
      $ptinsno[]    = $ptinsno_;
      $ptinsgrp[]   = $ptinsgrp_;
      $ptinsstart[] = fm_date_assemble("ptinsstart_");
      $ptinsend[]   = fm_date_assemble("ptinsend_");
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
	  unset($ptins[$arr_idx]); // take it off the stack, no duplication
	}
        if ($ptinsdel[$arr_idx]) unset($ptins[$arr_idx]); // delete 
        $arr_idx++; // increment at *end*!
      } // for each insco listed
    } // if ptins is an array
    
    $ins_r = freemed_search_query( array ($ins_s_val => $ins_s_field),
               array ("insconame", "inscostate", "inscocity"), "insco", "");
    
    $book->add_page(
      _("Insurance"),
      array ("ptins", "ptinsno", "ptinsgrp", "ptinsstart",
        "ptinsend", "ins_disp_inactive", "ins_s_val", "ins_s_field", 
	"ptins_", "ptinsno_", "ptinsgrp_", date_vars("ptinsstart_"), 
	date_vars("ptinsend_")),
      "
    <TABLE CELLSPACING=0 CELLPADDING=2 BORDER=0 WIDTH=\"100%\">
     <TR><TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Display Inactive Insurance Companies")." : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=CHECKBOX NAME=\"ins_disp_inactive\"".
       ($ins_disp_inactive ? " CHECKED" : "").">
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
        $ptguar_active[$idx]=( $guar_disp_inactive OR
          (!date_in_range($cur_date,$ptinsstart[$idx],$ptinsend[$idx])) );
	// unsure why, but have to take !date_in_range...
      } // for each insurance co, determine if active
      reset($ptguar);
      for ($arr_idx=0;$arr_idx<count($ptguar);) { // increment at bottom
        if (!isset($ptguarmod)) $ptguarmod=-1; // sanity check
	if ($ptinsmod==$arr_idx) { // push it onto the current one
          $ptins_ = $ptins[$arr_idx];echo $arr_idx;
          $ptinsno_ = $ptinsno[$arr_idx];
          $ptinsgrp_ = $ptinsgrp[$arr_idx];
          $ptinsstart_ = $ptins[$arr_idx]; // what's the 
          $ptinsend_ = $ptins[$arr_idx]; // opposite of fm_date_assemble?
	  unset($ptins[$arr_idx]); // take it off the stack so it's not added again
	  $arr_idx++;
	}
        if ($ptinsdel[$arr_idx]) unset($ptins[$arr_idx]); // delete 
        if (!isset($ptins[$arr_idx])) continue; // take this one off the list
        if ($ins_disp_inactive OR !date_in_range($cur_date,
            $ptinsstart[$arr_idx], $ptinsend[$arr_idx]) ) {
        } // if it's a visible insco
      } // for each insco listed
    } // if inscos already exist
    
   $dep_r = freemed_search_query ( 
        array ($dep_s_val => $dep_s_field, $dep_s_val2 => $dep_s_field2),
        array ("ptlname", "ptfname"), "patient", 
        "ptdep" );

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
          "#ptlname#, #ptfname#", "ptdep")."
    </TD></TR>
    
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Relation to Guarantor")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".select_widget("ptreldep", array (
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
     $result = fdb_query($query);
     if ($result) echo _("Done");
     else echo _("Error");
     echo "
      <$STDFONT_E>
      <P><$STDFONT_B>
      <A HREF=\"manage.php3?$_auth&id=$id\">
      "._("Manage This Patient")."
      </A><$STDFONT_E>
      
      </CENTER>
     ";
   } // end checking if done

   break; // end action add/mod

  case "del":
    // STUB
    echo "del STUB <BR>\n";
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

    $result = fdb_query($query); 

      freemed_display_html_top ();
      freemed_display_banner ();
      freemed_display_box_top (_("$Patients_meeting_criteria $_crit"),
        $page_name);

      if (strlen($_ref)<5) {
        $_ref="main.php3";
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
  
  case "view":
    // KludgE AlerTx0r!
    header("Location:".ereg_replace("patient.php3",
           "manage.php3", $REQUEST_URI));
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
      $result = fdb_query ("SELECT COUNT(*) FROM patient");
      if ($result) {
        $_res   = fdb_fetch_array ($result);
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
	 <$STDFONT_B><A HREF=\"manage.php3?$_auth&id=$current_patient\"
         >$Current_Patient : ".$patient->fullName(true)."</A>
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
       <OPTION VALUE=\"ptdob\"   >"._("Date of birth")."
       <OPTION VALUE=\"ptid\"    >"._("Internal Practice ID")."
       <OPTION VALUE=\"ptcity\"  >"._("City")."
       <OPTION VALUE=\"ptstate\" >"._("State")."
       <OPTION VALUE=\"ptzip\"   >"._("Zip Code")."
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
      <A HREF=\"call-in.php3?$_auth\"
       ><$STDFONT_B>"._("Call In Menu")."<$STDFONT_E></A>
      <P> 
      </CENTER>
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
      ><$STDFONT_B>"._("Return to Main Menu")."<$STDFONT_E></A>
      </CENTER>
    ";

    freemed_display_box_bottom (); 

    break; // end default action
} // end action

freemed_display_box_bottom();



/* 
  BEGIN OBSOLETE JUNK!!!
  BEGIN OBSOLETE JUNK!!!
  BEGIN OBSOLETE JUNK!!!
  BEGIN OBSOLETE JUNK!!!

if ($action=="addform") {

  echo "
     ";
  //////////

  /////////
  freemed_display_ptstatus ($ptstatus, "ptstatus");
  echo "
    <BR>

    <$STDFONT_B>$Discount_percent_if_applic : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptdisc SIZE=3 MAXLENGTH=2
     VALUE=\"$ptdisc\">
    <BR>


    <$STDFONT_B>$Guarantor : <$STDFONT_E>
    <SELECT NAME=ptdep>
      <OPTION VALUE=\"\">--$Self_insured--
      $_dep
    </SELECT>
    <P>

    <P>

    <$STDFONT_B>$Next_of_kin_information : <$STDFONT_E><BR>
    <TEXTAREA NAME=\"ptnextofkin\" ROWS=4 COLS=25 WRAP=VIRTUAL
     >$ptnextofkin</TEXTAREA>
    <P>

       <!-- should you be able to choose NULL for this -->

    <$STDFONT_B>$Employed_presently? : <$STDFONT_E>
    <SELECT NAME=ptempl>
      <OPTION VALUE=\"\" >$UNKNOWN
      <OPTION VALUE=\"y\">$Yes
      <OPTION VALUE=\"n\">$No
    </SELECT>
    <BR>

      <!-- employers -- come from db, not yet -->
      <!-- ptemp1/2                           -->

    <INPUT TYPE=HIDDEN NAME=ptupdt VALUE=\"$cur_date\">

    <BR><BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Add \">
    <INPUT TYPE=RESET  VALUE=\"$Clear\">
    </CENTER></FORM>
  ";
  freemed_display_box_bottom ();

} elseif ($action=="add") {

  freemed_display_box_top ("$Adding_patient", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

  //


  $result = fdb_query($query);
  if ($debug) {
    echo "\n<BR><BR><B>$Query_result :</B><BR>\n";
    echo $result;      
    echo "\n<BR><BR><B>$Query_string :</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>$Actual_query_result :</B><BR>\n";
    echo "($result)";
  }

  $id = fdb_last_record ($result, "patient");

  if ($result) {
    echo "
      <B>$Done .</B> : <$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  }

  // display link to manage this new patient
  if ($id > 0) {
    echo "
     <P>
     <CENTER>
      <A HREF=\"manage.php3?$_auth&id=$id\"
      ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
     </CENTER>
     <P>
    ";
  } // end if there *is* an id from the add

  freemed_display_box_bottom ();

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modify $Patient", $page_name);

  if (empty($id)) {
    echo "

     <B><CENTER>$Please_use_the_MODIFY_form !</B>
     </CENTER>

     <P>     
    ";

    if ($debug) {
      echo "
        ID = [<B>$id</B>]
        <P>
      ";
    }

    freemed_display_box_bottom ();
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >"._("Return to the Main Menu")."</A>
      </CENTER>
    ";
    DIE("");
  }

  echo "
    <P>
    <CENTER>
    <A HREF=\"manage.php3?$_auth&id=$id\"
     ><$STDFONT_B>Manage Patient<$STDFONT_E></A>
    </CENTER>
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\">

    <$STDFONT_B>$Date $Of_entry: $ptdtadd <$STDFONT_E>
    <BR>

    <$STDFONT_B>$Date $Of_last_mod: $ptdtmod <$STDFONT_E>
    <BR>

  <!--
    <$STDFONT_B>Diagnosis Code 1 : <$STDFONT_E>
  ";
  //freemed_display_icdcodes($ptdiag1, "ptdiag1");
  echo "
    <BR>
    <$STDFONT_B>Diagnosis Code 2 : <$STDFONT_E>
  ";
  //freemed_display_icdcodes($ptdiag2, "ptdiag2");
  echo "
    <BR>
    <$STDFONT_B>Diagnosis Code 3 : <$STDFONT_E>
  ";
  //freemed_display_icdcodes($ptdiag3, "ptdiag3");
  echo "
    <BR>
    <$STDFONT_B>Diagnosis Code 4 : <$STDFONT_E>
  ";
  //freemed_display_icdcodes($ptdiag4, "ptdiag4");
  echo "
    <BR>
  -->

    <$STDFONT_B>$Type_of_billing : <$STDFONT_E>
    <SELECT NAME=\"ptbilltype\">
      <OPTION VALUE=\"mon\">$Monthly_billing_on_acct
      <OPTION VALUE=\"sta\">$Statement_billing
      <OPTION VALUE=\"chg\">$Charge_card_billing
      <OPTION VALUE=\"\" SELECTED>$NONE_SELECTED
    </SELECT>
    <BR>

    <$STDFONT_B>$Monthly_budget_amount : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptbudg SIZE=10 MAXLENGTH=20
     VALUE=\"$ptbudg\">
    <BR>
       ";

  echo "
    <$STDFONT_B>$Primary_insurance : <$STDFONT_E>
    <SELECT NAME=\"ptins1\">
  ";

  freemed_display_insco ($ptins1);

  echo "
     </SELECT><BR>
    <$STDFONT_B>Primary Insurance Code : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"ptinsno1\" SIZE=20 MAXLENGTH=50
     VALUE=\"$ptinsno1\"><BR>
    <$STDFONT_B>Primary Insurance Group Code : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"ptinsgrp1\" SIZE=20 MAXLENGTH=50
     VALUE=\"$ptinsgrp1\"><BR>

    <$STDFONT_B>$Secondary_insurance : <$STDFONT_E>
    <SELECT NAME=\"ptins2\">
  ";

  freemed_display_insco ($ptins2);

  echo "
     </SELECT><BR>
    <$STDFONT_B>Secondary Insurance Code : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"ptinsno2\" SIZE=20 MAXLENGTH=50
     VALUE=\"$ptinsno2\"><BR>
    <$STDFONT_B>Secondary Insurance Group Code : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"ptinsgrp2\" SIZE=20 MAXLENGTH=50
     VALUE=\"$ptinsgrp2\"><BR>

    <$STDFONT_B>$Tertiary_insurance : <$STDFONT_E>
    <SELECT NAME=\"ptins3\">
  ";

  freemed_display_insco ($ptins3);

  echo "
     </SELECT><BR>
    <$STDFONT_B>Tertiary Insurance Code : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"ptinsno3\" SIZE=20 MAXLENGTH=50
     VALUE=\"$ptinsno3\"><BR>
    <$STDFONT_B>Tertiary Insurance Group Code : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"ptinsgrp3\" SIZE=20 MAXLENGTH=50
     VALUE=\"$ptinsgrp3\"><BR>

    <$STDFONT_B>Patient Status : <$STDFONT_E>
  ";
  freemed_display_ptstatus ($ptstatus, "ptstatus");
  echo "
    <BR>

    <$STDFONT_B>$Discount_percent_if_applic : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptdisc SIZE=3 MAXLENGTH=2
     VALUE=\"$ptdisc\">
    <BR>

    <$STDFONT_B>$Guarantor : <$STDFONT_E>
    <SELECT NAME=ptdep>
      <OPTION VALUE=\"\">--$Self_insured--
      $_dep
    </SELECT>
    <P>

    <$STDFONT_B>$Next_of_kin_information : <$STDFONT_E><BR>
    <TEXTAREA NAME=\"ptnextofkin\" ROWS=4 COLS=25 WRAP=VIRTUAL
     >$ptnextofkin</TEXTAREA>
    <P>

       <!-- should you be able to choose NULL for this -->

    <$STDFONT_B>$Employed_presently : <$STDFONT_E>
    <SELECT NAME=ptempl>
      <OPTION VALUE=\"\"  $_emp_u>$UNKNOWN
      <OPTION VALUE=\"y\" $_emp_y>$Yes
      <OPTION VALUE=\"n\" $_emp_n>$No
    </SELECT>
    <BR>

      <!-- employers -- come from db, not yet -->
      <!-- ptemp1/2                           -->

    <INPUT TYPE=HIDDEN NAME=ptupdt VALUE=\"$cur_date\">

    <BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_changes\">
    </CENTER></FORM>
    <P>
    <CENTER>
    <A HREF=\"manage.php3?$_auth&id=$id\"
     ><$STDFONT_B>Manage Patient<$STDFONT_E></A>
    </CENTER>
    <P>
  ";
  freemed_display_box_bottom ();

} elseif ($action=="mod") {

   #      M O D I F Y - R O U T I N E

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $Patient", $page_name);

  $result = fdb_query("DELETE FROM patient
    WHERE (id = \"$id\")");

  echo "
    <P>
    <I>$Patient <B>$id</B> $Deleted</I>.
  ";
  if ($debug==1) {
    echo "
      <BR><B>$Result :</B><BR>
      $result<BR><BR>
    ";
  } // debug code
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth\"
     >$Select_another $Patient</A></CENTER>
  ";
  freemed_display_box_bottom ();

  echo "
    <BR><BR>
    <CENTER>
    <A HREF=\"$page_name?$_auth\">".
     _("back")."</A>
    <BR><BR>
    <A HREF=\"main.php3?$_auth\"
     >_("Return to the Main Menu")."</A></CENTER>
  ";

} elseif ($action=="find") {

  switch ($criteria) {
    case "letter":
      $query = "SELECT * FROM patient ".
       "WHERE (ptlname LIKE '$f1%') ".
       "ORDER BY ptlname, ptfname, ptdob";
      $_crit = "$Last_Names ($f1)";
      break;
    case "contains":
      $query = "SELECT * FROM patient ".
       "WHERE ($f1 LIKE '%$f2%') ".
       "ORDER BY ptlname, ptfname, ptdob";
      $_crit = "$Searching_for \"$f2\"";
      break;
    case "soundex":
      $query = "SELECT * FROM patient ". 
       "WHERE (soundex($f1) = soundex('$f2')) ".
       "ORDER BY ptlname, ptfname, ptdob";
      $_crit = "Sounds Like \"$f2\"";
      break;
    case "all":
      $query = "SELECT * FROM patient ".
       "ORDER BY ptlname, ptfname, ptdob";
      $_crit = "\"$All_Patients\"";
      break;
    case "dependants":
      $query = "SELECT * FROM patient ".
       "WHERE (ptdep = '$f1') ".
       "ORDER BY ptlname, ptfname, ptdob";
      $_crit = "$Dependants";
      break;
    case "guarantor":
      $query = "SELECT * FROM patient ".
       "WHERE (id = '$f1') ".
       "ORDER BY ptlname, ptfname, ptdob";
      $_crit = "Guarantor";
      break;
    default:
      $_crit = "";
      break;
  }

  $result = fdb_query($query); 

  if ($result) {
    freemed_display_html_top ();
    freemed_display_banner ();
    freemed_display_box_top ("$Patients_meeting_criteria $_crit", $page_name);

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    freemed_display_actionbar($page_name, $_ref);

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$Last_name</B></TD>
       <TD><B>$First_name</B></TD>
       <TD><B>Date of Birth</B></TD>
       <TD><B>Practice ID</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = fdb_fetch_array($result)) {

      $ptfname = $r["ptfname"];
      $ptlname = $r["ptlname"];
      $ptdob   = $r["ptdob"  ];
      $ptid    = $r["ptid"   ];
      //$ptdep   = $r["ptdep"  ]; // guarantor, or 0 if guarantor
      $id      = $r["id"     ];

      if (freemed_check_access_for_patient ($LoginCookie, $id)) {

          // alternate the bar color
        $_alternate = freemed_bar_alternate_color ($_alternate);
 
        if ($debug) {
          $id_mod = " [$id]"; // if debug, insert ID #
        } else {
          $id_mod = ""; // else, let's avoid it...
        } // end debug clause (like sanity clause)
  
        echo "
          <TR BGCOLOR=$_alternate>
          <TD><$STDFONT_B>$ptlname<$STDFONT_E></TD>
          <TD><$STDFONT_B>$ptfname<$STDFONT_E></TD>
          <TD><$STDFONT_B>$ptdob<$STDFONT_E></TD>
          <TD><$STDFONT_B>".
            ( !empty($ptid) ? $ptid : "&nbsp;" ) .
           "<$STDFONT_E></TD>
          <TD>
        ";
        //if (freemed_get_userlevel ($LoginCookie)>$delete_level) {
        //  echo "
        //    &nbsp;
        //    <A HREF=\"$page_name?$_auth&id=$id&action=del\"
        //    ><FONT SIZE=-1>$DEL$id_mod</FONT></A>
        //  "; // show delete
        //}
          // patient dependency check
        //if ($ptdep=="0") {
        // echo "
        //    &nbsp;
        //    <A HREF=
        //    \"$page_name?$_auth&id=$id&action=addform\"
        //    ><FONT SIZE=-1>$NEWDEP$id_mod</FONT></A>
        //    &nbsp;
        //    <A HREF=
        //    \"$page_name?$_auth&action=find&criteria=dependants&f1=$id\"
        //    ><FONT SIZE=-1>$DEPS$id_mod</FONT></A>
        // ";
        //} else {
        // echo "
        //    &nbsp;
        //    <A HREF=
        //     \"$page_name?$_auth&action=find&criteria=guarantor&f1=$ptdep\"
        //    ><FONT SIZE=-1>$GUA$id_mod</FONT></A>
        // ";
        //} // check to see if
 
        //
        // MANAGEMENT LINK -- RESTRICT ACCESS??
        //
        echo "
          <A HREF=
           \"manage.php3?$_auth&id=$id\"
          ><FONT SIZE=-2>$MANAGE</FONT></A>
        ";
   
           // end dependency check
        echo "
          </TD></TR>
        ";
      } // end checking if the patient is accessable by this user

    } // while there are no more

    echo "
      </TABLE>
    "; // end table (fixed 19990617)

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    freemed_display_actionbar ($page_name, $_ref);
    echo "
     <P>
     <CENTER>
      <A HREF=\"$page_name?$_auth\"
      ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
     </CENTER>
     <P>
    ";
    freemed_display_box_bottom (); // display bottom of the box
    // }    
  } else { // result loop
    freemed_display_box_top ("$Patients_meeting_criteria $_crit", $page_name);
    echo "
      <B>$No_patients_found_with_that .</B>
      <BR>
    ";
    if ($debug) echo " ( query = \"$query\" ) ";
    freemed_display_box_bottom ();
  } // result loop
} else {

  freemed_display_box_top ("$PATIENTS", $_ref, $page_name);

  if (freemed_get_userlevel($LoginCookie)>$database_level) {
    echo "
      <TABLE WIDTH=100% BGCOLOR=#000000 BORDER=0 CELLSPACING=0
       CELLPADDING=0 VALIGN=TOP ALIGN=CENTER><TR><TD>
      <FONT FACE=\"Arial, Helvetica, Verdana\" COLOR=#ffffff>
    ";
    $result = fdb_query ("SELECT COUNT(*) FROM patient");
    if ($result) {
      $_res   = fdb_fetch_array ($result);
      $_total = $_res[0];               // total number in db

        // patched 19990622 for 1 and 0 values...
      if ($_total>1)
        echo "
          <CENTER>
           <B><I>$_total $Ppl $In_system</I></B>
          </CENTER>
        ";
      elseif ($_total==0)
        echo "
          <CENTER>
           <B><I>$No_patients $In_system</I></B>
          </CENTER>
        ";
      elseif ($_total==1)
        echo "
          <CENTER>
          <B><I>$One $Patient $In_system</I></B>
          </CENTER>
        ";
    } else {
      echo "
        <CENTER>
         <B><I>$No_patients $In_system</I></B>
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
         <A HREF=\"manage.php3?$_auth&id=$current_patient\"
         >$Current_Patient : ".$patient->fullName(true)."</A>
         </CENTER></TD></TR></TABLE>
      ";
    } // end check for current patient cookie

  echo "
    <BR>
    <CENTER>
     <B>$PATIENTS $By_name</B>
    <BR>
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
    <BR>
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

    <P>

    <FORM ACTION=\"$page_name\" METHOD=POST>
     <B>$Patients_field_search</B>
    <BR>
    <INPUT TYPE=HIDDEN NAME=\"action\"   VALUE=\"find\">
    <INPUT TYPE=HIDDEN NAME=\"criteria\" VALUE=\"contains\">
    <SELECT NAME=\"f1\">
     <OPTION VALUE=\"ptlname\" SELECTED>$Last_name
     <OPTION VALUE=\"ptfname\" >$First_name
     <OPTION VALUE=\"ptdob\"   >$Date_of_birth
     <OPTION VALUE=\"ptid\"    >$Internal_practice_id
     <OPTION VALUE=\"ptcity\"  >$City
     <OPTION VALUE=\"ptstate\" >$State
     <OPTION VALUE=\"ptzip\"   >$Zip_code
     <OPTION VALUE=\"pthphone\">$Home_phone
     <OPTION VALUE=\"ptwphone\">$Work_phone
     <OPTION VALUE=\"ptemail\" >$Email_address
     <OPTION VALUE=\"ptssn\"   >$Social_security_number
     <OPTION VALUE=\"ptdmv\"   >$Drivers_license
     <OPTION VALUE=\"ptacct\"  >$Patient_account_number
    </SELECT>
      <I>$CONTAINS</I>
    <INPUT TYPE=TEXT NAME=\"f2\" SIZE=15 MAXLENGTH=30>
    <INPUT TYPE=SUBMIT VALUE=\"find\">
    </FORM>
    <P>

    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"find\">
    <INPUT TYPE=HIDDEN NAME=\"criteria\" VALUE=\"soundex\">
    <B>Soundalike Search</B><BR>
    <SELECT NAME=\"f1\">
     <OPTION VALUE=\"ptlname\" >$Last_name
     <OPTION VALUE=\"ptfname\" >$First_name
    </SELECT>
      <I>sounds like</I>
    <INPUT TYPE=TEXT NAME=\"f2\" SIZE=15 MAXLENGTH=30>
    <INPUT TYPE=SUBMIT VALUE=\"find\">
    </FORM>
    <P>

    <A HREF=\"$page_name?$_auth&action=find&criteria=all&f1=\"
     ><$STDFONT_B>$Show_all $PATIENTS<$STDFONT_E></A> |
    <A HREF=\"$page_name?$_auth&action=addform\"
     ><$STDFONT_B>$Add $Patient<$STDFONT_E></A> |
    <A HREF=\"call-in.php3?$_auth\"
     ><$STDFONT_B>$Call_In_Menu<$STDFONT_E></A>
    <P> 
    </CENTER>
  ";

  freemed_display_box_bottom (); 

  echo "
    <P>
    <CENTER>
    <A HREF=\"main.php3?$_auth\"
     >"._("Return to the Main Menu")."</A>
    </CENTER>
  "; // close out with return to main menu tags
}

*/

freemed_close_db(); 
freemed_display_html_bottom ();
?>


