<?php
	// $Id$
	// $Author$
	// lic : GPL, v2

LoadObjectDependency('_FreeMED.MaintenanceModule');

class PharmacyMaintenance extends MaintenanceModule {

	var $MODULE_NAME    = "Pharmacy Maintenance";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name    = "Pharmacies";
	var $table_name     = "pharmacy";

	var $widget_hash    = "##phname## (##phcity##, ##phstpr##)";

	var $variables = array (
		'phname',
		'phaddr1',
		'phaddr2',
		'phcity',
		'phstpr',
		'phzip',
		'phfax',
		'phmethod',
		'id'
	);

	function PharmacyMaintenance () {
		global $display_buffer;

		$this->table_definition = array (
			'phname'	=>	SQL__VARCHAR(50),
			'phaddr1'	=>	SQL__VARCHAR(150),
			'phaddr2'	=>	SQL__VARCHAR(150),
			'phcity'	=>	SQL__VARCHAR(150),
			'phstpr'	=>	SQL__VARCHAR(3),
			'phzip'		=>	SQL__VARCHAR(10),
			'phfax'		=>	SQL__VARCHAR(16),
			'phmethod'	=>	SQL__VARCHAR(50),
			'id'		=>	SQL__SERIAL
		);
		if ($debug) {
		global $sql;$display_buffer .= "query = \"".$sql->create_table_query(
			$this->table_name, $this->table_definition).
			"\"<BR>\n";
		} // end if $debug

			// Run constructor
		$this->MaintenanceModule();
	} // end constructor PharmacyMaintenance

	function form () {
		global $display_buffer, $action;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
		switch ($action) { // inner switch
			case "addform":
			break;

			case "modform":
			if ($id<1) trigger_error ("NO ID", E_USER_ERROR);
			$r = freemed::get_link_rec ($id, $this->table_name);
			foreach ($r AS $k => $v) {
				global ${$k};
				${$k} = $v;
			}
			break;
		} // end inner switch

		$display_buffer .= "
		<p/>
		<form ACTION=\"".$this->page_name."\" METHOD=\"POST\">
		<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
		( ($action=="addform") ? "add" : "mod" )."\"/> 
		<input TYPE=\"HIDDEN\" NAME=\"id\"   VALUE=\"".prepare($id)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"module\"   VALUE=\"".prepare($module)."\"/>
		".html_form::form_table ( array (
		__("Pharmacy Name") => html_form::text_widget('phname', 50),
		__("Address Line 1") => html_form::text_widget('phaddr1', 150),
		__("Address Line 2") => html_form::text_widget('phaddr2', 150),
		__("City, State Zip") =>
			html_form::text_widget('phcity', 50)."<b>,</b> ".
			html_form::state_pulldown('phstpr')."&nbsp;".
			html_form::text_widget('phzip', 10),

		__("Preferred Transmission") =>
			html_form::select_widget('phmethod', array(
				__("Fax") => 'fax'
			)),

		__("Fax Number (as dialed)") => html_form::text_widget('phfax')

		) )."
		<p/>
		<div ALIGN=\"CENTER\">
		<input class=\"button\" TYPE=\"SUBMIT\" VALUE=\" ".
		( ($action=="addform") ? __("Add") : __("Modify") )." \"/>
		<input class=\"button\" NAME=\"submit\" TYPE=\"SUBMIT\" ".
			"VALUE=\"".__("Cancel")."\"/>
		</div></form>
		";
	} // end function PharmacyMaintenance->form()

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query (
				"SELECT phname, CONCAT(phcity,', ',phstpr) AS ".
					"citystate,id ".
				"FROM ".addslashes($this->table_name)." ".
				freemed::itemlist_conditions().
                		"ORDER BY phname,phcity,phstpr"
			),
			$this->page_name,
			array (
				__("Name") => "phname",
				__("City, State") => "citystate"
			),
			array ("", __("NO DESCRIPTION"))
		);
	} // end function PharmacyMaintenance->view()

} // end class PharmacyMaintenance

register_module ("PharmacyMaintenance");

?>
