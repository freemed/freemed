<?php
 // $Id$
 // $Author$
 // Defines FreeMED.Patient.* namespace

class Patient {

	function name($id) {
		global $sql;

		// Perform search
		$query = "SELECT * FROM patient WHERE id='".addslashes($id)."'";
		$result = $sql->query($query);

		// Loop through results and add
		if ($sql->results($result)) {
			$r = $sql->fetch_array($result);
			return $r['ptlname'].', '.$r['ptfname'].
				( !empty($r['ptmname']) ? ' '.$r['ptmname'] : '' );
		} else {
			return 'ERROR';
		}
	} // end function name

	function picklist($criteria) {
		global $sql;

		// Perform search
		$query = "SELECT * FROM patient WHERE (".
			"LCASE(ptlname) LIKE '%".addslashes($criteria)."%' OR ".
			"LCASE(ptfname) LIKE '%".addslashes($criteria)."%' ".
			") ORDER BY ptlname,ptfname";
		$result = $sql->query($query);

		$result_array = array();

		// Loop through results and add
		while ($r = $sql->fetch_array($result)) {
			$element = CreateObject('PHP.xmlrpcval');
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
		$val = CreateObject('PHP.xmlrpcval');
		$val->addArray($result_array);
		return CreateObject('PHP.xmlrpcresp', $val);
	} // end method picklist

} // end class Patient
 
?>
