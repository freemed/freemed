<?php
 // $Id$
 // desc: module for call-in patients
 // lic : GPL, v2

$page_name = "call-in.php";          // page name
include ("lib/freemed.php");           // global variables
include ("lib/API.php");    // API calls
$record_name = _("Call In");          // name of record
$db_name = "callin";                  // database name

freemed_open_db ();
$this_user = new User ();

switch ($action) {

 case "addform":
  // Set page title
  $page_title = _("Add")." "._($record_name);

  // Push onto stack
  page_push();

  // ... continue ...
  if (strlen($citookcall)<1) {
    $citookcall = $this_user->getDescription();
  } // if there wasn't one passed to us...
  $display_buffer .= "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2
     VALIGN=CENTER ALIGN=CENTER><TR><TD>

      <!-- form fitting box for both tables -->

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2
     VALIGN=TOP ALIGN=CENTER>
    <TR><TD COLSPAN=2 ALIGN=CENTER>
      <B><FONT COLOR=#000000>"._("Name")."</FONT></B>
    </TD></TR>
    <TR>
     <TD WIDTH=30% ALIGN=RIGHT
     ><FONT COLOR=#444444>"._("Last")."</FONT></TD>
     <TD><INPUT TYPE=TEXT NAME=\"cilname\" SIZE=20 MAXLENGTH=50
          VALUE=\"".prepare($cilname)."\"></TD>
    </TR>
    <TR>
     <TD WIDTH=30% ALIGN=RIGHT
     ><FONT COLOR=#444444>"._("First")."</FONT></TD>
     <TD><INPUT TYPE=TEXT NAME=\"cifname\" SIZE=20 MAXLENGTH=50
          VALUE=\"".prepare($cifname)."\"></TD>
    </TR>
    <TR>
     <TD WIDTH=30% ALIGN=RIGHT
     ><FONT COLOR=#444444>"._("Middle")."</FONT></TD>
     <TD><INPUT TYPE=TEXT NAME=\"cimname\" SIZE=20 MAXLENGTH=50
          VALUE=\"$cimname\"></TD>
    </TR>
    </TABLE>

    </TD><TD>

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 VALIGN=TOP
     ALIGN=CENTER BGCOLOR=\"$darker_bgcolor\">
    <TR><TD COLSPAN=2 ALIGN=CENTER>
     <B><FONT COLOR=#ffffff>"._("Contact Information")."</FONT></B>
    </TD></TR>
    <TR>
     <TD WIDTH=40% ALIGN=RIGHT>
     <FONT COLOR=#cccccc>"._("Home Phone")." &nbsp;
      </FONT></TD>
     <TD><B>(</B> <INPUT TYPE=TEXT NAME=\"cihphone1\" SIZE=4 MAXLENGTH=3
                   VALUE=\"".prepare($cihphone1)."\">
         <B>)</B> <INPUT TYPE=TEXT NAME=\"cihphone2\" SIZE=4 MAXLENGTH=3
                   VALUE=\"".prepare($cihphone2)."\">
         <B>-</B> <INPUT TYPE=TEXT NAME=\"cihphone3\" SIZE=5 MAXLENGTH=4
                   VALUE=\"".prepare($cihphone3)."\">
     </TD>
    </TR>
    <TR>
     <TD WIDTH=40% ALIGN=RIGHT>
     <FONT COLOR=#cccccc>"._("Work Phone")." &nbsp;
      </FONT></TD>
     <TD><B>(</B> <INPUT TYPE=TEXT NAME=\"ciwphone1\" SIZE=4 MAXLENGTH=3
                   VALUE=\"".prepare($ciwphone1)."\">
         <B>)</B> <INPUT TYPE=TEXT NAME=\"ciwphone2\" SIZE=4 MAXLENGTH=3
                   VALUE=\"".prepare($ciwphone2)."\">
         <B>-</B> <INPUT TYPE=TEXT NAME=\"ciwphone3\" SIZE=5 MAXLENGTH=4
                   VALUE=\"".prepare($ciwphone3)."\">
     </TD>
    </TR>
    <TR>
     <TD WIDTH=40% ALIGN=RIGHT>
     <FONT COLOR=#cccccc>"._("Took Call")." &nbsp;
      </FONT></TD>
    <TD>
     <INPUT TYPE=TEXT NAME=\"citookcall\" SIZE=25 MAXLENGTH=50
      VALUE=\"".prepare($citookcall)."\">
    </TD>
    </TR>
    </TABLE>

     <!-- now, end of form fitting table... -->
    </TD></TR></TABLE>

    <P>
    ";
    
    $fac_r = $sql->query("SELECT * FROM facility ORDER BY psrname,psrnote");
    if (!isset($cifacility)) $cifacility=$default_facility; 
      // doesn't seem to hurt, but doesn't seem to do anything...
   
    $display_buffer .= "
    <TABLE WIDTH=100% BORDER=0 ALIGN=CENTER VALIGN=CENTER
     CELLSPACING=0 CELLPADDING=5>
     <TR>
      <TD ALIGN=RIGHT><FONT>"._("Date of Birth")."</FONT></TD>
      <TD>".fm_date_entry("cidob")."</TD>
     </TR>
     <TR>
      <TD ALIGN=RIGHT><FONT>"._("Complaint")." </FONT></TD>
      <TD><TEXTAREA NAME=\"cicomplaint\" ROWS=4 COLS=40
           WRAP=VIRTUAL>".prepare($cicomplaint)."</TEXTAREA>
      </TD>
     </TR>
     <TR>
      <TD ALIGN=RIGHT><FONT>"._("Facility")." </FONT></TD>
      <TD>
      ".freemed_display_selectbox ($fac_r, "#psrname# [#psrnote#]", "cifacility")."
      </TD>
     </TR>
     <TR>
      <TD ALIGN=RIGHT><FONT>"._("Physician")." </FONT></TD>
      <TD>
    ";

    if ($ciphysician < 1) {
      $ciphysician = freemed_get_link_field ($default_facility, "facility",
        "psrdefphy");
    }
    $phys_r = $sql->query("SELECT * FROM physician ORDER BY phylname, phyfname");

    $display_buffer .= "
    ".freemed_display_selectbox($phys_r, "#phylname#, #phyfname#", "ciphysician")."
      </TD>
    </TR>
    </TABLE>
    <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\" "._("Add")." \"  >
     <INPUT TYPE=RESET  VALUE=\" "._("Clear")." \">
    </CENTER>
    </FORM>
    <P>
  ";
  break;

 case "add":
  $page_title = _("Adding")." "._("$record_name");
  $cicomplaint = addslashes ($cicomplaint);
  $cicomment   = addslashes ($citookcall);
  $cihphone    = $cihphone1 . $cihphone2 . $cihphone3;
  $ciwphone    = $ciwphone1 . $ciwphone2 . $ciwphone3;
  $display_buffer .= "\n"._("Adding")." "._("$record_name")." ... \n";
  $query = "INSERT INTO $db_name VALUES (
    '$cilname',
    '$cifname',
    '$cimname',
    '$cihphone',
    '$ciwphone',
    '".fm_date_assemble("cidob")."',
    '$cicomplaint',
    '$cur_date',
    '$default_facility',
    '$ciphysician',
    '$citookcall',
    '0',
    NULL )";
  $result = $sql->query ($query);

  if ($result) $display_buffer .= _("done");
   else $display_buffer .= _("ERROR");
  $display_buffer .= " 
    <P>
    <CENTER>
     <A HREF=\"patient.php\"
      >Patient Menu |
     <A HREF=\"$page_name\"
      >Call In Menu |
     <A HREF=\"main.php\"
      >"._("Return to the Main Menu")."
    </CENTER>
    <P>
  ";
  break;

 case "view":
 case "display":
  $page_title = _("$record_name")." "._("View/Manage");
  $query   = "SELECT * FROM scheduler WHERE
              ((calpatient='$id') AND (caltype='temp'))
              ORDER BY caldateof, calhour, calminute";
  $result  = $sql->query ($query);
  $rows    = $sql->num_rows ($result);
  $ciname  = freemed_get_link_rec ($id, "callin");
  $cilname = $ciname ["cilname"];
  $cifname = $ciname ["cifname"];
  $cimname = $ciname ["cimname"];
  $display_buffer .= "
    <TABLE WIDTH=100% BGCOLOR=#000000 CELLSPACING=0 CELLPADDING=2
     VALIGN=CENTER ALIGN=CENTER>
    <TR><TD ALIGN=CENTER BGCOLOR=#000000>
     <FONT COLOR=#ffffff>
      <B>$cilname, $cifname $cimname</B> : $rows "._("Appointments")."
     </FONT>
    </TD></TR>
    </TABLE>
    <P>
    <A HREF=\"show_appointments.php?patient=$id&type=temp\"
     >"._("Show Today's Appointments")."</A>
    <P>
    <A HREF=\"show_appointments.php?patient=$id&type=temp&show=all\"
     >"._("Show All Appointments")."</A>
    <P>
    <A HREF=\"main.php\"
     >"._("Return to the Main Menu")."</A>
    </A>
    <P>
  ";
  break;

 default:
  // Set page title
  $page_title = _("$record_name");
  
  // Push onto stack
  page_push();

  $display_buffer .= "
    <BR>
    <CENTER>
     <A HREF=\"$page_name?type=old\"
      >"._("Old")."</A> |
     <A HREF=\"$page_name?type=all\"
      >"._("All")."</A> |
     <A HREF=\"$page_name?type=cur\"
      >"._("Current")."</A>
    </CENTER>
    <BR>
  ";

  $display_buffer .= freemed_display_actionbar ($page_name);

  $display_buffer .= "
    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=3 VALIGN=CENTER
     ALIGN=CENTER BGCOLOR=\"".($_alternate=freemed_bar_alternate_color())."\">
    <TR>
     <TD><B>"._("Name")."</B></TD>
     <TD><B>"._("Date of Call")."</B></TD>
     <TD><B>"._("Home/Work Phone")."</B></TD>
     <TD><B>"._("Action")."</B></TD>
    </TR> 
  ";

    // checks to make sure this hasn't been entered yet...
  switch ($type) {
    case "old":          $__type_call_in__ = "cipatient > 0";  break;
    case "all":          $__type_call_in__ = "0 = 0"; break;
    case "cur": default: $__type_call_in__ = "cipatient = 0";  break;
  } // end checking for type...

  $result = $sql->query ("SELECT * FROM $db_name
             WHERE ($__type_call_in__)
             ORDER BY cidatestamp, cilname, cifname, cimname");

  while ($r = $sql->fetch_array ($result)) {
    extract ($r);

    if (freemed_check_access_for_facility ($cifacility)) {

    if (strlen($cimname)>0) $ci_comma = ", ";
     else $ci_comma = " ";
    $cihphone_raw = $r["cihphone"];
    if (strlen($cihphone_raw)>6)
      $cihphone = "H: " .
                  substr ($cihphone_raw, 0, 3) . "-" .
                  substr ($cihphone_raw, 3, 3) . "-" .
                  substr ($cihphone_raw, 6, 4);
      else $cihphone = "";
    $ciwphone_raw = $r["ciwphone"]; 
    if (strlen($ciwphone_raw)>6)
      $ciwphone = "W: " .
                  substr ($ciwphone_raw, 0, 3) . "-" .
                  substr ($ciwphone_raw, 3, 3) . "-" .
                  substr ($ciwphone_raw, 6, 4);
      else $ciwphone = "";
    if ((strlen($ciwphone)>0) and (strlen($cihphone)>0))
      $ciphonesep = "<BR>";
    else $ciphonesep = " ";

    $display_buffer .= "
      <TR BGCOLOR=\"".($_alternate=freemed_bar_alternate_color($alternate))."\">
       <TD>$cilname, $cifname$ci_comma $cimname</TD>
       <TD>$cidatestamp</TD>
       <TD>$ciwphone $ciphonesep $cihphone&nbsp;</TD>
       <TD>
    ";

     // display the convert link
    $display_buffer .= "
     <A HREF=\"patient.php?action=addform".
        "&ptfname=".rawurlencode ($cifname).
        "&ptlname=".rawurlencode ($cilname).
        "&ptmname=".rawurlencode ($cimname).
        "&pthphone1=".rawurlencode (substr($cihphone_raw, 0, 3)).
        "&pthphone2=".rawurlencode (substr($cihphone_raw, 3, 3)).
        "&pthphone3=".rawurlencode (substr($cihphone_raw, 6, 4)).
        "&ptwphone1=".rawurlencode (substr($ciwphone_raw, 0, 3)).
        "&ptwphone2=".rawurlencode (substr($ciwphone_raw, 3, 3)).
        "&ptwphone3=".rawurlencode (substr($ciwphone_raw, 6, 4)).
        "&ptdob1=".rawurlencode (substr($cidob, 0, 4)).
        "&ptdob2=".rawurlencode (substr($cidob, 5, 2)).
        "&ptdob3=".rawurlencode (substr($cidob, 8, 2)).
        "&ci="     . $id.
        "\"><FONT SIZE=-1>ENTER</FONT></A> &nbsp;
    ";

      // view link
    $display_buffer .= "
     <A HREF=\"$page_name?action=display&id=$id\"
      ><FONT SIZE=-1>"._("VIEW")."</FONT></A> &nbsp;
    ";

     // book link
    $display_buffer .= "
     <A HREF=\"book_appointment.php?action=&".
      "patient=$id&type=temp\"
      ><FONT SIZE=-1>"._("BOOK")."</FONT></A> &nbsp;
    ";

    $display_buffer .= "
        </TD>
      </TR>
    ";

    } // if there was no access for the facility

    $cihphone = "";
    $ciwphone = "";
  } // end while

  $display_buffer .= "
    </TABLE>
  "; // end of the table

  $display_buffer .= freemed_display_actionbar ($page_name);

  $display_buffer .= "
    <BR>
    <CENTER>
     <A HREF=\"$page_name?type=old\"
      >"._("Old")."</A> |
     <A HREF=\"$page_name?type=all\"
      >"._("All")."</A> |
     <A HREF=\"$page_name?type=cur\"
      >"._("Current")."</A>
    </CENTER>
    <BR>
  ";

  break;

} // end master switch

freemed_close_db ();
template_display();

?>
