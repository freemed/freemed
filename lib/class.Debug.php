<?php
	// $Id$
	// $Author$

// class Debug
class Debug {

	function Debug () {
		// Doesn't really do much...
		return(TRUE);
	} // end constructor Debug

	function init() {

		global $sql;

		$result=$sql->query("DROP TABLE test");
		$result=$sql->query($sql->create_table_query(
			'test',
			array(
				'name' => SQL_CHAR(10),
				'other' => SQL_CHAR(12),
				'phone' => SQL_INT(0),
				'id' => SQL_SERIAL
			),array('id')
		));

		return($result);

	} // end method Debug->init

} // end class Debug

?>
