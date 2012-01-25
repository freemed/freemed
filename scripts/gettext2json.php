#!/usr/bin/env php
<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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

ini_set('include_path', dirname(dirname(__FILE__)).':'.ini_get('include_path'));
define('SESSION_DISABLE', true);
include_once ( 'lib/freemed.php' );

if (empty($_SERVER['argv'][1])) {
	die("syntax: ".$_SERVER['argv'][0]." locale\n");
} else {
	$my_locale = $_SERVER['argv'][1];
}

$reader = new FileReader(dirname(dirname(__FILE__)) . "/locale/" . $my_locale . "/LC_MESSAGES/gwt.mo" );
$streamer = new gettext_reader( $reader );
$streamer->load_tables();

// Remove bad entry at the beginning
unset( $streamer->cache_translations[''] );

$out = json_encode( $streamer->cache_translations );

$filename = dirname(dirname(__FILE__)) . "/ui/gwt/src/main/webapp/resources/locale/" . $my_locale . ".json";

// Write to locale file
file_put_contents( $filename, $out );

?>
