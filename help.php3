<?php
  # file: help.php3
  # note: help module
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL

  $_pg_desc = "[HELP]"; // show that we are a help page...
  include ("global.var.inc");
  include ("freemed-functions.inc");

  freemed_open_db ($LoginCookie);
  freemed_display_html_top ();
  freemed_display_banner ();

 //
 //  eventually, we want to be able to call a
 //  statement like include "doc/$page_name.$section.php3"
 //  or something like that to read documentation in...
 //  because they are coded in plain HTML, they can be
 //  linked.

  // build helpfile name...
if ((strlen($page_name)<1) AND (strlen($section)<1)) {
  $_help_name = "lang/$language/doc/default.$language.html";
} elseif ((strlen($page_name)>0) AND (strlen($section)<1)) {
  $_help_name = "lang/$language/doc/$page_name.$language.html";
} elseif ((strlen($page_name)>0) AND (strlen($section)>0)) {
  $_help_name = "lang/$language/doc/$page_name.$section.$language.html";
} else {
  $_help_name = "lang/$language/doc/default.$language.html";
}

 // if the helpfile doesn't exist, but is enabled, ERROR! out...
if (!file_exists($_help_name)) {
  freemed_display_box_top ("$package_name Help System Error");
  echo "
    <B>The requested help file was not found on this<BR>
       system. It is possible that it has not been<BR>
       implemented, or it is missing from your system.<BR>
    </B>
  ";
  freemed_display_box_bottom ();
  echo "
    <P>
    <CENTER>
    <A HREF=\"help.php3?$_auth\"
    ><$STDFONT_B>Go to the Help Page<$STDFONT_E></A>
    </CENTER>
  "; // link back to the main help page
  DIE("");  // and we bite the big one
} // if the help file does not exist

freemed_display_box_top ("$package_name Help System");

echo "
  <$STDFONT_B>
"; // standard font -- begin

if ($debug) {
  echo "
    page_name = $page_name<BR>
    section = $section<BR>
  ";
} // debug stuff

include ($_help_name); // include the actual help text

freemed_display_box_bottom (); // end of box

echo "
  <$STDFONT_E>
  <P>
  <CENTER>
  <$STDFONT_B><B>If this is in a \"child window\",<BR>
  please close it or minimize it to<BR>
  return to </B><$STDFONT_E>
  </CENTER>
";

freemed_close_db (); // close db after user authentication

freemed_display_html_bottom (); // show ending bit...

?>
