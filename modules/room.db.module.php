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

class RoomMaintenance extends SupportModule {

	var $MODULE_NAME = "Room Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "b3992bbd-4920-4243-bc5f-97f333edbc44";

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name = "Room";
	var $table_name = "room";
	var $order_field = 'roomname';

	var $variables  = array (
		"roomname",
		"roompos",
		"roomdescrip",
		"roomequipment",
		"roomdefphy",
		"roomsurgery",
		"roombooking",
		"roomipaddr"
	);

	public function __construct ( ) {
		// For i18n: __("Room Maintenance")

		$this->list_view = array (
			__("Name")		=>	"roomname",
			__("Description")	=>	"roomdescrip"
		);

		// Run constructor
		parent::__construct();
	} // end constructor RoomMaintenance

} // end class RoomMaintenance

register_module ("RoomMaintenance");

?>
