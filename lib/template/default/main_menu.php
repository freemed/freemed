<?php
 // $Id$
 // $Author$
 // note: main menu module
 // lic : GPL

$page_name = "main.php";
include ("lib/freemed.php");
include ("lib/API.php");

//----- Add page to page history list
page_push ();

//---- DB and authenticate
freemed_open_db ();

//----- Create user object
if (!is_object($this_user)) $this_user = new User;

//---- Set page title
$page_title = PACKAGENAME." "._("Main Menu");

$display_buffer .= "
  <P>

  <TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=2 CELLPADDING=0 VALIGN=MIDDLE
   ALIGN=CENTER>
 "; // standard font begin

 if ($this_user->getLevel() > $admin_level)
   $display_buffer .= "
     <TR>
     <TD ALIGN=RIGHT>
     <A HREF=\"admin.php\"
      ><IMG SRC=\"img/KeysOnChain.gif\" BORDER=0
        ALT=\"\"></TD>
     <TD ALIGN=LEFT>
     <A HREF=\"admin.php\"
      >"._("Administration Menu")."</A>
     </A>
     </TD></TR>
   ";

 if ($this_user->getLevel() > $database_level)
   $display_buffer .= "
    <TR>
    <TD ALIGN=RIGHT>
     <A HREF=\"billing_functions.php\"
     ><IMG SRC=\"img/CashRegister.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"billing_functions.php\"
     >"._("Billing Functions")."</A>
    </TD></TR>
   ";

 $display_buffer .= "
   <TR>
   <TD ALIGN=RIGHT>
    <A HREF=\"call-in.php\"
    ><IMG SRC=\"img/Text.gif\" BORDER=0 ALT=\"\"></A>
   </TD>
   <TD ALIGN=LEFT>
   <B>"._("Call In")." : &nbsp;</B>
   <A HREF=\"call-in.php?action=addform\"
    >"._("Entry")."</A> |
   <A HREF=\"call-in.php\"
    >"._("Menu")."</A>
   </TD></TR>
 ";

 if ($this_user->getLevel() > $database_level)
   $display_buffer .= "
    <TR>
    <TD ALIGN=RIGHT>
     <A HREF=\"db_maintenance.php\"
     ><IMG SRC=\"img/Database.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"db_maintenance.php\"
     >"._("Database Maintenance")."</A>
    </TD></TR>
   ";

 if ($this_user->isPhysician())
   $display_buffer .= "
    <TR>
    <TD ALIGN=RIGHT>
     <A HREF=\"physician_day_view.php?physician=".
      $this_user->getPhysician()."\"
     ><IMG SRC=\"img/karm.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"physician_day_view.php?physician=".
      $this_user->getPhysician()."\"
     >"._("Day View")."</A><BR>
    <A HREF=\"physician_week_view.php?physician=".
      $this_user->getPhysician()."\"
     >"._("Week View")."</A>
    </TD></TR>
   ";

 if ($this_user->getLevel() > $database_level)
   $display_buffer .= "
    <TR> 
    <TD ALIGN=RIGHT>
     <A HREF=\"patient.php\"
     ><IMG SRC=\"img/HandOpen.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"patient.php\"
     >"._("Patient Functions")."</A>
    </TD></TR>
   ";

 if ($this_user->getLevel() > $database_level)
   $display_buffer .= "
    <TR> 
    <TD ALIGN=RIGHT>
     <A HREF=\"reports.php\"
     ><IMG SRC=\"img/reports.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"reports.php\"
     >"._("Reports")."</A>
    </TD></TR>
   ";

 if ($this_user->getLevel() > $database_level)
   $display_buffer .= "
    <TR> 
    <TD ALIGN=RIGHT>
     <A HREF=\"calendar.php\"
     ><IMG SRC=\"img/clock.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"calendar.php\"
     >"._("Calendar")."</A>
    </TD></TR>
   ";

    // help screen
$display_buffer .= "
  <TR>
  <TD ALIGN=RIGHT>
   <A HREF=\"help.php?page_name=$page_name\"
    TARGET=\"__HELP__\"
   ><IMG SRC=\"img/readme.gif\" BORDER=0 ALT=\"\"></A>
  </TD>
  <TD ALIGN=LEFT>
  <A HREF=\"help.php?page_name=$page_name\"
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

?>
