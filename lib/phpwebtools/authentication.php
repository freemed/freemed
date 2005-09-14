<?php
  // $Id$
  // desc: authentication routines
  // code: jeff b (jeff@ourexchange.net)
  // lic : LGPL

if (!defined("__AUTHENTICATION_PHP__")) {

define ('__AUTHENTICATION_PHP__', true);

function basic_authentication ($realm, $_users_array,
                               $access_denied = "Access denied.\n") {
	global $PHP_AUTH_USER, $PHP_AUTH_PW, $PHP_SELF, $LOGOUT;
	$users_array = flatten_array ($_users_array);
	reset ($users_array);

	if ( (!isset($PHP_AUTH_USER)) or isset($LOGOUT) ) {
    	Header("WWW-Authenticate: Basic realm=\"".
			prepare($realm)." ".time()."\"");
    	Header("HTTP/1.0 401 Unauthorized");
    	die($access_denied);
	} else {
		if ( empty($users_array["$PHP_AUTH_USER"]) or
			($users_array["$PHP_AUTH_USER"] != $PHP_AUTH_PW) ) {
			Header("Location: ".$PHP_SELF."?LOGOUT=1");
			exit();
		}
	}
	return true;
		
/*
  if ((!isset($PHP_AUTH_USER)) or
      ($users_array["$PHP_AUTH_USER"] != $PHP_AUTH_PW) or
      (empty($users_array["$PHP_AUTH_USER"]))) {
	SetCookie("NoAuthenticate", "");
    Header("WWW-Authenticate: Basic realm=\"".prepare($realm)." ".time()."\"");
    Header("HTTP/1.0 401 Unauthorized");
    die($access_denied);
  } else {
    return true;
  }
*/
} // end function basic_authentication

} // end checking if defined

?>
