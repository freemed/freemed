<?php
 // $Id$
 // note: all billing functions accessable from this menu, which is called
 //       by the main menu
 // lic : GPL, v2

$page_name = "billing_functions.php";
include ("lib/freemed.php");
include ("lib/API.php");
include ("lib/module.php");
include ("lib/module_billing.php");

//----- Login/authenticate
freemed_open_db ();

//----- Create user object
$this_user = new User ();

//----- Set page title
$page_title = _("Billing Functions");

//----- Add page to stack
page_push();

//----- Check for "current_patient" in SESSION
if ($SESSION["current_patient"] != 0) $patient = $SESSION["current_patient"];

$patient_information = "<B>"._("NO PATIENT SPECIFIED")."</B>";
  if ($patient>0) {
    $this_patient = new Patient ($patient);
    $patient_information =
      freemed_patient_box ($this_patient);
  } // if there is a patient

//
// payment links removed till billing module is
// complete. use manage to make payments
//
   // here is the actual guts of the menu
  if ($this_user->getLevel() > $database_level) {
   $display_buffer .= "
    <P>

    <CENTER>
    $patient_information
    </CENTER>

    <P>

    <TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
     ALIGN=CENTER>
    ".($this_patient ? "" :
    "<TR>
     <TD COLSPAN=2 ALIGN=CENTER>
      <CENTER>
      <A HREF=\"patient.php\"
      >"._("Select a Patient")."</A>
      </CENTER>
     </TD>
    </TR>" )."


    </TABLE> 
    <P>
    ";
	$catagory = "Billing";
	$module_template = "
		<TR><TD ALIGN=RIGHT>
        <B>#name#</B> : 
        </TD>
        <TD>
        <A HREF=\"module_loader.php?module=#class#&patient=$patient\"
         >"._("Menu")."</A>
        </TD>
		</TR>";
    // modules list
    $module_list = new module_list (PACKAGENAME, ".billing.module.php");
    $display_buffer .= "<CENTER><TABLE>\n";
    $display_buffer .= $module_list->generate_list($catagory, 0, $module_template);
    $display_buffer .= "</TABLE></CENTER>\n";
	$catagory = "X12";
	$module_template = "
		<TR><TD ALIGN=RIGHT>
        <B>#name#</B> : 
        </TD>
        <TD>
        <A HREF=\"module_loader.php?module=#class#&patient=$patient\"
         >"._("Menu")."</A>
        </TD>
		</TR>";
    // modules list
    //$module_list2 = new module_list (PACKAGENAME);
    $display_buffer .= "<CENTER><TABLE>\n";
    $display_buffer .= $module_list->generate_list($catagory, 0, $module_template);
    $display_buffer .= "</TABLE></CENTER>\n";

  } else { 
    $display_buffer .= "
      <P>
        "._("You don't have access for this menu.")."
      <P>
    ";
  }

template_display ();
?>
