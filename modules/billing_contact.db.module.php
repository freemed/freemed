<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.MaintenanceModule');

class BillingContact extends MaintenanceModule {

	var $MODULE_NAME = "Billing Contact";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Billing Contact";
	var $table_name = "bcontact";
	var $order_field = "bclname,bcfname,bcstate,bczip";

	var $variables		= array (
		'bcfname',
		'bcmname',
		'bclname',
		'bcaddr',
		'bccity',
		'bcstate',
		'bczip',
		'bcphone'
	);

	function BillingContact () {
		// Table definition
		$this->table_definition = array (
			'bcfname' => SQL__NOT_NULL(SQL__VARCHAR(50)),
			'bcmname' => SQL__VARCHAR(50),
			'bclname' => SQL__NOT_NULL(SQL__VARCHAR(50)),
			'bcaddr' => SQL__VARCHAR(45),
			'bccity' => SQL__VARCHAR(30),
			'bcstate' => SQL__CHAR(3),
			'bczip' => SQL__VARCHAR(10),
			'bcphone' => SQL__VARCHAR(16),
			'id' => SQL__SERIAL
		);
	
		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor BillingContact

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
			__("Contact Name (First Middle Last)") =>
			html_form::text_widget('bcfname', 50).' '.
			html_form::text_widget('bcmname', 50).' '.
			html_form::text_widget('bclname', 50),

			__("Address") =>
			html_form::text_widget('bcaddr', 45),

			__("City, State Zip") =>
			html_form::text_widget('bccity', 30).', '.
			html_form::state_pulldown('bcstate').' '.
			html_form::text_widget('bczip', 10)
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
				__("Last Name")		=>	'bclname',
				__("First Name")	=>	'bcfname',
				__("City")		=>	'bccity',
				__("State"	)	=>	'bcstate'
			),
			array ("", "", ""),
			array("","",""),
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
			$w[$r['bclname'].', '.$r['bcfname'].' ('.$r['bccity'].', '.$r['bcstate'].')'] = $r['id'];
		}

		// Pass widget parameters
		return html_form::select_widget($varname, $w, $_options);
	} // end method widget

} // end class BillingContact

register_module ("BillingContact");

?>
