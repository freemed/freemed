<?php
 // $Id$
 // $Author$
 // Defines FreeMED.EpisodeOfCare.* namespace

function FreeMED_EpisodeOfCare_list($param) {
	global $sql;

	// Get criteria
	$my_param = $param->getParam(0);
	$criteria = strtolower($my_param->scalarval());

	// Perform search
	$query = "SELECT * FROM eoc WHERE (".
		"eocpatient = '".addslashes($criteria)."' ".
		") ORDER BY eocdtlastsimilar DESC";
	$result = $sql->query($query);

	$result_array = array();

	// Loop through results and add
	while ($r = $sql->fetch_array($result)) {
		$element = new xmlrpcval;
		$element->addStruct(array(
			"last_similar" => rpc_prepare($r[eocdtlastsimilar]),
			"description" => rpc_prepare($r[eocdescrip]),
			"id" => rpc_prepare($r[id])
		));
		$result_array[] = $element;
		unset($element);
	}

	// Return array of structures
	$val = new xmlrpcval;
	$val->addArray($result_array);
	return new xmlrpcresp($val);
} rpc_register("FreeMED.EpisodeOfCare.list");

?>
