#!/usr/bin/env php
<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

if (!$_SERVER['argc']) { die ("cannot be called via web"); }

ini_set('include_path', dirname(dirname(__FILE__)).':'.ini_get('include_path'));
include_once ( 'lib/freemed.php' );

$cache = freemed::module_cache();

print "ACL Rebuild Tool\n";
print "(c) 2006 FreeMED Software Foundation\n\n";

$tables = array (
	'acl_acl',
	'acl_acl_sections',
	'acl_acl_seq',
	'acl_aco',
	'acl_aco_map',
	'acl_aco_sections',
	'acl_aro',
	'acl_aro_groups',
	'acl_aro_groups_map',
	'acl_aro_map',
	'acl_aro_sections',
	'acl_aro_seq',
	'acl_axo',
	'acl_axo_groups',
	'acl_axo_groups_map',
	'acl_axo_map',
	'acl_axo_sections',
	'acl_axo_seq',
	'acl_groups_aro_map',
	'acl_groups_axo_map',
	'acl_phpgacl'
);

print " - Dropping tables ... ";
foreach ($tables AS $table) { $GLOBALS['sql']->query('DROP TABLE '.$table); }
print "[done]\n";

// Reinitialize db
print " - Reinitializing ACL tables ... ";
$GLOBALS['sql']->query( "CREATE TABLE IF NOT EXISTS acl ( id SERIAL );" );
module_function('acl', '_setup', array());
print "[done]\n";

// Reimport users
print " - Reimporting users ... \n";
$users = $GLOBALS['sql']->queryCol( "SELECT id FROM user" );
foreach ( $users AS $u ) {
	print " -- Importing user #${u} ... ";
	module_function('acl', 'UserAdd', array( $u ) );
	print "done.\n";
}

?>
