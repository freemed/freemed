<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.ReportsModule');

class GraphModule extends ReportsModule {
	// contructor method
	function GraphModule () {
		// Call parent constructor
		$this->ReportsModule();
	} // end function GraphModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module))
		{
			trigger_error("No Module Defined", E_ERROR);
		}
		return true;
	} // end function check_vars

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $action, $submit;

		// Handle Cancel button
		if ($submit == __("Cancel")) {
			global $refresh; $refresh = 'reports.php';
			return false;
		}

		switch ($action) {
			case "display":
				$this->display();
				break;
			case "image":
				$this->image();
				break;
			case "view":
			default:
				$this->view();
				break;
		} // end switch action
	} // end function main

	// ********************** MODULE SPECIFIC ACTIONS *********************
	function header() {
		if ($_REQUEST['graphmode']) {
			// don't display the box top
			freemed::connect();
//------HIPAA Logging
// Dont see a need here...

			return;
		}
		BaseModule::header();
	} // end function header

	function footer() {
		global $display_buffer;

		// dont display the bottom
		if ($_REQUEST['graphmode']) {
			return;
		} else {
			template_display();
		}
	} // end function footer

	function _view () {
		global $display_buffer, $start_dt, $end_dt;

		if (!isset($start_dt)) {
			// Default to one year ago
			list ($y, $m, $d) = explode("-", date("Y-m-d"));
			$start_dt = date("Y-m-d", mktime(0,0,0,$m,$d,$y-1));
		}

		if (!isset($end_dt)) {
			// Default to current date
			$end_dt = date("Y-m-d");
		}

		$display_buffer .= $this->GetGraphOptions(
			$this->graph_text,
			$this->graph_opts
		);
	}
	function view() { $this->_view(); }

	function AssembleURL($opts='') {
		$__req = array_merge($_GET, $_POST);
		if (is_array($opts)) {
			foreach ($opts AS $k => $v) {
				if (is_integer($k)) {
					global $v;
					$__req["$v"] = ${$v};
				} else {
					$__req["$k"] = $v;
				}
			}
		}

		// Go through each one and make it all proper-like
		foreach ($__req AS $k => $v) {
			$___req[] = $k."=".urlencode($v);
		}
		
		// Form url
		return $this->page_name."?".join('&', $___req);
	}

	function GetGraphOptions($title, $_opts=array()) {
		global $action, $module, $start_dt, $end_dt;

		if (is_array($_opts)) {
			$opts = $_opts;
		} else {
			$opts = array();
		}

		// Add defaults
		$opts = array_merge(
			array(
			__("Start Date") =>
			fm_date_entry("start_dt"),

			__("End Date") => 
			fm_date_entry("end_dt")
			), $opts);

		$buffer = "
		<div ALIGN=\"CENTER\">
		<b>".$title."</b>
		</div>

	        <form ACTION=\"".$this->page_name."\" METHOD=\"POST\">
        	<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"display\"/>
        	<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>

		<div align=\"CENTER\">
		".html_form::form_table($opts)."
		</div>

		<div align=\"CENTER\">
		<input class=\"button\" TYPE=\"SUBMIT\" NAME=\"submit\"
			value=\"".__("Graph")."\"/>
		<input class=\"button\" type=\"SUBMIT\" name=\"submit\"
			value=\"".__("Cancel")."\"/>
		</form>
		</div>
        ";
		return $buffer;

	} // end function GetGraphOptions

} // end class GraphModule

?>
