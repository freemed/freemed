<?php
 // file: redirect.php3
 // note: redirector for /DIS, etc...
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL

 include ("freemed-functions.inc"); // API functions (19990610)

 // then we do an if... else clause to loop through
 // any other instances, and put them here....

 // at some point, we should document this table, for ease of
 // use in jumping from place A to place B, and eventually put
 // it on the tool bar.
switch ($location) {
  case "/aicd": $redirect="icd9.php3?$_auth&action=addform"; break;
  case "/cfg":  $redirect="admin.php3?$_auth&action=cfgform"; break;
  case "/dis":  echo "not yet"; break;
  case "/icd":  $redirect="icd9.php3?$_auth&action=view"; break;
  case "/init": $redirect="admin.php3?$_auth&action=reinit"; break;
  case "/npat": $redirect="patient.php3?$_auth&action=addform"; break;
  case "/nphy": $redirect="physician.php3?$_auth&action=addform"; break;
  case "/pat":  $redirect="patient.php3?$_auth&action=view";
  case "/phy":  $redirect="physician.php3?$_auth&action=view"; break;
  default:      $redirect="main.php3?$_auth"; break;
}

  // and now the actual redirector

Header("Location: $complete_url$redirect");

echo "<HTML>
<HEAD>
<TITLE>redirector</TITLE>
<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0;URL=$complete_url$redirect\"> 
</HEAD> 
<BODY>
<CENTER><B>"._("If your browser does not support the REFRESH tag")."
<A HREF=\"$redirect\">"._("click here")."</A>.</B>
</CENTER>
</BODY></HTML>
";

?>
