<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.GraphInterface
//
class GraphInterface {

	public function __construct ( ) { }

	// Method: GetAvailableGraphs
	//
	//	Retrieve list of installed GraphingModule handler modules.
	//
	// Returns:
	//
	//	Hash of graph modules available.
	//
	public function GetAvailableGraphs ( ) {
		$h = freemed::module_handler ( 'GraphingModule' );
		$req = $GLOBALS['sql']->queryAll( "SELECT module_name AS v, module_class AS k FROM modules WHERE FIND_IN_SET( LOWER(module_class), LOWER(".$GLOBALS['sql']->quote( join(',', $h ) ).") ) ");
		foreach ( $req AS $r ) {
			$result[$r['k']] = $r['v'];
		}
		return $result;
	} // end method GetAvailableGraphs

} // end class GraphInterface

?>
