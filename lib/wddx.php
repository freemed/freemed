<?php
 // $Id$
 // $Author$
 // desc: WDDX helper functions
 // lic : GPL
 // $Log$
 // Revision 1.1  2001/10/12 15:01:43  rufustfirefly
 // Initial commit of WDDX data exchange wrapper functions
 //

//----- Check for WDDX support in PHP
if (!function_exists("wddx_deserialize")) {
	DIE("WDDX support needs to be built into PHP!");
} // end checking for WDDX support in PHP

// function wddx_serve ( array )
function wddx_serve ( $service, $values ) {
	// Bring all into current scope
	if (is_array($values)) {
		foreach ($values AS $k => $v) global ${$v};
	} // end scope adjustment

	// Generate packet id
	$packet = wddx_packet_start ( $service );
	if (!$packet) DIE("wddx_serve :: internal error");

	// Add parameters to packet
	$parameters = array_merge ( array($packet), $values );
	call_user_func_array ("wddx_add_vars", $parameters);

	// Return generated packet
	return wddx_packet_end ($packet);
} // end function wddx_serve

// function wddx_client ( location, (globalize vars?) )
function wddx_client ( $location, $globalize=true ) {
	// Open first
	$fp = file($location);

	// If we can't open it, return false
	if (!$fp) return false;

	// Get packet
	$packet = implode ("", $fp);

	// Deserialize values
	$values = wddx_deserialize($packet);

	// Either return the array, or globalize it
	if ($globalize) {
		foreach ($values AS $k => $v) {
			global ${$k};
			${$k} = $v;
		} // end looping
	} else { // return the values
		return $values;
	} // end globalize or return values
} // end function wddx_client

?>
