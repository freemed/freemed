<?php
 // $Id$
 // $Author$
 // Defines FreeMED.Allergies.* namespace

class Allergies {

	function add ($patient, $allergy) {
		global $sql;

		// Perform search
		$query = "SELECT ptallergies FROM patient WHERE id='".addslashes($patient)."'";
		$result = $sql->query($query);
		if ($sql->results($result)) { 
			extract($sql->fetch_array($result)); 
		}

		// Same as in allergies module... expand
		$my_allergies = sql_expand($ptallergies);
		//foreach ($my_allergies AS $k => $v) { print "$k = $v\n"; }
		if (!is_array($my_allergies)) { 
			$my_allergies = array ($my_allergies);
		}

		// Add a new member to the array
		$my_allergies[] = $allergy;

		// Remove empties
		foreach ($my_allergies AS $k => $v) {
			if (empty($v)) unset($my_allergies[$k]);
		}

		// Recombine
		$allergies = sql_squash($my_allergies);

		// Actually update the patient record
		$res = $sql->query($sql->update_query(
				"patient",
				array("ptallergies" => $allergies),
				array("id" => $patient)
			));
		return CreateObject('PHP.xmlrpcval', $res, 'boolean');
	} // end method add

	function get ($patient) {
		global $sql;

		// Perform search
		$query = "SELECT ptallergies FROM patient WHERE id='".addslashes($patient)."'";
		$result = $sql->query($query);

		if ($sql->results($result)) {
			$r = $sql->fetch_array($result);
			$_a = sql_expand($r['ptallergies']);
			if (!is_array($_a)) $_a = array($_a);
			foreach ($_a as $k => $v) {
				$a[] = CreateObject('PHP.xmlrpcval', $v, 'string');
			}
			return CreateObject('PHP.xmlrpcresp', 
				CreateObject('PHP.xmlrpcval', $a, 'array'));
		} else {
			return CreateObject('PHP.xmlrpcval', false, 'boolean');
		}
	} // end method get

	function remove ($patient, $allergy) {
		global $sql;

		// Perform search
		$query = "SELECT ptallergies FROM patient WHERE id='".addslashes($patient)."'";
		$result = $sql->query($query);
		if ($sql->results($result)) { extract($sql->fetch_array($result)); }
	
		// Same as in allergies module... expand
		$my_allergies = sql_expand($ptallergies);
		//foreach ($my_allergies AS $k => $v) { print "$k = $v\n"; }
		if (!is_array($my_allergies)) { $my_allergies = array ($my_allergies); }
	
		// Removal
		foreach ($my_allergies AS $k => $v) {
			// Remove empties or matches
			if (empty($v) or ($v == $allergy)) {
				unset($my_allergies[$k]);
			}
		}
	
		// Recombine
		$allergies = sql_squash($my_allergies);
	
		// Actually update the patient record
		$res = $sql->query($sql->update_query(
				"patient",
				array("ptallergies" => $allergies),
				array("id" => $patient)
			));
		return CreateObject('PHP.xmlrpcval', $res, "boolean");
	} // end method remove

} // end class Allergies

?>
