<?php
 // $Id$
 // $Author$
 // Defines FreeMED.EpisodeOfCare.* namespace

class EpisodeOfCare {

	function picklist($criteria) {
		global $sql;
	
		// Perform search
		$query = "SELECT * FROM eoc WHERE (".
			"eocpatient = '".addslashes($criteria)."' ".
			") ORDER BY eocdtlastsimilar DESC";
		$result = $sql->query($query);
	
		$result_array = array();
	
		// Loop through results and add
		while ($r = $sql->fetch_array($result)) {
			$element = CreateObject('PHP.xmlrpcval');
			$element->addStruct(array(
				"last_similar" => rpc_prepare($r[eocdtlastsimilar]),
				"description" => rpc_prepare($r[eocdescrip]),
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

} // end class EpisodeOfCare

?>
