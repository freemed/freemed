<?php
 // $Id$
 // note: sets name/password cookie
 // lic : GPL
 // code: jeff b <jeff@ourexchange.net>, max k <amk@span.ch>

$page_name = "authenticate.php" ;
include_once ("lib/freemed.php");

//----- Clear old session information
unset($_SESSION['authdata']);

//----- Disable menu bar
$GLOBALS['__freemed']['no_menu_bar'] = true;

//Fred Trotter
// The firstboot sequence has been taken care of by healthcheck
// That handles intial admin creation!!

//$connect = freemed_auth_login ($_username, $_password);
$connect = freemed::verify_auth ();
if (!$connect) {
    if (!empty($_URL)) $__url_part = "?_URL=".urlencode($_URL);
    $display_buffer .= "
       <div ALIGN=\"CENTER\">".__("ERROR")."!</div>
       <p/>
       <div ALIGN=\"CENTER\">".__("You have entered an incorrect name or password.")."</div>
       <p/>
       <div ALIGN=\"CENTER\"><a HREF=\"index.php$__url_part\"
        >".__("Return to the login screen")."</a></div>
    ";
    template_display();
}

if (freemed::check_access_for_facility ($_f)) {
	SetCookie('default_facility', $_f);
	$_COOKIE['default_facility'] = $_SESSION['default_facility'] = $_f;
} else {
	SetCookie('default_facility', 0);
	$_COOKIE['default_facility'] = $_SESSION['default_facility'] = 0;
}

//----- Determine "language session variable, if set
//if ($_l != DEFAULT_LANGUAGE) {
$_SESSION['language'] = $_l;
SetCookie('language', $_l);
//}

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
$page_title = __("Authenticating")." ... ";

$display_buffer .= "
      <p/>
      <div ALIGN=\"CENTER\">
        <b>".__("If your browser does not support the REFRESH tag")."
        <a HREF=\"$_jump_page\">".__("click here")."</a></b>
      </div>
      <p/>
";

//----- Load the template
template_display();
//print "session[language] = ".$_SESSION['language'].", s_lng = $s_lng<BR>\n";

?>
