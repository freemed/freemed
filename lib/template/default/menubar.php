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
<?php if (isset($menu_bar)) { print "<HR>\n".$menu_bar; } else { print "&nbsp;"; } ?>
