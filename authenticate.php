<?php
 // $Id$
 // note: sets name/password cookie
 // lic : GPL
 // code: jeff b <jeff@univrel.pr.uconn.edu>, max k <amk@span.ch>

$page_name = "authenticate.php" ;
include ("lib/freemed.php");
include ("lib/API.php");

//----- Disable menu bar
$no_menu_bar = true;

//$connect = freemed_auth_login ($_username, $_password);
$connect = freemed_verify_auth ();
if (!$connect) {
    if (!empty($_URL)) $__url_part = "?_URL=".urlencode($_URL);
    $display_buffer .= "
       <CENTER>"._("Error")." !</CENTER>
       <P>
       <CENTER>"._("You have entered an incorrect name or password.")."</CENTER>
       <P>
       <CENTER><A HREF=\"index.php$__url_part\"
        >"._("Return to the login screen")."</A></CENTER>
    ";
    template_display();
}

//$f_user = explode (":", $SessionLoginCookie); 
//SetCookie("_ref",   "",  time()+$_cookie_expire); // clear _ref
//SetCookie("u_lang", $_l, time()+$_cookie_expire);

if (freemed_check_access_for_facility ($SessionLoginCookie, $_f)) {
	SetCookie("default_facility", "$_f", time()+$_cookie_expire);
} else {
	SetCookie("default_facility", "0"  , time()+$_cookie_expire);
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

/*
$display_buffer .= "
    <HTML>
    <HEAD>
     <TITLE>authentication for ".PACKAGENAME."</TITLE>
     <META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0;URL=$_jump_page\">
    </HEAD>
    <BODY BGCOLOR=\"#ffffff\">
  ";
*/

//----- Set refresh properly
$refresh = $_jump_page;

//----- Set page title
$page_title = _("Authenticating")." ... ";

$display_buffer .= "
      <P>
      <CENTER>
        <B>"._("If your browser does not support the REFRESH tag")."
        <A HREF=\"$_jump_page\">"._("click here")."</A></B>
      </CENTER>
      <P>
";

//----- Load the template
template_display();

?>
