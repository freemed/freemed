<?php
 // $Id$
 // $Author$
 // note: redirector for /DIS, etc...
 // lic : GPL

include ("lib/freemed.php");

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

$display_buffer .= "<html>
<head>
<title>redirector</TITLE>
<meta HTTP-EQUIV=\"REFRESH\" CONTENT=\"0;URL=$location\"> 
</head> 
<body>
<div ALIGN=\"CENTER\"><b>".__("If your browser does not support the REFRESH tag")."
<a HREF=\"$redirect\">".__("click here")."</a>.</b></div>
</body></html>
";

?>
