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

	// Method: picklist
	//
	//	Get a list of patients based on provided criteria.
	function picklist($criteria) {
		global $sql;

		if (!is_array($criteria)) {
			// Perform simple search
			$query = "SELECT * FROM patient WHERE (".
				"LCASE(ptlname) LIKE '%".addslashes($criteria)."%' OR ".
				"LCASE(ptfname) LIKE '%".addslashes($criteria)."%' ".
				") ORDER BY ptlname,ptfname";
		} else {
			// Perform complex search by extracting keys and
			// values from passed structure. WARNING! THIS CURRENTLY
			// SANITIZES VALUES, BUT DOES NOT CHECK KEY NAMES!
			$params = array ();
			foreach ($criteria AS $k => $v) {
				switch ($k) {
					case 'last_name': $k = 'ptlname'; break;
					case 'first_name': $k = 'ptfname'; break;
					case 'city': $k = 'ptcity'; break;
					case 'state': $k = 'ptstate'; break;
					default: break;
				}
				// Handle single patient request
				if ($k == 'id') {
					$params[] = 'id = \''.addslashes($v).'\'';
				} else {
					$params[] = 'LCASE('.addslashes($k).') LIKE '.
						'\'%'.addslashes($v).'%\'';
				}
			}
			$query = "SELECT * FROM patient WHERE (".
				join(' AND ', $params).
				") ORDER BY ptlname, ptfname";
		}
		$result = $sql->query($query);

		$result_array = array();

		// Loop through results and add
		while ($r = $sql->fetch_array($result)) {
			$element = CreateObject('PHP.xmlrpcval');
			$element->addStruct(array(
				"last_name" => rpc_prepare($r['ptlname']),
				"middle_name" => rpc_prepare($r['ptmname']),
				"first_name" => rpc_prepare($r['ptfname']),
				"date_of_birth" => rpc_prepare($r['ptdob']),
				"city" => rpc_prepare($r['ptcity']),
				"state" => rpc_prepare($r['ptstate']),
				"patient_id" => rpc_prepare($r['ptid']),
				"id" => rpc_prepare($r['id'])
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
