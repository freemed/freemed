<?php
 // $Id$
 // $Author$
 // Defines FreeMED.Scheduler.* namespace

class Scheduler {

	function get ($params) {
		// Get all facilities
		global $sql;

		// "date"
		$caldateof = $params["date"];
		if (!empty($caldateof))
			$query[] = "caldateof='".addslashes($caldateof)."'";
	
		// "facility"
		$calfacility = $params["facility"];
		if (!empty($calfacility))
			$query[] = "calfacility='".addslashes($calfacility)."'";

		// "patient"
		$calpatient = $params["patient"];
		if (!empty($calfacility))
			$query[] = "calpatient='".addslashes($calpatient)."'";

		// "physician"
		$calphysician = $params["physician"];
		if (!empty($calphysician))
			$query[] = "calphysician='".addslashes($calphysician)."'";

		// Create "criteria"
		if (count($query)>0) {
			$criteria = join(" AND ", $query);
		} else {
			// Handle no queries
			$criteria = "(1 = 1)";
		}
	
		return rpc_generate_sql_hash(
			"scheduler",
			array(
				"date" => "caldateof",
				"hour" => "calhour",
				"minute" => "calminute",
				"physician" => "calphysician",
				"patient" => "calpatient",
				"prenote" => "calprenote",
				"postnote" => "calpostnote",
				"id"
			),
			"WHERE ".$criteria." ORDER BY caldateof"
		);
	} // end method get

} // end class Scheduler

?>
