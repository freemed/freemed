<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
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

// Handle public methods for initialization
if ( ! file_exists( dirname(__FILE__).'/data/cache/healthy' ) ) {
	define( 'SKIP_SQL_INIT', true );
}

include_once ( 'lib/freemed.php' );

// Start logging
if ( ! defined ( 'SKIP_SQL_INIT' ) ) {
	unset ( $log );
	$log = freemed::log_object ( );
	$log->SystemLog( LOG__SECURITY, 'Provider', 'Relay', "Relay called with ".$_SERVER['PATH_INFO'] );
}

// Get provider from URL
unset ( $_provider ); unset ( $_method );
list ( $_garbage_, $_provider, $_method ) = explode ( '/', $_SERVER['PATH_INFO'] );
$_provider = ucfirst( strtolower ( $_provider ) );

// Sanity checking
if (!ereg("^[[:alpha:]]+$", $_provider )) {
	print "Hack attempt, dying ( '${_provider}' given ).";
	exit;
}

if ( !file_exists( dirname(__FILE__)."/lib/core/class.Relay_${_provider}.php" ) ) {
	print "Relay ${_provider} not present.";
	exit;
}

// Otherwise, instantiate
unset ( $obj );
//print "DEBUG : creating relay for method ${_method}<br/>\n";
$obj = CreateObject ( "org.freemedsoftware.core.Relay_${_provider}" );
//print "DEBUG : relay created<br/>\n";
print $obj->handle_request ( $_method );
//print "DEBUG: done handling request<br/>\n";

?>
