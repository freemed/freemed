#!/usr/bin/env php
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

if (!$_SERVER['argc']) { die ("cannot be called via web"); }

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('include_path', dirname(dirname(__FILE__)).':'.ini_get('include_path'));

$base = dirname(__FILE__) . "/..";

print "FreeMED Settings Configuration Tool\n";
print "(c) 2009-2012 FreeMED Software Foundation\n\n";

if ( ! file_exists ( './scripts/install.php' ) ) {
	print "You must run this from the root directory of your FreeMED install.\n\n";
	die();
}

if ( file_exists ( 'lib/settings.php' ) ) {
	print "Your lib/settings.php file already exists; please remove if you are going\nto use this tool to generate it.\n";
	die();
}

function getInput ( $mask ) { fscanf(STDIN, "${mask}\n", $x); return $x; }

print "Installation name: ";
$data['installation'] = getInput('%s');
print "\n";
print "MySQL Hostname (should be '127.0.0.1' if you're running from a single machine): ";
$data['host'] = getInput('%s');
if ($data['host'] == '') { $data['host'] = '127.0.0.1'; }
print "\n";
print "MySQL database name (usually 'freemed'): ";
$data['name'] = getInput('%s');
if ($data['name'] == '') { $data['name'] = 'freemed'; }
print "\n";
print "MySQL Username: ";
$data['username'] = getInput('%s');
print "\n";
print "MySQL Password: ";
$data['password'] = getInput('%s');
print "\n";
print "Default UI language ('en_US' is the default language): ";
$data['language'] = getInput('%s');
if ($data['language'] == '') { $data['language'] = 'en_US'; }
print "\n";

include_once 'lib/loader.php';

// Load smarty engine
unset ( $smarty );
$smarty = CreateObject('net.php.smarty.Smarty');
$smarty->setTemplateDir( $base . "/lib/" );
$compile_dir = $base . "/data/cache/smarty/templates_c/";
$smarty->setCompileDir( $base . "/data/cache/smarty/templates_c/" );
`mkdir -p ${compile_dir}; chmod 777 ${compile_dir}`;
$cache_dir = $base . "/data/cache/smarty/cache/";
$smarty->setCacheDir( $base . "/data/cache/smarty/cache/" );
`mkdir -p ${cache_dir}; chmod 777 ${cache_dir}`;
$smarty->left_delimiter = '<{';
$smarty->right_delimiter = '}>';

foreach ($data AS $k => $v) {
	$smarty->assign($k, $v);
}

file_put_contents("lib/settings.php", $smarty->fetch( 'file:settings.php.tpl' ));

print "Congratulations, you have created a lib/settings.php file. To\n".
      "continue the installation, run ./scripts/install.php\n";

?>
