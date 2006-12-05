<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class AppointmentTemplates extends SupportModule {

	var $MODULE_NAME    = "Appointment Templates";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "c5a9345d-ccb5-476a-83ed-59b4c1f21aad";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Appointment Template";
	var $table_name     = "appttemplate";

	var $widget_hash    = "##atname## (##atduration## min)";

	var $variables = array (
		'atname',
		'atduration',
		'atequipment',
		'atcolor'
	);

	public function __construct ( ) {
		// For i18n: __("Appointment Templates")

		$this->rpc_field_map = array (
			'name' => 'atname',
			'duration' => 'atduration',	
			'color' => 'atcolor'
		);

		$this->list_view = array (
			__("Template") => 'atname',
			__("Duration") => 'atduration'
		);

			// Run constructor
		parent::__construct( );
	} // end constructor

	// Method: get_description
	//
	//	Get the description of the specified appointment template.
	//
	// Parameters:
	//
	//	$id - Record id field for template
	//
	// Returns:
	//
	//	Description of specified template.
	//
	public function get_description ( $id ) {
		$r = $GLOBALS['sql']->get_link( $this->table_name, $id );
		return $r['atname'];
	} // end method get_description

	// Method: get_duration
	//
	//	Get the duration of the specified appointment template.
	//
	// Parameters:
	//
	//	$id - Record id field for template
	//
	// Returns:
	//
	//	Duration of specified template.
	//
	public function get_duration ( $id ) {
		$r = $GLOBALS['sql']->get_link( $this->table_name, $id );
		return $r['atduration'] + 0;
	} // end method get_duration

	// Method: get_rooms
	//
	//	Get rooms which are acceptable with the current
	//	template requirements.
	//
	// Parameters:
	//
	//	$id - Record id field for template
	//
	// Returns:
	//
	//	Array of acceptable ids, or false if there is no
	//	limit on rooms.
	//
	public function get_rooms ( $id ) {
		$t = $GLOBALS['sql']->get_link( $this->table_name, $id );
		if (!$t['atequipment']) { return false; }

		// Cache rooms
		$res = $GLOBALS['sql']->queryAll( "SELECT * FROM room" );
		foreach ( $res AS $my_r ) {
			$rooms[$my_r['id']] = explode(',', $my_r['roomequipment']);
		}
		
		$e = explode(',', $t['atequipment']);
		foreach ($e AS $this_e) {
			if (is_array($rooms)) {
				foreach ($rooms AS $k => $this_room) {
					$found = false;
					if ($this_room == $this_e) {
						$found = true;
					}
					foreach ($this_room AS $check) {
						if ($check == $this_e) {
							$found = true;
						}
					}
					if (!$found) { unset($rooms[$k]); }
				}
			} else {
				// What in the hell do you do when there is
				// nothing that matches the criteria??!?!?!?
				// FIXME FIXME FIXME HACK FIXME FIXME FIXME
				return false;
			}
		}
		if (is_array($rooms)) {
			foreach ($rooms AS $k => $v) {
				$return[] = $k;
			}
			return $return;
		} else {
			// Ridiculous restrictions?
			return false;
		}
	} // end method get_rooms

	function _update ( ) {
		$version = freemed::module_version ( $this->MODULE_NAME );

		// Version 0.1.1
		//
		//	Add colors
		//
		if (!version_check($version, '0.1.1')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN atcolor CHAR(7) AFTER atequipment');
			$GLOBALS['sql']->query('UPDATE '.$this->table_name.' '.
				'SET atcolor=\'\' WHERE id>0');
		} // end version 0.1.1
	} // end method _update

} // end class AppointmentTemplates

register_module ("AppointmentTemplates");

?>
