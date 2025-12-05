#!/usr/bin/env php
<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2015 FreeMED Software Foundation
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

$status_map = array(
	0 => "completed",
	1 => "validation",
	2 => "render",
	3 => "translation",
	4 => "transmission",
	5 => "unknown"
);

$bk = (int)$_SERVER['argv'][1];

if ($bk == 0) {
	print " - Must specify billkey.\n";
	die();
}

$remitt = CreateObject( 'org.freemedsoftware.api.Remitt', false );
$billkey_hash = unserialize(freemed::get_link_field($bk, 'billkey', 'billkey'));
$xml = $remitt->RenderPayerXML($bk, $billkey_hash['procedures'], $billkey_hash['contact'], $billkey_hash['service'], $billkey_hash['clearinghouse']);

print $xml;

?>
