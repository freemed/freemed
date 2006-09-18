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

if ($_SERVER['PHP_SELF'] == basename(__FILE__)) {
	die("Bootstrap cannot be run directly.\n");
}

ini_set('include_path', dirname(dirname(__FILE__)).':'.ini_get('include_path'));

include_once ( 'lib/freemed.php' );

function t ( $test, $output ) {
	print ( !$_SERVER['argv'] ? "<b>$test</b>" : " [[ $test ]] : " );
	print_r($output);
	print ( !$_SERVER['argv'] ? "<br/>\n" : "\n" );
}

// Cache the admin user for these tests...
print ( !$_SERVER['argv'] ? "<b>Loading user #1 (admin)</b><br/>" : " * Loading user #1 (admin)\n" );
$this_user = CreateObject('org.freemedsoftware.core.User', 1);

?>
