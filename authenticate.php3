<?php
  # file: authenticate.php3
  # note: sets name/password cookie
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL
  # modif for languages max k <amk@span.ch> <19990807>

  $page_name = "authenticate.php3" ;
  include ("global.var.inc");
  include ("freemed-functions.inc");

  $connect = freemed_auth_login ($_u, $_p);
  if (!$connect) {
    freemed_display_html_top ();
    freemed_display_banner ();
    if (strlen($_URL)>0) $__url_part = "?_URL=".urlencode($_URL);
    echo "
      <TABLE BORDER=1 CELLPADDING=4 VALIGN=CENTER ALIGN=CENTER WIDTH=60%
       BGCOLOR=#cccccc>
      <TR><TD>
       <P>
       <$HEADERFONT_B>$Error !<$HEADERFONT_E>
       <P>
       <$STDFONT_B>$Incorrect_name_password <$STDFONT_E>
       <P>
       <CENTER><$STDFONT_B><A HREF=\"$complete_url$__url_part\"
        >$Return_to_the_login_screen </A><$STDFONT_E></CENTER>
       <P>
      </TD></TR>
      </TABLE>
    ";
    freemed_display_html_bottom ();
    DIE("");
  }

  $f_user = explode (":", $SessionLoginCookie); 
  SetCookie("_ref", "",  time()+$_cookie_expire); // clear _ref
  SetCookie("u_lang", $_l, time()+$_cookie_expire);

  if (freemed_check_access_for_facility ($SessionLoginCookie, $_f))
    SetCookie("default_facility", $_f, time()+$_cookie_expire);

  # Header("Location: $complete_url/main.php3");

  $_jump_page = "main.php3"; // by default, go to the main page
  $_URL = ereg_replace ("$base_url/", "", $_URL);
  if (!empty($_URL)) {
    if (strpos($_URL, "?")!=false) {
      $_URL_a = explode ("?", $_URL); // split by ?
      $_page  = $_URL_a[0];           // first element of array 
    } else {
      $_page = $_URL;                 // otherwise, the whole thing
    } // end checking for ? in $_URL
    //if ( (is_file($_page)) or 
    //   ( (is_link($_page)) and (is_file(readlink($_page))) ))
     $_jump_page = $_URL;  // if it's there, let 'em jump to it 
  } // end checking for $_URL

  echo "
    <HTML>
    <HEAD>
     <TITLE>authentication for $packagename</TITLE>
     <META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0;URL=$_jump_page\">
    </HEAD>
    <BODY BGCOLOR=#ffffff>
  ";
  freemed_display_banner ();
  freemed_display_box_top ("Authenticating ... ");
  echo "
      <P>
      <CENTER>
        <B>If your browser does not support the REFRESH tag, click
        <A HREF=\"$_jump_page\">here</A>.</B>
      </CENTER>
      <P>
  ";
  freemed_display_box_bottom ();

?>
