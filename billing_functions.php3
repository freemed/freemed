<?php
 // file: billing_functions.php3
 // note: all billing functions accessable from this menu, which is called
 //       by the main menu
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

  $page_name = "billing_functions.php3";
  include ("global.var.inc");
  include ("freemed-functions.inc");

  SetCookie ("_ref", $page_name, time()+$_cookie_expire);

  freemed_open_db ($LoginCookie);
  $this_user = new User ($LoginCookie);

  freemed_display_html_top ();
  freemed_display_box_top (_("Billing Functions"));

  $patient_information = "<$STDFONT_B><B>"._("NO PATIENT SPECIFIED")."</B><$STDFONT_E>";
  if ($patient>0) {
    $this_patient = new Patient ($patient);
    $patient_information =
      freemed_patient_box ($this_patient);
  } // if there is a patient

   // here is the actual guts of the menu
  if ($this_user->getLevel() > $database_level) 
   echo "
    <$STDFONT_B>

    <P>

    <CENTER>
    $patient_information
    </CENTER>

    <P>

    <TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
     ALIGN=CENTER>

    ".($this_patient ?  
    "<TR>
    <TD ALIGN=RIGHT>
      <$STDFONT_B><B>"._("Patient Payment")." : </B><$STDFONT_E></TD>
    <TD ALIGN=LEFT>
     <A HREF=\"payment_record.php3?$_auth&action=addform&patient=$patient\"
     ><$STDFONT_B>"._("Entry")."<$STDFONT_E></A>
    </TD>
    <TD ALIGN=LEFT>
     <A HREF=\"payment_record.php3?$_auth&action=view&patient=$patient\"
     ><$STDFONT_B>"._("View/Manage")."<$STDFONT_E></A>
    </TD>
    </TR>" :
    "<TR>
     <TD COLSPAN=2 ALIGN=CENTER>
      <CENTER>
      <A HREF=\"patient.php3?$_auth\"
      >"._("Select a Patient")."</A>
      </CENTER>
     </TD>
    </TR>" )."

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B><B>"._("Generate Insurance Billing")." : </B><$STDFONT_E>
     </TD><TD ALIGN=LEFT COLSPAN=2>
      <A HREF=\"generate_fixed_forms.php3?$_auth\"
      ><$STDFONT_B>"._("Menu")."<$STDFONT_E></A>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B><B>Unpaid Procedures  : </B><$STDFONT_E>
     </TD><TD ALIGN=LEFT COLSPAN=2>
      <A HREF=\"manage_bills.php3?$_auth&action=list\"
      ><$STDFONT_B>View<$STDFONT_E></A>
     </TD>
    </TR>


    </TABLE> 
    <P>

    <$STDFONT_E>
  ";
  else echo "
      <P>
      <$HEADERFONT_B>
        "._("You don't have access for this menu.")."
      <$HEADERFONT_E>
      <P>
    ";

  freemed_display_box_bottom ();
  freemed_display_html_bottom ();
  freemed_close_db (); // close db
?>
