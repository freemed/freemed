<?php
 // $Id$
 // note: sets name/password cookie
 // lic : GPL
 // code: jeff b <jeff@ourexchange.net>, max k <amk@span.ch>

$page_name = "authenticate.php" ;
include_once ("lib/freemed.php");

//----- Disable menu bar
$GLOBALS['__freemed']['no_menu_bar'] = true;

//----- First, make sure that password is correct if updated
// Get root password
$result = $sql->query ( "SELECT userpassword FROM user WHERE id='1'");
// If there are results, process (exception for first "boot")
if ($sql->results($result)) {
	// Extract information
	$r = $sql->fetch_array($query);

	// If it doesn't match...
	if ((stripslashes($r['userpassword']) != DB_PASSWORD) and ($r['username']=="root")) {
		// ... execute update query to *make* it match.
		$update_result = $sql->query($sql->update_query(
			"user",
			array ( "userpassword" => DB_PASSWORD ),
			array ( "id" => 1 )
		));
	} // end checking for matching
} // end checking for results

//$connect = freemed_auth_login ($_username, $_password);
$connect = freemed::verify_auth ();
if (!$connect) {
    if (!empty($_URL)) $__url_part = "?_URL=".urlencode($_URL);
    $display_buffer .= "
       <div ALIGN=\"CENTER\">"._("Error")." !</div>
       <p/>
       <div ALIGN=\"CENTER\">"._("You have entered an incorrect name or password.")."</div>
       <p/>
       <div ALIGN=\"CENTER\"><a HREF=\"index.php$__url_part\"
        >"._("Return to the login screen")."</a></div>
    ";
    template_display();
}

if (freemed_check_access_for_facility ($_f)) {
	SetCookie('default_facility', $_f);
	$_COOKIE['default_facility'] = $_SESSION['default_facility'] = $_f;
} else {
	SetCookie('default_facility', 0);
	$_COOKIE['default_facility'] = $_SESSION['default_facility'] = 0;
}

//----- Determine "language session variable, if set
if ($_l != $default_language) {
	$_SESSION['language'] = $_l;
}

// Header("Location: ".COMPLETE_URL."/main.php");

$_jump_page = "main.php"; // by default, go to the main page
$_URL = ereg_replace (BASE_URL."/", "", $_URL);
if (!empty($_URL)) {
	if (strpos($_URL, "?") != false) {
		$_URL_a = explode ("?", $_URL); // split by ?
		$_page  = $_URL_a[0];           // first element of array 
	} else {
		$_page = $_URL;                 // otherwise, the whole thing
	} // end checking for ? in $_URL
	$_jump_page = $_URL;  // if it's there, let 'em jump to it 
} // end checking for $_URL

//----- Set refresh properly
$refresh = $_jump_page;

//----- Set page title
$page_title = _("Authenticating")." ... ";

$display_buffer .= "
      <p/>
      <div ALIGN=\"CENTER\">
        <b>"._("If your browser does not support the REFRESH tag")."
        <a HREF=\"$_jump_page\">"._("click here")."</a></b>
      </div>
      <p/>
";

//----- Load the template
template_display();
//print "session[language] = ".$_SESSION['language'].", s_lng = $s_lng<BR>\n";

?>
