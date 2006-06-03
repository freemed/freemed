<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.ReportsModule');

class SystemReports extends ReportsModule {

	var $MODULE_NAME = "System Reports";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;
	var $MODULE_DESCRIPTION = "Interface to the Agata 7.x reporting engine.";

	var $PACKAGE_MINIMUM_VERSION = '0.8.2';
	// __("System Reports")

	function display () {
		// Import parameters
		foreach ($_GET AS $k => $v) {
			switch ($k) {
				case 'module':
				case 'action':
				case 'format':
				case 'report':
				case 'submit_action';
					break;
	
				default:
					$param['$'.$k] = $v;
					break;
			}
		}

		$a = CreateObject('_FreeMED.Agata7');
		if (is_array($param)) {
			$a->CreateReport( $_REQUEST['format'], $_REQUEST['report'], $param );
		} else {
			$a->CreateReport( $_REQUEST['format'], $_REQUEST['report'] );
		}

		$a->ServeReport();
	} // end method display

	function form ( ) {
		global $display_buffer;
		$a = CreateObject('_FreeMED.Agata7');
		$f = $a->CreateForm($_REQUEST['report']);
		if (is_object($f)) { $display_buffer .= $f->toHtml(); }
		return true;
	} // end method form

	function view() { 
		global $display_buffer;

		if ($_REQUEST['action'] == 'form') { return $this->form(); }
		if ($_REQUEST['action'] == 'view') { return $this->display(); }
	
		// Get list from Agata class
		$agata = CreateObject('_FreeMED.Agata7');
		$reports = $agata->GetReports();
		$display_buffer .= "<table width=\"90%\" border=\"0\" ".
			"cellspacing=\"0\">\n<tr class=\"DataHead\">\n".
			"<td>".__("Report")."</td>\n".
			"<td>".__("Description")."</td>\n".
			"<td>&nbsp;</td>\n".
			"</tr>\n";
		foreach ($reports AS $k => $v) {
			$display_buffer .= "<tr><td><b>".prepare($v['Title'])."</b></td>\n".
				"<td><i>".prepare($v['Description'])."</i></td>\n".
				"<form method=\"post\">\n".
				"<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\">\n".
				"<input type=\"hidden\" name=\"action\" value=\"form\">\n".
				"<input type=\"hidden\" name=\"report\" value=\"".prepare($k)."\">\n";
			$display_buffer .= "</td><td>".
				"<input type=\"submit\" value=\"".__("Get Report")."\"/></form></tr>\n";
		}
		$display_buffer .= "</table>\n";
	}

} // end class SystemReports

register_module ("SystemReports");

?>
