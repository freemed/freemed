<?php
 // file: manage.php3
 // note: patient management functions -- links to other modules
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

  $page_name = "manage.php3";
  include ("global.var.inc");
  include ("freemed-functions.inc"); // API functions

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

switch ($action) {
  default:
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
         \"patient.php3?$_auth\"
        >"._("Select a Patient")."</A>
        </CENTER>
        <BR><BR>
      ";

     } else {
      $_auth   = "_ref=$page_name";
      echo "

     ".freemed_patient_box($this_patient)."

        <TABLE WIDTH=100% BORDER=0 CELLPADDING=3>
        <TR><TD ALIGN=RIGHT>
        <$STDFONT_B><B>"._("Appointments")."</B> : <$STDFONT_E>
        </TD><TD>
        <A HREF=\"book_appointment.php3?$_auth&patient=$id&type=pat\"
         ><$STDFONT_B>"._("Add")."<$STDFONT_E></A> 
        </TD><TD>
        <A HREF=\"manage_appointments.php3?$_auth&patient=$id\"
         ><$STDFONT_B>"._("View/Manage")."<$STDFONT_E></A><BR>
        </TD><TD>
        </TD></TR>
        <TR><TD>&nbsp;</TD>
        <TD><A HREF=\"show_appointments.php3?$_auth&patient=$id&type=pat\"
         ><$STDFONT_B>"._("Show Today")."<$STDFONT_E></A></TD>
        <TD>&nbsp;</TD>
        </TR>
        <TR><TD ALIGN=RIGHT>
        <$STDFONT_B><B>"._("Authorizations")." : </B><$STDFONT_E>
        </TD><TD>
        <A HREF=\"authorizations.php3?$_auth&patient=$id&action=addform\"
        ><$STDFONT_B>"._("Add")."<$STDFONT_E></A>
        </TD><TD>
        <A HREF=\"authorizations.php3?$_auth&patient=$id&action=view\"
        ><$STDFONT_B>"._("View/Manage")."<$STDFONT_E></A>
        </TD></TR>
        <TR><TD ALIGN=RIGHT>
         <$STDFONT_B><B>"._("Billing Functions")."</B> : <$STDFONT_E>
        </TD><TD>
         <A HREF=\"payment_record.php3?$_auth&patient=$id\"
         ><$STDFONT_B>Patient Ledger<$STDFONT_E></A>
        </TD><TD>
         <A HREF=\"payment_record.php3?$_auth&patient=$id&action=addform\"
         ><$STDFONT_B>New Record Entry<$STDFONT_E></A>
        </TD>
        </TR>
        <TR><TD>&nbsp;</TD><TD>
        <A HREF=\"manage_payment_records.php3?$_auth&patient=$id\"
         ><$STDFONT_B>"._("View/Manage")."<$STDFONT_E></A>
        </TD><TD>
        &nbsp;
        </TD></TR>
      ";
      $f_results = fdb_query("SELECT * FROM patrectemplate
                              ORDER BY prtname");
      if (($f_results>0) and (fdb_num_rows($f_results))) {
       echo "
         <TR><TD ALIGN=RIGHT>
          <$STDFONT_B><B>"._("Custom Records")."</B> : <$STDFONT_E>
         </TD><TD COLSPAN=2>
          <FORM ACTION=\"custom_records.php3\" METHOD=POST>
           <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$id\">
           <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
           <SELECT NAME=\"form\">
       ";
       while ($f_r = fdb_fetch_array ($f_results)) 
         echo "<OPTION VALUE=\"".$f_r["id"]."\">".$f_r["prtname"]."\n";
       echo "
           </SELECT>
           <INPUT TYPE=SUBMIT VALUE=\""._("Add")."\">
          </FORM>
         </TR>
        <TR>
         <TD>&nbsp;</TD>
         <TD><A HREF=\"custom_records.php3?$_auth&patient=$id\" 
          ><$STDFONT_B>"._("View/Manage")."<$STDFONT_E></A></TD>
        </TR>
       ";
      } // end of if, checking for custom records....
      echo "
        <TR><TD ALIGN=RIGHT>
         <$STDFONT_B><B>Dependent Information</B> : <$STDFONT_E>
        </TD><TD ALIGN=LEFT>
     ";

     if (!$this_patient->isDependent()) {
      $dep_query = "SELECT COUNT(*) FROM patient WHERE ptdep='".
                   $this_patient->id."'";
      $dep_result = fdb_query($dep_query);
      $dep_r = fdb_fetch_array($dep_result);
      $num_deps = $dep_r[0];
      if ($num_deps<1)
        echo "<$STDFONT_B>No Dependents<$STDFONT_E>";
      else
        echo "
	 <$STDFONT_B><A HREF=\"patient.php3?$_auth&action=find&criteria=".
	 "dependants&f1=$id\">"._("Dependents")."</A> [$num_deps]<$STDFONT_E>
        ";
      } else {
      $guarantor = new Patient ($this_patient->ptdep);
      echo "
         <A HREF=\"manage.php3?$_auth&action=view&id=".$this_patient->ptdep."\"
         ><$STDFONT_B>"._("Guarantor")."<$STDFONT_E></A>
	</TD><TD><$STDFONT_B>[".$guarantor->fullName()."]<$STDFONT_E></TD></TR>
      ";
      }

     echo "
        <TR><TD ALIGN=RIGHT>
         <$STDFONT_B><B>"._("Episode of Care")."</B> : <$STDFONT_E>
        </TD><TD ALIGN=LEFT>
         <A HREF=\"episode_of_care.php3?$_auth&patient=$id&action=addform\"
          ><$STDFONT_B>"._("Add")."<$STDFONT_E></A>
        </TD><TD>
         <A HREF=\"episode_of_care.php3?$_auth&patient=$id\"
          ><$STDFONT_B>"._("View/Manage")."<$STDFONT_E></A>
        </TD></TR>
        <TR><TD ALIGN=RIGHT>
        <$STDFONT_B><B>"._("Patient Information")."</B> : <$STDFONT_E>
        </TD><TD> 
        <A HREF=\"patient.php3?$_auth&action=modform&id=$id\"
         ><$STDFONT_B>"._("Modify")."<$STDFONT_E></A>
        </TD><TD>&nbsp;
        </TD><TD>
        </TD></TR>

        <TR><TD ALIGN=RIGHT>
        <$STDFONT_B><B>"._("Prescriptions")."</B> : <$STDFONT_E>
        </TD><TD>
        <A HREF=\"Rx.php3?$_auth&action=addform&patient=$id\"
         ><$STDFONT_B>"._("Add")."<$STDFONT_E></A>
        </TD><TD> 
        <A HREF=\"Rx.php3?$_auth&patient=$id\"
         ><$STDFONT_B>"._("View/Manage")."<$STDFONT_E></A>
        </TD><TD>
        </TD></TR>

        <TR><TD ALIGN=RIGHT>
        <$STDFONT_B><B>".("Procedures")."</B> : <$STDFONT_E>
        </TD><TD>
        <A HREF=\"procedure.php3?$_auth&action=addform&patient=$id\"
         ><$STDFONT_B>"._("Add")."<$STDFONT_E></A>
        </TD><TD> 
        <A HREF=\"procedure.php3?$_auth&patient=$id\"
         ><$STDFONT_B>"._("View/Manage")."<$STDFONT_E></A>
         </TD><TD>
         </TD></TR>

        <TR><TD ALIGN=RIGHT>
        <$STDFONT_B><B>"._("Progress Notes")."</B> : <$STDFONT_E>
        </TD><TD>
        <A HREF=\"progress_notes.php3?$_auth&action=addform&patient=$id\"
         ><$STDFONT_B>"._("Add")."<$STDFONT_E></A>
        </TD><TD> 
        <A HREF=\"progress_notes.php3?$_auth&patient=$id\"
         ><$STDFONT_B>"._("View/Manage")."<$STDFONT_E></A>
        </TD><TD>
        </TD></TR>

        <TR><TD ALIGN=RIGHT>
        <$STDFONT_B><B>"._("Reports and Certificates")."</B> : <$STDFONT_E>
        </TD><TD>
        <A HREF=\"simplerep.php3?$_auth&action=choose&patient=$id\"
        ><$STDFONT_B>"._("Choose")."<$STDFONT_E></A>
        </TD><TD>
        </TD><TD>
        </TD></TR>
        </TABLE>
      ";

      // guarantor or dependant -- ?
      //if (!$this_patient->isDependent()) {
      //  echo "
      //    <A HREF=\"patient.php3?$_auth&action=find&criteria=dependants&f1=$id\"
      //     ><$STDFONT_B>$Show_Dependants<$STDFONT_E></A>
      //    <BR>
      //  ";
      //} else {
      //  echo "
      //    <A HREF=\"patient.php3?$_auth&action=find&criteria=guarantor&f1=$id\"
      //    ><$STDFONT_B>$Show_Guarantor<$STDFONT_E></A>
      //    <BR>
      //  ";
      //} // end of checking for dependency
      echo "
          <CENTER>
          <A HREF=\"patient.php3?$_auth\"
           ><$STDFONT_B>"._("Select Another Patient")."<$STDFONT_E></A>
          </CENTER>
          <P>
        </CENTER>
      ";
    } // if there is an ID specified
    
    freemed_display_box_bottom ();
    break;
} // main switch statement

freemed_close_db ();
freemed_display_html_bottom ();
?>
