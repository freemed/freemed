<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

class AnesthesiologyCalendar extends SupportModule {

	var $MODULE_NAME = "Anesthesiology Scheduler";
	var $MODULE_VERSION = "0.6.0";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "fb8deddf-f81c-4ce6-b2fc-805760b1798c";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name = "anesth";

	public function __construct () {
		// __("Anesthesiology Scheduler")

		// Call parent constructor
		parent::__construct ( );
	} // end constructor AnesthTable

	function SingleBook ( $physician, $facility, $selected_date ) {
		// Determine if day is already booked for this person,
		// if so, change it.
		$old = $sql->queryRow("SELECT * FROM $this->table_name ".
			"WHERE andate='".addslashes($selected_date)."' AND ".
			"anphysician='".addslashes($physician)."'");
		if (is_array($old)) {
			$old = $GLOBALS['sql']->fetch_array($result);
			$result = $GLOBALS['sql']->query(
				$GLOBALS['sql']->update_query(
					$this->table_name,
					array (
						"anfacility" => $facility
					),
					array ( "id" => $old[id] )
				)
			);
		} else {
			$result = $GLOBALS['sql']->query(
				$GLOBALS['sql']->insert_query(
					$this->table_name,
					array (
						"andate" => $selected_date,
						"anphysician" => $physician,
						"anfacility" => $facility
					)
				)
			);
		}
	} // end method SingleBook

	function DeleteDate( $anesth, $date ) {
		$query = "DELETE FROM ".$this->table_name." ".
			"WHERE anphysician=".$GLOBALS['sql']->quote( $anesth )." AND ".
			"andate=".$GLOBALS['sql']->quote( $date );
		$result = $GLOBALS['sql']->query( $query );
	} // end method DeleteDate

	/*
	function bulk_book() {
		// Globalize
		foreach ($GLOBALS AS $k => $v) global ${$k};
		global $mark;

		// Insert a travel entry in the appropriate spot
		$query = $sql->insert_query(
			$this->table_name,
			array(
			)
		);
		$result = $sql->query($query);
	} // end method bulk_book
	*/

}

register_module('AnesthesiologyCalendar');

?>
