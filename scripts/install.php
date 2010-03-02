#!/usr/bin/env php
<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //      Philipp Meng <pmeng@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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

define ( 'SESSION_DISABLE', true );

print "FreeMED CLI Installation Tool\n";
print "(c) 2009 FreeMED Software Foundation\n\n";

if ( ! file_exists ( './scripts/install.php' ) ) {
	print "You must run this from the root directory of your FreeMED install.\n\n";
	die();
}

if ( ! file_exists ( 'lib/settings.php' ) ) {
	print "Please create the file lib/settings.php form the template file lib/settings.php.tpl and enter the correct values.\nWhen finished, please execute this script again.\nABORTED.\n\n";
	die();
}

function getInput ( $mask ) { fscanf(STDIN, "${mask}\n", $x); return $x; }

//Check for MySQL-Version and inform the user about the necessarity of SUPER-rights for MySQL
//When the user likes, he can enter his root-user/pass to override declaration in lib/settings.php
$output = shell_exec('mysql -V');
preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
if ($version[0] >= "5.0.4") {
	print "You have MySQL Version ".$version[0]." installed. There are problems with creating triggers on the Tables with non-root privleges (creating triggers requires SUPER privileges). Does the username/password specified in lib/settings.php have SUPER privileges? (yes/no) [no] ";
	if (getInput('%s') != 'yes') {
		print "\n\nWithout the correct rights, this script won't work. You can enter the username and password of a user with SUPER privileges here. These settings will not be saved, they are only used by this script. The settings in lib/settings.php are kept and used by all transactions of FreeMED in the future. Do you want to do that? (yes/no) [no] ";
		if (getInput('%s') != 'yes') {
			print "Installation interrupted by user.\n";			
			die();
		}
		print "MySQL Username: ";
		$username = getInput('%s');
		print "\n";
		print "MySQL Password: ";
		$password = getInput('%s');
		print "\n\n";
		// Define the DB-User and Password here to override settings in lib/settings.php	
		define ( 'DB_USER', $username ); // SQL server username
		define ( 'DB_PASSWORD', $password ); // SQL server password
	}
}

include_once ( 'lib/freemed.php' );

function execSql  ( $s    ) { print " - Executing \"$s\" : "; $GLOBALS['sql']->query( $s ); print " ... [done]\n"; }
function printHeader ( $x ) { print "\n\n ----->> ${x} <<-----\n\n"; }
function loadSchema ( $s ) { $c="./scripts/load_schema.sh 'mysql' '${s}' '".DB_USER."' '".DB_PASSWORD."' '".DB_NAME."'"; print `$c`; print "\n\n"; }

print "\nPlease type 'yes' if you're *sure* you want to do this : ";
if ( getInput( '%s' ) != 'yes' ) {
	print "\nI didn't think so. :(\n";
	die();
}

print "

Thank you for choosing FreeMED as your electronic medical record / practice
management system. FreeMED is an opensource program and is located on the
web at http://www.freemedsoftware.org/

This command line tool will allow you to install FreeMED from the Linux
command line. Please make sure that your FreeMED installation is writeable,
otherwise this may cause some *nasty* problems.

";

if ( !function_exists( 'mysql_connect' ) ) {
	print "mysql support needs to be enabled before we can proceed!\n";
	die();
}

printHeader( "Include aggregation table definition" );
loadSchema( 'patient' );

printHeader( "Load admin table definitions" );
loadSchema( 'session' );
loadSchema( 'modules' );
loadSchema( 'user' );
loadSchema( 'scheduler' );

printHeader( "Install ACL tables" );
loadSchema( 'acl' );

// Add the administrative account
printHeader( "Add an administrative account" );
print "\nPlease enter an username for your administrative account [root] : ";
$username = getInput( '%s' );
if ($username == "") { $username = "root"; }
print "\nPlease enter a password for your administrative account : ";
$password = getInput( '%s' );
LoadObjectDependency( "org.freemedsoftware.public.Installation" );
Installation::CreateAdministrationAccount( $username, $password );
print "\n\n";

printHeader( "Form ACL rules for users" );
include_once( dirname(__FILE__) . '/../lib/acl.php' );
include_once( dirname(__FILE__) . '/../lib/org/freemedsoftware/module/ACL.class.php' );
$a = new ACL();
$q = "SELECT username, id FROM user WHERE id > 0";
$r = $GLOBALS['sql']->queryAll( $q );
foreach ( $r AS $user ) {
	print " - Adding ACL record for user ".$user['username']." (".$user['id'].") \n";
	$a->UserAdd( $r['id'] );
}

printHeader( "Create 'healthy system' status" );
`touch ./data/cache/healthy`;

printHeader( "Force module definition upgrades" );
$modules = CreateObject( 'org.freemedsoftware.core.ModuleIndex', true, false );

printHeader( "Force module definition upgrades (round 2)" );
$modules = CreateObject( 'org.freemedsoftware.core.ModuleIndex', true, false );

print "\n\nfinished.\n";

?>
