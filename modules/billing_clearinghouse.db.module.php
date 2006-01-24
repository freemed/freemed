<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class BillingClearinghouse extends MaintenanceModule {

	var $MODULE_NAME = "Billing Clearinghouses";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Billing Clearinghouse";
	var $table_name = "clearinghouse";
	var $order_field = "chname,chstate,chzip";

	var $variables		= array (
		'chname',
		'chaddr',
		'chcity',
		'chstate',
		'chzip',
		'chphone',
		'chetin',
		'chx12gssender',
		'chx12gsreceiver'
	);

	var $widget_hash = '##chname## (##chcity##, ##chstate##)';

	function BillingClearinghouse () {
		// Table definition
		$this->table_definition = array (
			'chname' => SQL__NOT_NULL(SQL__VARCHAR(50)),
			'chaddr' => SQL__VARCHAR(45),
			'chcity' => SQL__VARCHAR(30),
			'chstate' => SQL__CHAR(3),
			'chzip' => SQL__VARCHAR(10),
			'chphone' => SQL__VARCHAR(16),
			'chetin' => SQL__VARCHAR(24),
			'chx12gssender' => SQL__VARCHAR(20),
			'chx12gsreceiver' => SQL__VARCHAR(20),
			'id' => SQL__SERIAL
		);
	
		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor BillingClearinghouse

	function form () {
		global $display_buffer;

		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		switch ($GLOBALS['action']) {
			case 'modform':
			$r = freemed::get_link_rec ( $GLOBALS['id'], $this->table_name );
			foreach ($r AS $k => $v) {
				global ${$k};
				${$k} = prepare($v);
			}
			$this_action = 'mod';
			break;

			case 'addform':
			$this_action = 'add';
			break;
		}

		$display_buffer .= "
		<form action=\"".$this->page_name."\" method=\"post\">
		<input type=\"hidden\" name=\"module\" value=\"".prepare($GLOBALS['module'])."\" />
		<input type=\"hidden\" name=\"action\" value=\"".prepare($this_action)."\" />
		<input type=\"hidden\" name=\"return\" value=\"".prepare($_REQUEST['return'])."\" />
		<input type=\"hidden\" name=\"id\" value=\"".prepare($id)."\" />
		";

		$display_buffer .= html_form::form_table ( array (
			__("Clearinghouse Name") =>
			html_form::text_widget('chname', 50),

			__("Clearinghouse Address") =>
			html_form::text_widget('chaddr', 45),

			__("City, State Zip") =>
			html_form::text_widget('chcity', 30).', '.
			html_form::state_pulldown('chstate').' '.
			html_form::text_widget('chzip', 10),

			__("ETIN") =>
			html_form::text_widget('chetin', 24),

			__("X12 GS Sender ID") =>
			html_form::text_widget('chx12gssender', 20),

			__("X12 GS Receiver ID") =>
			html_form::text_widget('chx12gsreceiver', 20)
		) );

		$display_buffer .= "
		<div align=\"center\">
		<input type=\"submit\" name=\"__submit\" value=\"".( $action=='addform' ?
			__("Add") :
			__("Modify") )."\" class=\"button\" />
		<input type=\"submit\" name=\"__submit\" value=\"".__("Cancel")."\" class=\"button\" />
		</div>
		</form>
		";

	} // end method form

	function view () { 
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT * FROM ".
				$this->table_name." ".
				"ORDER BY ".$this->order_field),
			$this->page_name,
			array (
				__("Name")	=>	'chname',
				__("City")	=>	'chcity',
				__("State")	=>	'chstate',
				__("ETIN")	=>	'chetin'
			),
			array ("", "", ""),
			array("","","",""),
			"", "",
			ITEMLIST_MOD|ITEMLIST_VIEW
		);
	} // end method view

	function widget ( $varname, $_options = NULL ) {
		global ${$varname};

		// Get all entries
		$result = $GLOBALS['sql']->query('SELECT * FROM '.
			$this->table_name.' ORDER BY '.$this->order_field);
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
			$w[$r['chname'].' ('.$r['chcity'].', '.$r['chstate'].')'] = $r['id'];
		}

		// Pass widget parameters
		return html_form::select_widget($varname, $w, $_options);
	} // end method widget

} // end class BillingClearinghouse

register_module ("BillingClearinghouse");

?>
