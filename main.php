<?php
 // $Id$
 // note: main menu module
 // code: jeff b (jeff@univrel.pr.uconn.edu), max k <amk@span.ch>
 // lic : GPL

 $page_name="main.php";
 include ("lib/freemed.php");
 include ("lib/API.php");

   // checking for _ref tag.... (19990607) 
 if ((strlen($_ref)>0) AND ($_ref != "main.php")) {
   SetCookie("_ref", "main.php", time()+$_cookie_expire);
      // set _ref cookie to be current menu...
 } // if there is a _ref cookie...

 freemed_open_db ($LoginCookie);
 $this_user = new User ($LoginCookie);

 freemed_display_html_top ();
 freemed_display_banner ();

freemed_display_box_top(PACKAGENAME." "._("Main Menu"));

echo "
  <P>

  <$STDFONT_B>
  <TABLE WIDTH=100% BORDER=0 CELLSPACING=2 CELLPADDING=0 VALIGN=CENTER
   ALIGN=CENTER>
 "; // standard font begin

 if ($this_user->getLevel() > $admin_level)
   echo "
     <TR>
     <TD ALIGN=RIGHT BGCOLOR=#dddddd>
     <A HREF=\"admin.php?$_auth\"
      ><IMG SRC=\"img/KeysOnChain.gif\" BORDER=0
        ALT=\"\"></TD>
     <TD ALIGN=LEFT>
     <A HREF=\"admin.php?$_auth\"
      >"._("Administration Menu")."</A>
     </A>
     </TD></TR>
   ";

 if ($this_user->getLevel() > $database_level)
   echo "
    <TR>
    <TD ALIGN=RIGHT>
     <A HREF=\"billing_functions.php?$_auth\"
     ><IMG SRC=\"img/CashRegister.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"billing_functions.php?$_auth\"
     >"._("Billing Functions")."</A>
    </TD></TR>
   ";

 echo "
   <TR>
   <TD ALIGN=RIGHT BGCOLOR=#dddddd>
    <A HREF=\"call-in.php?$_auth\"
    ><IMG SRC=\"img/Text.gif\" BORDER=0 ALT=\"\"></A>
   </TD>
   <TD ALIGN=LEFT>
   <B>"._("Call In")." : &nbsp;</B>
   <A HREF=\"call-in.php?$_auth&action=addform\"
    >"._("Entry")."</A> |
   <A HREF=\"call-in.php?$_auth\"
    >"._("Menu")."</A>
   </TD></TR>
 ";

 if ($this_user->getLevel() > $database_level)
   echo "
    <TR>
    <TD ALIGN=RIGHT BGCOLOR=\"#dddddd\">
     <A HREF=\"db_maintenance.php?$_auth\"
     ><IMG SRC=\"img/Database.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"db_maintenance.php?$_auth\"
     >"._("Database Maintenance")."</A>
    </TD></TR>
   ";

 if ($this_user->isPhysician())
   echo "
    <TR>
    <TD ALIGN=RIGHT BGCOLOR=#dddddd>
     <A HREF=\"physician_day_view.php?$_auth&physician=".
      $this_user->getPhysician()."\"
     ><IMG SRC=\"img/karm.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"physician_day_view.php?$_auth&physician=".
      $this_user->getPhysician()."\"
     >"._("Day View")."</A><BR>
    <A HREF=\"physician_week_view.php?$_auth&physician=".
      $this_user->getPhysician()."\"
     >"._("Week View")."</A>
    </TD></TR>
   ";

 if ($this_user->getLevel() > $database_level)
   echo "
    <TR> 
    <TD ALIGN=RIGHT BGCOLOR=#dddddd>
     <A HREF=\"patient.php?$_auth\"
     ><IMG SRC=\"img/HandOpen.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"patient.php?$_auth\"
     >"._("Patient Functions")."</A>
    </TD></TR>
   ";

    // help screen
echo "
  <TR>
  <TD ALIGN=RIGHT>
   <A HREF=\"help.php3?$_auth&page_name=$page_name\"
    TARGET=\"__HELP__\"
   ><IMG SRC=\"img/readme.gif\" BORDER=0 ALT=\"\"></A>
  </TD>
  <TD ALIGN=LEFT>
  <A HREF=\"help.php3?$_auth&page_name=$page_name\"
   TARGET=\"__HELP__\">"._("Main Menu Help")."</A>
  </TD></TR>
  <TR>
  <TD ALIGN=RIGHT>
  </TD>
  <TD ALIGN=LEFT>
  <B><A HREF=\"logout.php\">"._("Logout of")." ".PACKAGENAME."</A>
  </B>
  </TD></TR>
  </TABLE>
";

// redirection "Quickjump" box 
echo "
<FORM ACTION=\"redirect.php?$_auth\">
  <CENTER>
  <B><FONT SIZE=-1>"._("Quickjump")."</FONT></B><BR>
  <INPUT TYPE=TEXT NAME=\"location\" VALUE=\"/\" SIZE=5>
  </CENTER>
</FORM>
";

echo "<$STDFONT_E>"; // end standard font

// this should always be shown
//if ($debug)
  echo "
    </TD></TR>
    <TR><TD>
     <$STDFONT_B SIZE=-1>
     "._("User level").": ".$this_user->getLevel()."
     <$STDFONT_E>
      &nbsp;<B>|</B>&nbsp;
     <$STDFONT_B SIZE=-1>
     "._("User description").": ".prepare($this_user->getDescription())."
     <$STDFONT_E>
    </TD></TR>
    <TR><TD>
  ";

freemed_display_box_bottom();
freemed_display_html_bottom();
freemed_close_db();
?>
