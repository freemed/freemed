<?php
 // $Id$
 // $Author$
 // $Log$
 // Revision 1.2  2002/12/29 14:23:46  rufustfirefly
 // Fixed scope problem in FreeMED.Login.* namespace.
 //
 // Revision 1.1  2002/11/27 20:39:57  rufustfirefly
 // New Login class, for method FreeMED.Login.check
 //

class Login {

	function check () {
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
				"userpassword='".addslashes($pass)."'";
			$result = $GLOBALS['sql']->query($query);
	
			if (@$GLOBALS['sql']->num_rows($result) == 1) {
				$authed = true;
				$r = $GLOBALS['sql']->fetch_array($result);
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
			$authed = false;
			$GLOBALS['__freemed']['basic_auth_id'] = 0;
			$GLOBALS['__freemed']['basic_auth_phy'] = 0;
		}
		return $authed;
	} // function check

} // end class Login

?>
