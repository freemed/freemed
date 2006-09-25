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

LoadObjectDependency('net.php.pear.MDB2');

// Class: org.freemedsoftware.core.FreemedDb
//
class FreemedDb extends MDB2 {

	private $db;

	public function __construct (  ) {
		PEAR::setErrorHandling ( PEAR_ERROR_RETURN );
		$uri = "mysqli://". DB_USER .":". DB_PASSWORD ."@". DB_HOST ."/". DB_NAME;
		$this->db =& MDB2::factory ( $uri );
		if ( PEAR::isError ( $this->db ) ) {
			trigger_error ( $this->db->getMessage(), E_USER_ERROR );
		}

		$this->db->setFetchMode( MDB2_FETCHMODE_ASSOC );
		$this->db->loadModule( 'Extended' );
		$this->db->loadModule( 'Manager' );
		$this->db->loadModule( 'Reverse' );
	}

	function __call ( $method, $param ) {
		if ( method_exists ( $this, $method ) ) {
			return call_user_func_array ( array ( $this, $method ), $param );
		} elseif ( method_exists ( $this->db, $method ) ) {
			return call_user_func_array ( array ( $this->db, $method ), $param );
		} else {
			trigger_error ( "Could not load method $method", E_USER_ERROR );
		}
	}

} // end class FreemedDb

?>
