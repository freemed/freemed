<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class FaxContacts extends MaintenanceModule {

	var $MODULE_NAME = "Fax Contacts";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Fax Contacts";
	var $table_name = "fcontact";
	var $order_field = "fcname,fcfax";

	var $variables		= array (
		'fcname',
		'fcfax'
	);

	var $widget_hash = '##fcname## ##fcfax##';

	function FaxContacts () {
		// Table definition
		$this->table_definition = array (
			'fcname' => SQL__NOT_NULL(SQL__VARCHAR(75)),
			'fcfax' => SQL__VARCHAR(16),
			'id' => SQL__SERIAL
		);
	
		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor FaxContacts

	function generate_form () {
		return array (
			__("Contact Name") =>
			html_form::text_widget('fcname', 75),

			__("Fax Number") =>
			html_form::text_widget('fcfax', 20)
		);
	} // end method generate_form

	function widget ( $varname, $conditions = false, $field = 'id', $options = NULL ) {
		include_once(freemed::template_file('ajax.php'));
		return ajax_widget($varname, get_class($this), $this, $field);
	} // end method widget

	// override ajax_lookup to allow this function to provide
	// results from the provider table as well
	function ajax_lookup ( $parameter, $field = 'id', $patient = NULL ) {
		$table = $this->table_name;
		$hash = $this->widget_hash;
		$limit = 10;		// logical limit to how many we can display
	
		// Extract keys
		$fields = _extract_keys ( $hash );
		foreach ($fields AS $f) {
			$q[] = $f.' LIKE \'%'.addslashes($parameter).'%\'';
		}
	
		$query = "SELECT * FROM ".$table." WHERE ( ".join(' OR ', $q)." ) ";
		$res = $GLOBALS['sql']->query($query);
		$count = 0;
		while ($r = $GLOBALS['sql']->fetch_array( $res ) ) {
			$count++;
			if ($count < $limit) {
				$_res = trim(_result_to_hash($r, $hash));
				$_res = addslashes($_res);
				syslog(LOG_INFO, "1: $_res @ $r[fcfax]");
				$return[] = $_res.'@'.$r['fcfax'];
			}
		}

		// Repeat for provider table
		include_once(resolve_module('providermodule'));
		$m = new providermodule();
		$table = $m->table_name;
		$hash = $m->widget_hash;

		// Extract keys
		$fields = _extract_keys ( $hash );
		$q = array();
		foreach ($fields AS $f) {
			$q[] = $f.' LIKE \'%'.addslashes($parameter).'%\'';
		}

		$query = "SELECT * FROM ".$table." WHERE ( ".join(' OR ', $q)." ) ";
		$res = $GLOBALS['sql']->query($query);
		while ($r = $GLOBALS['sql']->fetch_array( $res ) ) {
			$count++;
			if ($count < $limit) {
				$_res = trim(_result_to_hash($r, $hash));
				$_res = addslashes($_res);
				$return[] = $_res.'@'.$r[$field];
			}
		}

		if ($count >= $limit) { $return[] = " ... "; }
		return join('|', $return);
	} // end method ajax_lookup

	function view () { 
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT * FROM ".
				$this->table_name." ".
				"ORDER BY ".$this->order_field),
			$this->page_name,
			array (
				__("Contact")		=>	'fcname',
				__("Fax Number")	=>	'fcfax'
			),
			array ("", ""),
			array("",""),
			"", "",
			ITEMLIST_MOD|ITEMLIST_VIEW|ITEMLIST_DEL
		);
	} // end method view

} // end class FaxContacts

register_module ("FaxContacts");

?>
