<?php
 // $Id$
 // $Author$

// class Debug
class GeneralConfig {

	function GeneralConfig () {
	
		return(TRUE);

		//doesnt really do much...


	} // end constructor Debug

	function init() {

		global $sql;

		$result=$sql->query("DROP TABLE config"); 
		$result=$sql->query($sql->create_table_query(
		'config',
		array (
			'c_option' => SQL_CHAR(6),
			'c_value' => SQL_VARCHAR(100),
			'id' => SQL_SERIAL
			), array ('id')
		));
		if ($result) $display_buffer .= "<li>".__("Configuration")."</li>\n";

			$stock_config = array (
				'icd9' => '9',
				'gfx' => '1',
				'calshr' => $cal_starting_hour,
				'calehr' => $cal_ending_hour,
				'cal_ob' => 'enable',
				'dtfmt' => 'ymd',
				'phofmt' => 'unformatted',
				'folded' => 'yes',
				'cal1' => '',
				'cal2' => '',
				'cal3' => '',
				'cal4' => '',
				'cal5' => '',
				'cal6' => '',
				'cal7' => '',
				'cal8' => '',
			);
			foreach ($stock_config AS $key => $val) {
				if (!is_integer($key)) {
					$result = $sql->query($sql->insert_query(
						'config',
						array (
							'c_option' => $key,
							'c_value' => $val
						)
					));
				}
		}


		return($result);


	}


} // end class Debug

?>
