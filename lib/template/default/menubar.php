<?php
 // $Id$
 // $Author$

// Check for presence of patient and pagehistories
$patient_history = patient_history_list();
$page_history = page_history_list();
if ($patient_history or $page_history) {
	print "
	<table WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\"0\" VALIGN=\"TOP\"
	 ALIGN=\"CENTER\">
	";
}
if ($patient_history) {
	print "
	<TR><TD ALIGN=\"RIGHT\">
	<form ACTION=\"manage.php\" METHOD=\"POST\">
	".html_form::select_widget("id", $patient_history)."
	</TD><TD ALIGN=\"CENTER\">
	<input TYPE=\"IMAGE\" SRC=\"lib/template/default/magnifying_glass.".
	IMAGE_TYPE."\"
		WIDTH=\"16\" HEIGHT=\"16\" ALT=\"[Manage]\"/>
	</form>
	</TD></TR>
	";
} // end checking for patient history

if ($page_history) {
	// Set current page as default selection
	$location = basename($PHP_SELF);
	
	// Show the actual pick list
	print "
	<TR><TD ALIGN=RIGHT>
	<FORM ACTION=\"redirect.php\" METHOD=\"POST\">
	".html_form::select_widget("location", $page_history)."
	</TD><TD ALIGN=\"CENTER\">
	<INPUT TYPE=\"IMAGE\" SRC=\"lib/template/default/forward.".
	IMAGE_TYPE."\"
		WIDTH=\"16\" HEIGHT=\"16\" ALT=\"[Jump to page]\">
	</FORM>
	</TD></TR>
	";
} // end checking for page history

if ($patient_history or $page_history) {
	print "
	</TABLE>
	";
}

?>
<UL>
	<LI><A HREF="admin.php"><?php print _("Administration Menu"); ?></A>
	<LI><A HREF="billing_functions.php?patient=<?php print $SESSION["current_patient"]; ?>"
		><?php print _("Billing Functions"); ?></A>
	<LI><A HREF="calendar.php"><?php print _("Calendar"); ?></A>
	<LI><A HREF="call-in.php"><?php print _("Call-In"); ?></A>
	<LI><A HREF="db_maintenance.php"
		><?php print _("Database Maintenance"); ?></A>
	<LI><A HREF="messages.php"><?php print _("Messages"); ?></A> |
	    <A HREF="messages.php?action=addform"><?php print _("Add"); ?></A>
	<LI><A HREF="patient.php"><?php print _("Patients"); ?></A> |
	    <A HREF="patient.php?action=addform"><?php print _("New"); ?></A>
	<LI><A HREF="reports.php"><?php print _("Reports"); ?></A>
</UL>
<hr/>
<UL>
<?php
//----- Check for help file link
if ( ($help_url = help_url()) != "help.php" ) print "\t<LI><A HREF=\"#\" ".
	"onClick=\"window.open('".$help_url."', 'Help', 'width=600,height=400,".
	"resizable=yes');\">"._("Help")."</A>\n";
?>
	<LI><A HREF="preferences.php"><?php print _("Preferences"); ?></A>
	<LI><A HREF="main.php"><?php print _("Return to Main Menu"); ?></A>
	<LI><A HREF="logout.php"><?php print _("Logout"); ?></A>
</UL>
<!-- new functions come *after* everything else -->
<?php 

//----- Check to see if a menubar array exists
if (is_array($menu_bar)) {
	print "<HR>\n\n";
	print "<UL>\n";
	foreach ($menu_bar AS $k => $v) {
		if ($v != NULL) {
		if (strpos($v, "help.php")===false) {
		print "\t<LI><A HREF=\"".$v."\" ".
			"onMouseOver=\"window.status='".prepare(_($k))."'; ".
			"return true;\" ".
			"onMouseOut=\"window.status=''; return true;\">".
			prepare(_($k))."</A>\n";
		} else { // if there *is* a help string in there
		// Make sure that bad help links aren't displayed
		if ($v != "help.php") print "\t<LI><A HREF=\"#\" ".
			"onClick=\"window.open('".$v."', 'Help', ".
			"'width=600,height=400,resizable=yes');\" ".
			"onMouseOver=\"window.status='".prepare(_($k))."'; ".
			"return true;\" ".
			"onMouseOut=\"window.status=''; return true;\">".
			prepare(_($k))."</A>\n";
		} // end checking for help.php
		} // end checking for null
	} // end foreach
	print "</UL>\n";
} else { // if is array
	print "&nbsp;\n";
} // end if is array


?>
