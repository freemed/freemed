<?php
	// $Id$
	// $Author$

// I don't know what this condition could be, but there's always
// some reason *not* to load this
if (1 == 1) {
	// Load phpgacl object, etc here....
	$acl = CreateObject('_ACL.gacl', 
		array (
			// Database information from FreeMED
			'db_type' => 'mysql', // hardcoded for now
			'db_host' => DB_HOST,
			'db_user' => DB_USER,
			'db_password' => DB_PASSWORD,
			'db_name' => DB_NAME,
			'db_table_prefix' => 'acl_',
			// Caching and security settings
			'caching' => true,
			'force_cache_expire' => true,
			'cache_expire_time' => 600
		)
	);
}

?>
