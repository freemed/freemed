<?php
 // $Id$
 // $Author$
 // Defines FreeMED.Config.* namespace

class Config {

	function get ($config) {
		global $sql;

		// Perform search
		$query = "SELECT * FROM config WHERE c_option='".addslashes($config)."'";
		$result = $sql->query($query);

		if ($sql->results($result)) {
			$r = $sql->fetch_array($result);
			return stripslashes($r['c_value']);
		} else {
			return false;
		}
	}

	function set($var, $val) {
		global $sql;

		// Perform search (to decide if it's insert or update)
		$query = "SELECT * FROM config WHERE c_option='".addslashes($var)."'";
		$result = $sql->query($query);

		if ($sql->results($result)) {
			$res = $sql->query($sql->update_query(
				"config",
				array("c_value" => $val),
				array("c_option" => $var)
			));
			return ($res == true);
		} else {
			$res = $sql->query($sql->insert_query(
				"config",
				array("c_value" => $val)
			));
			return ($res == true);
		}
	} 

} // end class Config

?>
