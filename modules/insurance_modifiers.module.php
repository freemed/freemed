<?php
 // $Id$
 // note: internal attributes for insurance companies
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class InsuranceModifiersMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Insurance Modifiers Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name = "insmod";
	var $record_name = "Insurance Modifiers";
	var $order_field = "insmoddesc";
 
	var $variables = array (
		"insmod",
		"insmoddesc"
	);
 
	function InsuranceModifiersMaintenance () {
		// Table definition
		$this->table_definition = array (
			'insmod' => SQL__VARCHAR(15),
			'insmoddesc' => SQL__VARCHAR(50),
			'id' => SQL__SERIAL
		);

		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor InsuranceModifiersMaintenance

	function addform () { $this->view(); }

	function modform () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
		$r = freemed::get_link_rec ($id, $this->table_name);
		extract ($r);

		$display_buffer .= "
		<p/>
		<form ACTION=\"$this->page_name\" METHOD=\"POST\">
		<input TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"/> 
		<input TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\"/> 
		<input TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"/>

		".html_form::form_table ( array (
		__("Modifier") =>
			html_form::text_widget('insmod', 15),
		__("Description") =>
			html_form::text_widget('insmoddesc', 20, 50)
		) )."

		<p/>
		<div ALIGN=\"CENTER\">
		<input class=\"button\" type=\"SUBMIT\" VALUE=\" ".
		 ( ($action=="addform") ? __("Add") : __("Modify") )." \"/>
		<input TYPE=\"RESET\" VALUE=\"".__("Clear")."\"/>
		<input class=\"button\" name=\"submit\" ".
		"type=\"SUBMIT\" VALUE=\"".__("Cancel")."\"/>
		</div></form>
		<p/>
		";
	} // end function InsuranceModifiersMaintenance->modform()

	function view () {
		global $display_buffer;
		global $sql, $module;

		$display_buffer .= freemed_display_itemlist (
			$sql->query(
				"SELECT * ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY ".$this->order_field
			),
			$this->page_name,
			array (
				__("Modifier") => "insmod",
				__("Description") => "insmoddesc"
			),
			array (
				"",
				__("NO DESCRIPTION")
			)
		);  
		$display_buffer .= "
		<div ALIGN=\"CENTER\">
		<table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"3\">
		 <tr>
		  <td>".__("Modifier")."</td>
		  <td>".__("Description")."</td>
		  <td>&nbsp;</td>
		 </tr>
		 <tr VALIGN=\"CENTER\">
		 <td VALIGN=\"CENTER\"><FORM ACTION=\"$this->page_name\" METHOD=\"POST\">
		  <input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"add\"/>
		  <input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
		  <input TYPE=\"TEXT\" NAME=\"insmod\" SIZE=\"15\" MAXLENGTH=\"16\"></td>
		 <td VALIGN=\"CENTER\">
		  <input TYPE=\"TEXT\" NAME=\"insmoddesc\" SIZE=\"20\"
		   MAXLENGTH=\"50\"></td>
		 <td VALIGN=\"CENTER\"><input TYPE=\"SUBMIT\" VALUE=\"".__("Add")."\"></form></td>
		 </tr>
		</table>
		</div>
		";
	} // end function InsuranceModifiersMaintenance->view()

} // end class InsuranceModifiersMaintenance

register_module("InsuranceModifiersMaintenance");

?>
