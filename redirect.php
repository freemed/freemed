<?php
 // $Id$
 // note: redirector for /DIS, etc...
 // lic : GPL

 include ("lib/freemed.php");
 include ("lib/API.php");

 // then we do an if... else clause to loop through
 // any other instances, and put them here....

 // at some point, we should document this table, for ease of
 // use in jumping from place A to place B, and eventually put
 // it on the tool bar.

/*
switch ($location) {
  case "/aicd": $redirect="icd9.php?action=addform"; break;
  case "/cfg":  $redirect="admin.php?action=cfgform"; break;
  case "/dis":  $display_buffer .= "not yet"; break;
  case "/icd":  $redirect="icd9.php?action=view"; break;
  case "/init": $redirect="admin.php?action=reinit"; break;
  case "/npat": $redirect="patient.php?action=addform"; break;
  case "/nphy": $redirect="physician.php?action=addform"; break;
  case "/pat":  $redirect="patient.php?action=view";
  case "/phy":  $redirect="physician.php?action=view"; break;
  default:      $redirect="main.php"; break;
}
*/

  // and now the actual redirector
Header("Location: $location");

$display_buffer .= "<HTML>
<HEAD>
<TITLE>redirector</TITLE>
<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0;URL=$location\"> 
</HEAD> 
<BODY>
<CENTER><B>"._("If your browser does not support the REFRESH tag")."
<A HREF=\"$redirect\">"._("click here")."</A>.</B>
</CENTER>
</BODY></HTML>
";

?>
