<?php
  # file: patient.php3
  # note: patient database functions
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  #       some small stuff by: max k <amk@span.ch>
  # lic : GPL, v2

  $page_name="patient.php3"; // for help info, later
  $record_name="Patient";    // compatibility with API functions
  include ("global.var.inc");
  include ("freemed-functions.inc");

  SetCookie ("_ref", $page_name, time()+$_cookie_expire);

  if ( ($id>0) AND 
       ($action != "addform") AND ($action != "add") AND
       ($action != "delform") AND ($action != "del")) {
    SetCookie ("current_patient", $id, time()+$_cookie_expire);
    $current_patient = $id;   // patch for first time....
  } // end checking for current_patient value

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top ();
  freemed_display_banner ();

if ($action=="addform") {

  $_dep = ""; // by default, no guarantor
  if (empty($ptstatus)) {
    $ptstatus="0";         // inactive status
  }

    // dependant (19990520) guarantor generation
  if (strlen($id)>0) { // if called with guarantor
    $dependant = new Patient ($id);
    $_dep = "\n  <OPTION VALUE=\"$id\" SELECTED>".$dependant->fullName()."\n";
  } // end this clause dependant of guarantor

    // change title appropriately if dependant
  if (strlen($id)>0) 
    freemed_display_box_top ("$Add $Dependant", $page_name, $_ref); 
  else 
    freemed_display_box_top ("$Add $Patient", $page_name, $_ref);

  if ($debug) {
    echo "
      date = ($cur_date)<BR>
    ";
  }
  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 

    <$STDFONT_B>$Last_name : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptlname SIZE=25 MAXLENGTH=50
     VALUE=\"$ptlname\">
    <BR>
    <$STDFONT_B>$First_name : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptfname SIZE=25 MAXLENGTH=50
     VALUE=\"$ptfname\">
    <BR>

    <$STDFONT_B>$Middle_name : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptmname SIZE=25 MAXLENGTH=50
     VALUE=\"$ptmname\">
    <BR>

    <$STDFONT_B>$Date_of_birth : <$STDFONT_E>
  ";
  fm_date_entry("ptdob", true);
  echo "
    <BR>  

    <$STDFONT_B>$Gender : <$STDFONT_E>
    <SELECT NAME=\"ptsex\">
      <OPTION VALUE=\"\">$NONE_SELECTED
      <OPTION VALUE=\"f\">$Female
      <OPTION VALUE=\"m\">$Male
      <OPTION VALUE=\"t\">$Transgender
    </SELECT>
    <BR>

    <$STDFONT_B>$Marital_status : <$STDFONT_E>
    <SELECT NAME=\"ptmarital\">
      <OPTION VALUE=\"\"         >--$Unknown--
      <OPTION VALUE=\"single\"   >$Single
      <OPTION VALUE=\"married\"  >$Married
      <OPTION VALUE=\"divorced\" >$Divorced
      <OPTION VALUE=\"separated\">$Separated
      <OPTION VALUE=\"widowed\"  >Widowed
    </SELECT>
    <BR>

    <$STDFONT_B>Social Security Number : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"ptssn\" SIZE=9 MAXLENGTH=10
     VALUE=\"$ptssn\">
    <BR>

    <$STDFONT_B>$Internal_practice_id # : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"ptid\" SIZE=10 MAXLENGTH=10
     VALUE=\"$ptid\">
    <BR>
 
    <$STDFONT_B>$Address $Line 1 : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptaddr1 SIZE=25 MAXLENGTH=45
     VALUE=\"$ptaddr1\">
    <BR>

    <$STDFONT_B>$Address $Line 2 : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptaddr2 SIZE=25 MAXLENGTH=45
     VALUE=\"$ptaddr2\">
    <BR>

    <$STDFONT_B>$City : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptcity SIZE=10 MAXLENGTH=45
     VALUE=\"$ptcity\">    

    <$STDFONT_B>$State : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptstate SIZE=3 MAXLENGTH=2
     VALUE=\"$ptstate\">    

    <$STDFONT_B>$Zip_code : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptzip SIZE=10 MAXLENGTH=10
     VALUE=\"$ptzip\">
    <BR>
    <$STDFONT_B>$Country : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptcountry SIZE=20 MAXLENGTH=50
     VALUE=\"$ptcountry\">
    <P>

    <$STDFONT_B>$Home_phone : <$STDFONT_E>
  ";
  fm_phone_entry ("pthphone");
  echo "
    <BR>

    <$STDFONT_B>$Work_phone : <$STDFONT_E>
  ";
  fm_phone_entry ("ptwphone");
  echo "
    <BR>
    <$STDFONT_B>$Fax_number : <$STDFONT_E>
  ";
  fm_phone_entry ("ptfax");
  echo "
    <BR>

    <$STDFONT_B>$Email_address : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptemail1 SIZE=20 MAXLENGTH=40
     VALUE=\"$ptemail1\"> <B>@</B>
    <INPUT TYPE=TEXT NAME=ptemail2 SIZE=20 MAXLENGTH=40
     VALUE=\"$ptemail2\">
    <BR>

    <$STDFONT_B>$Drivers_license ($No_state) : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptdmv SIZE=10 MAXLENGTH=9
     VALUE=\"$ptdmv\">
    <BR>
       ";
  if ($ptdoc < 1) $ptdoc = freemed_get_link_field ($default_facility,
    "facility", "psrdefphy");
  echo "
    <$STDFONT_B>$In_house_doctor : <$STDFONT_E>
    <SELECT NAME=\"ptdoc\">
  ";

  freemed_display_physicians ($ptdoc);

  echo "
    </SELECT>
    <BR>
       ";
    /// 
  echo "
    <$STDFONT_B>$Referring_doctor : <$STDFONT_E>
    <SELECT NAME=\"ptrefdoc\">
  "; // break for doctor list

  freemed_display_physicians ($ptrefdoc);

  echo "
    </SELECT><BR>

    <$STDFONT_B>$Primary_care_physician : <$STDFONT_E>
    <SELECT NAME=\"ptpcp\">
  ";

  freemed_display_physicians ($ptpcp);

  echo "
    </SELECT><BR>

    <$STDFONT_B>$Other $Physician 1 : <$STDFONT_E>
    <SELECT NAME=\"ptphy1\">
  ";

  freemed_display_physicians ($ptphy1);

  echo "
    </SELECT><BR>

    <$STDFONT_B>$Other $Physician 2 : <$STDFONT_E>
    <SELECT NAME=\"ptphy2\">
  ";

  freemed_display_physicians ($ptphy2);

  echo "
    </SELECT><BR>

    <$STDFONT_B>$Other $Physician 3 : <$STDFONT_E>
    <SELECT NAME=\"ptphy3\">
  ";

  freemed_display_physicians ($ptphy3);
  
  echo "
    </SELECT><BR>

    <$STDFONT_B>$Other $Physician 4 : <$STDFONT_E>
    <SELECT NAME=\"ptphy4\">
  ";

  freemed_display_physicians ($ptphy4);

  echo "
    </SELECT><BR>

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
  //////////

  /////////
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

    <$STDFONT_B>Relation to Guarantor : <$STDFONT_E>
    <SELECT NAME=\"ptreldep\">
     <OPTION VALUE=\"S\" ".
      ( ($ptreldep=="S") ? "SELECTED" : "" ).">Self
     <OPTION VALUE=\"C\" ".
      ( ($ptreldep=="C") ? "SELECTED" : "" ).">Child
     <OPTION VALUE=\"H\" ".
      ( ($ptreldep=="H") ? "SELECTED" : "" ).">Husband
     <OPTION VALUE=\"W\" ".
      ( ($ptreldep=="W") ? "SELECTED" : "" ).">Wife
     <OPTION VALUE=\"O\" ".
      ( ($ptreldep=="O") ? "SELECTED" : "" ).">Other
    </SELECT>
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

  $ptdtadd = $cur_date; // current date of add...
  $ptdtmod = $cur_date; // current date for mod as well

    // next of kin prepare blob field
  $ptnextofkin = addslashes ($ptnextofkin);

    // assemble phone numbers
  $pthphone   = fm_phone_assemble ("pthphone");
  $ptwphone   = fm_phone_assemble ("ptwphone");
  $ptfax      = fm_phone_assemble ("ptfax");

    // assemble date of birth
  $ptdob      = fm_date_assemble("ptdob");

    // knock state to upper case
  $ptstate  = strtoupper ($ptstate); 

   // assemble email
  if ((strlen($ptemail1)>0) AND (strlen($ptemail2)>3))
    $ptemail = $ptemail1 . "@" . $ptemail2;

  $query = "INSERT INTO patient VALUES (
           '$ptdtadd',
           '$ptdtmod',
           '$ptbal',
           '$ptbalfwd',
           '$ptunapp',
           '$ptrefdoc',
           '$ptpcp',
           '$ptphy1',
           '$ptphy2',
           '$ptphy3',
           '$ptphy4',
           '$ptbilltype',
           '$ptbudg',
           '$ptdoc',
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
           '$ptdep',
           '$ptreldep',
           '$ptins1',
           '$ptins2',
           '$ptins3',
           '$ptinsno1',
           '$ptinsno2',
           '$ptinsno3',
           '$ptinsgrp1',
           '$ptinsgrp2',
           '$ptinsgrp3',
           '$ptnextofkin',
           '$__ISO_SET__',
           NULL ) ";

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
       >$Return_to_the $Main_menu</A>
      </CENTER>
    ";
    DIE("");
  }

  $result = fdb_query("SELECT * FROM patient ".
    "WHERE ( id = '$id' )");

  if ($debug==1) {
    echo " <B>$Result</B> = [$result]<BR><BR> ";
  }

  $r = fdb_fetch_array($result); // dump into array r[]

    # now comes the monotony
    # of horrendous repetition...
  $ptdtadd      = $r["ptdtadd"     ]; // 19990810 add by max
  $ptdtmod      = $r["ptdtmod"     ]; // idem
  $ptbal        = $r["ptbal"       ];
  $ptbalfwd     = $r["ptbalfwd"    ];
  $ptunapp      = $r["ptunapp"     ];
  $ptrefdoc     = $r["ptrefdoc"    ];
  $ptpcp        = $r["ptpcp"       ];
  $ptphy1       = $r["ptphy1"      ];
  $ptphy2       = $r["ptphy2"      ];
  $ptphy3       = $r["ptphy3"      ];
  $ptphy4       = $r["ptphy4"      ];
  $ptbilltype   = $r["ptbilltype"  ];
  $ptbudg       = $r["ptbudg"      ];
  $ptdoc        = $r["ptdoc"       ];
  $ptlname      = $r["ptlname"     ];
  $ptfname      = $r["ptfname"     ];
  $ptmname      = $r["ptmname"     ];
  $ptaddr1      = $r["ptaddr1"     ];
  $ptaddr2      = $r["ptaddr2"     ];
  $ptcity       = $r["ptcity"      ];
  $ptstate      = strtoupper ($r["ptstate"     ]);
  $ptzip        = $r["ptzip"       ];
  $ptcountry    = $r["ptcountry"   ]; // 19990728 country field add
  $pthphone     = $r["pthphone"    ];
  $ptwphone     = $r["ptwphone"    ];
  $ptfax        = $r["ptfax"       ];
  $ptemail      = $r["ptemail"     ];
  $ptsex        = $r["ptsex"       ];
  $ptssn        = $r["ptssn"       ];
  $ptdmv        = $r["ptdmv"       ];
  $ptdtlpay     = $r["ptdtlpay"    ];
  $ptamtlpay    = $r["ptamtlpay"   ];
  $ptpaytype    = $r["ptpaytype"   ];
  $ptdtbill     = $r["ptdtbill"    ];
  $ptamtbill    = $r["ptamtbill"   ];
  $ptstatus     = $r["ptstatus"    ];
  $ptytdchg     = $r["ptytdchg"    ];
  $ptar         = $r["ptar"        ];
  $ptextinf     = $r["ptextinf"    ];
  $ptdisc       = $r["ptdisc"      ];
  $ptdol        = $r["ptdol"       ];
  $ptdiag1      = $r["ptdiag1"     ];
  $ptdiag2      = $r["ptdiag2"     ];
  $ptdiag3      = $r["ptdiag3"     ];
  $ptdiag4      = $r["ptdiag4"     ];
  $ptid         = $r["ptid"        ]; // mk changed id to ptid 19990923
  $pthistbal    = $r["pthistbal"   ];
  $ptmarital    = $r["ptmarital"   ];
  $ptempl       = $r["ptempl"      ];
  $ptemp1       = $r["ptemp1"      ];
  $ptemp2       = $r["ptemp2"      ];
  $ptdep        = $r["ptdep"       ]; // guarantor
  $ptdob        = $r["ptdob"       ]; // date of birth
  $ptins1       = $r["ptins1"      ]; // added insurance fields
  $ptins2       = $r["ptins2"      ];
  $ptins3       = $r["ptins3"      ];
  $ptinsno1     = $r["ptinsno1"    ]; // insurance identifiers
  $ptinsno2     = $r["ptinsno2"    ]; // insurance identifiers
  $ptinsno3     = $r["ptinsno3"    ]; // insurance identifiers
  $ptinsgrp1    = $r["ptinsgrp1"   ]; // insurance groups
  $ptinsgrp2    = $r["ptinsgrp2"   ]; // insurance groups
  $ptinsgrp3    = $r["ptinsgrp3"   ]; // insurance groups

  // 19990728 -- next of kin pull and remake
  $ptnextofkin  = htmlentities ($r["ptnextofkin"]);

  // 19990823 -- resplit email
  if (strlen($ptemail)>3) {
    $ptemail_array = explode ("@", $ptemail);
    $ptemail1      = $ptemail_array[0];
    $ptemail2      = $ptemail_array[1];
  }

  // this is the code for SELECT/OPTION clauses...
  //switch ($ptsex) {
  //  case "m": $_sex_m="SELECTED"; break;
  //  case "f": $_sex_f="SELECTED"; break;
  //  case "t": $_sex_t="SELECTED"; break;
  //  default:  $_sex_n="SELECTED";
  //} // this switch is to set the $ptsex (not <INPUT>)

  //switch ($ptmarital) {
  //  case "single"   : $_mar_s="SELECTED"; break;
  //  case "married"  : $_mar_m="SELECTED"; break;
  //  case "divorced" : $_mar_d="SELECTED"; break;
  //  case "separated": $_mar_e="SELECTED"; break;
  //  default:  $_mar_n="SELECTED";
  //} // this switch is to set the $ptmarital (not <INPUT>)

  switch ($ptempl) {
    case "y": $_emp_y="SELECTED"; break;
    case "n": $_emp_n="SELECTED"; break;
    default:  $_emp_u="SELECTED";
  } // this switch is to set the $ptempl (not <INPUT>)
  $_dep = ""; // by default, no dependants

    // dependant (19990520) guarantor generation
  if ($ptdep!=0) { // if called with guarantor
    $dependant = new Patient ($ptdep);
    $_dep = "\n  <OPTION VALUE=\"$id\" SELECTED>".$dependant->fullName()."\n";
  } // end this clause dependant of guarantor

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

    <$STDFONT_B>$Last_name : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptlname SIZE=25 MAXLENGTH=50
     VALUE=\"$ptlname\">
    <BR>
    <$STDFONT_B>$First_name : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptfname SIZE=25 MAXLENGTH=50
     VALUE=\"$ptfname\">
    <BR>

    <$STDFONT_B>$Middle_name : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptmname SIZE=25 MAXLENGTH=50
     VALUE=\"$ptmname\">
    <BR>

    <$STDFONT_B>$Date_of_birth <!-- ($YMD) --> : <$STDFONT_E>
  ";
  fm_date_entry("ptdob", true);
  echo "
    <BR>  

    <$STDFONT_B>$Gender : <$STDFONT_E>
    <SELECT NAME=\"ptsex\">
      <OPTION VALUE=\"\"  ".
      ( ($ptsex == "") ? "SELECTED" : "" ).">$NONE_SELECTED
      <OPTION VALUE=\"f\" ".
      ( ($ptsex == "f") ? "SELECTED" : "" ).">$Female
      <OPTION VALUE=\"m\" ".
      ( ($ptsex == "m") ? "SELECTED" : "" ).">$Male
      <OPTION VALUE=\"t\" ".
      ( ($ptsex == "t") ? "SELECTED" : "" ).">$Transgender
    </SELECT>
    <BR>    


    <STDFONT_B>$Marital_status : <$STDFONT_E>
    <SELECT NAME=ptmarital>
      <OPTION VALUE=\"\"          ".
       ( ($ptmarital==""         ) ? "SELECTED" : "" ).">--$Unknown--
      <OPTION VALUE=\"single\"    ".
       ( ($ptmarital=="single"   ) ? "SELECTED" : "" ).">$Single
      <OPTION VALUE=\"married\"   ".
       ( ($ptmarital=="married"  ) ? "SELECTED" : "" ).">$Married
      <OPTION VALUE=\"divorced\"  ".
       ( ($ptmarital=="divorced" ) ? "SELECTED" : "" ).">$Divorced
      <OPTION VALUE=\"separated\" ".
       ( ($ptmarital=="separated") ? "SELECTED" : "" ).">$Separated
    </SELECT>
    <BR>   

    <$STDFONT_B>Social Security Number : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"ptssn\" SIZE=9 MAXLENGTH=10
     VALUE=\"$ptssn\">
    <BR>  

    <$STDFONT_B>$Internal_practice_id # : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptid SIZE=10 MAXLENGTH=20
     VALUE=\"$ptid\">
    <BR>  

    <$STDFONT_B>$Address $Line 1 : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptaddr1 SIZE=25 MAXLENGTH=45
     VALUE=\"$ptaddr1\">
    <BR>

    <$STDFONT_B>Address Line 2 : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptaddr2 SIZE=25 MAXLENGTH=45
     VALUE=\"$ptaddr2\">
    <BR>

    <$STDFONT_B>$City : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptcity SIZE=10 MAXLENGTH=45
     VALUE=\"$ptcity\">

    <$STDFONT_B>$State : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptstate SIZE=3 MAXLENGTH=2
     VALUE=\"$ptstate\">    

    <$STDFONT_B>$Zip_code : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptzip SIZE=10 MAXLENGTH=10
     VALUE=\"$ptzip\">
    <BR>
    <$STDFONT_B>$Country : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptcountry SIZE=20 MAXLENGTH=50
     VALUE=\"$ptcountry\">
    <P>

    <$STDFONT_B>$Home_phone : <$STDFONT_E>
  ";
  fm_phone_entry ("pthphone");
  echo "
    <BR>

    <$STDFONT_B>$Work_phone : <$STDFONT_E>
  ";
  fm_phone_entry ("ptwphone");
  echo "
    <BR>
    <$STDFONT_B>$Fax_number : <$STDFONT_E>
  ";
  fm_phone_entry ("ptfax");
  echo "
    <BR>

    <$STDFONT_B>$Email_address : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptemail1 SIZE=20 MAXLENGTH=40
     VALUE=\"$ptemail1\"> <B>@</B>
    <INPUT TYPE=TEXT NAME=ptemail2 SIZE=20 MAXLENGTH=40
     VALUE=\"$ptemail2\">
    <BR>

    <$STDFONT_B>$Drivers_license ($No_state) : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ptdmv SIZE=10 MAXLENGTH=9
     VALUE=\"$ptdmv\">
    <BR>
     ";
   //// ptdoc moved here 19990926 (mk)

  echo "     
    <$STDFONT_B>$In_house_doctor : <$STDFONT_E>
    <SELECT NAME=\"ptdoc\">
  ";

  freemed_display_physicians ($ptdoc);

  echo "
    </SELECT>
    <BR>
  ";

  echo "
    <$STDFONT_B>$Referring_doctor : <$STDFONT_E>
    <SELECT NAME=\"ptrefdoc\">
  "; // break for doctor list

  freemed_display_physicians ($ptrefdoc);

  echo "
    </SELECT><BR>

    <$STDFONT_B>$Primary_care_physician : <$STDFONT_E>
    <SELECT NAME=\"ptpcp\">
  ";

  freemed_display_physicians ($ptpcp);

  echo "
    </SELECT><BR>

    <$STDFONT_B>$Other $Physician 1 : <$STDFONT_E>
    <SELECT NAME=\"ptphy1\">
  ";

  freemed_display_physicians ($ptphy1);

  echo "
    </SELECT><BR>

    <$STDFONT_B>$Other $Physician 2 : <$STDFONT_E>
    <SELECT NAME=\"ptphy2\">
  ";

  freemed_display_physicians ($ptphy2);

  echo "
    </SELECT><BR>

    <$STDFONT_B>$Other $Physician 3 : <$STDFONT_E>
    <SELECT NAME=\"ptphy3\">
  ";

  freemed_display_physicians ($ptphy3);

  echo "
    </SELECT><BR>

    <$STDFONT_B>$Other $Physician 4 : <$STDFONT_E>
    <SELECT NAME=\"ptphy4\">
  ";

  freemed_display_physicians ($ptphy4);

  echo "
    </SELECT><BR>

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

  freemed_display_box_top ("$Modifying $Patient", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying ... 
  ";

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
    "ptdep       ='$ptdep',        ".
    "ptins1      ='$ptins1',       ".
    "ptins2      ='$ptins2',       ".
    "ptins3      ='$ptins3',       ".
    "ptinsno1    ='$ptinsno1',     ".
    "ptinsno2    ='$ptinsno2',     ".
    "ptinsno3    ='$ptinsno3',     ".
    "ptinsgrp1   ='$ptinsgrp1',    ".
    "ptinsgrp2   ='$ptinsgrp2',    ".
    "ptinsgrp3   ='$ptinsgrp3',    ".
    "ptnextofkin ='$ptnextofkin',  ". // 19990728 next of kin add
    "iso         ='iso'            ". // 19991228
    "WHERE id='$id'";

  $result = fdb_query($query);
  if ($debug==1) {
    echo "\n<BR><BR><B>$Query_result :</B><BR>\n";
    echo $result;
    echo "\n<BR><BR><B>$Query_string :</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>$Actual_query_result :</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>$Done .</B> : <$STDFONT_E>
    ";
  } else {
    echo ("<B>$Error ($result)</B>\n"); 
  } // end of error reporting clause

  echo "
    <P>
    <CENTER>
    <A HREF=\"manage.php3?$_auth&id=$id\"
     ><$STDFONT_B>$Manage $Patient<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom ();

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $Patient", $page_name);

  $result = fdb_query("DELETE FROM patient
    WHERE (id = \"$id\")");

  echo "
    <P>
    <I>$Patient <B>$id</B> $Deleted<I>.
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
    <A HREF=\"$page_name?$_auth\">$Return_to_the
     $Patient_menu</A>
    <BR><BR>
    <A HREF=\"main.php3?$_auth\">$Return_to_the
    $Main Menu</A></CENTER>
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
      ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
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
    <A HREF=\"main.php3?$_auth\">$Return_to $Main_menu</A>
    </CENTER>
  "; // close out with return to main menu tags
}

freemed_close_db(); 
freemed_display_html_bottom ();
?>
