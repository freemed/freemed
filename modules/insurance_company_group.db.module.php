<?php
 // $Id$
 // note: insurance company group(s) functions
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class InsuranceCompanyGroupMaintenance extends MaintenanceModule {

	var $MODULE_NAME	= "Insurance Company Group Maintenance";
	var $MODULE_AUTHOR	= "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION	= "0.1";
	var $MODULE_FILE	= __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name		= "inscogroup";
	var $record_name	= "Insurance Company Groups";

	var $variables		= array (
		"inscogroup"
	);

	function InsuranceCompanyGroupMaintenance () {
		// Table definition
		$this->table_definition = array (
			'inscogroup' => SQL__VARCHAR(50),
			'id' => SQL__SERIAL
		);
	
		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor InsuranceCompanyGroupMaintenance

	function addform () { $this->form(); }
	function modform () { $this->form(); }

 	function form () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		if ($id) {
			$r = freemed::get_link_rec ($id, $this->table_name);
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
				__("Name") =>
					"<INPUT TYPE=TEXT NAME=\"inscogroup\" SIZE=20 MAXLENGTH=20 ".
					"VALUE=\"".prepare($inscogroup)."\">"
			) )."
		<BR>

		<CENTER>
			<INPUT TYPE=SUBMIT VALUE=\" ".
				( ($action=="addform") ? __("Add") : __("Modify") )." \">
			<INPUT TYPE=RESET  VALUE=\"".__("Clear")."\">
		</CENTER>

		</FORM>
		";
	} // end function InsuranceCompanyGroupMaintenance->form

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
	} // end function InsuranceCompanyGroupMaintenance->view
 
} // end of master case statement

register_module ("InsuranceCompanyGroupMaintenance");

?>
