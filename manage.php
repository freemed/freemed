<?php
 // $Id$
 // note: patient management functions -- links to other modules
 // lic : GPL, v2

$page_name = "manage.php";
include ("lib/freemed.php");
include ("lib/API.php");
include ("lib/module.php");
include ("lib/module_emr.php");
include ("lib/module_emr_report.php");

if ($id != $current_patient)
  SetCookie ("current_patient", $id, time()+$_cookie_expire);
$current_patient = $id; // kludge

freemed_open_db ($LoginCookie);
if (($id<1) AND ($current_patient>0)) { $id = $current_patient; }
 elseif (($id<1) and ($patient>0))    { $id = $patient;         }

// load the Patient object
$this_patient = new Patient ($id);

freemed_display_html_top ();
freemed_display_banner ();

freemed_display_box_top (_("Manage Patient"), $_ref);
if ($id<1) {
  // if someone needs to 1st go to the patient menu
      echo "
        <BR><BR>
        <CENTER>
          <B>"._("You must select a patient.")."</B>
        </CENTER>
        <BR><BR>
        <CENTER>
        <A HREF=
         \"patient.php?$_auth\"
        >"._("Select a Patient")."</A>
        </CENTER>
        <BR><BR>
      ";

     } else {
      $_auth   = "_ref=$page_name";

      // **************************************************** STATIC MODULES

      echo "

     ".freemed_patient_box($this_patient)."

        <TABLE WIDTH=100% BORDER=0 CELLPADDING=3>
        <TR><TD ALIGN=RIGHT>
        <B>"._("Appointments")."</B> : 
        </TD><TD>
        <A HREF=\"book_appointment.php?$_auth&patient=$id&type=pat\"
         >"._("Add")."</A> 
        </TD><TD>
        <A HREF=\"manage_appointments.php?$_auth&patient=$id\"
         >"._("View/Manage")."</A><BR>
        </TD><TD>
        </TD></TR>
        <TR><TD>&nbsp;</TD>
        <TD><A HREF=\"show_appointments.php?$_auth&patient=$id&type=pat\"
         >"._("Show Today")."</A></TD>
        <TD>&nbsp;</TD>
        </TR>
      ";
      $f_results = $sql->query("SELECT * FROM patrectemplate
                              ORDER BY prtname");
      if (($f_results>0) and ($sql->num_rows($f_results))) {
       echo "
         <TR><TD ALIGN=RIGHT>
          <B>"._("Custom Records")."</B> : 
         </TD><TD COLSPAN=2>
          <FORM ACTION=\"custom_records.php3\" METHOD=POST>
           <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$id\">
           <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
           <SELECT NAME=\"form\">
       ";
       while ($f_r = $sql->fetch_array ($f_results)) 
         echo "<OPTION VALUE=\"".$f_r["id"]."\">".$f_r["prtname"]."\n";
       echo "
           </SELECT>
           <INPUT TYPE=SUBMIT VALUE=\""._("Add")."\">
          </FORM>
         </TR>
        <TR>
         <TD>&nbsp;</TD>
         <TD><A HREF=\"custom_records.php3?$_auth&patient=$id\" 
          >"._("View/Manage")."</A></TD>
        </TR>
       ";
      } // end of if, checking for custom records....
      echo "
        <TR><TD ALIGN=RIGHT>
         <B>Dependent Information</B> : 
        </TD><TD ALIGN=LEFT>
     ";
//      removed as part of coverage overhaul
//     if (!$this_patient->isDependent()) {
//      $dep_query = "SELECT COUNT(*) FROM patient WHERE ptdep='".
//                   $this_patient->id."'";
//      $dep_result = $sql->query($dep_query);
//      $dep_r = $sql->fetch_array($dep_result);
//      $num_deps = $dep_r[0];
//      if ($num_deps<1)
//        echo "No Dependents";
//      else
//        echo "
//	 <A HREF=\"patient.php?$_auth&action=find&criteria=".
//	 "dependants&f1=$id\">"._("Dependents")."</A> [$num_deps]
//        ";
//      } else {
//      $guarantor = new Patient ($this_patient->ptdep);
//      echo "
//         <A HREF=\"manage.php?$_auth&action=view&id=".$this_patient->ptdep."\"
//         >"._("Guarantor")."</A>
//	</TD><TD>[".$guarantor->fullName()."]</TD></TR>
//     ";
//    }

     echo "
        <TR><TD ALIGN=RIGHT>
        <B>"._("Patient Information")."</B> : 
        </TD><TD> 
        <A HREF=\"patient.php?$_auth&action=modform&id=$id\"
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
        <A HREF=\"module_loader.php?$_auth&module=#class#&action=addform&patient=$id\"
         >"._("Add")."</A>
        </TD><TD> 
        <A HREF=\"module_loader.php?$_auth&module=#class#&patient=$id\"
         >"._("View/Manage")."</A>
        </TD><TD>
        </TD></TR>

	";

	$module_list = new module_list (PACKAGENAME, ".emr.module.php");
	echo $module_list->generate_list ($category, 0, $template);

    echo "
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
        <A HREF=\"module_loader.php?$_auth&module=#class#&patient=$id\"
         >"._("View")."</A>
        </TD><TD>
        </TD></TR>

	";

	$module_list = new module_list (PACKAGENAME, ".emr_report.module.php");
	echo $module_list->generate_list ($category, 0, $template);
	// end of loadable modules code

	echo "
        <!--

	  // this is commented out until we can make it work properly

        <TR><TD ALIGN=RIGHT>
        <B>"._("Reports and Certificates")."</B> : 
        </TD><TD>
        <A HREF=\"simplerep.php3?$_auth&action=choose&patient=$id\"
        >"._("Choose")."</A>
        </TD><TD>
        </TD><TD>
        </TD></TR>
        -->

		<!--	
        <TR><TD ALIGN=RIGHT>
        <B>"._("Patient Reports")."</B> : 
        </TD><TD>
        <A HREF=\"emrreports.php?$_auth&action=choose&patient=$id\"
        >"._("Choose")."</A>
        </TD><TD>
        </TD><TD>
        </TD></TR>
        -->
        </TABLE>

        <CENTER>
		<P>
        <A HREF=\"patient.php?$_auth\"
         >"._("Select Another Patient")."</A>
        </CENTER>
        <P>
      </CENTER>
     ";
} // if there is an ID specified
    
freemed_display_box_bottom ();
freemed_close_db ();
freemed_display_html_bottom ();
?>
