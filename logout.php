<?php
 // $Id$
 // note: sets name/password cookie to null...
 // lic : GPL, v2

$page_name = 'logout.php';
include_once ('lib/freemed.php');

// Header("Location: ".COMPLETE_URL); // 19990610 - header instead
//if (strlen($_URL)>0) $__url_part = "?_URL=".urlencode($_URL);

//----- Destroy authdata and ipaddr from session
unset($SESSION['authdata']);
unset($SESSION['ipaddr']);

//----- Set template pieces
$refresh = "index.php";
$GLOBALS['__freemed']['no_menu_bar'] = true;
$page_title = _("Logging Out ... ");

$display_buffer .= "
      <p/>
      <div ALIGN=\"CENTER\">
        <b>"._("If your browser does not support the REFRESH tag")."
        <a HREF=\"".$refresh."\">"._("click here")."</a>.</b>
      </div>
      <p/>
";

//----- Display the template
template_display();

?>
