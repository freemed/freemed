<?php
 // $Id$
 //
 // Authors:
 //     Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

 // obligatory initialization garbage
$page_name = "djvu_service.php";
include ("lib/freemed.php");
define ('RESIZE', 800);

//----- Authenticate user cookie
freemed::connect ();

//----- Check ACLs
if (!freemed::acl('emr', 'view')) {
//	trigger_error(__("You don't have access to do that."), E_USER_ERROR);
}

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"djvu_service.php|user $user_to_log image access");}	

//----- Clean all variables
$patient = freemed::secure_filename($_REQUEST['patient']);
$type = freemed::secure_filename($_REQUEST['type']);
$id = freemed::secure_filename($_REQUEST['id']);
$page = freemed::secure_filename($_REQUEST['page']);
$name = freemed::secure_filename($_REQUEST['name']);

//----- Assemble proper file name
switch ($type) {
	case 'unfiled': case 'unread':
	$imagefilename = 'data/fax/' . $type . '/' . $name;
	break;

	default:
	$imagefilename = freemed::image_filename($patient, $id, 'djvu');
	break;
}

// Get the page
$d = CreateObject('_FreeMED.Djvu', $imagefilename);
if (!is_object($d)) { die ("ERROR!"); }

// Check for past page end
if ($page > $d->NumberOfPages() or $page < 1) { die("ERROR, page out of bounds"); }

// display header for content type
Header ("Content-Type: image/jpeg");
$contents = $d->GetPage( $page, true, false, false );
print $contents;
die();

?>
