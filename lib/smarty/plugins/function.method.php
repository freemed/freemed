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

function smarty_function_method ( $params, &$smarty ) {
	if ( !isset ( $params['namespace'] ) ) { $smarty->error ( "Namespace not specified" ); }
	if ( count ( $params['param'] ) > 0 ) {
		return call_user_func_array ( 'CallMethod', array ( $params['namespace'], $params['param'] ) );
	} else {
		return CallMethod ( $params['namespace'] );
	}
} // end function smarty_function_method

?>
