<?php
 // $Id$
 // note: sets name/password cookie to null...
 // lic : GPL, v2

$page_name = 'logout.php';
include_once ('lib/freemed.php');

// Header("Location: ".COMPLETE_URL); // 19990610 - header instead
//if (strlen($_URL)>0) $__url_part = "?_URL=".urlencode($_URL);

//----- Destroy authdata and ipaddr from session
//unset($_SESSION['authdata']);
//unset($_SESSION['ipaddr']);
$_SESSION['authdata'] = NULL;
$_SESSION['ipaddr'] = NULL;
$_SESSION['language'] = NULL;
SetCookie('language', '');

//----- Set template pieces
$refresh = "index.php";
$GLOBALS['__freemed']['no_menu_bar'] = true;
$page_title = __("Logging Out ... ");

$display_buffer .= "
      <p/>
      <div ALIGN=\"CENTER\">
        <b>".__("If your browser does not support the REFRESH tag")."
        <a HREF=\"".$refresh."\">".__("click here")."</a>.</b>
      </div>
      <p/>
";

//----- Display the template
template_display();

?>
