<?php
  # file: phy_avail_map.php3
  # note: physician availability map -- for lunches, etc
  # code: I'm-So-Lazy-That-I-Use-The-Template
  #       jeff b (jeff@univrel.pr.uconn.edu) -- template
  # lic : GPL

    // *** local variables section ***
    // complete these to reflect the data for this
    // module.

  $page_name="phy_avail_map.php3";    // for help info, later
  $db_name  ="phyavailmap";           // get this from jeff
  $record_name="Physician Availability Map";  // such as Room for Rooms module
                                      // or "CPT Modifiers" for cptmod
  $order_field="pamdatefrom,pamdateto";     // what field the records are
                                      // sorted by... multiples can
                                      // be used with commas
                                      // ("value_a, value_b")
  $separate_add_section=true;         // if you need the addform action
                                      // keep this, if not, set to false

    // *** includes section ***

  include ("global.var.inc");         // load global variables
  include ("freemed-functions.inc");  // API functions
  include ("freemed-calendar-functions.inc"); // calendar API functions

    // *** authenticate user ***

  freemed_open_db ($LoginCookie);  // authenticate user

    // *** initializing page ***

  freemed_display_html_top ();  // generate top of page
  freemed_display_banner ();    // display package banner

   // check if user is physician, if so, assign var to them, if not...
  $f_auth=explode(":", $LoginCookie);
  if (freemed_get_link_field($f_auth, "user", "usertype")=="phy") {
    $this_physician = freemed_get_link_field ($f_auth, "user", "userrealphy");
  } else { // if they aren't a physician
    if ($physician>0) { $this_physician = $physician; } else {
      freemed_display_box_top ("$record_name $ERROR");
      echo "
        <P>
        <CENTER><$STDFONT_B>$Must_specify_physician<$STDFONT_E></CENTER>
        <P>
      ";
      freemed_display_box_bottom (); 
      freemed_close_db ();
      freemed_display_html_bottom ();
      DIE("");
    } // end checking for physician>0
  } // end of that mess

// *** main action loop ***
// (default action is "view")

if (($action=="addform") AND ($separate_add_section)) {

  freemed_display_box_top ("$Add $record_name", $page_name);

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\"    VALUE=\"add\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"        VALUE=\"$id\">
    <INPUT TYPE=HIDDEN NAME=\"physician\" VALUE=\"$physician\">

    <TABLE BORDER=0 CELLSPACING=5 CELLPADDING=2 VALIGN=CENTER
     ALIGN=CENTER>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Absence_date_from : <$STDFONT_E></TD>
    <TD>
  ";
  $pamdatefrom = $cur_date;
  fm_date_entry ("pamdatefrom");
  echo "
    </TD></TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Absence_date_to : <$STDFONT_E></TD>
    <TD>
  ";
  $pamdateto   = $cur_date;
  fm_date_entry ("pamdateto");
  echo "
    </TD></TR>
 
    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Time_beginning : <$STDFONT_E></TD>
    <TD>
    <SELECT NAME=\"pamtimefromhour\">
  ";

  // show the hours, mapped properly, etc
  for ($h=fc_starting_hour();$h<=fc_ending_hour();$h++) {
    if ($h<12)       { $hour = "$h am";       }
     elseif ($h==12) { $hour = "$noon";       }
     elseif ($h==24) { $hour = "$midnight";   } 
     else            { $hour = ($h-12)." pm"; }
    if ($h==$pamtimefromhour) { $this_selected = "$SELECTED"; }
     else                     { $this_selected = "";          }
    echo "\n<OPTION VALUE=\"$h\" $this_selected>$hour";
  } // end for loop (hours)

  echo "
    </SELECT>
    <SELECT NAME=\"pamtimefrommin\">
  ";

  // show the minutes, properly mapped
  for ($m=0;$m<60;$m+=15) {
    if ($m==$pamtimefrommin) { $this_selected = "$SELECTED"; }
     else                    { $this_selected = "";          }
    if ($m==0)               { $min           = "00";        }
     else                    { $min           = $m;          }
    echo "\n<OPTION VALUE=\"$m\" $this_selected>$min ";
  } // end for loop (minutes)

  echo "
    </SELECT>
    </TD></TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Time_ending : <$STDFONT_E></TD>
    <TD>
     FINISH ME!!!!
    </TD></TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Comment<$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"pamcomment\" SIZE=30 MAXLENGTH=75
     VALUE=\"$pamcomment\">
    </TD></TR>
    </TABLE>

    <BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Add \"  >
    <INPUT TYPE=RESET  VALUE=\" $Clear \">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view&physician=$physician\"
     >$Abandon_Addition</A>
    </CENTER>
  ";

} elseif ($action=="add") {

  freemed_display_box_top("$Adding $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

  // build data fields
  $pamdatefrom = fm_date_assemble ("pamdatefrom");
  $pamdateto   = fm_date_assemble ("pamdateto"  ); 
  $pamcomment  = addslashes ($pamcomment);

  $query = "INSERT INTO $db_name VALUES (
     '$pamdatefrom', '$pamdateto',
     '$pamtimefrom', '$pamtimeto',
     '$physician',   '$pamcomment', 
     NULL )";

    // query the db with new values
  $result = fdb_query($query);

  if ($debug==1) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;      
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>$Done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B><$STDFONT_E>\n"); 
  }

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth&physician=$physician\"
     ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>
    <P>
  "; // readability fix 19990714

  freemed_display_box_bottom (); // display the bottom of the box
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modify $record_name", $page_name);

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a
       $record_name!</B>
     </CENTER>

     <P>
    ";

    if ($debug==1) {
      echo "
        ID = [<B>$id</B>]
        <P>
      ";
    }

    freemed_display_box_bottom (); // display the bottom of the box
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >$Return_to_the_Main_Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  // if there _IS_ an ID tag presented, we must extract the record
  // from the database, and proverbially "fill in the blanks"

    // grab record number "id"
  $result = fdb_query("SELECT * FROM $db_name ".
    "WHERE ( id = '$id' )");

    // display for debugging purposes
  if ($debug==1) {
    echo " <B>RESULT</B> = [$result]<BR><BR> ";
  }

  $r = fdb_fetch_array($result); // dump into array r[]

  $pamdatefrom_y = substr($r["pamdatefrom"], 0, 4);
  $pamdatefrom_m = substr($r["pamdatefrom"], 5, 2);
  $pamdatefrom_d = substr($r["pamdatefrom"], 8, 2);
  $pamdateto_y   = substr($r["pamdateto"  ], 0, 4);
  $pamdateto_m   = substr($r["pamdateto"  ], 5, 2);
  $pamdateto_d   = substr($r["pamdateto"  ], 8, 2);
  $pamcomment    = $r["pamcomment"  ];
  $pamphysician  = $r["pamphysician"];

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >
    <INPUT TYPE=HIDDEN NAME=\"physician\" VALUE=\"$physician\">

    <TABLE BORDER=0 CELLSPACING=5 CELLPADDING=2 VALIGN=MIDDLE
     ALIGN=CENTER>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Absence_date_from : <$STDFONT_E></TD>
    <TD>
  ";
  fm_date_entry("pamdatefrom");
  echo "
    </TD></TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Absence_date_to : <$STDFONT_E></TD>
  ";
  fm_date_entry("pamdateto");
  echo "
    </TD></TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Time_beginning : <$STDFONT_E></TD>
    <TD><SELECT NAME=\"pamtimefromhour\">
  ";

  // show the hours, mapped properly, etc
  for ($h=fc_starting_hour();$h<=fc_ending_hour();$h++) {
    if ($h<12)       { $hour = "$h am";       }
     elseif ($h==12) { $hour = "$noon";       }
     elseif ($h==24) { $hour = "$midnight";   } 
     else            { $hour = ($h-12)." pm"; }
    if ($h==$pamtimefromhour) { $this_selected = "$SELECTED"; }
     else                     { $this_selected = "";          }
    echo "\n<OPTION VALUE=\"$h\" $this_selected>$hour";
  } // end for loop (hours)

  echo "
    </SELECT>
    <SELECT NAME=\"pamtimefrommin\">
  ";

  // show the minutes, properly mapped
  for ($m=0;$m<60;$m+=15) {
    if ($m==$pamtimefrommin) { $this_selected = "$SELECTED"; }
     else                    { $this_selected = "";          }
    if ($m==0)               { $min           = "00";        }
     else                    { $min           = $m;          }
    echo "\n<OPTION VALUE=\"$m\" $this_selected>$min ";
  } // end for loop (minutes)

  echo "
    </SELECT>
    </TD></TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Time_ending<$STDFONT_E></TD>
    <TD>
     NOT FINISHED YET!!! FINISH ME!!!
    </TD></TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Comment<$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"pamcomment\" SIZE=30 MAXLENGTH=75
     VALUE=\"$pamcomment\">
    </TD></TR>
    </TABLE>

    <BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view&physician=$physician\"
     >$Abandon_Modification</A>
    </CENTER>
  ";

} elseif ($action=="mod") {

   #      M O D I F Y - R O U T I N E

  freemed_display_box_top ("$Modifying $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

  $pamdatefrom = fm_date_assemble ("pamdatefrom");
  $pamdateto   = fm_date_assemble ("pamdateto"  );
  $pamcomment  = addslashes ($pamcomment);
 
  $query = "UPDATE $db_name SET ".
    "pamdatefrom     = '$pamdatefrom',     ".
    "pamdateto       = '$pamdateto',       ".
    "pamtimefromhour = '$pamtimefromhour', ".
    "pamtimefrommin  = '$pamtimefrommin',  ".
    "pamtimetohour   = '$pamtimetohour',   ".
    "pamtimetomin    = '$pamtimetomin',    ".
    "pamcomment      = '$pamcomment'       ".
    "WHERE id='$id'";

  $result = fdb_query($query); // execute query

  if ($debug==1) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>$Done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  } // end of error reporting clause

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth&physician=$physician\"
     ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom (); // display box bottom 
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $record_name", $page_name);

    // select only "id" record, and delete
  $result = fdb_query("DELETE FROM $db_name
    WHERE (id = \"$id\")");

  echo "
    <BR><BR>
    <I>$record_name <B>$id</B> $Deleted<I>.
    <BR>
  ";
  if ($debug==1) {
    echo "
      <BR><B>RESULT:</B><BR>
      $result<BR><BR>
    ";
  }
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view&physician=$physician\"
     >$Update_Delete_Another</A></CENTER>
  ";
  freemed_display_box_bottom ();
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} else {

  // with no anythings, ?action=search returns everything
  // in the database for modification... useful to note in
  // future...

  $query = "SELECT * FROM $db_name ".
   "ORDER BY $order_field";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ($record_name, $_ref, $page_name);

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    // if you would rather have the add form built onto the view
    // menu, uncomment the next few lines. 

    //echo "
    //  <TABLE BGCOLOR=#000000 WIDTH=100% BORDER=0
    //   CELLSPACING=0 CELLPADDING=3>
    //  <TR BGCOLOR=#000000>
    //  <TD ALIGN=LEFT>&nbsp;</TD>
    //  <TD WIDTH=30%>&nbsp;</TD>
    //  <TD ALIGN=RIGHT><A HREF=\"$_ref?$_auth\"
    //   ><FONT COLOR=#ffffff FACE=\"Arial, Helvetica, Verdana\"
    //   SIZE=-1><B>RETURN TO MENU</B></FONT></A></TD>
    //  </TR></TABLE>
    //";

    // and comment this line:
    freemed_display_actionbar($page_name, $_ref);

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$Physician</B></TD>
       <TD><B>$Availability</B></TD>
       <TD><B>$Comment</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = fdb_fetch_array($result)) {

      $physician       = freemed_get_link_rec($r["pamphysician"], "physician");
      $phylname        = $physician["phylname"];
      $phyfname        = $physician["phyfname"];
      $phymname        = $physician["phymname"]; 
      $pamtimefromhour = $r["pamtimefromhour" ];
      $pamtimefrommin  = $r["pamtimefrommin"  ];
      $pamtimetohour   = $r["pamtimetohour"   ];
      $pamtimetomin    = $r["pamtimetomin"    ];
      $comment         = htmlentities($r["pamcomment"]);
      $id              = $r["id"];

        // alternate the bar color
      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug==1) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$phylname, $phyfname $phymname</TD>
        <TD><I>$pamdatefrom $lang_to $pamdateto<BR>
         $pamtimefromhour:$pamtimefrommin - $pamtimetohour:$pamtimetomin
         </I>&nbsp;</TD>
        <TD>$comment &nbsp;</TD>
        <TD><A HREF=
         \"$page_name?$_auth&id=$id&action=modform&physician=$physician\"
         ><FONT SIZE=-1>$lang_MOD$id_mod</FONT></A>
      ";
      if (freemed_get_userlevel($LoginCookie)>$delete_level)
        echo "
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del&physician=$physician\"
          ><FONT SIZE=-1>$lang_DEL$id_mod</FONT></A>
        "; // show delete
      echo "
        </TD></TR>
      ";

    } // while there are no more

    echo "
      </TABLE>
    "; // end table (fixed 19990617)

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    // then comment this:
    freemed_display_actionbar ($page_name, $_ref);

    freemed_display_box_bottom (); // display bottom of the box
    freemed_display_bottom_links ($record_name, $page_name, $_ref);

  } else {
    echo "\n<B>$No_Record_Found</B>\n";
  }

} 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
