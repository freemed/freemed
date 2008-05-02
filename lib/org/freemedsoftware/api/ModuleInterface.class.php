<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.ModuleInterface
//
//	"Factory" type of interface to module functions to greatly
//	simplify RPC.
//
class ModuleInterface {

	public function __construct ( ) { }

	// Method: ModuleAddMethod
	//
	// Parameters:
	//
	//	$module - Module name
	//
	//	$data - Associative array of data to be added.
	//
	// Returns:
	//
	//	New id created.
	//
	public function ModuleAddMethod ( $module, $data ) {
		return module_function( $module, 'add', array ( $data ) );		
	} // end method ModuleAddMethod

	// Method: ModuleDeleteMethod
	//
	// Parameters:
	//
	//	$module - Module name
	//
	//	$id - Id to be removed
	//
	public function ModuleDeleteMethod ( $module, $id ) {
		return module_function( $module, 'del', array ( $id ) );
	} // end method ModuleDeleteMethod

	// Method: ModuleModifyMethod
	//
	// Parameters:
	//
	//	$module - Module name
	//
	//	$data - Associative array of data to be modified.
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function ModuleModifyMethod ( $module, $data ) {
		return module_function( $module, 'mod', array ( $data ) );
	} // end method ModuleModifyMethod

} // end class ModuleInterface

?>
