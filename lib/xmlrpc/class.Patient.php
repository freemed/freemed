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

	// Method: get
	//
	//	Fetch an individual patient record.
	//
	// Parameters:
	//
	//	$id - Patient record id
	//
	// Returns:
	//
	//	Structure containing main EMR record for patient
	//
	function get ( $id ) {
		// Pass through from patient record
		$r = freemed::get_link_rec($id, 'patient');
		$element = CreateObject('PHP.xmlrpcval');
		$element->addStruct(array(
			"last_name" => rpc_prepare($r['ptlname']),
			"middle_name" => rpc_prepare($r['ptmname']),
			"first_name" => rpc_prepare($r['ptfname']),
			"address_line1" => rpc_prepare($r['ptaddr1']),
			"address_line2" => rpc_prepare($r['ptaddr2']),
			"date_of_birth" => rpc_prepare($r['ptdob']),
			"city" => rpc_prepare($r['ptcity']),
			"state" => rpc_prepare($r['ptstate']),
			"zip" => rpc_prepare($r['ptzip']),
			"country" => rpc_prepare($r['ptcountry']),
			"phone_home" => rpc_prepare($r['pthphone']),
			"phone_work" => rpc_prepare($r['ptwphone']),
			"phone_fax" => rpc_prepare($r['ptfax']),
			"sex" => rpc_prepare($r['ptsex']),
			"marital" => rpc_prepare($r['ptmarital']),
			"ssn" => rpc_prepare($r['ptssn']),
			"next_of_kin" => rpc_prepare($r['ptnextofkin']),
			"patient_id" => rpc_prepare($r['ptid']),
			"id" => rpc_prepare($r['id'])
		));

		// Return structure
		return CreateObject('PHP.xmlrpcresp', $element);
	} // end method get

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
					case 'date_of_birth': $k = 'ptdob'; break;
					case 'dob': $k = 'ptdob'; break;
					default: break;
				}
				// Handle single patient request
				switch ($k) {
					case 'id':
					$params[] = 'id = \''.addslashes($v).'\'';
					break;

					case 'ptdob':
					$params[] = 'ptdob = \''.addslashes($v).'\'';
					break;

					default:
					$params[] = 'LCASE('.addslashes($k).') LIKE '.
						'\'%'.addslashes($v).'%\'';
					break;
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
