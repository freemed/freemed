<?php
 // $Id$
 // $Author$
 // Defines FreeMED.Facility.* namespace

class Facility {

	function picklist () {
		// Get all facilities
		global $sql;
		$result = $sql->query("SELECT * FROM facility ORDER BY psrname");
		if (!$sql->results($result)) {
			return CreateObject('PHP.xmlrpcresp',
				CreateObject('PHP.xmlrpcval', "error", 'string')
			);
		}

		return rpc_generate_sql_hash(
			"facility",
			array(
				"name" => "psrname",
				"city" => "psrcity",
				"state" => "psrstate",
				"id"
			),
			"ORDER BY psrname"
		);
	} // end method picklist

} // end class Facility

?>
