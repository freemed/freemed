<?php
 // $Id$
 // $Author$
 // note: template for patient management functions
 // lic : GPL, v2

//----- Pull configuration for this user
if (!is_object($this_user)) $this_user = new User;

//----- Extract all configuration data
if (is_array($this_user->manage_config)) extract($this_user->manage_config);

//----- Check for a *reasonable* refresh time
if ($automatic_refresh_time > 14) $automatic_refresh = $automatic_refresh_time;

//----- Display patient information box...
$display_buffer .= freemed_patient_box($this_patient);

//----- Suck in management panels
//-- Static first...
foreach ($static_components AS $garbage => $component) {
	switch ($component) {
		case "appointments": // Appointments static component
		// Add header and strip at top
		$panel[_("Appointments")] .= "
			<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0
			 CELLPADDING=3>
			<TR><TD COLSPAN=3 VALIGN=MIDDLE ALIGN=CENTER
			 CLASS=\"menubar_items\">
			<A HREF=\"book_appointment.php?patient=$id&type=pat\"
			>"._("Add")."</A> |
			<A HREF=\"manage_appointments.php?patient=$id\"
			>"._("View/Manage")."</A> |
			<A HREF=\"show_appointments.php?patient=$id&type=pat\"
			>"._("Show Today")."</A>
			</TD></TR>
		";

		// Show last few appointments
		$panel[_("Appointments")] .= "
			<TR><TD COLSPAN=3 VALIGN=MIDDLE ALIGN=CENTER>
			<B>FIXME! FIXME!</B>
			</TD></TR>
		";

		// Footer
		$panel[_("Appointments")] .= "
			</TABLE>
		";
		break; // end appointments

		case "custom_reports":
		$f_results = $sql->query("SELECT * FROM patrectemplate ".
			"ORDER BY prtname");
		if ($sql->results($f_results)) {
			$panel[_("Custom Records")] .= "
          		<FORM ACTION=\"custom_records.php\" METHOD=POST>
        		<INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($id)."\">
			<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
			<SELECT NAME=\"form\">
			";
			while ($f_r = $sql->fetch_array ($f_results)) 
			$display_buffer .= "<OPTION VALUE=\"".$f_r["id"]."\">".
				$f_r["prtname"]."\n";
			$display_buffer .= "
				</SELECT>
				<INPUT TYPE=SUBMIT VALUE=\""._("Add")."\">
				</FORM>
			";
		} else {
			// Quick null panel
			$panel[_("Custom Records")] .= "
				<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0
				 CELLPADDING=3>
				<TR><TD VALIGN=MIDDLE ALIGN=CENTER
				 CLASS=\"menubar_items\">
				<A HREF=\"custom_records.php?patient=$id\" 
				>"._("View/Manage")."</A>
				</TD></TR>
				<TR><TD ALIGN=CENTER VALIGN=MIDDLE>
				<B>"._("NONE")."</B>
				</TD></TR></TABLE>
			";
		} // end checking for results
		break; // end custom_reports

		case "patient_information":
		$panel[_("Patient Information")] .= "
			<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0
			 CELLPADDING=3>
			<TR><TD VALIGN=MIDDLE ALIGN=CENTER
			 CLASS=\"menubar_items\">
			<A HREF=\"patient.php?action=modform&id=$id\" 
			>"._("Modify")."</A>
			</TD></TR>
			<TR><TD ALIGN=CENTER VALIGN=MIDDLE>
			<B>Vital information goes here?</B>
			</TD></TR></TABLE>
		";
		break; // end patient information

		default: // Everything else.... do nothing (ERROR)
		break; // end default
	} // end component switch
} // end static components

//-- ... then modular
foreach ($modular_components AS $garbage => $component) {
	// Determine if the class exists
	if (!is_object($module_list))
		$module_list = new module_list (PACKAGENAME, ".emr.module.php");
	
	// End checking for component
	if ($module_list->check_for($component)) {
		// Execute proper portion and add to panel
		$panel[_($module_list->get_module_name($component))] .= "
			<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0
			 CELLPADDING=3>
			<TR><TD VALIGN=MIDDLE ALIGN=CENTER
			 CLASS=\"menubar_items\">
			<A HREF=\"module_loader.php?module=".
			$component."&patient=$id\" 
			>"._("View/Manage")."</A> |
			<A HREF=\"module_loader.php?module=".
			$component."&patient=$id&action=addform\" 
			>"._("Add")."</A>
			</TD></TR>
			<TR><TD ALIGN=CENTER VALIGN=MIDDLE>
			".module_function($component, "summary",
				array (
					$id, // patient ID
					5 // hardcode 5 items for now
				)
			)."</TD></TR></TABLE>
		";
	} else {
		// Don't do anything if it doesn't exist
	} // end checking for component existing
} // end static components

//----- Determine column requirements
if ($manage_columns < 1) $manage_columns = 1;
if (count($panel) > 0) {
	$column_cutoff = ceil ( count($panel) / $manage_columns );
} // check for ability to display panels

//----- Display tables

if (count($panel) > 0) {
	// Table header
	$display_buffer .= "
	<TABLE WIDTH=\"100%\" CELLSPACING=3 CELLPADDING=0 BORDER=0>
	<TR VALIGN=MIDDLE ALIGN=CENTER>
	";

	$column = 1; reset ($panel);
	foreach ($panel AS $k => $v) {
		// Check to see if we're on a new row yet
		if ($column > $manage_columns) {
			$column = 1;

			// Display footer and new header
			$display_buffer .= "
			</TR><TR VALIGN=MIDDLE ALIGN=CENTER>
			";
		}

		// Add panel
		$display_buffer .= "
		<TD VALIGN=TOP ALIGN=CENTER>
		<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0 CELLPADDING=0>
		<TR><TD CLASS=\"reverse\" VALIGN=MIDDLE ALIGN=CENTER>
		<B>".prepare($k)."</B>
		</TD></TR>
		<TR><TD VALIGN=MIDDLE ALIGN=CENTER>
		<CENTER>$v</CENTER>
		</TD></TR></TABLE>
		</TD>
		";

		// Move to the next column
		$column += 1;
	} // end looping

	// Fill up empty space
	if ($column < $manage_columns) {
		for ($i=1; $i<=($manage_columns-$column); $i++)
			$display_buffer .= "<TD>&nbsp;</TD>\n";
	} // end filling up empty space

	// Table footer
	$display_buffer .= "
	</TR></TABLE>
	";

} // end checking for *any* panels

// **************************************************** STATIC MODULES

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

//----- Add to menu bar
$module_list = new module_list (PACKAGENAME, ".emr.module.php");
// Form template for menubar
$template_menubar = "<LI><A HREF=\"module_loader.php?module=#class#&".
	"patient=$id\">#name#</A>\n";
$menu_bar .= "<UL>\n";
$menu_bar .= $module_list->generate_list ($category, 0, $template_menubar);
if ($action != "config") {
	$menu_bar .= "<LI><A HREF=\"manage.php?id=$id&action=config\"".
		">"._("Configure")."</A>\n";
}
$menu_bar .= "</UL>\n";

/*
$display_buffer .= "
	<TR><TD ALIGN=RIGHT>
	<BR>
    	<B>"._("Certifications")."</B>
	<BR>
    	</TD>
";
$category = "EMR Certifications";
$module_template = "
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

$category = "Electronic Medical Record Report";
$module_template = "
        <TR><TD ALIGN=RIGHT>
        <B>#name#</B> : 
        </TD>
		<TD> 
        <A HREF=\"module_loader.php?module=#class#&patient=$id\"
         >"._("View")."</A>
        </TD><TD>
        </TD></TR>

";
*/

/*
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
         >"._("Select Another Patient")."</A> |
	<A HREF=\"manage.php?id=$id&action=config\"
	 >"._("Configuration")."</A>
        </CENTER>
        <P>
      </CENTER>
";
*/
    
?>
