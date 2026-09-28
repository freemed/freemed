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

// $_SERVER['argc'] only exists in the CLI SAPI. Reading it unguarded emitted an
// E_WARNING into the response body on every web request, and that output is
// fatal to the 401/403 challenges below ("Cannot modify header information -
// headers already sent"). Read it defensively.
if (isset($_SERVER['argc']) and $_SERVER['argc']) {
	trigger_error('Cannot be called from the command line.', E_USER_ERROR);
}

//----- Define freemed authorization
//
// Function: freemed_basic_auth
//
//	HTTP Basic authentication for this endpoint.
//
//	Returns true and populates $GLOBALS['__freemed']['basic_auth_id'] and
//	$GLOBALS['__freemed']['basic_auth_phy'] (the authenticated account's own
//	provider record, 0 when the account is not a provider) when the supplied
//	credential is correct. Returns false, with both globals cleared, when it is
//	not - and with no Authorization header at all it issues the 401 challenge
//	and stops, which is what makes a plain `curl -u` client work.
//
//	The stored credential is an unsalted MD5 digest of the password (moving the
//	storage format is out of scope here), so the password is still hashed with
//	MD5 before it is compared - but the comparison now happens in PHP against
//	the row fetched by username, using hash_equals(), instead of in SQL via
//	`userpassword = MD5('<pass>')`. That removes the timing side channel on the
//	comparison and keeps the credential out of a query string.
function freemed_basic_auth () {
	//----- Check for authentication
	$headers = getallheaders();
	$authorization = isset($headers['Authorization']) ? $headers['Authorization'] : '';
	$authed = false;
	if (preg_match('/Basic/', $authorization)) {
		// Parse headers
		$tmp = $authorization;
		$tmp = preg_replace('/ /', '', $tmp);
		$tmp = preg_replace('/Basic/', '', $tmp);
		$auth = base64_decode(trim($tmp));
		if (strpos($auth, ':') !== false) {
			list ($user, $pass) = explode(':', $auth, 2);
		} else {
			$user = ''; $pass = '';
		}

		// Fetch the account by username and compare the digest in PHP.
		$query = "SELECT username, userpassword, userrealphy, id FROM user ".
			"WHERE username='".addslashes($user)."'";
		$r = $GLOBALS['sql']->queryRow( $query );

		if (is_array($r) and !empty($r['id']) and
				hash_equals(strtolower((string) $r['userpassword']), md5((string) $pass))) {
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
		Header("WWW-Authenticate: Basic realm=\"".PACKAGENAME." v".VERSION." vCalendar\"");
		Header("HTTP/1.0 401 Unauthorized");
		die();
	}
	return $authed;
} // function freemed_basic_auth

// Function: freemed_legacy_hash_enabled
//
//	Compatibility gate for the deprecated GET `user`+`hash` credential path.
//
//	That path compared a request-supplied value directly against the stored
//	userpassword column - an unsalted MD5 digest - so anyone who could read the
//	digest (a database read, a backup, a log) could authenticate without ever
//	knowing the password: pass-the-hash. The path is therefore OFF by default
//	and only a site that explicitly sets the `vcals_legacy_hash` option in the
//	config table to a true value gets it back for the duration of a client
//	migration. Off is the default and there is no way to turn it on by request.
//
//	(freemed::config_value() has no isset() guard on its cache, so the read is
//	suppressed: the common "option not set" case must not emit a warning into
//	the response.)
function freemed_legacy_hash_enabled () {
	$v = @freemed::config_value('vcals_legacy_hash');
	if ($v === false or $v === null) { return false; }
	return in_array(strtolower(trim((string) $v)), array('1', 'on', 'yes', 'true'), true);
} // end function freemed_legacy_hash_enabled

// Function: freemed_get_auth
//
//	DEPRECATED. The GET `user`+`hash` credential path, kept only behind
//	freemed_legacy_hash_enabled(). Returns false, and does nothing at all,
//	unless a site has opted in; every use of the path is logged.
//
//	The claimed username is recorded because that is the only audit trail a
//	pass-the-hash attempt leaves; it is NOT yet authenticated at this point and
//	the log says so. (PHP has no LOG__SECURITY constant - LOG_NOTICE is used.)
function freemed_get_auth ( ) {
	global $sql;

	if (!freemed_legacy_hash_enabled()) { return false; }

	$__user = isset($_GET['user']) ? (string) $_GET['user'] : '';
	$__hash = isset($_GET['hash']) ? (string) $_GET['hash'] : '';

	syslog(LOG_NOTICE, "vCalendar [get] DEPRECATED hash authentication used ".
		"(vcals_legacy_hash=on), claimed username = ".$__user.
		", remote = ".(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-'));

	$query = "SELECT username, userpassword, userrealphy, id FROM user ".
		"WHERE username='".addslashes($__user)."'";
	$r = $sql->queryRow( $query );
	if (is_array($r) and !empty($r['id']) and
			hash_equals(strtolower((string) $r['userpassword']), strtolower($__hash))) {
		$GLOBALS['__freemed']['basic_auth_id'] = $r['id'];
		$GLOBALS['__freemed']['basic_auth_phy'] = $r['userrealphy'];
		return true;
	} else {
		// Clear basic auth id
		$GLOBALS['__freemed']['basic_auth_id'] = 0;
		$GLOBALS['__freemed']['basic_auth_phy'] = 0;
		return false;
	}
	return false;
} // end function freemed_get_auth

// Check for GET, then basic authentication
//
// The GET-hash path is dead unless vcals_legacy_hash is on (see above), so the
// only live credential is HTTP Basic. A rejected credential gets an explicit
// 401 - not a 200 with a complaint in the body, which is what die() alone
// produced before.
if (!freemed_get_auth()) {
	if (!freemed_basic_auth()) {
		Header("HTTP/1.0 401 Unauthorized");
		die("Not authorized.");
	}
}

// Intelligently decide which physician to use
//
// `physician` is a request parameter, so it is only honoured as an integer and,
// for a caller that is not already that provider, only with the scheduler view
// ACL (the same gate SchedulerTable uses). A non-admin account cannot name
// another provider's calendar; its own provider record comes from the identity
// the request actually authenticated as, not from the request.
$__phy = (int) $GLOBALS['__freemed']['basic_auth_phy'];

if ((int) (isset($_REQUEST['physician']) ? $_REQUEST['physician'] : 0) > 0) {
	$__requested = (int) $_REQUEST['physician'];
	if ($__requested !== $__phy) {
		// $GLOBALS['acl'] is built by lib/freemed.php only inside its session
		// block, which a SESSION_DISABLE request like this one skips, so the
		// object has to be loaded on demand here. It is loaded at request
		// scope (an include inside a function would bind $acl to that
		// function), and a request that cannot obtain or use it is DENIED:
		// an unavailable authorization object must never widen access.
		if (!isset($GLOBALS['acl']) or !is_object($GLOBALS['acl'])) {
			try {
				include_once(dirname(__FILE__)."/lib/acl.php");
			} catch (Throwable $e) {
				syslog(LOG_WARNING, "vCalendar [get] ACL unavailable: ".
					$e->getMessage());
			}
		}
		$__allowed = false;
		try {
			if (isset($GLOBALS['acl']) and is_object($GLOBALS['acl'])) {
				// Suppressed: this call builds the user cache, whose
				// constructor reads an unset $authdata under SESSION_DISABLE
				// and warns. The warning is printed into the response body and
				// would then block the 403 below ("headers already sent"); the
				// decision itself is logged explicitly further down.
				$__allowed = (bool) @freemed::acl('schedule', 'view');
			}
		} catch (Throwable $e) {
			syslog(LOG_WARNING, "vCalendar [get] schedule/view ACL check ".
				"errored (".$e->getMessage()."), denying");
			$__allowed = false;
		}
		if (! $__allowed) {
			syslog(LOG_NOTICE, "vCalendar [get] provider ".$__requested.
				" refused for user ".$GLOBALS['__freemed']['basic_auth_id']);
			Header("HTTP/1.0 403 Forbidden");
			die("Not authorized for that provider.");
		}
	}
	$__phy = $__requested;
}

// Figure out name, etc
$__type = isset($_REQUEST['type']) ? (string) $_REQUEST['type'] : '';
switch ($__type) {
	case 'fromdate':
	if ($__phy > 0) {
		// Assume that it's for a physician
		$__m = (int) (isset($_REQUEST['m']) ? $_REQUEST['m'] : 0);
		$__d = (int) (isset($_REQUEST['d']) ? $_REQUEST['d'] : 0);
		$__y = (int) (isset($_REQUEST['y']) ? $_REQUEST['y'] : 0);
		if (!checkdate($__m, $__d, $__y)) {
			Header("HTTP/1.0 400 Bad Request");
			die('Invalid date.');
		}
		$ts = mktime (0, 0, 0, $__m, $__d, $__y);
		$physician = CreateObject('org.freemedsoftware.core.Physician', $__phy);
		$name = $physician->fullName();
		$criteria = "calphysician='".intval($__phy)."' AND ".
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
		$criteria = "calphysician='".intval($__phy)."' AND ".
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
