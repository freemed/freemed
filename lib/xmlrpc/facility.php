<?php
 // $Id$
 // $Author$
 // Defines FreeMED.Facility.* namespace

function FreeMED_Facility_list ($params) {
	// Get all facilities
	global $sql;
	$result = $sql->query("SELECT * FROM facility ORDER BY psrname");
	if (!$sql->results($result)) {
		return new xmlrpcresp(
			new xmlrpcval("error", "string")
		);
	}

	$result_array = array();

	// Loop through results and add
	while ($r = $sql->fetch_array($result)) {
		$element = new xmlrpcval();
		$element->addStruct(array(
			"name" => xmlrpc_php_encode(stripslashes($r[psrname])),
			"city" => xmlrpc_php_encode(stripslashes($r[psrcity])),
			"state" => xmlrpc_php_encode(stripslashes($r[psrstate]))
		));
		$result_array[] = $element; unset($element);
	}

	// Create struct to return
	$val = new xmlrpcval();
	$val->addArray($result_array);
	return new xmlrpcresp($val);
} rpc_register("FreeMED.Facility.list");

?>
