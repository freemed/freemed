<?php
 // $Id$
 // note: insurance company group(s) functions
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

if (!defined("__INSURANCE_COMPANY_GROUP_MODULE_PHP__")) {

define (__INSURANCE_COMPANY_GROUP_MODULE_PHP__, true);

class insuranceCompanyGroupMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME	= "Insurance Company Group Maintenance";
	var $MODULE_VERSION	= "0.1";

	var $table_name		= "inscogroup";
	var $record_name	= "Insurance Company Groups";

	var $variables		= array (
		"inscogroup"
	);

	function insuranceCompanyGroupMaintenance () {
		$this->freemedMaintenanceModule();
	} // end constructor insuranceCompanyGroupMaintenance

	function addform () { $this->form(); }
	function modform () { $this->form(); }

 	function form () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		if ($id) {
			$r = freemed_get_link_rec ($id, $this->table_name);
			extract ($r);
		} // end checking for id

		$display_buffer .= "
		<P>
		<FORM ACTION=\"$this->page_name\" METHOD=POST>
			<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
				( ($action=="addform") ? "add" : "mod" )."\"> 
			<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\"> 
			<INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"".prepare($id)."\"  >
			".html_form::form_table ( array (
				_("Name") =>
					"<INPUT TYPE=TEXT NAME=\"inscogroup\" SIZE=20 MAXLENGTH=20 ".
					"VALUE=\"".prepare($inscogroup)."\">"
			) )."
		<BR>

		<CENTER>
			<INPUT TYPE=SUBMIT VALUE=\" ".
				( ($action=="addform") ? _("Add") : _("Modify") )." \">
			<INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
		</CENTER>

		</FORM>
		";
	} // end function insuranceCompanyGroupMaintenance->form

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query ("SELECT inscogroup,id FROM inscogroup ".
				"ORDER BY inscogroup"),
			$this->page_name,
			array (
				_($this->record_name)		=>	"inscogroup"
			),
			array (
				""
			)
		);
	} // end function insuranceCompanyGroupMaintenance->view
 
} // end of master case statement

register_module ("insuranceCompanyGroupMaintenance");

} // end if not defined

?>
