<?php
 // $Id$
 // note: sets name/password cookie
 // lic : GPL
 // code: jeff b <jeff@univrel.pr.uconn.edu>, max k <amk@span.ch>

$page_name = "authenticate.php" ;
include ("lib/freemed.php");
include ("lib/API.php");

$connect = freemed_auth_login ($_u, $_p);
if (!$connect) {
    freemed_display_html_top ();
    freemed_display_banner ();
    if (!empty($_URL)) $__url_part = "?_URL=".urlencode($_URL);
    echo "
      <TABLE BORDER=1 CELLPADDING=4 VALIGN=CENTER ALIGN=CENTER WIDTH=100%
       BGCOLOR=\"#cccccc\">
      <TR><TD ALIGN=CENTER>
       <P>
       <$HEADERFONT_B>"._("Error")." !<$HEADERFONT_E>
       <P>
       <$STDFONT_B>
       "._("You have entered an incorrect name or password.")."
       <$STDFONT_E>
       <P>
       <CENTER><$STDFONT_B><A HREF=\"".COMPLETE_URL."$__url_part\"
        >"._("Return to the login screen")."</A><$STDFONT_E></CENTER>
       <P>
      </TD></TR>
      </TABLE>
    ";
    freemed_display_html_bottom ();
    DIE("");
}

$f_user = explode (":", $SessionLoginCookie); 
SetCookie("_ref",   "",  time()+$_cookie_expire); // clear _ref
SetCookie("u_lang", $_l, time()+$_cookie_expire);

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

echo "
    <HTML>
    <HEAD>
     <TITLE>authentication for ".PACKAGENAME."</TITLE>
     <META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0;URL=$_jump_page\">
    </HEAD>
    <BODY BGCOLOR=\"#ffffff\">
  ";
freemed_display_banner ();
freemed_display_box_top (_("Authenticating")." ... ");
echo "
      <P>
      <CENTER>
        <B>"._("If your browser does not support the REFRESH tag")."
        <A HREF=\"$_jump_page\">"._("click here")."</A></B>
      </CENTER>
      <P>
";
freemed_display_box_bottom ();

?>
