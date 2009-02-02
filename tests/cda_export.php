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

include_once( dirname(__FILE__).'/bootstrap.test.php' );

if ($argc < 2) {
	print "syntax: $argv[0] patient_id [class, default=HL7v3_CDA]\n";
	exit(0);
}

$patient = $argv[1];
$class = ( $argv[2] ? $argv[2] : 'HL7v3_CDA' );

$sql = CreateObject('org.freemedsoftware.core.FreemedDb');

$cda = CreateObject( 'org.freemedsoftware.core.' . $class );
if (!is_object($cda) || !$patient) {
	die("Could not load class '${class}' or patient '${patient}'\n");
}

$cda->LoadPatient ( $patient );
print $cda->Generate ( );

?>
