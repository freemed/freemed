<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

function smarty_function_method ( $params, &$smarty ) {
	if ( !isset ( $params['namespace'] ) ) { $smarty->trigger_error ( "Namespace not specified" ); }
	if ( isset ( $params['param'] ) ) {
		$x = call_user_func_array ( 'CallMethod', array ( $params['namespace'], $params['param'] ) );
	} elseif ( isset( $params['param0'] ) ) {
                // Use param1 ... paramN
		foreach ($params AS $k => $v) {
			if ( substr( $k, 0, 5 ) == 'param' ) {
				$key = substr( $k, 5, strlen($k) - 5 );
				if ( ( $key == '0' ) or ( ($key + 0) > 0 ) ) {
					$r[$key+0] = $v;
				}
			} // end if substr(param)
		} // end foreach

		ksort( $r );
		$x = call_user_func_array ( 'CallMethod', array_merge ( array ( $params['namespace'] ), $r ) );
	} else {

		$x = CallMethod ( $params['namespace'] );
	}

	// Handle optional variable return policy
	if ( $params['var'] ) {
		$smarty->assign( $params['var'], $x );
	} else {
		return $x;
	}
} // end function smarty_function_method

?>
