<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class EnclosureTypesMaintenance extends MaintenanceModule {

	var $MODULE_NAME    = "Enclosure Types Maintenance";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.2';

	var $table_name     = "enctype";
	var $record_name    = "Enclosure Type";
	var $order_field    = "enclosure";
 
	var $variables      = array (
		"enclosure"
	); 

	function EnclosureTypesMaintenance() {
		// Table definition
		$this->table_definition = array (
			'enclosure' => SQL__VARCHAR(50),
			'id' => SQL__SERIAL
		);

		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor EnclosureTypesMaintenance

	function addform () { $this->view(); }

	function modform () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		 // grab record number "id"
		$r = freemed::get_link_rec($id, $this->table_name);
		foreach ($r AS $k => $v) {
			global ${$k};
			${$k} = stripslashes($v);
		}

		$display_buffer .= "
		<p/>
		<form ACTION=\"$this->page_name\" METHOD=\"POST\">
		<input TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"/> 
		<input TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\"/> 
		<input TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"/>

		<div ALIGN=\"CENTER\">
		".__($this->record_name)." :
		".html_form::text_widget('enclosure', 25, 50)."
		</div>
 
		<p/>
		<div ALIGN=\"CENTER\">
		<input TYPE=\"SUBMIT\" VALUE=\" ".__("Modify")." \"/>
		<input TYPE=\"RESET\" VALUE=\"".__("Clear")."\"/>
		</div></form>
		";
	} // end method modform

	function view () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
		$display_buffer .= freemed_display_itemlist (
			$GLOBALS['sql']->query(
				"SELECT * ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY ".$this->order_field
			),
			$this->page_name,
			array (
				__($this->record_name)	=>	"enclosure"
			),
			array("")
		);
 
		$display_buffer .= "
		<table CLASS=\"reverse\" WIDTH=\"100%\" BORDER=\"0\"
		 CELLSPACING=\"0\" CELLPADDING=\"3\">
		<tr VALIGN=\"CENTER\">
		<td VALIGN=\"CENTER\"><form ACTION=\"$this->page_name\" METHOD=\"POST\"
		 ><input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"add\"/>
		<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\">
		".html_form::text_widget('enclosure', 25, 50)."</td>
		<td VALIGN=\"CENTER\">
		<input TYPE=\"SUBMIT\" VALUE=\"".__("Add")."\"/></form></td>
		</tr>
		</table>
		";
	} // end method view

} // end class EnclosureTypesMaintenance

register_module ("EnclosureTypesMaintenance");

?>
