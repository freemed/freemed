<?php
  # file: call-in.php3
  # desc: module for call-in patients
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL, v2

  $page_name = "call-in.php3";          // page name
  include ("global.var.inc");           // global variables
  include ("freemed-functions.inc");    // API calls
  $record_name = "$Call_In";            // name of record
  $db_name = "callin";                  // database name

freemed_open_db ($LoginCookie);
freemed_display_html_top ();
freemed_display_banner ();

switch ($action) {

 case "addform":
  freemed_display_box_top ("$Add $record_name", $_ref, $page_name); 
  if (strlen($citookcall)<1) {
    $f_auth = explode (":", $LoginCookie);
    $citookcall = freemed_get_link_field ($f_auth[0], "user", "userdescrip");
  } // if there wasn't one passed to us...
  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2
     VALIGN=CENTER ALIGN=CENTER><TR><TD>

      <!-- form fitting box for both tables -->

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2
     VALIGN=TOP ALIGN=CENTER>
    <TR><TD COLSPAN=2 ALIGN=CENTER>
      <B><$STDFONT_B COLOR=#000000>$Name<$STDFONT_E></B>
    </TD></TR>
    <TR>
     <TD WIDTH=30% ALIGN=RIGHT><$STDFONT_B COLOR=#444444>$Last<$STDFONT_E></TD>
     <TD><INPUT TYPE=TEXT NAME=\"cilname\" SIZE=20 MAXLENGTH=50
          VALUE=\"$cilname\"></TD>
    </TR>
    <TR>
     <TD WIDTH=30% ALIGN=RIGHT><$STDFONT_B COLOR=#444444>$First<$STDFONT_E></TD>
     <TD><INPUT TYPE=TEXT NAME=\"cifname\" SIZE=20 MAXLENGTH=50
          VALUE=\"$cifname\"></TD>
    </TR>
    <TR>
     <TD WIDTH=30% ALIGN=RIGHT><$STDFONT_B COLOR=#444444>$Middle<$STDFONT_E></TD>
     <TD><INPUT TYPE=TEXT NAME=\"cimname\" SIZE=20 MAXLENGTH=50
          VALUE=\"$cimname\"></TD>
    </TR>
    </TABLE>

    </TD><TD>

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 VALIGN=TOP
     ALIGN=CENTER BGCOLOR=$darker_bgcolor>
    <TR><TD COLSPAN=2 ALIGN=CENTER>
     <B><$STDFONT_B COLOR=#ffffff>$Contact_Information<$STDFONT_E></B>
    </TD></TR>
    <TR>
     <TD WIDTH=40% ALIGN=RIGHT><$STDFONT_B COLOR=#cccccc>$Home_Phone &nbsp;
      <$STDFONT_E></TD>
     <TD><B>(</B> <INPUT TYPE=TEXT NAME=\"cihphone1\" SIZE=4 MAXLENGTH=3
                   VALUE=\"$cihphone1\">
         <B>)</B> <INPUT TYPE=TEXT NAME=\"cihphone2\" SIZE=4 MAXLENGTH=3
                   VALUE=\"$cihphone2\">
         <B>-</B> <INPUT TYPE=TEXT NAME=\"cihphone3\" SIZE=5 MAXLENGTH=4
                   VALUE=\"$cihphone3\">
     </TD>
    </TR>
    <TR>
     <TD WIDTH=40% ALIGN=RIGHT><$STDFONT_B COLOR=#cccccc>$Work_Phone &nbsp;
      <$STDFONT_E></TD>
     <TD><B>(</B> <INPUT TYPE=TEXT NAME=\"ciwphone1\" SIZE=4 MAXLENGTH=3
                   VALUE=\"$ciwphone1\">
         <B>)</B> <INPUT TYPE=TEXT NAME=\"ciwphone2\" SIZE=4 MAXLENGTH=3
                   VALUE=\"$ciwphone2\">
         <B>-</B> <INPUT TYPE=TEXT NAME=\"ciwphone3\" SIZE=5 MAXLENGTH=4
                   VALUE=\"$ciwphone3\">
     </TD>
    </TR>
    <TR>
     <TD WIDTH=40% ALIGN=RIGHT><$STDFONT_B COLOR=#cccccc>$Took_Call &nbsp;
      <$STDFONT_E></TD>
    <TD>
     <INPUT TYPE=TEXT NAME=\"citookcall\" SIZE=25 MAXLENGTH=50
      VALUE=\"$citookcall\">
    </TD>
    </TR>
    </TABLE>

     <!-- now, end of form fitting table... -->
    </TD></TR></TABLE>

    <P>

    <TABLE WIDTH=100% BORDER=0 ALIGN=CENTER VALIGN=CENTER
     CELLSPACING=0 CELLPADDING=5>
     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>$Date_of_Birth<$STDFONT_E></TD>
      <TD><INPUT NAME=\"cidob1\" SIZE=5 MAXLENGTH=4
           VALUE=\"$cidob1\"> <B>-</B>
          <INPUT NAME=\"cidob2\" SIZE=3 MAXLENGTH=2
           VALUE=\"$cidob2\"> <B>-</B>
          <INPUT NAME=\"cidob3\" SIZE=3 MAXLENGTH=2
           VALUE=\"$cidob3\">
      </TD>
     </TR>
     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>$Complaint <$STDFONT_E></TD>
      <TD><TEXTAREA NAME=\"cicomplaint\" ROWS=4 COLS=40
           WRAP=VIRTUAL>$cicomplaint</TEXTAREA>
      </TD>
     </TR>
     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>$Facility <$STDFONT_E></TD>
      <TD><SELECT NAME=\"cifacility\"> 
    ";
    freemed_display_facilities ($default_facility);
    echo "
       </SELECT>
      </TD>
     </TR>
     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>$Physician <$STDFONT_E></TD>
      <TD><SELECT NAME=\"ciphysician\">
    ";

    if ($ciphysician < 1) {
      $ciphysician = freemed_get_link_field ($default_facility, "facility",
        "psrdefphy");
    }

    freemed_display_physicians($ciphysician);

    echo "
          </SELECT>
      </TD>
    </TR>
    </TABLE>
    <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\" $Add \"  >
     <INPUT TYPE=RESET  VALUE=\" $Clear \">
    </CENTER>
    </FORM>
    <P>
  ";
  freemed_display_box_bottom ();
  break;

 case "add":
  freemed_display_box_top ("$Adding $record_name", $_ref, $page_name);
  $cicomplaint = addslashes ($cicomplaint);
  $cicomment   = addslashes ($citookcall);
  $cihphone    = $cihphone1 . $cihphone2 . $cihphone3;
  $ciwphone    = $ciwphone1 . $ciwphone2 . $ciwphone3;
  $cidob       = $cidob1 . "-" . $cidob2 . "-" . $cidob3;
  echo "
    <$STDFONT_B>$Adding $record_name ...
  ";
  $query = "INSERT INTO $db_name VALUES (
    '$cilname',
    '$cifname',
    '$cimname',
    '$cihphone',
    '$ciwphone',
    '$cidob',
    '$cicomplaint',
    '$cur_date',
    '$default_facility',
    '$ciphysician',
    '$citookcall',
    '0',
    NULL )";
  $result = fdb_query ($query);

  if ($debug==1) echo " (query = \"$query\", result = \"$result\") ";

  if ($result) echo "done.";
   else echo "ERROR";
  echo " <$STDFONT_E>
    <P>
    <CENTER>
     <A HREF=\"patient.php3?$_auth\"
      ><$STDFONT_B>$Patient_Menu<$STDFONT_E> |
     <A HREF=\"call-in.php3?$_auth\"
      ><$STDFONT_B>$Call_In_Menu<$STDFONT_E> |
     <A HREF=\"main.php3?$_auth\"
      ><$STDFONT_B>$Main_Menu<$STDFONT_E>
    </CENTER>
    <P>
  ";
  freemed_display_box_bottom ();
  break;

 case "display":
  freemed_display_box_top ("$record_name Display");
  $query   = "SELECT * FROM scheduler WHERE
              ((calpatient='$id') AND (caltype='temp'))
              ORDER BY caldateof, calhour, calminute";
  $result  = fdb_query ($query);
  $rows    = fdb_num_rows ($result);
  $ciname  = freemed_get_link_rec ($id, "callin");
  $cilname = $ciname ["cilname"];
  $cifname = $ciname ["cifname"];
  $cimname = $ciname ["cimname"];
  echo "
    <TABLE WIDTH=100% BGCOLOR=#000000 CELLSPACING=0 CELLPADDING=2
     VALIGN=CENTER ALIGN=CENTER>
    <TR><TD ALIGN=CENTER BGCOLOR=#000000>
     <$STDFONT_B COLOR=#ffffff>
      <B>$cilname, $cifname $cimname</B> : $rows Appointments
     <$STDFONT_E>
    </TD></TR>
    </TABLE>
    <P>
    <A HREF=\"show_appointments.php3?$_auth&patient=$id&type=temp\"
     ><$STDFONT_B>Show Today's Appointments<$STDFONT_E></A>
    <P>
    <A HREF=\"show_appointments.php3?$_auth&patient=$id&type=temp&show=all\"
     ><$STDFONT_B>Show All Appointments<$STDFONT_E></A>
    <P>
    <A HREF=\"main.php3?$_auth\"
     ><$STDFONT_B>Return to Main Menu<$STDFONT_E></A>
    </A>
    <P>
  ";
  freemed_display_box_bottom ();
  break;

 default:
  freemed_display_box_top ("$record_name");

  echo "
    <BR>
    <CENTER>
     <A HREF=\"$page_name?$_auth&type=old\"
      ><$STDFONT_B>$Old<$STDFONT_E></A> |
     <A HREF=\"$page_name?$_auth&type=all\"
      ><$STDFONT_B>$All<$STDFONT_E></A> |
     <A HREF=\"$page_name?$_auth&type=cur\"
      ><$STDFONT_B>$Current<$STDFONT_E></A>
    </CENTER>
    <BR>
  ";

  freemed_display_actionbar ($page_name);

  $_alternate = freemed_bar_alternate_color ();

  echo "
    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=3 VALIGN=CENTER
     ALIGN=CENTER BGCOLOR=$_alternate>
    <TR>
     <TD><B>$Name</B></TD>
     <TD><B>$Date_of_Call</B></TD>
     <TD><B>$Home_Work_Phone</B></TD>
     <TD><B>$Action</B></TD>
    </TR> 
  ";

    // checks to make sure this hasn't been entered yet...
  switch ($type) {
    case "old":          $__type_call_in__ = "cipatient > 0";  break;
    case "all":          $__type_call_in__ = "0 = 0"; break;
    case "cur": default: $__type_call_in__ = "cipatient = 0";  break;
  } // end checking for type...

  $result = fdb_query ("SELECT * FROM $db_name
             WHERE ($__type_call_in__)
             ORDER BY cidatestamp, cilname, cifname, cimname");

  while ($r = fdb_fetch_array ($result)) {

    $_alternate = freemed_bar_alternate_color ($_alternate);

    $cilname     = $r["cilname"    ];
    $cifname     = $r["cifname"    ];
    $cimname     = $r["cimname"    ];
    $cidatestamp = $r["cidatestamp"];
    $cidob       = $r["cidob"      ];
    $cifacility  = $r["cifacility" ];
    $ciphysician = $r["ciphysician"];
    $id          = $r["id"         ];

    if (freemed_check_access_for_facility ($LoginCookie, $cifacility)) {

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

    echo "
      <TR BGCOLOR=$_alternate>
       <TD><$STDFONT_B>$cilname, $cifname$ci_comma $cimname<$STDFONT_E></TD>
       <TD><$STDFONT_B>$cidatestamp<$STDFONT_E></TD>
       <TD><$STDFONT_B>$ciwphone $ciphonesep $cihphone&nbsp;<$STDFONT_E></TD>
       <TD><$STDFONT_B>
    ";

     // display the convert link
    echo "
     <A HREF=\"patient.php3?$_auth&action=addform".
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
    echo "
     <A HREF=\"$page_name?$_auth&action=display&id=$id\"
      ><FONT SIZE=-1>$VIEW</FONT></A> &nbsp;
    ";

     // book link
    echo "
     <A HREF=\"book_appointment.php3?$_auth&action=&".
      "patient=$id&type=temp\"
      ><FONT SIZE=-1>$BOOK</FONT></A> &nbsp;
    ";

    echo "
        <$STDFONT_E></TD>
      </TR>
    ";

    } // if there was no access for the facility

    $cihphone = "";
    $ciwphone = "";
  } // end while

  echo "
    </TABLE>
  "; // end of the table

  freemed_display_actionbar ($page_name);

  echo "
    <BR>
    <CENTER>
     <A HREF=\"$page_name?$_auth&type=old\"
      ><$STDFONT_B>$Old<$STDFONT_E></A> |
     <A HREF=\"$page_name?$_auth&type=all\"
      ><$STDFONT_B>$All<$STDFONT_E></A> |
     <A HREF=\"$page_name?$_auth&type=cur\"
      ><$STDFONT_B>$Current<$STDFONT_E></A>
    </CENTER>
    <BR>
  ";

  freemed_display_box_bottom ();
  break;

} // end master switch

freemed_display_html_bottom ();
freemed_close_db ();

?>
