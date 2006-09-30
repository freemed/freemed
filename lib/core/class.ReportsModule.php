<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

LoadObjectDependency('org.freemedsoftware.core.BaseModule');

class ReportsModule extends BaseModule {

	var $CATEGORY_NAME = "Reports";
	var $CATEGORY_VERSION = "0.2";

	// vars to be passed from child modules
	var $form_vars;

	// user
	var $this_user;

	public function __construct ( ) {
		parent::__construct();
	} // end constructor

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) 
		{
			trigger_error("Module not Defined", E_ERROR);
		}
		// FIXME!!: check access to facility
		//if (!freemed::check_access_for_patient($patient)) return false;
		return true;
	} // end function check_vars

	// function create_table
	// - used to initially create SQL table
	public function create_table () {
		if (!isset($this->table_definition)) return false;
		$query = $GLOBALS['sql']->create_table_query(
			$this->table_name,
			$this->table_definition,
			( is_array($this->table_keys) ?
				array_merge(array("id"), $this->table_keys) :
				array("id")
			)
		);
		$result = $GLOBALS['sql']->query ($query);
		return !empty($result);
	} // end function create_table

} // end class ReportsModule

?>
