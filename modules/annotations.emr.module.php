<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class Annotations extends EMRModule {

	var $MODULE_NAME = "Annotations";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name = "Annotations";
	var $table_name = 'annotations';
	var $patient_field = 'apatient';

	function Annotations () {
		$this->table_definition = array (
			'atimestamp' => SQL__TIMESTAMP(14),
			'apatient' => SQL__INT_UNSIGNED(0),
			'amodule' => SQL__VARCHAR(150),
			'atable' => SQL__VARCHAR(150),
			'aid' => SQL__INT_UNSIGNED(0),
			'auser' => SQL__INT_UNSIGNED(0),
			'annotation' => SQL__TEXT,
			'id' => SQL__SERIAL
		);

		global $this_user;
		if (!is_object($this_user)) { $this_user = CreateObject('_FreeMED.User'); }
		$this->variables = array (
			'atimestamp' => SQL__NOW,
			'apatient' => $_REQUEST['patient'],
			'amodule',
			'atable',
			'aid',
			'auser' => $_SESSION['authdata']['user'],
			'annotation'
		);

		$this->summary_vars = array (
			__("Date/Time") => 'atimestamp',
			__("Table") => 'atable',
			" " => 'annotation'
		);
		$this->summary_options = SUMMARY_VIEW | SUMMARY_DELETE |
			SUMMARY_NOANNOTATE;

		$this->form_hidden = array (
			'amodule',
			'atable',
			'aid'
		);

		// call parent constructor
		$this->EMRModule();
	} // end constructor Annotations

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
			__("Annotation") =>
			html_form::text_area('annotation')
		);
	} // end method form_table

	function view ( ) {
		global $sql; global $display_buffer; global $patient;

		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT DATE_FORMAT(atimestamp, '%d %M %Y %H:%i') AS ts, ".
				"amodule, auser, annotation, id FROM ".$this->table_name." ".
				"WHERE apatient='".addslashes($patient)."' ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY atimestamp DESC"),
			$this->page_name,
			array(
				__("Date") => 'ts',
				__("Module") => 'amodule',
				__("User") => 'auser',
				__("Annotation") => 'annotation'
			),
			array('', __("Not specified")), //blanks
			array(
				"",
				"",
				"user" => "username",
				""
			)
		);
	} // end method view

	// Method: createAnnotation
	//
	//	Create an annotation.
	//
	// Parameters:
	//
	//	$module - Module to create annotation in
	//
	//	$id - ID number
	//
	//	$text - Text to annotate
	//
	//	$patient - (optional) Patient number
	//
	function createAnnotation ($module, $id, $text, $patient = NULL) {
		$q = $GLOBALS['sql']->insert_query(
			$this->table_name,
			array(
				'amodule' => $module,
				'aid' => $id,
				'atimestamp' => SQL__NOW,
				'apatient' => ( $patient ? $patient : $_REQUEST['patient'] ),
				'atable' => $this->_resolve_module_to_table($module),
				'auser' => $_SESSION['authdata']['user'],
				'annotation' => $text
			)
		);
		$res = $GLOBALS['sql']->query($q);
	} // end method createAnnotation

	function _resolve_module_to_table ( $module ) {
		$cache = freemed::module_cache();
		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] AS $v) {
			if (strtolower($v['MODULE_CLASS']) == strtolower($module)) {
				return $v['META_INFORMATION']['table_name'];
			}
		}
		trigger_error(__("Could not resolve table name!"), E_USER_ERROR);
	} // end method _resolve_module_to_table

	// Method: getAnnotations
	//
	//	Get annotations, if present.
	//
	// Parameters:
	//
	//	$module - Module to examine for annotations
	//
	//	$id - ID number
	//
	// Returns:
	//
	//	Array of annotations, otherwise false.
	//
	function getAnnotations ($module, $id) {
		$q = "SELECT * FROM ".$this->table_name." ".
			"WHERE amodule = '".addslashes($module)."' ".
			"AND aid = '".addslashes($id)."'";
		$res = $GLOBALS['sql']->query($q);
		if (!$GLOBALS['sql']->results($res)) {
			return false;
		}
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$a[] = $r;
		}
		return $a;
	} // end method getAnnotations

	// Method: outputAnnotations
	//
	//	Produce tooltip-friendly annotations from the output
	//	of <getAnnotations>.
	//
	// Parameters:
	//
	//	$annotations - Array of annotations
	//
	// Returns:
	//
	//	XHTML-formatted annotation string
	//
	function outputAnnotations ( $annotations ) {
		foreach ($annotations AS $a) {
			$user = freemed::get_link_rec($a['auser'], 'user');
			$p = str_replace("\r", '', stripslashes($a['annotation']));
			$b[] .= "<b>".stripslashes($user['userdescrip'])."</b>\n".
				"<i>".freemed::sql2date($a['atimestamp'])."</i>\n".
				$p;
		}
		return join("\n\n", $b);
	} // end method outputAnnotations

	// Method: prepareAnnotation
	//
	//	Prepare an annotation for being embedded in a Javascript
	//	string.
	//
	// Parameters:
	//
	//	$a - Annotation text.
	//
	// Returns:
	//
	//	Javascript string formatted text.
	//
	function prepareAnnotation ( $a ) {
		$b = $a;
		$b = str_replace("'", '\\\'', $b);
		$b = str_replace("\"", '\\"', $b);
		$b = str_replace("\n", '<br/>\n', $b);
		$b = htmlentities($b);
		return $b;
	} // end method prepareAnnotation

	// Update
	function _update ( ) {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		//if (!version_check($version, '0.2')) {
		//}	
	} // end method _update

} // end class Annotations

register_module ("Annotations");

?>
