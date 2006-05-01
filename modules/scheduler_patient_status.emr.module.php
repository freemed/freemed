<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class SchedulerPatientStatus extends EMRModule {

	var $MODULE_NAME = "Scheduler Patient Status";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Scheduler Patient Status";
	var $table_name = 'scheduler_status';
	var $patient_field = 'cspatient';

	function SchedulerPatientStatus () {
		$this->table_definition = array (
			'csstamp' => SQL__TIMESTAMP(14),
			'cspatient' => SQL__INT_UNSIGNED(0),
			'csappt' => SQL__INT_UNSIGNED(0),
			'csnote' => SQL__VARCHAR(250),
			'csstatus' => SQL__INT_UNSIGNED(0),
			'csuser' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);

		global $this_user;
		if (!is_object($this_user)) { $this_user = CreateObject('_FreeMED.User'); }
		$this->variables = array (
			'csstamp' => SQL__NOW,
			'cspatient' => $_REQUEST['patient'],
			'csappt',
			'csnote',
			'csstatus',
			'csuser' => $this_user->user_number
		);

		$this->summary_vars = array (
			__("Date/Time") => 'csstamp',
			__("Status") => 'sname',
			__("Note") => 'csnote'
		);
		$this->summary_options = SUMMARY_VIEW | SUMMARY_DELETE | SUMMARY_NOANNOTATE;
		$this->summary_query_link = array ( 'csstatus' => 'schedulerstatustype' );

		// call parent constructor
		$this->EMRModule();
	} // end constructor SchedulerPatientStatus

	function addform () {
		// Display parent form
		$this->form();

		// Display all past annotations, if present
		global $display_buffer;
		$q = "SELECT ".
			"DATE_FORMAT(atimestamp, '%d %M %Y %H:%i') AS ts,".
				"annotation,auser ".
			"FROM ".$this->table_name." ".
			"WHERE aid='".addslashes($_REQUEST['aid'])."' AND ".
			"atable='".addslashes($_REQUEST['atable'])."' AND ".
			"apatient='".addslashes($_REQUEST['patient'])."' ".
			"ORDER BY atimestamp DESC";
		$a = $GLOBALS['sql']->query($q);
		while ($r = $GLOBALS['sql']->fetch_array($a)) {
			$display_buffer .=
			"<div class=\"thinbox_noscroll\" width=\"60%\">".
			"<i>".$r['ts']."</i> ".__("by")." <b>".freemed::get_link_field($r['auser'], 'user', 'username')."</b>".
			"<br/>\n".
			prepare($r['annotation'])."</div>\n";
		}
	}

	// Keep people from trying to modify these ...
	function modform() { $this->view(); }
	function mod() { $this->add(); }

	function form_table ( ) {
		return array (
			__("Appointment") =>
			module_function('schedulertable', 'widget', array('csappt', "calpatient='".addslashes($_REQUEST['patient'])."'")),
			__("Status") =>
			module_function('schedulerstatustype', 'widget', array('csstatus')),
			__("Note") =>
			html_form::text_area('csnote')
		);
	} // end method form_table

	function view ( ) {
		global $sql; global $display_buffer; global $patient;

		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT DATE_FORMAT(csstamp, '%d %M %Y %H:%i') AS ts, ".
				"csuser, csnote, id FROM ".$this->table_name." ".
				"WHERE cspatient='".addslashes($patient)."' ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY csstamp DESC"),
			$this->page_name,
			array(
				__("Date") => 'ts',
				__("Status") => 'csstatus',
				__("User") => 'csuser',
				__("Note") => 'csnote'
			),
			array('', __("Not specified")), //blanks
			array(
				"",
				"schedulerstatustype" => "sname",
				"user" => "username",
				""
			)
		);
	} // end method view

	// Method: getPatientStatus
	//
	//	Get current patient status.
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	//	$appt - Appointment id
	//
	// Returns:
	//
	//	Numeric id describing current status
	//
	function getPatientStatus ( $patient, $appt ) {
		$q = "SELECT * FROM ".$this->table_name." WHERE cspatient = '".addslashes($patient)."' AND csappt = '".addslashes($appt)."' ORDER BY csstamp DESC LIMIT 1";
		$res = $GLOBALS['sql']->query($q);
		if (!$GLOBALS['sql']->results($res)) {
			return false;
		}
		$r = $GLOBALS['sql']->fetch_array($res);
		return $r['csstatus'];
	} // end method getPatientStatus

	// Update
	function _update ( ) {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		//if (!version_check($version, '0.2')) {
		//}	
	} // end method _update

} // end class SchedulerPatientStatus

register_module ("SchedulerPatientStatus");

?>
