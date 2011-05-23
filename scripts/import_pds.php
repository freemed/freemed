#!/usr/bin/env php
<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

print "Patient Data Store Import Tool\n";
print "(c) 2010 FreeMED Software Foundation\n\n";

// Loop through the scanned documents table
print " - Getting all scanned documents ... ";
$ds = $GLOBALS['sql']->queryAll( "SELECT * FROM images;" );
print "[done]\n";

print " - Removing any existing images ... ";
$GLOBALS['sql']->query( "DELETE FROM pds WHERE module = 'scanneddocuments';" );
print "[done]\n";

print " - Looping through all scanned documents.\n";
foreach ($ds AS $d) {
	$fn = PHYSICAL_LOCATION . "/" . freemed::image_filename(
		  freemed::secure_filename($d['imagepat'])
		, freemed::secure_filename($d['id'])
		, 'djvu'
	);
	print " - Processing document id " . $d['id'] ." for patient id " . $d['imagepat'] . " ( filename = ${fn} ) ... ";
	if ( ! file_exists( $fn ) ) {
		print "[FAILED, DOESN'T EXIST]\n";
	} else {
		$GLOBALS['sql']->query( "INSERT INTO pds ( id, patient, module, contents ) VALUES ( ".$GLOBALS['sql']->quote($d['id']).", ".$GLOBALS['sql']->quote($d['imagepat']).", ".$GLOBALS['sql']->quote("scanneddocuments").", ".$GLOBALS['sql']->quote(file_get_contents($fn))." );" );
		print "[done]\n";
	}
}

// Loop through the photoid table
print " - Getting all photo id ... ";
$ds = $GLOBALS['sql']->queryAll( "SELECT * FROM photoid;" );
print "[done]\n";

print " - Removing any existing photoid ... ";
$GLOBALS['sql']->query( "DELETE FROM pds WHERE module = 'photographicidentification';" );
print "[done]\n";

print " - Looping through all photoid.\n";
foreach ($ds AS $d) {
	$fn = $d['p_filename'];
	print " - Processing photoid id " . $d['id'] ." for patient id " . $d['imagepat'] . " ( filename = ${fn} ) ... ";
	if ( ! file_exists( $fn ) ) {
		print "[FAILED, DOESN'T EXIST]\n";
	} else {
		$GLOBALS['sql']->query( "INSERT INTO pds ( id, patient, module, contents ) VALUES ( ".$GLOBALS['sql']->quote($d['id']).", ".$GLOBALS['sql']->quote($d['p_patient']).", ".$GLOBALS['sql']->quote("photographicidentification").", ".$GLOBALS['sql']->quote(file_get_contents($fn))." );" );
		print "[done]\n";
	}
}
print " - Finished looping.\n";

?>
