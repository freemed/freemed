<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class Notifications extends EMRModule {

	var $MODULE_NAME = "Notifications";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name = "Notifications";
	var $table_name = 'notification';
	var $patient_field = 'npatient';
	var $order_field = 'ntarget';

	function Notifications () {
		$this->table_definition = array (
			'noriginal' => SQL__DATE,
			'ntarget' => SQL__DATE,
			'ndescrip' => SQL__TEXT,
			'nuser' => SQL__INT_UNSIGNED(0),
			'nfor' => SQL__INT_UNSIGNED(0),
			'npatient' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);

		global $this_user;
		if (!is_object($this_user)) { $this_user = CreateObject('_FreeMED.User'); }

		$this->variables = array (
			'noriginal' => date('Y-m-d'),
			'ntarget',
			'ndescrip',
			'nuser' => $this_user->user_number,
			'nfor',
			'npatient'
		);

		$this->summary_vars = array (
			__("Date") => 'ntarget',
			__("User") => 'nfor:user'
		);
		$this->summary_options = SUMMARY_DELETE;

		// Set up a tickler, so we can send messages to the user
		$this->_SetHandler('Tickler', 'notify_user');

		// call parent constructor
		$this->EMRModule();
	} // end constructor AllergiesModule

	function form_table ( ) {
		$scheduler = CreateObject('FreeMED.Scheduler');
		if (!$_REQUEST['nfor']) {
			global $this_user;
			if (!is_object($this_user)) { $this_user = CreateObject('FreeMED.User'); }
			global $nfor;
			$_REQUEST['nfor'] = $nfor = $this_user->user_number;
		}
		return array (
			__("Target Date") =>
			html_form::select_widget(
				'ntarget',
				array (
					__("1 Week") => $scheduler->date_add(date('Y-m-d'), 7),
					__("2 Weeks") => $scheduler->date_add(date('Y-m-d'), 14),
					__("1 Month") => $scheduler->date_add(date('Y-m-d'), 28),
					__("2 Months") => $scheduler->date_add(date('Y-m-d'), 56),
					__("3 Months") => $scheduler->date_add(date('Y-m-d'), 84),
					__("1 Year") => $scheduler->date_add(date('Y-m-d'), 365)
				)
			),

			__("Target User") =>
			freemed_display_selectbox(
				$GLOBALS['sql']->query(
					"SELECT * FROM user ".
					"WHERE username != 'admin' ".
					"ORDER BY userdescrip"
					),
				"#username# (#userdescrip#)",
				"nfor"
			),

			__("Description") =>
			html_form::text_widget('ndescrip', array('length'=>250))
		);
	} // end method form_table

	function view ( ) {
		global $sql; global $display_buffer; global $patient;
		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT * FROM ".$this->table_name." ".
				"WHERE npatient='".addslashes($patient)."' ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY ".$this->order_field),
			$this->page_name,
			array(
				__("Date") => 'ntarget',
				__("User") => 'nuser'
			),
			array ( '', '' ), // blanks
			array ( '', 'user' => 'userdescrip' ) // x-ref
		);
	} // end method view

	function notify_user ( $params = NULL ) {
		// Only do this once a day
		$date = ( $params['date'] ? $params['date'] : date('Y-m-d') );
		if ($params['interval'] == 'daily') {
			$query = "SELECT * FROM ".$this->table_name." ".
				"WHERE ntarget='".addslashes($date)."'";
			$res = $GLOBALS['sql']->query($query);
			if (!$GLOBALS['sql']->results($res)) {
				return "Notifications: nothing to do";
			}
			$m = CreateObject('_FreeMED.Messages');
			$count = 0;
			while ($r = $GLOBALS['sql']->fetch_array($res)) {
				$count += 1;
				$m->send(array(
					'system' => true, // system message
					'user' => $r['nfor'],
					'patient' => $r['npatient'],
					'subject' => __("Notification"),
					'text' => $r['ndescrip'],
					'urgency' => 4
				));
			}
			return "Notifications: sent $count notifications";
		} // end checking for appropriate interval
		return "Notifications: nothing to do";
	} // end method notify_user

} // end class Notifications

register_module ("Notifications");

?>
