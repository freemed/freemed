<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.AdminModule');

class CDRWBackup extends AdminModule {

	var $MODULE_NAME = "CD/RW Backup";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";
	var $MODULE_HIDDEN = true;
	var $ICON = "img/cdrw_backup.gif";

	var $MODULE_FILE = __FILE__;

	function CDRWBackup ( ) {
		// Set administration handler
		$this->_SetHandler('AdminMenu', 'menu');

		// Form proper configuration information
		$this->_SetMetaInformation('global_config_vars', array(
			'cdrw_device', 'cdrw_driver', 'cdrw_speed'
		));
		$this->_SetMetaInformation('global_config', array(
			__("CD Recorder Device") =>
			'html_form::select_widget("cdrw_device", '.
				'module_function ( "CDRWBackup", '.
				'"device_list" ) )',
			__("CD Recorder Driver") =>
			'html_form::select_widget("cdrw_driver", '.
				'module_function ( "CDRWBackup", '.
				'"driver_list" ) )',
			__("CD Recorder Max Speed") =>
			'html_form::select_widget("cdrw_speed", '.
				'array ( '.
					'"1x" => "1",'.
					'"2x" => "2",'.
					'"4x" => "4",'.
					'"8x" => "8",'.
					'"12x" => "12",'.
					'"16x" => "16",'.
					'"24x" => "24",'.
					'"32x" => "32",'.
					'"40x" => "40",'.
					'"48x" => "48",'.
					'"52x" => "52"'.
				') )'
		));

		// Call parent constructor
		$this->AdminModule();
	} // end constructor CDRWBackup

	function menu ( ) {
		global $display_buffer;
		$display_buffer .= $this->action();
	} // end method menu

	function action ( ) {
		$buffer .= __("Creating backup")." ... ";
		$pwd = `pwd`;
		$dev = freemed::config_value('cdrw_device');
		$driver = freemed::config_value('cdrw_driver');
		$speed = freemed::config_value('cdrw_speed');
		$output = `/usr/share/freemed/scripts/cdrw_backup.sh $dev $driver $speed`;
		//print "/usr/share/freemed/scripts/cdrw_backup.sh $dev | $driver | $speed\n";
		$buffer .= "<b>".__("done")."</b><br/>\n";
		$buffer .= "<pre>".prepare($output, true)."</pre>\n";
		$buffer .= "<br/><br/>\n".
			"<div align=\"center\">\n".
			"<a href=\"admin.php\" class=\"button\"".
			">".__("Return to Administration Menu")."</a>\n";
		return $buffer;
	} // end method action

	// Picklist callbacks

	function device_list ( ) {
		global ${$varname};

		// Get devices from cdrecord -scanbus
		$_devices = `cdrecord -scanbus 2>&1 | grep CD`;

		$_devices = explode ("\n", $_devices);
		$stack = array();
		foreach ($_devices as $__garbage => $device) {
			$p = explode(" ",$device);
			
			// Check for "2,0,0\t200)" type device names
			$p[0] = trim($p[0]);
			if (strpos($p[0], "\t") > 1) {
				list ($_p, $__garbage) = explode ("\t", $p[0]);
			} else {
				$_p = $p[0];
			}

			$a = explode("'",$device);
			if (!empty($a[1])) {
				$stack[$a[1]." - ".$a[3]] = trim($_p);
			}
		}
		return $stack;
	} // end method device_list

	function driver_list ( ) {
		$_list = `cdrecord driver=help 2>&1`;
		$_list = explode("\n", $_list);
		$stack = array ( );
		// Skip first two lines (drek)
		for ($i=2; $i < count($_list); $i++) {
			$name = trim(substr($_list[$i], 0, 20));
			$desc = trim(substr($_list[$i], -(strlen($_list[$i]) - 20)));
			if (strlen($desc) > 30) {
				$desc = substr ($desc, 0, 30). "...";
			}
			$key = $name . " (" . $desc . ")";
			if (!empty($name)) { $stack[$key] = $name; }
		}
		return $stack;
	} // end method driver_list

} // end class CDRWBackup

register_module('CDRWBackup');

?>
