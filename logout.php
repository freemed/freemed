<?php
 // $Id$
 // note: sets name/password cookie to null...
 // lic : GPL, v2

$page_name = "logout.php";
include ("lib/freemed.php");
include ("lib/API.php");

// Header("Location: ".COMPLETE_URL); // 19990610 - header instead
//if (strlen($_URL)>0) $__url_part = "?_URL=".urlencode($_URL);

//----- Destroy authdata and ipaddr from session
unset($SESSION["authdata"]);
unset($SESSION["ipaddr"]);

//----- Set template pieces
$refresh = "index.php";
$no_menu_bar = true;
$page_title = _("Logging Out ... ");

$display_buffer .= "
      <P>
      <CENTER>
        <B>"._("If your browser does not support the REFRESH tag")."
        <A HREF=\"".$refresh."\">"._("click here")."</A>.</B>
      </CENTER>
      <P>
";

template_display();

?>
