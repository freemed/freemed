<?php
	// $Id$
	// jeff@freemedsoftware.org

include_once ( 'lib/freemed.php' );

// Start logging
unset ( $log );
$log = freemed::log_object ( );
$log->SystemLog( LOG__SECURITY, 'Provider', 'Relay', "Relay called with ".$_SERVER['PATH_INFO'] );

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
