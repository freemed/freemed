<?php
 // $Id$
 // $Author$

// Check for presence of patient_history
$patient_history = patient_history_list();
if ($patient_history) {
	print "
	<CENTER>
	<FORM ACTION=\"manage.php\" METHOD=POST>
	".html_form::select_widget("id", $patient_history)."
	<INPUT TYPE=IMAGE SRC=\"lib/template/default/magnifying_glass.png\"
		WIDTH=\"16\" HEIGHT=\"16\" ALT=\"[Manage]\">
	</FORM>
	</CENTER>
	";
} // end checking for patient history

?>
<UL>
	<LI><A HREF="admin.php"><?php print _("Administration Menu"); ?></A>
	<LI><A HREF="billing_functions.php"
		><?php print _("Billing Functions"); ?></A>
	<LI><A HREF="db_maintenance.php"
		><?php print _("Database Maintenance"); ?></A>
	<LI><A HREF="patient.php"><?php print _("Patient Functions"); ?></A>
	<LI><A HREF="reports.php"><?php print _("Reports"); ?></A>
</UL>
<HR>
<UL>
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
		print "\t<LI><A HREF=\"".$v."\" ".
			"onMouseOver=\"window.status='".prepare(_($k))."'; ".
			"return true;\" ".
			"onMouseOut=\"window.status=''; return true;\">".
			prepare(_($k))."</A>\n";
		} // end checking for null
	} // end foreach
	print "</UL>\n";
} else { // if is array
	print "&nbsp;\n";
} // end if is array


?>
