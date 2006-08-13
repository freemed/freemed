<?php
	// $Id$
	// jeff@freemedsoftware.org

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
	$controller->action ( $piece );
	exit;
}

unset ( $controller );
unset ( $controller_name );
include_once(dirname(__FILE__)."/ui/".strtolower($layout)."/controller/controller.${piece}.php");
$controller_name = 'controller_'.str_replace('.', '_', $piece);
$controller = new ${controller_name};
$controller->action ( );

//----------------- Functions ----------------------------------------------

function controller_standard_error_handler ($no, $str, $file, $line, $context) {
	switch ($no) {
		case E_USER_ERROR:
		die("$file [$line] : $str");
		break;
	}
}

?>
