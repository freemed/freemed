<?php
 // $Id$
 // $Author$
 // note: main menu module
 // lic : GPL

$page_name = "main.php";
include_once ("lib/freemed.php");

//----- Add page to page history list
page_push ();

//---- DB and authenticate
freemed_open_db ();

//----- Create user object
if (!is_object($this_user)) $this_user = CreateObject('FreeMED.User');

//---- Set page title
$page_title = PACKAGENAME." ".__("Main Menu");

// Check for new messages
if ($new_messages = $this_user->newMessages()) {
	$display_buffer .= "
		<div align=\"center\" valign=\"MIDDLE\" class=\"infobox\">
		<img src=\"img/messages_small.gif\" alt=\"\" ".
		"width=\"16\" height=\"16\" border=\"0\"/>
		<a HREF=\"messages.php\"
		>".sprintf(__("You have %d new message(s)."), $new_messages).
		"</a>
		<img src=\"img/messages_small.gif\" ALT=\"\" ".
		"WIDTH=\"16\" HEIGHT=\"16\" BORDER=\"0\"/>
		</div>
	";
}

// Header for main table
$display_buffer .= "
<p/>

<table WIDTH=\"100%\" BORDER=0 CELLSPACING=2 CELLPADDING=0 VALIGN=MIDDLE
 ALIGN=\"CENTER\">
";

if (freemed::user_flag(USER_ADMIN))
   $display_buffer .= "
     <TR>
     <TD ALIGN=RIGHT>
     <A HREF=\"admin.php\"
      ><IMG SRC=\"img/KeysOnChain.gif\" BORDER=0
        ALT=\"\"></TD>
     <TD ALIGN=LEFT>
     <A HREF=\"admin.php\"
      >".__("Administration Menu")."</A>
     </A>
     </TD></TR>
   ";

if (freemed::user_flag(USER_DATABASE))
   $display_buffer .= "
    <TR>
    <TD ALIGN=RIGHT>
     <A HREF=\"billing_functions.php\"
     ><IMG SRC=\"img/CashRegister.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"billing_functions.php\"
     >".__("Billing Functions")."</A>
    </TD></TR>
   ";

 $display_buffer .= "
   <TR>
   <TD ALIGN=RIGHT>
    <A HREF=\"call-in.php\"
    ><IMG SRC=\"img/Text.gif\" BORDER=0 ALT=\"\"></A>
   </TD>
   <TD ALIGN=LEFT>
   <B>".__("Call In")." : &nbsp;</B>
   <A HREF=\"call-in.php?action=addform\"
    >".__("Entry")."</A> |
   <A HREF=\"call-in.php\"
    >".__("Menu")."</A>
   </TD></TR>
 ";

 if (freemed::user_flag(USER_DATABASE))
   $display_buffer .= "
    <TR>
    <TD ALIGN=RIGHT>
     <A HREF=\"db_maintenance.php\"
     ><IMG SRC=\"img/Database.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"db_maintenance.php\"
     >".__("Database Maintenance")."</A>
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
     >".__("Day View")."</A><BR>
    <A HREF=\"physician_week_view.php?physician=".
      $this_user->getPhysician()."\"
     >".__("Week View")."</A>
    </TD></TR>
   ";

$display_buffer .= "
    <TR>
    <TD ALIGN=RIGHT>
     <A HREF=\"messages.php\"
     ><IMG SRC=\"img/messages.gif\" BORDER=0 WIDTH=48 HEIGHT=48 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"messages.php\">".__("Messages")."</A>
    </TD></TR>
";

if (freemed::user_flag(USER_DATABASE))
   $display_buffer .= "
    <TR> 
    <TD ALIGN=RIGHT>
     <A HREF=\"patient.php\"
     ><IMG SRC=\"img/HandOpen.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"patient.php\"
     >".__("Patient Functions")."</A>
    </TD></TR>
   ";

 if (freemed::user_flag(USER_DATABASE))
   $display_buffer .= "
    <TR> 
    <TD ALIGN=RIGHT>
     <A HREF=\"reports.php\"
     ><IMG SRC=\"img/reports.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"reports.php\"
     >".__("Reports")."</A>
    </TD></TR>
   ";

 if (freemed::user_flag(USER_DATABASE))
   $display_buffer .= "
    <TR> 
    <TD ALIGN=RIGHT>
     <A HREF=\"calendar.php\"
     ><IMG SRC=\"img/clock.gif\" BORDER=0 ALT=\"\"></A>
    </TD>
    <TD ALIGN=LEFT>
    <A HREF=\"calendar.php\"
     >".__("Calendar")."</A>
    </TD></TR>
   ";

    // help screen
$display_buffer .= "
  <TR>
  <TD ALIGN=RIGHT>
   <A HREF=\"#\"
   onClick=\"window.open('help.php?page_name=$page_name', 'Help', ".
   "'width=600,height=400,resizable=yes');\"
   ><IMG SRC=\"img/readme.gif\" BORDER=0 ALT=\"\"></A>
  </TD>
  <TD ALIGN=LEFT>
   <A HREF=\"#\"
   onClick=\"window.open('help.php?page_name=$page_name', 'Help', ".
   "'width=600,height=400,resizable=yes');\"
   >".__("Main Menu Help")."</A>
  </TD></TR>
  <TR>
  <TD ALIGN=RIGHT>
  </TD>
  <TD ALIGN=LEFT>
  <B><A HREF=\"logout.php\">".__("Logout of")." ".PACKAGENAME."</A>
  </B>
  </TD></TR>
  </TABLE>
";

?>
