<?php
 // $Id$
 // $Author$
 // $Log$
 // Revision 1.1  2001/11/20 21:59:06  rufustfirefly
 // WDDX/XMLRPC services
 //

// function emri_capability ( )
//   This function is a clone of IMAP's CAPABILITY function,
//   in that it reports what it is capable of doing, as an array.
function emri_capability () {
	$capabilities = array (
		"capability",
		"ping"
	);

	// Return the appropriate array
	return $capabilities;
} // end function emri_capability
wddx_add_service("emri_capability");

// function emri_ping ( )
//   This simple function simply returns whatever it was sent,
//   and is useful as a diagnostic function.
function emri_ping ($packet) {
	return $packet;
} // end function emri_ping
wddx_add_service("emri_ping");

?>
