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

//----- Load neccesary headers
define ('SESSION_DISABLE', true);
include_once ("lib/freemed.php");

if ($_SERVER['argc']) {
	trigger_error('Cannot be called from the command line.', E_USER_ERROR);
}

//----- Define freemed authorization
function freemed_basic_auth () {
	//----- Check for authentication
	$headers = getallheaders(); $authed = false;
	if (ereg('Basic', $headers['Authorization'])) {
		// Parse headers
		$tmp = $headers['Authorization'];
		$tmp = ereg_replace(' ', '', $tmp);
		$tmp = ereg_replace('Basic', '', $tmp);
		$auth = base64_decode(trim($tmp));
		list ($user, $pass) = explode(':', $auth);
	
		// Check for username/password
		$query = "SELECT username, userpassword, userrealphy, id FROM user ".
			"WHERE username='".addslashes($user)."' AND ".
			"userpassword=MD5('".addslashes($pass)."')";
		$r = $GLOBALS['sql']->queryRow( $query );

		if ($r['id']) {
			$authed = true;
			$GLOBALS['__freemed']['basic_auth_id'] = $r['id'];
			$GLOBALS['__freemed']['basic_auth_phy'] = $r['userrealphy'];
		} else {
			// Clear basic auth id
			$authed = false;
			$GLOBALS['__freemed']['basic_auth_id'] = 0;
			$GLOBALS['__freemed']['basic_auth_phy'] = 0;
		}
	} else {
		// Otherwise return fault for no authorization
		Header("WWW-Authenticate: Basic realm=\"".prepare(PACKAGENAME." v".VERSION." vCalendar")."\"");
		Header("HTTP/1.0 401 Unauthorized");
		die();
	}
	return $authed;
} // function freemed_basic_auth

function freemed_get_auth ( ) {
	global $sql;
	syslog(LOG_INFO, "vCalendar [get] username = ".$_GET['user']);
	$query = "SELECT username, userpassword, userrealphy, id FROM user ".
		"WHERE username='".addslashes($_GET['user'])."' AND ".
		"userpassword='".addslashes($_GET['hash'])."'";
	$r = $sql->queryRow( $query );
	if ($r['id']) {
		$authed = true;
		$GLOBALS['__freemed']['basic_auth_id'] = $r['id'];
		$GLOBALS['__freemed']['basic_auth_phy'] = $r['userrealphy'];
		return true;
	} else {
		// Clear basic auth id
		$authed = false;
		$GLOBALS['__freemed']['basic_auth_id'] = 0;
		$GLOBALS['__freemed']['basic_auth_phy'] = 0;
		return false;
	}
	return false;
} // end function freemed_get_auth

// Check for GET, then basic authentication
if (!freemed_get_auth()) {
	if (!freemed_basic_auth()) {
		die("Not authorized.");
	}
}

// Intelligently decide which physician to use
$__phy = ( ($_REQUEST['physician'] > 0) ?
		$_REQUEST['physician'] :
		$GLOBALS['__freemed']['basic_auth_phy'] );

// Figure out name, etc
switch ($_REQUEST['type']) {
	case 'fromdate':
	if ($__phy > 0) {
		// Assume that it's for a physician
		$ts = mktime (0,0,0, $_REQUEST['m'], $_REQUEST['d'], $_REQUEST['y']);
		$physician = CreateObject('org.freemedsoftware.core.Physician', $__phy);
		$name = $physician->fullName();
		$criteria = "calphysician='".addslashes($__phy)."' AND ".
			"caldateof >= '".addslashes(date("Y-m-d", $ts))."'";
		$stamp = date("Ymd", $ts) . '.' . $__phy;
	} else {
		die('Not enough information provided.');
	}
	break;

	default:
	if ($__phy > 0) {
		// Assume that it's for a physician
		$physician = CreateObject('org.freemedsoftware.core.Physician', $__phy);
		$name = $physician->fullName();
		$criteria = "calphysician='".addslashes($__phy)."' AND ".
			"caldateof >= '".addslashes(date("Y-m-d"))."'";
		$stamp = date("Ymd") . '.' . $__phy;
	} else {
		die('Not enough information provided.');
	}
	break; // end default
}

// vCalendar headers
Header("Content-Type: text/x-vCalendar");
Header("Content-Disposition: inline; filename=".$stamp.".vcs");

// Create vCalendar object
$v = CreateObject('org.freemedsoftware.core.vCalendar', $name, $criteria);

// Output the information
print $v->generate();

?>
