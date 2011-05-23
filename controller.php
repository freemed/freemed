<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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

include_once 'lib/freemed.php';

error_reporting ( );
set_error_handler("controller_standard_error_handler");

// Start logging
unset ( $log );
$log = freemed::log_object ( );
$log->SystemLog( LOG__SECURITY, 'Provider', 'Controller', "Controller called with ".$_SERVER['PATH_INFO'] );

// Get provider from URL
unset ( $layout );
unset ( $piece );
list ( $_garbage_, $layout, $piece ) = explode ('/', $_SERVER['PATH_INFO']);
$layout = ucfirst(strtolower($layout));

Header( 'Content-Type: text/html; charset=' . $GLOBALS['ISOSET'] );

// Sanity checking
if (!ereg("^[[:alpha:]]+$", $layout )) {
	print "Hack attempt, dying ( '${layout}' given ).";
	exit;
}

if (!file_exists(dirname(__FILE__)."/ui/".strtolower(${layout})."/controller/controller.${piece}.php")) {
	//print "Controller ${layout}::${piece} not present.";
	//exit;
	// Attempt to load default controller
	unset ( $controller );
	unset ( $controller_name );
	include_once(dirname(__FILE__)."/ui/".strtolower($layout)."/controller/controller.default.php");
	$controller_name = 'controller_default';
	$controller = new ${controller_name};
	if ( CallMethod ( 'org.freemedsoftware.public.Login.LoggedIn' ) ) {
		$controller->action ( $piece );
	} else {
		session_regenerate_id( );
		$controller->load_default ( );
	}
	exit;
}

unset ( $controller );
unset ( $controller_name );
include_once(dirname(__FILE__)."/ui/".strtolower($layout)."/controller/controller.${piece}.php");
$controller_name = 'controller_'.str_replace('.', '_', $piece);
$controller = new ${controller_name};
if ( CallMethod ( 'org.freemedsoftware.public.Login.LoggedIn' ) ) {
	$controller->action ( );
} else {
	$controller->load_default ( );
}

//----------------- Functions ----------------------------------------------

function controller_standard_error_handler ($no, $str, $file, $line, $context) {
	switch ($no) {
		case E_USER_ERROR:
		die("$file [$line] : $str");
		break;
	}
}

?>
