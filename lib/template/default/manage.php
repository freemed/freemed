<?php
 // $Id$
 // $Author$
 // note: template for patient management functions
 // lic : GPL, v2

// load the Patient object
$this_patient = new Patient ($id);

if ($id<1) {
  // if someone needs to 1st go to the patient menu
      $display_buffer .= "
        <BR><BR>
        <CENTER>
          <B>"._("You must select a patient.")."</B>
        </CENTER>
        <BR><BR>
        <CENTER>
        <A HREF=
         \"patient.php\"
        >"._("Select a Patient")."</A>
        </CENTER>
        <BR><BR>
      ";

     } else {
      // **************************************************** STATIC MODULES

      $display_buffer .= "

     ".freemed_patient_box($this_patient)."

        <TABLE WIDTH=100% BORDER=0 CELLPADDING=3>
        <TR><TD ALIGN=RIGHT>
        <B>"._("Appointments")."</B> : 
        </TD><TD>
        <A HREF=\"book_appointment.php?patient=$id&type=pat\"
         >"._("Add")."</A> 
        </TD><TD>
        <A HREF=\"manage_appointments.php?patient=$id\"
         >"._("View/Manage")."</A><BR>
        </TD><TD>
        </TD></TR>
        <TR><TD>&nbsp;</TD>
        <TD><A HREF=\"show_appointments.php?patient=$id&type=pat\"
         >"._("Show Today")."</A></TD>
        <TD>&nbsp;</TD>
        </TR>
      ";
      $f_results = $sql->query("SELECT * FROM patrectemplate
                              ORDER BY prtname");
      if (($f_results>0) and ($sql->num_rows($f_results))) {
       $display_buffer .= "
         <TR><TD ALIGN=RIGHT>
          <B>"._("Custom Records")."</B> : 
         </TD><TD COLSPAN=2>
          <FORM ACTION=\"custom_records.php\" METHOD=POST>
           <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$id\">
           <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
           <SELECT NAME=\"form\">
       ";
       while ($f_r = $sql->fetch_array ($f_results)) 
         $display_buffer .= "<OPTION VALUE=\"".$f_r["id"]."\">".$f_r["prtname"]."\n";
       $display_buffer .= "
           </SELECT>
           <INPUT TYPE=SUBMIT VALUE=\""._("Add")."\">
          </FORM>
         </TR>
        <TR>
         <TD>&nbsp;</TD>
         <TD><A HREF=\"custom_records.php?patient=$id\" 
          >"._("View/Manage")."</A></TD>
        </TR>
       ";
      } // end of if, checking for custom records....
//      $display_buffer .= "
//        <TR><TD ALIGN=RIGHT>
//         <B>Dependent Information</B> : 
//        </TD><TD ALIGN=LEFT>
//     ";
//      removed as part of coverage overhaul
//     if (!$this_patient->isDependent()) {
//      $dep_query = "SELECT COUNT(*) FROM patient WHERE ptdep='".
//                   $this_patient->id."'";
//      $dep_result = $sql->query($dep_query);
//      $dep_r = $sql->fetch_array($dep_result);
//      $num_deps = $dep_r[0];
//      if ($num_deps<1)
//        $display_buffer .= "No Dependents";
//      else
//        $display_buffer .= "
//	 <A HREF=\"patient.php?action=find&criteria=".
//	 "dependants&f1=$id\">"._("Dependents")."</A> [$num_deps]
//        ";
//      } else {
//      $guarantor = new Patient ($this_patient->ptdep);
//      $display_buffer .= "
//         <A HREF=\"manage.php?action=view&id=".$this_patient->ptdep."\"
//         >"._("Guarantor")."</A>
//	</TD><TD>[".$guarantor->fullName()."]</TD></TR>
//     ";
//    }

     $display_buffer .= "
        <TR><TD ALIGN=RIGHT>
        <B>"._("Patient Information")."</B> : 
        </TD><TD> 
        <A HREF=\"patient.php?action=modform&id=$id\"
         >"._("Modify")."</A>
        </TD><TD>&nbsp;
        </TD><TD>
        </TD></TR>

	";

    // **************************************************** DYNAMIC MODULES
	// loadable modules start here
	$category = "Electronic Medical Record";
	$template = "
        <TR><TD ALIGN=RIGHT>
        <B>#name#</B> : 
        </TD><TD>
        <A HREF=\"module_loader.php?module=#class#&action=addform&patient=$id\"
         >"._("Add")."</A>
        </TD><TD> 
        <A HREF=\"module_loader.php?module=#class#&patient=$id\"
         >"._("View/Manage")."</A>
        </TD><TD>
        </TD></TR>

	";
	// Form template for menubar
	$template_menubar = "<LI><A HREF=\"module_loader.php?module=#class#&patient=$id\"
         >#name#</A>";

	$module_list = new module_list (PACKAGENAME, ".emr.module.php");
	$display_buffer .= $module_list->generate_list ($category, 0, $template);
	$menu_bar .= "<UL>\n";
	$menu_bar .= $module_list->generate_list ($category, 0, $template_menubar);
	$menu_bar .= "</UL>\n";

    $display_buffer .= "
		<TR><TD ALIGN=RIGHT>
		<BR>
    	<B>"._("Certifications")."</B>
		<BR>
    	</TD>
	";
	$category = "EMR Certifications";
	$template = "
        <TR><TD ALIGN=RIGHT>
        <B>#name#</B> : 
        </TD><TD> 
        <A HREF=\"module_loader.php?module=#class#&action=addform&patient=$id\"
         >"._("Add")."</A>
        </TD><TD> 
        <A HREF=\"module_loader.php?module=#class#&patient=$id\"
         >"._("View/Manage")."</A>
        </TD><TD>
        </TD></TR>

	";

	$module_list = new module_list (PACKAGENAME, ".emr_report.module.php");
	$display_buffer .= $module_list->generate_list ($category, 0, $template);
	// end of loadable modules code
    $display_buffer .= "
		<TR><TD ALIGN=RIGHT>
		<BR>
    	<B>"._("Patient Reports")."</B>
		<BR>
    	</TD>
	";
	$category = "Electronic Medical Record Report";
	$template = "
        <TR><TD ALIGN=RIGHT>
        <B>#name#</B> : 
        </TD>
		<TD> 
        <A HREF=\"module_loader.php?module=#class#&patient=$id\"
         >"._("View")."</A>
        </TD><TD>
        </TD></TR>

	";

	$module_list = new module_list (PACKAGENAME, ".emr_report.module.php");
	$display_buffer .= $module_list->generate_list ($category, 0, $template);
	// end of loadable modules code

	$display_buffer .= "
        <!--

	  // this is commented out until we can make it work properly

        <TR><TD ALIGN=RIGHT>
        <B>"._("Reports and Certificates")."</B> : 
        </TD><TD>
        <A HREF=\"simplerep.php?action=choose&patient=$id\"
        >"._("Choose")."</A>
        </TD><TD>
        </TD><TD>
        </TD></TR>
        -->

		<!--	
        <TR><TD ALIGN=RIGHT>
        <B>"._("Patient Reports")."</B> : 
        </TD><TD>
        <A HREF=\"emrreports.php?action=choose&patient=$id\"
        >"._("Choose")."</A>
        </TD><TD>
        </TD><TD>
        </TD></TR>
        -->
        </TABLE>

        <CENTER>
		<P>
        <A HREF=\"patient.php\"
         >"._("Select Another Patient")."</A>
        </CENTER>
        <P>
      </CENTER>
     ";
} // if there is an ID specified
    
?>
