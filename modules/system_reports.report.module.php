<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.ReportsModule');

class SystemReports extends ReportsModule {

	var $MODULE_NAME = "System Reports";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	function display () {
		$agata = CreateObject('_FreeMED.Agata');
		$meta = $agata->_ReadMetaInformation($_REQUEST['report'].'.report');
		// Handle split dates if we have them:
		if (isset($_REQUEST['by_y'])) {
			$by = freemed::date_assemble('by');
		} else {
			$by = $_REQUEST['by'];
		}
		
		// Serve it up
		switch ($meta['type']) {
			case 'merge':
			$agata->CreateReport(
				'Merge',
				$_REQUEST['report'],
				$meta['name'],
				array('##BY##' => addslashes($by))
			);
			switch ($_REQUEST['format']) {
				case 'ps':
				$agata->ServeReport();
				break;
			
				case 'pdf': default:
				$agata->ServeMergeAsPDF();
				break;
			}
			break;
			
			case 'standard': default:
			$agata->CreateReport(
				$_REQUEST['format'],
				$_REQUEST['report'],
				$meta['name'],
				array('##BY##' => addslashes($by))
			);
			$agata->ServeReport();
			break;
		}
	} // end method display

	function view() { 
		global $display_buffer;
	
		// Get list from Agata class
		$agata = CreateObject('_FreeMED.Agata');
		$reports = $agata->GetReports();
		$display_buffer .= "<table width=\"90%\" border=\"0\" ".
			"cellspacing=\"0\">\n<tr class=\"DataHead\">\n".
			"<td>".__("Report")."</td>\n".
			"<td>".__("For")."</td>\n".
			"<td>".__("Format")."</td>\n".
			"<td>&nbsp;</td>\n".
			"</tr>\n";
		foreach ($reports AS $k => $v) {
			$display_buffer .= "<tr><td>".prepare($v['name']).
				"</td>\n".
				"<form method=\"post\" target=\"_report\">\n".
				"<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\">\n".
				"<input type=\"hidden\" name=\"action\" value=\"display\">\n".
				"<input type=\"hidden\" name=\"report\" value=\"".prepare($k)."\">\n".
				"<td>";
			switch ($v['by']) {
				case 'date':
				$display_buffer .= fm_date_entry('by');
				break;

				case 'provider':
				$display_buffer .= module_function(
					'ProviderModule',
					'widget',
					array (
						'by',
						'phyref=\'no\''
					)
				);
				break;
				
				default: $display_buffer .= '--'; break;
			}
			$display_buffer .= "</td><td>\n";
			switch ($v['type']) {
				case 'merge':
				$display_buffer .= html_form::select_widget(
					'format',
					array(
						'PDF' => 'pdf',
						'Postscript' => 'ps'
					)
				); break;
				
				case 'standard': default:
				$display_buffer .= html_form::select_widget(
					'format',
					array(
						'HTML' => 'Html',
						'CSV'  => 'Csv',
						'PDF'  => 'Pdf',
						'Postscript' => 'Ps',
						'Plain Text' => 'Txt'
					)
				);
				break;
			}
			$display_buffer .= "</td><td>".
				"<input type=\"submit\" value=\"".__("Get Report")."\"/></form></tr>\n";
		}
		$display_buffer .= "</table>\n";
	}

} // end class SystemReports

register_module ("SystemReports");

?>
