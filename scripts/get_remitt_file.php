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

print "REMITT Filestore Pull Tool\n";
print "(c) 2015 FreeMED Software Foundation\n\n";

$fn = "";
if (array_key_exists(1, $_SERVER['argv'])) {
	$fn = $_SERVER['argv'][1];
}

//print " - Submitting billkey $bk : ";
//print_r( SubmitBillkey($bk, '4010_837p', 'org.remitt.plugin.transport.StoreFile', '') );

if ($fn == "") {
	$fl = GetFileList();
	foreach ($fl AS $k => $v) {
		print $v->filename . " [" . $v->filesize . "] billkey = " . $v->originalId . "\n";	
	}
	die();
}

file_put_contents( $fn, GetFile( $fn ));

print "Done.\n";

//
//
//

function GetFileList ( ) {
	$remitt = CreateObject('org.freemedsoftware.api.Remitt', false); //freemed::config_value('remitt_url'));

	return $remitt->getFileList('output', 'year', date('Y'));
} // end method GetFileList

function GetFile ( $f ) {
	$remitt = CreateObject('org.freemedsoftware.api.Remitt', false); // freemed::config_value('remitt_url'));

	return $remitt->GetFile('output', $f);
} // end method GetFile

?>

