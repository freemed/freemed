<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

class Holiday extends SupportModule {

	var $MODULE_NAME = "Holiday";
	var $MODULE_VERSION = "0.9.0";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "7f0cb966-9335-4088-8bef-d117b4248a46";  

	var $PACKAGE_MINIMUM_VERSION = '0.7.2';

	var $table_name = "holiday";
	var $acl_category = 'emr';

	var $variables = array (	
	    'hol_date',                      
            'hol_descrip'
	);	

	public function __construct ( ) {
		// DEBUG TESTING: $this->defeat_acl = true;

		// Call parent constructor
		parent::__construct ( );
	} // end constructor PatientModule

	protected function add_pre ( &$data ) {
	} // end method add_pre
	
	
	
	public function CheckForHoliday ($date) {
		$query = "SELECT COUNT(*) as c from holiday where hol_date=".$GLOBALS['sql']->quote( $date );
		$result = $GLOBALS['sql']->queryAll( $query );
		return $result[0]['c']+0;
	} // end method GetAll
	
	public function GetHolidayDesc ($date) {
		$query = "select h.hol_descrip from holiday h where h.hol_date = ".$GLOBALS['sql']->quote( $date );
		$result = $GLOBALS['sql']->queryRow( $query );
		return $result['hol_descrip'];
	} // end method GetAll
}

register_module('Holiday');

?>
