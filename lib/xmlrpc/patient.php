<?php
 // $Id$
 // $Author$
 // Defines FreeMED.Patient namespace

function FreeMED_Patient_list($param) {
	global $sql;

	// Get criteria
	$my_param = $param->getParam(0);
	$criteria = strtolower($my_param->scalarval());

	// Perform search
	$query = "SELECT * FROM patient WHERE (".
		"LCASE(ptlname) LIKE '%".addslashes($criteria)."%' OR ".
		"LCASE(ptfname) LIKE '%".addslashes($criteria)."%' ".
		") ORDER BY ptlname,ptfname";
	$result = $sql->query($query);

	$result_array = array();

	// Loop through results and add
	while ($r = $sql->fetch_array($result)) {
		$element = new xmlrpcval;
		$element->addStruct(array(
			"last_name" => rpc_prepare($r[ptlname]),
			"first_name" => rpc_prepare($r[ptfname]),
			"date_of_birth" => rpc_prepare($r[ptdob]),
			"patient_id" => rpc_prepare($r[ptid]),
			"id" => rpc_prepare($r[id])
		));
		$result_array[] = $element;
		unset($element);
	}

	// Return array of structures
	$val = new xmlrpcval;
	$val->addArray($result_array);
	return new xmlrpcresp($val);
} rpc_register("FreeMED.Patient.list");

?>
