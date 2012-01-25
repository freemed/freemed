<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

LoadObjectDependency('org.freemedsoftware.core.BaseModule');

class CDRWBackup extends BaseModule {

	var $MODULE_NAME = "CD/RW Backup";
	var $MODULE_VERSION = "0.1";
	var $ICON = "img/cdrw_backup.gif";

	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "11d64a77-2eca-4348-8061-bd59ab360fb1";

	public function __construct ( ) {
		// __("CD/RW Backup")

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
		parent::__construct ( );
	} // end constructor CDRWBackup

	// Method: RunBackup
	//
	//	Execute the backup routine.
	//
	// Returns:
	//
	//	Textual output from the backup commands.
	//
	public function RunBackup( ) {
		$pwd = `pwd`;
		$dev = escapeshellarg( freemed::config_value('cdrw_device') );
		$driver = escapeshellarg( freemed::config_value('cdrw_driver') );
		$speed = escapeshellarg( freemed::config_value('cdrw_speed') );
		$output = `/usr/share/freemed/scripts/cdrw_backup.sh $dev $driver $speed`;
		//print "/usr/share/freemed/scripts/cdrw_backup.sh $dev | $driver | $speed\n";
		return $output;
	} // end method action

	// Picklist callbacks

	// Method: device_list
	//
	// Returns:
	//
	//	Hash containing available CD/RW devices.
	//
	public function device_list ( ) {
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

	// Method: driver_list
	//
	// Returns:
	//
	//	Hash of available drivers.
	//
	public function driver_list ( ) {
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
