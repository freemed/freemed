<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class BillingService extends MaintenanceModule {

	var $MODULE_NAME = "Billing Service";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Billing Service";
	var $table_name = "bservice";
	var $order_field = "bsname,bsstate,bszip";

	var $variables		= array (
		'bsname',
		'bsaddr',
		'bscity',
		'bsstate',
		'bszip',
		'bsphone',
		'bsetin',
		'bstin'
	);

	var $widget_hash = '##bsname## (##bscity##, ##bsstate##)';

	function BillingService () {
		// Table definition
		$this->table_definition = array (
			'bsname' => SQL__NOT_NULL(SQL__VARCHAR(50)),
			'bsaddr' => SQL__VARCHAR(45),
			'bscity' => SQL__VARCHAR(30),
			'bsstate' => SQL__CHAR(3),
			'bszip' => SQL__VARCHAR(10),
			'bsphone' => SQL__VARCHAR(16),
			'bsetin' => SQL__VARCHAR(24),
			'bstin' => SQL__VARCHAR(24),
			'id' => SQL__SERIAL
		);
	
		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor BillingService

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
		<input type=\"hidden\" name=\"id\" value=\"".prepare($id)."\" />
		";

		$display_buffer .= html_form::form_table ( array (
			__("Service Name") =>
			html_form::text_widget('bsname', 50),

			__("Address") =>
			html_form::text_widget('bsaddr', 45),

			__("City, State Zip") =>
			html_form::text_widget('bscity', 30).', '.
			html_form::state_pulldown('bsstate').' '.
			html_form::text_widget('bszip', 10),

			__("ETIN") =>
			html_form::text_widget('bsetin', 24),

			__("TIN") =>
			html_form::text_widget('bstin', 24)
		) );

		$display_buffer .= "
		<div align=\"center\">
		<input type=\"submit\" name=\"__submit\" value=\"".prepare( $action=='addform' ?
			__("Add") :
			__("Modify") )."\" class=\"button\" />
		<input type=\"submit\" name=\"__submit\" value=\"".prepare(__("Cancel"))."\" class=\"button\" />
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
				__("Name")	=>	'bsname',
				__("City")	=>	'bscity',
				__("State")	=>	'bsstate',
				__("ETIN")	=>	'bsetin'
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
			$w[$r['bsname'].' ('.$r['bscity'].', '.$r['bsstate'].')'] = $r['id'];
		}

		// Pass widget parameters
		return html_form::select_widget($varname, $w, $_options);
	} // end method widget

} // end class BillingService

register_module ("BillingService");

?>
