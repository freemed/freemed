<?php
 // $Id$
 // note: sets name/password cookie to null...
 // lic : GPL, v2

  $page_name = "logout.php";
  include ("lib/freemed.php");

    // expire all of the cookies
  SetCookie("LoginCookie",      " ", time()+0);
  SetCookie("_ref",             " ", time()+0);
  SetCookie("u_lang",           " ", time()+0);
  SetCookie("default_facility", "0", time()+0);
  SetCookie("current_patient",  "0", time()+0);

  # Header("Location: $complete_url"); // 19990610 - header instead
  include ("lib/API.php");

  if (strlen($_URL)>0) $__url_part = "?_URL=".urlencode($_URL);
  echo "
    <HTML>
    <HEAD>
     <TITLE>"._("Logout of")." ".PACKAGENAME."</TITLE>
     <META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0;URL=$base_url$__url_part\">
    </HEAD>
    <BODY BGCOLOR=#ffffff>
  ";
  freemed_display_banner ();
  freemed_display_box_top (_("Logging Out ... "));
  echo "
      <P>
      <CENTER>
        <B>"._("If your browser does not support the REFRESH tag")."
        <A HREF=\"$base_url\">"._("click here")."</A>.</B>
      </CENTER>
      <P>
  ";
  freemed_display_box_bottom ();
  freemed_display_html_bottom ();

?>
