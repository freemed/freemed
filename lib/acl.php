<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

// I don't know what this condition could be, but there's always
// some reason *not* to load this
if (1 == 1) {
	// Load phpgacl object, etc here....
	$acl = CreateObject('org.freemedsoftware.acl.gacl', 
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
