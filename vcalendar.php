<?php
	// $Id$
	// $Author$

//----- Load neccesary headers
define ('SESSION_DISABLE', true);
include_once ("lib/freemed.php");

//----- Define freemed authorization
function freemed_basic_auth () {
	global $sql;
	//----- Check for authentication
	$headers = getallheaders(); $authed = false;
	if (ereg('Basic', $headers['Authorization'])) {
		// Parse headers
		$tmp = $headers['Authorization'];
		$tmp = ereg_replace(' ', '', $tmp);
		$tmp = ereg_replace('Basic', '', $tmp);
		$auth = base64_decode(trim($tmp));
		list ($user, $pass) = split(':', $auth);
	
		// Check for username/password
		$query = "SELECT username, userpassword, userrealphy, id FROM user ".
			"WHERE username='".addslashes($user)."' AND ".
			"userpassword=MD5('".addslashes($pass)."')";
		$result = $sql->query($query);

		if (@$sql->num_rows($result) == 1) {
			$authed = true;
			$r = $sql->fetch_array($result);
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
	syslog(LOG_INFO, "XMLRPC [get] username = ".$_GET['user']);
	$query = "SELECT username, userpassword, userrealphy, id FROM user ".
		"WHERE username='".addslashes($_GET['user'])."' AND ".
		"userpassword='".addslashes($_GET['hash'])."'";
	$result = $sql->query($query);
	if (@$sql->num_rows($result) == 1) {
		$authed = true;
		$r = $sql->fetch_array($result);
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
		$physician = CreateObject('FreeMED.Physician', $__phy);
		$name = $physician->fullName();
		$criteria = "calphysician='".addslashes($__phy)."' AND ".
			"caldateof >= '".date("Y-m-d", $ts)."'";
		$stamp = date("Ymd", $ts) . '.' . $__phy;
	} else {
		die('Not enough information provided.');
	}
	break;

	default:
	if ($__phy > 0) {
		// Assume that it's for a physician
		$physician = CreateObject('FreeMED.Physician', $__phy);
		$name = $physician->fullName();
		$criteria = "calphysician='".addslashes($__phy)."' AND ".
			"caldateof >= '".date("Y-m-d")."'";
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
$v = CreateObject('FreeMED.vCalendar', $name, $criteria);

// Output the information
echo $v->generate();

?>
