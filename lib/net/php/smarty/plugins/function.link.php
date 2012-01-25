<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

function smarty_function_link ( $params, &$smarty ) {
	static $cache;

	if ( !isset ( $params['table'] ) ) { $smarty->trigger_error ( "Table not specified" ); }
	if ( !isset ( $params['link'] ) ) { $smarty->trigger_error ( "Link not specified" ); }
	if ( !isset ( $params['field'] ) ) { $smarty->trigger_error ( "Field not specified" ); }

	// Check for cache
	if ( !isset ( $cache[$params['table']][$params['link']] ) ) {
		$cache[$params['table']][$params['link']] = $GLOBALS['sql']->get_link( $params['table'], $params['link'] );
	}

	if ( isset ( $params['var'] ) ) {
		$smarty->assign( $params['var'], $cache[$params['table']][$params['link']][$params['field']] );
	} else {
		return $cache[$params['table']][$params['link']][$params['field']];
	}
} // end function smarty_function_link

?>
